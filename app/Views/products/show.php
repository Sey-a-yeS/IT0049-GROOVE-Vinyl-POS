<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Product details</p><h1><?= esc($product['name']) ?></h1><p><?= $product['is_archived'] ? 'Archived catalog record' : 'Active catalog record' ?></p></div><?php if (! $product['is_archived']): ?><a class="button button-primary" href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit product</a><?php endif; ?></header>
<section class="panel detail-grid">
    <img class="detail-image" src="<?= esc($product['image'] ? base_url('uploads/products/' . $product['image']) : base_url('assets/images/product-placeholder.svg')) ?>" alt="<?= esc($product['name']) ?>">
    <dl><div><dt>Price</dt><dd>₱<?= number_format((float) $product['price'], 2) ?></dd></div><div><dt>Stock</dt><dd><?= esc($product['stock_quantity']) ?> units</dd></div><div><dt>Status</dt><dd><?= $product['is_archived'] ? 'Archived' : 'Active' ?></dd></div><div><dt>Created</dt><dd><?= esc(date('F j, Y g:i A', strtotime($product['created_at']))) ?></dd></div></dl>
</section>
<a class="back-link" href="<?= site_url('products') ?>">← Back to products</a>
<?= $this->endSection() ?>
