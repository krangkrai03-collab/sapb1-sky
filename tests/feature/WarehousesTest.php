<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\WarehouseModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class WarehousesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
        $filters                    = config('Filters');
        $filters->globals['before'] = array_values(array_filter(
            $filters->globals['before'],
            static fn ($f) => $f !== 'csrf'
        ));
    }

    private function makeUser(string $username, string $group): User
    {
        $users = new UserModel();
        $user  = new User(['username' => $username, 'email' => $username . '@example.com', 'password' => 'secret12345', 'active' => 1]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->addGroup($group);

        return $user;
    }

    public function testAdminCanViewWarehouses(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->get('warehouses')->assertStatus(200);
    }

    public function testViewerIsForbidden(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->actingAs($viewer)->get('warehouses')->assertStatus(403);
    }

    public function testSyncUnknownCompanyReturns404(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($admin)->post('warehouses/sync/NOPE');
    }

    public function testSyncWithoutApiUrlRedirectsAndAddsNothing(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('warehouses/sync/SKY')->assertRedirect();

        $this->assertSame(0, (new WarehouseModel())->where('company', 'SKY')->countAllResults());
    }

    public function testSyncWithUrlButNoEndpointRedirects(): void
    {
        service('settings')->set('Branding.apiUrlSky', 'https://example.test');
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('warehouses/sync/SKY')->assertRedirect();

        $this->assertSame(0, (new WarehouseModel())->where('company', 'SKY')->countAllResults());
    }
}
