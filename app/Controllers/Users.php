<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WarehouseModel;
use App\Models\UserWarehouseModel;
use CodeIgniter\Shield\Entities\User;

class Users extends BaseController
{
    /** Selectable companies for a user. */
    private const COMPANIES = ['ALL', 'SKY', 'JOJO'];

    /** Warehouse-owning companies a "company" grants access to. */
    private const COMPANY_WAREHOUSES = [
        'ALL'  => ['SKY', 'JOJO'],
        'SKY'  => ['SKY'],
        'JOJO' => ['JOJO'],
    ];

    private UserModel $users;
    private WarehouseModel $warehouses;
    private UserWarehouseModel $userWarehouses;

    public function __construct()
    {
        $this->users          = new UserModel();
        $this->warehouses     = new WarehouseModel();
        $this->userWarehouses = new UserWarehouseModel();
    }

    /** Warehouses grouped by owning company, for the form selects. */
    private function warehousesByCompany(): array
    {
        $byCompany = ['SKY' => [], 'JOJO' => []];
        foreach ($this->warehouses->orderBy('name', 'asc')->findAll() as $w) {
            $byCompany[$w->company][] = $w;
        }
        return $byCompany;
    }

    /**
     * Persist a user's warehouse bindings from the posted form, keeping only
     * warehouses whose company is allowed by the chosen company (max 1 each).
     */
    private function syncWarehouses(int $userId, string $company): void
    {
        $allowed = self::COMPANY_WAREHOUSES[$company] ?? [];
        $ids     = [];
        foreach ($allowed as $c) {
            $wid = (int) $this->request->getPost('warehouse_' . strtolower($c));
            if ($wid > 0) {
                // Verify the warehouse really belongs to that company.
                $w = $this->warehouses->find($wid);
                if ($w !== null && $w->company === $c) {
                    $ids[] = $wid;
                }
            }
        }
        $this->userWarehouses->sync($userId, $ids);
    }

    public function index()
    {
        return $this->render('users/index', [
            'title' => lang('App.users'),
            'users' => $this->users->orderBy('id', 'asc')->findAll(),
        ]);
    }

    public function create()
    {
        return $this->render('users/form', [
            'title'       => lang('App.addUser'),
            'user'        => null,
            'groups'      => setting('AuthGroups.groups'),
            'companies'   => self::COMPANIES,
            'warehouses'  => $this->warehousesByCompany(),
            'boundWh'     => [],
        ]);
    }

