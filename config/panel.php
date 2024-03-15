<?php

return [
    'brand' => env('PANEL_BRAND', 'Cipi Control Panel'),
    'website' => env('PANEL_WEBSITE', 'https://panel.sh'),
    'favicon' => env('PANEL_FAVICON', '/favicon.ico'),
    'path' => env('PANEL_PATH', 'panel'),
    'logo' => [
        'url' => env('PANEL_LOGO', '/logo.png'),
        'height' => env('PANEL_LOGO_HEIGHT', '45px'),
    ],
    'admin' => [
        'name' => env('PANEL_ADMIN_NAME', 'John Doe'),
        'email' => env('PANEL_ADMIN_EMAIL', 'john.doe@cipi.sh'),
        'password' => env('PANEL_ADMIN_PASSWORD', 'C1p1P4n3!#4.sh'),
    ],
    'force_2fa' => env('PANEL_FORCE_2FA', false),
    'ip_dns_mapping' => env('PANEL_IP_DNS_MAPPING', '.nip.io'),
];
