<?php
$statusTheme  = ['Open' => 'success', 'Closed' => 'secondary', 'Cancelled' => 'danger'];
$companyTheme = ['SKY' => 'info', 'JOJO' => 'warning'];
?>
<div class="card shadow-sm">
	<div class="card-header d-flex align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-right-left me-1"></i> <?= esc($title) ?></h3>
		<a href="<?= site_url('transfer-requests/create') ?>" class="btn btn-primary btn-sm ms-auto"><i class="fas fa-plus me-1"></i> <?= lang('App.itrNew') ?></a>
	</div>
	<div class="card-body pb-0">
		<div class="row g-2 mb-3">
			<div class="col-6 col-md-3">
				<div class="border rounded p-2 bg-body-secondary"><div class="small text-body-secondary"><?= lang('App.itrStatusTotal') ?></div><div class="fs-4 fw-semibold"><?= (int) $counts['total'] ?></div></div>
			</div>
			<div class="col-6 col-md-3">
				<div class="border rounded p-2 bg-body-secondary"><div class="small text-success"><?= lang('App.itrStatusOpen') ?></div><div class="fs-4 fw-semibold text-success"><?= (int) $counts['open'] ?></div></div>
			</div>
			<div class="col-6 col-md-3">
				<div class="border rounded p-2 bg-body-secondary"><div class="small text-body-secondary"><?= lang('App.itrStatusClosed') ?></div><div class="fs-4 fw-semibold"><?= (int) $counts['closed'] ?></div></div>
			</div>
			<div class="col-6 col-md-3">
				<div class="border rounded p-2 bg-body-secondary"><div class="small text-danger"><?= lang('App.itrStatusCancelled') ?></div><div class="fs-4 fw-semibold text-danger"><?= (int) $counts['cancelled'] ?></div></div>
			</div>
		</div>
	</div>
	<div class="card-body table-responsive p-0">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th><?= lang('App.itrDocNo') ?></th>
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
					<tr><td colspan="<?= $isAdmin ? 7 : 6 ?>" class="text-center text-body-secondary py-4"><i class="fas fa-inbox me-1"></i> <?= lang('App.itrEmpty') ?></td></tr>
				<?php else: foreach ($requests as $r): ?>
					<tr>
						<td>
							<a href="<?= site_url('transfer-requests/show/' . $r->id) ?>" class="fw-semibold text-decoration-none" style="font-family:var(--bs-font-monospace)"><?= esc($r->doc_no) ?></a>
							<?php if ($r->sap_doc_no): ?><div class="small text-body-secondary"><i class="fas fa-link me-1"></i><?= esc($r->sap_doc_no) ?></div><?php endif; ?>
						</td>
						<td><span class="badge text-bg-<?= $companyTheme[$r->company] ?? 'secondary' ?>"><?= esc($r->company) ?></span></td>
						<td class="text-body-secondary"><?= esc($r->from_warehouse ?: '—') ?> <i class="fas fa-arrow-right mx-1 small"></i> <?= esc($r->to_warehouse ?: '—') ?></td>
						<td><?= esc($r->posting_date ?: '—') ?></td>
						<?php if ($isAdmin): ?><td><i class="fas fa-user-circle me-1 text-body-secondary"></i><?= esc($r->created_by_name ?: '—') ?></td><?php endif; ?>
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
