<?php

namespace App\Controllers;

use App\Models\TransferRequestModel;
use App\Models\TransferRequestItemModel;
use App\Models\WarehouseModel;
use App\Models\ItemModel;

class TransferRequests extends BaseController
{
    private const COMPANIES = ['SKY', 'JOJO'];

    private TransferRequestModel $requests;
    private TransferRequestItemModel $lines;
    private WarehouseModel $warehouses;
    private ItemModel $items;

    public function __construct()
    {
        $this->requests   = new TransferRequestModel();
        $this->lines      = new TransferRequestItemModel();
        $this->warehouses = new WarehouseModel();
        $this->items      = new ItemModel();
    }

    public function index()
    {
        $isAdmin = auth()->user()->inGroup('superadmin');

        $builder = $this->requests
            ->select('transfer_requests.*, users.username AS created_by_name')
            ->join('users', 'users.id = transfer_requests.created_by', 'left')
            ->orderBy('transfer_requests.id', 'DESC');

        // Non-admins only see their own requests.
        if (! $isAdmin) {
            $builder->where('transfer_requests.created_by', auth()->id());
        }

        // Status summary (same scope as the list).
        $scopeId = $isAdmin ? null : (int) auth()->id();
        $count   = static function (?string $status) use ($scopeId) {
            $m = new TransferRequestModel();
            if ($scopeId !== null) {
                $m->where('created_by', $scopeId);
            }
            if ($status !== null) {
                $m->where('status', $status);
            }
            return $m->countAllResults();
        };

        return $this->render('transfer_requests/index', [
            'title'    => lang('App.inventoryTransferRequest'),
            'requests' => $builder->paginate(20),
            'pager'    => $this->requests->pager,
            'isAdmin'  => $isAdmin,
            'counts'   => [
                'total'     => $count(null),
                'open'      => $count('Open'),
                'closed'    => $count('Closed'),
                'cancelled' => $count('Cancelled'),
            ],
        ]);
    }

    public function create()
    {
        return $this->render('transfer_requests/form', [
            'title'      => lang('App.itrNew'),
            'companies'  => self::COMPANIES,
            'warehouses' => $this->byCompany($this->warehouses, ['code', 'name']),
            'items'      => $this->byCompany($this->items, ['item_code', 'item_name']),
            'docNo'      => $this->requests->nextDocNo(self::COMPANIES[0]),
        ]);
    }

