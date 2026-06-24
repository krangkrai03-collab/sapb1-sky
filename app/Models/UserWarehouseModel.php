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

    /**
     * Warehouse ids currently bound to a user, keyed by company
     * (e.g. ['SKY' => 12, 'JOJO' => 5]). One warehouse per company.
     */
    public function boundByCompany(int $userId): array
    {
        $rows = $this->select('warehouses.id, warehouses.company')
            ->join('warehouses', 'warehouses.id = user_warehouses.warehouse_id')
            ->where('user_warehouses.user_id', $userId)
            ->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->company] = (int) $row->id;
        }
        return $map;
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
