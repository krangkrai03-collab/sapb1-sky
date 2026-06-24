<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\ApiEndpointModel;

class Items extends BaseController
{
    /** Companies that own items (kept separate). */
    private const COMPANIES = ['SKY', 'JOJO'];

    /** API endpoint (configured in Settings) that returns item-master data. */
    private const SYNC_ENDPOINT = 'ItemMaster';

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

        $endpoint = $this->endpoints->where('company', $company)->like('name', self::SYNC_ENDPOINT, 'none')->first();
        if ($endpoint === null) {
            return redirect()->to('items')->with('error', lang('App.syncNoEndpoint', [$company, self::SYNC_ENDPOINT]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');

        $options = ['timeout' => 10, 'http_errors' => false];
        $apiKey  = (string) branding('apiKey' . ucfirst(strtolower($company)), '');
        if ($apiKey !== '') {
            $options['headers'] = ['X-API-Key' => $apiKey];
        }

        try {
            $client   = service('curlrequest', $options);
            $response = $client->get($url);
            $data     = json_decode((string) $response->getBody(), true);
            if (! is_array($data)) {
                throw new \RuntimeException('invalid response');
            }

            $added = 0;
            foreach ($data as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code = trim((string) ($row['item_code'] ?? $row['itemCode'] ?? $row['ItemCode'] ?? $row['code'] ?? ''));
                $name = trim((string) ($row['item_name'] ?? $row['itemName'] ?? $row['ItemName'] ?? $row['name'] ?? ''));
                $wh   = trim((string) ($row['default_warehouse'] ?? $row['defaultWarehouse'] ?? $row['DefaultWarehouse'] ?? $row['warehouse'] ?? ''));
                if ($code === '') {
                    continue;
                }

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

            log_activity('item.sync', "ซิงก์ Item Master จาก SAP: [{$company}] +{$added}");
            return redirect()->to('items')->with('message', lang('App.syncDone', [$company, $added]));
        } catch (\Throwable $e) {
            log_activity('item.sync.fail', "ซิงก์ Item Master จาก SAP ไม่สำเร็จ: [{$company}]");
            return redirect()->to('items')->with('error', lang('App.syncFailed', [$company]));
        }
    }
}
