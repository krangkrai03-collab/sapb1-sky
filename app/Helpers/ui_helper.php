<?php

/**
 * UI helpers for the admin layout (branding, theme, permissions, menu).
 * Branding values: settings store (Branding.*) overrides config defaults.
 */

if (! function_exists('branding')) {
    function branding(string $key, $default = null)
    {
        $cfg = config('Branding');
        // DB override via codeigniter4/settings, fall back to config property.
        $val = setting('Branding.' . $key);
        if ($val !== null && $val !== '') {
            return $val;
        }
        return $cfg->{$key} ?? $default;
    }
}

if (! function_exists('app_name')) {
    function app_name(): string
    {
        return (string) (branding('appName') ?: 'Admin Portal');
    }
}

if (! function_exists('theme_color')) {
    function theme_color(): string
    {
        $allowed = array_keys(config('Branding')->themeColors);
        $c       = (string) branding('themeColor', 'primary');
        return in_array($c, $allowed, true) ? $c : 'primary';
    }
}

if (! function_exists('theme_sidebar')) {
    function theme_sidebar(): string
    {
        return branding('themeSidebar', 'dark') === 'light' ? 'light' : 'dark';
    }
}

if (! function_exists('sidebar_color')) {
    /**
     * Validated sidebar background colour key, or '' for the default neutral
     * sidebar. Must be one of the configured accent colours.
     */
    function sidebar_color(): string
    {
        $allowed = array_keys(config('Branding')->themeColors);
        $c       = (string) branding('themeSidebarColor', '');
        return in_array($c, $allowed, true) ? $c : '';
    }
}

if (! function_exists('sidebar_theme')) {
    /**
     * data-bs-theme value for the sidebar: 'light' for light-ish coloured
     * backgrounds, otherwise it honours the dark/light sidebar style.
     */
    function sidebar_theme(): string
    {
        $color = sidebar_color();
        if ($color !== '') {
            return in_array($color, config('Branding')->sidebarLightBgs, true) ? 'light' : 'dark';
        }
        return theme_sidebar();
    }
}

if (! function_exists('sidebar_bg_class')) {
    /** Bootstrap background class for the sidebar. */
    function sidebar_bg_class(): string
    {
        $color = sidebar_color();
        return $color !== '' ? 'bg-' . $color : 'bg-body-secondary';
    }
}

if (! function_exists('dark_mode')) {
    function dark_mode(): bool
    {
        return (string) branding('darkMode', '0') === '1';
    }
}

if (! function_exists('local_datetime')) {
    /**
     * Format a datetime stored in the app timezone (UTC) for display in the
     * local timezone. Storage stays UTC; only presentation is localised.
     */
    function local_datetime(?string $datetime, string $format = 'Y-m-d H:i:s', string $tz = 'Asia/Bangkok'): string
    {
        if (empty($datetime)) {
            return '';
        }
        try {
            $appTz = config('App')->appTimezone ?: 'UTC';
            $dt    = new \DateTime($datetime, new \DateTimeZone($appTz));
            $dt->setTimezone(new \DateTimeZone($tz));
            return $dt->format($format);
        } catch (\Throwable $e) {
            return (string) $datetime;
        }
    }
}

if (! function_exists('sync_url_is_safe')) {
    /**
     * SSRF guard for outbound sync requests: allow only http(s) to a host that
     * resolves to public IPs (blocks localhost, private ranges and cloud
     * metadata like 169.254.169.254).
     */
    function sync_url_is_safe(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }
        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];
        $ips  = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            foreach (@dns_get_record($host, DNS_A + DNS_AAAA) ?: [] as $r) {
                $ips[] = $r['ip'] ?? $r['ipv6'] ?? null;
            }
            if ($ips === [] && ($v4 = gethostbynamel($host)) !== false) {
                $ips = $v4;
            }
        }
        if ($ips === []) {
            return false; // unresolved -> treat as unsafe
        }

        foreach ($ips as $ip) {
            if ($ip === null || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false; // private or reserved range
            }
        }

        return true;
    }
}

if (! function_exists('sap_rows')) {
    /**
     * Extract the data array from a SAP response, which may be a plain list
     * or wrapped: {"Status":"Success","Warehouses":[...]}. Tries the given
     * preferred keys first, then common wrappers, then the first list value.
     */
    function sap_rows($data, array $preferKeys = []): array
    {
        if (! is_array($data)) {
            return [];
        }
        if (array_is_list($data)) {
            return $data;
        }
        foreach (array_merge($preferKeys, ['value', 'data', 'Data', 'result', 'Result', 'rows']) as $k) {
            if (isset($data[$k]) && is_array($data[$k])) {
                return array_values($data[$k]);
            }
        }
        foreach ($data as $v) {
            if (is_array($v) && (array_is_list($v) || $v === [])) {
                return array_values($v);
            }
        }
        return [];
    }
}

if (! function_exists('sap_ok')) {
    /** True unless the response carries an explicit non-success Status. */
    function sap_ok($data): bool
    {
        return ! (is_array($data) && isset($data['Status']) && strtolower((string) $data['Status']) !== 'success');
    }
}

if (! function_exists('user_can')) {
    /**
     * Null-safe permission check for the current user.
     */
    function user_can(string $permission): bool
    {
        $user = auth()->user();
        return $user !== null && $user->can($permission);
    }
}

if (! function_exists('active_menu')) {
    /**
     * Returns 'active' when the first URI segment matches $segment.
     */
    function active_menu(string $segment, string $class = 'active'): string
    {
        $current = service('uri')->getSegment(1) ?: 'dashboard';
        return $current === $segment ? $class : '';
    }
}

if (! function_exists('activity_record')) {
    /**
     * Core activity-log writer. Pass an explicit $actor (e.g. from a Shield
     * event) or leave null to use the currently authenticated user. Never
     * throws and no-ops if the table is missing.
     */
    function activity_record(string $action, ?string $description = null, $actor = null): void
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('activity_logs')) {
                return;
            }
            if ($actor === null && function_exists('auth') && auth()->loggedIn()) {
                $actor = auth()->user();
            }
            $db->table('activity_logs')->insert([
                'user_id'     => $actor?->id,
                'username'    => $actor?->username,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => service('request')->getIPAddress(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must never break the request.
        }
    }
}

if (! function_exists('log_activity')) {
    /** Log an activity attributed to the current user. */
    function log_activity(string $action, ?string $description = null): void
    {
        activity_record($action, $description);
    }
}

if (! function_exists('avatar_icons')) {
    /** Selectable avatar icons (Font Awesome class => Bootstrap colour). */
    function avatar_icons(): array
    {
        return [
            'fas fa-user-tie'       => 'primary',
            'fas fa-user-astronaut' => 'info',
            'fas fa-user-ninja'     => 'dark',
            'fas fa-user-secret'    => 'danger',
            'fas fa-user-graduate'  => 'success',
        ];
    }
}

if (! function_exists('avatar_icon')) {
    /**
     * The user's chosen avatar icon class, or the default user icon.
     * Falls back to the default for empty values or legacy uploaded paths.
     */
    function avatar_icon(?string $avatar): string
    {
        return array_key_exists((string) $avatar, avatar_icons()) ? (string) $avatar : 'fas fa-user-circle';
    }
}

if (! function_exists('avatar_color')) {
    /** Bootstrap colour for the chosen avatar icon (default for the fallback). */
    function avatar_color(?string $avatar): string
    {
        return avatar_icons()[(string) $avatar] ?? 'secondary';
    }
}
