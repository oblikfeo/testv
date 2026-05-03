<?php

return [
    'shop_id' => env('YOOKASSA_SHOP_ID', ''),
    'secret_key' => env('YOOKASSA_SECRET_KEY', ''),
    // Полный URL или путь. Пусто — route('cabinet.subscription') (страница /cabinet).
    'return_url' => env('YOOKASSA_RETURN_URL', ''),
];
