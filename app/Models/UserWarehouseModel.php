<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWarehouseModel extends Model
{
    protected $table         = 'user_warehouses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'warehouse_id'];

    /** Warehouse ids currently bound to a user. */
    public function boundIds(int $userId): array
    {
        return array_map(
            static fn ($row) => (int) $row->warehouse_id,
            $this->where('user_id', $userId)->findAll()
        );
    }

    /** Replace a user's warehouse bindings with the given warehouse ids. */
    public function sync(int $userId, array $warehouseIds): void
    {
        $this->where('user_id', $userId)->delete();
        foreach (array_unique(array_filter($warehouseIds)) as $wid) {
            $this->insert(['user_id' => $userId, 'warehouse_id' => (int) $wid]);
        }
    }
}
