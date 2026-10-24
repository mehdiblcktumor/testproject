<?php
declare(strict_types=1);

function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function site_url(string $path = ''): string {
    global $config;
    $base = rtrim((string)($config['base_url'] ?? ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . '/' . ltrim($path, '/');
}
function money(int|float|string $amount): string {
    return number_format((float)$amount) . ' تومان';
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    $sent = (string)($_POST['_csrf'] ?? '');
    if ($sent === '' || !hash_equals((string)($_SESSION['_csrf'] ?? ''), $sent)) {
        http_response_code(419);
        exit('درخواست معتبر نیست؛ صفحه را تازه‌سازی کنید.');
    }
}
function flash(string $type, string $message): void {
    $_SESSION['_flash'] = ['type'=>$type, 'message'=>$message];
}
function get_flash(): ?array {
    $f = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $f;
}
function cart_count(): int {
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}
function current_user(): ?array {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $q = $pdo->prepare('SELECT id, full_name, email, phone, created_at FROM users WHERE id = ? AND is_active = 1');
    $q->execute([(int)$_SESSION['user_id']]);
    $user = $q->fetch();
    if (!$user) unset($_SESSION['user_id']);
    return $user ?: null;
}
function is_user(): bool { return !empty($_SESSION['user_id']); }
function require_user(): void {
    if (!is_user()) { flash('error', 'برای مشاهده این صفحه ابتدا وارد حساب کاربری شوید.'); header('Location: ' . site_url('login.php')); exit; }
}
function is_admin(): bool {
    return !empty($_SESSION['admin_id']);
}
function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . site_url('admin/login.php'));
        exit;
    }
}
function product_image(?string $image): string {
    if ($image && preg_match('~^[a-zA-Z0-9._-]+$~', $image)) return site_url('storage/uploads/' . $image);
    return site_url('assets/product-placeholder.svg');
}
function get_product(PDO $pdo, int $id): ?array {
    $q = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $q->execute([$id]);
    $p = $q->fetch();
    return $p ?: null;
}
function get_cart_items(PDO $pdo): array {
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) return [];
    $ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn($v) => $v > 0));
    if (!$ids) return [];
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 AND id IN ($marks)");
    $q->execute($ids);
    $items = [];
    foreach ($q->fetchAll() as $p) {
        $qty = max(1, min(99, (int)($cart[$p['id']] ?? 1)));
        $opts = $_SESSION['cart_options'][$p['id']] ?? [];
        $selected = [];
        if (is_array($opts) && $opts) {
            $optIds = array_values(array_filter(array_map('intval', array_keys($opts)), fn($v)=>$v>0));
            if ($optIds) {
                $om=implode(',',array_fill(0,count($optIds),'?'));
                $oq=$pdo->prepare("SELECT id,option_group,option_name,price_delta FROM print_options WHERE is_active=1 AND (product_id IS NULL OR product_id=?) AND id IN ($om)");
                $oq->execute(array_merge([(int)$p['id']],$optIds));
                foreach($oq->fetchAll() as $op) { $selected[]=$op; }
            }
        }
        $delta=array_sum(array_map(fn($op)=>(int)$op['price_delta'],$selected));
        $p['selected_options']=$selected;
        $p['unit_price']=max(0,(int)$p['price']+$delta);
        $p['qty'] = $qty;
        $p['line_total'] = $qty * $p['unit_price'];
        $items[] = $p;
    }
    return $items;
}
function cart_total(array $items): int {
    return array_sum(array_map(fn($p) => (int)$p['line_total'], $items));
}
function render_header(string $title = ''): void {
    global $config;
    $flash = get_flash();
    $site = e($config['site_name'] ?? 'ADVERSING');
    echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="theme-color" content="#050a12"><title>' . e($title ? $title . ' | ' : '') . $site . '</title>';
    echo '<link rel="stylesheet" href="' . e(site_url('assets/style.css')) . '"></head><body>';
    echo '<div class="topline">چاپ حرفه‌ای، از ایده تا واقعیت <span>پشتیبانی سفارش‌ها</span></div>';
    echo '<header class="site-header"><a class="brand brand-image" href="' . e(site_url()) . '"><img src="' . e(site_url('assets/adversing-logo.jpg')) . '" alt="ADVERSING Advertising Agency"></a>';
    echo '<nav><a href="' . e(site_url()) . '">خانه</a><a href="' . e(site_url('products.php')) . '">محصولات</a><a href="' . e(site_url('products.php?category=کارت ویزیت')) . '">کارت ویزیت</a><a href="' . e(site_url('products.php?category=انواع%20جعبه%20و%20بسته‌بندی')) . '">بسته‌بندی</a><a href="' . e(site_url('about.php')) . '">درباره ما</a></nav>';
    echo '<form class="search" action="' . e(site_url('products.php')) . '"><input name="q" placeholder="جست‌وجوی محصولات چاپی..." value="' . e($_GET['q'] ?? '') . '"><button aria-label="جست‌وجو">⌕</button></form>';
    echo '<div class="head-actions"><a href="' . e(site_url(is_user() ? 'account.php' : 'login.php')) . '" class="cart-link">' . (is_user() ? 'حساب کاربری' : 'ورود / ثبت‌نام') . '</a> <a href="' . e(site_url('cart.php')) . '" class="cart-link">سبد <b>' . cart_count() . '</b></a></div><button class="mobile-menu" type="button" data-menu-toggle>☰</button></header>';
    if ($flash) echo '<div class="flash ' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}
