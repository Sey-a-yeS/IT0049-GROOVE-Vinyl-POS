<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header">
    <div class="page-header-copy"><p class="eyebrow">Store operations</p><h1>Dashboard</h1><p>A clear view of today’s catalog, people, and transaction activity.</p></div>
    <div class="page-actions"><a class="button button-primary" href="<?= site_url('sales/new') ?>">Record a sale</a></div>
</header>

<section class="metrics" aria-label="Store totals">
    <article><span>Active products</span><strong><?= esc($metrics['products']) ?></strong></article>
    <article><span>Customers</span><strong><?= esc($metrics['customers']) ?></strong></article>
    <article><span>Active staff</span><strong><?= esc($metrics['staff']) ?></strong></article>
    <article><span>Recorded sales</span><strong><?= esc($metrics['sales']) ?></strong></article>
</section>

<div class="dashboard-grid">
    <section class="panel" aria-labelledby="low-stock-title">
        <div class="section-heading"><div><p class="eyebrow">Inventory watch</p><h2 id="low-stock-title">Low stock</h2></div><a class="text-link" href="<?= site_url('products') ?>">View products →</a></div>
        <?php if ($lowStock): ?>
            <div class="compact-list">
                <?php foreach ($lowStock as $product): ?>
                    <a href="<?= site_url('products/' . $product['id']) ?>"><span><?= esc($product['name']) ?><small>Restock attention recommended</small></span><strong class="stock <?= (int) $product['stock_quantity'] === 0 ? 'stock-out' : 'stock-low' ?>"><?= esc($product['stock_quantity']) ?> left</strong></a>
                <?php endforeach; ?>
            </div>
        <?php else: ?><div class="empty-state empty-state-compact"><p>No products are low on stock.</p></div><?php endif; ?>
    </section>
    <section class="panel" aria-labelledby="recent-sales-title">
        <div class="section-heading"><div><p class="eyebrow">Latest activity</p><h2 id="recent-sales-title">Recent sales</h2></div><a class="text-link" href="<?= site_url('sales/history') ?>">Full history →</a></div>
        <?php if ($recentSales): ?>
            <div class="compact-list">
                <?php foreach ($recentSales as $sale): ?>
                    <div><span><?= esc($sale['product_name']) ?><small>#<?= esc($sale['id']) ?> · <?= esc($sale['customer_name'] ?: 'Walk-in Customer') ?></small></span><strong>₱<?= number_format((float) $sale['total_price'], 2) ?></strong></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?><div class="empty-state empty-state-compact"><p>No sales recorded yet. The first transaction will appear here.</p></div><?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
