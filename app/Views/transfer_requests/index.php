<?php $statusTheme = ['Open' => 'success', 'Closed' => 'secondary', 'Cancelled' => 'danger']; ?>
<div class="card">
	<div class="card-header d-flex align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-right-left me-1"></i> <?= esc($title) ?></h3>
		<a href="<?= site_url('transfer-requests/create') ?>" class="btn btn-primary btn-sm ms-auto"><i class="fas fa-plus me-1"></i> <?= lang('App.itrNew') ?></a>
	</div>
	<div class="card-body table-responsive p-0">
		<table class="table table-striped table-hover mb-0 align-middle">
			<thead>
				<tr>
					<th><?= lang('App.itrDocNo') ?></th>
					<th><?= lang('App.itrSapDoc') ?></th>
					<th><?= lang('App.fCompany') ?></th>
					<th><?= lang('App.itrFromWh') ?> → <?= lang('App.itrToWh') ?></th>
					<th><?= lang('App.itrPostingDate') ?></th>
					<?php if ($isAdmin): ?><th><?= lang('App.itrCreatedBy') ?></th><?php endif; ?>
					<th><?= lang('App.status') ?></th>
					<th class="text-end"><?= lang('App.actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($requests)): ?>
					<tr><td colspan="<?= $isAdmin ? 8 : 7 ?>" class="text-center text-body-secondary py-4"><?= lang('App.itrEmpty') ?></td></tr>
				<?php else: foreach ($requests as $r): ?>
					<tr>
						<td><a href="<?= site_url('transfer-requests/show/' . $r->id) ?>"><strong><?= esc($r->doc_no) ?></strong></a></td>
						<td><?= $r->sap_doc_no ? esc($r->sap_doc_no) : '<span class="text-body-secondary">—</span>' ?></td>
						<td><?= esc($r->company) ?></td>
						<td><?= esc($r->from_warehouse ?: '—') ?> → <?= esc($r->to_warehouse ?: '—') ?></td>
						<td><?= esc($r->posting_date ?: '—') ?></td>
						<?php if ($isAdmin): ?><td><?= esc($r->created_by_name ?: '—') ?></td><?php endif; ?>
						<td><span class="badge text-bg-<?= $statusTheme[$r->status] ?? 'secondary' ?>"><?= esc($r->status) ?></span></td>
						<td class="text-end"><a href="<?= site_url('transfer-requests/show/' . $r->id) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
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
