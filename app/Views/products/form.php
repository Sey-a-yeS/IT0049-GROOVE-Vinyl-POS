<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $editing = $product !== null; $errors = session('errors') ?? []; ?>
<header class="page-header"><div><p class="eyebrow">Catalog &amp; inventory</p><h1><?= $editing ? 'Edit product' : 'New product' ?></h1><p>Use accurate pricing and whole-number stock quantities.</p></div></header>
<section class="panel form-panel">
<form action="<?= $editing ? site_url('products/' . $product['id']) : site_url('products') ?>" method="post" enctype="multipart/form-data" class="form-stack" novalidate>
<?= csrf_field() ?>
<div class="field"><label for="name">Product name</label><input id="name" name="name" maxlength="100" value="<?= esc(old('name', $product['name'] ?? '')) ?>" required><small class="field-error"><?= esc($errors['name'] ?? '') ?></small></div>
<div class="form-grid">
    <div class="field"><label for="price">Price in PHP</label><input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="<?= esc(old('price', $product['price'] ?? '')) ?>" required><small class="field-error"><?= esc($errors['price'] ?? '') ?></small></div>
    <div class="field"><label for="stock_quantity">Stock quantity</label><input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" value="<?= esc(old('stock_quantity', $product['stock_quantity'] ?? '0')) ?>" required><small class="field-error"><?= esc($errors['stock_quantity'] ?? '') ?></small></div>
</div>
<div class="field"><label for="image">Product image <span>optional</span></label><input id="image" name="image" type="file" accept="image/jpeg,image/png"><small>JPG or PNG, up to 2 MB. Images are prepared to 800 × 800 pixels.</small><small class="field-error"><?= esc($errors['image'] ?? '') ?></small></div>
<?php if ($editing && $product['image']): ?><div class="current-image"><img src="<?= base_url('uploads/products/' . esc($product['image'])) ?>" alt="Current product image"><span>Current image remains unless replaced.</span></div><?php endif; ?>
<div class="form-actions"><button class="button button-primary" type="submit"><?= $editing ? 'Save changes' : 'Create product' ?></button><a class="button button-secondary" href="<?= $editing ? site_url('products/' . $product['id']) : site_url('products') ?>">Cancel</a></div>
</form>
</section>
<?= $this->endSection() ?>
