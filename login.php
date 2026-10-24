<?php
require __DIR__ . '/app/bootstrap.php';
if(is_user()){header('Location: '.site_url('account.php'));exit;}
$error='';$email=strtolower(trim((string)($_POST['email']??'')));
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$password=(string)($_POST['password']??'');
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$error='ایمیل یا رمز عبور صحیح نیست.';
 else{$q=$pdo->prepare('SELECT id,password_hash,is_active FROM users WHERE email=? LIMIT 1');$q->execute([$email]);$u=$q->fetch();if(!$u||!$u['is_active']||!password_verify($password,$u['password_hash']))$error='ایمیل یا رمز عبور صحیح نیست.';else{session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];flash('success','با موفقیت وارد حساب کاربری شدید.');$next=(string)($_GET['next']??'account.php');if(!preg_match('~^(account.php|checkout.php)$~',$next))$next='account.php';header('Location: '.site_url($next));exit;}}
}
render_header('ورود به حساب'); ?>
<main class="container"><div class="page-title"><h1>ورود به حساب کاربری</h1><p>به حساب ADVERSING خوش آمدید.</p></div><section class="form-card"><h2>ورود</h2><?php if($error): ?><div class="flash error"><?=e($error)?></div><?php endif; ?><form method="post"><?=csrf_field()?><div class="form-group"><label>ایمیل</label><input type="email" name="email" required autocomplete="email" value="<?=e($email)?>"></div><div class="form-group"><label>رمز عبور</label><input type="password" name="password" required autocomplete="current-password"></div><p><button class="btn">ورود به حساب</button></p></form><p><a href="<?=e(site_url('forgot-password.php'))?>">رمز عبور را فراموش کرده‌اید؟</a></p><p>حساب ندارید؟ <a href="<?=e(site_url('register.php'))?>" style="color:var(--gold)">ثبت‌نام کنید</a></p></section></main><?php render_footer(); ?>