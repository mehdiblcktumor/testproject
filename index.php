<?php
require __DIR__ . '/app/bootstrap.php';
$banners = $pdo->query('SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order,id DESC')->fetchAll();
$featured = $pdo->query('SELECT * FROM products WHERE is_active=1 ORDER BY is_featured DESC, created_at DESC LIMIT 8')->fetchAll();
$cats = $pdo->query('SELECT DISTINCT category FROM products WHERE is_active=1 ORDER BY category LIMIT 8')->fetchAll(PDO::FETCH_COLUMN);
render_header('چاپ حرفه‌ای برای برند شما');
?>
<main>
<?php if($banners): ?><section class="container" style="padding-top:18px"><div class="product-grid"><?php foreach($banners as $b): ?><a class="promo-card" href="<?=e($b['link_url']?:site_url('products.php'))?>"><?php if($b['image']): ?><img src="<?=e(product_image($b['image']))?>" alt="<?=e($b['title'])?>" style="width:100%;max-height:260px;object-fit:cover;border-radius:14px"><?php endif; ?><h2><?=e($b['title'])?></h2><p><?=e($b['subtitle'])?></p></a><?php endforeach; ?></div></section><?php endif; ?>
<section class="hero">
  <div class="hero-copy"><span class="eyebrow">PRINT BETTER. STAND OUT.</span><h1><?=e(setting('hero_title','چاپ حرفه‌ای برای برندهای خاص شما'))?></h1><p><?=e(setting('hero_subtitle','از کارت ویزیت تا بسته‌بندی اختصاصی، با کیفیتی که هویت برند شما را ماندگار می‌کند.'))?></p><a class="btn" href="<?=e(site_url('products.php'))?>">مشاهده محصولات ←</a><a class="btn secondary" href="<?=e(site_url('contact.php'))?>">مشاوره سفارش</a></div>
  <div class="hero-art"><img src="<?=e(site_url('assets/product-placeholder.svg'))?>" alt="محصولات چاپی ADVERSING"></div>
</section>
<div class="container">
  <div class="benefits"><div class="benefit"><b>◇</b>چاپ باکیفیت<span>دقت در جزئیات</span></div><div class="benefit"><b>♧</b>پشتیبانی حرفه‌ای<span>همراه شما تا تحویل</span></div><div class="benefit"><b>↗</b>قیمت شفاف<span>تعرفه مشخص</span></div><div class="benefit"><b>▣</b>سفارش آنلاین<span>ساده و سریع</span></div></div>
  <section class="section"><div class="section-head"><h2>دسته‌بندی محصولات</h2><a href="<?=e(site_url('products.php'))?>">مشاهده همه ←</a></div>
    <div class="categories">
    <?php $defaults=['کارت ویزیت','تراکت و بروشور','سربرگ و اداری','لیبل و برچسب','بسته‌بندی','هدایای تبلیغاتی']; foreach(array_unique(array_merge($defaults,$cats)) as $cat): ?>
      <a class="category" href="<?=e(site_url('products.php?category='.urlencode($cat)))?>"><img src="<?=e(site_url('assets/product-placeholder.svg'))?>" alt=""><strong><?=e($cat)?></strong></a>
    <?php endforeach; ?>
    </div>
  </section>
  <section class="section"><div class="section-head"><h2>محصولات منتخب</h2><a href="<?=e(site_url('products.php'))?>">مشاهده همه ←</a></div>
    <?php if($featured): ?><div class="product-grid"><?php foreach($featured as $p): ?><article class="product-card"><a href="<?=e(site_url('product.php?id='.$p['id']))?>"><img src="<?=e(product_image($p['image']))?>" alt="<?=e($p['name'])?>"></a><div class="product-info"><h3><?=e($p['name'])?></h3><p><?=e(mb_strimwidth((string)$p['description'],0,85,'…','UTF-8'))?></p><div class="product-actions"><span class="price"><?=money($p['price'])?></span><a class="small-btn" href="<?=e(site_url('product.php?id='.$p['id']))?>">جزئیات</a></div></div></article><?php endforeach; ?></div>
    <?php else: ?><div class="empty">هنوز محصولی اضافه نشده است. از پنل مدیریت اولین محصول را ایجاد کنید.</div><?php endif; ?>
  </section>
  <section class="section promo"><div class="promo-card"><span class="eyebrow">PRINT FOR YOUR BRAND</span><h3>جزئیات کوچک، تفاوت بزرگ</h3><p>برای هویت بصری یکپارچه، محصولات چاپی خود را با دقت انتخاب کنید.</p><a class="btn" href="<?=e(site_url('products.php'))?>">شروع خرید</a></div><div class="promo-card"><span class="eyebrow">CUSTOM PRINT</span><h3>سفارش اختصاصی دارید؟</h3><p>برای تیراژ، مشخصات یا خدمات خاص با ما در تماس باشید.</p><a class="btn secondary" href="<?=e(site_url('contact.php'))?>">درخواست مشاوره</a></div></section>
  <section class="section"><div class="section-head"><h2>فرآیند سفارش آنلاین</h2><span class="eyebrow">۴ قدم ساده</span></div><div class="steps"><div class="step"><b>۱</b><strong>انتخاب محصول</strong><span>محصول و مشخصات موردنظر را انتخاب کنید.</span></div><div class="step"><b>۲</b><strong>ثبت سفارش</strong><span>تعداد و اطلاعات تحویل را وارد کنید.</span></div><div class="step"><b>۳</b><strong>تأیید پرداخت</strong><span>پس از اتصال درگاه، پرداخت امن انجام می‌شود.</span></div><div class="step"><b>۴</b><strong>آماده‌سازی</strong><span>وضعیت سفارش را از کد پیگیری ببینید.</span></div></div></section>
</div>
<section class="testimonials"><div class="container"><div class="quote"><span>★★★★★</span><p>«کیفیت چاپ و توجه به جزئیات، بخش مهمی از تجربه برند ماست.»</p><b>تجربه مشتریان</b></div><div class="quote"><span>★★★★★</span><p>«فرآیند سفارش آنلاین باید روشن، ساده و قابل پیگیری باشد.»</p><b>تجربه سفارش</b></div><div class="quote"><span>★★★★★</span><p>«برای سفارش‌های اختصاصی، ارتباط شفاف و پشتیبانی اهمیت دارد.»</p><b>خدمات مشتریان</b></div></div></section>
</main>
<?php render_footer(); ?>
