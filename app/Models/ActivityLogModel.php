<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'username', 'action', 'description', 'ip_address', 'created_at'];
}
