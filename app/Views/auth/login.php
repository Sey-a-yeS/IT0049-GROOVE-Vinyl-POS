<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<section class="login-card" aria-labelledby="login-title">
    <div class="login-brand">
        <span class="brand-mark brand-mark-large" aria-hidden="true"></span>
        <p class="eyebrow">Vinyl &amp; Music Store POS</p>
        <h1>GROOVE</h1>
        <p class="login-intro">A focused workspace for catalog care, customer service, and every record leaving the counter.</p>
        <p class="login-caption">Drop the needle. Run the shop.</p>
    </div>
    <div class="login-form-wrap">
        <p class="eyebrow">Staff access</p>
        <h2 id="login-title">Welcome back</h2>
        <p>Sign in with an active GROOVE staff account.</p>
        <form action="<?= site_url('login') ?>" method="post" class="form-stack" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" maxlength="50" value="<?= esc(session('login_username') ?? '') ?>" autocomplete="username" required autofocus <?= isset($errors['username']) ? 'aria-invalid="true" aria-describedby="username-error"' : '' ?>>
                <small class="field-error" id="username-error"><?= esc($errors['username'] ?? '') ?></small>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" maxlength="255" autocomplete="current-password" required <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>>
                <small class="field-error" id="password-error"><?= esc($errors['password'] ?? '') ?></small>
            </div>
            <button class="button button-primary button-wide" type="submit">Sign in to GROOVE</button>
            <p class="login-security">Protected staff access · Passwords are never displayed or repopulated</p>
        </form>
    </div>
</section>
<?= $this->endSection() ?>
