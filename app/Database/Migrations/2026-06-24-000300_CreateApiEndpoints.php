<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiEndpoints extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'path'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company');
        // No duplicate endpoint name within the same company.
        $this->forge->addUniqueKey(['company', 'name']);
        $this->forge->createTable('api_endpoints');
    }

    public function down()
    {
        $this->forge->dropTable('api_endpoints');
    }
}
