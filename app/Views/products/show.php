<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div class="page-header-copy"><p class="eyebrow">Product details</p><h1><?= esc($product['name']) ?></h1><p><?= $product['is_archived'] ? 'Archived catalog record retained for history.' : 'Active catalog record available to store operations.' ?></p></div><?php if (! $product['is_archived']): ?><div class="page-actions"><a class="button button-primary" href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit product</a></div><?php endif; ?></header>
<section class="panel detail-grid">
    <img class="detail-image" src="<?= esc($product['image'] ? base_url('uploads/products/' . $product['image']) : base_url('assets/images/product-placeholder.svg')) ?>" alt="<?= esc($product['name']) ?> product image">
    <dl><div><dt>Catalog reference</dt><dd>#<?= esc($product['id']) ?></dd></div><div><dt>Price</dt><dd>₱<?= number_format((float) $product['price'], 2) ?></dd></div><div><dt>Stock</dt><dd><span class="stock <?= (int) $product['stock_quantity'] === 0 ? 'stock-out' : ((int) $product['stock_quantity'] <= 5 ? 'stock-low' : '') ?>"><?= esc($product['stock_quantity']) ?> units</span></dd></div><div><dt>Status</dt><dd><span class="status-pill <?= $product['is_archived'] ? 'status-archived' : '' ?>"><?= $product['is_archived'] ? 'Archived' : 'Active' ?></span></dd></div><div><dt>Created</dt><dd><time datetime="<?= esc(date(DATE_ATOM, strtotime($product['created_at']))) ?>"><?= esc(date('F j, Y · g:i A', strtotime($product['created_at']))) ?></time></dd></div></dl>
</section>
<a class="back-link" href="<?= site_url('products') ?>">← Back to products</a>
<?= $this->endSection() ?>
