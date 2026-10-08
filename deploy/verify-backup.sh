#!/usr/bin/env bash
# Run as the operator in the existing taxi deployment directory. Read-only
# production export; imports/extraction affect newly created isolated copies only.
set -euo pipefail
umask 077

task_deployment=$(pwd -P)
if [[ "$task_deployment" != /www/wwwroot/taxi || ! -f docker-compose.yml || ! -f .env ]]; then
    echo 'Refusing backup outside the expected existing taxi deployment.' >&2
    exit 1
fi
task_app=lalataxi-app-1
task_database=lalataxi-mysql-1
for task_container in "$task_app" "$task_database"; do
    [[ $(docker inspect "$task_container" --format '{{index .Config.Labels "com.docker.compose.project"}}') == lalataxi ]] || exit 1
done
task_image=$(docker inspect "$task_database" --format '{{.Image}}')
task_app_image=$(docker inspect "$task_app" --format '{{.Image}}')
task_database_name=$(docker exec "$task_database" printenv MYSQL_DATABASE)
[[ "$task_database_name" =~ ^[a-zA-Z0-9_]+$ ]] || exit 1
mkdir -p "$task_deployment/backup"
task_job=$(mktemp -d "$task_deployment/backup/corporate-preflight.XXXXXX")
chmod 700 "$task_job"
task_restore_name="lala-corporate-restore-$(basename "$task_job" | tr . -)"
task_restore_started=false
trap 'if [[ "$task_restore_started" == true ]]; then docker stop "$task_restore_name" >/dev/null; fi' EXIT

echo 'Exporting a transaction-consistent database and protected deployment/upload archives.'
docker exec "$task_database" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --quick --routines --events --triggers --hex-blob --set-gtid-purged=OFF --no-tablespaces --column-statistics=0 "$MYSQL_DATABASE"' | gzip > "$task_job/database.sql.gz"
tar -czf "$task_job/deployment.tar.gz" .env docker-compose.yml nginx.conf
docker exec "$task_app" tar -C /var/www/html/storage -czf - . > "$task_job/storage.tar.gz"
docker exec "$task_app" tar -C /var/www/html/public/trip_file -czf - . > "$task_job/trip-files.tar.gz"
docker exec "$task_app" tar -C /var/www/html/public/images -czf - . > "$task_job/images.tar.gz"
docker exec "$task_app" tar -C /var/www/html --exclude='./storage' --exclude='./public/trip_file' --exclude='./public/images' --exclude='./vendor' -czf - . > "$task_job/code.tar.gz"
for task_archive in "$task_job"/*.gz; do gzip -t "$task_archive"; done
(cd "$task_job" && sha256sum ./*.gz > SHA256SUMS)

echo 'Restoring the database into a fresh container with no network or published ports.'
task_restore_password=$(openssl rand -hex 32)
docker run -d --name "$task_restore_name" --network none \
    --label lala.corporate.backup-verification="$(basename "$task_job")" \
    -e MYSQL_ROOT_PASSWORD="$task_restore_password" -e MYSQL_DATABASE="$task_database_name" \
    "$task_image" > /dev/null
task_restore_started=true
for task_attempt in {1..60}; do
    if docker exec "$task_restore_name" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --execute "SELECT 1" "$MYSQL_DATABASE"' >/dev/null 2>&1; then break; fi
    sleep 2
done
gzip -dc "$task_job/database.sql.gz" | docker exec -i "$task_restore_name" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql "$MYSQL_DATABASE"'
# MySQL 8.4 images omit mysqlcheck. Use the same server-side CHECK TABLE
# operation through the bundled SQL client, including every restored table.
docker exec -i "$task_restore_name" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --batch --skip-column-names "$MYSQL_DATABASE"' \
    <<< "SELECT CONCAT('CHECK TABLE \`',REPLACE(TABLE_NAME,'\`','\`\`'),'\`;') FROM information_schema.TABLES WHERE TABLE_SCHEMA='$task_database_name' AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME;" \
    > "$task_job/restored-table-check-statements.sql"
docker exec -i "$task_restore_name" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --batch --skip-column-names "$MYSQL_DATABASE"' \
    < "$task_job/restored-table-check-statements.sql" > "$task_job/restored-table-checks.txt"
awk -F '\t' 'BEGIN { checked=0; failed=0 } $3 == "error" { failed=1 } $3 == "status" { checked++; if ($4 != "OK") failed=1 } END { exit (checked == 0 || failed) }' "$task_job/restored-table-checks.txt"

task_schema_query="SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COALESCE(COLUMN_DEFAULT,'NULL'),EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$task_database_name' ORDER BY TABLE_NAME,ORDINAL_POSITION; SELECT TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX,COLUMN_NAME,NON_UNIQUE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='$task_database_name' ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX; SELECT TABLE_NAME,CONSTRAINT_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='$task_database_name' ORDER BY TABLE_NAME,CONSTRAINT_NAME,ORDINAL_POSITION;"
for task_target in "$task_database" "$task_restore_name"; do
    docker exec -i "$task_target" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --batch --skip-column-names "$MYSQL_DATABASE"' <<< "$task_schema_query" > "$task_job/schema-$task_target.txt"
done
cmp "$task_job/schema-$task_database.txt" "$task_job/schema-$task_restore_name.txt"
docker exec -i "$task_restore_name" sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --batch --skip-column-names "$MYSQL_DATABASE"' \
    <<< 'SELECT "users",COUNT(*) FROM users UNION ALL SELECT "trips",COUNT(*) FROM trips UNION ALL SELECT "corporate_accounts",COUNT(*) FROM company_corporate_accounts UNION ALL SELECT "corporate_invoices",COUNT(*) FROM company_corporate_invoices;' > "$task_job/restored-record-counts.txt"

echo 'Restoring protected files into new directories and comparing them to each archive.'
mkdir "$task_job/restored-files"
for task_kind in deployment storage trip-files images code; do
    mkdir "$task_job/restored-files/$task_kind"
    tar -xzf "$task_job/$task_kind.tar.gz" -C "$task_job/restored-files/$task_kind"
    tar -dzf "$task_job/$task_kind.tar.gz" -C "$task_job/restored-files/$task_kind" > /dev/null
done
(cd "$task_job" && sha256sum -c SHA256SUMS > archive-checks.txt)
{
    printf 'Verified UTC: %s\nApp image: %s\nMySQL image: %s\n' "$(date -u +%FT%TZ)" "$task_app_image" "$task_image"
    printf 'Database: %s\nRestore container: %s\n' "$task_database_name" "$task_restore_name"
    echo 'PASS database import and table checks; columns/indexes/FKs match source.'
    echo 'PASS archive checksums and file extraction/compare.'
    echo 'No production services stopped, migrations applied or financial rows changed.'
    echo 'Upload archives are captured sequentially; this is not a globally atomic DB/upload snapshot.'
    echo 'Protected backup, restored files and stopped isolated restore container are retained.'
} > "$task_job/RESTORE_EVIDENCE.txt"
chmod -R go-rwx "$task_job"
echo "PASS protected backup and isolated restore: $task_job"
echo "Evidence: $task_job/RESTORE_EVIDENCE.txt"
