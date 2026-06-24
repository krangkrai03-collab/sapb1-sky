<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Models\BusinessPartnerModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class BusinessPartnersTest extends CIUnitTestCase
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

    public function testAdminCanViewBusinessPartners(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->get('business-partners')->assertStatus(200);
    }

    public function testViewerIsForbidden(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');
        $this->actingAs($viewer)->get('business-partners')->assertStatus(403);
    }

    public function testPartnersShowOnPage(): void
    {
        (new BusinessPartnerModel())->insert(['company' => 'JOJO', 'bp_code' => 'BP-9', 'bp_name' => 'ACME Co', 'ship_to' => 'Bangkok']);
        $admin  = $this->makeUser('admin', 'superadmin');
        $result = $this->actingAs($admin)->get('business-partners');
        $result->assertSee('BP-9');
        $result->assertSee('ACME Co');
    }

    public function testSyncWithoutApiUrlRedirects(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('business-partners/sync/SKY')->assertRedirect();
        $this->assertSame(0, (new BusinessPartnerModel())->where('company', 'SKY')->countAllResults());
    }

    public function testSyncUnknownCompany404(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->actingAs($admin)->post('business-partners/sync/ZZ');
    }
}
