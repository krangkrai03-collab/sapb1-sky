<?php $total = $pager->getDetails()['total'] ?? count($warehouses); ?>
<div class="card">
	<div class="card-header d-flex flex-wrap gap-2 align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-warehouse me-1"></i> <?= lang('App.warehouses') ?></h3>
		<span class="badge text-bg-light"><?= (int) $total ?></span>
		<form method="get" action="<?= site_url('warehouses') ?>" class="ms-auto" id="whSearchForm" role="search">
			<div class="input-group input-group-sm" style="width:280px;max-width:60vw">
				<span class="input-group-text"><i class="fas fa-search"></i></span>
				<input type="search" name="q" id="whSearch" class="form-control" value="<?= esc($q, 'attr') ?>"
					placeholder="<?= esc(lang('App.warehouseSearchPlaceholder'), 'attr') ?>" autocomplete="off" autofocus>
			</div>
		</form>
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
				<?php if (empty($warehouses)): ?>
					<tr><td colspan="2" class="text-center text-body-secondary py-3"><?= $q !== '' ? lang('App.noSearchResults') : lang('App.noWarehouses') ?></td></tr>
				<?php else: foreach ($warehouses as $w): ?>
					<tr>
						<td><code><?= esc($w->code) ?></code></td>
						<td><?= esc($w->name) ?></td>
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
		<form action="<?= site_url('warehouses/sync') ?>" method="post" class="d-grid">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
		</form>
	</div>
</div>

<script>
	(function () {
		var input = document.getElementById('whSearch');
		var form  = document.getElementById('whSearchForm');
		if (! input || ! form) { return; }
		var len = input.value.length;
		input.setSelectionRange(len, len);
		var timer;
		input.addEventListener('input', function () {
			clearTimeout(timer);
			timer = setTimeout(function () { form.submit(); }, 350);
		});
	})();
</script>
