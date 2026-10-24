<?php
declare(strict_types=1);
$env = static function (array $names, string $default = ''): string {
    foreach ($names as $name) { $value = getenv($name); if ($value !== false && $value !== '') return $value; }
    return $default;
};
$baseUrl = rtrim($env(['APP_URL']), '/');
$dbHost = $env(['DB_HOST', 'MYSQLHOST', 'MYSQL_HOST']);
$dbPort = $env(['DB_PORT', 'MYSQLPORT', 'MYSQL_PORT'], '3306');
// The app's existing PDO DSN accepts port as part of the host string.
if ($dbHost !== '' && strpos($dbHost, ';port=') === false) $dbHost .= ';port=' . $dbPort;
$config = [
 'site_name' => $env(['SITE_NAME'], 'ADVERSING'),
 'base_url' => $baseUrl,
 'currency' => 'تومان', 'timezone' => 'Asia/Tehran',
 'db' => ['host' => $dbHost, 'name' => $env(['DB_NAME','MYSQLDATABASE','MYSQL_DATABASE']), 'user' => $env(['DB_USER','MYSQLUSER','MYSQL_USER']), 'pass' => $env(['DB_PASSWORD','MYSQLPASSWORD','MYSQL_PASSWORD']), 'charset' => 'utf8mb4'],
 'payment_provider' => $env(['PAYMENT_PROVIDER'], 'manual'),
 'payment' => ['merchant_id' => $env(['ZARINPAL_MERCHANT_ID']), 'sandbox' => filter_var($env(['ZARINPAL_SANDBOX'], 'true'), FILTER_VALIDATE_BOOLEAN), 'callback_path' => 'payment-callback.php'],
 'mail' => ['from' => $env(['MAIL_FROM'], 'no-reply@example.com'), 'from_name' => $env(['MAIL_FROM_NAME'], 'ADVERSING')],
 'upload_max_bytes' => 5 * 1024 * 1024,
];
$path='/var/www/html/config/config.php';
if (!is_dir(dirname($path))) mkdir(dirname($path),0775,true);
if (file_put_contents($path, "<?php\ndeclare(strict_types=1);\nreturn ".var_export($config,true).";\n", LOCK_EX) === false) { fwrite(STDERR,"Unable to write config/config.php\n"); exit(1); }
