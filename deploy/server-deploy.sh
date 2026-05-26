#!/usr/bin/env bash
# Деплой / обновление после git pull (production, IP 195.133.198.70).
#
# Первый раз (после bootstrap):
#   git clone https://github.com/oblikfeo/testv.git /var/www/testv
#   cd /var/www/testv && bash deploy/server-deploy.sh --first-run
#
# Обновление:
#   cd /var/www/testv && git pull origin main && bash deploy/server-deploy.sh

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/testv}"
REPO_URL="${REPO_URL:-https://github.com/oblikfeo/testv.git}"
BRANCH="${BRANCH:-main}"
SITE_IP="${SITE_IP:-195.133.198.70}"
FIRST_RUN=0

for arg in "$@"; do
  case "$arg" in
    --first-run) FIRST_RUN=1 ;;
  esac
done

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  SUDO="sudo"
else
  SUDO=""
fi

run_as_app_owner() {
  if [[ -d "$APP_DIR/.git" ]]; then
    local owner
    owner="$(stat -c '%U' "$APP_DIR" 2>/dev/null || echo root)"
    if [[ "$owner" != "root" && "$(id -un)" == "root" ]]; then
      sudo -u "$owner" bash -lc "$*"
    else
      bash -lc "$*"
    fi
  else
    bash -lc "$*"
  fi
}

echo "== 1. Репозиторий: $APP_DIR =="
$SUDO mkdir -p "$(dirname "$APP_DIR")"
if [[ ! -d "$APP_DIR/.git" ]]; then
  $SUDO git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
  $SUDO chown -R "${SUDO_USER:-root}:www-data" "$APP_DIR" 2>/dev/null || true
fi

cd "$APP_DIR"
run_as_app_owner "cd '$APP_DIR' && git fetch origin && git checkout '$BRANCH' && git pull origin '$BRANCH'"

echo "== 2. Composer =="
run_as_app_owner "cd '$APP_DIR' && composer install --no-dev --optimize-autoloader --no-interaction"

if [[ ! -f "$APP_DIR/.env" ]]; then
  echo "== 3. .env (первый запуск) =="
  cp .env.example .env
  php artisan key:generate --force
  sed -i "s|^APP_URL=.*|APP_URL=http://${SITE_IP}|" .env
  sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env
  sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env
  touch database/database.sqlite
  chown www-data:www-data database/database.sqlite 2>/dev/null || true
  echo "Создан .env — заполните секреты (YooKassa, API_TOKEN, ADMIN_*, VPN nodes) и перезапустите deploy."
fi

echo "== 4. Frontend (Vite) =="
if [[ -f package.json ]]; then
  run_as_app_owner "cd '$APP_DIR' && npm install && npm run build"
fi

echo "== 5. Laravel =="
php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force
if [[ "$FIRST_RUN" -eq 1 ]]; then
  php artisan db:seed --force --class=PlansSeeder 2>/dev/null || true
fi
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear

if [[ "${APP_ENV:-production}" == "production" ]] || grep -q '^APP_ENV=production' .env 2>/dev/null; then
  php artisan config:cache
fi

echo "== 6. Права =="
$SUDO chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
$SUDO chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "== 7. Nginx =="
if [[ -f deploy/nginx-site-ip.conf ]]; then
  $SUDO cp deploy/nginx-site-ip.conf /etc/nginx/sites-available/testv
  $SUDO ln -sf /etc/nginx/sites-available/testv /etc/nginx/sites-enabled/testv
  $SUDO rm -f /etc/nginx/sites-enabled/default
  $SUDO nginx -t
  $SUDO systemctl reload nginx
fi

echo "== 8. Queue worker =="
if systemctl list-unit-files testv-queue.service >/dev/null 2>&1; then
  $SUDO systemctl restart testv-queue.service || $SUDO systemctl start testv-queue.service
fi

echo "== Готово =="
php artisan about --only=environment 2>/dev/null || true
echo "Откройте: http://${SITE_IP}/"
