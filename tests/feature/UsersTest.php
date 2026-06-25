<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\WarehouseModel;
use App\Models\UserWarehouseModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class UsersTest extends CIUnitTestCase
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

    private function makeWarehouse(string $code): int
    {
        $m = new WarehouseModel();
        $m->insert(['code' => $code, 'name' => $code . ' name']);

        return $m->getInsertID();
    }

    public function testCreateUserBindsSelectedWarehouses(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $wh1   = $this->makeWarehouse('W1');
        $wh2   = $this->makeWarehouse('W2');

        $this->actingAs($admin)->post('users/create', [
            'name'       => 'Somchai',
            'username'   => 'somchai',
            'email'      => 'somchai@example.com',
            'password'   => 'secret12345',
            'group'      => 'viewer',
            'status'     => 'active',
            'warehouses' => [$wh1, $wh2],
        ])->assertRedirectTo('/users');

        $created = (new UserModel())->where('username', 'somchai')->first();
        $this->assertNotNull($created);

        $bound = (new UserWarehouseModel())->boundIds((int) $created->id);
        sort($bound);
        $this->assertSame([$wh1, $wh2], $bound);
    }

    public function testCreateUserIgnoresUnknownWarehouseId(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $wh1   = $this->makeWarehouse('W1');

        $this->actingAs($admin)->post('users/create', [
            'name'       => 'All User',
            'username'   => 'alluser',
            'email'      => 'alluser@example.com',
            'password'   => 'secret12345',
            'group'      => 'viewer',
            'status'     => 'active',
            'warehouses' => [$wh1, 999999], // 999999 doesn't exist → dropped
        ])->assertRedirectTo('/users');

        $created = (new UserModel())->where('username', 'alluser')->first();
        $this->assertSame([$wh1], (new UserWarehouseModel())->boundIds((int) $created->id));
    }
}
