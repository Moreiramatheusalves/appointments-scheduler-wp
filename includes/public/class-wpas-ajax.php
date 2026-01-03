<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Ajax
{
    public function get_booking_data()
    {
        check_ajax_referer('wpas_booking_nonce', 'nonce');

        $professionals = WPAS_Model_Professional::get_all(array('active' => 1));
        $services      = WPAS_Model_Service::get_all(array('active' => 1));

        $prof_data = array();
        foreach ($professionals as $prof) {
            $prof_data[] = array(
                'id'   => (int) $prof->id,
                'name' => $prof->name,
            );
        }

        $serv_data = array();
        foreach ($services as $serv) {
            $serv_data[] = array(
                'id'       => (int) $serv->id,
                'name'     => $serv->name,
                'duration' => (int) $serv->duration,
                'price'    => WPAS_Helpers::format_price($serv->price),
            );
        }

        WPAS_Helpers::json_success(array(
            'professionals' => $prof_data,
            'services'      => $serv_data,
        ));
    }


    public function get_available_slots()
    {
        check_ajax_referer('wpas_booking_nonce', 'nonce');

        $professional_id = isset($_POST['professional_id']) ? (int) $_POST['professional_id'] : 0;
        $service_id      = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        $date            = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';

        if ($professional_id <= 0 || $service_id <= 0 || empty($date)) {
            WPAS_Helpers::json_error(
                __('Profissional, serviço e data são obrigatórios.', 'wp-appointments-scheduler')
            );
        }

        $service = WPAS_Model_Service::get($service_id);
        if (! $service || ! $service->active) {
            WPAS_Helpers::json_error(
                __('Serviço inválido ou inativo.', 'wp-appointments-scheduler')
            );
        }

        $duration = (int)$service->duration;
        if ($duration <= 0) {
            $duration = 1;
        }

        $starts = WPAS_Model_Agenda::get_start_slots_for_duration($professional_id, $date, $duration);

        $formatted = array();
        foreach ($starts as $slot) {
            $start = substr($slot->start_time, 0, 5);
            $end   = substr($slot->end_time, 0, 5);

            $formatted[] = array(
                'id'    => (int) $slot->id,
                'start' => $start,
                'end'   => $end,
                'value' => $slot->start_time . '|' . $slot->end_time . '|' . $slot->id,
                'label' => $start . ' - ' . $end,
            );
        }

        WPAS_Helpers::json_success(array(
            'slots' => $formatted,
        ));
    }

    public function create_booking()
    {
        check_ajax_referer('wpas_booking_nonce', 'nonce');

        $raw = array(
            'customer_name'   => isset($_POST['customer_name']) ? $_POST['customer_name'] : '',
            'customer_email'  => isset($_POST['customer_email']) ? $_POST['customer_email'] : '',
            'customer_phone'  => isset($_POST['customer_phone']) ? $_POST['customer_phone'] : '',
            'professional_id' => isset($_POST['professional_id']) ? $_POST['professional_id'] : '',
            'service_id'      => isset($_POST['service_id']) ? $_POST['service_id'] : '',
            'date'            => isset($_POST['date']) ? $_POST['date'] : '',
            'time_slot'       => isset($_POST['time_slot']) ? $_POST['time_slot'] : '',
        );

        $rules = array(
            'customer_name'   => array('required', 'min:3'),
            'customer_email'  => array('email'),
            'customer_phone'  => array('phone'),
            'professional_id' => array('required', 'positive'),
            'service_id'      => array('required', 'positive'),
            'date'            => array('required', 'date'),
            'time_slot'       => array('required'),
        );

        $errors = WPAS_Validator::validate($raw, $rules);

        if (! empty($errors)) {
            WPAS_Helpers::json_error(
                __('Existem erros nos dados enviados.', 'wp-appointments-scheduler'),
                array('errors' => $errors)
            );
        }

        $customer_name   = WPAS_Helpers::sanitize_text($raw['customer_name']);
        $customer_email  = WPAS_Helpers::sanitize_email($raw['customer_email']);
        $customer_phone  = WPAS_Helpers::sanitize_phone($raw['customer_phone']);
        $professional_id = (int) $raw['professional_id'];
        $service_id      = (int) $raw['service_id'];
        $date            = sanitize_text_field($raw['date']);
        $time_slot       = sanitize_text_field($raw['time_slot']);

        if (function_exists('mb_substr')) {
            $customer_name  = mb_substr($customer_name, 0, 80);
            $customer_email = mb_substr($customer_email, 0, 120);
            $customer_phone = mb_substr($customer_phone, 0, 20);
        } else {
            $customer_name  = substr($customer_name, 0, 80);
            $customer_email = substr($customer_email, 0, 120);
            $customer_phone = substr($customer_phone, 0, 20);
        }

        $customer_phone = preg_replace('/\D+/', '', (string) $customer_phone);

        if (strlen($customer_phone) > 11 && strpos($customer_phone, '55') === 0) {
            $customer_phone = substr($customer_phone, 2);
        }

        if (strlen($customer_phone) > 11) {
            $customer_phone = substr($customer_phone, 0, 11);
        }

        $professional = WPAS_Model_Professional::get($professional_id);
        if (! $professional || ! $professional->active) {
            WPAS_Helpers::json_error(
                __('Profissional inválido ou inativo.', 'wp-appointments-scheduler')
            );
        }

        $service = WPAS_Model_Service::get($service_id);
        if (! $service || ! $service->active) {
            WPAS_Helpers::json_error(
                __('Serviço inválido ou inativo.', 'wp-appointments-scheduler')
            );
        }

        $parts   = explode('|', $time_slot);
        $start   = isset($parts[0]) ? $parts[0] : '';
        $end     = isset($parts[1]) ? $parts[1] : '';
        $slot_id = isset($parts[2]) ? (int) $parts[2] : null;

        if (! WPAS_Validator::time($start) || ! WPAS_Validator::time($end)) {
            WPAS_Helpers::json_error(
                __('Horário selecionado é inválido.', 'wp-appointments-scheduler')
            );
        }

        if (empty($slot_id)) {
            WPAS_Helpers::json_error(
                __('Não foi possível identificar o horário selecionado. Atualize a página e tente novamente.', 'wp-appointments-scheduler')
            );
        }

        $duration = (int)$service->duration;
        if ($duration <= 0) {
            $duration = 1;
        }

        $reservation = WPAS_Model_Agenda::reserve_slots_for_duration(
            $slot_id,
            $professional_id,
            $date,
            $duration
        );

        if (! $reservation) {
            WPAS_Helpers::json_error(
                __('O horário selecionado não está mais disponível para a duração desse serviço. Por favor, escolha outro horário.', 'wp-appointments-scheduler')
            );
        }

        $start = $reservation['start_time'];
        $end   = $reservation['end_time'];

        $appointment_id = WPAS_Model_Appointment::create(array(
            'customer_name'   => $customer_name,
            'customer_email'  => $customer_email,
            'customer_phone'  => $customer_phone,
            'professional_id' => $professional_id,
            'service_id'      => $service_id,
            'slot_id'         => $slot_id,
            'date'            => $date,
            'start_time'      => $start,
            'end_time'        => $end,
            'status'          => 'pending',
        ));

        if (! $appointment_id) {
            WPAS_Model_Agenda::release_range($professional_id, $date, $start, $end);

            WPAS_Helpers::json_error(
                __('Erro ao criar o agendamento. Tente novamente.', 'wp-appointments-scheduler')
            );
        }

        WPAS_Helpers::json_success(array(
            'message'        => __('Agendamento realizado com sucesso! Em breve o profissional poderá entrar em contato para confirmar.', 'wp-appointments-scheduler'),
            'appointment_id' => (int) $appointment_id,
        ));
    }
}
