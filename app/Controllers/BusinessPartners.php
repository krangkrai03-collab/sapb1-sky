<?php

namespace App\Controllers;

use App\Models\BusinessPartnerModel;
use App\Models\ApiEndpointModel;

class BusinessPartners extends BaseController
{
    /** Accepted endpoint names (configured in Settings) for business-partner data. */
    private const SYNC_ENDPOINTS = ['BusinessPartner', 'BusinessPartners', 'BP'];

    private BusinessPartnerModel $partners;
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->partners  = new BusinessPartnerModel();
        $this->endpoints = new ApiEndpointModel();
    }

    public function index()
    {
        return $this->render('business_partners/index', [
            'title'    => lang('App.businessPartners'),
            'partners' => $this->partners->orderBy('bp_code', 'asc')->findAll(),
        ]);
    }

    /**
     * Pull business-partner data from SAP (base Web API URL + the
     * "BusinessPartner" sub-endpoint) and upsert it into the table.
     *
     * Expected response: a JSON array of objects with BP code, name and
     * (optionally) a ship-to address — common key spellings accepted.
     */
    public function sync()
    {
        $baseUrl = (string) branding('apiUrl', '');
        if ($baseUrl === '') {
            return redirect()->to('business-partners')->with('error', lang('App.syncNoUrl'));
        }

        $endpoint = $this->endpoints->whereIn('name', self::SYNC_ENDPOINTS)->first();
        if ($endpoint === null) {
            return redirect()->to('business-partners')->with('error', lang('App.syncNoEndpoint', [self::SYNC_ENDPOINTS[0]]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');
        if (! sync_url_is_safe($url)) {
            return redirect()->to('business-partners')->with('error', lang('App.syncUnsafeUrl'));
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
            foreach (sap_rows($data, ['BusinessPartners', 'BPList']) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code   = trim((string) ($row['bp_code'] ?? $row['CardCode'] ?? $row['bpCode'] ?? $row['BPCode'] ?? $row['code'] ?? ''));
                $name   = trim((string) ($row['bp_name'] ?? $row['CardName'] ?? $row['bpName'] ?? $row['BPName'] ?? $row['name'] ?? ''));
                $shipTo = trim((string) ($row['ship_to'] ?? $row['ShipToDef'] ?? $row['shipTo'] ?? $row['ShipTo'] ?? $row['ship_to_address'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $seen[] = $code;

                $existing = $this->partners->where('bp_code', $code)->first();
                if ($existing === null) {
                    $this->partners->insert([
                        'bp_code'    => $code,
                        'bp_name'    => $name,
                        'ship_to'    => $shipTo,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $added++;
                } else {
                    $this->partners->update($existing->id, ['bp_name' => $name, 'ship_to' => $shipTo]);
                }
            }

            if ($seen !== []) {
                $this->partners->whereNotIn('bp_code', $seen)->delete();
            }

            log_activity('bp.sync', "ซิงก์ Business Partner จาก SAP: +{$added}");
            return redirect()->to('business-partners')->with('message', lang('App.syncDone', [$added]));
        } catch (\Throwable $e) {
            log_activity('bp.sync.fail', 'ซิงก์ Business Partner จาก SAP ไม่สำเร็จ — ' . $e->getMessage());
            return redirect()->to('business-partners')->with('error', lang('App.syncFailed'));
        }
    }
}
