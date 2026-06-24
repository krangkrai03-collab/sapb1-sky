<form action="<?= site_url('transfer-requests/create') ?>" method="post">
	<?= csrf_field() ?>
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0"><i class="fas fa-right-left me-1"></i> <?= esc($title) ?></h3>
			<span class="badge text-bg-success"><?= lang('App.itrStatusOpen') ?></span>
		</div>
		<div class="card-body">
			<div class="row">
				<!-- Left: partner / company -->
				<div class="col-lg-6">
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.fCompany') ?> <span class="text-danger">*</span></label>
						<div class="col-sm-8">
							<select name="company" id="company" class="form-select" required>
								<?php foreach ($companies as $c): ?>
									<option value="<?= esc($c, 'attr') ?>" <?= old('company') === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
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
				<!-- Right: doc info / warehouses -->
				<div class="col-lg-6">
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrDocNo') ?></label>
						<div class="col-sm-8"><input type="text" id="doc_no_preview" class="form-control" value="<?= esc($docNo) ?>" readonly></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrPostingDate') ?></label>
						<div class="col-sm-8"><input type="text" name="posting_date" class="form-control js-datepicker" value="<?= esc(old('posting_date', date('Y-m-d'))) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrDueDate') ?></label>
						<div class="col-sm-8"><input type="text" name="due_date" class="form-control js-datepicker" value="<?= esc(old('due_date', date('Y-m-d'))) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label"><?= lang('App.itrDocumentDate') ?></label>
						<div class="col-sm-8"><input type="text" name="document_date" class="form-control js-datepicker" value="<?= esc(old('document_date', date('Y-m-d'))) ?>"></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label text-success"><i class="fas fa-arrow-right me-1"></i><?= lang('App.itrFromWh') ?></label>
						<div class="col-sm-8"><select name="from_warehouse" id="from_warehouse" class="form-select"></select></div>
					</div>
					<div class="row mb-2">
						<label class="col-sm-4 col-form-label text-success"><i class="fas fa-arrow-right me-1"></i><?= lang('App.itrToWh') ?></label>
						<div class="col-sm-8"><select name="to_warehouse" id="to_warehouse" class="form-select"></select></div>
					</div>
				</div>
			</div>

			<!-- Contents -->
			<h5 class="mt-3 mb-2"><i class="fas fa-list me-1"></i> <?= lang('App.itrContents') ?></h5>
			<div class="table-responsive">
				<table class="table table-bordered table-sm align-middle" id="lines">
					<thead class="table-light">
						<tr>
							<th style="width:36px">#</th>
							<th style="min-width:220px"><?= lang('App.itrItemNo') ?></th>
							<th style="min-width:180px"><?= lang('App.itrItemDesc') ?></th>
							<th style="min-width:150px"><?= lang('App.itrFromWh') ?></th>
							<th style="min-width:150px"><?= lang('App.itrToWh') ?></th>
							<th style="width:110px"><?= lang('App.itrQuantity') ?></th>
							<th style="width:90px"><?= lang('App.itrUom') ?></th>
							<th style="width:40px"></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<button type="button" class="btn btn-outline-primary btn-sm" id="addLine"><i class="fas fa-plus me-1"></i> <?= lang('App.itrAddLine') ?></button>

			<!-- Footer remarks -->
			<div class="row mt-3">
				<div class="col-md-6 mb-2">
					<label class="form-label"><?= lang('App.itrJournalRemarks') ?></label>
					<textarea name="journal_remarks" class="form-control" rows="2"><?= esc(old('journal_remarks')) ?></textarea>
				</div>
				<div class="col-md-6 mb-2">
					<label class="form-label"><?= lang('App.itrRemarks') ?></label>
					<textarea name="remarks" class="form-control" rows="2"><?= esc(old('remarks')) ?></textarea>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= lang('App.save') ?></button>
			<a href="<?= site_url('transfer-requests') ?>" class="btn btn-secondary"><?= lang('App.cancel') ?></a>
		</div>
	</div>
</form>

<script>
	(function () {
		var WH    = <?= json_encode($warehouses, JSON_UNESCAPED_UNICODE) ?>;
		var ITEMS = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
		var idx = 0;

		var company  = document.getElementById('company');
		var fromWh   = document.getElementById('from_warehouse');
		var toWh     = document.getElementById('to_warehouse');
		var tbody    = document.querySelector('#lines tbody');

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
			var c = company.value;
			var tr = document.createElement('tr');
			tr.innerHTML =
				'<td class="text-center text-body-secondary line-num"></td>' +
				'<td><select name="items[' + idx + '][item_code]" class="form-select form-select-sm item-sel">' + options(ITEMS[c], '', true) + '</select></td>' +
				'<td><input type="text" class="form-control form-control-sm item-desc" readonly></td>' +
				'<td><select name="items[' + idx + '][from_warehouse]" class="form-select form-select-sm">' + options(WH[c], fromWh.value, false) + '</select></td>' +
				'<td><select name="items[' + idx + '][to_warehouse]" class="form-select form-select-sm">' + options(WH[c], toWh.value, false) + '</select></td>' +
				'<td><input type="number" step="0.001" min="0" name="items[' + idx + '][quantity]" class="form-control form-control-sm text-end" value="0"></td>' +
				'<td><input type="text" name="items[' + idx + '][uom]" class="form-control form-control-sm"></td>' +
				'<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger del-line"><i class="fas fa-times"></i></button></td>';
			tbody.appendChild(tr);
			idx++;
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
		}
		function rebuild() {
			tbody.innerHTML = '';
			idx = 0;
			fromWh.innerHTML = options(WH[company.value], '', false);
			toWh.innerHTML   = options(WH[company.value], '', false);
			addRow();
		}

		// Preview the next document number per company + posting-date month.
		var posting = document.querySelector('input[name="posting_date"]');
		var docNoEl = document.getElementById('doc_no_preview');
		function refreshDocNo() {
			if (! docNoEl) { return; }
			var qs = '?company=' + encodeURIComponent(company.value) + '&date=' + encodeURIComponent(posting ? posting.value : '');
			fetch('<?= site_url('transfer-requests/next-doc-no') ?>' + qs)
				.then(function (r) { return r.json(); })
				.then(function (j) { if (j && j.doc_no) { docNoEl.value = j.doc_no; } })
				.catch(function () {});
		}
		if (posting) { posting.addEventListener('change', refreshDocNo); }

		company.addEventListener('change', function () { rebuild(); refreshDocNo(); });
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

		rebuild();
		refreshDocNo();
	})();
</script>
