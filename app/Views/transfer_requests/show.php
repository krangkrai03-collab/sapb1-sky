<?php
$statusTheme  = ['Open' => 'success', 'Closed' => 'secondary', 'Cancelled' => 'danger'];
$companyTheme = ['SKY' => 'info', 'JOJO' => 'warning'];
$syncTheme    = ['pending' => 'secondary', 'sending' => 'info', 'sent' => 'success', 'failed' => 'danger'];
$syncLabel    = ['pending' => lang('App.syncPending'), 'sending' => lang('App.syncSending'), 'sent' => lang('App.syncSent'), 'failed' => lang('App.syncFailed')];
$sync         = $req->sync_status ?: 'pending';
$fmtQty = static fn ($q) => rtrim(rtrim(number_format((float) $q, 3), '0'), '.');
$totalQty = 0;
foreach ($lines as $l) { $totalQty += (float) $l->quantity; }
?>
<div class="card shadow-sm">
	<div class="card-header d-flex align-items-center flex-wrap gap-2">
		<i class="fas fa-right-left text-body-secondary"></i>
		<span class="fs-5 fw-semibold" style="font-family:var(--bs-font-monospace)"><?= esc($req->doc_no) ?></span>
		<span class="badge text-bg-<?= $statusTheme[$req->status] ?? 'secondary' ?>"><?= esc($req->status) ?></span>
		<span class="badge text-bg-<?= $companyTheme[$req->company] ?? 'secondary' ?>"><?= esc($req->company) ?></span>
		<span class="badge text-bg-<?= $syncTheme[$sync] ?? 'secondary' ?>"><i class="fas fa-cloud-arrow-up me-1"></i><?= esc($syncLabel[$sync] ?? $sync) ?></span>
		<a href="<?= site_url('transfer-requests') ?>" class="btn btn-sm btn-secondary ms-auto"><i class="fas fa-arrow-left me-1"></i> <?= lang('App.back') ?></a>
	</div>
	<div class="card-body">
		<div class="row g-4">
			<!-- Business partner -->
			<div class="col-lg-7">
				<div class="text-uppercase text-body-secondary small fw-semibold mb-2"><i class="fas fa-user-tie me-1"></i> <?= lang('App.itrBusinessPartner') ?></div>
				<table class="table table-sm mb-0">
					<tr><td class="text-body-secondary" style="width:40%"><?= lang('App.itrBusinessPartner') ?></td><td><?= esc($req->business_partner ?: '—') ?></td></tr>
					<tr><td class="text-body-secondary"><?= lang('App.itrName') ?></td><td><?= esc($req->name ?: '—') ?></td></tr>
					<tr><td class="text-body-secondary"><?= lang('App.itrContactPerson') ?></td><td><?= esc($req->contact_person ?: '—') ?></td></tr>
					<tr><td class="text-body-secondary"><?= lang('App.itrShipTo') ?></td><td><?= nl2br(esc($req->ship_to ?: '—')) ?></td></tr>
					<tr><td class="text-body-secondary"><?= lang('App.itrPriceList') ?></td><td><?= esc($req->price_list ?: '—') ?></td></tr>
				</table>
			</div>
			<!-- Document panel -->
			<div class="col-lg-5">
				<div class="border rounded p-3 bg-body-secondary">
					<div class="text-uppercase text-body-secondary small fw-semibold mb-2"><i class="fas fa-file-invoice me-1"></i> <?= lang('App.itrDocNo') ?></div>
					<table class="table table-sm mb-3">
						<tr><td class="text-body-secondary" style="width:45%"><?= lang('App.itrPostingDate') ?></td><td class="text-end"><?= esc($req->posting_date ?: '—') ?></td></tr>
						<tr><td class="text-body-secondary"><?= lang('App.itrDueDate') ?></td><td class="text-end"><?= esc($req->due_date ?: '—') ?></td></tr>
						<tr><td class="text-body-secondary"><?= lang('App.itrDocumentDate') ?></td><td class="text-end"><?= esc($req->document_date ?: '—') ?></td></tr>
						<tr><td class="text-body-secondary"><?= lang('App.itrSapDoc') ?></td><td class="text-end"><?= $req->sap_doc_no ? esc($req->sap_doc_no) : '<span class="text-body-secondary">—</span>' ?></td></tr>
					</table>
					<div class="border rounded p-2 bg-body d-flex align-items-center gap-2">
						<div class="flex-fill"><div class="small text-success"><i class="fas fa-warehouse me-1"></i><?= lang('App.itrFromWh') ?></div><div class="fw-semibold"><?= esc($req->from_warehouse ?: '—') ?></div></div>
						<i class="fas fa-arrow-right text-success"></i>
						<div class="flex-fill text-end"><div class="small text-success"><?= lang('App.itrToWh') ?><i class="fas fa-warehouse ms-1"></i></div><div class="fw-semibold"><?= esc($req->to_warehouse ?: '—') ?></div></div>
					</div>
				</div>
			</div>
		</div>

		<h5 class="mt-4 mb-2"><i class="fas fa-list me-1"></i> <?= lang('App.itrContents') ?></h5>
		<div class="table-responsive border rounded">
			<table class="table table-striped table-sm align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th style="width:36px">#</th>
						<th><?= lang('App.itrItemNo') ?></th>
						<th><?= lang('App.itrItemDesc') ?></th>
						<th><?= lang('App.itrFromWh') ?></th>
						<th><?= lang('App.itrToWh') ?></th>
						<th class="text-end"><?= lang('App.itrQuantity') ?></th>
						<th><?= lang('App.itrUom') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($lines as $i => $l): ?>
						<tr>
							<td class="text-center text-body-secondary"><?= $i + 1 ?></td>
							<td><code><?= esc($l->item_code) ?></code></td>
							<td><?= esc($l->item_name) ?></td>
							<td><?= esc($l->from_warehouse ?: '—') ?></td>
							<td><?= esc($l->to_warehouse ?: '—') ?></td>
							<td class="text-end"><?= esc($fmtQty($l->quantity)) ?></td>
							<td><?= esc($l->uom ?: '—') ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot class="table-light fw-semibold">
					<tr>
						<td colspan="5" class="text-end"><?= lang('App.itrQuantity') ?></td>
						<td class="text-end"><?= esc($fmtQty($totalQty)) ?></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<?php if ($req->journal_remarks || $req->remarks): ?>
			<div class="row mt-3">
				<div class="col-md-6"><strong><?= lang('App.itrJournalRemarks') ?>:</strong> <?= esc($req->journal_remarks ?: '—') ?></div>
				<div class="col-md-6"><strong><?= lang('App.itrRemarks') ?>:</strong> <?= esc($req->remarks ?: '—') ?></div>
			</div>
		<?php endif; ?>
	</div>
	<div class="card-footer d-flex align-items-center gap-2 flex-wrap">
		<?php if ($sync === 'sent'): ?>
			<span class="badge text-bg-success py-2"><i class="fas fa-check-circle me-1"></i> <?= lang('App.syncSent') ?> · <?= esc($req->sap_doc_no) ?></span>
		<?php else: ?>
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendSapModal">
				<i class="fas fa-paper-plane me-1"></i> <?= $sync === 'failed' ? lang('App.sapRetry') : lang('App.sapSend') ?>
			</button>
			<?php if ($sync === 'failed' && $req->sync_error): ?>
				<span class="text-danger small"><i class="fas fa-triangle-exclamation me-1"></i><?= esc($req->sync_error) ?></span>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ($sync !== 'sent'): ?>
			<form action="<?= site_url('transfer-requests/delete/' . $req->id) ?>" method="post" class="ms-auto" onsubmit="return confirm('<?= esc(lang('App.confirmDelete'), 'js') ?>');">
				<?= csrf_field() ?>
				<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> <?= lang('App.delete') ?></button>
			</form>
		<?php endif; ?>
	</div>
</div>

<?php if ($sync !== 'sent'): ?>
<div class="modal fade" id="sendSapModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-paper-plane me-1"></i> <?= lang('App.sapConfirmTitle') ?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<?= lang('App.sapConfirmBody') ?>
				<div class="mt-2 fw-semibold" style="font-family:var(--bs-font-monospace)"><?= esc($req->doc_no) ?></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('App.no') ?></button>
				<form action="<?= site_url('transfer-requests/send/' . $req->id) ?>" method="post">
					<?= csrf_field() ?>
					<button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> <?= lang('App.yesSend') ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
