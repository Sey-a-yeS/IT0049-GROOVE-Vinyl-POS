<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header">
    <div class="page-header-copy"><p class="eyebrow">Catalog &amp; inventory</p><h1>Products</h1><p>Browse records and accessories, review stock, and keep pricing current.</p></div>
    <div class="page-actions"><a class="button button-primary" href="<?= site_url('products/new') ?>">New product</a></div>
</header>
<section class="panel table-panel" aria-labelledby="product-list-title">
    <div class="table-toolbar">
        <p id="product-list-title"><strong><?= count($products) ?></strong> <?= $query !== '' ? 'matching' : 'active' ?> product<?= count($products) === 1 ? '' : 's' ?> on this page</p>
        <form class="search-form" action="<?= site_url('products') ?>" method="get" role="search">
            <div class="search-control"><svg aria-hidden="true"><use href="<?= esc(base_url('assets/images/icons.svg#icon-search')) ?>"></use></svg><label class="skip-link" for="product-search">Search products</label><input id="product-search" name="q" type="search" value="<?= esc($query) ?>" placeholder="Search catalog" maxlength="100"></div>
            <button class="button button-secondary" type="submit">Search</button>
            <?php if ($query !== ''): ?><a class="button button-secondary" href="<?= site_url('products') ?>">Clear</a><?php endif; ?>
        </form>
    </div>
<?php if ($products): ?>
<div class="table-wrap"><table><thead><tr><th>Product</th><th class="numeric">Price</th><th>Stock</th><th>Added</th><th class="actions-column">Actions</th></tr></thead><tbody>
<?php foreach ($products as $product): ?>
<tr>
    <td><div class="identity"><img src="<?= esc($product['image'] ? base_url('uploads/products/' . $product['image']) : base_url('assets/images/product-placeholder.svg')) ?>" alt=""><span class="identity-copy"><strong><?= esc($product['name']) ?></strong><small>Catalog #<?= esc($product['id']) ?></small></span></div></td>
    <td class="numeric"><strong>₱<?= number_format((float) $product['price'], 2) ?></strong></td>
    <td><span class="stock <?= (int) $product['stock_quantity'] === 0 ? 'stock-out' : ((int) $product['stock_quantity'] <= 5 ? 'stock-low' : '') ?>"><?= esc($product['stock_quantity']) ?> <?= (int) $product['stock_quantity'] === 1 ? 'unit' : 'units' ?></span></td>
    <td><time datetime="<?= esc(date(DATE_ATOM, strtotime($product['created_at']))) ?>"><?= esc(date('M j, Y', strtotime($product['created_at']))) ?></time></td>
    <td class="actions"><a href="<?= site_url('products/' . $product['id']) ?>">View</a><a href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit</a><form action="<?= site_url('products/' . $product['id'] . '/archive') ?>" method="post" data-confirm="Archive this product? It will remain visible in historical sales."><?= csrf_field() ?><button type="submit">Archive</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?= $pager->links() ?>
<?php else: ?><div class="empty-state"><h2><?= $query !== '' ? 'No matching products' : 'No active products' ?></h2><p><?= $query !== '' ? 'Try another catalog search or clear the current query.' : 'Create the first catalog item to begin tracking inventory.' ?></p><?php if ($query !== ''): ?><a class="button button-secondary" href="<?= site_url('products') ?>">Clear search</a><?php endif; ?></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
