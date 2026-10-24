<?php
require __DIR__.'/app/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: '.site_url());exit;}
verify_csrf();
$email=trim((string)($_POST['email']??''));
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('error','ایمیل معتبر وارد کنید.');}else{
 try{$stmt=$pdo->prepare('INSERT IGNORE INTO newsletter_subscribers (email,created_at) VALUES (?,NOW())');$stmt->execute([$email]);flash('success','ایمیل شما ثبت شد.');}
 catch(Throwable $e){flash('error','ثبت ایمیل فعلاً امکان‌پذیر نیست.');}
}
header('Location: '.site_url());exit;
