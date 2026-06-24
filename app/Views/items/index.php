<?php
// Distinct colour per company so the two are visually separated.
$companyTheme = ['SKY' => 'info', 'JOJO' => 'warning'];
?>
<div class="row g-3">
	<?php foreach ($companies as $company): ?>
		<?php $theme = $companyTheme[$company] ?? 'secondary'; $list = $byCompany[$company] ?? []; ?>
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header text-bg-<?= esc($theme, 'attr') ?> d-flex justify-content-between align-items-center">
					<h3 class="card-title mb-0"><i class="fas fa-boxes-stacked me-1"></i> <?= esc($company) ?></h3>
					<span class="badge text-bg-light"><?= count($list) ?></span>
				</div>
				<div class="table-responsive">
					<table class="table table-striped table-sm mb-0 align-middle">
						<thead>
							<tr>
								<th><?= lang('App.itemCode') ?></th>
								<th><?= lang('App.itemName') ?></th>
								<th><?= lang('App.defaultWarehouse') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($list)): ?>
								<tr><td colspan="3" class="text-center text-body-secondary py-3"><?= lang('App.noItems') ?></td></tr>
							<?php else: foreach ($list as $it): ?>
								<tr>
									<td><code><?= esc($it->item_code) ?></code></td>
									<td><?= esc($it->item_name) ?></td>
									<td><?= $it->default_warehouse ? esc($it->default_warehouse) : '<span class="text-body-secondary">—</span>' ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
				<div class="card-footer">
					<form action="<?= site_url('items/sync/' . $company) ?>" method="post" class="d-grid">
						<?= csrf_field() ?>
						<button type="submit" class="btn btn-<?= esc($theme, 'attr') ?>"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
					</form>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
