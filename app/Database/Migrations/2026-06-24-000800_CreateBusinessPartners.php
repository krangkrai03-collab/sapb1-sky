<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessPartners extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'company'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'bp_code'    => ['type' => 'VARCHAR', 'constraint' => 60],
            'bp_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'ship_to'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('company');
        // One BP code per company.
        $this->forge->addUniqueKey(['company', 'bp_code']);
        $this->forge->createTable('business_partners');
    }

    public function down()
    {
        $this->forge->dropTable('business_partners');
    }
}
