<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\ApiEndpointModel;

class Items extends BaseController
{
    /** Companies that own items (kept separate). */
    private const COMPANIES = ['SKY', 'JOJO'];

    /** Accepted endpoint names (configured in Settings) for item-master data. */
    private const SYNC_ENDPOINTS = ['ItemMaster', 'Item', 'Items'];

    private ItemModel $items;
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->items     = new ItemModel();
        $this->endpoints = new ApiEndpointModel();
    }

    public function index()
    {
        $byCompany = [];
        foreach (self::COMPANIES as $company) {
            $byCompany[$company] = $this->items
                ->where('company', $company)
                ->orderBy('item_code', 'asc')
                ->findAll();
        }

        return $this->render('items/index', [
            'title'     => lang('App.itemMaster'),
            'companies' => self::COMPANIES,
            'byCompany' => $byCompany,
        ]);
    }

    /**
     * Pull item-master data for a company from SAP (base Web API URL + the
     * "ItemMaster" sub-endpoint) and upsert it into the items table.
     *
     * Expected response: a JSON array of objects, each with item code, name
     * and (optionally) a default warehouse — common key spellings accepted.
     */
    public function sync($company)
    {
        $company = strtoupper((string) $company);
        if (! in_array($company, self::COMPANIES, true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $baseUrl = (string) branding('apiUrl' . ucfirst(strtolower($company)), '');
        if ($baseUrl === '') {
            return redirect()->to('items')->with('error', lang('App.syncNoUrl', [$company]));
        }

        $endpoint = $this->endpoints->where('company', $company)->whereIn('name', self::SYNC_ENDPOINTS)->first();
        if ($endpoint === null) {
            return redirect()->to('items')->with('error', lang('App.syncNoEndpoint', [$company, self::SYNC_ENDPOINTS[0]]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');
        if (! sync_url_is_safe($url)) {
            return redirect()->to('items')->with('error', lang('App.syncUnsafeUrl'));
        }

        $options = ['timeout' => 10, 'http_errors' => false];
        $apiKey  = (string) branding('apiKey' . ucfirst(strtolower($company)), '');
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
                $wh   = trim((string) ($row['default_warehouse'] ?? $row['DefaultWarehouse'] ?? $row['DfltWH'] ?? $row['defaultWarehouse'] ?? $row['warehouse'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $seen[] = $code;

                $existing = $this->items->where('company', $company)->where('item_code', $code)->first();
                if ($existing === null) {
                    $this->items->insert([
                        'company'           => $company,
                        'item_code'         => $code,
                        'item_name'         => $name,
                        'default_warehouse' => $wh,
                        'created_at'        => date('Y-m-d H:i:s'),
                    ]);
                    $added++;
                } else {
                    $this->items->update($existing->id, [
                        'item_name'         => $name,
                        'default_warehouse' => $wh,
                    ]);
                }
            }

            if ($seen !== []) {
                $this->items->where('company', $company)->whereNotIn('item_code', $seen)->delete();
            }

            log_activity('item.sync', "ซิงก์ Item Master จาก SAP: [{$company}] +{$added}");
            return redirect()->to('items')->with('message', lang('App.syncDone', [$company, $added]));
        } catch (\Throwable $e) {
            log_activity('item.sync.fail', "ซิงก์ Item Master จาก SAP ไม่สำเร็จ: [{$company}] — " . $e->getMessage());
            return redirect()->to('items')->with('error', lang('App.syncFailed', [$company]));
        }
    }
}
