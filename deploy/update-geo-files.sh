#!/usr/bin/env bash
# Скачивает geoip.dat и geosite.dat (Loyalsoldier) в public/geo/ для Happ routing.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/testv}"
GEO_DIR="$APP_DIR/public/geo"
BASE_URL="https://github.com/Loyalsoldier/v2ray-rules-dat/releases/latest/download"

mkdir -p "$GEO_DIR"

echo "== geoip.dat =="
curl -fsSL "$BASE_URL/geoip.dat" -o "$GEO_DIR/geoip.dat"

echo "== geosite.dat =="
curl -fsSL "$BASE_URL/geosite.dat" -o "$GEO_DIR/geosite.dat"

chown -R www-data:www-data "$GEO_DIR" 2>/dev/null || true
chmod 644 "$GEO_DIR"/*.dat

ls -lh "$GEO_DIR"
