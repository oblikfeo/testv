#!/usr/bin/env bash
# Одноразовая подготовка Ubuntu 22.04 (nginx, PHP 8.2, Composer, Node).
# Запуск на сервере из клона репозитория:
#   bash deploy/server-bootstrap.sh

set -euo pipefail

export DEBIAN_FRONTEND=noninteractive

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  echo "Запустите от root: sudo bash deploy/server-bootstrap.sh"
  exit 1
fi

echo "== apt update =="
apt-get update -y

echo "== базовые пакеты =="
apt-get install -y nginx git curl unzip ca-certificates gnupg software-properties-common

if ! dpkg -l | grep -q php8.2-fpm; then
  echo "== PHP 8.2 (ondrej PPA) =="
  add-apt-repository -y ppa:ondrej/php
  apt-get update -y
  apt-get install -y \
    php8.2-fpm php8.2-cli php8.2-common php8.2-sqlite3 php8.2-mysql \
    php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath \
    php8.2-intl php8.2-gd php8.2-readline
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "== Composer =="
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

if ! command -v node >/dev/null 2>&1 || [[ "$(node -v 2>/dev/null || echo v0)" < "v18" ]]; then
  echo "== Node.js 20 =="
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

echo "== systemd: nginx + php-fpm =="
systemctl enable nginx php8.2-fpm
systemctl start nginx php8.2-fpm

echo "== cron: Laravel scheduler =="
CRON_LINE='* * * * * cd /var/www/testv && php artisan schedule:run >> /dev/null 2>&1'
(crontab -l 2>/dev/null | grep -v 'artisan schedule:run' || true; echo "$CRON_LINE") | crontab -

echo "== systemd: queue worker =="
cat >/etc/systemd/system/testv-queue.service <<'UNIT'
[Unit]
Description=testv Laravel queue worker
After=network.target php8.2-fpm.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=3
WorkingDirectory=/var/www/testv
ExecStart=/usr/bin/php artisan queue:work database --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
UNIT

systemctl daemon-reload
systemctl enable testv-queue.service

echo "Bootstrap готов. Далее: клон в /var/www/testv и bash deploy/server-deploy.sh"
