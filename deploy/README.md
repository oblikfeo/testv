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

После первого запуска отредактируйте `/var/www/testv/.env` (секреты, YooKassa, `ADMIN_*`, `API_TOKEN`, узлы VPN) и снова:

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
| `nginx-site-ip.conf` | vhost по IP (`/var/www/testv/public`) |
| `nginx-avavpn.ru.conf` | старый конфиг под домен avavpn.ru |

### Проверка

```bash
curl -sI http://195.133.198.70/ | head
cd /var/www/testv && git log -1 --oneline
systemctl status nginx php8.2-fpm testv-queue --no-pager
```
