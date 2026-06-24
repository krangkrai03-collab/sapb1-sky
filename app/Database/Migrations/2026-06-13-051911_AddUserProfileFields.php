<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserProfileFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'username',
            ],
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['name', 'avatar']);
    }
}
