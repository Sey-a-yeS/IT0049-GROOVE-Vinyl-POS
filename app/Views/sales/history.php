<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Transaction ledger</p><h1>Sales history</h1><p>Recent transactions with linked product, customer, and staff records.</p></div><a class="button button-primary" href="<?= site_url('sales/new') ?>">Record a sale</a></header>
<section class="panel table-panel">
<?php if ($sales): ?><div class="table-wrap"><table><thead><tr><th>Sale</th><th>Product</th><th>Customer</th><th>Staff</th><th>Qty</th><th>Total</th><th>Date &amp; time</th></tr></thead><tbody>
<?php foreach ($sales as $sale): ?><tr><td>#<?= esc($sale['id']) ?></td><td><strong><?= esc($sale['product_name']) ?></strong></td><td><?= esc($sale['customer_name'] ?: 'Walk-in Customer') ?></td><td><?= esc($sale['staff_name']) ?></td><td><?= esc($sale['quantity']) ?></td><td><strong>₱<?= number_format((float) $sale['total_price'], 2) ?></strong></td><td><?= esc(date('M j, Y g:i A', strtotime($sale['created_at']))) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?= $pager->links() ?><?php else: ?><div class="empty-state"><h2>No sales yet</h2><p>Completed transactions will appear here with their related records.</p><a class="button button-primary" href="<?= site_url('sales/new') ?>">Record the first sale</a></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
