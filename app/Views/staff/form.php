<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $editing = $staffMember !== null; $errors = session('errors') ?? []; ?>
<header class="page-header"><div><p class="eyebrow">Staff access</p><h1><?= $editing ? 'Edit staff account' : 'New staff account' ?></h1><p>Passwords are hashed before storage and never shown in listings.</p></div></header>
<section class="panel form-panel"><form action="<?= $editing ? site_url('staff/' . $staffMember['id']) : site_url('staff') ?>" method="post" enctype="multipart/form-data" class="form-stack" novalidate>
<?= csrf_field() ?>
<div class="form-grid"><div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="100" value="<?= esc(old('full_name', $staffMember['full_name'] ?? '')) ?>" required><small class="field-error"><?= esc($errors['full_name'] ?? '') ?></small></div><div class="field"><label for="username">Username</label><input id="username" name="username" maxlength="50" value="<?= esc(old('username', $staffMember['username'] ?? '')) ?>" autocomplete="off" required><small>Letters, numbers, dots, underscores, and hyphens.</small><small class="field-error"><?= esc($errors['username'] ?? '') ?></small></div></div>
<div class="field"><label for="password"><?= $editing ? 'New password ' : 'Password' ?><span><?= $editing ? 'leave blank to keep current password' : 'minimum 10 characters' ?></span></label><input id="password" name="password" type="password" minlength="10" maxlength="255" autocomplete="new-password" <?= $editing ? '' : 'required' ?>><small class="field-error"><?= esc($errors['password'] ?? '') ?></small></div>
<div class="field"><label for="avatar">Avatar <span>optional</span></label><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png"><small>JPG or PNG, up to 2 MB. Images are prepared to 800 × 800 pixels.</small><small class="field-error"><?= esc($errors['avatar'] ?? '') ?></small></div>
<?php if ($editing && $staffMember['avatar']): ?><div class="current-image"><img class="avatar" src="<?= base_url('uploads/avatars/' . esc($staffMember['avatar'])) ?>" alt="Current avatar"><span>Current avatar remains unless replaced.</span></div><?php endif; ?>
<div class="form-actions"><button class="button button-primary" type="submit"><?= $editing ? 'Save changes' : 'Create staff account' ?></button><a class="button button-secondary" href="<?= $editing ? site_url('staff/' . $staffMember['id']) : site_url('staff') ?>">Cancel</a></div>
</form></section>
<?= $this->endSection() ?>
