<?php

return [
    'enabled' => env('DEMO_MODE', false),
    'tenant_id' => env('DEMO_TENANT_ID', 'demo'),
    'reset_time' => env('DEMO_RESET_TIME', '01:00'),
];
