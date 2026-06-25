<?php

namespace App\Controllers;

use App\Models\ApiEndpointModel;

class ApiEndpoints extends BaseController
{
    private ApiEndpointModel $endpoints;

    public function __construct()
    {
        $this->endpoints = new ApiEndpointModel();
    }

    public function store()
    {
        $rules = [
            'name'   => ['label' => lang('App.endpointName'), 'rules' => 'required|max_length[100]'],
            'method' => ['label' => lang('App.endpointMethod'), 'rules' => 'required|in_list[GET,POST]'],
            'path'   => ['label' => lang('App.endpointPath'), 'rules' => 'required|max_length[255]'],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        $path = trim((string) $this->request->getPost('path'));

        if ($this->endpoints->where('name', $name)->first() !== null) {
            return redirect()->back()->withInput()->with('error', lang('App.endpointExists'));
        }

        $this->endpoints->insert([
            'name'       => $name,
            'method'     => strtoupper((string) $this->request->getPost('method')) === 'POST' ? 'POST' : 'GET',
            'path'       => $path,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        log_activity('endpoint.create', "เพิ่ม API endpoint: {$name} → {$path}");
        return redirect()->to('settings')->with('message', lang('App.endpointAdded'));
    }

    public function delete($id)
    {
        $endpoint = $this->endpoints->find((int) $id);
        if ($endpoint === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->endpoints->delete((int) $id);
        log_activity('endpoint.delete', "ลบ API endpoint: {$endpoint->name}");
        return redirect()->to('settings')->with('message', lang('App.endpointDeleted'));
    }
}
