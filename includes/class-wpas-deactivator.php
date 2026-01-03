<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Deactivator
{
    public static function deactivate()
    {

        $cron_hooks = array(
            'wpas_cron_notify_upcoming_appointments',
            'wpas_cron_cleanup_old_appointments',
        );

        foreach ($cron_hooks as $hook) {
            while ($timestamp = wp_next_scheduled($hook)) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }
}