function render_footer(): void {
    global $config;
    echo '<footer class="footer"><div><a class="brand brand-image" href="' . e(site_url()) . '"><img src="' . e(site_url('assets/adversing-logo.jpg')) . '" alt="ADVERSING Advertising Agency"></a><p>طراحی، اجرا و چاپ تبلیغاتی</p><p>نشانی: میدان بهارستان، ابتدای خیابان جمهوری، پلاک ۱۷، مجموعه سلکت</p><p>تلفن: ۰۹۱۲۹۲۷۱۹۸۲ — ۰۹۰۰۹۲۷۱۹۸۲</p><p>۰۹۱۲۲۸۷۱۵۵۸ — ۰۹۰۲۲۸۷۱۵۵۸</p></div>';
    echo '<div><h3>دسترسی سریع</h3><a href="' . e(site_url('products.php')) . '">همه محصولات</a><a href="' . e(site_url('about.php')) . '">درباره ما</a><a href="' . e(site_url('contact.php')) . '">تماس با ما</a></div>';
    echo '<div><h3>خدمات مشتریان</h3><span>پیگیری سفارش از صفحه سفارش</span><span>پشتیبانی: ۰۹۱۲۹۲۷۱۹۸۲</span><span>شنبه تا پنجشنبه</span></div>';
    echo '<div><h3>خبرنامه</h3><p>برای دریافت خبرهای تازه، ایمیل خود را ثبت کنید.</p><form class="newsletter" action="' . e(site_url('newsletter.php')) . '" method="post">' . csrf_field() . '<input type="email" name="email" required placeholder="ایمیل شما"><button>ثبت</button></form></div>';
    echo '<div class="copyright">© ' . date('Y') . ' ADVERSING — تمامی حقوق محفوظ است.</div></footer>';
    echo '<script src="' . e(site_url('assets/app.js')) . '"></script></body></html>';
}


function setting(string $key, string $default = ''): string {
    global $pdo;
    try { $q=$pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key=?'); $q->execute([$key]); $v=$q->fetchColumn(); return $v===false?$default:(string)$v; }
    catch(Throwable $e) { return $default; }
}
function safe_upload(array $file, array $allowed, string $folder, int $maxBytes): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('بارگذاری فایل انجام نشد.');
    if (($file['size'] ?? 0) < 1 || $file['size'] > $maxBytes) throw new RuntimeException('حجم فایل خارج از محدوده مجاز است.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) throw new RuntimeException('نوع فایل مجاز نیست.');
    $dir=__DIR__.'/../storage/'.$folder; if(!is_dir($dir)) mkdir($dir,0750,true);
    $name=bin2hex(random_bytes(18)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new RuntimeException('ذخیره فایل ناموفق بود.');
    return ['name'=>$name,'mime'=>$mime,'size'=>(int)$file['size']];
}
function calculate_coupon(PDO $pdo, string $code, int $subtotal): array {
    $code=strtoupper(trim($code)); if($code==='') return ['discount'=>0,'code'=>null,'error'=>null];
    $q=$pdo->prepare('SELECT * FROM coupons WHERE code=? AND is_active=1 LIMIT 1');$q->execute([$code]);$c=$q->fetch();
    if(!$c) return ['discount'=>0,'code'=>null,'error'=>'کد تخفیف معتبر نیست.'];
    $now=time(); if(!empty($c['starts_at'])&&strtotime($c['starts_at'])>$now) return ['discount'=>0,'code'=>null,'error'=>'زمان استفاده از این کد هنوز شروع نشده است.'];
    if(!empty($c['expires_at'])&&strtotime($c['expires_at'])<$now) return ['discount'=>0,'code'=>null,'error'=>'اعتبار کد تخفیف تمام شده است.'];
    if($c['max_uses']!==null&&(int)$c['used_count'] >= (int)$c['max_uses']) return ['discount'=>0,'code'=>null,'error'=>'ظرفیت استفاده از کد تمام شده است.'];
    if($subtotal<(int)$c['min_order']) return ['discount'=>0,'code'=>null,'error'=>'مبلغ سفارش به حداقل لازم برای این کد نمی‌رسد.'];
    $discount=$c['discount_type']==='percent'?(int)floor($subtotal*min(100,(int)$c['discount_value'])/100):min($subtotal,(int)$c['discount_value']);
    return ['discount'=>min($subtotal,$discount),'code'=>$code,'error'=>null];
}
