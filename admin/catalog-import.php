<?php
require __DIR__.'/_init.php';
$message='';$added=0;
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 try{
  foreach(["ALTER TABLE products ADD COLUMN tirages TEXT NULL","ALTER TABLE products ADD COLUMN catalog_key VARCHAR(64) NULL","ALTER TABLE products ADD UNIQUE KEY uq_products_catalog_key(catalog_key)"] as $migration){try{$pdo->exec($migration);}catch(Throwable $ignored){}}
  $catalog=require __DIR__.'/../catalog.php';
  $seed=$pdo->prepare('INSERT IGNORE INTO products (name,slug,category,description,price,image,tirages,catalog_key,is_featured,is_active,created_at) VALUES (?,?,?,?,0,NULL,?,?,0,1,NOW())');
  foreach($catalog as $cat=>$rows){foreach($rows as $i=>$row){$name=(string)$row[0];$tirages=(string)$row[1];$key=hash('sha256',$cat."\0".$i."\0".$name."\0".$tirages);$seed->execute([$name,null,$cat,'تیراژ موجود: '.$tirages.' عددی',$tirages,$key]);$added+=$seed->rowCount();}}
  $message='همگام‌سازی کاتالوگ انجام شد. محصولات جدید اضافه شدند و مواردی که قبلاً با شناسه کاتالوگ وارد شده بودند، تکرار نشدند. تعداد ردیف‌های جدید: '.number_format($added).'.';
 }catch(Throwable $e){$message='همگام‌سازی انجام نشد. ساختار دیتابیس و دسترسی کاربر دیتابیس را بررسی کنید.';}
}
render_header('همگام‌سازی کاتالوگ ADVERSING');
?><main class="container"><div class="page-title"><h1>همگام‌سازی کاتالوگ ADVERSING</h1><p>تمام دسته‌بندی‌ها و تیراژهای موجود در فایل کاتالوگ را به پایگاه داده اضافه کنید.</p></div><div class="form-card"><p>این عملیات محصولات دسته‌بندی‌شده را وارد می‌کند، تیراژها را نگه می‌دارد و محصولات مشابه در دسته‌های مختلف را حذف یا ادغام نمی‌کند. قیمت محصولات بدون قیمت به‌صورت «استعلام قیمت» نمایش داده می‌شود.</p><?php if($message): ?><div class="flash success"><?=e($message)?></div><?php endif; ?><form method="post"><?=csrf_field()?><button class="btn" type="submit">همگام‌سازی محصولات</button></form><p class="notice">این عملیات را پس از بارگذاری نسخه جدید، یک بار از پنل مدیریت اجرا کنید.</p><a href="<?=e(site_url('admin/index.php'))?>">بازگشت به داشبورد</a></div></main><?php render_footer(); ?>
