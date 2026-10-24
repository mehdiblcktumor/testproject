<?php
require __DIR__ . '/app/bootstrap.php';
$q = trim((string)($_GET['q'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$where = ['is_active=1']; $args=[];
if ($q !== '') { $where[]='(name LIKE ? OR description LIKE ? OR category LIKE ?)'; $args[]='%'.$q.'%'; $args[]='%'.$q.'%'; $args[]='%'.$q.'%'; }
if ($category !== '') { $where[]='category = ?'; $args[]=$category; }
$whereSql = implode(' AND ', $where);
$count=$pdo->prepare("SELECT COUNT(*) FROM products WHERE $whereSql");$count->execute($args);$total=(int)$count->fetchColumn();
$pages=max(1,(int)ceil($total/$perPage)); if($page>$pages)$page=$pages;
$sql="SELECT * FROM products WHERE $whereSql ORDER BY is_featured DESC, category ASC, name ASC, id ASC LIMIT $perPage OFFSET ".(($page-1)*$perPage);
$stmt=$pdo->prepare($sql);$stmt->execute($args);$products=$stmt->fetchAll();
$categories=$pdo->query('SELECT DISTINCT category FROM products WHERE is_active=1 ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
function printo_tirage_html(?string $value): string {
 if (!$value) return '';
 $sets=explode('|',$value); $html='<div class="tirage-list"><span class="tirage-label">تیراژ:</span>';
 foreach($sets as $idx=>$set){$nums=array_filter(array_map('trim',explode('،',trim($set))),fn($x)=>$x!=='');$html.='<div class="tirage-set">';if(count($sets)>1)$html.='<span class="tirage-set-label">'.($idx===0?'سری اول':'سری دوم').'</span>';
 foreach($nums as $num)$html.='<span class="tirage-chip">'.e($num).'</span>';
 $html.='</div>';}
 return $html.'</div>';
}
render_header('محصولات چاپی');
?>
<main class="container"><div class="page-title"><span class="eyebrow">کاتالوگ ADVERSING</span><h1><?= $category!==''?e($category):'همه محصولات چاپی' ?></h1><p>محصول مناسب برند خود را پیدا کنید؛ تمام دسته‌بندی‌ها و گزینه‌های تیراژ در جای خود حفظ شده‌اند.</p></div>
<div class="category-strip"><a class="category-pill <?=$category===''?'active':''?>" href="<?=e(site_url('products.php'))?>">همه محصولات</a><?php foreach($categories as $c): ?><a class="category-pill <?=$category===$c?'active':''?>" href="<?=e(site_url('products.php?category='.rawurlencode($c)))?>"><?=e($c)?></a><?php endforeach; ?></div>
<form class="filters" method="get"><input type="text" name="q" value="<?=e($q)?>" placeholder="نام محصول یا دسته‌بندی"><select name="category"><option value="">همه دسته‌ها</option><?php foreach($categories as $c): ?><option value="<?=e($c)?>" <?=$category===$c?'selected':''?>><?=e($c)?></option><?php endforeach; ?></select><button class="btn">اعمال فیلتر</button></form>
<div class="catalog-meta"><span>تعداد محصولات: <?=number_format($total)?></span><span>صفحه <?=number_format($page)?> از <?=number_format($pages)?></span></div>
<?php if($products): ?><div class="product-grid"><?php foreach($products as $p): ?><article class="product-card"><a href="<?=e(site_url('product.php?id='.$p['id']))?>"><img src="<?=e(product_image($p['image']))?>" alt="<?=e($p['name'])?>" loading="lazy"></a><div class="product-info"><span class="product-category"><?=e($p['category'])?></span><h3><?=e($p['name'])?></h3><?php if(!empty($p['tirages'])) echo printo_tirage_html((string)$p['tirages']); ?><div class="product-actions"><span class="price"><?=((int)$p['price']>0)?money($p['price']):'استعلام قیمت'?></span><a class="small-btn" href="<?=e(site_url('product.php?id='.$p['id']))?>">مشاهده محصول</a></div></div></article><?php endforeach; ?></div>
<?php if($pages>1): ?><nav class="pagination" aria-label="صفحه‌بندی محصولات"><?php if($page>1): ?><a href="<?=e(site_url('products.php?'.http_build_query(['q'=>$q,'category'=>$category,'page'=>$page-1])))?>">قبلی</a><?php endif; ?><?php for($n=max(1,$page-2);$n<=min($pages,$page+2);$n++): ?><a class="<?=$n===$page?'active':''?>" href="<?=e(site_url('products.php?'.http_build_query(['q'=>$q,'category'=>$category,'page'=>$n])))?>"><?=number_format($n)?></a><?php endfor; ?><?php if($page<$pages): ?><a href="<?=e(site_url('products.php?'.http_build_query(['q'=>$q,'category'=>$category,'page'=>$page+1])))?>">بعدی</a><?php endif; ?></nav><?php endif; ?>
<?php else: ?><div class="empty">محصولی مطابق جست‌وجوی شما پیدا نشد.</div><?php endif; ?>
</main><?php render_footer(); ?>
