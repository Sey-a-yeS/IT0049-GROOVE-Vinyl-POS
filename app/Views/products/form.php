<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $editing = $product !== null; $errors = session('errors') ?? []; $imageSrc = $editing && $product['image'] ? base_url('uploads/products/' . $product['image']) : base_url('assets/images/product-placeholder.svg'); ?>
<header class="page-header"><div class="page-header-copy"><p class="eyebrow">Catalog &amp; inventory</p><h1><?= $editing ? 'Edit product' : 'New product' ?></h1><p>Use accurate pricing, whole-number stock quantities, and clear square imagery.</p></div></header>
<section class="panel form-panel">
<form action="<?= $editing ? site_url('products/' . $product['id']) : site_url('products') ?>" method="post" enctype="multipart/form-data" class="form-stack" novalidate>
<?= csrf_field() ?>
<div class="field"><label for="name">Product name</label><input id="name" name="name" maxlength="100" value="<?= esc(old('name', $product['name'] ?? '')) ?>" required <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>><small class="field-error" id="name-error"><?= esc($errors['name'] ?? '') ?></small></div>
<div class="form-grid">
    <div class="field"><label for="price">Price in PHP</label><input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="<?= esc(old('price', $product['price'] ?? '')) ?>" required <?= isset($errors['price']) ? 'aria-invalid="true" aria-describedby="price-error"' : '' ?>><small class="field-error" id="price-error"><?= esc($errors['price'] ?? '') ?></small></div>
    <div class="field"><label for="stock_quantity">Stock quantity</label><input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" value="<?= esc(old('stock_quantity', $product['stock_quantity'] ?? '0')) ?>" required <?= isset($errors['stock_quantity']) ? 'aria-invalid="true" aria-describedby="stock-error"' : '' ?>><small class="field-error" id="stock-error"><?= esc($errors['stock_quantity'] ?? '') ?></small></div>
</div>
<div class="field"><label for="image">Product image <span>optional</span></label><input id="image" name="image" type="file" accept="image/jpeg,image/png" data-image-input data-preview-target="product-image-preview" <?= isset($errors['image']) ? 'aria-invalid="true" aria-describedby="image-help image-error"' : 'aria-describedby="image-help"' ?>><small id="image-help">Genuine JPG or PNG, up to 2 MB. GROOVE prepares an 800 × 800 image.</small><small class="field-error" id="image-error"><?= esc($errors['image'] ?? '') ?></small></div>
<div class="upload-preview"><img id="product-image-preview" src="<?= esc($imageSrc) ?>" alt="<?= $editing && $product['image'] ? 'Current product image' : 'Product image preview' ?>"><div><strong><?= $editing && $product['image'] ? 'Current image' : 'Image preview' ?></strong><span><?= $editing ? 'The current image remains unless you choose a replacement.' : 'A local GROOVE sleeve illustration is used when no image is uploaded.' ?></span></div></div>
<div class="form-actions"><button class="button button-primary" type="submit"><?= $editing ? 'Save changes' : 'Create product' ?></button><a class="button button-secondary" href="<?= $editing ? site_url('products/' . $product['id']) : site_url('products') ?>">Cancel</a></div>
</form>
</section>
<?= $this->endSection() ?>
