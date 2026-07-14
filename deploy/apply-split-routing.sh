#!/bin/bash
set -euo pipefail

APP=/var/www/testv
ENV="$APP/.env"

cp /tmp/HappRouting.php "$APP/app/Support/HappRouting.php"
cp /tmp/happ_routing.php "$APP/config/happ_routing.php"

set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "$ENV"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$ENV"
  else
    echo "${key}=${val}" >> "$ENV"
  fi
}

set_env HAPP_ROUTING_MODE split
set_env HAPP_ROUTING_ENABLED true
set_env 'HAPP_ROUTING_NAME' '"AVA Split RU"'
set_env HAPP_ROUTING_LAST_UPDATED 1784060400
set_env HAPP_GEO_USE_BUILTIN false
set_env HAPP_GEO_USE_CHUNK_FILES false
set_env HAPP_SUBSCRIPTION_ANNOUNCE '"Обновите подписку — исправлен роутинг: RU напрямую, заблокированное через VPN"'

bash "$APP/deploy/update-geo-files.sh"

cd "$APP"
php artisan config:clear
php artisan config:cache

echo "--- routing profile ---"
curl -sk -A 'Happ/1.0' -D - "https://127.0.0.1/sub/UbF3E1OAJt4cLqP3" -o /dev/null 2>/dev/null | grep -i routing | head -1
python3 << 'PY'
import base64, json, re, subprocess
hdr = subprocess.check_output(
    "curl -sk -A 'Happ/1.0' -D - https://127.0.0.1/sub/UbF3E1OAJt4cLqP3 -o /dev/null 2>/dev/null",
    shell=True,
).decode()
m = re.search(r'routing: (happ://routing/onadd/[^\r\n]+)', hdr, re.I)
b64 = m.group(1).split('/')[-1]
d = json.loads(base64.b64decode(b64))
print('Name:', d.get('Name'))
print('Geoipurl:', d.get('Geoipurl'))
print('Geositeurl:', d.get('Geositeurl'))
print('DirectSites:', len(d.get('DirectSites', [])))
print('ProxySites:', len(d.get('ProxySites', [])))
print('DomesticDNSIP:', d.get('DomesticDNSIP'))
PY
