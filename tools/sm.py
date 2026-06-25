import subprocess
import re
from sys import argv
import difflib

baseline_tables = ['activity_types', 'users']

def read_config() :
    with open('config.php') as config_file :
        lines = config_file.readlines()
        config = {}
        for line in lines :
            if line.startswith('define') :
                config_pair = re.search(r"define\s*\([\"'](?P<field>[^\"']+)[\"'],\s*[\"'](?P<value>[^\"']+)[\"']\)", line)
                field_name = config_pair.group('field')
                value = config_pair.group('value')
                config[field_name] = value
        for field in [ 'SERVER_HOSTNAME','USERNAME','PASSWORD','DATABASE_NAME'] :
            try :
                config[field]
            except :
                print('config missing ' + field)
                exit(1)
        return config

def do_dump(config, extraArgs=[], tables=[]) :
    user = config['USERNAME']
    password = config['PASSWORD']
    host = config['SERVER_HOSTNAME']
    db_name = config['DATABASE_NAME']
    command = ['mysqldump', '--skip-disable-keys', '--skip-add-locks', '--skip-add-drop-table', '--skip-comments',
               f'--user={user}', f'--password={password}', f'--host={host}'] + extraArgs + [db_name] + tables
    proc = subprocess.run(command, capture_output=True)
    return proc.stdout.decode('utf-8')

def run_sql(config, sql_lines) :
    user = config['USERNAME']
    password = config['PASSWORD']
    host = config['SERVER_HOSTNAME']
    db_name = config['DATABASE_NAME']
    command = ['mysql', f'--user={user}', f'--password={password}', f'--host={host}', db_name]
    proc = subprocess.Popen(command, stdin=subprocess.PIPE, stderr=subprocess.DEVNULL)
    proc.communicate('\n'.join(sql_lines).encode('utf-8'))

def filter_dump_lines(raw, transform=None) :
    lines = []
    excessive_newline = True
    for line in raw.split('\n') :
        if line.strip() == '' :
            if not excessive_newline :
                lines.append('')
                excessive_newline = True
        else :
            if transform :
                line = transform(line)
            lines.append(line.strip())
            excessive_newline = False
    return lines

# --- Schema ---

