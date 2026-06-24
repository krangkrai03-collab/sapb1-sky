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
final class ProfileTest extends CIUnitTestCase
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

    public function testChoosingAnIconAvatarIsSaved(): void
    {
        $user = $this->makeUser('bob', 'viewer');
        $this->actingAs($user)->post('profile', [
            'name'     => 'Bob',
            'username' => 'bob',
            'email'    => 'bob@example.com',
            'avatar'   => 'fas fa-user-ninja',
        ])->assertRedirectTo('/profile');

        $fresh = (new UserModel())->findById($user->id);
        $this->assertSame('fas fa-user-ninja', $fresh->avatar);
    }

    public function testInvalidAvatarIconIsRejected(): void
    {
        $user = $this->makeUser('bob', 'viewer');
        $this->actingAs($user)->post('profile', [
            'name'     => 'Bob',
            'username' => 'bob',
            'email'    => 'bob@example.com',
            'avatar'   => 'fas fa-evil-hacker',
        ])->assertRedirect();

        $fresh = (new UserModel())->findById($user->id);
        $this->assertNull($fresh->avatar);
    }

    public function testDefaultAvatarClearsToNull(): void
    {
        $user         = $this->makeUser('bob', 'viewer');
        $user->avatar = 'fas fa-user-tie';
        (new UserModel())->save($user);

        $this->actingAs($user)->post('profile', [
            'name'     => 'Bob',
            'username' => 'bob',
            'email'    => 'bob@example.com',
            'avatar'   => '', // default
        ])->assertRedirectTo('/profile');

        $fresh = (new UserModel())->findById($user->id);
        $this->assertNull($fresh->avatar);
    }
}
