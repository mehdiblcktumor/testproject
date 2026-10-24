<?php
require __DIR__ . '/app/bootstrap.php';
if (is_user()) { header('Location: '.site_url('account.php')); exit; }
$errors=[]; $name=trim((string)($_POST['full_name']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $phone=trim((string)($_POST['phone']??''));
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $password=(string)($_POST['password']??''); $confirm=(string)($_POST['password_confirm']??'');
 if(mb_strlen($name)<2 || mb_strlen($name)>190) $errors[]='نام و نام خانوادگی را به‌درستی وارد کنید.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($email)>190) $errors[]='ایمیل معتبر وارد کنید.';
 if($phone!=='' && !preg_match('/^[0-9+()\-\s]{8,20}$/',$phone)) $errors[]='شماره تماس معتبر نیست.';
 if(strlen($password)<10) $errors[]='رمز عبور باید حداقل ۱۰ نویسه باشد.';
 if($password!==$confirm) $errors[]='تکرار رمز عبور مطابقت ندارد.';
 if(!$errors){$q=$pdo->prepare('SELECT id FROM users WHERE email=?');$q->execute([$email]);if($q->fetch())$errors[]='با این ایمیل قبلاً حساب ساخته شده است.';}
 if(!$errors){$q=$pdo->prepare('INSERT INTO users(full_name,email,phone,password_hash,created_at) VALUES(?,?,?,?,NOW())');$q->execute([$name,$email,$phone?:null,password_hash($password,PASSWORD_DEFAULT)]);session_regenerate_id(true);$_SESSION['user_id']=(int)$pdo->lastInsertId();flash('success','حساب کاربری شما با موفقیت ساخته شد.');header('Location: '.site_url('account.php'));exit;}
}
render_header('ثبت‌نام'); ?>
<main class="container"><div class="page-title"><h1>ساخت حساب کاربری</h1><p>برای پیگیری سفارش‌ها و مدیریت اطلاعات خود ثبت‌نام کنید.</p></div><section class="form-card"><h2>ثبت‌نام در ADVERSING</h2><?php foreach($errors as $error): ?><div class="flash error"><?=e($error)?></div><?php endforeach; ?><form method="post" autocomplete="on"><?=csrf_field()?><div class="form-grid"><div class="form-group full"><label>نام و نام خانوادگی *</label><input name="full_name" required maxlength="190" autocomplete="name" value="<?=e($name)?>"></div><div class="form-group"><label>ایمیل *</label><input type="email" name="email" required maxlength="190" autocomplete="email" value="<?=e($email)?>"></div><div class="form-group"><label>شماره تماس (اختیاری)</label><input name="phone" inputmode="tel" autocomplete="tel" value="<?=e($phone)?>"></div><div class="form-group"><label>رمز عبور *</label><input type="password" name="password" required minlength="10" autocomplete="new-password"></div><div class="form-group"><label>تکرار رمز عبور *</label><input type="password" name="password_confirm" required minlength="10" autocomplete="new-password"></div></div><p><button class="btn">ساخت حساب</button> <a class="btn secondary" href="<?=e(site_url('login.php'))?>">قبلاً ثبت‌نام کرده‌ام</a></p></form></section></main><?php render_footer(); ?>