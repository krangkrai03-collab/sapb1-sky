<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\ItemModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ItemsTest extends CIUnitTestCase
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

    public function testAdminCanViewItems(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->get('items')->assertStatus(200);
    }

    public function testViewerIsForbidden(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->actingAs($viewer)->get('items')->assertStatus(403);
    }

    public function testItemsShowOnPage(): void
    {
        (new ItemModel())->insert(['company' => 'SKY', 'item_code' => 'ITM-1', 'item_name' => 'Widget', 'default_warehouse' => 'WH1']);
        $admin  = $this->makeUser('admin', 'superadmin');
        $result = $this->actingAs($admin)->get('items');
        $result->assertSee('ITM-1');
        $result->assertSee('Widget');
    }

    public function testSyncWithoutApiUrlRedirects(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('items/sync/JOJO')->assertRedirect();
        $this->assertSame(0, (new ItemModel())->where('company', 'JOJO')->countAllResults());
    }

    public function testSyncUnknownCompany404(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($admin)->post('items/sync/XX');
    }
}
