<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $receipt = session('sale_receipt'); ?>
<header class="page-header"><div class="page-header-copy"><p class="eyebrow">Transaction ledger</p><h1>Sales history</h1><p>A durable record of product, customer, staff, quantity, and total.</p></div><div class="page-actions"><a class="button button-primary" href="<?= site_url('sales/new') ?>">Record a sale</a></div></header>
<?php if (is_array($receipt)): ?>
<section class="panel receipt" aria-labelledby="receipt-title">
    <div><p class="eyebrow">Transaction complete</p><h2 id="receipt-title">Sale #<?= esc($receipt['id']) ?> · <?= esc($receipt['product_name']) ?></h2><p>Inventory was updated and the ledger entry is now permanent.</p></div>
    <dl><div><dt>Quantity</dt><dd><?= esc($receipt['quantity']) ?></dd></div><div><dt>Total</dt><dd>₱<?= number_format((float) $receipt['total_price'], 2) ?></dd></div><div><dt>Customer</dt><dd><?= esc($receipt['customer_name'] ?: 'Walk-in') ?></dd></div><div><dt>Stock left</dt><dd><?= esc($receipt['remaining_stock']) ?></dd></div></dl>
</section>
<?php endif; ?>
<section class="panel table-panel" aria-labelledby="sales-list-title">
<div class="table-toolbar">
    <p id="sales-list-title"><strong><?= count($sales) ?></strong> <?= $query !== '' ? 'matching' : 'recent' ?> transaction<?= count($sales) === 1 ? '' : 's' ?> on this page</p>
    <form class="search-form" action="<?= site_url('sales/history') ?>" method="get" role="search"><div class="search-control"><svg aria-hidden="true"><use href="<?= esc(base_url('assets/images/icons.svg#icon-search')) ?>"></use></svg><label class="skip-link" for="sales-search">Search sales history</label><input id="sales-search" name="q" type="search" value="<?= esc($query) ?>" placeholder="Sale ID, product, customer, staff" maxlength="100"></div><button class="button button-secondary" type="submit">Search</button><?php if ($query !== ''): ?><a class="button button-secondary" href="<?= site_url('sales/history') ?>">Clear</a><?php endif; ?></form>
</div>
<?php if ($sales): ?><div class="table-wrap"><table><thead><tr><th>Sale</th><th>Product</th><th>Customer</th><th>Staff</th><th class="numeric">Qty</th><th class="numeric">Total</th><th>Date &amp; time</th></tr></thead><tbody>
<?php foreach ($sales as $sale): ?><tr><td><strong>#<?= esc($sale['id']) ?></strong></td><td><?= esc($sale['product_name']) ?></td><td><?= esc($sale['customer_name'] ?: 'Walk-in Customer') ?></td><td><?= esc($sale['staff_name']) ?></td><td class="numeric"><?= esc($sale['quantity']) ?></td><td class="numeric"><strong>₱<?= number_format((float) $sale['total_price'], 2) ?></strong></td><td><time datetime="<?= esc(date(DATE_ATOM, strtotime($sale['created_at']))) ?>"><?= esc(date('M j, Y · g:i A', strtotime($sale['created_at']))) ?></time></td></tr><?php endforeach; ?>
</tbody></table></div><?= $pager->links() ?><?php else: ?><div class="empty-state"><h2><?= $query !== '' ? 'No matching transactions' : 'No sales yet' ?></h2><p><?= $query !== '' ? 'Try another sale ID, product, customer, or staff name.' : 'Completed transactions will appear here with their related records.' ?></p><?php if ($query !== ''): ?><a class="button button-secondary" href="<?= site_url('sales/history') ?>">Clear search</a><?php else: ?><a class="button button-primary" href="<?= site_url('sales/new') ?>">Record the first sale</a><?php endif; ?></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
