#!/usr/bin/env bash
set -euo pipefail

cat > ~/.my.cnf <<EOF
[client]
host=${DATABASE_HOSTNAME}
user=${DATABASE_USERNAME}
password=${DATABASE_PASSWORD}
EOF

cnt=$(mysql --skip-ssl-verify-server-cert -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DATABASE_NAME';")

if [[ "$VERBOSE_SQL" == "true" ]]; then
  cat >>~/.my.cnf <<EOF
[mysql]
verbose
EOF
fi

echo "$DATABASE_NAME has $cnt tables"

if ! [[ "${cnt}" =~ ^[0-9]+$ ]]; then
  echo "Warning: table count is not a number: '${cnt}'" >&2
fi

if ! [[ "$cnt" -gt 0 ]]; then
  sed -e "s|\${DATABASE_HOSTNAME}|${DATABASE_HOSTNAME}|g" \
      -e "s|\${DATABASE_USERNAME}|${DATABASE_USERNAME}|g" \
      -e "s|\${DATABASE_PASSWORD}|${DATABASE_PASSWORD}|g" \
      -e "s|\${DATABASE_NAME}|${DATABASE_NAME}|g" \
      config.template.php > config.php

  echo "inserting data"

  mysql --skip-ssl-verify-server-cert "$DATABASE_NAME" < ./dump_schema.sql

  mysql --skip-ssl-verify-server-cert "$DATABASE_NAME" < ./dump_baseline_data.sql

  if [ -f ./dump_dev_data_local.sql ]; then
    echo "inserting local dev data"
    mysql --skip-ssl-verify-server-cert "$DATABASE_NAME" < ./dump_dev_data_local.sql
  else
    echo "inserting dev data"
    mysql --skip-ssl-verify-server-cert "$DATABASE_NAME" < ./dump_dev_data.sql
  fi
fi

docker-php-entrypoint apache2-foreground
