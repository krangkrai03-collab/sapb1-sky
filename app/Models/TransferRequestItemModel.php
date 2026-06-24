<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferRequestItemModel extends Model
{
    protected $table         = 'transfer_request_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'request_id', 'line_no', 'item_code', 'item_name',
        'from_warehouse', 'to_warehouse', 'quantity', 'uom',
    ];
}
