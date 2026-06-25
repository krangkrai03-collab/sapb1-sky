<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\ItemUomModel;
use App\Models\ApiEndpointModel;

class Items extends BaseController
{
    /** Accepted endpoint names (configured in Settings) for item-master data. */
    private const SYNC_ENDPOINTS = ['ItemMaster', 'Item', 'Items'];

    private ItemModel $items;
    private ItemUomModel $uoms;
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->items     = new ItemModel();
        $this->uoms      = new ItemUomModel();
        $this->endpoints = new ApiEndpointModel();
    }

    public function index()
    {
        $list = $this->items->orderBy('item_code', 'asc')->findAll();

        // Attach each item's units of measure (inventory UoM first).
        $ids    = array_map(static fn ($it) => $it->id, $list);
        $byItem = [];
        if ($ids !== []) {
            $rows = $this->uoms->whereIn('item_id', $ids)
                ->orderBy('is_inventory_uom', 'DESC')
                ->orderBy('base_qty', 'ASC')
                ->findAll();
            foreach ($rows as $u) {
                $byItem[$u->item_id][] = $u;
            }
        }
        foreach ($list as $it) {
            $it->uoms = $byItem[$it->id] ?? [];
        }

        return $this->render('items/index', [
            'title' => lang('App.itemMaster'),
            'items' => $list,
        ]);
    }

    /**
     * Pull item-master data from SAP (base Web API URL + the "ItemMaster"
     * sub-endpoint) and upsert it into the items table.
     *
     * Expected response: a JSON array of objects, each with item code, name,
     * a default warehouse and a list of UoMs — common key spellings accepted.
     */
    public function sync()
    {
        $baseUrl = (string) branding('apiUrl', '');
        if ($baseUrl === '') {
            return redirect()->to('items')->with('error', lang('App.syncNoUrl'));
        }

        $endpoint = $this->endpoints->whereIn('name', self::SYNC_ENDPOINTS)->first();
        if ($endpoint === null) {
            return redirect()->to('items')->with('error', lang('App.syncNoEndpoint', [self::SYNC_ENDPOINTS[0]]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');
        if (! sync_url_is_safe($url)) {
            return redirect()->to('items')->with('error', lang('App.syncUnsafeUrl'));
        }

        $options = ['timeout' => 10, 'http_errors' => false];
        $apiKey  = (string) branding('apiKey', '');
        if ($apiKey !== '') {
            $options['headers'] = ['X-API-Key' => $apiKey];
        }

        try {
            $method   = strtoupper($endpoint->method ?? 'GET') === 'POST' ? 'POST' : 'GET';
            $client   = service('curlrequest', $options);
            $response = $client->request($method, $url);
            $data = json_decode((string) $response->getBody(), true);
            if (! is_array($data) || ! sap_ok($data)) {
                throw new \RuntimeException(is_array($data) ? (string) ($data['errMsg'] ?? 'SAP error') : 'invalid response');
            }

            $added = 0;
            $seen  = [];
            foreach (sap_rows($data, ['Items', 'ItemMaster']) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code = trim((string) ($row['item_code'] ?? $row['ItemCode'] ?? $row['itemCode'] ?? $row['code'] ?? ''));
                $name = trim((string) ($row['item_name'] ?? $row['ItemName'] ?? $row['itemName'] ?? $row['name'] ?? ''));
                $wh   = trim((string) ($row['default_warehouse'] ?? $row['DefaultWhs'] ?? $row['DefaultWarehouse'] ?? $row['DfltWH'] ?? $row['defaultWarehouse'] ?? $row['warehouse'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $seen[] = $code;

                $existing = $this->items->where('item_code', $code)->first();
                if ($existing === null) {
                    $this->items->insert([
                        'item_code'         => $code,
                        'item_name'         => $name,
                        'default_warehouse' => $wh,
                        'created_at'        => date('Y-m-d H:i:s'),
                    ]);
                    $itemId = (int) $this->items->getInsertID();
                    $added++;
                } else {
                    $itemId = (int) $existing->id;
                    $this->items->update($itemId, [
                        'item_name'         => $name,
                        'default_warehouse' => $wh,
                    ]);
                }

                // Replace the item's units of measure and cache its base UoM.
                $inventoryUom = $this->syncUoms($itemId, $row['Uoms'] ?? $row['uoms'] ?? $row['UoMs'] ?? null);
                $this->items->update($itemId, ['inventory_uom' => $inventoryUom]);
            }

            if ($seen !== []) {
                // Children (item_uoms) are removed via the ON DELETE CASCADE FK.
                $this->items->whereNotIn('item_code', $seen)->delete();
            }

            log_activity('item.sync', "ซิงก์ Item Master จาก SAP: +{$added}");
            return redirect()->to('items')->with('message', lang('App.syncDone', [$added]));
        } catch (\Throwable $e) {
            log_activity('item.sync.fail', 'ซิงก์ Item Master จาก SAP ไม่สำเร็จ — ' . $e->getMessage());
            return redirect()->to('items')->with('error', lang('App.syncFailed'));
        }
    }

    /**
     * Replace an item's units of measure (SAP Uoms[]) and return the code of
     * its inventory/base UoM (the row with IsInventoryUom = true), or null.
     */
    private function syncUoms(int $itemId, $rawUoms): ?string
    {
        $this->uoms->where('item_id', $itemId)->delete();
        if (! is_array($rawUoms)) {
            return null;
        }

        $now          = date('Y-m-d H:i:s');
        $inventoryUom = null;
        foreach ($rawUoms as $u) {
            if (! is_array($u)) {
                continue;
            }
            $uomCode = trim((string) ($u['UomCode'] ?? $u['uom_code'] ?? $u['Code'] ?? ''));
            if ($uomCode === '') {
                continue;
            }
            $isInventory = (bool) ($u['IsInventoryUom'] ?? $u['is_inventory_uom'] ?? false);

            $this->uoms->insert([
                'item_id'          => $itemId,
                'uom_entry'        => (int) ($u['UomEntry'] ?? $u['uom_entry'] ?? 0),
                'uom_code'         => $uomCode,
                'base_qty'         => (float) ($u['BaseQty'] ?? $u['base_qty'] ?? 1),
                'base_uom'         => trim((string) ($u['BaseUom'] ?? $u['base_uom'] ?? '')),
                'is_inventory_uom' => $isInventory ? 1 : 0,
                'created_at'       => $now,
            ]);

            if ($isInventory && $inventoryUom === null) {
                $inventoryUom = $uomCode;
            }
        }

        return $inventoryUom;
    }
}
