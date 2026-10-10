<?php
$isAuthenticated = (bool) session()->get('staff_id');
$currentPath = trim(uri_string(), '/');
$iconSprite = base_url('assets/images/icons.svg');
$navigation = [
    ['label' => 'Dashboard', 'href' => 'dashboard', 'icon' => 'dashboard', 'active' => $currentPath === 'dashboard'],
    ['label' => 'Products', 'href' => 'products', 'icon' => 'record', 'active' => str_starts_with($currentPath, 'products')],
    ['label' => 'Customers', 'href' => 'customers', 'icon' => 'customers', 'active' => str_starts_with($currentPath, 'customers')],
    ['label' => 'Staff', 'href' => 'staff', 'icon' => 'staff', 'active' => str_starts_with($currentPath, 'staff')],
    ['label' => 'Record Sale', 'href' => 'sales/new', 'icon' => 'sale', 'active' => $currentPath === 'sales/new' || $currentPath === 'sales'],
    ['label' => 'Sales History', 'href' => 'sales/history', 'icon' => 'history', 'active' => $currentPath === 'sales/history'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GROOVE Vinyl and Music Store point-of-sale management system">
    <meta name="theme-color" content="#252326">
    <title><?= esc($title ?? 'GROOVE') ?> | GROOVE POS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/groove.css') ?>">
</head>
<body class="<?= $isAuthenticated ? 'app-shell' : 'auth-shell' ?>">
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php if ($isAuthenticated): ?>
    <aside class="sidebar" aria-label="Primary navigation">
        <a class="brand" href="<?= site_url('dashboard') ?>" aria-label="GROOVE dashboard">
            <span class="brand-mark" aria-hidden="true"><i></i></span>
            <span><strong>GROOVE</strong><small>Vinyl &amp; Music Store POS</small></span>
        </a>
        <nav>
            <?php foreach ($navigation as $item): ?>
                <a href="<?= site_url($item['href']) ?>" class="<?= $item['active'] ? 'active' : '' ?>" <?= $item['active'] ? 'aria-current="page"' : '' ?>>
                    <svg class="nav-icon" aria-hidden="true"><use href="<?= esc($iconSprite . '#icon-' . $item['icon']) ?>"></use></svg>
                    <span><?= esc($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="staff-panel">
            <img class="staff-panel-avatar" src="<?= esc(session()->get('staff_avatar') ? base_url('uploads/avatars/' . session()->get('staff_avatar')) : base_url('assets/images/avatar-placeholder.svg')) ?>" alt="">
            <div class="staff-panel-copy">
                <span class="eyebrow">Signed in</span>
                <strong><?= esc(session()->get('staff_name')) ?></strong>
                <span>@<?= esc(session()->get('staff_username')) ?></span>
            </div>
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="link-button" type="submit">Sign out</button>
            </form>
        </div>
    </aside>
<?php endif; ?>

<main id="main-content" tabindex="-1" class="<?= $isAuthenticated ? 'workspace' : 'auth-workspace' ?>">
    <?= $this->include('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>
<script src="<?= base_url('assets/js/groove.js') ?>" defer></script>
</body>
</html>
