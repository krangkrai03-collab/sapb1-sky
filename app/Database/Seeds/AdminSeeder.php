<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $users = new UserModel();

        // Skip if the admin already exists (idempotent).
        if ($users->where('username', 'admin')->first() !== null) {
            return;
        }

        $user = new User([
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'secret12345',
        ]);

        $users->save($user);

        $user = $users->findById($users->getInsertID());
        $user->addGroup('superadmin');
    }
}
