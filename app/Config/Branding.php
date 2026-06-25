<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Branding / theme defaults.
 *
 * These act as fallbacks; values can be overridden at runtime via the
 * codeigniter4/settings store (Branding.*) from the Settings UI.
 */
class Branding extends BaseConfig
{
    public string $locale   = 'en'; // ภาษาเริ่มต้น (th | en)
    public string $appName  = 'Admin Portal';
    public string $logoIcon = 'fas fa-shield-halved';
    public string $footer   = 'สร้างด้วย CodeIgniter 4 + Shield + AdminLTE';
    public string $version  = 'v2.0';

    // ข้อความแจ้งบนแดชบอร์ด (เว้นว่าง = ซ่อน)
    public string $dashboardNote = '';

    // Login page
    public string $loginBg   = ''; // background image path (relative to base_url)
    public string $loginHint = ''; // helper text under the login form (empty = hidden)

    // Web API endpoints (per company, kept separate)
    public string $apiUrlSky  = '';
    public string $apiUrlJojo = '';
    public string $apiKeySky  = '';
    public string $apiKeyJojo = '';

    // Session idle timeout in minutes (auto-logout when no activity)
    public string $sessionTimeout = '120';

    // Theme
    public string $themeColor        = 'primary'; // AdminLTE accent
    public string $themeSidebar      = 'dark';    // dark | light
    public string $themeSidebarColor = '';        // '' = default (bg-body-secondary), else a themeColors key
    public string $darkMode          = '0';       // '1' = dark-mode

    /** Allowed accent colours — Bootstrap 5 theme colours (AdminLTE 4). */
    public array $themeColors = [
        'primary'   => 'น้ำเงิน (Primary)',
        'secondary' => 'เทา (Secondary)',
        'success'   => 'เขียว (Success)',
        'info'      => 'ฟ้า (Info)',
        'warning'   => 'เหลือง (Warning)',
        'danger'    => 'แดง (Danger)',
        'dark'      => 'ดำ (Dark)',
    ];

    /**
     * Sidebar background colours: '' keeps the default neutral sidebar; any
     * other key tints the sidebar with the matching Bootstrap bg-* colour.
     * Light-ish backgrounds need dark text (data-bs-theme="light").
     */
    public array $sidebarLightBgs = ['warning', 'info'];
}
