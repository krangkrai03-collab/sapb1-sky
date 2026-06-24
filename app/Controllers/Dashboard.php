<?php

namespace App\Controllers;

use CodeIgniter\Shield\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $users = new UserModel();

        return $this->render('dashboard', [
            'title'       => lang('App.dashboard'),
            'totalUsers'  => $users->countAllResults(),
            'totalGroups' => count(setting('AuthGroups.groups')),
            'myGroups'    => auth()->user()->getGroups(),
        ]);
    }
}
