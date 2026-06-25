<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // X-Frame-Options, X-Content-Type-Options, Referrer-Policy and HSTS are
        // set once at the Apache level (docker/security-headers.conf) so they
        // cover every response incl. redirects/errors without duplication.
        // CSP stays here (it needs the per-app CDN allowlist).
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
            . "img-src 'self' data:; frame-ancestors 'self'; base-uri 'self'; form-action 'self'";
        $response->setHeader('Content-Security-Policy', $csp);

        return $response;
    }
}
