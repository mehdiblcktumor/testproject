<?php
require __DIR__ . '/app/bootstrap.php';
$code=preg_replace('/[^a-f0-9]/','',strtolower((string)($_GET['code']??'')));
$q=$pdo->prepare('SELECT * FROM orders WHERE public_code=?');$q->execute([$code]);$order=$q->fetch();
render_header('پیگیری سفارش');
?>
<main class="container"><div class="page-title"><h1>پیگیری سفارش</h1></div><?php if(!$order): ?><div class="empty">سفارش پیدا نشد. کد پیگیری را بررسی کنید.</div><?php else: ?><div class="form-card"><h2>سفارش ثبت شد</h2><p>کد پیگیری: <strong><?=e(substr($order['public_code'],0,8))?>…</strong></p><p>نام گیرنده: <?=e($order['customer_name'])?></p><p>مبلغ سفارش: <span class="price"><?=money($order['total_amount'])?></span></p><p>وضعیت سفارش: <span class="status"><?=e($order['status']==='pending'?'در انتظار بررسی':$order['status'])?></span></p><p>وضعیت پرداخت: <span class="status"><?=e($order['payment_status']==='unpaid'?'پرداخت نشده':$order['payment_status'])?></span></p><div class="notice">این سفارش پرداخت نشده است. برای پرداخت آنلاین، اتصال درگاه واقعی باید توسط مدیر سایت تکمیل شود.</div><a class="btn" href="<?=e(site_url('products.php'))?>">بازگشت به فروشگاه</a></div><?php endif; ?></main>
<?php render_footer(); ?>
