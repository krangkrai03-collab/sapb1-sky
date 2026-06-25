<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_code'         => ['type' => 'VARCHAR', 'constraint' => 60],
            'item_name'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'default_warehouse' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Item code is unique across the master.
        $this->forge->addUniqueKey('item_code');
        $this->forge->createTable('items');
    }

    public function down()
    {
        $this->forge->dropTable('items');
    }
}
