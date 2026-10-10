<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GROOVE Vinyl and Music Store point-of-sale management system">
    <title><?= esc($title ?? 'GROOVE') ?> | GROOVE POS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/groove.css') ?>">
</head>
<body class="<?= session()->get('staff_id') ? 'app-shell' : 'auth-shell' ?>">
<?php if (session()->get('staff_id')): ?>
    <aside class="sidebar" aria-label="Primary navigation">
        <a class="brand" href="<?= site_url('dashboard') ?>" aria-label="GROOVE dashboard">
            <span class="brand-mark" aria-hidden="true"></span>
            <span><strong>GROOVE</strong><small>Vinyl &amp; Music Store POS</small></span>
        </a>
        <nav>
            <a href="<?= site_url('dashboard') ?>">Dashboard</a>
            <a href="<?= site_url('products') ?>">Products</a>
            <a href="<?= site_url('customers') ?>">Customers</a>
            <a href="<?= site_url('staff') ?>">Staff</a>
            <a href="<?= site_url('sales/new') ?>">Record Sale</a>
            <a href="<?= site_url('sales/history') ?>">Sales History</a>
        </nav>
        <div class="staff-panel">
            <span class="eyebrow">Signed in as</span>
            <strong><?= esc(session()->get('staff_name')) ?></strong>
            <span>@<?= esc(session()->get('staff_username')) ?></span>
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="link-button" type="submit">Sign out</button>
            </form>
        </div>
    </aside>
<?php endif; ?>

<main class="<?= session()->get('staff_id') ? 'workspace' : 'auth-workspace' ?>">
    <?= $this->include('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>
<script src="<?= base_url('assets/js/groove.js') ?>" defer></script>
</body>
</html>
