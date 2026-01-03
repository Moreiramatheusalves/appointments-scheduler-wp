<?php

/**
 * Plugin Name:       Appointments Scheduler BRENIAC
 * Plugin URI:        https://breniacsoftec.com/appointments-scheduler/
 * Description:       Plugin de Agendamento de Serviços.
 * Version:           1.0.3
 * Author:            BR Eniac SofTec
 * Author URI:        https://breniacsoftec.com
 * Text Domain:       appointments-scheduler-breniac
 * Domain Path:       /languages
 * Update URI:        https://github.com/Moreiramatheusalves/appointments-scheduler-wp
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WPAS_VERSION', '1.0.3');
define('WPAS_PLUGIN_FILE', __FILE__);
define('WPAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPAS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPAS_PLUGIN_DIR . 'includes/class-wpas-loader.php';
require_once WPAS_PLUGIN_DIR . 'includes/class-wpas-activator.php';
require_once WPAS_PLUGIN_DIR . 'includes/class-wpas-deactivator.php';
require_once WPAS_PLUGIN_DIR . 'includes/class-wpas-db.php';
require_once WPAS_PLUGIN_DIR . 'includes/class-wpas-plugin.php';

function wpas_activate_plugin()
{
    WPAS_Activator::activate();
}
register_activation_hook(__FILE__, 'wpas_activate_plugin');

function wpas_deactivate_plugin()
{
    WPAS_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'wpas_deactivate_plugin');

function wpas_run_plugin()
{
    $plugin = new WPAS_Plugin();
    $plugin->run();
}

add_action('plugins_loaded', 'wpas_run_plugin');


if (is_admin()) {
    $puc_path = WPAS_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
    if (file_exists($puc_path)) {
        require_once $puc_path;

        $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/Moreiramatheusalves/appointments-scheduler-wp/',
            __FILE__,
            'appointments-scheduler-wp'
        );

        $updateChecker->getVcsApi()->enableReleaseAssets();
    }
}
