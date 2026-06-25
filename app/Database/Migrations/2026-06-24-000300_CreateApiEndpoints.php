<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiEndpoints extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'path'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // No duplicate endpoint name.
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('api_endpoints');
    }

    public function down()
    {
        $this->forge->dropTable('api_endpoints');
    }
}
