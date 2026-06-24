<?php
$isEdit   = $key !== null;
$action   = $isEdit ? site_url('roles/edit/' . $key) : site_url('roles/create');
$assigned = old('permissions', $assigned);

$grouped = [];
foreach ($permissions as $pKey => $pLabel) {
    $grouped[explode('.', $pKey)[0]][$pKey] = $pLabel;
}
$groupLabels = ['admin' => lang('App.system'), 'users' => lang('App.users'), 'roles' => lang('App.roles'), 'settings' => lang('App.settings'), 'logs' => lang('App.logs')];
?>
<div class="row">
	<div class="col-lg-8">
		<form action="<?= $action ?>" method="post">
			<?= csrf_field() ?>
			<div class="card">
				<div class="card-header"><h3 class="card-title"><?= esc($title) ?></h3></div>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fKey') ?></label>
						<input type="text" name="key" class="form-control" value="<?= esc(old('key', $key)) ?>" <?= $isEdit ? 'readonly' : '' ?> required>
						<small class="text-body-secondary"><?= lang('App.keyHint') ?> <?= $isEdit ? lang('App.keyHintLocked') : '' ?></small>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fTitle') ?></label>
						<input type="text" name="title" class="form-control" value="<?= esc(old('title', $role['title'] ?? '')) ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?= lang('App.fDescription') ?></label>
						<input type="text" name="description" class="form-control" value="<?= esc(old('description', $role['description'] ?? '')) ?>">
					</div>

					<hr>
					<label class="form-label d-block mb-2"><?= lang('App.permissions') ?></label>
					<?php foreach ($grouped as $prefix => $perms): ?>
						<div class="card mb-2">
							<div class="card-header py-2"><strong><?= esc($groupLabels[$prefix] ?? $prefix) ?></strong></div>
							<div class="card-body"><div class="row">
								<?php foreach ($perms as $pKey => $pLabel): ?>
									<div class="col-md-6">
										<div class="form-check mb-2">
											<input type="checkbox" class="form-check-input" id="p_<?= esc($pKey, 'attr') ?>"
												   name="permissions[]" value="<?= esc($pKey, 'attr') ?>" <?= in_array($pKey, $assigned, true) ? 'checked' : '' ?>>
											<label class="form-check-label" for="p_<?= esc($pKey, 'attr') ?>"><?= esc($pLabel) ?> <code class="small text-body-secondary"><?= esc($pKey) ?></code></label>
										</div>
									</div>
								<?php endforeach; ?>
							</div></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="card-footer">
					<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.save') ?></button>
					<a href="<?= site_url('roles') ?>" class="btn btn-secondary"><?= lang('App.cancel') ?></a>
				</div>
			</div>
		</form>
	</div>
</div>
