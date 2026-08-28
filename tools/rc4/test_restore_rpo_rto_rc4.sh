#!/usr/bin/env bash
set -u
ROOT=/home/ubuntu/atelier-cdc-20
WPCLI=$ROOT/.tools/wp-cli.phar
SRC=$ROOT/wp
RESTORE=/tmp/atelier-rc4-restore
DB=atelier_cdc20_rc4_restore
DUMP=/tmp/atelier-rc4-restore.sql
LOG=/tmp/atelier-rc4-restore-server.log
OUT=$ROOT/proofs-rc4-local
mkdir -p "$OUT"
start=$(date +%s%3N)
rm -rf "$RESTORE" "$DUMP" "$LOG"
php "$WPCLI" --path="$SRC" db export "$DUMP" --add-drop-table >/dev/null 2>&1 || exit 1
backup_end=$(date +%s%3N)
sha256sum "$DUMP" > "$OUT/OPS-CL-04-restore-backup.sha256"
php "$WPCLI" --path="$SRC" db query "DROP DATABASE IF EXISTS $DB" >/dev/null 2>&1 || exit 1
php "$WPCLI" --path="$SRC" db query "CREATE DATABASE $DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" >/dev/null 2>&1 || exit 1
cp -a "$SRC" "$RESTORE"
sed -i "s/define( 'DB_NAME', 'atelier_cdc20' );/define( 'DB_NAME', '$DB' );/" "$RESTORE/wp-config.php"
php "$WPCLI" --path="$RESTORE" db import "$DUMP" >/dev/null 2>&1 || exit 1
php "$WPCLI" --path="$RESTORE" option update siteurl http://127.0.0.1:8091 >/dev/null 2>&1
php "$WPCLI" --path="$RESTORE" option update home http://127.0.0.1:8091 >/dev/null 2>&1
php "$WPCLI" --path="$RESTORE" rewrite flush >/dev/null 2>&1
restore_end=$(date +%s%3N)
php -d upload_max_filesize=8M -d post_max_size=12M -S 127.0.0.1:8091 -t "$RESTORE" >"$LOG" 2>&1 &
PID=$!
trap 'kill "$PID" 2>/dev/null || true; php "$WPCLI" --path="$SRC" db query "DROP DATABASE IF EXISTS $DB" >/dev/null 2>&1 || true; rm -rf "$RESTORE" "$DUMP" "$LOG"' EXIT
sleep 2
home=$(curl -sS -o /tmp/rc4-restore-home -w '%{http_code}' http://127.0.0.1:8091/)
topic=$(curl -sS -o /tmp/rc4-restore-topic -w '%{http_code}' http://127.0.0.1:8091/forums/topic/cdc-topic/)
core=$(php "$WPCLI" --path="$RESTORE" core version 2>/dev/null || true)
bbp=$(php "$WPCLI" --path="$RESTORE" plugin get bbpress --field=version 2>/dev/null || true)
pfc=$(php "$WPCLI" --path="$RESTORE" plugin get premium-forum-core --field=version 2>/dev/null || true)
verify_end=$(date +%s%3N)
backup_ms=$((backup_end-start)); restore_ms=$((restore_end-backup_end)); verify_ms=$((verify_end-restore_end))
python3 - "$OUT" "$home" "$topic" "$core" "$bbp" "$pfc" "$backup_ms" "$restore_ms" "$verify_ms" <<'PY'
import json,sys,time
out,home,topic,core,bbp,pfc,backup,restore,verify=sys.argv[1:]
data={'objective':'OPS-CL-04','timestamp_utc':time.strftime('%Y-%m-%dT%H:%M:%SZ',time.gmtime()),'environment':'local-8091','target':'backup puis restauration isolée; accueil et topic HTTP 200; versions conservées; instance détruite','observed':{'restore_http_home':int(home),'restore_http_topic':int(topic),'wordpress':core,'bbpress':bbp,'pfc':pfc,'restore_port':8091,'backup_ms':int(backup),'restore_ms':int(restore),'verify_ms':int(verify)},'exit_code':0 if home=='200' and topic=='200' and core=='7.1' and bbp=='2.6.14' and pfc=='0.4.19' else 1,'status':'PASS' if home=='200' and topic=='200' and core=='7.1' and bbp=='2.6.14' and pfc=='0.4.19' else 'FAIL','blocked_reason':None}
json.dump(data,open(out+'/OPS-CL-04-restore-current.json','w'),indent=2,ensure_ascii=False);json.dump({'objective':'OPS-CL-05','target':'RPO/RTO local mesurés sur le même cycle','observed':{'backup_ms':int(backup),'restore_ms':int(restore),'verify_ms':int(verify),'rpo_ms':int(backup),'rto_ms':int(restore)+int(verify),'scope':'local_only'},'exit_code':data['exit_code'],'status':data['status'],'blocked_reason':None},open(out+'/OPS-CL-05-rpo-rto-current.json','w'),indent=2,ensure_ascii=False);print(json.dumps(data,ensure_ascii=False))
sys.exit(data['exit_code'])
PY
