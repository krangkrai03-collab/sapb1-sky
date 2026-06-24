<div class="card">
	<div class="card-header d-flex align-items-center">
		<h3 class="card-title"><?= lang('App.roleList') ?></h3>
		<?php if (user_can('roles.manage')): ?>
			<a href="<?= site_url('roles/create') ?>" class="btn btn-primary btn-sm ms-auto"><i class="fas fa-plus me-1"></i> <?= lang('App.addRole') ?></a>
		<?php endif; ?>
	</div>
	<div class="card-body table-responsive p-0">
		<table class="table table-hover mb-0">
			<thead><tr><th><?= lang('App.roleCode') ?></th><th><?= lang('App.fName') ?></th><th><?= lang('App.fDescription') ?></th><th><?= lang('App.permCount') ?></th><th><?= lang('App.userCount') ?></th><th class="text-end"><?= lang('App.actions') ?></th></tr></thead>
			<tbody>
				<?php foreach ($groups as $key => $info): ?>
					<?php $isProtected = in_array($key, $protected, true); ?>
					<tr>
						<td><span class="badge text-bg-info"><?= esc($key) ?></span> <?php if ($isProtected): ?><span class="badge text-bg-dark"><?= lang('App.system') ?></span><?php endif; ?></td>
						<td><?= esc($info['title']) ?></td>
						<td class="text-body-secondary"><?= esc($info['description'] ?? '') ?></td>
						<td><span class="badge text-bg-secondary"><?= count($matrix[$key] ?? []) ?></span></td>
						<td><span class="badge text-bg-info"><?= (int) ($counts[$key] ?? 0) ?></span></td>
						<td class="text-end">
							<?php if (user_can('roles.manage') && ! $isProtected): ?>
								<a href="<?= site_url('roles/edit/' . $key) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> <?= lang('App.edit') ?></a>
								<form action="<?= site_url('roles/delete/' . $key) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('App.confirmDelete') ?>')">
									<?= csrf_field() ?>
									<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
								</form>
							<?php else: ?>
								<span class="text-body-secondary small">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="card-footer text-body-secondary small"><?= lang('App.roleManageHint') ?></div>
</div>
