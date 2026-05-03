<?php

return [
    /**
     * Чат, куда летят пуши о новых тикетах и ответах пользователей.
     * Бот (TELEGRAM_BOT_TOKEN) должен быть участником этого чата/группы.
     * Если пусто — уведомления админу не отправляются.
     */
    'admin_telegram_chat_id' => env('SUPPORT_ADMIN_TELEGRAM_CHAT_ID'),
];
