<form action="<?= site_url('transfer-requests/create') ?>" method="post">
	<?= csrf_field() ?>
	<div class="card shadow-sm">
		<div class="card-header d-flex align-items-center">
			<h3 class="card-title mb-0"><i class="fas fa-right-left me-1"></i> <?= esc($title) ?></h3>
			<span class="badge text-bg-success ms-auto"><?= lang('App.itrStatusOpen') ?></span>
		</div>
		<div class="card-body">
			<div class="row g-4">
				<!-- Left: business partner -->
				<div class="col-lg-7">
					<div class="text-uppercase text-body-secondary small fw-semibold mb-2"><i class="fas fa-user-tie me-1"></i> <?= lang('App.itrBusinessPartner') ?></div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrBusinessPartner') ?></label>
						<div class="col-sm-8"><input type="text" name="business_partner" class="form-control" value="<?= esc(old('business_partner')) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrName') ?></label>
						<div class="col-sm-8"><input type="text" name="name" class="form-control" value="<?= esc(old('name')) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrContactPerson') ?></label>
						<div class="col-sm-8"><input type="text" name="contact_person" class="form-control" value="<?= esc(old('contact_person')) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrShipTo') ?></label>
						<div class="col-sm-8"><textarea name="ship_to" class="form-control" rows="2"><?= esc(old('ship_to')) ?></textarea></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrPriceList') ?></label>
						<div class="col-sm-8"><input type="text" name="price_list" class="form-control" value="<?= esc(old('price_list', 'Last Purchase Price')) ?>"></div>
					</div>
				</div>

				<!-- Right: document panel -->
				<div class="col-lg-5">
					<div class="border rounded p-3 bg-body-secondary">
						<div class="text-uppercase text-body-secondary small fw-semibold mb-2"><i class="fas fa-file-invoice me-1"></i> <?= lang('App.itrDocNo') ?></div>
						<div class="row mb-2">
							<label class="col-5 col-form-label col-form-label-sm"><?= lang('App.itrDocNo') ?></label>
							<div class="col-7"><input type="text" id="doc_no_preview" class="form-control form-control-sm fw-semibold" style="font-family:var(--bs-font-monospace)" value="<?= esc($docNo) ?>" readonly></div>
						</div>
						<div class="row mb-2">
							<label class="col-5 col-form-label col-form-label-sm"><?= lang('App.itrPostingDate') ?></label>
							<div class="col-7"><input type="text" name="posting_date" class="form-control form-control-sm js-datepicker" value="<?= esc(old('posting_date', date('Y-m-d'))) ?>"></div>
						</div>
						<div class="row mb-2">
							<label class="col-5 col-form-label col-form-label-sm"><?= lang('App.itrDueDate') ?></label>
							<div class="col-7"><input type="text" name="due_date" class="form-control form-control-sm js-datepicker" value="<?= esc(old('due_date', date('Y-m-d'))) ?>"></div>
						</div>
						<div class="row mb-3">
							<label class="col-5 col-form-label col-form-label-sm"><?= lang('App.itrDocumentDate') ?></label>
							<div class="col-7"><input type="text" name="document_date" class="form-control form-control-sm js-datepicker" value="<?= esc(old('document_date', date('Y-m-d'))) ?>"></div>
						</div>

						<!-- From -> To warehouse -->
						<div class="border rounded p-2 bg-body">
							<div class="row g-2 align-items-end">
								<div class="col">
									<label class="form-label small text-success mb-1"><i class="fas fa-warehouse me-1"></i><?= lang('App.itrFromWh') ?></label>
									<select name="from_warehouse" id="from_warehouse" class="form-select form-select-sm"></select>
								</div>
								<div class="col-auto pb-2"><i class="fas fa-arrow-right text-success"></i></div>
								<div class="col">
									<label class="form-label small text-success mb-1"><i class="fas fa-warehouse me-1"></i><?= lang('App.itrToWh') ?></label>
									<select name="to_warehouse" id="to_warehouse" class="form-select form-select-sm"></select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Contents -->
			<div class="d-flex align-items-center mt-4 mb-2">
				<h5 class="mb-0"><i class="fas fa-list me-1"></i> <?= lang('App.itrContents') ?></h5>
				<span class="ms-auto text-body-secondary small">
					<i class="fas fa-list-ol me-1"></i><span id="lineCount">0</span>
					<span class="mx-1">·</span>
					<i class="fas fa-cubes me-1"></i><?= lang('App.itrQuantity') ?> <span id="lineQty">0</span>
				</span>
			</div>
			<div class="table-responsive border rounded">
				<table class="table table-striped table-hover table-sm align-middle mb-0" id="lines">
					<thead class="table-light">
						<tr>
							<th style="width:36px">#</th>
							<th style="min-width:220px"><?= lang('App.itrItemNo') ?></th>
							<th style="min-width:180px"><?= lang('App.itrItemDesc') ?></th>
							<th style="min-width:150px"><?= lang('App.itrFromWh') ?></th>
							<th style="min-width:150px"><?= lang('App.itrToWh') ?></th>
							<th style="width:110px" class="text-end"><?= lang('App.itrQuantity') ?></th>
							<th style="width:90px"><?= lang('App.itrUom') ?></th>
							<th style="width:40px"></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addLine"><i class="fas fa-plus me-1"></i> <?= lang('App.itrAddLine') ?></button>

			<!-- Footer remarks -->
			<div class="row mt-4">
				<div class="col-md-6 mb-2">
					<label class="form-label"><i class="fas fa-pen me-1 text-body-secondary"></i> <?= lang('App.itrJournalRemarks') ?></label>
					<textarea name="journal_remarks" class="form-control" rows="2"><?= esc(old('journal_remarks')) ?></textarea>
				</div>
				<div class="col-md-6 mb-2">
					<label class="form-label"><i class="fas fa-comment-dots me-1 text-body-secondary"></i> <?= lang('App.itrRemarks') ?></label>
					<textarea name="remarks" class="form-control" rows="2"><?= esc(old('remarks')) ?></textarea>
				</div>
			</div>
		</div>
		<div class="card-footer d-flex gap-2">
			<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.save') ?></button>
			<a href="<?= site_url('transfer-requests') ?>" class="btn btn-secondary"><?= lang('App.cancel') ?></a>
		</div>
	</div>
