# Деплой testv

## Новый сервер (195.133.198.70)

Workflow: **изменения локально → push в GitHub → на сервере `git pull` + `server-deploy.sh`**.

### Одноразово (bootstrap)

```bash
ssh root@195.133.198.70
git clone https://github.com/oblikfeo/testv.git /var/www/testv
cd /var/www/testv
bash deploy/server-bootstrap.sh
bash deploy/server-deploy.sh --first-run
```

После первого запуска отредактируйте `/var/www/testv/.env` (секреты, YooKassa, `ADMIN_*`, `API_TOKEN`, `SHARED_HY2_URI`, `SHARED_VLESS_URI`, `SHARED_CDN_URI`) и снова:

```bash
cd /var/www/testv && bash deploy/server-deploy.sh
```

### Каждое обновление

```bash
cd /var/www/testv
git pull origin main
bash deploy/server-deploy.sh
```

### Полная пересборка БД (удаляет все данные)

```bash
cd /var/www/testv
git pull origin main
MIGRATE_FRESH=1 bash deploy/server-deploy.sh
```

### Файлы

| Файл | Назначение |
|------|------------|
| `server-bootstrap.sh` | nginx, PHP 8.2, Composer, Node, cron, queue systemd |
| `server-deploy.sh` | pull, composer, npm build, migrate, nginx reload |
| `nginx-site-ip.conf` | vhost :80 — только редирект 308 на HTTPS + ACME-challenge |
| `nginx-security-headers.conf` | заголовки безопасности для vhost'а `reality-fallback` (:8443) — подключается вручную, см. ниже |

### Как отдаётся HTTPS (важно)

Порт **443 на main занят xray** (inbound `reality-443`). Браузер без Reality-ключа
проваливается в fallback на **nginx `127.0.0.1:8443`** (vhost `reality-fallback`,
сертификат `avavpn.ru`, root `/var/www/testv/public`) — именно он отдаёт Laravel.
Наружу nginx слушает только `:80`, и это единственный порт, где проходит
HTTP-01 продление Let's Encrypt.

Поэтому **нельзя** добавлять в nginx блок `listen 443 ssl` — он конфликтует с xray
и уронит VPN-узел. Редирект на HTTPS живёт в `:80`-vhost'е, заголовки — в vhost'е
`reality-fallback`.

### Разовая ручная правка: заголовки на :8443

Файл `reality-fallback` не лежит в репозитории (его TLS-часть завёл certbot),
поэтому `server-deploy.sh` его не трогает. Один раз добавьте include:

```bash
sudo nano /etc/nginx/sites-available/reality-fallback
# внутрь server { ... }:
#   include /var/www/testv/deploy/nginx-security-headers.conf;
sudo nginx -t && sudo systemctl reload nginx
```

### Проверка

```bash
# :80 должен отвечать 308 на https://avavpn.ru/
curl -sI http://avavpn.ru/ | head -3

# сайт по HTTPS + заголовки безопасности
curl -sI https://avavpn.ru/ | grep -iE '^HTTP|x-frame|x-content|strict-transport'

# сессионная кука обязана иметь secure
curl -sI https://avavpn.ru/ | grep -i '^set-cookie: laravel-session' | grep -o secure

# продление сертификата не сломалось редиректом
sudo certbot renew --dry-run

cd /var/www/testv && git log -1 --oneline
systemctl status nginx php8.2-fpm xray testv-queue --no-pager
```
