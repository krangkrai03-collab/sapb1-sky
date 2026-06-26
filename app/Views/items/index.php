<?php $total = $pager->getDetails()['total'] ?? count($items); ?>
<div class="card">
	<div class="card-header d-flex flex-wrap gap-2 align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-boxes-stacked me-1"></i> <?= lang('App.itemMaster') ?></h3>
		<span class="badge text-bg-light"><?= (int) $total ?></span>
		<form method="get" action="<?= site_url('items') ?>" class="ms-auto" id="itemSearchForm" role="search">
			<div class="input-group input-group-sm" style="width:280px;max-width:60vw">
				<span class="input-group-text"><i class="fas fa-search"></i></span>
				<input type="search" name="q" id="itemSearch" class="form-control" value="<?= esc($q, 'attr') ?>"
					placeholder="<?= esc(lang('App.itemSearchPlaceholder'), 'attr') ?>" autocomplete="off" autofocus>
			</div>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table table-striped table-sm mb-0 align-middle">
			<thead>
				<tr>
					<th><?= lang('App.itemCode') ?></th>
					<th><?= lang('App.itemName') ?></th>
					<th><?= lang('App.defaultWarehouse') ?></th>
					<th><?= lang('App.uoms') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($items)): ?>
					<tr><td colspan="4" class="text-center text-body-secondary py-3"><?= $q !== '' ? lang('App.noSearchResults') : lang('App.noItems') ?></td></tr>
				<?php else: foreach ($items as $it): ?>
					<tr>
						<td><code><?= esc($it->item_code) ?></code></td>
						<td><?= esc($it->item_name) ?></td>
						<td><?= $it->default_warehouse ? esc($it->default_warehouse) : '<span class="text-body-secondary">—</span>' ?></td>
						<td>
							<?php if (empty($it->uoms)): ?>
								<span class="text-body-secondary">—</span>
							<?php else: foreach ($it->uoms as $u): $qty = rtrim(rtrim((string) $u->base_qty, '0'), '.'); ?>
								<span class="badge <?= $u->is_inventory_uom ? 'text-bg-primary' : 'text-bg-light border' ?> me-1"
									title="<?= esc(($u->is_inventory_uom ? lang('App.uomInventory') . ' · ' : '') . $qty . ' ' . $u->base_uom, 'attr') ?>"><?= esc($u->uom_code) ?><?php if (! $u->is_inventory_uom && (float) $u->base_qty != 1.0): ?> <span class="opacity-75">×<?= esc($qty) ?></span><?php endif; ?></span>
							<?php endforeach; endif; ?>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
		<div class="card-body border-top py-2 d-flex justify-content-center">
			<?= $pager->links('default', 'bootstrap5') ?>
		</div>
	<?php endif; ?>
	<div class="card-footer">
		<form action="<?= site_url('items/sync') ?>" method="post" class="d-grid">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
		</form>
	</div>
</div>

<script>
	(function () {
		var input = document.getElementById('itemSearch');
		var form  = document.getElementById('itemSearchForm');
		if (! input || ! form) { return; }
		// Keep the caret at the end after the page reloads mid-typing.
		var len = input.value.length;
		input.setSelectionRange(len, len);
		// Type-to-search: auto-submit shortly after the user stops typing.
		var timer;
		input.addEventListener('input', function () {
			clearTimeout(timer);
			timer = setTimeout(function () { form.submit(); }, 350);
		});
	})();
</script>
