<?php $u = auth()->user(); $loc = service('request')->getLocale(); ?>
<!DOCTYPE html>
<html lang="th" data-bs-theme="<?= dark_mode() ? 'dark' : 'light' ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= isset($title) ? esc($title) . ' | ' : '' ?><?= esc(app_name()) ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.2/dist/css/adminlte.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
	<style> body, .app-content { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="layout-fixed sidebar-mini sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

	<nav class="app-header navbar navbar-expand bg-<?= esc(theme_color(), 'attr') ?>" data-bs-theme="dark">
		<div class="container-fluid">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="fas fa-bars"></i></a></li>
			</ul>
			<ul class="navbar-nav ms-auto">
				<li class="nav-item dropdown">
					<a class="nav-link" data-bs-toggle="dropdown" href="#"><i class="fas fa-globe"></i> <span class="text-uppercase"><?= esc($loc) ?></span></a>
					<div class="dropdown-menu dropdown-menu-end">
						<span class="dropdown-item-text text-body-secondary small"><?= lang('App.language') ?></span>
						<a class="dropdown-item <?= $loc === 'th' ? 'active' : '' ?>" href="<?= site_url('locale/th') ?>"><?= lang('App.thai') ?></a>
						<a class="dropdown-item <?= $loc === 'en' ? 'active' : '' ?>" href="<?= site_url('locale/en') ?>"><?= lang('App.english') ?></a>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link" data-bs-toggle="dropdown" href="#">
						<i class="<?= esc(avatar_icon($u->avatar), 'attr') ?>"></i>
						<span class="d-none d-sm-inline ms-1"><?= esc($u->username) ?></span>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<span class="dropdown-item-text small">
							<i class="fas fa-user me-1"></i><?= esc($u->username) ?><br>
							<?php foreach ($u->getGroups() as $g): ?><span class="badge text-bg-info"><?= esc($g) ?></span> <?php endforeach; ?>
						</span>
						<div class="dropdown-divider"></div>
						<a href="<?= site_url('profile') ?>" class="dropdown-item"><i class="fas fa-id-card me-2"></i> <?= lang('App.profile') ?></a>
						<div class="dropdown-divider"></div>
						<a href="<?= site_url('logout') ?>" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> <?= lang('App.logout') ?></a>
					</div>
				</li>
			</ul>
		</div>
	</nav>

	<aside class="app-sidebar <?= sidebar_bg_class() ?> shadow" data-bs-theme="<?= sidebar_theme() ?>">
		<div class="sidebar-brand">
			<a href="<?= site_url('dashboard') ?>" class="brand-link text-center">
				<i class="<?= esc(branding('logoIcon', 'fas fa-shield-halved'), 'attr') ?> ms-2"></i>
				<span class="brand-text fw-light"><?= esc(app_name()) ?></span>
			</a>
		</div>
		<div class="sidebar-wrapper">
			<nav class="mt-2">
				<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
					<li class="nav-item">
						<a href="<?= site_url('dashboard') ?>" class="nav-link <?= active_menu('dashboard') ?>"><i class="nav-icon fas fa-tachometer-alt"></i><p><?= lang('App.dashboard') ?></p></a>
					</li>
					<li class="nav-item">
						<a href="<?= site_url('transfer-requests') ?>" class="nav-link <?= active_menu('transfer-requests') ?>"><i class="nav-icon fas fa-right-left"></i><p><?= lang('App.inventoryTransferRequest') ?></p></a>
					</li>
					<?php if (user_can('settings.manage')): ?>
					<?php $masterOpen = active_menu('business-partners') || active_menu('items') || active_menu('warehouses'); ?>
					<li class="nav-item <?= $masterOpen ? 'menu-open' : '' ?>">
						<a href="#" class="nav-link <?= $masterOpen ? 'active' : '' ?>">
							<i class="nav-icon fas fa-database"></i>
							<p><?= lang('App.masterData') ?> <i class="nav-arrow fas fa-angle-right"></i></p>
						</a>
						<ul class="nav nav-treeview">
							<li class="nav-item"><a href="<?= site_url('business-partners') ?>" class="nav-link <?= active_menu('business-partners') ?>"><i class="nav-icon fas fa-handshake"></i><p><?= lang('App.businessPartners') ?></p></a></li>
							<li class="nav-item"><a href="<?= site_url('items') ?>" class="nav-link <?= active_menu('items') ?>"><i class="nav-icon fas fa-boxes-stacked"></i><p><?= lang('App.itemMaster') ?></p></a></li>
							<li class="nav-item"><a href="<?= site_url('warehouses') ?>" class="nav-link <?= active_menu('warehouses') ?>"><i class="nav-icon fas fa-warehouse"></i><p><?= lang('App.warehouses') ?></p></a></li>
						</ul>
					</li>
					<?php endif; ?>
					<?php if (user_can('users.view') || user_can('roles.view') || user_can('logs.view') || user_can('settings.manage')): ?>
					<?php $adminOpen = active_menu('users') || active_menu('roles') || active_menu('logs') || active_menu('settings'); ?>
					<li class="nav-item <?= $adminOpen ? 'menu-open' : '' ?>">
						<a href="#" class="nav-link <?= $adminOpen ? 'active' : '' ?>">
							<i class="nav-icon fas fa-screwdriver-wrench"></i>
							<p><?= lang('App.administration') ?> <i class="nav-arrow fas fa-angle-right"></i></p>
						</a>
						<ul class="nav nav-treeview">
							<?php if (user_can('users.view')): ?><li class="nav-item"><a href="<?= site_url('users') ?>" class="nav-link <?= active_menu('users') ?>"><i class="nav-icon fas fa-users"></i><p><?= lang('App.users') ?></p></a></li><?php endif; ?>
							<?php if (user_can('roles.view')): ?><li class="nav-item"><a href="<?= site_url('roles') ?>" class="nav-link <?= active_menu('roles') ?>"><i class="nav-icon fas fa-user-shield"></i><p><?= lang('App.roles') ?></p></a></li><?php endif; ?>
							<?php if (user_can('logs.view')): ?><li class="nav-item"><a href="<?= site_url('logs') ?>" class="nav-link <?= active_menu('logs') ?>"><i class="nav-icon fas fa-history"></i><p><?= lang('App.logs') ?></p></a></li><?php endif; ?>
							<?php if (user_can('settings.manage')): ?><li class="nav-item"><a href="<?= site_url('settings') ?>" class="nav-link <?= active_menu('settings') ?>"><i class="nav-icon fas fa-cog"></i><p><?= lang('App.settings') ?></p></a></li><?php endif; ?>
						</ul>
					</li>
					<?php endif; ?>
				</ul>
			</nav>
		</div>
	</aside>

	<main class="app-main">
		<div class="app-content-header"><div class="container-fluid"><h1 class="m-0 h3"><?= isset($title) ? esc($title) : '' ?></h1></div></div>
		<div class="app-content"><div class="container-fluid">
			<?php if (session('message') !== null): ?>
				<div class="alert alert-success alert-dismissible fade show"><?= esc(session('message')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
			<?php endif; ?>
			<?php if (session('error') !== null): ?>
				<div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
			<?php endif; ?>
			<?php if (session('errors') !== null): ?>
				<div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
			<?php endif; ?>
			<?= $_content ?>
		</div></div>
	</main>

	<footer class="app-footer">
		<strong><?= esc(app_name()) ?></strong><?= branding('footer') ? ' — ' . esc(branding('footer')) : '' ?>
		<div class="float-end d-none d-sm-inline"><?= esc(branding('version')) ?></div>
	</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
	// Pretty, consistent date picker (submits Y-m-d, shows d/m/Y in Gregorian).
	flatpickr('.js-datepicker', {
		altInput: true,
		altFormat: 'd/m/Y',
		dateFormat: 'Y-m-d',
		allowInput: true,
	});
</script>
</body>
</html>
