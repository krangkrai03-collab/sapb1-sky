<?php

namespace Tests\Feature;

use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class RolesTest extends CIUnitTestCase
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

        // CSRF is enabled globally; drop it for feature POSTs in tests.
        $filters                    = config('Filters');
        $filters->globals['before'] = array_values(array_filter(
            $filters->globals['before'],
            static fn ($f) => $f !== 'csrf'
        ));
    }

    private function makeUser(string $username, string $group): User
    {
        $users = new UserModel();
        $user  = new User([
            'username' => $username,
            'email'    => $username . '@example.com',
            'password' => 'secret12345',
            'active'   => 1,
        ]);
        $users->save($user);
        $user = $users->findById($users->getInsertID());
        $user->addGroup($group);

        return $user;
    }

    public function testViewerCanSeeRolesPage(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer'); // has roles.view
        $this->actingAs($viewer)->get('roles')->assertStatus(200);
    }

    public function testEditorCannotAccessRoleManagement(): void
    {
        $editor = $this->makeUser('editor', 'editor'); // lacks roles.manage
        $this->actingAs($editor)->get('roles/create')->assertStatus(403);
    }

    public function testCreateRolePersistsToDatabaseAndAppliesPermissions(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');

        $this->actingAs($admin)->post('roles/create', [
            'key'           => 'support',
            'title'         => 'Support',
            'description'   => 'desk',
            'permissions'   => ['users.view', 'logs.view'],
        ])->assertRedirectTo('/roles');

        // Stored in the DB-backed settings (Shield reads via setting()).
        $groups = setting('AuthGroups.groups');
        $matrix = setting('AuthGroups.matrix');
        $this->assertArrayHasKey('support', $groups);
        $this->assertSame('Support', $groups['support']['title']);
        $this->assertContains('logs.view', $matrix['support']);

        // A user in the new group gets exactly those permissions.
        $sup = $this->makeUser('sup1', 'support');
        $this->assertTrue($sup->can('logs.view'));
        $this->assertTrue($sup->can('users.view'));
        $this->assertFalse($sup->can('users.delete'));
        $this->assertFalse($sup->can('settings.manage'));
    }

    public function testEditRoleUpdatesPermissions(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('roles/create', [
            'key' => 'support', 'title' => 'Support', 'permissions' => ['users.view', 'logs.view'],
        ]);

        // Remove logs.view.
        $this->actingAs($admin)->post('roles/edit/support', [
            'title' => 'Support', 'permissions' => ['users.view'],
        ])->assertRedirectTo('/roles');

        $this->assertNotContains('logs.view', setting('AuthGroups.matrix')['support']);

        $sup = $this->makeUser('sup1', 'support');
        $this->assertFalse($sup->can('logs.view'));
        $this->assertTrue($sup->can('users.view'));
    }

    public function testCannotEditSystemGroup(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->get('roles/edit/superadmin')->assertRedirectTo('/roles');
    }

    public function testCannotDeleteRoleThatHasUsers(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('roles/create', [
            'key' => 'support', 'title' => 'Support', 'permissions' => ['users.view'],
        ]);
        $this->makeUser('sup1', 'support');

        $this->actingAs($admin)->post('roles/delete/support')->assertRedirectTo('/roles');

        // Still present (delete was blocked).
        $this->assertArrayHasKey('support', setting('AuthGroups.groups'));
    }

    public function testDeleteEmptyRole(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('roles/create', [
            'key' => 'temp', 'title' => 'Temp', 'permissions' => [],
        ]);
        $this->assertArrayHasKey('temp', setting('AuthGroups.groups'));

        $this->actingAs($admin)->post('roles/delete/temp')->assertRedirectTo('/roles');
        $this->assertArrayNotHasKey('temp', setting('AuthGroups.groups'));
    }
}
