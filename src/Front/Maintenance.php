<?php
namespace BakerStreet\ComingSoon\Front;

defined('ABSPATH') || exit;

use BakerStreet\ComingSoon\Plugin;

final class Maintenance {
    public static function init(): void {
        add_action('template_redirect', [__CLASS__, 'maybe_render'], 0);
    }

    private static function is_login_or_admin_request(): bool {
        if (is_admin()) {
            return true;
        }
        $pagenow = $GLOBALS['pagenow'] ?? '';
        if ($pagenow === 'wp-login.php') {
            return true;
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (is_string($uri) && preg_match('#/(wp-login\.php|wp-admin)#', $uri)) {
            return true;
        }
        return false;
    }

    private static function is_special_request(): bool {
        if (wp_doing_ajax()) return true;
        if (defined('REST_REQUEST') && REST_REQUEST) return true;
        if (defined('DOING_CRON') && DOING_CRON) return true;
        return false;
    }

    private static function request_is_wartungsmodus(): bool {
        $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        if (!is_string($path)) return false;
        $path = rtrim($path, '/');
        return ($path === '/wartungsmodus');
    }

    public static function maybe_render(): void {
        // Always allow logged in users
        if (is_user_logged_in()) {
            return;
        }

        // Let WP internals through
        if (self::is_login_or_admin_request() || self::is_special_request()) {
            return;
        }

        $opts = Plugin::get_options();
        $enabled = isset($opts['enabled']) && $opts['enabled'] === '1';

        // /wartungsmodus should always be reachable as a preview / fallback, even if not enabled
        if (self::request_is_wartungsmodus()) {
            self::render_page(false);
            exit;
        }

        if (!$enabled) {
            return;
        }

        self::render_page(true);
        exit;
    }

    private static function render_page(bool $as_maintenance): void {
        if (!headers_sent()) {
            if ($as_maintenance) {
                status_header(503);
                header('Retry-After: 3600');
            } else {
                status_header(200);
            }
            nocache_headers();
            header('X-Robots-Tag: noindex, nofollow, noarchive', true);
        }

        $view = BSCS_PLUGIN_DIR . 'views/public-page.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo 'Wartungsmodus';
        }
    }
}
