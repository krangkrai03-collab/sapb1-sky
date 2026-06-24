<?php
$isEdit    = $user !== null;
$action    = $isEdit ? site_url('users/edit/' . $user->id) : site_url('users/create');
$curGroup   = $isEdit ? (($user->getGroups()[0] ?? '')) : '';
$curStatus  = $isEdit ? ($user->isBanned() ? 'banned' : 'active') : 'active';
$curCompany = $isEdit ? ($user->company ?? 'ALL') : 'ALL';
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
						<label class="form-label"><?= lang('App.fCompany') ?></label>
						<select name="company" id="company" class="form-select" required>
							<?php foreach ($companies as $c): ?>
								<option value="<?= esc($c, 'attr') ?>" <?= old('company', $curCompany) === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php
					$whTheme = ['SKY' => 'info', 'JOJO' => 'warning'];
					foreach (['SKY', 'JOJO'] as $wc):
						$field    = 'warehouse_' . strtolower($wc);
						$selected = (int) old($field, $boundWh[$wc] ?? 0);
					?>
						<div class="mb-3 wh-block" data-company="<?= $wc ?>">
							<label class="form-label"><span class="badge text-bg-<?= $whTheme[$wc] ?> me-1"><?= $wc ?></span> <?= lang('App.bindWarehouse') ?></label>
							<select name="<?= $field ?>" class="form-select">
								<option value="">— <?= lang('App.none') ?> —</option>
								<?php foreach (($warehouses[$wc] ?? []) as $w): ?>
									<option value="<?= (int) $w->id ?>" <?= $selected === (int) $w->id ? 'selected' : '' ?>><?= esc($w->name) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endforeach; ?>

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
<script>
	// Show warehouse selects based on the chosen company (ALL = both).
	(function () {
		var company = document.getElementById('company');
		var blocks  = document.querySelectorAll('.wh-block');
		function refresh() {
			var v = company.value;
			blocks.forEach(function (b) {
				var show = (v === 'ALL') || (v === b.getAttribute('data-company'));
				b.style.display = show ? '' : 'none';
				if (!show) { var sel = b.querySelector('select'); if (sel) { sel.value = ''; } }
			});
		}
		company.addEventListener('change', refresh);
		refresh();
	})();
</script>
