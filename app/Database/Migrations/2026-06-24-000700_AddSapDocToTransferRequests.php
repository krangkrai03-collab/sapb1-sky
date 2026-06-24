<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSapDocToTransferRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transfer_requests', [
            'sap_doc_no' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'doc_no'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transfer_requests', 'sap_doc_no');
    }
}
