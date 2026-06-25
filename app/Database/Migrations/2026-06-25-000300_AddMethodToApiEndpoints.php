<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMethodToApiEndpoints extends Migration
{
    public function up()
    {
        $this->forge->addColumn('api_endpoints', [
            'method' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'GET', 'after' => 'name'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('api_endpoints', 'method');
    }
}
