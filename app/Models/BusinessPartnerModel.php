<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessPartnerModel extends Model
{
    protected $table         = 'business_partners';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['company', 'bp_code', 'bp_name', 'ship_to', 'created_at'];
}
