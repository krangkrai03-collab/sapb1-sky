<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'item_code'         => ['type' => 'VARCHAR', 'constraint' => 60],
            'item_name'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'default_warehouse' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company');
        // One item code per company.
        $this->forge->addUniqueKey(['company', 'item_code']);
        $this->forge->createTable('items');
    }

    public function down()
    {
        $this->forge->dropTable('items');
    }
}
