#!/usr/bin/env bash
# Что делать на сервере Ubuntu (SSH под root или sudo), чтобы avavpn.ru показывал Laravel из GitHub, а не HTML из /var/www/html.
#
# Репозиторий: https://github.com/oblikfeo/testv.git
# Каталог приложения: /var/www/avavpn  (можно изменить — тогда поправьте root в nginx-avavpn.ru.conf)

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/avavpn}"
REPO_URL="${REPO_URL:-https://github.com/oblikfeo/testv.git}"

echo "== 1. Убираем статический сайт из /var/www/html (бэкап, не удаляем навсегда) =="
BACKUP="/var/www/_static_backup_$(date +%Y%m%d_%H%M%S)"
if [ -d /var/www/html ] && [ "$(ls -A /var/www/html 2>/dev/null)" ]; then
  sudo mkdir -p "$BACKUP"
  sudo mv /var/www/html "$BACKUP/html_old"
  sudo mkdir -p /var/www/html
  echo "Старый html перенесён в: $BACKUP/html_old"
fi

echo "== 2. Клон / git pull =="
sudo mkdir -p "$(dirname "$APP_DIR")"
if [ ! -d "$APP_DIR/.git" ]; then
  sudo git clone "$REPO_URL" "$APP_DIR"
  sudo chown -R "$USER:www-data" "$APP_DIR"
else
  cd "$APP_DIR"
  git pull origin main
fi

cd "$APP_DIR"

echo "== 3. Composer (только на сервере) =="
composer install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
  echo "Создан .env из примера. Проверьте APP_URL, БД и запустите: php artisan migrate --force"
fi

echo "== 4. Кэш и права =="
php artisan view:clear
php artisan cache:clear
php artisan config:clear
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

echo "== 5. Nginx =="
echo "Скопируйте deploy/nginx-avavpn.ru.conf в sites-available и поправьте fastcgi_pass под ваш PHP."
echo "Затем: sudo nginx -t && sudo systemctl reload nginx"
echo "Готово. Откройте https://avavpn.ru (или http) — должна быть главная с лендингом, не стартовая Laravel из старого welcome."
