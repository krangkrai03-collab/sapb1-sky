<div class="card">
	<div class="card-header d-flex align-items-center">
		<h3 class="card-title"><?= lang('App.userList') ?></h3>
		<?php if (user_can('users.create')): ?>
			<a href="<?= site_url('users/create') ?>" class="btn btn-primary btn-sm ms-auto"><i class="fas fa-plus me-1"></i> <?= lang('App.addUser') ?></a>
		<?php endif; ?>
	</div>
	<div class="card-body table-responsive p-0">
		<table class="table table-hover text-nowrap mb-0">
			<thead>
				<tr><th>#</th><th><?= lang('App.fName') ?></th><th><?= lang('App.fUsername') ?></th><th><?= lang('App.fEmail') ?></th><th><?= lang('App.fGroup') ?></th><th><?= lang('App.status') ?></th><th class="text-end"><?= lang('App.actions') ?></th></tr>
			</thead>
			<tbody>
				<?php if (empty($users)): ?>
					<tr><td colspan="7" class="text-center text-body-secondary py-3"><?= lang('App.noUsers') ?></td></tr>
				<?php else: foreach ($users as $u): ?>
					<tr>
						<td><?= (int) $u->id ?></td>
						<td><?= esc($u->name ?? '—') ?></td>
						<td><code><?= esc($u->username) ?></code></td>
						<td><?= $u->email ? esc($u->email) : '<span class="text-body-secondary">—</span>' ?></td>
						<td><?php foreach ($u->getGroups() as $g): ?><span class="badge text-bg-info"><?= esc($g) ?></span> <?php endforeach; ?></td>
						<td>
							<?php if ($u->isBanned()): ?>
								<span class="badge text-bg-secondary"><?= lang('App.suspended') ?></span>
							<?php else: ?>
								<span class="badge text-bg-success"><?= lang('App.active') ?></span>
							<?php endif; ?>
						</td>
						<td class="text-end">
							<?php if (user_can('users.edit')): ?>
								<a href="<?= site_url('users/edit/' . $u->id) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
							<?php endif; ?>
							<?php if (user_can('users.delete')): ?>
								<form action="<?= site_url('users/delete/' . $u->id) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= lang('App.confirmDelete') ?>')">
									<?= csrf_field() ?>
									<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
</div>
