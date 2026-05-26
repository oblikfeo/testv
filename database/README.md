# База данных

## Схема (актуальная)

- `users`, `sessions`, `password_reset_tokens`
- `cache`, `jobs`
- `plans`, `subscriptions`, `orders`
- `trial_keys`, `trial_feedback`, `trial_feedback_requests`
- `support_tickets`, `support_messages`
- `settings`, `site_counters`, `landing_source_stats`

Удалены из кода: `sale_keys`, `devices`, `trial_devices`, `pairs`, `subscription_keys`.

## Полный сброс (production — только осознанно)

```bash
cd /var/www/testv
php artisan migrate:fresh --force --seed
```

Или при деплое:

```bash
MIGRATE_FRESH=1 bash deploy/server-deploy.sh
```

После `migrate:fresh` все пользователи, заказы и подписки удаляются. Остаются только тарифы из `PlansSeeder`.
