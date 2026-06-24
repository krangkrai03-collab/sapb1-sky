<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserWarehouses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'warehouse_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        // A user may bind a given warehouse only once.
        $this->forge->addUniqueKey(['user_id', 'warehouse_id']);
        $this->forge->addKey('user_id');
        $this->forge->createTable('user_warehouses');
    }

    public function down()
    {
        $this->forge->dropTable('user_warehouses');
    }
}
