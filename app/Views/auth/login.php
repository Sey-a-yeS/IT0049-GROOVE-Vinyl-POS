<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="login-card">
    <div class="login-brand">
        <span class="brand-mark brand-mark-large" aria-hidden="true"></span>
        <p class="eyebrow">Vinyl &amp; Music Store POS</p>
        <h1>GROOVE</h1>
        <p>Sign in with an active staff account to manage inventory and sales.</p>
    </div>
    <form action="<?= site_url('login') ?>" method="post" class="form-stack" novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" maxlength="50" value="<?= esc(session('login_username') ?? '') ?>" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" maxlength="255" autocomplete="current-password" required>
        </div>
        <button class="button button-primary button-wide" type="submit">Sign in</button>
    </form>
</section>
<?= $this->endSection() ?>
