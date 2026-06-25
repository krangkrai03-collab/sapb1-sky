<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseCode extends Migration
{
    public function up()
    {
        $this->forge->addColumn('warehouses', [
            'code' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true, 'after' => 'name'],
        ]);

        // Code is the natural key for warehouses.
        $this->db->table('warehouses')->truncate();
        $this->forge->addUniqueKey('code');
        $this->forge->processIndexes('warehouses');
    }

    public function down()
    {
        $this->forge->dropKey('warehouses', 'code', false);
        $this->forge->dropColumn('warehouses', 'code');
    }
}
