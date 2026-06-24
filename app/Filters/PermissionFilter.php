<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Permission filter: redirects guests to login, but shows a proper 403 page
 * to authenticated users who lack the required permission (instead of Shield's
 * default redirect-to-login).
 *
 * Usage: ['filter' => 'permission:users.view'] — requires ALL listed perms.
 */
class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        if (! auth()->loggedIn()) {
            session()->setTempdata('beforeLoginUrl', current_url(), 300);
            return redirect()->route('login');
        }

        $user = auth()->user();
        foreach ($arguments as $permission) {
            if (! $user->can($permission)) {
                return service('response')
                    ->setStatusCode(403)
                    ->setBody(view('errors/forbidden'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
