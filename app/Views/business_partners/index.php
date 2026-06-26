<?php $total = $pager->getDetails()['total'] ?? count($partners); ?>
<div class="card">
	<div class="card-header d-flex flex-wrap gap-2 align-items-center">
		<h3 class="card-title mb-0"><i class="fas fa-handshake me-1"></i> <?= lang('App.businessPartners') ?></h3>
		<span class="badge text-bg-light"><?= (int) $total ?></span>
		<form method="get" action="<?= site_url('business-partners') ?>" class="ms-auto" id="bpSearchForm" role="search">
			<div class="input-group input-group-sm" style="width:280px;max-width:60vw">
				<span class="input-group-text"><i class="fas fa-search"></i></span>
				<input type="search" name="q" id="bpSearch" class="form-control" value="<?= esc($q, 'attr') ?>"
					placeholder="<?= esc(lang('App.bpSearchPlaceholder'), 'attr') ?>" autocomplete="off" autofocus>
			</div>
		</form>
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
					<tr><td colspan="3" class="text-center text-body-secondary py-3"><?= $q !== '' ? lang('App.noSearchResults') : lang('App.noBusinessPartners') ?></td></tr>
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
	<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
		<div class="card-body border-top py-2 d-flex justify-content-center">
			<?= $pager->links('default', 'bootstrap5') ?>
		</div>
	<?php endif; ?>
	<div class="card-footer">
		<form action="<?= site_url('business-partners/sync') ?>" method="post" class="d-grid">
			<?= csrf_field() ?>
			<button type="submit" class="btn btn-primary"><i class="fas fa-rotate me-1"></i> <?= lang('App.syncFromSap') ?></button>
		</form>
	</div>
</div>

<script>
	(function () {
		var input = document.getElementById('bpSearch');
		var form  = document.getElementById('bpSearchForm');
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
