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
final class SettingsTest extends CIUnitTestCase
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
        helper('ui');
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

    public function testUpdateSavesThemeApiUrlAndKey(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('settings', [
            'app_name'            => 'My Portal',
            'theme_color'         => 'success',
            'theme_sidebar'       => 'light',
            'theme_sidebar_color' => 'success',
            'locale'              => 'th',
            'api_url'             => 'https://api.test',
            'api_key'             => 'SECRETKEY',
        ])->assertRedirectTo('/settings');

        $this->assertSame('My Portal', setting('Branding.appName'));
        $this->assertSame('success', setting('Branding.themeSidebarColor'));
        $this->assertSame('https://api.test', setting('Branding.apiUrl'));
        $this->assertSame('SECRETKEY', setting('Branding.apiKey'));
    }

    public function testInvalidApiUrlIsRejected(): void
    {
        $admin = $this->makeUser('admin', 'superadmin');
        $this->actingAs($admin)->post('settings', [
            'app_name' => 'My Portal',
            'api_url'  => 'not-a-valid-url',
        ])->assertRedirect();

        // The invalid value must never be persisted.
        $this->assertNotSame('not-a-valid-url', setting('Branding.apiUrl'));
    }

    public function testSidebarHelpersReflectSavedColor(): void
    {
        service('settings')->set('Branding.themeSidebarColor', 'success');
        $this->assertSame('success', sidebar_color());
        $this->assertSame('bg-success', sidebar_bg_class());

        service('settings')->set('Branding.themeSidebarColor', 'bogus');
        $this->assertSame('', sidebar_color());
        $this->assertSame('bg-body-secondary', sidebar_bg_class());
    }
}
