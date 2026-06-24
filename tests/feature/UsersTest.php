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

    private function makeWarehouse(string $company, string $code): int
    {
        $m = new WarehouseModel();
        $m->insert(['company' => $company, 'code' => $code, 'name' => $code . ' name']);

        return $m->getInsertID();
    }

    public function testCreateUserBindsOnlyAllowedCompanyWarehouse(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $skyWh = $this->makeWarehouse('SKY', 'WS1');
        $jojoWh = $this->makeWarehouse('JOJO', 'WJ1');

        $this->actingAs($admin)->post('users/create', [
            'name'           => 'Somchai',
            'username'       => 'somchai',
            'email'          => 'somchai@example.com',
            'password'       => 'secret12345',
            'group'          => 'viewer',
            'company'        => 'JOJO',
            'status'         => 'active',
            'warehouse_sky'  => $skyWh,   // must be ignored for a JOJO user
            'warehouse_jojo' => $jojoWh,
        ])->assertRedirectTo('/users');

        $created = (new UserModel())->where('username', 'somchai')->first();
        $this->assertNotNull($created);
        $this->assertSame('JOJO', $created->company);

        $bound = (new UserWarehouseModel())->boundByCompany((int) $created->id);
        $this->assertSame([$jojoWh], array_values($bound));
        $this->assertArrayNotHasKey('SKY', $bound);
    }

    public function testCreateAllCompanyUserBindsBothWarehouses(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $skyWh = $this->makeWarehouse('SKY', 'WS1');
        $jojoWh = $this->makeWarehouse('JOJO', 'WJ1');

        $this->actingAs($admin)->post('users/create', [
            'name'           => 'All User',
            'username'       => 'alluser',
            'email'          => 'alluser@example.com',
            'password'       => 'secret12345',
            'group'          => 'viewer',
            'company'        => 'ALL',
            'status'         => 'active',
            'warehouse_sky'  => $skyWh,
            'warehouse_jojo' => $jojoWh,
        ])->assertRedirectTo('/users');

        $created = (new UserModel())->where('username', 'alluser')->first();
        $bound   = (new UserWarehouseModel())->boundByCompany((int) $created->id);

        $this->assertSame($skyWh, $bound['SKY']);
        $this->assertSame($jojoWh, $bound['JOJO']);
    }

    public function testCreateUserRejectsInvalidCompany(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('users/create', [
            'name'     => 'Bad',
            'username' => 'baduser',
            'email'    => 'bad@example.com',
            'password' => 'secret12345',
            'group'    => 'viewer',
            'company'  => 'NOPE',
            'status'   => 'active',
        ])->assertRedirect();

        $this->assertNull((new UserModel())->where('username', 'baduser')->first());
    }
}
