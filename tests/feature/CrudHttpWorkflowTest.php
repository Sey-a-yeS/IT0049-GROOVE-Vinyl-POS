<?php

use App\Database\Seeds\GrooveSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CrudHttpWorkflowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $basePath = APPPATH . 'Database';
    protected $seed = GrooveSeeder::class;

    public function testProductCreateUpdateAndArchiveThroughProtectedHttpActions(): void
    {
        $create = $this->authenticatedPost('/products', [
            'name' => 'HTTP Test Product',
            'price' => '1234.50',
            'stock_quantity' => '9',
        ]);
        $create->assertRedirectTo(site_url('products'));
        $product = $this->db->table('products')->where('name', 'HTTP Test Product')->get()->getRowArray();
        $this->assertSame('1234.50', $product['price']);
        $this->db->table('products')->where('id', $product['id'])->update(['image' => 'existing-product.jpg']);

        $update = $this->authenticatedPost('/products/' . $product['id'], [
            'name' => 'HTTP Test Product Updated',
            'price' => '1300.00',
            'stock_quantity' => '7',
        ]);
        $update->assertRedirectTo(site_url('products/' . $product['id']));
        $updatedProduct = $this->db->table('products')->where('id', $product['id'])->get()->getRowArray();
        $this->assertSame(7, (int) $updatedProduct['stock_quantity']);
        $this->assertSame('existing-product.jpg', $updatedProduct['image']);

        $archive = $this->authenticatedPost('/products/' . $product['id'] . '/archive');
        $archive->assertRedirectTo(site_url('products'));
        $this->assertSame(1, (int) $this->db->table('products')->select('is_archived')->where('id', $product['id'])->get()->getRow('is_archived'));
    }

    public function testCustomerCreateUpdateAndArchiveThroughProtectedHttpActions(): void
    {
        $create = $this->authenticatedPost('/customers', [
            'full_name' => 'HTTP Test Customer',
            'email' => 'http.customer@example.com',
            'phone' => '+63 917 555 0199',
        ]);
        $create->assertRedirectTo(site_url('customers'));
        $customer = $this->db->table('customers')->where('email', 'http.customer@example.com')->get()->getRowArray();
        $this->assertSame('HTTP Test Customer', $customer['full_name']);

        $update = $this->authenticatedPost('/customers/' . $customer['id'], [
            'full_name' => 'HTTP Customer Updated',
            'email' => 'http.customer@example.com',
            'phone' => '',
        ]);
        $update->assertRedirectTo(site_url('customers/' . $customer['id']));
        $this->assertSame('HTTP Customer Updated', $this->db->table('customers')->select('full_name')->where('id', $customer['id'])->get()->getRow('full_name'));

        $archive = $this->authenticatedPost('/customers/' . $customer['id'] . '/archive');
        $archive->assertRedirectTo(site_url('customers'));
        $this->assertSame(1, (int) $this->db->table('customers')->select('is_archived')->where('id', $customer['id'])->get()->getRow('is_archived'));
    }

    public function testStaffCreateUpdateAndArchiveThroughProtectedHttpActions(): void
    {
        $create = $this->authenticatedPost('/staff', [
            'username' => 'httpstaff',
            'full_name' => 'HTTP Test Staff',
            'password' => 'Strong-test-password!',
        ]);
        $create->assertRedirectTo(site_url('staff'));
        $staff = $this->db->table('users')->where('username', 'httpstaff')->get()->getRowArray();
        $this->assertTrue(password_verify('Strong-test-password!', $staff['password']));
        $this->db->table('users')->where('id', $staff['id'])->update(['avatar' => 'existing-avatar.png']);

        $update = $this->authenticatedPost('/staff/' . $staff['id'], [
            'username' => 'httpstaff',
            'full_name' => 'HTTP Staff Updated',
            'password' => '',
        ]);
        $update->assertRedirectTo(site_url('staff/' . $staff['id']));
        $updated = $this->db->table('users')->where('id', $staff['id'])->get()->getRowArray();
        $this->assertSame('HTTP Staff Updated', $updated['full_name']);
        $this->assertSame($staff['password'], $updated['password']);
        $this->assertSame('existing-avatar.png', $updated['avatar']);

        $archive = $this->authenticatedPost('/staff/' . $staff['id'] . '/archive');
        $archive->assertRedirectTo(site_url('staff'));
        $this->assertSame(1, (int) $this->db->table('users')->select('is_archived')->where('id', $staff['id'])->get()->getRow('is_archived'));
    }

    public function testProductValidationRejectsNegativeInventory(): void
    {
        $before = $this->db->table('products')->countAllResults();
        $result = $this->authenticatedPost('/products', [
            'name' => 'Invalid Product',
            'price' => '-1.00',
            'stock_quantity' => '-2',
        ]);
        $result->assertRedirect();
        $result->assertSessionHas('errors');
        $this->assertSame($before, $this->db->table('products')->countAllResults());
    }

    public function testCurrentStaffAccountCannotBeArchived(): void
    {
        $staff = $this->db->table('users')->where('is_archived', false)->get()->getRowArray();
        $result = $this->authenticatedPost('/staff/' . $staff['id'] . '/archive');

        $result->assertRedirectTo(site_url('staff'));
        $result->assertSessionHas('error', 'You cannot archive the account currently signed in.');
        $this->assertSame(0, (int) $this->db->table('users')->select('is_archived')->where('id', $staff['id'])->get()->getRow('is_archived'));
    }

    private function authenticatedPost(string $path, array $data = [])
    {
        $staff = $this->db->table('users')->where('is_archived', false)->get()->getRowArray();
        $security = service('security');
        $tokenName = $security->getTokenName();
        $session = [
            $tokenName => session()->get($tokenName),
            'staff_id' => (int) $staff['id'],
            'staff_username' => $staff['username'],
            'staff_name' => $staff['full_name'],
        ];

        return $this->withSession($session)->post($path, [$tokenName => $security->getHash()] + $data);
    }
}
