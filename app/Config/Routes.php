<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Shield auth routes (/logout, magic-link, ...) — login handled by our controller.
// Self-registration is removed entirely: this is an internal admin portal, so the
// 'register' route group is never published (accounts are created by an admin via
// /users). Disabling Auth::$allowRegistration alone leaves /register routed; excluding
// the group here makes GET/POST /register a hard 404 at the router.
service('auth')->routes($routes, ['except' => ['login', 'register']]);

// Quick UI language switch (per session)
$routes->get('locale/(:segment)', 'Locale::set/$1');

// Custom login (single field: username OR email).
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView', ['as' => 'login']);
$routes->post('login', '\App\Controllers\Auth\LoginController::loginAction', ['filter' => 'auth-rates']);

// Protected admin area — every route requires an authenticated session
// with the 'admin.access' permission.
$routes->group('', ['filter' => 'perm:admin.access'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('transfer-requests', 'TransferRequests::index');
    $routes->get('transfer-requests/create', 'TransferRequests::create');
    $routes->get('transfer-requests/next-doc-no', 'TransferRequests::docNoPreview');
    $routes->post('transfer-requests/create', 'TransferRequests::store');
    $routes->get('transfer-requests/show/(:num)', 'TransferRequests::show/$1');
    $routes->post('transfer-requests/send/(:num)', 'TransferRequests::send/$1');
    $routes->post('transfer-requests/delete/(:num)', 'TransferRequests::delete/$1');
});

// Roles / permissions (dynamic — stored in DB via settings)
$routes->get('roles',                  'Roles::index',  ['filter' => 'perm:roles.view']);
$routes->get('roles/create',           'Roles::create', ['filter' => 'perm:roles.manage']);
$routes->post('roles/create',          'Roles::store',  ['filter' => 'perm:roles.manage']);
$routes->get('roles/edit/(:segment)',  'Roles::edit/$1',   ['filter' => 'perm:roles.manage']);
$routes->post('roles/edit/(:segment)', 'Roles::update/$1', ['filter' => 'perm:roles.manage']);
$routes->post('roles/delete/(:segment)', 'Roles::delete/$1', ['filter' => 'perm:roles.manage']);

// Activity log
$routes->get('logs', 'Logs::index', ['filter' => 'perm:logs.view']);

// System settings (branding / theme / login)
$routes->get('settings',  'Settings::index',  ['filter' => 'perm:settings.manage']);
$routes->post('settings', 'Settings::update', ['filter' => 'perm:settings.manage']);

// API endpoints (per-company sub-paths under each Web API URL) — part of system settings
$routes->post('api-endpoints/create',        'ApiEndpoints::store',     ['filter' => 'perm:settings.manage']);
$routes->post('api-endpoints/delete/(:num)', 'ApiEndpoints::delete/$1', ['filter' => 'perm:settings.manage']);

// Warehouses — synced from SAP, part of system settings
$routes->get('warehouses',       'Warehouses::index', ['filter' => 'perm:settings.manage']);
$routes->post('warehouses/sync', 'Warehouses::sync',  ['filter' => 'perm:settings.manage']);

// Item Master — synced from SAP, part of system settings
$routes->get('items',       'Items::index', ['filter' => 'perm:settings.manage']);
$routes->post('items/sync', 'Items::sync',  ['filter' => 'perm:settings.manage']);

// Business Partners — synced from SAP, part of system settings
$routes->get('business-partners',       'BusinessPartners::index', ['filter' => 'perm:settings.manage']);
$routes->post('business-partners/sync', 'BusinessPartners::sync',  ['filter' => 'perm:settings.manage']);

// Self-service profile (any authenticated user; own account only)
$routes->get('profile',           'Profile::index',    ['filter' => 'session']);
$routes->post('profile',          'Profile::update',   ['filter' => 'session']);
$routes->post('profile/password', 'Profile::password', ['filter' => 'session']);

// User management (each action gated by its own permission)
$routes->get('users',              'Users::index',     ['filter' => 'perm:users.view']);
$routes->get('users/create',       'Users::create',    ['filter' => 'perm:users.create']);
$routes->post('users/create',      'Users::store',     ['filter' => 'perm:users.create']);
$routes->get('users/edit/(:num)',  'Users::edit/$1',   ['filter' => 'perm:users.edit']);
$routes->post('users/edit/(:num)', 'Users::update/$1', ['filter' => 'perm:users.edit']);
$routes->post('users/delete/(:num)', 'Users::delete/$1', ['filter' => 'perm:users.delete']);
