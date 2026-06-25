<?php

namespace App\Controllers;

use App\Models\WarehouseModel;
use App\Models\ApiEndpointModel;

class Warehouses extends BaseController
{
    /** Accepted endpoint names (configured in Settings) for warehouse data. */
    private const SYNC_ENDPOINTS = ['Warehouses', 'Warehouse'];

    private WarehouseModel $warehouses;
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->warehouses = new WarehouseModel();
        $this->endpoints  = new ApiEndpointModel();
    }

    public function index()
    {
        return $this->render('warehouses/index', [
            'title'      => lang('App.warehouses'),
            'warehouses' => $this->warehouses->orderBy('code', 'asc')->findAll(),
        ]);
    }

    /**
     * Pull warehouse data from SAP (the configured Web API URL) and upsert it
     * into the warehouses table.
     *
     * Expected response: a JSON array of objects with a warehouse code and
     * name ([{"code": "WH01", "name": "Main"}, ...]); common key spellings
     * accepted. Plain strings are treated as code = name.
     */
    public function sync()
    {
        $baseUrl = (string) branding('apiUrl', '');
        if ($baseUrl === '') {
            return redirect()->to('warehouses')->with('error', lang('App.syncNoUrl'));
        }

        // Resolve the "Warehouses" sub-endpoint configured in Settings.
        $endpoint = $this->endpoints->whereIn('name', self::SYNC_ENDPOINTS)->first();
        if ($endpoint === null) {
            return redirect()->to('warehouses')->with('error', lang('App.syncNoEndpoint', [self::SYNC_ENDPOINTS[0]]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');
        if (! sync_url_is_safe($url)) {
            return redirect()->to('warehouses')->with('error', lang('App.syncUnsafeUrl'));
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
            foreach (sap_rows($data, ['Warehouses']) as $item) {
                if (is_string($item)) {
                    $code = $name = trim($item);
                } elseif (is_array($item)) {
                    $code = trim((string) ($item['code'] ?? $item['WhsCode'] ?? $item['warehouse_code'] ?? $item['warehouseCode'] ?? $item['WarehouseCode'] ?? ''));
                    $name = trim((string) ($item['name'] ?? $item['WhsName'] ?? $item['warehouse_name'] ?? $item['warehouseName'] ?? $item['WarehouseName'] ?? ''));
                } else {
                    continue;
                }
                if ($code === '') {
                    continue;
                }
                $seen[] = $code;

                $exists = $this->warehouses->where('code', $code)->first();
                if ($exists === null) {
                    $this->warehouses->insert([
                        'code'       => $code,
                        'name'       => $name !== '' ? $name : $code,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $added++;
                } else {
                    $this->warehouses->update($exists->id, ['name' => $name !== '' ? $name : $code]);
                }
            }

            // Mirror SAP: drop local rows no longer returned.
            if ($seen !== []) {
                $this->warehouses->whereNotIn('code', $seen)->delete();
            }

            log_activity('warehouse.sync', "ซิงก์คลังสินค้าจาก SAP: +{$added}");
            return redirect()->to('warehouses')->with('message', lang('App.syncDone', [$added]));
        } catch (\Throwable $e) {
            log_activity('warehouse.sync.fail', 'ซิงก์คลังสินค้าจาก SAP ไม่สำเร็จ — ' . $e->getMessage());
            return redirect()->to('warehouses')->with('error', lang('App.syncFailed'));
        }
    }
}
