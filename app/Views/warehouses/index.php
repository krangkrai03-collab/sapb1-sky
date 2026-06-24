<?php
// Distinct colour per company so the two are visually separated.
$companyTheme = ['SKY' => 'info', 'JOJO' => 'warning'];
?>
<div class="row g-3">
	<?php foreach ($companies as $company): ?>
		<?php $theme = $companyTheme[$company] ?? 'secondary'; $list = $byCompany[$company] ?? []; ?>
		<div class="col-md-6">
			<div class="card h-100">
				<div class="card-header text-bg-<?= esc($theme, 'attr') ?> d-flex justify-content-between align-items-center">
					<h3 class="card-title mb-0"><i class="fas fa-warehouse me-1"></i> <?= esc($company) ?></h3>
					<span class="badge text-bg-light"><?= count($list) ?></span>
				</div>
				<div class="table-responsive">
					<table class="table table-striped table-sm mb-0 align-middle">
						<thead>
							<tr>
								<th><?= lang('App.warehouseCode') ?></th>
								<th><?= lang('App.warehouseName') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($list)): ?>
								<tr><td colspan="2" class="text-center text-body-secondary py-3"><?= lang('App.noWarehouses') ?></td></tr>
							<?php else: foreach ($list as $w): ?>
								<tr>
									<td><code><?= esc($w->code) ?></code></td>
									<td><?= esc($w->name) ?></td>
								</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
				<div class="card-footer">
					<form action="<?= site_url('warehouses/sync/' . $company) ?>" method="post" class="d-grid">
						<?= csrf_field() ?>
						<button type="submit" class="btn btn-<?= esc($theme, 'attr') ?>"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
					</form>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
