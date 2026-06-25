<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemUomModel extends Model
{
    protected $table         = 'item_uoms';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'item_id', 'uom_entry', 'uom_code', 'base_qty', 'base_uom', 'is_inventory_uom', 'created_at',
    ];
}