def get_db_schema(config) :
    raw = do_dump(config, ['--no-data'], baseline_tables)
    return filter_dump_lines(raw,
        lambda l: l.replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `'))

def get_file_schema(file_lines) :
    return [l for l in file_lines if not re.match(r'^INSERT INTO', l)]

def normalize_schema_line(line) :
    return line.replace('IF NOT EXISTS ', '')

def diff_schema_lines(db_lines, file_lines) :
    db_norm = [normalize_schema_line(l) for l in db_lines]
    file_norm = [normalize_schema_line(l) for l in file_lines]
    diff = list(difflib.unified_diff(file_norm, db_norm))
    schema_file_extra = False
    for line in diff :
        if line.startswith(('+++', '---', '@@')) :
            continue
        if line.startswith('+') and line[1:].strip() :
            print(f'Extra in db   (schema): {line[1:]}')
        elif line.startswith('-') and line[1:].strip() :
            print(f'Extra in file (schema): {line[1:]}')
            schema_file_extra = True
    return bool(diff), schema_file_extra

# --- Data ---

def get_db_data(config) :
    raw = do_dump(config, ['--complete-insert', '--skip-extended-insert', '--no-create-info'], baseline_tables)
    lines = filter_dump_lines(raw)
    return [l for l in lines if re.match(r'^INSERT INTO', l) or l == '']

def get_file_data(file_lines) :
    return [l for l in file_lines if re.match(r'^INSERT INTO', l) or l == '']

def parse_values(s) :
    values = []
    i, n = 0, len(s)
    while i < n :
        while i < n and s[i] in (' ', '\t', ',') :
            i += 1
        if i >= n :
            break
        if s[i] == "'" :
            j = i + 1
            while j < n :
                if s[j] == '\\' :
                    j += 2
                elif s[j] == "'" :
                    j += 1
                    break
                else :
                    j += 1
            values.append(s[i:j])
            i = j
        elif s[i:i+4] == 'NULL' and (i+4 >= n or s[i+4] in (',', ' ', '\t')) :
            values.append('NULL')
            i += 4
        else :
            j = i
            while j < n and s[j] not in (',', ' ', '\t') :
                j += 1
            values.append(s[i:j])
            i = j
    return values

def parse_insert_line(line) :
    m = re.match(r"INSERT INTO `([^`]+)` \(([^)]+)\) VALUES \((.+)\);$", line.strip())
    if not m :
        return None
    table = m.group(1)
    cols = [c.strip().strip('`') for c in m.group(2).split(',')]
    vals = parse_values(m.group(3))
    return (table, cols, dict(zip(cols, vals)))

def parse_insert_statements(lines) :
    tables = {}
    for line in lines :
        parsed = parse_insert_line(line)
        if parsed :
            table, cols, row = parsed
            if table not in tables :
                tables[table] = {'cols': cols, 'rows': []}
            tables[table]['rows'].append(row)
    return tables

def row_key(row) :
    return frozenset(row.items())

def diff_table_data(db_data, file_data) :
    result = {}
    for table in baseline_tables :
        db_entry = db_data.get(table, {})
        file_entry = file_data.get(table, {})
        db_rows = db_entry.get('rows', [])
        file_rows = file_entry.get('rows', [])
        cols = db_entry.get('cols') or file_entry.get('cols', [])
        db_keyed = {row_key(r): r for r in db_rows}
        file_keyed = {row_key(r): r for r in file_rows}
        only_in_db = [db_keyed[k] for k in db_keyed if k not in file_keyed]
        only_in_file = [file_keyed[k] for k in file_keyed if k not in db_keyed]
        result[table] = (only_in_db, only_in_file, cols)
    return result

def print_data_diffs(diffs) :
    for table, (only_in_db, only_in_file, _) in diffs.items() :
        for row in only_in_db :
            print(f'Extra in db   ({table}): {row}')
        for row in only_in_file :
            print(f'Extra in file ({table}): {row}')

def has_data_changes(diffs) :
    return any(a or b for a, b, _ in diffs.values())

def row_to_insert(table, row, cols) :
    col_str = ', '.join(f'`{c}`' for c in cols)
    val_str = ', '.join(row[c] for c in cols)
    return f"INSERT INTO `{table}` ({col_str}) VALUES ({val_str});"

def row_to_delete(table, row) :
    conds = ' AND '.join(
        f'`{k}` IS NULL' if v == 'NULL' else f'`{k}` = {v}'
        for k, v in row.items()
    )
    return f"DELETE FROM `{table}` WHERE {conds};"

# --- Commands ---

def parse_args(arg) :
    arg = arg.lower()
    has_d = 'd' in arg
    has_l = 'l' in arg
    has_b = 'b' in arg
    has_s = 's' in arg
    if has_d and has_l :
        print('error: d (dump) and l (load) are mutually exclusive')
        exit(1)
    if not has_d and not has_l :
        print('error: must specify d (dump) or l (load)')
        exit(1)
    if not has_b and not has_s :
        print('error: must specify b (baseline data) and/or s (structure)')
        exit(1)
    return {'dump': has_d, 'structure': has_s, 'baseline': has_b}

def write_file(schema_lines, data_lines) :
    parts = schema_lines + ([''] if schema_lines and data_lines else []) + data_lines
    with open('dump_baseline_data.sql', 'w') as f :
        f.write('\n'.join(parts) + '\n')

def do_dump_cmd(config, args, schema_changed, schema_file_extra, data_diffs, in_file, db_schema, db_data) :
    do_s = args['structure']
    do_b = args['baseline']
    has_file_extra = (do_s and schema_file_extra) or (do_b and any(b for _, b, _ in data_diffs.values()))
    has_change = (do_s and schema_changed) or (do_b and has_data_changes(data_diffs))
    if has_file_extra :
        print('there is extra stuff in file, would you like to continue?')
        if input() != 'y' :
            exit(0)
    elif not has_change :
        print('no changes, no save needed')
        exit(0)
    new_schema = db_schema if do_s else get_file_schema(in_file)
    new_data = db_data if do_b else get_file_data(in_file)
    write_file(new_schema, new_data)

def do_load_cmd(config, args, schema_changed, data_diffs, in_file) :
    do_s = args['structure']
    do_b = args['baseline']
    has_db_extra = do_b and any(a for a, _, _ in data_diffs.values())
    has_change = (do_s and schema_changed) or (do_b and has_data_changes(data_diffs))
    if has_db_extra :
        print('there is extra stuff in db not in file, would you like to delete it?')
        if input() != 'y' :
            exit(0)
    elif not has_change :
        print('no changes, no save needed')
        exit(0)
    if do_s :
        run_sql(config, get_file_schema(in_file))
    if do_b :
        sql = []
        for table, (db_only, file_only, cols) in data_diffs.items() :
            for row in file_only :
                sql.append(row_to_insert(table, row, cols))
            for row in db_only :
                sql.append(row_to_delete(table, row))
        if sql :
            run_sql(config, sql)

def main() :
    args = parse_args(argv[1])
    config = read_config()
    in_file = read_baseline_data_file()

    schema_changed = schema_file_extra = False
    data_diffs = {t: ([], [], []) for t in baseline_tables}
    db_schema = db_data = None

    if args['structure'] :
        db_schema = get_db_schema(config)
        schema_changed, schema_file_extra = diff_schema_lines(db_schema, get_file_schema(in_file))

    if args['baseline'] :
        db_data = get_db_data(config)
        data_diffs = diff_table_data(
            parse_insert_statements(db_data),
            parse_insert_statements(get_file_data(in_file))
        )
        print_data_diffs(data_diffs)

    if args['dump'] :
        do_dump_cmd(config, args, schema_changed, schema_file_extra, data_diffs, in_file, db_schema, db_data)
    else :
        do_load_cmd(config, args, schema_changed, data_diffs, in_file)

def read_baseline_data_file() :
    with open('dump_baseline_data.sql') as baseline_file :
        return [line.strip() for line in baseline_file.readlines()]

if __name__ == '__main__' :
    main()
