<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSyncStatusToTransferRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transfer_requests', [
            'sync_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending', 'after' => 'status'],
            'sync_error'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'sync_status'],
            'synced_at'   => ['type' => 'DATETIME', 'null' => true, 'after' => 'sync_error'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transfer_requests', ['sync_status', 'sync_error', 'synced_at']);
    }
}
