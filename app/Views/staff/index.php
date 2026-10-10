<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<header class="page-header"><div><p class="eyebrow">Access &amp; accountability</p><h1>Staff</h1><p>Manage authenticated accounts used to record transactions.</p></div><a class="button button-primary" href="<?= site_url('staff/new') ?>">New staff account</a></header>
<section class="panel table-panel">
<?php if ($staff): ?><div class="table-wrap"><table><thead><tr><th>Staff member</th><th>Username</th><th>Added</th><th class="actions-column">Actions</th></tr></thead><tbody>
<?php foreach ($staff as $person): ?><tr><td><div class="identity"><img class="avatar" src="<?= esc($person['avatar'] ? base_url('uploads/avatars/' . $person['avatar']) : base_url('assets/images/avatar-placeholder.svg')) ?>" alt=""><strong><?= esc($person['full_name']) ?></strong></div></td><td>@<?= esc($person['username']) ?></td><td><?= esc(date('M j, Y', strtotime($person['created_at']))) ?></td><td class="actions"><a href="<?= site_url('staff/' . $person['id']) ?>">View</a><a href="<?= site_url('staff/' . $person['id'] . '/edit') ?>">Edit</a><?php if ((int) session('staff_id') !== (int) $person['id']): ?><form action="<?= site_url('staff/' . $person['id'] . '/archive') ?>" method="post" data-confirm="Archive this staff account? The user will no longer be able to sign in."><?= csrf_field() ?><button type="submit">Archive</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?= $pager->links() ?><?php else: ?><div class="empty-state"><h2>No active staff</h2><p>An active staff account is required to use GROOVE.</p></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
