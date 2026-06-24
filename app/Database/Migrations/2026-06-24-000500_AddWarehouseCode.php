<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseCode extends Migration
{
    public function up()
    {
        $this->forge->addColumn('warehouses', [
            'code' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true, 'after' => 'company'],
        ]);

        // Switch the natural key from name to code.
        $this->forge->dropKey('warehouses', 'company_name', false);
        // Old rows were placeholder data without codes; start fresh for code-based sync.
        $this->db->table('warehouses')->truncate();
        $this->forge->addUniqueKey(['company', 'code']);
        $this->forge->processIndexes('warehouses');
    }

    public function down()
    {
        $this->forge->dropKey('warehouses', 'company_code', false);
        $this->forge->dropColumn('warehouses', 'code');
    }
}
