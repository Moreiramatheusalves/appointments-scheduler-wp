<?php
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$settings = get_option('wpas_settings', array());
$delete_data = ! empty($settings['delete_data_on_uninstall']);

if (! $delete_data) {
    return;
}

$tables = array(
    $wpdb->prefix . 'wpas_professionals',
    $wpdb->prefix . 'wpas_categories',
    $wpdb->prefix . 'wpas_services',
    $wpdb->prefix . 'wpas_professional_service',
    $wpdb->prefix . 'wpas_agenda_slots',
    $wpdb->prefix . 'wpas_appointments',
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

delete_option('wpas_settings');
delete_option('wpas_version');
delete_option('wpas_db_version');
