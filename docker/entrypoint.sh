#!/usr/bin/env bash

cat config.template.php |
  sed 's/${DATABASE_HOSTNAME}/'"$DATABASE_HOSTNAME"'/' |
  sed 's/${DATABASE_USERNAME}/'"$DATABASE_USERNAME"'/' |
  sed 's/${DATABASE_PASSWORD}/'"$DATABASE_PASSWORD"'/' |
  sed 's/${DATABASE_NAME}/'"$DATABASE_NAME"'/' >config.php

mysql --skip-ssl-verify-server-cert -v -h "$DATABASE_HOSTNAME" \
  -u "$DATABASE_USERNAME" \
  -p"$DATABASE_PASSWORD" \
  "$DATABASE_NAME" <./dump_schema.sql

mysql --skip-ssl-verify-server-cert -v -h "$DATABASE_HOSTNAME" \
  -u "$DATABASE_USERNAME" \
  -p"$DATABASE_PASSWORD" \
  "$DATABASE_NAME" <./dump_baseline_data.sql

docker-php-entrypoint apache2-foreground
