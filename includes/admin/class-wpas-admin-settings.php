<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Settings
{

    protected $option_name = 'wpas_settings';

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['wpas_settings_nonce']) && wp_verify_nonce($_POST['wpas_settings_nonce'], 'wpas_save_settings')) {

            $settings = array(
                'notify_email'             => isset($_POST['notify_email']) ? sanitize_email($_POST['notify_email']) : '',
                'min_cancel_hours'         => isset($_POST['min_cancel_hours']) ? (int) $_POST['min_cancel_hours'] : 12,
                'delete_data_on_uninstall' => isset($_POST['delete_data_on_uninstall']) ? 1 : 0,
            );


            update_option($this->option_name, $settings);

            echo '<div class="updated"><p>' . esc_html__('Configurações atualizadas.', 'wp-appointments-scheduler') . '</p></div>';
        }

        if (isset($_POST['wpas_reconcile_slots_nonce']) && wp_verify_nonce($_POST['wpas_reconcile_slots_nonce'], 'wpas_reconcile_slots')) {
            $affected = WPAS_Model_Agenda::reconcile_slots();
            if ($affected === false) {
                echo '<div class="error"><p>' . esc_html__('Não foi possível reconciliar os horários.', 'wp-appointments-scheduler') . '</p></div>';
            } else {
                echo '<div class="updated"><p>' . sprintf(
                    esc_html__('Reconciliador executado. Linhas ajustadas: %d', 'wp-appointments-scheduler'),
                    (int) $affected
                ) . '</p></div>';
            }
        }

        $settings = get_option($this->option_name, array(
            'notify_email'     => get_option('admin_email'),
            'min_cancel_hours' => 12,
        ));

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-settings.php';
    }
}
