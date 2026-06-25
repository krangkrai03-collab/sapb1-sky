<?php

namespace App\Controllers;

use App\Models\BusinessPartnerModel;
use App\Models\ApiEndpointModel;

class BusinessPartners extends BaseController
{
    /** Companies that own business partners (kept separate). */
    private const COMPANIES = ['SKY', 'JOJO'];

    /** API endpoint (configured in Settings) that returns business-partner data. */
    private const SYNC_ENDPOINT = 'BusinessPartner';

    private BusinessPartnerModel $partners;
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->partners  = new BusinessPartnerModel();
        $this->endpoints = new ApiEndpointModel();
    }

    public function index()
    {
        $byCompany = [];
        foreach (self::COMPANIES as $company) {
            $byCompany[$company] = $this->partners
                ->where('company', $company)
                ->orderBy('bp_code', 'asc')
                ->findAll();
        }

        return $this->render('business_partners/index', [
            'title'     => lang('App.businessPartners'),
            'companies' => self::COMPANIES,
            'byCompany' => $byCompany,
        ]);
    }

    /**
     * Pull business-partner data for a company from SAP (base Web API URL +
     * the "BusinessPartner" sub-endpoint) and upsert it into the table.
     *
     * Expected response: a JSON array of objects with BP code, name and
     * (optionally) a ship-to address — common key spellings accepted.
     */
    public function sync($company)
    {
        $company = strtoupper((string) $company);
        if (! in_array($company, self::COMPANIES, true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $baseUrl = (string) branding('apiUrl' . ucfirst(strtolower($company)), '');
        if ($baseUrl === '') {
            return redirect()->to('business-partners')->with('error', lang('App.syncNoUrl', [$company]));
        }

        $endpoint = $this->endpoints->where('company', $company)->like('name', self::SYNC_ENDPOINT, 'none')->first();
        if ($endpoint === null) {
            return redirect()->to('business-partners')->with('error', lang('App.syncNoEndpoint', [$company, self::SYNC_ENDPOINT]));
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint->path, '/');
        if (! sync_url_is_safe($url)) {
            return redirect()->to('business-partners')->with('error', lang('App.syncUnsafeUrl'));
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
            $data     = json_decode((string) $response->getBody(), true);
            if (! is_array($data)) {
                throw new \RuntimeException('invalid response');
            }

            $added = 0;
            foreach ($data as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code   = trim((string) ($row['bp_code'] ?? $row['bpCode'] ?? $row['BPCode'] ?? $row['code'] ?? $row['CardCode'] ?? ''));
                $name   = trim((string) ($row['bp_name'] ?? $row['bpName'] ?? $row['BPName'] ?? $row['name'] ?? $row['CardName'] ?? ''));
                $shipTo = trim((string) ($row['ship_to'] ?? $row['shipTo'] ?? $row['ShipTo'] ?? $row['ship_to_address'] ?? ''));
                if ($code === '') {
                    continue;
                }

                $existing = $this->partners->where('company', $company)->where('bp_code', $code)->first();
                if ($existing === null) {
                    $this->partners->insert([
                        'company'    => $company,
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

            log_activity('bp.sync', "ซิงก์ Business Partner จาก SAP: [{$company}] +{$added}");
            return redirect()->to('business-partners')->with('message', lang('App.syncDone', [$company, $added]));
        } catch (\Throwable $e) {
            log_activity('bp.sync.fail', "ซิงก์ Business Partner จาก SAP ไม่สำเร็จ: [{$company}]");
            return redirect()->to('business-partners')->with('error', lang('App.syncFailed', [$company]));
        }
    }
}
