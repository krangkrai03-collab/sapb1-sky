<?php

namespace App\Controllers;

/**
 * Dynamic role (Shield group) management. Groups and the group→permission
 * matrix are stored in the DB via codeigniter4/settings (Shield reads them
 * through setting('AuthGroups.*')), so they can be edited from the web UI.
 *
 * The permission catalogue stays defined in Config\AuthGroups (it maps to
 * route filters); this UI assigns those permissions to groups.
 */
class Roles extends BaseController
{
    /** Groups that cannot be edited/deleted from the UI. */
    private const PROTECTED = ['superadmin'];

    public function index()
    {
        return $this->render('roles/index', [
            'title'       => lang('App.roles'),
            'groups'      => setting('AuthGroups.groups'),
            'matrix'      => setting('AuthGroups.matrix'),
            'permissions' => setting('AuthGroups.permissions'),
            'counts'      => $this->userCounts(),
            'protected'   => self::PROTECTED,
        ]);
    }

    public function create()
    {
        return $this->render('roles/form', [
            'title'       => lang('App.addRole'),
            'key'         => null,
            'role'        => null,
            'assigned'    => [],
            'permissions' => setting('AuthGroups.permissions'),
        ]);
    }

    public function store()
    {
        $groups = setting('AuthGroups.groups');

        $rules = [
            'key'   => ['label' => lang('App.fKey'), 'rules' => 'required|regex_match[/^[a-z0-9_-]+$/]|max_length[50]'],
            'title' => ['label' => lang('App.fTitle'), 'rules' => 'required|max_length[100]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $key = $this->request->getPost('key');
        if (isset($groups[$key])) {
            return redirect()->back()->withInput()->with('error', lang('App.keyTaken'));
        }

        $groups[$key] = [
            'title'       => $this->request->getPost('title'),
            'description' => (string) $this->request->getPost('description'),
        ];
        service('settings')->set('AuthGroups.groups', $groups);
        $this->saveMatrix($key);

        log_activity('role.create', 'เพิ่มบทบาท: ' . $key);
        return redirect()->to('roles')->with('message', lang('App.roleCreated'));
    }

    public function edit($key)
    {
        $groups = setting('AuthGroups.groups');
        if (! isset($groups[$key])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (in_array($key, self::PROTECTED, true)) {
            return redirect()->to('roles')->with('error', lang('App.cannotEditSystemRole'));
        }

        return $this->render('roles/form', [
            'title'       => lang('App.editRole'),
            'key'         => $key,
            'role'        => $groups[$key],
            'assigned'    => setting('AuthGroups.matrix')[$key] ?? [],
            'permissions' => setting('AuthGroups.permissions'),
        ]);
    }

    public function update($key)
    {
        $groups = setting('AuthGroups.groups');
        if (! isset($groups[$key])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (in_array($key, self::PROTECTED, true)) {
            return redirect()->to('roles')->with('error', lang('App.cannotEditSystemRole'));
        }
        if (! $this->validate(['title' => ['label' => lang('App.fTitle'), 'rules' => 'required|max_length[100]']])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $groups[$key] = [
            'title'       => $this->request->getPost('title'),
            'description' => (string) $this->request->getPost('description'),
        ];
        service('settings')->set('AuthGroups.groups', $groups);
        $this->saveMatrix($key);

        log_activity('role.update', 'แก้ไขบทบาท: ' . $key);
        return redirect()->to('roles')->with('message', lang('App.roleUpdated'));
    }

    public function delete($key)
    {
        $groups = setting('AuthGroups.groups');
        if (! isset($groups[$key])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (in_array($key, self::PROTECTED, true) || $key === setting('AuthGroups.defaultGroup')) {
            return redirect()->to('roles')->with('error', lang('App.cannotDeleteSystemRole'));
        }
        if (($this->userCounts()[$key] ?? 0) > 0) {
            return redirect()->to('roles')->with('error', lang('App.roleHasUsers'));
        }

        unset($groups[$key]);
        service('settings')->set('AuthGroups.groups', $groups);

        $matrix = setting('AuthGroups.matrix');
        unset($matrix[$key]);
        service('settings')->set('AuthGroups.matrix', $matrix);

        log_activity('role.delete', 'ลบบทบาท: ' . $key);
        return redirect()->to('roles')->with('message', lang('App.roleDeleted'));
    }

    // ---- helpers ----

    /** Persist the posted permission checkboxes for $key into the matrix. */
    private function saveMatrix(string $key): void
    {
        $valid    = array_keys(setting('AuthGroups.permissions'));
        $selected = array_values(array_intersect($valid, (array) $this->request->getPost('permissions')));

        $matrix       = setting('AuthGroups.matrix');
        $matrix[$key] = $selected;
        service('settings')->set('AuthGroups.matrix', $matrix);
    }

    /** @return array<string,int> group => active user count */
    private function userCounts(): array
    {
        $rows = db_connect()->table('auth_groups_users')
            ->select('`group` AS g, COUNT(*) AS c')
            ->groupBy('`group`')
            ->get()->getResultArray();

        return array_column($rows, 'c', 'g');
    }
}
