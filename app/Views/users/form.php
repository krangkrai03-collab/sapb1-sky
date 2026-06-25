<?php
$isEdit    = $user !== null;
$action    = $isEdit ? site_url('users/edit/' . $user->id) : site_url('users/create');
$curGroup  = $isEdit ? (($user->getGroups()[0] ?? '')) : '';
$curStatus = $isEdit ? ($user->isBanned() ? 'banned' : 'active') : 'active';
// Bound warehouse ids to pre-select (posted values win on validation error).
$boundSel = old('warehouses') !== null ? array_map('intval', (array) old('warehouses')) : $boundWh;
?>
<div class="row">
	<div class="col-md-8 col-lg-6">
		<div class="card">
			<div class="card-header"><h3 class="card-title"><?= esc($title) ?></h3></div>
			<form action="<?= $action ?>" method="post">
				<?= csrf_field() ?>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fName') ?></label>
						<input type="text" name="name" class="form-control" value="<?= esc(old('name', $isEdit ? $user->name : '')) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fUsername') ?></label>
						<input type="text" name="username" class="form-control" value="<?= esc(old('username', $isEdit ? $user->username : '')) ?>" required>
						<small class="text-body-secondary"><?= lang('App.usernameHint') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fEmail') ?></label>
						<input type="email" name="email" class="form-control" value="<?= esc(old('email', $isEdit ? $user->email : '')) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fPassword') ?> <?= $isEdit ? '<small class="text-body-secondary">' . lang('App.passwordKeepHint') . '</small>' : '' ?></label>
						<input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
						<small class="text-body-secondary"><?= lang('App.min8') ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fGroup') ?></label>
						<select name="group" class="form-select" required>
							<option value="">— <?= lang('App.fGroup') ?> —</option>
							<?php foreach ($groups as $gkey => $info): ?>
								<option value="<?= esc($gkey, 'attr') ?>" <?= old('group', $curGroup) === $gkey ? 'selected' : '' ?>><?= esc($info['title']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mb-3">
						<label class="form-label"><?= lang('App.bindWarehouse') ?></label>
						<select name="warehouses[]" class="form-select" multiple size="6">
							<?php foreach ($warehouses as $w): ?>
								<option value="<?= (int) $w->id ?>" <?= in_array((int) $w->id, $boundSel, true) ? 'selected' : '' ?>><code><?= esc($w->code) ?></code> — <?= esc($w->name) ?></option>
							<?php endforeach; ?>
						</select>
						<small class="text-body-secondary"><?= lang('App.bindWarehouseHint') ?></small>
					</div>

					<div class="mb-3">
						<label class="form-label"><?= lang('App.status') ?></label>
						<select name="status" class="form-select">
							<option value="active" <?= old('status', $curStatus) === 'active' ? 'selected' : '' ?>><?= lang('App.active') ?></option>
							<option value="banned" <?= old('status', $curStatus) === 'banned' ? 'selected' : '' ?>><?= lang('App.suspended') ?></option>
						</select>
					</div>
				</div>
				<div class="card-footer">
					<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.save') ?></button>
					<a href="<?= site_url('users') ?>" class="btn btn-secondary"><?= lang('App.cancel') ?></a>
				</div>
			</form>
		</div>
	</div>
</div>
