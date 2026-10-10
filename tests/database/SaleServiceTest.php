<?php

use App\Database\Seeds\GrooveSeeder;
use App\Services\SaleService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class SaleServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $basePath = APPPATH . 'Database';
    protected $seed = GrooveSeeder::class;

    public function testMandatoryControlledSaleAndOversellRollback(): void
    {
        $staffId = (int) $this->db->table('users')->select('id')->where('is_archived', false)->get()->getRow('id');
        $this->db->table('products')->insert([
            'name' => 'Controlled Sales Test Record',
            'price' => '1250.00',
            'stock_quantity' => 10,
            'image' => null,
            'is_archived' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $productId = (int) $this->db->insertID();
        $service = new SaleService($this->db);

        $saleId = $service->record($productId, null, 3, $staffId);

        $product = $this->db->table('products')->where('id', $productId)->get()->getRowArray();
        $sale = $this->db->table('sales')->where('id', $saleId)->get()->getRowArray();
        $this->assertSame(7, (int) $product['stock_quantity']);
        $this->assertSame(3, (int) $sale['quantity']);
        $this->assertSame('3750.00', (string) $sale['total_price']);
        $this->assertSame($staffId, (int) $sale['sold_by']);
        $this->assertNull($sale['customer_id']);
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $productId)->countAllResults());

        try {
            $service->record($productId, null, 8, $staffId);
            $this->fail('Expected an insufficient-stock exception.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('Insufficient stock', $exception->getMessage());
        }

        $stockAfterFailure = (int) $this->db->table('products')->select('stock_quantity')->where('id', $productId)->get()->getRow('stock_quantity');
        $saleCountAfterFailure = $this->db->table('sales')->where('product_id', $productId)->countAllResults();
        $this->assertSame(7, $stockAfterFailure);
        $this->assertSame(1, $saleCountAfterFailure);
    }

    public function testInvalidAndArchivedRelationshipsAreRejectedWithoutChanges(): void
    {
        $staffId = (int) $this->db->table('users')->select('id')->where('is_archived', false)->get()->getRow('id');
        $product = $this->db->table('products')->where('stock_quantity >', 2)->get()->getRowArray();
        $before = (int) $product['stock_quantity'];
        $service = new SaleService($this->db);

        foreach ([0, -1] as $quantity) {
            try {
                $service->record((int) $product['id'], null, $quantity, $staffId);
                $this->fail('Expected an invalid-quantity exception.');
            } catch (\DomainException) {
                $this->assertSame($before, (int) $this->db->table('products')->select('stock_quantity')->where('id', $product['id'])->get()->getRow('stock_quantity'));
            }
        }

        $this->assertSame(0, $this->db->table('sales')->where('product_id', $product['id'])->countAllResults());
    }

    public function testConcurrentSalesCannotOversellInventory(): void
    {
        if (! function_exists('proc_open')) {
            $this->markTestSkipped('The proc_open function is required for the concurrency test.');
        }

        $staffId = (int) $this->db->table('users')->select('id')->where('is_archived', false)->get()->getRow('id');
        $this->db->table('products')->insert([
            'name' => 'Concurrent Sales Test Record',
            'price' => '500.00',
            'stock_quantity' => 5,
            'image' => null,
            'is_archived' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $productId = (int) $this->db->insertID();
        $processes = [];
        $worker = TESTPATH . '_support/concurrent_sale_worker.php';
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        for ($i = 0; $i < 2; $i++) {
            $process = proc_open([PHP_BINARY, $worker, (string) $productId, '4', (string) $staffId], $descriptors, $pipes, ROOTPATH);
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $processes[] = [$process, $pipes];
        }

        $statuses = [];
        foreach ($processes as [$process, $pipes]) {
            stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $statuses[] = proc_close($process);
        }
        sort($statuses);

        $this->assertSame([0, 2], $statuses, 'Exactly one competing sale should succeed.');
        $this->assertSame(1, (int) $this->db->table('products')->select('stock_quantity')->where('id', $productId)->get()->getRow('stock_quantity'));
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $productId)->countAllResults());
    }
}
