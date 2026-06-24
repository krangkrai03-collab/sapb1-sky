<?php $statusTheme = ['Open' => 'success', 'Closed' => 'secondary', 'Cancelled' => 'danger']; ?>
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-right-left me-1"></i> <?= esc($req->doc_no) ?>
			<span class="badge text-bg-<?= $statusTheme[$req->status] ?? 'secondary' ?> ms-2"><?= esc($req->status) ?></span>
			<span class="badge text-bg-light ms-1"><?= esc($req->company) ?></span>
		</h3>
		<a href="<?= site_url('transfer-requests') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i> <?= lang('App.back') ?></a>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-lg-6">
				<dl class="row mb-0">
					<dt class="col-sm-4"><?= lang('App.itrBusinessPartner') ?></dt><dd class="col-sm-8"><?= esc($req->business_partner ?: '—') ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrName') ?></dt><dd class="col-sm-8"><?= esc($req->name ?: '—') ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrContactPerson') ?></dt><dd class="col-sm-8"><?= esc($req->contact_person ?: '—') ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrShipTo') ?></dt><dd class="col-sm-8"><?= nl2br(esc($req->ship_to ?: '—')) ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrPriceList') ?></dt><dd class="col-sm-8"><?= esc($req->price_list ?: '—') ?></dd>
				</dl>
			</div>
			<div class="col-lg-6">
				<dl class="row mb-0">
					<dt class="col-sm-4"><?= lang('App.itrPostingDate') ?></dt><dd class="col-sm-8"><?= esc($req->posting_date ?: '—') ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrDueDate') ?></dt><dd class="col-sm-8"><?= esc($req->due_date ?: '—') ?></dd>
					<dt class="col-sm-4"><?= lang('App.itrDocumentDate') ?></dt><dd class="col-sm-8"><?= esc($req->document_date ?: '—') ?></dd>
					<dt class="col-sm-4 text-success"><?= lang('App.itrFromWh') ?></dt><dd class="col-sm-8"><?= esc($req->from_warehouse ?: '—') ?></dd>
					<dt class="col-sm-4 text-success"><?= lang('App.itrToWh') ?></dt><dd class="col-sm-8"><?= esc($req->to_warehouse ?: '—') ?></dd>
				</dl>
			</div>
		</div>

		<h5 class="mt-3 mb-2"><i class="fas fa-list me-1"></i> <?= lang('App.itrContents') ?></h5>
		<div class="table-responsive">
			<table class="table table-bordered table-sm align-middle">
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
							<td class="text-end"><?= esc(rtrim(rtrim(number_format((float) $l->quantity, 3), '0'), '.')) ?></td>
							<td><?= esc($l->uom ?: '—') ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ($req->journal_remarks || $req->remarks): ?>
			<div class="row mt-2">
				<div class="col-md-6"><strong><?= lang('App.itrJournalRemarks') ?>:</strong> <?= esc($req->journal_remarks ?: '—') ?></div>
				<div class="col-md-6"><strong><?= lang('App.itrRemarks') ?>:</strong> <?= esc($req->remarks ?: '—') ?></div>
			</div>
		<?php endif; ?>
	</div>
	<div class="card-footer">
		<form action="<?= site_url('transfer-requests/delete/' . $req->id) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= esc(lang('App.confirmDelete'), 'js') ?>');">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> <?= lang('App.delete') ?></button>
		</form>
	</div>
</div>
