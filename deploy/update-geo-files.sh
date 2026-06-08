#!/usr/bin/env bash
# Lite geofiles для Happ routing (~500 KB). Полные Loyalsoldier — 30 MB, на мобилках не грузятся.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/testv}"
GEO_DIR="$APP_DIR/public/geo"
BASE_URL="https://raw.githubusercontent.com/DigneZzZ/routing/main/v2ray/happ"

mkdir -p "$GEO_DIR"

echo "== lite geoip.dat =="
curl -fsSL "$BASE_URL/geoip.dat" -o "$GEO_DIR/geoip.dat"

echo "== lite geosite.dat =="
curl -fsSL "$BASE_URL/geosite.dat" -o "$GEO_DIR/geosite.dat"

sha256sum "$GEO_DIR/geoip.dat" | awk '{print $1}' > "$GEO_DIR/geoip.dat.sha256"
sha256sum "$GEO_DIR/geosite.dat" | awk '{print $1}' > "$GEO_DIR/geosite.dat.sha256"

chown -R www-data:www-data "$GEO_DIR" 2>/dev/null || true
chmod 644 "$GEO_DIR"/*

ls -lh "$GEO_DIR"
