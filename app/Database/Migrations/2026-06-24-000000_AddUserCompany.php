<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserCompany extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'ALL',
                'after'      => 'avatar',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'company');
    }
}
