<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company');
        // No duplicate warehouse name within the same company.
        $this->forge->addUniqueKey(['company', 'name']);
        $this->forge->createTable('warehouses');
    }

    public function down()
    {
        $this->forge->dropTable('warehouses');
    }
}