    public function store()
    {
        if (! $this->validateUser()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($this->emailTaken($this->request->getPost('email'))) {
            return redirect()->back()->withInput()->with('error', lang('App.emailTaken'));
        }

        $user = new User([
            'name'     => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'company'  => $this->request->getPost('company'),
            'active'   => 1,
        ]);
        $this->users->save($user);

        $user = $this->users->findById($this->users->getInsertID());
        $user->addGroup($this->request->getPost('group'));
        if ($this->request->getPost('status') === 'banned') {
            $user->ban('disabled by admin');
        }
        $this->syncWarehouses((int) $user->id, (string) $this->request->getPost('company'));

        log_activity('user.create', 'เพิ่มผู้ใช้: ' . $this->request->getPost('username'));
        return redirect()->to('users')->with('message', lang('App.userCreated'));
    }

    public function edit($id)
    {
        $user = $this->users->findById((int) $id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('users/form', [
            'title'       => lang('App.editUser'),
            'user'        => $user,
            'groups'      => setting('AuthGroups.groups'),
            'companies'   => self::COMPANIES,
            'warehouses'  => $this->warehousesByCompany(),
            'boundWh'     => $this->userWarehouses->boundByCompany((int) $user->id),
        ]);
    }

    public function update($id)
    {
        $user = $this->users->findById((int) $id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->validateUser((int) $id)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($this->emailTaken($this->request->getPost('email'), (int) $id)) {
            return redirect()->back()->withInput()->with('error', lang('App.emailTaken'));
        }

        // Guard: don't strip the last superadmin or ban them.
        $newGroup  = $this->request->getPost('group');
        $newStatus = $this->request->getPost('status');
        if ($this->isLastSuperadmin((int) $id) && ($newGroup !== 'superadmin' || $newStatus === 'banned')) {
            return redirect()->back()->withInput()->with('error', lang('App.cannotDemoteLastAdmin'));
        }

        $user->name     = $this->request->getPost('name');
        $user->username = $this->request->getPost('username');
        $user->email    = $this->request->getPost('email');
        $user->company  = $this->request->getPost('company');
        if ($this->request->getPost('password')) {
            $user->password = $this->request->getPost('password');
        }
        $this->users->save($user);

        // Sync group (single group per user in this portal).
        foreach ($user->getGroups() as $g) {
            $user->removeGroup($g);
        }
        $user->addGroup($newGroup);

        // Sync status via Shield ban/unban.
        if ($newStatus === 'banned' && ! $user->isBanned()) {
            $user->ban('disabled by admin');
        } elseif ($newStatus === 'active' && $user->isBanned()) {
            $user->unBan();
        }

        $this->syncWarehouses((int) $id, (string) $this->request->getPost('company'));

        log_activity('user.update', 'แก้ไขผู้ใช้: ' . $this->request->getPost('username'));
        return redirect()->to('users')->with('message', lang('App.userUpdated'));
    }

    public function delete($id)
    {
        $id   = (int) $id;
        $user = $this->users->findById($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($id === (int) auth()->id()) {
            return redirect()->to('users')->with('error', lang('App.cannotDeleteSelf'));
        }
        if ($this->isLastSuperadmin($id)) {
            return redirect()->to('users')->with('error', lang('App.cannotDeleteLastAdmin'));
        }

        $username = $user->username;
        $this->userWarehouses->where('user_id', $id)->delete();
        $this->users->delete($id);
        log_activity('user.delete', 'ลบผู้ใช้: ' . $username);
        return redirect()->to('users')->with('message', lang('App.userDeleted'));
    }

    // ---- helpers ----

    private function validateUser(?int $id = null): bool
    {
        $idClause = $id !== null ? ',id,' . $id : '';
        $groupKeys = implode(',', array_keys(setting('AuthGroups.groups')));
        $companies = implode(',', self::COMPANIES);

        $rules = [
            'name'     => ['label' => lang('App.fName'), 'rules' => 'required|max_length[150]'],
            'username' => ['label' => lang('App.fUsername'), 'rules' => "required|regex_match[/^[A-Za-z0-9._-]+$/]|max_length[100]|is_unique[users.username{$idClause}]"],
            'email'    => ['label' => lang('App.fEmail'), 'rules' => 'required|valid_email|max_length[150]'],
            'group'    => ['label' => lang('App.fGroup'), 'rules' => "required|in_list[{$groupKeys}]"],
            'company'  => ['label' => lang('App.fCompany'), 'rules' => "required|in_list[{$companies}]"],
            'status'   => ['label' => lang('App.status'), 'rules' => 'required|in_list[active,banned]'],
        ];
        $rules['password'] = ['label' => lang('App.fPassword'), 'rules' => ($id === null ? 'required|' : 'permit_empty|') . 'min_length[8]'];

        return $this->validate($rules);
    }

    /**
     * Is this email already used by another user? (email lives in auth_identities)
     */
    private function emailTaken(string $email, ?int $exceptId = null): bool
    {
        $found = $this->users->findByCredentials(['email' => $email]);
        return $found !== null && (int) $found->id !== (int) ($exceptId ?? 0);
    }

    private function superadminCount(): int
    {
        return db_connect()->table('auth_groups_users')->where('group', 'superadmin')->countAllResults();
    }

    private function isLastSuperadmin(int $id): bool
    {
        $user = $this->users->findById($id);
        return $user !== null && $user->inGroup('superadmin') && $this->superadminCount() <= 1;
    }
}
