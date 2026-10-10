<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Relationships</p><h1>Customers</h1><p>Maintain customer details while preserving linked sales history.</p></div><a class="button button-primary" href="<?= site_url('customers/new') ?>">New customer</a></header>
<section class="panel table-panel">
<?php if ($customers): ?>
<div class="table-wrap"><table><thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Added</th><th class="actions-column">Actions</th></tr></thead><tbody>
<?php foreach ($customers as $customer): ?><tr><td><strong><?= esc($customer['full_name']) ?></strong></td><td><a href="mailto:<?= esc($customer['email']) ?>"><?= esc($customer['email']) ?></a></td><td><?= esc($customer['phone'] ?: '—') ?></td><td><?= esc(date('M j, Y', strtotime($customer['created_at']))) ?></td><td class="actions"><a href="<?= site_url('customers/' . $customer['id']) ?>">View</a><a href="<?= site_url('customers/' . $customer['id'] . '/edit') ?>">Edit</a><form action="<?= site_url('customers/' . $customer['id'] . '/archive') ?>" method="post" data-confirm="Archive this customer? Historical sales will be preserved."><?= csrf_field() ?><button type="submit">Archive</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?= $pager->links() ?>
<?php else: ?><div class="empty-state"><h2>No active customers</h2><p>Add a customer or record sales as walk-in transactions.</p></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
