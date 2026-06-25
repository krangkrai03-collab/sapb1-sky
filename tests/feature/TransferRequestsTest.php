<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\TransferRequestModel;
use App\Models\TransferRequestItemModel;
use App\Models\WarehouseModel;
use App\Models\ItemModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class TransferRequestsTest extends CIUnitTestCase
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

    /** Seed the warehouses/items master used by createRequest() for a company. */
    private function seedMaster(string $company): void
    {
        $wh = new WarehouseModel();
        foreach (['WH1', 'WH2'] as $code) {
            $wh->insert(['company' => $company, 'code' => $code, 'name' => $code]);
        }
        (new ItemModel())->insert([
            'company' => $company, 'item_code' => 'ITM-1', 'item_name' => 'Item 1', 'default_warehouse' => 'WH1',
        ]);
    }

    private function createRequest(User $actor, string $company): void
    {
        $this->seedMaster($company);
        $this->actingAs($actor)->post('transfer-requests/create', [
            'company'      => $company,
            'posting_date' => '2026-06-15',
            'items'        => [
                ['item_code' => 'ITM-1', 'quantity' => '5', 'from_warehouse' => 'WH1', 'to_warehouse' => 'WH2', 'uom' => 'PCS'],
            ],
        ]);
    }

    public function testCreatePersistsHeaderAndLine(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->createRequest($viewer, 'SKY');

        $req = (new TransferRequestModel())->orderBy('id', 'DESC')->first();
        $this->assertNotNull($req);
        $this->assertStringStartsWith('ITRS2606', $req->doc_no);
        $this->assertSame('SKY', $req->company);
        $this->assertSame('Open', $req->status);
        $this->assertSame((int) $viewer->id, (int) $req->created_by);

        $lines = (new TransferRequestItemModel())->where('request_id', $req->id)->findAll();
        $this->assertCount(1, $lines);
        $this->assertSame('ITM-1', $lines[0]->item_code);
    }

    public function testRequestWithoutLinesIsRejected(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->actingAs($viewer)->post('transfer-requests/create', [
            'company'      => 'SKY',
            'posting_date' => '2026-06-15',
        ])->assertRedirect();

        $this->assertSame(0, (new TransferRequestModel())->countAllResults());
    }

    public function testCrossCompanyWarehouseRejected(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->seedMaster('SKY'); // WH1/WH2 + ITM-1 belong to SKY only

        // SKY document, but the line references a warehouse that isn't in SKY's master.
        $this->actingAs($viewer)->post('transfer-requests/create', [
            'company'      => 'SKY',
            'posting_date' => '2026-06-15',
            'items'        => [
                ['item_code' => 'ITM-1', 'quantity' => '5', 'from_warehouse' => 'JOJO-WH', 'to_warehouse' => 'WH2', 'uom' => 'PCS'],
            ],
        ])->assertRedirect();

        $this->assertSame(0, (new TransferRequestModel())->countAllResults());
    }

    public function testUnknownItemRejected(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->seedMaster('SKY');

        // Item code that does not exist in the company master must be refused.
        $this->actingAs($viewer)->post('transfer-requests/create', [
            'company'      => 'SKY',
            'posting_date' => '2026-06-15',
            'items'        => [
                ['item_code' => 'GHOST-ITEM', 'quantity' => '5', 'from_warehouse' => 'WH1', 'to_warehouse' => 'WH2', 'uom' => 'PCS'],
            ],
        ])->assertRedirect();

        $this->assertSame(0, (new TransferRequestModel())->countAllResults());
    }

    public function testDocNumbersAreSeparatePerCompany(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->createRequest($viewer, 'SKY');
        $this->createRequest($viewer, 'JOJO');

        $m   = new TransferRequestModel();
        $sky = $m->where('company', 'SKY')->first();
        $jojo = $m->where('company', 'JOJO')->first();

        $this->assertSame('ITRS26060001', $sky->doc_no);
        $this->assertSame('ITRJ26060001', $jojo->doc_no);
    }

    public function testNonAdminCannotViewOthersRequest(): void
    {
        $owner = $this->makeUser('owner', 'viewer');
        $this->createRequest($owner, 'SKY');
        $req = (new TransferRequestModel())->orderBy('id', 'DESC')->first();

        $other = $this->makeUser('other', 'viewer');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($other)->get('transfer-requests/show/' . $req->id);
    }

    public function testAdminCanViewAnyRequest(): void
    {
        $owner = $this->makeUser('owner', 'viewer');
        $this->createRequest($owner, 'SKY');
        $req = (new TransferRequestModel())->orderBy('id', 'DESC')->first();

        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->get('transfer-requests/show/' . $req->id)->assertStatus(200);
    }

    public function testSendToSapMarksSynced(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->createRequest($viewer, 'SKY');
        $req = (new TransferRequestModel())->orderBy('id', 'DESC')->first();

        $this->actingAs($viewer)->post('transfer-requests/send/' . $req->id)->assertRedirect();

        $fresh = (new TransferRequestModel())->find($req->id);
        $this->assertSame('sent', $fresh->sync_status);
        $this->assertNotEmpty($fresh->sap_doc_no);
    }

    public function testCannotDeleteRequestAlreadySentToSap(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->createRequest($viewer, 'SKY');
        $req = (new TransferRequestModel())->orderBy('id', 'DESC')->first();
        $this->actingAs($viewer)->post('transfer-requests/send/' . $req->id);

        $this->actingAs($viewer)->post('transfer-requests/delete/' . $req->id)->assertRedirect();
        $this->assertNotNull((new TransferRequestModel())->find($req->id));
    }

    public function testDocNoPreviewEndpointReturnsJson(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $result = $this->actingAs($viewer)->get('transfer-requests/next-doc-no?company=JOJO&date=2026-06-15');
        $result->assertStatus(200);
        $this->assertSame('ITRJ26060001', json_decode($result->getJSON())->doc_no);
    }
}
