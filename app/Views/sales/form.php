<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; $placeholder = base_url('assets/images/product-placeholder.svg'); ?>
<header class="page-header"><div class="page-header-copy"><p class="eyebrow">Point of sale</p><h1>Record sale</h1><p>Select an item, confirm the quantity, and review the transaction before completion.</p></div><div class="page-actions"><a class="button button-secondary" href="<?= site_url('sales/history') ?>">Sales history</a></div></header>
<div class="sale-layout">
<section class="panel form-panel" aria-labelledby="sale-form-title"><form action="<?= site_url('sales') ?>" method="post" class="form-stack" id="sale-form" novalidate>
<?= csrf_field() ?>
<input type="hidden" name="sale_token" value="<?= esc($saleToken) ?>">
<h2 id="sale-form-title" class="eyebrow">Transaction details</h2>
<div class="field"><label for="product_id">Product</label><select id="product_id" name="product_id" required <?= isset($errors['product_id']) ? 'aria-invalid="true" aria-describedby="product-error"' : '' ?>><option value="" data-image="<?= esc($placeholder) ?>">Select an in-stock product</option><?php foreach ($products as $product): ?><?php $image = $product['image'] ? base_url('uploads/products/' . $product['image']) : $placeholder; ?><option value="<?= esc($product['id']) ?>" data-price="<?= esc($product['price']) ?>" data-stock="<?= esc($product['stock_quantity']) ?>" data-image="<?= esc($image) ?>" <?= old('product_id') == $product['id'] ? 'selected' : '' ?>><?= esc($product['name']) ?> — <?= esc($product['stock_quantity']) ?> available</option><?php endforeach; ?></select><small class="field-error" id="product-error"><?= esc($errors['product_id'] ?? '') ?></small></div>
<div class="field"><label for="customer_id">Customer <span>optional</span></label><select id="customer_id" name="customer_id" <?= isset($errors['customer_id']) ? 'aria-invalid="true" aria-describedby="customer-error"' : '' ?>><option value="">Walk-in Customer</option><?php foreach ($customers as $customer): ?><option value="<?= esc($customer['id']) ?>" <?= old('customer_id') == $customer['id'] ? 'selected' : '' ?>><?= esc($customer['full_name']) ?></option><?php endforeach; ?></select><small>Leave as Walk-in Customer when no profile is needed.</small><small class="field-error" id="customer-error"><?= esc($errors['customer_id'] ?? '') ?></small></div>
<div class="field"><label for="quantity">Quantity</label><input id="quantity" name="quantity" type="number" min="1" step="1" value="<?= esc(old('quantity', '1')) ?>" required <?= isset($errors['quantity']) ? 'aria-invalid="true" aria-describedby="quantity-error"' : '' ?>><small class="field-error" id="quantity-error"><?= esc($errors['quantity'] ?? '') ?></small></div>
<p class="form-note">The database verifies the current price and locks stock before recording the sale. This preview is not the final authority.</p>
<div class="form-actions"><button class="button button-primary" type="submit" <?= $products ? '' : 'disabled' ?>>Complete sale</button><a class="button button-secondary" href="<?= site_url('dashboard') ?>">Cancel</a></div>
</form></section>
<aside class="panel sale-summary" aria-live="polite" aria-label="Sale summary">
    <div class="sale-summary-art"><img id="summary-image" src="<?= esc($placeholder) ?>" alt=""><div><p class="eyebrow">Sale summary</p><h2 id="summary-product">Choose a product</h2></div></div>
    <div class="sale-summary-body"><dl><div><dt>Available stock</dt><dd id="summary-stock">—</dd></div><div><dt>Unit price</dt><dd id="summary-price">—</dd></div><div class="summary-total"><dt>Estimated total</dt><dd id="summary-total">—</dd></div></dl><p class="summary-note" id="summary-note">Select a product to review its price and current inventory.</p></div>
</aside>
</div>
<?= $this->endSection() ?>
