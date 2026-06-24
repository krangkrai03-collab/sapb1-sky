<div class="row">
	<div class="col-lg-6">
		<form action="<?= site_url('profile') ?>" method="post" enctype="multipart/form-data">
			<?= csrf_field() ?>
			<div class="card">
				<div class="card-header"><h3 class="card-title"><i class="fas fa-id-card me-1"></i> <?= lang('App.profileInfo') ?></h3></div>
				<div class="card-body">
					<?php $current = old('avatar', $user->avatar); ?>
					<div class="mb-3 text-center">
						<i id="avatarPreview" class="<?= esc(avatar_icon($current), 'attr') ?> text-<?= esc(avatar_color($current), 'attr') ?>" style="font-size:96px;"></i>
					</div>
					<div class="mb-3">
						<label class="form-label d-block"><?= lang('App.avatar') ?></label>
						<style>
							.avatar-pick { cursor:pointer; display:inline-flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:50%; border:2px solid transparent; box-shadow:0 0 0 1px rgba(0,0,0,.15); margin:0 .4rem .4rem 0; font-size:24px; transition:box-shadow .15s; }
							.avatar-opt input:checked + .avatar-pick { border-color:currentColor; box-shadow:0 0 0 4px rgba(0,0,0,.08); }
						</style>
						<label class="avatar-opt" title="<?= esc(lang('App.avatarDefault'), 'attr') ?>">
							<input type="radio" name="avatar" value="" class="d-none avatar-radio" data-icon="fas fa-user-circle" data-color="secondary" <?= $current === '' || $current === null ? 'checked' : '' ?>>
							<span class="avatar-pick text-secondary"><i class="fas fa-user-circle"></i></span>
						</label>
						<?php foreach (avatar_icons() as $icon => $color): ?>
							<label class="avatar-opt">
								<input type="radio" name="avatar" value="<?= esc($icon, 'attr') ?>" class="d-none avatar-radio" data-icon="<?= esc($icon, 'attr') ?>" data-color="<?= esc($color, 'attr') ?>" <?= $current === $icon ? 'checked' : '' ?>>
								<span class="avatar-pick text-<?= esc($color, 'attr') ?>"><i class="<?= esc($icon, 'attr') ?>"></i></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fName') ?></label>
						<input type="text" name="name" class="form-control" value="<?= esc(old('name', $user->name)) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fUsername') ?></label>
						<input type="text" name="username" class="form-control" value="<?= esc(old('username', $user->username)) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fEmail') ?></label>
						<input type="email" name="email" class="form-control" value="<?= esc(old('email', $user->email)) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.langPref') ?></label>
						<?php $ul = old('locale', $user->locale ?: service('request')->getLocale()); ?>
						<select name="locale" class="form-select" style="max-width:240px">
							<option value="th" <?= $ul === 'th' ? 'selected' : '' ?>><?= lang('App.thai') ?></option>
							<option value="en" <?= $ul === 'en' ? 'selected' : '' ?>><?= lang('App.english') ?></option>
						</select>
					</div>
					<div class="mb-0">
						<label class="form-label text-body-secondary"><?= lang('App.fGroup') ?></label>
						<div><?php foreach ($user->getGroups() as $g): ?><span class="badge text-bg-info"><?= esc($g) ?></span> <?php endforeach; ?>
							<small class="text-body-secondary ms-1"><?= lang('App.groupReadonly') ?></small></div>
					</div>
				</div>
				<div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.saveProfile') ?></button></div>
			</div>
		</form>
	</div>

	<div class="col-lg-6">
		<form action="<?= site_url('profile/password') ?>" method="post">
			<?= csrf_field() ?>
			<div class="card">
				<div class="card-header"><h3 class="card-title"><i class="fas fa-key me-1"></i> <?= lang('App.changePassword') ?></h3></div>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label"><?= lang('App.currentPassword') ?></label>
						<input type="password" name="current_password" class="form-control" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.newPassword') ?></label>
						<input type="password" name="new_password" class="form-control" required>
						<small class="text-body-secondary"><?= lang('App.min8') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.confirmPassword') ?></label>
						<input type="password" name="confirm_password" class="form-control" required>
					</div>
				</div>
				<div class="card-footer"><button type="submit" class="btn btn-warning"><i class="fas fa-key me-1"></i> <?= lang('App.changePassword') ?></button></div>
			</div>
		</form>
	</div>
</div>
<script>
	// Live-preview the selected avatar icon.
	(function () {
		var preview = document.getElementById('avatarPreview');
		document.querySelectorAll('.avatar-radio').forEach(function (r) {
			r.addEventListener('change', function () {
				if (preview) { preview.className = r.getAttribute('data-icon') + ' text-' + r.getAttribute('data-color'); }
			});
		});
	})();
</script>
