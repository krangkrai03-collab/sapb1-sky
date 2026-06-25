<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\ApiEndpointModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ApiEndpointsTest extends CIUnitTestCase
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

    public function testAdminCanCreateEndpoint(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('api-endpoints/create', [
            'company' => 'SKY', 'name' => 'Warehouses', 'method' => 'GET', 'path' => '/warehouses',
        ])->assertRedirectTo('/settings');

        $row = (new ApiEndpointModel())->where('company', 'SKY')->where('name', 'Warehouses')->first();
        $this->assertNotNull($row);
        $this->assertSame('/warehouses', $row->path);
        $this->assertSame('GET', $row->method);
    }

    public function testDuplicateNameSameCompanyIsRejected(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $payload = ['company' => 'SKY', 'name' => 'ItemMaster', 'method' => 'GET', 'path' => '/item'];
        $this->actingAs($admin)->post('api-endpoints/create', $payload);
        $this->actingAs($admin)->post('api-endpoints/create', $payload);

        $this->assertSame(1, (new ApiEndpointModel())->where('company', 'SKY')->where('name', 'ItemMaster')->countAllResults());
    }

    public function testSameNameDifferentCompanyAllowed(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('api-endpoints/create', ['company' => 'SKY', 'name' => 'ItemMaster', 'method' => 'GET', 'path' => '/item']);
        $this->actingAs($admin)->post('api-endpoints/create', ['company' => 'JOJO', 'name' => 'ItemMaster', 'method' => 'POST', 'path' => '/item']);

        $this->assertSame(2, (new ApiEndpointModel())->where('name', 'ItemMaster')->countAllResults());
    }

    public function testDeleteEndpoint(): void
    {
        $model = new ApiEndpointModel();
        $model->insert(['company' => 'SKY', 'name' => 'Warehouses', 'path' => '/warehouses']);
        $id = $model->getInsertID();

        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('api-endpoints/delete/' . $id)->assertRedirectTo('/settings');

        $this->assertNull($model->find($id));
    }

    public function testViewerCannotCreateEndpoint(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->actingAs($viewer)->post('api-endpoints/create', [
            'company' => 'SKY', 'name' => 'Warehouses', 'path' => '/warehouses',
        ])->assertStatus(403);
    }
}
