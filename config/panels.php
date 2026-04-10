<?php

return [

    'timeout' => (int) env('PANEL_HTTP_TIMEOUT', 30),

    'connect_timeout' => (int) env('PANEL_HTTP_CONNECT_TIMEOUT', 10),

    'verify_ssl' => filter_var(env('PANEL_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),

    'retry' => (int) env('PANEL_HTTP_RETRY', 2),

    'login_path' => env('PANEL_LOGIN_PATH', '/login'),

    /*
     * 3x-ui: обычно POST с inbound id и settings (JSON-строка с clients).
     * При смене версии панели скорректируйте путь/тело в ThreeXUiClient.
     */
    'add_client_path' => env('PANEL_ADD_CLIENT_PATH', '/panel/api/inbounds/addClient'),

    'update_client_path' => env('PANEL_UPDATE_CLIENT_PATH', '/panel/api/inbounds/updateClient'),

    'client_traffic_path' => env('PANEL_CLIENT_TRAFFIC_PATH', '/panel/api/inbounds/getClientTraffics'),

];
