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

echo "== 3b. Обязательные прод-настройки в .env =="
# Сайт наружу доступен только по HTTPS (:80 отдаёт 308), поэтому сессионная кука
# обязана иметь флаг secure. Без этого Laravel ставит его по схеме запроса и по
# http:// кука уходила бы открытым текстом. Идемпотентно: правим, а не дублируем.
if grep -q '^APP_ENV=production' .env 2>/dev/null; then
  if grep -q '^SESSION_SECURE_COOKIE=' .env; then
    sed -i 's|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|' .env
  else
    printf '\nSESSION_SECURE_COOKIE=true\n' >> .env
  fi
  echo "SESSION_SECURE_COOKIE=true"
else
  echo "APP_ENV != production — пропущено"
fi

echo "== 3c. DNS-резолвер =="
# glibc по умолчанию шлёт A и AAAA параллельно с одного сокета. На этом хостинге
# ответ на второй запрос теряется, getaddrinfo() ждёт полный timeout (5 c) и только
# потом отвечает: `getent hosts` (только A) — 0.15 c, а curl/PHP — 5.1 c.
# Из-за этого все вызовы api.yookassa.ru падали по connect timeout, возвраты не
# обнаруживались и подписка после возврата продолжала работать.
# single-request-reopen шлёт запросы последовательно на разных сокетах: 5.1 c → 0.15 c.
if ! grep -q 'single-request-reopen' /etc/resolv.conf 2>/dev/null; then
  $SUDO cp -a /etc/resolv.conf "/etc/resolv.conf.bak.$(date +%Y%m%d_%H%M%S)" || true
  echo 'options single-request-reopen timeout:2 attempts:3' | $SUDO tee -a /etc/resolv.conf >/dev/null
  echo "resolv.conf: добавлен single-request-reopen"
else
  echo "resolv.conf: уже настроен"
fi

echo "== 4. Frontend (Vite) =="
if [[ -f package.json ]]; then
  run_as_app_owner "cd '$APP_DIR' && npm install && npm run build"
fi

echo "== 5. Laravel =="
php artisan storage:link --force 2>/dev/null || true
if [[ "${MIGRATE_FRESH:-0}" == "1" ]]; then
  echo "== 5b. БД: migrate:fresh + seed =="
  php artisan migrate:fresh --force --seed
else
  php artisan migrate --force
fi
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
# Каталогам нужен +x (обход), файлам — нет. Раньше здесь был `chmod -R ug+rwx`,
# который вешал бит выполнения и на отслеживаемые .gitignore внутри storage/ —
# после каждого деплоя `git status` на сервере показывал 11 изменённых файлов.
$SUDO find storage bootstrap/cache -type d -exec chmod ug+rwx {} + 2>/dev/null || true
$SUDO find storage bootstrap/cache -type f -exec chmod ug+rw {} + 2>/dev/null || true

echo "== 7. Nginx =="
# Два vhost'а: :80 — только редирект на HTTPS, reality-fallback (127.0.0.1:8443)
# — сам сайт, куда xray отправляет браузеры без Reality-ключа.
# Оба под git: раньше reality-fallback правили руками на сервере и он молча
# разошёлся с репозиторием (не было буферов fastcgi и заголовков безопасности).
if [[ -f deploy/nginx-site-ip.conf ]]; then
  $SUDO cp deploy/nginx-site-ip.conf /etc/nginx/sites-available/testv
  $SUDO ln -sf /etc/nginx/sites-available/testv /etc/nginx/sites-enabled/testv
  $SUDO rm -f /etc/nginx/sites-enabled/default
fi
if [[ -f deploy/nginx-reality-fallback.conf ]]; then
  $SUDO cp deploy/nginx-reality-fallback.conf /etc/nginx/sites-available/reality-fallback
  $SUDO ln -sf /etc/nginx/sites-available/reality-fallback /etc/nginx/sites-enabled/reality-fallback
fi
# nginx -t до reload: при ошибке set -e оборвёт деплой, старый конфиг останется
# в памяти nginx и сайт продолжит работать.
$SUDO nginx -t
$SUDO systemctl reload nginx

echo "== 8. Queue worker =="
if systemctl list-unit-files testv-queue.service >/dev/null 2>&1; then
  $SUDO systemctl restart testv-queue.service || $SUDO systemctl start testv-queue.service
fi

echo "== 9. Inertia SSR (Node) =="
# The SSR bundle (bootstrap/ssr/ssr.js) is cached in the running Node process's memory,
# so it must be restarted on every deploy — a reload alone won't pick up new JS.
if systemctl list-unit-files testv-ssr.service >/dev/null 2>&1; then
  $SUDO systemctl restart testv-ssr.service || $SUDO systemctl start testv-ssr.service
fi

echo "== Готово =="
php artisan about --only=environment 2>/dev/null || true
echo "Откройте: https://avavpn.ru/  (http://${SITE_IP}/ теперь отдаёт 308 на HTTPS)"
