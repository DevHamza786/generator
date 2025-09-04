<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Generator API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the external generator APIs
    |
    */

    'base_url' => env('GENERATOR_API_BASE_URL', 'https://xyz.net'),

    'generator_ids' => env('GENERATOR_IDS', 'ID492ff2e5'),

    'timeout' => env('GENERATOR_API_TIMEOUT', 30),

    'fetch_interval' => env('GENERATOR_FETCH_INTERVAL', 30), // seconds

    'write_save_delay' => env('GENERATOR_WRITE_SAVE_DELAY', 90), // seconds
];
