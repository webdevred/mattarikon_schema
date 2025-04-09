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

def do_dump(config, extraArgs = []) :
    user = config['USERNAME']
    password = config['PASSWORD']
    host = config['SERVER_HOSTNAME']
    db_name = config['DATABASE_NAME']
    command = ['mysqldump', '--skip-disable-keys', '--skip-add-locks', '--skip-add-drop-table', '--skip-comments', f'--user={user}', f'--password={password}', f'--host={host}', db_name]
    command.extend(extraArgs)
    proc = subprocess.run(command, capture_output=True)
    return proc.stdout.decode('utf-8')

def load_dump(config, in_file, extraArgs = []) :
    user = config['USERNAME']
    password = config['PASSWORD']
    host = config['SERVER_HOSTNAME']
    db_name = config['DATABASE_NAME']
    command = ['mysql', f'--user={user}', f'--password={password}', f'--host={host}', db_name]
    command.extend(extraArgs)
    proc = subprocess.Popen(command, stdin=subprocess.PIPE,stderr=subprocess.DEVNULL)
    proc.communicate('\n'.join(in_file).encode('utf-8'))

def insert_stmt_for_table(line) :
    global baseline_tables
    for table in baseline_tables :
        if re.match(f'^INSERT INTO `{table}`', line) :
            return True
    return False

def dump_baseline(config) :
    current_sql_dump = do_dump(config, ['--complete-insert', '--skip-extended-insert', '--no-create-info'])
    lines=[]
    global baseline_tables
    excessive_newline=True
    for line in current_sql_dump.split('\n') :
        if line.strip() == '' :
            if not excessive_newline :
                lines.append('')
                excessive_newline=True
        elif insert_stmt_for_table(line) or not re.match(r'^INSERT INTO `[^`]+`', line) :
            lines.append(line.strip())
            excessive_newline=False
    return lines

def read_baseline_data_file() :
    with open('dump_baseline_data.sql') as baseline_file :
        lines_from_file = baseline_file.readlines()
        lines=[]
        for line in lines_from_file :
            lines.append(line.strip())
        return lines

def load_baseline_file(config, diffs, in_file) :
    (new_for_file,new_for_db) = diffs
    for item in new_for_file :
        print(f'Extra in in_file: {repr(item)}')
    for item in new_for_db :
        print(f'Extra in in_db:   {repr(item)}')
    if new_for_file :
       print('there is extra stuff in file, would you like to continue?')
       if input() != 'y' :
           exit(0)
    elif not new_for_file and not new_for_db :
        print('no changes, no save needed')
        exit(0)
    load_dump(config, in_file, extraArgs = [])

def store_baseline_dump(diffs,in_db) :
    (new_for_file,new_for_db) = diffs
    if new_for_file :
       print('there is extra stuff in file, would you like to continue?')
       if input() != 'y' :
           exit(0)
    elif not new_for_file and not new_for_db :
        print('no changes, no save needed')
        exit(0)
    with open('dump_baseline_data.sql', 'w') as storage :
        storage.write('\n'.join(in_db) + '\n')

def get_changed_lines(in_db, in_file) :
    new_for_file = []
    new_for_db = []
    for diffline in difflib.unified_diff(in_file, in_db) :
        if diffline.startswith('+++ ') or diffline.startswith('--- ') or diffline.startswith('@@ ') :
            continue
        elif diffline.startswith('+') :
            line=diffline[1:]
            if line != '' :
                print(f'Extra in in_db: {line}')
            new_for_db.append(line)
        elif diffline.startswith('-') :
            line=diffline[1:]
            if line != '' :
                print(f'Extra in in_file: {line}')
            new_for_file.append(line)
        else :
            continue
    return (new_for_file,new_for_db)

def main() :
    config = read_config()
    in_file = read_baseline_data_file()
    in_db = dump_baseline(config)
    diffs = get_changed_lines(in_db, in_file)
    match argv[1] :
        case 'd' :
            store_baseline_dump(diffs,in_db)
        case 'l' :
            load_baseline_file(config,diffs,in_file)

if __name__ == '__main__' :
    main()
