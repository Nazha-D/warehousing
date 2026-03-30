<?php
return [
    'url'   => env('EXCHANGE_RATE_URL', 'https://api.exchangerate.host/live'),
    'key'   => env('EXCHANGE_RATE_KEY'),
    'base'  => env('EXCHANGE_RATE_BASE', 'USD'),
];
