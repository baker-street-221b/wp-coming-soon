<?php
/**
 * Plugin Name: Baker Street – Coming Soon
 * Description: Wartungsmodus/Coming Soon Seite für nicht eingeloggte Besucher inkl. /wartungsmodus (noindex).
 * Version: 1.2.0
 * Author: Baker Street
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('BSCS_VERSION', '1.2.0');
define('BSCS_PLUGIN_FILE', __FILE__);
define('BSCS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BSCS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoload (Bedrock loads its own vendor/autoload.php; this is just a fallback)
$composerAutoload = BSCS_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Lightweight PSR-4 autoloader so the plugin works without running composer inside the plugin.
spl_autoload_register(function ($class) {
    $prefix = 'BakerStreet\\ComingSoon\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $rel = substr($class, strlen($prefix));
    $rel = str_replace('\\', DIRECTORY_SEPARATOR, $rel);
    $path = BSCS_PLUGIN_DIR . 'src' . DIRECTORY_SEPARATOR . $rel . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

add_action('plugins_loaded', function () {
    if (class_exists('BakerStreet\\ComingSoon\\Plugin')) {
        \BakerStreet\ComingSoon\Plugin::init();
    }
});

register_activation_hook(__FILE__, function () {
    if (class_exists('BakerStreet\\ComingSoon\\Plugin')) {
        \BakerStreet\ComingSoon\Plugin::on_activate();
    }
});

// IMPORTANT: We do not remove /wartungsmodus files on deactivation on purpose.
// Requirement: /wartungsmodus should remain reachable even when plugin is deactivated.
register_deactivation_hook(__FILE__, function () {
    if (class_exists('BakerStreet\\ComingSoon\\Plugin')) {
        \BakerStreet\ComingSoon\Plugin::on_deactivate();
    }
});
