<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-handshake me-1"></i> <?= lang('App.businessPartners') ?></h3>
		<span class="badge text-bg-light"><?= count($partners) ?></span>
	</div>
	<div class="table-responsive">
		<table class="table table-striped table-sm mb-0 align-middle">
			<thead>
				<tr>
					<th><?= lang('App.bpCode') ?></th>
					<th><?= lang('App.bpName') ?></th>
					<th><?= lang('App.bpShipTo') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($partners)): ?>
					<tr><td colspan="3" class="text-center text-body-secondary py-3"><?= lang('App.noBusinessPartners') ?></td></tr>
				<?php else: foreach ($partners as $bp): ?>
					<tr>
						<td><code><?= esc($bp->bp_code) ?></code></td>
						<td><?= esc($bp->bp_name) ?></td>
						<td><?= $bp->ship_to ? esc($bp->ship_to) : '<span class="text-body-secondary">—</span>' ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<div class="card-footer">
		<form action="<?= site_url('business-partners/sync') ?>" method="post" class="d-grid">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
		</form>
	</div>
</div>
