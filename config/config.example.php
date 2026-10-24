<?php
declare(strict_types=1);

/*
 * Copy this file to config.php and fill in your hosting details.
 * Never put secrets in public JavaScript or commit config.php to a public repository.
 */
return [
    'site_name' => 'ADVERSING',
    'base_url' => '', // e.g. https://example.com; leave empty to infer from request
    'currency' => 'تومان',
    'timezone' => 'Asia/Tehran',
    'db' => [
        'host' => 'localhost',
        'name' => 'YOUR_DATABASE',
        'user' => 'YOUR_DB_USER',
        'pass' => 'YOUR_DB_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'payment_provider' => 'zarinpal', // zarinpal or manual
    'payment' => [
        'merchant_id' => 'YOUR_ZARINPAL_MERCHANT_ID',
        'sandbox' => true,
        'callback_path' => 'payment-callback.php',
    ],
    'mail' => [
        'from' => 'no-reply@example.com',
        'from_name' => 'ADVERSING',
    ],
    'upload_max_bytes' => 5 * 1024 * 1024,
];
