<?php
require __DIR__.'/_init.php';
$list=$pdo->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
render_header('اعضای خبرنامه');
?><main class="container"><div class="page-title"><h1>اعضای خبرنامه</h1></div><div class="admin-layout"><aside class="admin-nav"><a href="<?=e(site_url('admin/index.php'))?>">داشبورد</a><a href="<?=e(site_url('admin/products.php'))?>">محصولات</a><a href="<?=e(site_url('admin/orders.php'))?>">سفارش‌ها</a><a href="<?=e(site_url('admin/logout.php'))?>">خروج</a></aside><section class="admin-content"><div class="table-wrap"><table class="table"><thead><tr><th>ایمیل</th><th>تاریخ ثبت</th></tr></thead><tbody><?php foreach($list as $item): ?><tr><td><?=e($item['email'])?></td><td><?=e($item['created_at'])?></td></tr><?php endforeach; ?></tbody></table></div></section></div></main><?php render_footer(); ?>