    public function store()
    {
        $companies = implode(',', self::COMPANIES);
        $rules     = [
            'company'       => ['label' => lang('App.fCompany'), 'rules' => "required|in_list[{$companies}]"],
            'posting_date'  => ['label' => lang('App.itrPostingDate'), 'rules' => 'permit_empty|valid_date'],
            'due_date'      => ['label' => lang('App.itrDueDate'), 'rules' => 'permit_empty|valid_date'],
            'document_date' => ['label' => lang('App.itrDocumentDate'), 'rules' => 'permit_empty|valid_date'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $company = (string) $this->request->getPost('company');
        $rawLines = $this->parseLines($company);
        if ($rawLines === []) {
            return redirect()->back()->withInput()->with('error', lang('App.itrNoLines'));
        }

        // Server-side company guard: the form filters warehouses/items by company
        // client-side, but a crafted POST could otherwise mix SKY/JOJO data or
        // reference master records that don't exist. Reject before persisting.
        if (($refError = $this->companyRefError($company, $rawLines)) !== null) {
            return redirect()->back()->withInput()->with('error', $refError);
        }

        // Running number is taken from the DB per company + posting-date month.
        $docNo = $this->requests->nextDocNo($company, $this->ymFromDate($this->request->getPost('posting_date')));
        $this->requests->insert([
            'doc_no'           => $docNo,
            'company'          => $company,
            'status'           => 'Open',
            'business_partner' => $this->request->getPost('business_partner'),
            'name'             => $this->request->getPost('name'),
            'contact_person'   => $this->request->getPost('contact_person'),
            'ship_to'          => $this->request->getPost('ship_to'),
            'price_list'       => $this->request->getPost('price_list'),
            'posting_date'     => $this->request->getPost('posting_date') ?: null,
            'due_date'         => $this->request->getPost('due_date') ?: null,
            'document_date'    => $this->request->getPost('document_date') ?: null,
            'from_warehouse'   => $this->request->getPost('from_warehouse'),
            'to_warehouse'     => $this->request->getPost('to_warehouse'),
            'journal_remarks'  => $this->request->getPost('journal_remarks'),
            'remarks'          => $this->request->getPost('remarks'),
            'created_by'       => auth()->id(),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $requestId = $this->requests->getInsertID();

        $lineNo = 1;
        foreach ($rawLines as $line) {
            $line['request_id'] = $requestId;
            $line['line_no']    = $lineNo++;
            $this->lines->insert($line);
        }

        log_activity('itr.create', "สร้างคำขอโอนย้าย {$docNo} [{$company}] " . ($lineNo - 1) . ' รายการ');
        return redirect()->to('transfer-requests/show/' . $requestId)->with('message', lang('App.itrCreated', [$docNo]));
    }

    /** AJAX: preview the next document number for a company + posting-date month. */
    public function docNoPreview()
    {
        $company = (string) $this->request->getGet('company');
        $ym      = $this->ymFromDate((string) $this->request->getGet('date'));
        return $this->response->setJSON(['doc_no' => $this->requests->nextDocNo($company, $ym)]);
    }

    public function show($id)
    {
        $req = $this->requests->find((int) $id);
        if ($req === null || ! $this->canAccess($req)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('transfer_requests/show', [
            'title' => $req->doc_no,
            'req'   => $req,
            'lines' => $this->lines->where('request_id', (int) $id)->orderBy('line_no', 'asc')->findAll(),
        ]);
    }

    /** Push the request to SAP (manual, with confirmation). Idempotent. */
    public function send($id)
    {
        $req = $this->requests->find((int) $id);
        if ($req === null || ! $this->canAccess($req)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        // Only un-sent documents may be sent (prevents double-posting).
        if (! in_array($req->sync_status, ['pending', 'failed'], true)) {
            return redirect()->to('transfer-requests/show/' . $id)->with('error', lang('App.sapAlreadySent'));
        }

        $this->requests->update($req->id, ['sync_status' => 'sending']);
        $lines  = $this->lines->where('request_id', $req->id)->orderBy('line_no', 'asc')->findAll();
        $result = $this->pushToSap($req, $lines);

        if ($result['ok']) {
            $this->requests->update($req->id, [
                'sync_status' => 'sent',
                'sap_doc_no'  => $result['sap_doc_no'],
                'sync_error'  => null,
                'synced_at'   => date('Y-m-d H:i:s'),
            ]);
            log_activity('itr.sap.send', "ส่ง {$req->doc_no} เข้า SAP → {$result['sap_doc_no']}");
            return redirect()->to('transfer-requests/show/' . $id)->with('message', lang('App.sapSent', [$result['sap_doc_no']]));
        }

        $this->requests->update($req->id, ['sync_status' => 'failed', 'sync_error' => $result['error']]);
        log_activity('itr.sap.fail', "ส่ง {$req->doc_no} เข้า SAP ไม่สำเร็จ");
        return redirect()->to('transfer-requests/show/' . $id)->with('error', lang('App.sapFailed', [$result['error']]));
    }

    /**
     * Post the document to SAP Business One and return its DocNum.
     *
     * STUB — replace with the real SAP call (Service Layer
     * `POST /b1s/v1/InventoryTransferRequests`, or DI). Map header + lines,
     * send with auth, and return ['ok'=>bool, 'sap_doc_no'=>string, 'error'=>string].
     * For now it simulates a successful post so the workflow can be exercised.
     */
    private function pushToSap(object $req, array $lines): array
    {
        return ['ok' => true, 'sap_doc_no' => 'SAP-' . $req->doc_no, 'error' => ''];
    }

    public function delete($id)
    {
        $req = $this->requests->find((int) $id);
        if ($req === null || ! $this->canAccess($req)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        // Don't delete documents already posted to SAP.
        if ($req->sync_status === 'sent') {
            return redirect()->to('transfer-requests/show/' . $id)->with('error', lang('App.sapCannotDeleteSent'));
        }

        $this->lines->where('request_id', (int) $id)->delete();
        $this->requests->delete((int) $id);
        log_activity('itr.delete', "ลบคำขอโอนย้าย {$req->doc_no}");
        return redirect()->to('transfer-requests')->with('message', lang('App.itrDeleted', [$req->doc_no]));
    }

    // ---- helpers ----

    /** Admins see everything; others only their own requests. */
    private function canAccess(object $req): bool
    {
        return auth()->user()->inGroup('superadmin') || (int) $req->created_by === (int) auth()->id();
    }

    /**
     * Every warehouse and item referenced by the document (header + lines) must
     * belong to the selected company; items must exist in that company's master.
     * Returns the first violation message, or null when everything is in scope.
     */
    private function companyRefError(string $company, array $rawLines): ?string
    {
        $validWh = [];
        foreach ($this->warehouses->where('company', $company)->findAll() as $w) {
            $validWh[(string) $w->code] = true;
        }
        $validItem = [];
        foreach ($this->items->where('company', $company)->findAll() as $it) {
            $validItem[(string) $it->item_code] = true;
        }

        $badWarehouse = static function (?string $code) use ($validWh): bool {
            $code = trim((string) $code);
            return $code !== '' && ! isset($validWh[$code]);
        };

        // Header warehouses (optional, but must match the company when present).
        foreach (['from_warehouse', 'to_warehouse'] as $field) {
            $code = trim((string) $this->request->getPost($field));
            if ($badWarehouse($code)) {
                return lang('App.itrBadWarehouse', [$code, $company]);
            }
        }

        // Line items must exist in the company master; line warehouses must match.
        foreach ($rawLines as $line) {
            if (! isset($validItem[$line['item_code']])) {
                return lang('App.itrBadItem', [$line['item_code'], $company]);
            }
            if ($badWarehouse($line['from_warehouse'])) {
                return lang('App.itrBadWarehouse', [$line['from_warehouse'], $company]);
            }
            if ($badWarehouse($line['to_warehouse'])) {
                return lang('App.itrBadWarehouse', [$line['to_warehouse'], $company]);
            }
        }

        return null;
    }

    /** 'ym' (e.g. '2606') from a Y-m-d date, falling back to the current month. */
    private function ymFromDate(?string $date): string
    {
        if ($date) {
            $ts = strtotime($date);
            if ($ts !== false) {
                return date('ym', $ts);
            }
        }
        return date('ym');
    }

    /** Group a model's rows by company: ['SKY' => [...], 'JOJO' => [...]]. */
    private function byCompany($model, array $fields): array
    {
        $out = ['SKY' => [], 'JOJO' => []];
        foreach ($model->orderBy($fields[0], 'asc')->findAll() as $row) {
            if (! isset($out[$row->company])) {
                continue;
            }
            $out[$row->company][] = ['code' => $row->{$fields[0]}, 'name' => $row->{$fields[1]}];
        }
        return $out;
    }

    /**
     * Build line rows from posted items[], keeping only rows with an item code
     * and a positive quantity, and resolving the item name from Item Master.
     */
    private function parseLines(string $company): array
    {
        $posted = $this->request->getPost('items');
        if (! is_array($posted)) {
            return [];
        }

        $rows = [];
        foreach ($posted as $row) {
            $code = trim((string) ($row['item_code'] ?? ''));
            $qty  = (float) ($row['quantity'] ?? 0);
            if ($code === '' || $qty <= 0) {
                continue;
            }
            $item = $this->items->where('company', $company)->where('item_code', $code)->first();
            $rows[] = [
                'item_code'      => $code,
                'item_name'      => $item->item_name ?? '',
                'from_warehouse' => trim((string) ($row['from_warehouse'] ?? '')),
                'to_warehouse'   => trim((string) ($row['to_warehouse'] ?? '')),
                'quantity'       => $qty,
                'uom'            => trim((string) ($row['uom'] ?? '')),
            ];
        }
        return $rows;
    }
}
