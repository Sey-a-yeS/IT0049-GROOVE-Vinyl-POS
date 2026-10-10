<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $editing = $customer !== null; $errors = session('errors') ?? []; ?>
<header class="page-header"><div class="page-header-copy"><p class="eyebrow">Customer records</p><h1><?= $editing ? 'Edit customer' : 'New customer' ?></h1><p>Keep contact information current, concise, and ready for the counter.</p></div></header>
<section class="panel form-panel"><form action="<?= $editing ? site_url('customers/' . $customer['id']) : site_url('customers') ?>" method="post" class="form-stack" novalidate>
<?= csrf_field() ?>
<div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="100" autocomplete="name" value="<?= esc(old('full_name', $customer['full_name'] ?? '')) ?>" required <?= isset($errors['full_name']) ? 'aria-invalid="true" aria-describedby="full-name-error"' : '' ?>><small class="field-error" id="full-name-error"><?= esc($errors['full_name'] ?? '') ?></small></div>
<div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="100" autocomplete="email" value="<?= esc(old('email', $customer['email'] ?? '')) ?>" required <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>><small class="field-error" id="email-error"><?= esc($errors['email'] ?? '') ?></small></div>
<div class="field"><label for="phone">Phone <span>optional</span></label><input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?= esc(old('phone', $customer['phone'] ?? '')) ?>" <?= isset($errors['phone']) ? 'aria-invalid="true" aria-describedby="phone-error"' : '' ?>><small class="field-error" id="phone-error"><?= esc($errors['phone'] ?? '') ?></small></div>
<div class="form-actions"><button class="button button-primary" type="submit"><?= $editing ? 'Save changes' : 'Create customer' ?></button><a class="button button-secondary" href="<?= $editing ? site_url('customers/' . $customer['id']) : site_url('customers') ?>">Cancel</a></div>
</form></section>
<?= $this->endSection() ?>
