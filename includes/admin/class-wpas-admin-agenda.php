<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Agenda
{

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $professionals = WPAS_Model_Professional::get_all(array('active' => null));

        $selected_professional = isset($_REQUEST['professional_id']) ? (int) $_REQUEST['professional_id'] : 0;
        if ($selected_professional <= 0 && ! empty($professionals)) {
            $selected_professional = (int) $professionals[0]->id;
        }

        $current_year  = (int) date('Y');
        $current_month = (int) date('n');

        $year  = isset($_GET['year']) ? (int) $_GET['year'] : $current_year;
        $month = isset($_GET['month']) ? (int) $_GET['month'] : $current_month;

        if ($month < 1 || $month > 12) {
            $month = $current_month;
        }

        if (
            isset($_POST['wpas_agenda_generate_nonce']) &&
            wp_verify_nonce($_POST['wpas_agenda_generate_nonce'], 'wpas_agenda_generate')
        ) {
            $gen_professional_id = isset($_POST['professional_id']) ? (int) $_POST['professional_id'] : 0;
            $date_from           = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to             = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            $start_time          = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
            $end_time            = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';
            $duration            = isset($_POST['duration']) ? (int) $_POST['duration'] : 30;
            $status              = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'available';
            $weekdays            = isset($_POST['weekdays']) ? (array) $_POST['weekdays'] : array();

            $weekdays = array_map('intval', $weekdays);

            if ($gen_professional_id > 0 && ! empty($date_from) && ! empty($date_to) && ! empty($start_time) && ! empty($end_time)) {
                $inserted = WPAS_Model_Agenda::generate_slots_range(
                    $gen_professional_id,
                    $date_from,
                    $date_to,
                    $start_time,
                    $end_time,
                    $duration,
                    $weekdays,
                    $status
                );

                echo '<div class="updated"><p>' .
                    sprintf(
                        esc_html__('%d slots foram criados/atualizados para o profissional selecionado.', 'wp-appointments-scheduler'),
                        (int) $inserted
                    ) .
                    '</p></div>';

                $db_error = WPAS_Model_Agenda::get_last_db_error();
                if (!empty($db_error) && current_user_can('manage_options')) {
                    echo '<div class="error"><p><strong>' .
                        esc_html__('Erro ao gravar no banco:', 'wp-appointments-scheduler') .
                        '</strong> ' . esc_html($db_error) .
                        '</p></div>';
                }

                $year  = (int) substr($date_from, 0, 4);
                $month = (int) substr($date_from, 5, 2);
                $selected_professional = $gen_professional_id;
            } else {
                echo '<div class="error"><p>' .
                    esc_html__('Preencha todos os campos obrigatórios para gerar a agenda.', 'wp-appointments-scheduler') .
                    '</p></div>';
            }
        }

        if (
            isset($_GET['clear_day'], $_GET['_wpnonce']) &&
            ! empty($selected_professional) &&
            wp_verify_nonce($_GET['_wpnonce'], 'wpas_agenda_clear_day_' . $selected_professional . '_' . sanitize_text_field($_GET['clear_day']))
        ) {
            $clear_date = sanitize_text_field($_GET['clear_day']);
            WPAS_Model_Agenda::delete_slots_by_date($selected_professional, $clear_date);

            echo '<div class="updated"><p>' .
                sprintf(
                    esc_html__('Todos os horários do dia %s foram removidos para o profissional selecionado.', 'wp-appointments-scheduler'),
                    esc_html($clear_date)
                ) .
                '</p></div>';
        }

        $view_date = isset($_GET['view_date']) ? sanitize_text_field($_GET['view_date']) : '';

        $day_slots = array();
        if ($selected_professional > 0 && ! empty($view_date)) {
            $day_slots = WPAS_Model_Agenda::get_all_slots($selected_professional, $view_date);
        }

        $overview = array();
        if ($selected_professional > 0) {
            $overview = WPAS_Model_Agenda::get_month_overview($selected_professional, $year, $month);
        }

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-agenda.php';
    }
}
