<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Model_Appointment
{

    protected static function table()
    {
        return WPAS_DB::table('appointments');
    }

    /**
     * Cache de colunas da tabela (para suportar instalações antigas).
     * @var array|null
     */
    protected static $table_columns = null;

    /**
     * Retorna colunas existentes na tabela de appointments (cacheado).
     */
    protected static function table_columns()
    {
        global $wpdb;

        if (self::$table_columns !== null) {
            return self::$table_columns;
        }

        $cols = array();
        $rows = $wpdb->get_results('DESCRIBE ' . self::table());
        if (!empty($rows)) {
            foreach ($rows as $r) {
                if (!empty($r->Field)) {
                    $cols[] = $r->Field;
                }
            }
        }

        self::$table_columns = $cols;
        return self::$table_columns;
    }

    protected static function has_column($column)
    {
        $column = (string) $column;
        if ($column === '') {
            return false;
        }
        return in_array($column, self::table_columns(), true);
    }

    protected static function generate_public_id()
    {
        return substr(wp_generate_password(16, false, false), 0, 16);
    }

    protected static function normalize_time($time)
    {
        $time = trim((string) $time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        return '';
    }

    public static function get_statuses()
    {
        return array(
            'pending'   => __('Pendente', 'wp-appointments-scheduler'),
            'confirmed' => __('Confirmado', 'wp-appointments-scheduler'),
            'cancelled' => __('Cancelado', 'wp-appointments-scheduler'),
            'no_show'   => __('Não compareceu', 'wp-appointments-scheduler'),
        );
    }

    public static function get_slot_occupying_statuses()
    {
        return array('pending', 'confirmed');
    }

    public static function get_slot_releasing_statuses()
    {
        return array('cancelled');
    }

    public static function status_occupies_slot($status)
    {
        return in_array($status, self::get_slot_occupying_statuses(), true);
    }

    public static function status_releases_slot($status)
    {
        return in_array($status, self::get_slot_releasing_statuses(), true);
    }

    public static function create($data)
    {
        global $wpdb;

        $now = current_time('mysql');

        $date = sanitize_text_field($data['date'] ?? '');
        $start_time = self::normalize_time($data['start_time'] ?? '');
        $end_time   = self::normalize_time($data['end_time'] ?? '');

        $insert = array(
            'customer_name'   => WPAS_Helpers::sanitize_text($data['customer_name'] ?? ''),
            'customer_email'  => WPAS_Helpers::sanitize_email($data['customer_email'] ?? ''),
            'customer_phone'  => WPAS_Helpers::sanitize_phone($data['customer_phone'] ?? ''),
            'professional_id' => (int) ($data['professional_id'] ?? 0),
            'service_id'      => (int) ($data['service_id'] ?? 0),
            'slot_id'         => ! empty($data['slot_id']) ? (int) $data['slot_id'] : null,
            'date'            => $date,
            'start_time'      => $start_time,
            'end_time'        => $end_time,
            'status'          => $data['status'] ?? 'pending',
            'notes'           => isset($data['notes']) ? wp_kses_post($data['notes']) : '',
        );

        if (self::has_column('public_id')) {
            $public_id = isset($data['public_id']) ? sanitize_text_field($data['public_id']) : '';
            if (empty($public_id)) {
                $public_id = self::generate_public_id();
                $tries = 0;
                while ($tries < 5) {
                    $exists = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . self::table() . ' WHERE public_id = %s LIMIT 1', $public_id));
                    if (empty($exists)) {
                        break;
                    }
                    $public_id = self::generate_public_id();
                    $tries++;
                }
            }
            $insert['public_id'] = $public_id;
        }

        if (self::has_column('created_at')) {
            $insert['created_at'] = $now;
        }
        if (self::has_column('updated_at')) {
            $insert['updated_at'] = $now;
        }

        $wpdb->insert(self::table(), $insert);
        return (int) $wpdb->insert_id;
    }

    public static function create_manual($data, $force = false)
    {
        $professional_id = (int) ($data['professional_id'] ?? 0);
        $service_id      = (int) ($data['service_id'] ?? 0);
        $date            = sanitize_text_field($data['date'] ?? '');
        $start_time      = self::normalize_time($data['start_time'] ?? '');

        if ($professional_id <= 0 || $service_id <= 0 || empty($date) || empty($start_time)) {
            return array('success' => false, 'appointment_id' => 0, 'message' => __('Preencha os campos obrigatórios.', 'wp-appointments-scheduler'));
        }

        $service = WPAS_Model_Service::get($service_id);
        $duration = $service ? (int) $service->duration : 0;
        if ($duration <= 0) {
            $duration = 30;
        }

        try {
            $dt_start = new DateTime($date . ' ' . $start_time);
            $dt_end   = clone $dt_start;
            $dt_end->modify('+' . $duration . ' minutes');
        } catch (Exception $e) {
            return array('success' => false, 'appointment_id' => 0, 'message' => __('Data/horário inválidos.', 'wp-appointments-scheduler'));
        }

        $end_time = $dt_end->format('H:i:s');

        $slot_id = WPAS_Model_Agenda::get_slot_id_by_start_time($professional_id, $date, $start_time);

        $reserved = false;
        if ($slot_id > 0) {
            $reserved = WPAS_Model_Agenda::reserve_slots_for_duration($slot_id, $professional_id, $date, $duration);
        }

        if (! $reserved && $force) {
            WPAS_Model_Agenda::force_book_range($professional_id, $date, $start_time, $end_time, 15);
            $slot_id = WPAS_Model_Agenda::get_slot_id_by_start_time($professional_id, $date, $start_time);
            $reserved = array(
                'start_time' => $start_time,
                'end_time'   => $end_time,
            );
        }

        if (! $reserved) {
            $msg = __('Não foi possível reservar o horário (slots indisponíveis).', 'wp-appointments-scheduler');
            $db_error = WPAS_Model_Agenda::get_last_db_error();
            if (!empty($db_error)) {
                $msg .= ' ' . $db_error;
            }
            return array('success' => false, 'appointment_id' => 0, 'message' => $msg);
        }

        $status = sanitize_text_field($data['status'] ?? 'pending');
        $allowed = array_keys(self::get_statuses());
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $appointment_id = self::create(array(
            'customer_name'   => $data['customer_name'] ?? '',
            'customer_email'  => $data['customer_email'] ?? '',
            'customer_phone'  => $data['customer_phone'] ?? '',
            'professional_id' => $professional_id,
            'service_id'      => $service_id,
            'slot_id'         => $slot_id > 0 ? $slot_id : null,
            'date'            => $date,
            'start_time'      => $reserved['start_time'] ?? $start_time,
            'end_time'        => $reserved['end_time'] ?? $end_time,
            'status'          => $status,
            'notes'           => $data['notes'] ?? '',
        ));

        if ($appointment_id <= 0) {
            if ($slot_id > 0 && self::status_releases_slot('cancelled')) {
            }
            return array('success' => false, 'appointment_id' => 0, 'message' => __('Erro ao criar o agendamento.', 'wp-appointments-scheduler'));
        }

        if (!self::status_occupies_slot($status) && !empty($slot_id)) {
            WPAS_Model_Agenda::release_range($professional_id, $date, $start_time, $end_time);
        }

        return array('success' => true, 'appointment_id' => (int) $appointment_id, 'message' => __('Agendamento criado com sucesso.', 'wp-appointments-scheduler'));
    }

    public static function update_manual($id, $data, $force = false)
    {
        global $wpdb;

        $id = (int) $id;
        $old = self::get($id);
        if (!$old) {
            return array('success' => false, 'message' => __('Agendamento não encontrado.', 'wp-appointments-scheduler'));
        }

        $professional_id = isset($data['professional_id']) ? (int) $data['professional_id'] : (int) $old->professional_id;
        $service_id      = isset($data['service_id']) ? (int) $data['service_id'] : (int) $old->service_id;
        $date            = isset($data['date']) ? sanitize_text_field($data['date']) : $old->date;
        $start_time      = isset($data['start_time']) ? self::normalize_time($data['start_time']) : $old->start_time;

        $service = WPAS_Model_Service::get($service_id);
        $duration = $service ? (int) $service->duration : 0;
        if ($duration <= 0) {
            $duration = 30;
        }

        try {
            $dt_start = new DateTime($date . ' ' . $start_time);
            $dt_end   = clone $dt_start;
            $dt_end->modify('+' . $duration . ' minutes');
        } catch (Exception $e) {
            return array('success' => false, 'message' => __('Data/horário inválidos.', 'wp-appointments-scheduler'));
        }
        $end_time = $dt_end->format('H:i:s');

        $new_status = sanitize_text_field($data['status'] ?? $old->status);
        $allowed = array_keys(self::get_statuses());
        if (!in_array($new_status, $allowed, true)) {
            $new_status = $old->status;
        }

        $schedule_changed = (
            (int) $professional_id !== (int) $old->professional_id ||
            (int) $service_id !== (int) $old->service_id ||
            $date !== $old->date ||
            $start_time !== $old->start_time
        );

        $only_status_change = (!$schedule_changed && $new_status !== $old->status);

        if ($schedule_changed && self::status_occupies_slot($old->status)) {
            WPAS_Model_Agenda::release_range((int) $old->professional_id, $old->date, $old->start_time, $old->end_time);
        }

        $slot_id = !empty($old->slot_id) ? (int) $old->slot_id : 0;
        $reserved_times = array('start_time' => $start_time, 'end_time' => $end_time);

        if ($schedule_changed && self::status_occupies_slot($new_status)) {
            $slot_id = WPAS_Model_Agenda::get_slot_id_by_start_time($professional_id, $date, $start_time);
            $reserved = false;
            if ($slot_id > 0) {
                $reserved = WPAS_Model_Agenda::reserve_slots_for_duration($slot_id, $professional_id, $date, $duration);
            }

            if (!$reserved && $force) {
                WPAS_Model_Agenda::force_book_range($professional_id, $date, $start_time, $end_time, 15);
                $slot_id = WPAS_Model_Agenda::get_slot_id_by_start_time($professional_id, $date, $start_time);
                $reserved = array('start_time' => $start_time, 'end_time' => $end_time);
            }

            if (!$reserved) {
                if (self::status_occupies_slot($old->status) && !empty($old->slot_id)) {
                    $old_service = WPAS_Model_Service::get((int) $old->service_id);
                    $old_dur = $old_service ? (int) $old_service->duration : 0;
                    WPAS_Model_Agenda::reserve_range_for_existing_appointment((int) $old->slot_id, (int) $old->professional_id, $old->date, $old_dur);
                }
                return array('success' => false, 'message' => __('Não foi possível reagendar: horário indisponível.', 'wp-appointments-scheduler'));
            }

            $reserved_times = array(
                'start_time' => $reserved['start_time'] ?? $start_time,
                'end_time'   => $reserved['end_time'] ?? $end_time,
            );
        }

        $update = array(
            'customer_name'   => WPAS_Helpers::sanitize_text($data['customer_name'] ?? $old->customer_name),
            'customer_email'  => WPAS_Helpers::sanitize_email($data['customer_email'] ?? $old->customer_email),
            'customer_phone'  => WPAS_Helpers::sanitize_phone($data['customer_phone'] ?? $old->customer_phone),
            'professional_id' => (int) $professional_id,
            'service_id'      => (int) $service_id,
            'date'            => $date,
            'start_time'      => $reserved_times['start_time'],
            'end_time'        => $reserved_times['end_time'],
            'status'          => $only_status_change ? $old->status : $new_status,
            'notes'           => isset($data['notes']) ? wp_kses_post($data['notes']) : $old->notes,
        );
        if ($slot_id > 0) {
            $update['slot_id'] = $slot_id;
        }
        if (self::has_column('updated_at')) {
            $update['updated_at'] = current_time('mysql');
        }

        $updated = $wpdb->update(self::table(), $update, array('id' => $id));
        if ($updated === false) {
            return array('success' => false, 'message' => __('Erro ao salvar o agendamento.', 'wp-appointments-scheduler'));
        }

        if (!$only_status_change && self::status_releases_slot($new_status)) {
            WPAS_Model_Agenda::release_range($professional_id, $date, $update['start_time'], $update['end_time']);
        }

        if ($only_status_change) {
            $ok = self::update_status($id, $new_status);
            if (!$ok) {
                return array('success' => false, 'message' => __('Não foi possível atualizar o status.', 'wp-appointments-scheduler'));
            }
        }

        return array('success' => true, 'message' => __('Agendamento atualizado.', 'wp-appointments-scheduler'));
    }

    public static function get($id)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            'SELECT * FROM ' . self::table() . ' WHERE id = %d',
            (int) $id
        );

        return $wpdb->get_row($sql);
    }

    public static function get_all($args = array())
    {
        global $wpdb;

        $defaults = array(
            'professional_id' => null,
            'status'          => null,
            'date_from'       => null,
            'date_to'         => null,
            'order'           => 'date ASC, start_time ASC',
        );
        $args = wp_parse_args($args, $defaults);

        $where = ' WHERE 1=1 ';

        if (! is_null($args['professional_id']) && (int) $args['professional_id'] > 0) {
            $where .= $wpdb->prepare(' AND professional_id = %d ', (int) $args['professional_id']);
        }

        if (! is_null($args['status']) && $args['status'] !== '') {
            $where .= $wpdb->prepare(' AND status = %s ', $args['status']);
        }

        if (! empty($args['date_from'])) {
            $where .= $wpdb->prepare(' AND date >= %s ', $args['date_from']);
        }

        if (! empty($args['date_to'])) {
            $where .= $wpdb->prepare(' AND date <= %s ', $args['date_to']);
        }

        $order = ' ORDER BY ' . esc_sql($args['order']);

        $sql = 'SELECT * FROM ' . self::table() . $where . $order;

        return $wpdb->get_results($sql);
    }

    public static function update_status($id, $status)
    {
        global $wpdb;

        $allowed = array_keys(self::get_statuses());
        if (! in_array($status, $allowed, true)) {
            return false;
        }

        $appointment = self::get((int) $id);
        if (! $appointment) {
            return false;
        }

        $old_status = $appointment->status;

        $now = current_time('mysql');

        if (! empty($appointment->slot_id)) {
            $slot_id = (int) $appointment->slot_id;

            if (self::status_releases_slot($status)) {
                WPAS_Model_Agenda::release_range(
                    (int) $appointment->professional_id,
                    $appointment->date,
                    $appointment->start_time,
                    $appointment->end_time
                );
            }

            if (self::status_releases_slot($old_status) && self::status_occupies_slot($status)) {
                $service  = WPAS_Model_Service::get((int) $appointment->service_id);
                $duration = $service ? (int) $service->duration : 0;

                $reserved = WPAS_Model_Agenda::reserve_range_for_existing_appointment(
                    $slot_id,
                    (int) $appointment->professional_id,
                    $appointment->date,
                    $duration
                );

                if (! $reserved) {
                    return false;
                }

                $update_times = array(
                    'start_time' => $reserved['start_time'],
                    'end_time'   => $reserved['end_time'],
                );
                $format_times = array('%s', '%s');
                if (self::has_column('updated_at')) {
                    $update_times['updated_at'] = $now;
                    $format_times[] = '%s';
                }

                $wpdb->update(
                    self::table(),
                    $update_times,
                    array('id' => (int) $id),
                    $format_times,
                    array('%d')
                );
            }
        }

        $update = array('status' => $status);
        $format = array('%s');
        if (self::has_column('updated_at')) {
            $update['updated_at'] = $now;
            $format[] = '%s';
        }

        $updated = $wpdb->update(
            self::table(),
            $update,
            array('id' => (int) $id),
            $format,
            array('%d')
        );

        return ($updated !== false);
    }

    public static function update($id, $data)
    {
        global $wpdb;

        $update = array();
        $format = array();

        if (isset($data['customer_name'])) {
            $update['customer_name'] = WPAS_Helpers::sanitize_text($data['customer_name']);
            $format[] = '%s';
        }

        if (isset($data['customer_email'])) {
            $update['customer_email'] = WPAS_Helpers::sanitize_email($data['customer_email']);
            $format[] = '%s';
        }

        if (isset($data['customer_phone'])) {
            $update['customer_phone'] = WPAS_Helpers::sanitize_phone($data['customer_phone']);
            $format[] = '%s';
        }

        if (isset($data['status'])) {
            $update['status'] = $data['status'];
            $format[] = '%s';
        }

        if (isset($data['notes'])) {
            $update['notes'] = wp_kses_post($data['notes']);
            $format[] = '%s';
        }

        if (empty($update)) {
            return false;
        }

        $update['updated_at'] = current_time('mysql');
        $format[] = '%s';

        return $wpdb->update(
            self::table(),
            $update,
            array('id' => (int) $id),
            $format,
            array('%d')
        );
    }

    public static function delete($id)
    {
        global $wpdb;

        $appointment = self::get((int) $id);
        if ($appointment) {
            WPAS_Model_Agenda::release_range(
                (int)$appointment->professional_id,
                $appointment->date,
                $appointment->start_time,
                $appointment->end_time
            );
        }

        return $wpdb->delete(
            self::table(),
            array('id' => (int) $id),
            array('%d')
        );
    }
}
