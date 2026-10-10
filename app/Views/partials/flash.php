<?php if (session('success')): ?>
    <div class="notice notice-success" role="status"><?= esc(session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="notice notice-error" role="alert"><?= esc(session('error')) ?></div>
<?php endif; ?>
<?php if ($errors = session('errors')): ?>
    <div class="notice notice-error" role="alert">
        <strong>Please correct the highlighted fields.</strong>
        <ul>
            <?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
