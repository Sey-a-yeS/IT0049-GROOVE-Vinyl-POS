<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Customer details</p><h1><?= esc($customer['full_name']) ?></h1><p><?= $customer['is_archived'] ? 'Archived customer record' : 'Active customer record' ?></p></div><?php if (! $customer['is_archived']): ?><a class="button button-primary" href="<?= site_url('customers/' . $customer['id'] . '/edit') ?>">Edit customer</a><?php endif; ?></header>
<section class="panel detail-grid detail-grid-compact"><div class="avatar-letter" aria-hidden="true"><?= esc(strtoupper(substr($customer['full_name'], 0, 1))) ?></div><dl><div><dt>Email</dt><dd><a href="mailto:<?= esc($customer['email']) ?>"><?= esc($customer['email']) ?></a></dd></div><div><dt>Phone</dt><dd><?= esc($customer['phone'] ?: 'Not provided') ?></dd></div><div><dt>Status</dt><dd><?= $customer['is_archived'] ? 'Archived' : 'Active' ?></dd></div><div><dt>Created</dt><dd><?= esc(date('F j, Y g:i A', strtotime($customer['created_at']))) ?></dd></div></dl></section>
<a class="back-link" href="<?= site_url('customers') ?>">← Back to customers</a>
<?= $this->endSection() ?>
