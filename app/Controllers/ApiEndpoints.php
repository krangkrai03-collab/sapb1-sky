<?php

namespace App\Controllers;

use App\Models\ApiEndpointModel;

class ApiEndpoints extends BaseController
{
    /** Companies that own API endpoints (kept separate). */
    public const COMPANIES = ['SKY', 'JOJO'];

    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->endpoints = new ApiEndpointModel();
    }

    public function store()
    {
        $companies = implode(',', self::COMPANIES);
        $rules     = [
            'company' => ['label' => lang('App.fCompany'), 'rules' => "required|in_list[{$companies}]"],
            'name'    => ['label' => lang('App.endpointName'), 'rules' => 'required|max_length[100]'],
            'path'    => ['label' => lang('App.endpointPath'), 'rules' => 'required|max_length[255]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $company = $this->request->getPost('company');
        $name    = trim((string) $this->request->getPost('name'));
        $path    = trim((string) $this->request->getPost('path'));

        if ($this->endpoints->where('company', $company)->where('name', $name)->first() !== null) {
            return redirect()->back()->withInput()->with('error', lang('App.endpointExists'));
        }

        $this->endpoints->insert([
            'company'    => $company,
            'name'       => $name,
            'path'       => $path,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_activity('endpoint.create', "เพิ่ม API endpoint: [{$company}] {$name} → {$path}");
        return redirect()->to('settings')->with('message', lang('App.endpointAdded'));
    }

    public function delete($id)
    {
        $endpoint = $this->endpoints->find((int) $id);
        if ($endpoint === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->endpoints->delete((int) $id);
        log_activity('endpoint.delete', "ลบ API endpoint: [{$endpoint->company}] {$endpoint->name}");
        return redirect()->to('settings')->with('message', lang('App.endpointDeleted'));
    }
}
