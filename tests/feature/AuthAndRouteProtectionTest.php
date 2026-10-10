<?php

use App\Database\Seeds\GrooveSeeder;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class AuthAndRouteProtectionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $basePath = APPPATH . 'Database';
    protected $seed = GrooveSeeder::class;

    public function testProtectedPageRedirectsAnonymousVisitor(): void
    {
        $result = $this->get('/products');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testValidCredentialsCreateAuthenticatedSession(): void
    {
        $user = (new UserModel())->where('username', 'grooveadmin')->first();
        (new UserModel())->update($user['id'], ['password' => password_hash('Valid-test-password!', PASSWORD_DEFAULT)]);
        [$tokenName, $postedHash, $sessionHash] = $this->csrfValues();

        $result = $this->withSession([$tokenName => $sessionHash])->post('/login', [
            $tokenName => $postedHash,
            'username' => 'grooveadmin',
            'password' => 'Valid-test-password!',
        ]);

        $result->assertRedirectTo(site_url('dashboard'));
        $result->assertSessionHas('staff_id', (int) $user['id']);
        $result->assertSessionHas('staff_username', 'grooveadmin');
    }

    public function testInvalidCredentialsDoNotAuthenticate(): void
    {
        [$tokenName, $postedHash, $sessionHash] = $this->csrfValues();
        $result = $this->withSession([$tokenName => $sessionHash])->post('/login', [
            $tokenName => $postedHash,
            'username' => 'grooveadmin',
            'password' => 'incorrect-password',
        ]);

        $result->assertRedirect();
        $result->assertSessionMissing('staff_id');
        $result->assertSessionHas('error', 'The username or password is incorrect.');
    }

    public function testArchivedStaffCannotAuthenticate(): void
    {
        $user = (new UserModel())->where('username', 'vinylclerk')->first();
        $password = 'Archived-' . bin2hex(random_bytes(12));
        (new UserModel())->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_archived' => true,
        ]);
        [$tokenName, $postedHash, $sessionHash] = $this->csrfValues();

        $result = $this->withSession([$tokenName => $sessionHash])->post('/login', [
            $tokenName => $postedHash,
            'username' => 'vinylclerk',
            'password' => $password,
        ]);

        $result->assertRedirect();
        $result->assertSessionMissing('staff_id');
        $result->assertSessionHas('error', 'The username or password is incorrect.');
    }

    public function testAnonymousPostCannotCreateCustomer(): void
    {
        $before = $this->db->table('customers')->countAllResults();
        [$tokenName, $postedHash, $sessionHash] = $this->csrfValues();
        $result = $this->withSession([$tokenName => $sessionHash])->post('/customers', [
            $tokenName => $postedHash,
            'full_name' => 'Unauthorized Customer',
            'email' => 'unauthorized@example.com',
        ]);

        $result->assertRedirectTo(site_url('login'));
        $this->assertSame($before, $this->db->table('customers')->countAllResults());
    }

    private function csrfValues(): array
    {
        $security = service('security');
        $tokenName = $security->getTokenName();
        $postedHash = $security->getHash();
        $sessionHash = session()->get($tokenName);

        return [$tokenName, $postedHash, $sessionHash];
    }
}
