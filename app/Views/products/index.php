<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header">
    <div><p class="eyebrow">Catalog &amp; inventory</p><h1>Products</h1><p>Manage sellable records, pricing, stock, and prepared images.</p></div>
    <a class="button button-primary" href="<?= site_url('products/new') ?>">New product</a>
</header>
<section class="panel table-panel">
<?php if ($products): ?>
<div class="table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Added</th><th class="actions-column">Actions</th></tr></thead><tbody>
<?php foreach ($products as $product): ?>
<tr>
    <td><div class="identity"><img src="<?= esc($product['image'] ? base_url('uploads/products/' . $product['image']) : base_url('assets/images/product-placeholder.svg')) ?>" alt=""><strong><?= esc($product['name']) ?></strong></div></td>
    <td>₱<?= number_format((float) $product['price'], 2) ?></td>
    <td><span class="stock <?= (int) $product['stock_quantity'] === 0 ? 'stock-out' : ((int) $product['stock_quantity'] <= 5 ? 'stock-low' : '') ?>"><?= esc($product['stock_quantity']) ?></span></td>
    <td><?= esc(date('M j, Y', strtotime($product['created_at']))) ?></td>
    <td class="actions"><a href="<?= site_url('products/' . $product['id']) ?>">View</a><a href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit</a><form action="<?= site_url('products/' . $product['id'] . '/archive') ?>" method="post" data-confirm="Archive this product? It will remain visible in historical sales."><?= csrf_field() ?><button type="submit">Archive</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?= $pager->links() ?>
<?php else: ?><div class="empty-state"><h2>No active products</h2><p>Create the first catalog item to begin tracking inventory.</p></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
