<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Item Master enrichment to match the SAP Web API ItemMaster response:
 * each item now carries a default warehouse (`DefaultWhs`) and a list of
 * units of measure (`Uoms[]`). The default warehouse already maps onto the
 * existing `items.default_warehouse` column; this migration adds the base
 * (inventory) UoM on the item and a child `item_uoms` table for the rest.
 */
class CreateItemUoms extends Migration
{
    public function up()
    {
        // Base / inventory unit of measure for quick access on the item itself
        // (the Uoms[] row where IsInventoryUom = true).
        $this->forge->addColumn('items', [
            'inventory_uom' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'default_warehouse',
            ],
        ]);

        // One row per unit of measure of an item (SAP Uoms[]).
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'item_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'uom_entry'        => ['type' => 'INT', 'constraint' => 11], // signed: SAP uses -1 for "Manual"
            'uom_code'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'base_qty'         => ['type' => 'DECIMAL', 'constraint' => '18,6', 'default' => 1],
            'base_uom'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_inventory_uom' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        // A UoM appears at most once per item.
        $this->forge->addUniqueKey(['item_id', 'uom_entry']);
        // Delete an item's UoMs automatically when the item is removed.
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('item_uoms');
    }

    public function down()
    {
        $this->forge->dropTable('item_uoms');
        $this->forge->dropColumn('items', 'inventory_uom');
    }
}
