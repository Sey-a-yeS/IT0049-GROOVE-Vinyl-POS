<?php

namespace Tests\Unit;

use CodeIgniter\Log\Handlers\ErrorlogHandler;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Cookie;
use Config\Database;
use Config\Logger;

final class RenderEnvironmentConfigTest extends CIUnitTestCase
{
    /** @var array<string, string|false> */
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['RENDER', 'RENDER_EXTERNAL_URL', 'GROOVE_BASE_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_SSL', 'DB_SSL_CA'] as $name) {
            $this->originalEnvironment[$name] = getenv($name);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnvironment as $name => $value) {
            if ($value === false) {
                putenv($name);
            } else {
                putenv($name . '=' . $value);
            }
        }

        parent::tearDown();
    }

    public function testRenderUrlAndSecureCookieConfiguration(): void
    {
        putenv('RENDER=true');
        putenv('RENDER_EXTERNAL_URL=https://groove-example.onrender.com');

        $app = new App();
        $cookie = new Cookie();

        $this->assertSame('https://groove-example.onrender.com/', $app->baseURL);
        $this->assertTrue($app->forceGlobalSecureRequests);
        $this->assertTrue($cookie->secure);
        $this->assertTrue($cookie->httponly);
        $this->assertSame('Lax', $cookie->samesite);
    }

    public function testExplicitBaseUrlOverridesRenderDefault(): void
    {
        putenv('RENDER=true');
        putenv('RENDER_EXTERNAL_URL=https://groove-example.onrender.com');
        putenv('GROOVE_BASE_URL=https://groove.example.com/store');

        $app = new App();

        $this->assertSame('https://groove.example.com/store/', $app->baseURL);
    }

    public function testDatabaseEnvironmentVariablesAreMappedWithoutHardCodedSecrets(): void
    {
        putenv('DB_HOST=mysql.internal');
        putenv('DB_PORT=3307');
        putenv('DB_DATABASE=groove_production');
        putenv('DB_USERNAME=groove_app');
        putenv('DB_PASSWORD=temporary-test-value');
        putenv('DB_SSL=true');
        putenv('DB_SSL_CA=/etc/secrets/aiven-ca.pem');

        $database = new Database();

        $this->assertSame('mysql.internal', $database->default['hostname']);
        $this->assertSame(3307, $database->default['port']);
        $this->assertSame('groove_production', $database->default['database']);
        $this->assertSame('groove_app', $database->default['username']);
        $this->assertSame('temporary-test-value', $database->default['password']);
        $this->assertSame([
            'ssl_ca' => '/etc/secrets/aiven-ca.pem',
            'ssl_verify' => true,
        ], $database->default['encrypt']);
    }

    public function testCriticalErrorsAreSentToPhpErrorLog(): void
    {
        $logger = new Logger();

        $this->assertArrayHasKey(ErrorlogHandler::class, $logger->handlers);
        $this->assertContains('critical', $logger->handlers[ErrorlogHandler::class]['handles']);
        $this->assertSame(ErrorlogHandler::TYPE_OS, $logger->handlers[ErrorlogHandler::class]['messageType']);
    }
}
