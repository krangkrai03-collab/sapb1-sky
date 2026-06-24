<?php

namespace App\Controllers\Auth;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Controllers\LoginController as ShieldLoginController;

/**
 * Custom login: a single "login" field accepts either an email or a username.
 * Detects which it is and keys the Shield credential accordingly.
 */
class LoginController extends ShieldLoginController
{
    public function loginAction(): RedirectResponse
    {
        $login    = trim((string) $this->request->getPost('login'));
        $password = (string) $this->request->getPost('password');

        if ($login === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อผู้ใช้/อีเมล และรหัสผ่าน');
        }

        $field       = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$field => $login, 'password' => $password];
        $remember    = (bool) $this->request->getPost('remember');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $result = $authenticator->remember($remember)->attempt($credentials);
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show')->withCookies();
        }

        return redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }
}
