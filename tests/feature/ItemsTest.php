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
        (new ItemModel())->insert(['item_code' => 'ITM-1', 'item_name' => 'Widget', 'default_warehouse' => 'WH1']);
        $admin  = $this->makeUser('admin', 'superadmin');
        $result = $this->actingAs($admin)->get('items');
        $result->assertSee('ITM-1');
        $result->assertSee('Widget');
    }

    public function testItemUomsShowOnPage(): void
    {
        $items = new ItemModel();
        $items->insert([
            'item_code'         => 'ITM-UOM',
            'item_name'         => 'Multi UoM Widget',
            'default_warehouse' => 'WH1',
            'inventory_uom'     => 'PCS',
        ]);
        $itemId = (int) $items->getInsertID();

        $uoms = new \App\Models\ItemUomModel();
        $uoms->insert(['item_id' => $itemId, 'uom_entry' => 9, 'uom_code' => 'PCS', 'base_qty' => 1, 'base_uom' => 'PCS', 'is_inventory_uom' => 1]);
        $uoms->insert(['item_id' => $itemId, 'uom_entry' => 62, 'uom_code' => '24 PCS/CTN', 'base_qty' => 24, 'base_uom' => 'PCS', 'is_inventory_uom' => 0]);

        $admin  = $this->makeUser('admin', 'superadmin');
        $result = $this->actingAs($admin)->get('items');
        $result->assertSee('ITM-UOM');
        $result->assertSee('PCS');
        $result->assertSee('24 PCS/CTN');
    }

    public function testDeletingItemCascadesUoms(): void
    {
        $items = new ItemModel();
        $items->insert(['item_code' => 'ITM-DEL', 'item_name' => 'Doomed']);
        $itemId = (int) $items->getInsertID();

        $uoms = new \App\Models\ItemUomModel();
        $uoms->insert(['item_id' => $itemId, 'uom_entry' => 9, 'uom_code' => 'PCS', 'base_qty' => 1, 'base_uom' => 'PCS', 'is_inventory_uom' => 1]);
        $this->assertSame(1, $uoms->where('item_id', $itemId)->countAllResults());

        $items->delete($itemId);
        $this->assertSame(0, (new \App\Models\ItemUomModel())->where('item_id', $itemId)->countAllResults());
    }

    public function testSyncWithoutApiUrlRedirects(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('items/sync')->assertRedirect();
        $this->assertSame(0, (new ItemModel())->countAllResults());
    }
}
