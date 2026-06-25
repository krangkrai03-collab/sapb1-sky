<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiEndpointModel extends Model
{
    protected $table         = 'api_endpoints';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['company', 'name', 'method', 'path', 'created_at'];
}
