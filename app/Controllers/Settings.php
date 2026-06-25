<?php

namespace App\Controllers;

use App\Models\ApiEndpointModel;

class Settings extends BaseController
{
    public function index()
    {
        return $this->render('settings/index', [
            'title'     => lang('App.settings'),
            'branding'  => config('Branding'),
            'endpoints' => (new ApiEndpointModel())->orderBy('name', 'asc')->findAll(),
        ]);
    }

    public function update()
    {
        $colors = implode(',', array_keys(config('Branding')->themeColors));

        $rules = [
            'app_name'      => ['label' => lang('App.appNameLabel'), 'rules' => 'required|max_length[100]'],
            'logo_icon'     => ['label' => lang('App.iconLabel'), 'rules' => 'permit_empty|max_length[100]'],
            'footer'        => ['label' => lang('App.footerLabel'), 'rules' => 'permit_empty|max_length[255]'],
            'version'       => ['label' => lang('App.versionLabel'), 'rules' => 'permit_empty|max_length[30]'],
            'login_hint'    => ['label' => lang('App.loginHintLabel'), 'rules' => 'permit_empty|max_length[255]'],
            'dashboard_note'=> ['label' => lang('App.dashboardNoteLabel'), 'rules' => 'permit_empty|max_length[255]'],
            'theme_color'   => ['label' => lang('App.accentColor'), 'rules' => "permit_empty|in_list[{$colors}]"],
            'theme_sidebar' => ['label' => lang('App.sidebarStyle'), 'rules' => 'permit_empty|in_list[dark,light]'],
            'theme_sidebar_color' => ['label' => lang('App.sidebarColor'), 'rules' => "permit_empty|in_list[{$colors}]"],
            'locale'        => ['label' => lang('App.defaultLang'), 'rules' => 'permit_empty|in_list[th,en]'],
            'session_timeout' => ['label' => lang('App.sessionTimeout'), 'rules' => 'permit_empty|is_natural_no_zero|less_than_equal_to[10080]'],
            'api_url'       => ['label' => lang('App.apiUrl'), 'rules' => 'permit_empty|valid_url_strict|max_length[255]'],
            'api_key'       => ['label' => lang('App.apiKey'), 'rules' => 'permit_empty|max_length[255]'],
        ];
        $hasUpload = $this->request->getFile('login_bg_file') && $this->request->getFile('login_bg_file')->isValid();
        if ($hasUpload) {
            $rules['login_bg_file'] = ['label' => lang('App.loginBgLabel'), 'rules' => 'is_image[login_bg_file]|max_size[login_bg_file,4096]|mime_in[login_bg_file,image/png,image/jpeg,image/gif,image/webp]'];
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settings = service('settings');
        $settings->set('Branding.locale', $this->request->getPost('locale') ?: 'th');
        $settings->set('Branding.sessionTimeout', (string) ((int) $this->request->getPost('session_timeout') ?: 120));
        $settings->set('Branding.appName', (string) $this->request->getPost('app_name'));
        $settings->set('Branding.logoIcon', $this->request->getPost('logo_icon') ?: 'fas fa-shield-halved');
        $settings->set('Branding.footer', (string) $this->request->getPost('footer'));
        $settings->set('Branding.version', (string) $this->request->getPost('version'));
        $settings->set('Branding.loginHint', (string) $this->request->getPost('login_hint'));
        $settings->set('Branding.dashboardNote', (string) $this->request->getPost('dashboard_note'));
        $settings->set('Branding.themeColor', $this->request->getPost('theme_color') ?: 'primary');
        $settings->set('Branding.themeSidebar', $this->request->getPost('theme_sidebar') ?: 'dark');
        $settings->set('Branding.themeSidebarColor', (string) $this->request->getPost('theme_sidebar_color'));
        $settings->set('Branding.darkMode', $this->request->getPost('dark_mode') ? '1' : '0');
        $settings->set('Branding.apiUrl', (string) $this->request->getPost('api_url'));
        $settings->set('Branding.apiKey', (string) $this->request->getPost('api_key'));

        // Login background: new upload wins; else honour explicit remove.
        if ($hasUpload) {
            $settings->set('Branding.loginBg', $this->storeLoginBg());
        } elseif ($this->request->getPost('remove_login_bg')) {
            $settings->set('Branding.loginBg', '');
        }

        log_activity('settings.update', 'อัปเดตการตั้งค่าระบบ');
        return redirect()->to('settings')->with('message', lang('App.settingsSaved'));
    }

    private function storeLoginBg(): string
    {
        $dir = FCPATH . 'uploads/branding/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $file    = $this->request->getFile('login_bg_file');
        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        return 'uploads/branding/' . $newName;
    }
}
