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
        'doc_no', 'sap_doc_no', 'company', 'status', 'business_partner', 'name', 'contact_person',
        'ship_to', 'price_list', 'posting_date', 'due_date', 'document_date',
        'from_warehouse', 'to_warehouse', 'journal_remarks', 'remarks',
        'created_by', 'created_at', 'updated_at',
    ];

    /** Single-letter series code per company, kept separate. */
    private const COMPANY_CODE = ['SKY' => 'S', 'JOJO' => 'J'];

    /**
     * Next document number for a company + month, e.g. ITRS26060001
     * (ITR + company letter + yymm + running). Running counts are kept
     * separate per company per month. $ym is 'ym' (e.g. '2606').
     */
    public function nextDocNo(string $company = '', ?string $ym = null): string
    {
        $cc      = self::COMPANY_CODE[strtoupper($company)] ?? '';
        $prefix  = 'ITR' . $cc . ($ym ?: date('ym'));
        $running = $this->like('doc_no', $prefix, 'after')->countAllResults() + 1;
        return $prefix . sprintf('%04d', $running);
    }
}