</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
	(function () {
		var WH    = <?= json_encode($warehouses, JSON_UNESCAPED_UNICODE) ?>;
		var ITEMS = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
		var idx = 0;

		var fromWh   = document.getElementById('from_warehouse');
		var toWh     = document.getElementById('to_warehouse');
		var tbody    = document.querySelector('#lines tbody');

		function tomify(el) {
			// Searchable dropdown (matches both code and name in the option text).
			if (window.TomSelect && ! el.tomselect) {
				new TomSelect(el, { maxOptions: 1000, allowEmptyOption: true, create: false, dropdownParent: 'body' });
			}
		}

		function esc(s) {
			return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		}
		function options(list, selected, withData) {
			var s = '<option value=""' + (withData ? ' data-name=""' : '') + '>—</option>';
			(list || []).forEach(function (o) {
				var data = withData ? ' data-name="' + esc(o.name) + '"' : '';
				var sel  = o.code === selected ? ' selected' : '';
				s += '<option value="' + esc(o.code) + '"' + data + sel + '>' + esc(o.code) + ' — ' + esc(o.name) + '</option>';
			});
			return s;
		}
		function addRow() {
			var tr = document.createElement('tr');
			tr.innerHTML =
				'<td class="text-center text-body-secondary line-num"></td>' +
				'<td><select name="items[' + idx + '][item_code]" class="form-select form-select-sm item-sel">' + options(ITEMS, '', true) + '</select></td>' +
				'<td><input type="text" class="form-control form-control-sm item-desc" readonly></td>' +
				'<td><select name="items[' + idx + '][from_warehouse]" class="form-select form-select-sm">' + options(WH, fromWh.value, false) + '</select></td>' +
				'<td><select name="items[' + idx + '][to_warehouse]" class="form-select form-select-sm">' + options(WH, toWh.value, false) + '</select></td>' +
				'<td><input type="number" step="0.001" min="0" name="items[' + idx + '][quantity]" class="form-control form-control-sm text-end qty" value="0"></td>' +
				'<td><input type="text" name="items[' + idx + '][uom]" class="form-control form-control-sm"></td>' +
				'<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger del-line"><i class="fas fa-times"></i></button></td>';
			tbody.appendChild(tr);
			idx++;
			tr.querySelectorAll('select').forEach(tomify);
			renumber();
		}
		function renumber() {
			var rows = tbody.querySelectorAll('tr');
			rows.forEach(function (tr, i) {
				tr.querySelector('.line-num').textContent = i + 1;
				// Always keep at least one line: hide the delete button when only one remains.
				var del = tr.querySelector('.del-line');
				if (del) { del.style.visibility = rows.length > 1 ? 'visible' : 'hidden'; }
			});
			updateSummary();
		}
		function updateSummary() {
			var rows = tbody.querySelectorAll('tr');
			var qty = 0;
			tbody.querySelectorAll('.qty').forEach(function (q) { qty += parseFloat(q.value) || 0; });
			document.getElementById('lineCount').textContent = rows.length;
			document.getElementById('lineQty').textContent = Math.round(qty * 1000) / 1000;
		}
		function rebuild() {
			tbody.innerHTML = '';
			idx = 0;
			[fromWh, toWh].forEach(function (el) { if (el.tomselect) { el.tomselect.destroy(); } });
			fromWh.innerHTML = options(WH, '', false);
			toWh.innerHTML   = options(WH, '', false);
			tomify(fromWh);
			tomify(toWh);
			addRow();
		}

		// Preview the next document number per posting-date month.
		var posting = document.querySelector('input[name="posting_date"]');
		var docNoEl = document.getElementById('doc_no_preview');
		function refreshDocNo() {
			if (! docNoEl) { return; }
			var qs = '?date=' + encodeURIComponent(posting ? posting.value : '');
			fetch('<?= site_url('transfer-requests/next-doc-no') ?>' + qs)
				.then(function (r) { return r.json(); })
				.then(function (j) { if (j && j.doc_no) { docNoEl.value = j.doc_no; } })
				.catch(function () {});
		}
		if (posting) { posting.addEventListener('change', refreshDocNo); }

		document.getElementById('addLine').addEventListener('click', addRow);
		tbody.addEventListener('click', function (e) {
			var btn = e.target.closest('.del-line');
			if (btn) { btn.closest('tr').remove(); renumber(); }
		});
		tbody.addEventListener('change', function (e) {
			if (e.target.classList.contains('item-sel')) {
				var opt  = e.target.options[e.target.selectedIndex];
				var desc = e.target.closest('tr').querySelector('.item-desc');
				desc.value = opt ? (opt.getAttribute('data-name') || '') : '';
			}
		});
		tbody.addEventListener('input', function (e) {
			if (e.target.classList.contains('qty')) { updateSummary(); }
		});

		rebuild();
		refreshDocNo();
	})();
</script>
