<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Appointments
{

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $base_url = add_query_arg(array('page' => 'wpas-appointments'), admin_url('admin.php'));

        $notice = isset($_GET['wpas_notice']) ? sanitize_key($_GET['wpas_notice']) : '';

        if (
            isset($_POST['wpas_manual_create_nonce']) &&
            wp_verify_nonce($_POST['wpas_manual_create_nonce'], 'wpas_manual_create')
        ) {
            $result = WPAS_Model_Appointment::create_manual($_POST, !empty($_POST['force_fit']));
            $redir = add_query_arg(
                array(
                    'page' => 'wpas-appointments',
                    'wpas_notice' => $result['success'] ? 'manual_created' : 'manual_error',
                    'wpas_msg' => rawurlencode($result['message']),
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redir);
            exit;
        }

        if (
            isset($_POST['wpas_manual_update_nonce'], $_POST['appointment_id']) &&
            wp_verify_nonce($_POST['wpas_manual_update_nonce'], 'wpas_manual_update_' . (int) $_POST['appointment_id'])
        ) {
            $id = (int) $_POST['appointment_id'];
            $result = WPAS_Model_Appointment::update_manual($id, $_POST, !empty($_POST['force_fit']));
            $redir = add_query_arg(
                array(
                    'page' => 'wpas-appointments',
                    'wpas_notice' => $result['success'] ? 'manual_updated' : 'manual_error',
                    'wpas_msg' => rawurlencode($result['message']),
                ),
                admin_url('admin.php')
            );
            wp_safe_redirect($redir);
            exit;
        }

        if (
            isset($_GET['wpas_change_status'], $_GET['new_status'], $_GET['_wpnonce']) &&
            ! empty($_GET['wpas_change_status']) &&
            ! empty($_GET['new_status'])
        ) {
            $appointment_id = (int) $_GET['wpas_change_status'];
            $new_status     = sanitize_text_field($_GET['new_status']);

            if (wp_verify_nonce($_GET['_wpnonce'], 'wpas_change_status_' . $appointment_id . '_' . $new_status)) {
                $updated = WPAS_Model_Appointment::update_status($appointment_id, $new_status);

                $redirect = remove_query_arg(array('wpas_change_status', 'new_status', '_wpnonce', 'wpas_notice', 'wpas_msg'), wp_get_referer() ?: $base_url);
                $redirect = add_query_arg(
                    array(
                        'wpas_notice' => $updated !== false ? 'status_updated' : 'status_error',
                    ),
                    $redirect
                );
                wp_safe_redirect($redirect);
                exit;
            }
        }

        if (
            isset($_GET['wpas_delete_appointment'], $_GET['_wpnonce']) &&
            ! empty($_GET['wpas_delete_appointment'])
        ) {
            $appointment_id = (int) $_GET['wpas_delete_appointment'];

            if (wp_verify_nonce($_GET['_wpnonce'], 'wpas_delete_appointment_' . $appointment_id)) {
                WPAS_Model_Appointment::delete($appointment_id);
                $redirect = remove_query_arg(array('wpas_delete_appointment', '_wpnonce', 'wpas_notice', 'wpas_msg'), wp_get_referer() ?: $base_url);
                $redirect = add_query_arg(array('wpas_notice' => 'deleted'), $redirect);
                wp_safe_redirect($redirect);
                exit;
            }
        }

        $filters = array(
            'professional_id' => isset($_GET['professional_id']) ? (int) $_GET['professional_id'] : 0,
            'status'          => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'date_from'       => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to'         => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
        );

        if (empty($filters['date_from']) && empty($filters['date_to'])) {
            $today = current_time('Y-m-d');
            $filters['date_from'] = $today;
            $filters['date_to']   = $today;
        }

        $query_args = array();

        if ($filters['professional_id'] > 0) {
            $query_args['professional_id'] = $filters['professional_id'];
        }

        if (! empty($filters['status'])) {
            $query_args['status'] = $filters['status'];
        }

        if (! empty($filters['date_from'])) {
            $query_args['date_from'] = $filters['date_from'];
        }

        if (! empty($filters['date_to'])) {
            $query_args['date_to'] = $filters['date_to'];
        }

        $query_args['order'] = 'date ASC, start_time ASC';

        $appointments  = WPAS_Model_Appointment::get_all($query_args);
        $professionals = WPAS_Model_Professional::get_all();
        $services      = WPAS_Model_Service::get_all();
        $statuses      = WPAS_Model_Appointment::get_statuses();

        $prof_index = array();
        foreach ($professionals as $p) {
            $prof_index[$p->id] = $p->name;
        }

        $serv_index = array();
        foreach ($services as $s) {
            $serv_index[$s->id] = $s->name;
        }

        $editing = (isset($_GET['action']) && sanitize_key($_GET['action']) === 'edit' && !empty($_GET['appointment_id']));
        $edit_appointment = null;
        if ($editing) {
            $edit_appointment = WPAS_Model_Appointment::get((int) $_GET['appointment_id']);
            if (!$edit_appointment) {
                $editing = false;
            }
        }

        $notice_message = isset($_GET['wpas_msg']) ? sanitize_text_field(wp_unslash($_GET['wpas_msg'])) : '';

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-appointments.php';
    }
}
