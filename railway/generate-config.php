<?php
declare(strict_types=1);

$env = static function (array $names, string $default = ''): string {
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') return $value;
    }
    return $default;
};
$domain = $env(['RAILWAY_PUBLIC_DOMAIN']);
$baseUrl = $env(['APP_URL'], $domain !== '' ? 'https://' . $domain : '');
$config = [
    'site_name' => $env(['SITE_NAME'], 'ADVERSING'),
    'base_url' => $baseUrl,
    'currency' => 'تومان',
    'timezone' => 'Asia/Tehran',
    'db' => [
        'host' => $env(['MYSQLHOST', 'DB_HOST', 'MYSQL_HOST'], '127.0.0.1'),
        'name' => $env(['MYSQLDATABASE', 'DB_NAME', 'MYSQL_DATABASE']),
        'user' => $env(['MYSQLUSER', 'DB_USER', 'MYSQL_USER']),
        'pass' => $env(['MYSQLPASSWORD', 'DB_PASSWORD', 'MYSQL_PASSWORD']),
        'charset' => 'utf8mb4',
    ],
    'payment_provider' => $env(['PAYMENT_PROVIDER'], 'manual'),
    'payment' => [
        'merchant_id' => $env(['ZARINPAL_MERCHANT_ID']),
        'sandbox' => filter_var($env(['ZARINPAL_SANDBOX'], 'true'), FILTER_VALIDATE_BOOLEAN),
        'callback_path' => 'payment-callback.php',
    ],
    'mail' => [
        'from' => $env(['MAIL_FROM'], 'no-reply@example.com'),
        'from_name' => $env(['MAIL_FROM_NAME'], 'ADVERSING'),
    ],
    'upload_max_bytes' => 5 * 1024 * 1024,
];
$path = '/var/www/html/config/config.php';
if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
$result = file_put_contents($path, "<?php\ndeclare(strict_types=1);\nreturn " . var_export($config, true) . ";\n", LOCK_EX);
if ($result === false) {
    fwrite(STDERR, "Unable to write config/config.php\n");
    exit(1);
}
