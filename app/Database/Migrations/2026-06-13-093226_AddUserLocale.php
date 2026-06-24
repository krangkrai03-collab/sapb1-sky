<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserLocale extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
                'after'      => 'avatar',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'locale');
    }
}
