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
final class AccessControlTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null; // run all migrations (App + Shield + Settings)

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

    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/')->assertRedirect();
    }

    public function testLoginPageLoads(): void
    {
        $this->get('login')->assertStatus(200);
    }

    public function testSuperadminCanAccessAllAreas(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');

        $this->actingAs($admin)->get('users')->assertStatus(200);
        $this->actingAs($admin)->get('logs')->assertStatus(200);
        $this->actingAs($admin)->get('settings')->assertStatus(200);
    }

    public function testViewerCanSeeUsersButNotSettings(): void
    {
        $viewer = $this->makeUser('viewer', 'viewer');

        $this->actingAs($viewer)->get('users')->assertStatus(200);
        $this->actingAs($viewer)->get('settings')->assertStatus(403);
    }

    public function testEditorIsDeniedLogs(): void
    {
        $editor = $this->makeUser('editor', 'editor');

        $this->actingAs($editor)->get('logs')->assertStatus(403);
    }

    public function testRbacPermissionMatrix(): void
    {
        $admin  = $this->makeUser('a', 'superadmin');
        $editor = $this->makeUser('e', 'editor');
        $viewer = $this->makeUser('v', 'viewer');

        $this->assertTrue($admin->can('users.delete'));
        $this->assertTrue($admin->can('settings.manage'));

        $this->assertTrue($editor->can('users.view'));
        $this->assertTrue($editor->can('users.edit'));
        $this->assertFalse($editor->can('users.delete'));
        $this->assertFalse($editor->can('logs.view'));

        $this->assertTrue($viewer->can('users.view'));
        $this->assertFalse($viewer->can('users.create'));
        $this->assertFalse($viewer->can('settings.manage'));
    }
}
