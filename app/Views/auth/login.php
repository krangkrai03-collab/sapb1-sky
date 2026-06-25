<?php helper(['form', 'setting', 'ui']); $loginBg = branding('loginBg'); $loginHint = branding('loginHint'); ?>
<!DOCTYPE html>
<html lang="th" data-bs-theme="<?= dark_mode() ? 'dark' : 'light' ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= lang('App.signIn') ?> | <?= esc(app_name()) ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.2/dist/css/adminlte.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap">
	<style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="login-page bg-body-secondary"
	<?php if ($loginBg): ?>style="background:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)), url('<?= esc(base_url($loginBg), 'attr') ?>') center / cover no-repeat fixed;"<?php endif; ?>>
<div class="login-box">
	<div class="login-logo">
		<i class="<?= esc(branding('logoIcon', 'fas fa-shield-halved'), 'attr') ?>"></i> <b><?= esc(app_name()) ?></b>
	</div>
	<div class="card">
		<div class="card-body login-card-body">
			<p class="login-box-msg"><?= lang('App.loginMsg') ?></p>

			<?php if (session('error') !== null): ?>
				<div class="alert alert-danger py-2"><?= esc(session('error')) ?></div>
			<?php endif; ?>
			<?php if (session('errors') !== null): ?>
				<div class="alert alert-danger py-2"><ul class="mb-0"><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
			<?php endif; ?>

			<form action="<?= site_url('login') ?>" method="post">
				<?= csrf_field() ?>
				<div class="input-group mb-3">
					<input type="text" name="login" class="form-control" placeholder="<?= esc(lang('App.loginIdPlaceholder'), 'attr') ?>" value="<?= old('login') ?>" required autofocus>
					<span class="input-group-text"><i class="fas fa-user"></i></span>
				</div>
				<div class="input-group mb-3">
					<input type="password" name="password" class="form-control" placeholder="<?= esc(lang('App.passwordPlaceholder'), 'attr') ?>" required>
					<span class="input-group-text"><i class="fas fa-lock"></i></span>
				</div>
				<?php if (config('Auth')->sessionConfig['allowRemembering'] ?? false): ?>
				<div class="form-check mb-3">
					<input type="checkbox" class="form-check-input" name="remember" id="remember" <?= old('remember') ? 'checked' : '' ?>>
					<label class="form-check-label" for="remember"><?= lang('App.rememberMe') ?></label>
				</div>
				<?php endif; ?>
				<button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-1"></i> <?= lang('App.signIn') ?></button>
			</form>

			<?php if ($loginHint !== ''): ?>
				<p class="mt-3 mb-0 text-body-secondary text-center small"><?= esc($loginHint) ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.2/dist/js/adminlte.min.js"></script>
</body>
</html>
