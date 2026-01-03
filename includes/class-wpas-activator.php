<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Activator
{
    public static function activate()
    {

        if (class_exists('WPAS_DB')) {
            WPAS_DB::create_tables();
        }

        $defaults = array(
            'notify_email'           => get_option('admin_email'),
            'min_cancel_hours'       => 12,
            'delete_data_on_uninstall' => 0,
        );


        $current = get_option('wpas_settings', array());
        if (! is_array($current)) {
            $current = array();
        }

        $merged = array_merge($defaults, $current);

        if (get_option('wpas_settings') === false) {
            add_option('wpas_settings', $merged);
        } else {
            update_option('wpas_settings', $merged);
        }

        if (defined('WPAS_VERSION')) {
            if (get_option('wpas_version') === false) {
                add_option('wpas_version', WPAS_VERSION);
            } else {
                update_option('wpas_version', WPAS_VERSION);
            }
        }

        $db_version = '1.0.0';
        if (get_option('wpas_db_version') === false) {
            add_option('wpas_db_version', $db_version);
        } else {
            update_option('wpas_db_version', $db_version);
        }

        self::maybe_migrate();
    }

    protected static function maybe_migrate()
    {
        $current_version = get_option('wpas_version');
        $db_version      = get_option('wpas_db_version');
    }
}
