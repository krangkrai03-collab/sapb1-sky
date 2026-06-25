<style>
	.theme-swatch { cursor: pointer; display: inline-block; margin: 0 .4rem .4rem 0; }
	.swatch-dot { display:inline-block; width:34px; height:34px; border-radius:50%; border:3px solid transparent; box-shadow:0 0 0 1px rgba(0,0,0,.15); vertical-align:middle; transition:box-shadow .15s; }
	.theme-swatch input:checked + .swatch-dot { box-shadow: 0 0 0 3px rgba(13,110,253,.7); }
	.swatch-dot.swatch-default { background: linear-gradient(135deg, #fff 0 50%, #6c757d 50% 100%); }
</style>
<div class="row">
	<div class="col-lg-8">
		<form action="<?= site_url('settings') ?>" method="post" enctype="multipart/form-data">
			<?= csrf_field() ?>
			<div class="card">
				<div class="card-header"><h3 class="card-title"><?= lang('App.brandSettings') ?></h3></div>
				<div class="card-body">

					<div class="mb-3">
						<label class="form-label"><?= lang('App.appNameLabel') ?></label>
						<input type="text" name="app_name" class="form-control" value="<?= esc(old('app_name', app_name())) ?>" required>
						<small class="text-body-secondary"><?= lang('App.appNameHint') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.defaultLang') ?></label>
						<?php $curLocale = old('locale', branding('locale', 'th')); ?>
						<select name="locale" class="form-select" style="max-width:240px">
							<option value="th" <?= $curLocale === 'th' ? 'selected' : '' ?>><?= lang('App.thai') ?></option>
							<option value="en" <?= $curLocale === 'en' ? 'selected' : '' ?>><?= lang('App.english') ?></option>
						</select>
						<small class="text-body-secondary"><?= lang('App.defaultLangHint') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.iconLabel') ?></label>
						<div class="input-group">
							<span class="input-group-text"><i class="<?= esc(branding('logoIcon', 'fas fa-shield-halved'), 'attr') ?>"></i></span>
							<input type="text" name="logo_icon" class="form-control" value="<?= esc(old('logo_icon', branding('logoIcon'))) ?>" placeholder="fas fa-shield-halved">
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.footerLabel') ?></label>
						<input type="text" name="footer" class="form-control" value="<?= esc(old('footer', branding('footer'))) ?>">
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.versionLabel') ?></label>
						<input type="text" name="version" class="form-control" value="<?= esc(old('version', branding('version'))) ?>" placeholder="v2.0">
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.dashboardNoteLabel') ?></label>
						<input type="text" name="dashboard_note" class="form-control" value="<?= esc(old('dashboard_note', branding('dashboardNote'))) ?>">
						<small class="text-body-secondary"><?= lang('App.dashboardNoteHint') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.sessionTimeout') ?></label>
						<input type="number" name="session_timeout" class="form-control" style="max-width:240px" min="1" max="10080" value="<?= esc(old('session_timeout', branding('sessionTimeout', '120'))) ?>">
						<small class="text-body-secondary"><?= lang('App.sessionTimeoutHint') ?></small>
					</div>

					<hr>
					<h5 class="mb-3"><i class="fas fa-palette me-1"></i> <?= lang('App.themeTitle') ?></h5>
					<div class="mb-3">
						<label class="form-label d-block"><?= lang('App.accentColor') ?></label>
						<?php foreach (config('Branding')->themeColors as $val => $label): ?>
							<label class="theme-swatch" title="<?= esc($label, 'attr') ?>">
								<input type="radio" name="theme_color" value="<?= esc($val, 'attr') ?>" class="d-none" <?= old('theme_color', theme_color()) === $val ? 'checked' : '' ?>>
								<span class="swatch-dot bg-<?= esc($val, 'attr') ?>"></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label d-block"><?= lang('App.sidebarStyle') ?></label>
						<?php $sb = old('theme_sidebar', theme_sidebar()); ?>
						<div class="form-check form-check-inline">
							<input type="radio" id="sb_dark" name="theme_sidebar" value="dark" class="form-check-input" <?= $sb === 'dark' ? 'checked' : '' ?>>
							<label class="form-check-label" for="sb_dark"><?= lang('App.dark') ?></label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="sb_light" name="theme_sidebar" value="light" class="form-check-input" <?= $sb === 'light' ? 'checked' : '' ?>>
							<label class="form-check-label" for="sb_light"><?= lang('App.light') ?></label>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label d-block"><?= lang('App.sidebarColor') ?></label>
						<?php $sbColor = old('theme_sidebar_color', sidebar_color()); ?>
						<label class="theme-swatch" title="<?= esc(lang('App.sidebarDefault'), 'attr') ?>">
							<input type="radio" name="theme_sidebar_color" value="" class="d-none" <?= $sbColor === '' ? 'checked' : '' ?>>
							<span class="swatch-dot swatch-default"></span>
						</label>
						<?php foreach (config('Branding')->themeColors as $val => $label): ?>
							<label class="theme-swatch" title="<?= esc($label, 'attr') ?>">
								<input type="radio" name="theme_sidebar_color" value="<?= esc($val, 'attr') ?>" class="d-none" <?= $sbColor === $val ? 'checked' : '' ?>>
								<span class="swatch-dot bg-<?= esc($val, 'attr') ?>"></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input type="checkbox" class="form-check-input" id="dark_mode" name="dark_mode" value="1" <?= dark_mode() ? 'checked' : '' ?>>
							<label class="form-check-label" for="dark_mode"><?= lang('App.darkMode') ?></label>
						</div>
					</div>

					<hr>
					<h5 class="mb-3"><i class="fas fa-image me-1"></i> <?= lang('App.loginSection') ?></h5>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.loginBgLabel') ?></label>
						<?php if (branding('loginBg')): ?>
							<div class="mb-2">
								<img src="<?= esc(base_url(branding('loginBg')), 'attr') ?>" alt="login bg" style="max-height:100px;border-radius:6px;border:1px solid #ddd;">
								<div class="form-check mt-2">
									<input type="checkbox" class="form-check-input" id="remove_login_bg" name="remove_login_bg" value="1">
									<label class="form-check-label" for="remove_login_bg"><?= lang('App.removeLoginBg') ?></label>
								</div>
							</div>
						<?php endif; ?>
						<input type="file" name="login_bg_file" class="form-control" accept="image/*">
						<small class="text-body-secondary"><?= lang('App.chooseImage') ?> (png/jpg/gif/webp ≤ 4MB)</small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.loginHintLabel') ?></label>
						<input type="text" name="login_hint" class="form-control" value="<?= esc(old('login_hint', branding('loginHint'))) ?>">
						<small class="text-body-secondary"><?= lang('App.loginHintHint') ?></small>
					</div>

					<hr>
					<h5 class="mb-3"><i class="fas fa-plug me-1"></i> <?= lang('App.apiSection') ?></h5>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.apiUrl') ?></label>
						<input type="url" name="api_url" class="form-control" value="<?= esc(old('api_url', branding('apiUrl'))) ?>" placeholder="https://api.example.com">
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.apiKey') ?></label>
						<input type="text" name="api_key" class="form-control" value="<?= esc(old('api_key', branding('apiKey'))) ?>" placeholder="<?= esc(lang('App.apiKeyPlaceholder'), 'attr') ?>" autocomplete="off">
					</div>
				</div>
				<div class="card-footer">
					<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.save') ?></button>
					<a href="<?= site_url('dashboard') ?>" class="btn btn-secondary"><?= lang('App.cancel') ?></a>
				</div>
			</div>
		</form>

		<div class="card mt-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h3 class="card-title mb-0"><i class="fas fa-sitemap me-1"></i> <?= lang('App.endpointSection') ?></h3>
				<span class="badge text-bg-light"><?= count($endpoints) ?></span>
			</div>
			<div class="card-body">
				<small class="text-body-secondary d-block mb-3"><?= lang('App.endpointHint') ?></small>
				<ul class="list-group list-group-flush mb-3">
					<?php if (empty($endpoints)): ?>
						<li class="list-group-item text-center text-body-secondary py-3"><?= lang('App.noEndpoints') ?></li>
					<?php else: foreach ($endpoints as $e): ?>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span><span class="badge text-bg-<?= ($e->method ?? 'GET') === 'POST' ? 'success' : 'secondary' ?> me-1"><?= esc($e->method ?? 'GET') ?></span><strong><?= esc($e->name) ?></strong> <code class="ms-1"><?= esc($e->path) ?></code></span>
							<form action="<?= site_url('api-endpoints/delete/' . $e->id) ?>" method="post" onsubmit="return confirm('<?= esc(lang('App.confirmDelete'), 'js') ?>');">
								<?= csrf_field() ?>
								<button type="submit" class="btn btn-sm btn-outline-danger" title="<?= esc(lang('App.delete'), 'attr') ?>"><i class="fas fa-trash"></i></button>
							</form>
						</li>
					<?php endforeach; endif; ?>
				</ul>
				<form action="<?= site_url('api-endpoints/create') ?>" method="post" class="row g-2">
					<?= csrf_field() ?>
					<div class="col-md-4"><input type="text" name="name" class="form-control form-control-sm" placeholder="<?= esc(lang('App.endpointName'), 'attr') ?> (ItemMaster)" maxlength="100" required></div>
					<div class="col-md-2"><select name="method" class="form-select form-select-sm"><option value="GET">GET</option><option value="POST">POST</option></select></div>
					<div class="col-md-4"><input type="text" name="path" class="form-control form-control-sm" placeholder="<?= esc(lang('App.endpointPath'), 'attr') ?> (/item)" maxlength="255" required></div>
					<div class="col-md-2 d-grid"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> <?= lang('App.add') ?></button></div>
				</form>
			</div>
		</div>
	</div>
</div>
