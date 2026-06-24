<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Self-service profile. Always operates on auth()->id() — never an id from
 * the request — and never touches role/status (no privilege escalation).
 */
class Profile extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function index()
    {
        return $this->render('profile/index', [
            'title' => lang('App.profile'),
            'user'  => auth()->user(),
        ]);
    }

    public function update()
    {
        $id   = (int) auth()->id();
        $user = $this->users->findById($id);

        $avatars = implode(',', array_keys(avatar_icons()));
        $rules   = [
            'name'     => ['label' => lang('App.fName'), 'rules' => 'required|max_length[150]'],
            'username' => ['label' => lang('App.fUsername'), 'rules' => "required|regex_match[/^[A-Za-z0-9._-]+$/]|max_length[100]|is_unique[users.username,id,{$id}]"],
            'email'    => ['label' => lang('App.fEmail'), 'rules' => 'required|valid_email|max_length[150]'],
            'avatar'   => ['label' => lang('App.avatar'), 'rules' => "permit_empty|in_list[{$avatars}]"],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($this->emailTaken($this->request->getPost('email'), $id)) {
            return redirect()->back()->withInput()->with('error', lang('App.emailTaken'));
        }

        $user->name     = $this->request->getPost('name');
        $user->username = $this->request->getPost('username');
        $user->email    = $this->request->getPost('email');
        if (in_array($this->request->getPost('locale'), config('App')->supportedLocales, true)) {
            $user->locale = $this->request->getPost('locale');
        }

        // Avatar is now a chosen icon class (empty = default icon).
        $avatar       = (string) $this->request->getPost('avatar');
        $user->avatar = array_key_exists($avatar, avatar_icons()) ? $avatar : null;

        $this->users->save($user);
        log_activity('profile.update', 'แก้ไขโปรไฟล์ของตนเอง');

        return redirect()->to('profile')->with('message', lang('App.profileUpdated'));
    }

    public function password()
    {
        $id   = (int) auth()->id();
        $user = $this->users->findById($id);

        $rules = [
            'current_password' => ['label' => lang('App.currentPassword'), 'rules' => 'required'],
            'new_password'     => ['label' => lang('App.newPassword'), 'rules' => 'required|min_length[8]'],
            'confirm_password' => ['label' => lang('App.confirmPassword'), 'rules' => 'required|matches[new_password]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->to('profile')->with('errors', $this->validator->getErrors());
        }

        $identity = $user->getEmailIdentity();
        if ($identity === null || ! service('passwords')->verify($this->request->getPost('current_password'), $identity->secret2)) {
            return redirect()->to('profile')->with('error', lang('App.wrongCurrentPassword'));
        }

        $user->password = $this->request->getPost('new_password');
        $this->users->save($user);
        log_activity('profile.password', 'เปลี่ยนรหัสผ่านของตนเอง');

        return redirect()->to('profile')->with('message', lang('App.passwordChanged'));
    }

    // ---- helpers ----

    private function emailTaken(string $email, int $exceptId): bool
    {
        $found = $this->users->findByCredentials(['email' => $email]);
        return $found !== null && (int) $found->id !== $exceptId;
    }
}
