<?php

use App\Database\Seeds\GrooveSeeder;
use App\Models\SaleModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class SalesHttpWorkflowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $basePath = APPPATH . 'Database';
    protected $seed = GrooveSeeder::class;

    public function testSalePostUsesOneTimeTokenAndReturnsDatabaseReceipt(): void
    {
        $staff = $this->db->table('users')->where('is_archived', false)->get()->getRowArray();
        $product = $this->db->table('products')->where('is_archived', false)->where('stock_quantity >=', 2)->get()->getRowArray();
        $beforeStock = (int) $product['stock_quantity'];
        $token = bin2hex(random_bytes(24));

        $result = $this->authenticatedPost('/sales', [
            'sale_token' => $token,
            'product_id' => $product['id'],
            'customer_id' => '',
            'quantity' => 2,
        ], ['sale_form_token' => $token], $staff);

        $result->assertRedirectTo(site_url('sales/history'));
        $result->assertSessionHas('sale_receipt');
        $this->assertSame($beforeStock - 2, (int) $this->db->table('products')->select('stock_quantity')->where('id', $product['id'])->get()->getRow('stock_quantity'));
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $product['id'])->countAllResults());

        $duplicate = $this->authenticatedPost('/sales', [
            'sale_token' => $token,
            'product_id' => $product['id'],
            'customer_id' => '',
            'quantity' => 2,
        ], [], $staff);

        $duplicate->assertRedirectTo(site_url('sales/new'));
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $product['id'])->countAllResults());
        $this->assertSame($beforeStock - 2, (int) $this->db->table('products')->select('stock_quantity')->where('id', $product['id'])->get()->getRow('stock_quantity'));

        $this->db->table('products')->where('id', $product['id'])->update(['is_archived' => true]);
        $historical = (new SaleModel())->withDetails()->where('sales.product_id', $product['id'])->first();
        $this->assertSame($product['name'], $historical['product_name']);
    }

    private function authenticatedPost(string $path, array $data, array $extraSession, array $staff)
    {
        $security = service('security');
        $tokenName = $security->getTokenName();
        $session = [
            $tokenName => session()->get($tokenName),
            'staff_id' => (int) $staff['id'],
            'staff_username' => $staff['username'],
            'staff_name' => $staff['full_name'],
        ] + $extraSession;

        return $this->withSession($session)->post($path, [$tokenName => $security->getHash()] + $data);
    }
}
