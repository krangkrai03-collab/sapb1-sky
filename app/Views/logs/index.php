<div class="card">
	<div class="card-header">
		<h3 class="card-title"><?= lang('App.recentActivity') ?></h3>
		<span class="badge text-bg-secondary float-end"><?= lang('App.totalRecords', [(int) $total]) ?></span>
	</div>
	<div class="card-body table-responsive p-0">
		<table class="table table-striped table-sm text-nowrap mb-0">
			<thead>
				<tr><th><?= lang('App.colTime') ?></th><th><?= lang('App.colUser') ?></th><th><?= lang('App.colAction') ?></th><th><?= lang('App.colDetail') ?></th><th><?= lang('App.colIp') ?></th></tr>
			</thead>
			<tbody>
				<?php if (empty($logs)): ?>
					<tr><td colspan="5" class="text-center text-body-secondary py-3"><?= lang('App.noLogs') ?></td></tr>
				<?php else: foreach ($logs as $row): ?>
					<tr>
						<td><?= esc(local_datetime($row->created_at)) ?></td>
						<td><?= $row->username ? esc($row->username) : '<span class="text-body-secondary">—</span>' ?></td>
						<td><span class="badge text-bg-info"><?= esc($row->action) ?></span></td>
						<td><?= esc($row->description) ?></td>
						<td class="text-body-secondary"><?= esc($row->ip_address) ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
		<div class="card-footer d-flex justify-content-center">
			<?= $pager->links('default', 'bootstrap5') ?>
		</div>
	<?php endif; ?>
</div>
