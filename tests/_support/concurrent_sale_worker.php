<?php

chdir(dirname(__DIR__, 2));
require 'vendor/codeigniter4/framework/system/Test/bootstrap.php';

$productId = (int) ($argv[1] ?? 0);
$quantity = (int) ($argv[2] ?? 0);
$staffId = (int) ($argv[3] ?? 0);

try {
    $connection = \Config\Database::connect('tests', false);
    (new \App\Services\SaleService($connection))->record($productId, null, $quantity, $staffId);
    exit(0);
} catch (\DomainException) {
    exit(2);
} catch (\Throwable $exception) {
    fwrite(STDERR, $exception->getMessage());
    exit(3);
}
