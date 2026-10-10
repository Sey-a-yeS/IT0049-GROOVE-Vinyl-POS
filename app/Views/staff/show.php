<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Staff details</p><h1><?= esc($staffMember['full_name']) ?></h1><p><?= $staffMember['is_archived'] ? 'Archived staff account' : 'Active staff account' ?></p></div><?php if (! $staffMember['is_archived']): ?><a class="button button-primary" href="<?= site_url('staff/' . $staffMember['id'] . '/edit') ?>">Edit staff</a><?php endif; ?></header>
<section class="panel detail-grid detail-grid-compact"><img class="detail-avatar" src="<?= esc($staffMember['avatar'] ? base_url('uploads/avatars/' . $staffMember['avatar']) : base_url('assets/images/avatar-placeholder.svg')) ?>" alt="<?= esc($staffMember['full_name']) ?>"><dl><div><dt>Username</dt><dd>@<?= esc($staffMember['username']) ?></dd></div><div><dt>Status</dt><dd><?= $staffMember['is_archived'] ? 'Archived' : 'Active' ?></dd></div><div><dt>Created</dt><dd><?= esc(date('F j, Y g:i A', strtotime($staffMember['created_at']))) ?></dd></div></dl></section>
<a class="back-link" href="<?= site_url('staff') ?>">← Back to staff</a>
<?= $this->endSection() ?>
