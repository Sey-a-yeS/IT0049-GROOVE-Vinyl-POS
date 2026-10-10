<?php

use App\Database\Seeds\GrooveSeeder;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class CrudAndSchemaTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $basePath = APPPATH . 'Database';
    protected $seed = GrooveSeeder::class;

    public function testRequiredTablesAndForeignKeysExist(): void
    {
        foreach (['products', 'customers', 'users', 'sales'] as $table) {
            $this->assertTrue($this->db->tableExists($table), $table . ' should exist');
        }

        $foreignKeys = $this->db->getForeignKeyData('sales');
        $columns = [];
        foreach ($foreignKeys as $key) {
            $columns = array_merge($columns, (array) $key->column_name);
        }
        sort($columns);
        $this->assertSame(['customer_id', 'product_id', 'sold_by'], $columns);
    }

    public function testCoreModelsCreateUpdateAndArchiveRecords(): void
    {
        $products = new ProductModel();
        $productId = $products->insert(['name' => 'Test Pressing', 'price' => '999.50', 'stock_quantity' => 6, 'is_archived' => false, 'created_at' => date('Y-m-d H:i:s')], true);
        $products->update($productId, ['stock_quantity' => 4]);
        $this->assertSame(4, (int) $products->find($productId)['stock_quantity']);
        $products->update($productId, ['is_archived' => true]);
        $this->assertSame(1, (int) $products->find($productId)['is_archived']);

        $customers = new CustomerModel();
        $customerId = $customers->insert(['full_name' => 'Test Customer', 'email' => 'customer@example.com', 'phone' => null, 'is_archived' => false, 'created_at' => date('Y-m-d H:i:s')], true);
        $customers->update($customerId, ['full_name' => 'Updated Customer']);
        $this->assertSame('Updated Customer', $customers->find($customerId)['full_name']);

        $users = new UserModel();
        $hash = password_hash('A-safe-test-password', PASSWORD_DEFAULT);
        $userId = $users->insert(['username' => 'teststaff', 'full_name' => 'Test Staff', 'password' => $hash, 'avatar' => null, 'is_archived' => false, 'created_at' => date('Y-m-d H:i:s')], true);
        $stored = $users->find($userId);
        $this->assertNotSame('A-safe-test-password', $stored['password']);
        $this->assertTrue(password_verify('A-safe-test-password', $stored['password']));
    }

    public function testSeedDataHasExpectedRangeAndValidPasswordHashes(): void
    {
        $this->assertGreaterThanOrEqual(18, $this->db->table('products')->countAllResults());
        $this->assertGreaterThanOrEqual(8, $this->db->table('customers')->countAllResults());
        $this->assertGreaterThanOrEqual(3, $this->db->table('users')->countAllResults());
        foreach ($this->db->table('users')->select('password')->get()->getResultArray() as $staff) {
            $this->assertNotSame('unknown', password_get_info($staff['password'])['algoName']);
        }
    }

    public function testPasswordCommandTargetsOneActiveAccountAndHashesTheSecret(): void
    {
        $target = (new UserModel())->where('username', 'grooveadmin')->first();
        $other = (new UserModel())->where('username', 'vinylclerk')->first();
        $otherHash = $other['password'];
        $secret = 'Qa-' . bin2hex(random_bytes(16));
        putenv('GROOVE_STAFF_PASSWORD=' . $secret);

        try {
            service('commands')->run('groove:staff-password', ['grooveadmin']);
        } finally {
            putenv('GROOVE_STAFF_PASSWORD');
        }

        $updated = (new UserModel())->find($target['id']);
        $unchanged = (new UserModel())->find($other['id']);
        $this->assertTrue(password_verify($secret, $updated['password']));
        $this->assertNotSame($secret, $updated['password']);
        $this->assertSame($otherHash, $unchanged['password']);
    }
}
