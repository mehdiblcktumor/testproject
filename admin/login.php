<?php
require __DIR__.'/../app/bootstrap.php';
if(is_admin()){header('Location: '.site_url('admin/index.php'));exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $username=trim((string)($_POST['username']??''));$password=(string)($_POST['password']??'');
 $stmt=$pdo->prepare('SELECT id,username,password_hash FROM admins WHERE username=?');$stmt->execute([$username]);$admin=$stmt->fetch();
 if($admin&&password_verify($password,$admin['password_hash'])){session_regenerate_id(true);$_SESSION['admin_id']=$admin['id'];$_SESSION['admin_username']=$admin['username'];header('Location: '.site_url('admin/index.php'));exit;}
 $error='نام کاربری یا رمز عبور اشتباه است.';
}
render_header('ورود مدیر');
?><main class="container"><div class="form-card"><h1>ورود به پنل مدیریت</h1><?php if($error): ?><div class="flash error"><?=e($error)?></div><?php endif; ?><form method="post"><?=csrf_field()?><div class="form-group"><label>نام کاربری</label><input name="username" autocomplete="username" required></div><div class="form-group"><label>رمز عبور</label><input type="password" name="password" autocomplete="current-password" required></div><p><button class="btn">ورود امن</button></p></form></div></main><?php render_footer(); ?>
