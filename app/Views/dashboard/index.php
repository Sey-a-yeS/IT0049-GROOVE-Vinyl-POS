<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header">
    <div><p class="eyebrow">Store operations</p><h1>Dashboard</h1><p>Live information from the GROOVE database.</p></div>
    <a class="button button-primary" href="<?= site_url('sales/new') ?>">Record a sale</a>
</header>

<section class="metrics" aria-label="Store totals">
    <article><span>Active products</span><strong><?= esc($metrics['products']) ?></strong></article>
    <article><span>Customers</span><strong><?= esc($metrics['customers']) ?></strong></article>
    <article><span>Active staff</span><strong><?= esc($metrics['staff']) ?></strong></article>
    <article><span>Recorded sales</span><strong><?= esc($metrics['sales']) ?></strong></article>
</section>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-heading"><div><p class="eyebrow">Inventory watch</p><h2>Low stock</h2></div><a href="<?= site_url('products') ?>">View products</a></div>
        <?php if ($lowStock): ?>
            <div class="compact-list">
                <?php foreach ($lowStock as $product): ?>
                    <a href="<?= site_url('products/' . $product['id']) ?>"><span><?= esc($product['name']) ?></span><strong class="stock <?= (int) $product['stock_quantity'] === 0 ? 'stock-out' : '' ?>"><?= esc($product['stock_quantity']) ?> left</strong></a>
                <?php endforeach; ?>
            </div>
        <?php else: ?><p class="empty-state">No products are low on stock.</p><?php endif; ?>
    </section>
    <section class="panel">
        <div class="section-heading"><div><p class="eyebrow">Latest activity</p><h2>Recent sales</h2></div><a href="<?= site_url('sales/history') ?>">Full history</a></div>
        <?php if ($recentSales): ?>
            <div class="compact-list">
                <?php foreach ($recentSales as $sale): ?>
                    <div><span><?= esc($sale['product_name']) ?><small><?= esc($sale['customer_name'] ?: 'Walk-in Customer') ?></small></span><strong>₱<?= number_format((float) $sale['total_price'], 2) ?></strong></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?><p class="empty-state">No sales recorded yet. The first transaction will appear here.</p><?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
