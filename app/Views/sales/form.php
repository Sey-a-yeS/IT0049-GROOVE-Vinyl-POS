<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<header class="page-header"><div><p class="eyebrow">Point of sale</p><h1>Record sale</h1><p>Inventory and totals are verified again on the server before completion.</p></div><a class="button button-secondary" href="<?= site_url('sales/history') ?>">Sales history</a></header>
<div class="sale-layout">
<section class="panel form-panel"><form action="<?= site_url('sales') ?>" method="post" class="form-stack" id="sale-form" novalidate>
<?= csrf_field() ?>
<div class="field"><label for="product_id">Product</label><select id="product_id" name="product_id" required><option value="">Select an in-stock product</option><?php foreach ($products as $product): ?><option value="<?= esc($product['id']) ?>" data-price="<?= esc($product['price']) ?>" data-stock="<?= esc($product['stock_quantity']) ?>" <?= old('product_id') == $product['id'] ? 'selected' : '' ?>><?= esc($product['name']) ?> — <?= esc($product['stock_quantity']) ?> available</option><?php endforeach; ?></select><small class="field-error"><?= esc($errors['product_id'] ?? '') ?></small></div>
<div class="field"><label for="customer_id">Customer <span>optional</span></label><select id="customer_id" name="customer_id"><option value="">Walk-in Customer</option><?php foreach ($customers as $customer): ?><option value="<?= esc($customer['id']) ?>" <?= old('customer_id') == $customer['id'] ? 'selected' : '' ?>><?= esc($customer['full_name']) ?></option><?php endforeach; ?></select><small class="field-error"><?= esc($errors['customer_id'] ?? '') ?></small></div>
<div class="field"><label for="quantity">Quantity</label><input id="quantity" name="quantity" type="number" min="1" step="1" value="<?= esc(old('quantity', '1')) ?>" required><small class="field-error"><?= esc($errors['quantity'] ?? '') ?></small></div>
<div class="form-actions"><button class="button button-primary" type="submit" <?= $products ? '' : 'disabled' ?>>Complete sale</button><a class="button button-secondary" href="<?= site_url('dashboard') ?>">Cancel</a></div>
</form></section>
<aside class="panel sale-summary" aria-live="polite"><p class="eyebrow">Sale summary</p><h2 id="summary-product">Choose a product</h2><dl><div><dt>Current stock</dt><dd id="summary-stock">—</dd></div><div><dt>Unit price</dt><dd id="summary-price">—</dd></div><div><dt>Estimated total</dt><dd id="summary-total">—</dd></div></dl><p class="summary-note">The database price is authoritative. The sale is rejected if stock is no longer sufficient.</p></aside>
</div>
<?= $this->endSection() ?>
