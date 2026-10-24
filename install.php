<?php
declare(strict_types=1);

if (getenv('ALLOW_INSTALL') !== '1') {
    http_response_code(403);
    exit('نصب در حال حاضر غیرفعال است. برای نصب اولیه، متغیر ALLOW_INSTALL را موقتاً روی 1 قرار دهید.');
}
$configPath=__DIR__.'/config/config.php';
if(!is_file($configPath)){http_response_code(500);exit('ابتدا config/config.example.php را به config/config.php کپی و تنظیم کنید.');}
$config=require $configPath;
try{$pdo=new PDO(sprintf('mysql:host=%s;dbname=%s;charset=%s',$config['db']['host'],$config['db']['name'],$config['db']['charset']??'utf8mb4'),$config['db']['user'],$config['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
catch(Throwable $e){exit('اتصال دیتابیس برقرار نشد؛ تنظیمات را بررسی کنید.');}
$message='';$done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
 $admin=trim((string)($_POST['username']??''));$pass=(string)($_POST['password']??'');
 if(strlen($admin)<4||strlen($pass)<12){$message='نام کاربری حداقل ۴ و رمز عبور حداقل ۱۲ نویسه باشد.';}
 else{
  $sql=[
  "CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(190) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, phone VARCHAR(40) NULL, password_hash VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, INDEX idx_users_created (created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS admins (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(80) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS products (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(190) NOT NULL, slug VARCHAR(220) NULL, category VARCHAR(100) NOT NULL DEFAULT 'عمومی', description TEXT NULL, price BIGINT UNSIGNED NOT NULL DEFAULT 0, image VARCHAR(255) NULL, tirages TEXT NULL, catalog_key VARCHAR(64) NULL UNIQUE, is_featured TINYINT(1) NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL, INDEX idx_products_active (is_active), INDEX idx_products_category (category)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS orders (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, public_code CHAR(24) NOT NULL UNIQUE, customer_name VARCHAR(190) NOT NULL, phone VARCHAR(40) NOT NULL, email VARCHAR(190) NULL, address TEXT NOT NULL, notes TEXT NULL, total_amount BIGINT UNSIGNED NOT NULL, status VARCHAR(40) NOT NULL DEFAULT 'pending', payment_status VARCHAR(40) NOT NULL DEFAULT 'unpaid', payment_ref VARCHAR(190) NULL, created_at DATETIME NOT NULL, INDEX idx_orders_created (created_at), INDEX idx_orders_status (status), INDEX idx_orders_user (user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS order_items (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, product_id INT UNSIGNED NULL, product_name VARCHAR(190) NOT NULL, unit_price BIGINT UNSIGNED NOT NULL, quantity INT UNSIGNED NOT NULL, line_total BIGINT UNSIGNED NOT NULL, CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS newsletter_subscribers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL UNIQUE, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS print_options (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NULL, option_group VARCHAR(100) NOT NULL, option_name VARCHAR(150) NOT NULL, price_delta BIGINT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL, INDEX idx_print_options_product(product_id), INDEX idx_print_options_active(is_active)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS coupons (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(80) NOT NULL UNIQUE, discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent', discount_value BIGINT UNSIGNED NOT NULL, min_order BIGINT UNSIGNED NOT NULL DEFAULT 0, max_uses INT UNSIGNED NULL, used_count INT UNSIGNED NOT NULL DEFAULT 0, starts_at DATETIME NULL, expires_at DATETIME NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS banners (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(190) NOT NULL, subtitle TEXT NULL, image VARCHAR(255) NULL, link_url VARCHAR(500) NULL, sort_order INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS password_resets (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at DATETIME NOT NULL, INDEX idx_password_resets_user(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS order_files (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size BIGINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, INDEX idx_order_files_order(order_id), CONSTRAINT fk_order_files_order FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS payment_transactions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, provider VARCHAR(40) NOT NULL, authority VARCHAR(100) NULL, amount_rial BIGINT UNSIGNED NOT NULL, status VARCHAR(40) NOT NULL DEFAULT 'created', reference_id VARCHAR(100) NULL, raw_response TEXT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NULL, INDEX idx_payment_order(order_id), INDEX idx_payment_authority(authority)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  ];
  try{foreach($sql as $s)$pdo->exec($s);
  // Safe schema upgrades for installations created by earlier Printo versions.
  foreach ([
   "ALTER TABLE users ADD COLUMN reset_requested_at DATETIME NULL",
   "ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(80) NULL",
   "ALTER TABLE orders ADD COLUMN discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0",
   "ALTER TABLE orders ADD COLUMN shipping_amount BIGINT UNSIGNED NOT NULL DEFAULT 0",
   "ALTER TABLE order_items ADD COLUMN customization_json TEXT NULL"
  ] as $migration) { try { $pdo->exec($migration); } catch (Throwable $ignored) {} }
  // Catalog migration for Printo v3. Category-specific duplicates are intentional.
  foreach (["ALTER TABLE products ADD COLUMN tirages TEXT NULL", "ALTER TABLE products ADD COLUMN catalog_key VARCHAR(64) NULL", "ALTER TABLE products ADD UNIQUE KEY uq_products_catalog_key(catalog_key)"] as $migration) { try { $pdo->exec($migration); } catch (Throwable $ignored) {} }
  $catalog = require __DIR__ . '/catalog.php';
  $seed = $pdo->prepare('INSERT IGNORE INTO products (name,slug,category,description,price,image,tirages,catalog_key,is_featured,is_active,created_at) VALUES (?,?,?,?,0,NULL,?,?,0,1,NOW())');
  foreach ($catalog as $cat => $rows) {
   foreach ($rows as $i => $row) {
    $name = (string)$row[0]; $tirages = (string)$row[1];
    $key = hash('sha256', $cat . "\0" . $i . "\0" . $name . "\0" . $tirages);
    $seed->execute([$name, null, $cat, 'تیراژ موجود: ' . $tirages . ' عددی', $tirages, $key]);
   }
  }
  $stmt=$pdo->prepare('SELECT COUNT(*) FROM admins WHERE username=?');$stmt->execute([$admin]);if((int)$stmt->fetchColumn()>0){$message='این نام کاربری از قبل وجود دارد.';}else{$stmt=$pdo->prepare('INSERT INTO admins(username,password_hash,created_at) VALUES(?,?,NOW())');$stmt->execute([$admin,password_hash($pass,PASSWORD_DEFAULT)]);$done=true;$message='نصب دیتابیس و ساخت مدیر با موفقیت انجام شد. اکنون install.php را حذف کنید.';}}
  catch(Throwable $e){$message='نصب با خطا روبه‌رو شد. دسترسی‌های دیتابیس را بررسی کنید.';}
 }
}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>نصب ADVERSING</title><link rel="stylesheet" href="assets/style.css"></head><body><main class="container"><div class="form-card"><h1>نصب ADVERSING</h1><p>این صفحه را فقط یک بار اجرا کنید و پس از نصب حذف کنید.</p><?php if($message): ?><div class="flash <?=$done?'':'error'?>"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif; ?><?php if(!$done): ?><form method="post"><div class="form-group"><label>نام کاربری مدیر</label><input name="username" required minlength="4" autocomplete="username"></div><div class="form-group"><label>رمز عبور مدیر (حداقل ۱۲ نویسه)</label><input type="password" name="password" required minlength="12" autocomplete="new-password"></div><p><button class="btn">ساخت جداول و مدیر</button></p></form><?php else: ?><a class="btn" href="admin/login.php">ورود به پنل مدیریت</a><?php endif; ?></div></main></body></html>
