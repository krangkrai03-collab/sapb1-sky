<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferRequestModel extends Model
{
    protected $table         = 'transfer_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'doc_no', 'sap_doc_no', 'status', 'sync_status', 'sync_error', 'synced_at',
        'business_partner', 'name', 'contact_person',
        'ship_to', 'price_list', 'posting_date', 'due_date', 'document_date',
        'from_warehouse', 'to_warehouse', 'journal_remarks', 'remarks',
        'created_by', 'created_at', 'updated_at',
    ];

    /**
     * Next document number for a month, e.g. ITR26060001
     * (ITR + yymm + running). $ym is 'ym' (e.g. '2606').
     */
    public function nextDocNo(?string $ym = null): string
    {
        $prefix  = 'ITR' . ($ym ?: date('ym'));
        $running = $this->like('doc_no', $prefix, 'after')->countAllResults() + 1;
        return $prefix . sprintf('%04d', $running);
    }
}
