#!/bin/bash
set -euo pipefail

APP=/var/www/testv
ENV="$APP/.env"

cp /tmp/HappRouting.php "$APP/app/Support/HappRouting.php"
cp /tmp/SharedVpnAccess.php "$APP/app/Support/SharedVpnAccess.php"
cp /tmp/happ_routing.php "$APP/config/happ_routing.php"

set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "$ENV"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$ENV"
  else
    echo "${key}=${val}" >> "$ENV"
  fi
}

set_env HAPP_ROUTING_MODE full_tunnel
set_env HAPP_ROUTING_ENABLED true
set_env 'HAPP_ROUTING_NAME' '"AVA Full VPN"'
set_env HAPP_ROUTING_LAST_UPDATED 20260608c

cd "$APP"
php artisan config:clear
php artisan config:cache

echo "--- subscription ---"
curl -sI -A 'Happ/1.0' 'http://127.0.0.1/sub/UbF3E1OAJt4cLqP3' | grep -i routing
curl -s -A 'Happ/1.0' 'http://127.0.0.1/sub/UbF3E1OAJt4cLqP3' | base64 -d | head -3
