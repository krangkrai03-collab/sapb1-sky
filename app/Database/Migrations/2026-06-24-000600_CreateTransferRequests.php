<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransferRequests extends Migration
{
    public function up()
    {
        // Header
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'doc_no'           => ['type' => 'VARCHAR', 'constraint' => 30],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Open'],
            'business_partner' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'contact_person'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'ship_to'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'price_list'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'posting_date'     => ['type' => 'DATE', 'null' => true],
            'due_date'         => ['type' => 'DATE', 'null' => true],
            'document_date'    => ['type' => 'DATE', 'null' => true],
            'from_warehouse'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'to_warehouse'     => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'journal_remarks'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'remarks'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('doc_no');
        $this->forge->createTable('transfer_requests');

        // Line items
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'request_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'line_no'        => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'item_code'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'item_name'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'from_warehouse' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'to_warehouse'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'quantity'       => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'uom'            => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('request_id');
        $this->forge->createTable('transfer_request_items');
    }

    public function down()
    {
        $this->forge->dropTable('transfer_request_items');
        $this->forge->dropTable('transfer_requests');
    }
}
