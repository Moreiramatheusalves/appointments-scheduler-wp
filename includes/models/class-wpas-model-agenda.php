<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Model_Agenda
{
    protected static function table()
    {
        return WPAS_DB::table('agenda_slots');
    }



    /**
     * @var array|null
     */
    protected static $table_columns = null;

    /**
     * @var string
     */
    protected static $last_db_error = '';

    /**
     * @return string
     */
    public static function get_last_db_error()
    {
        return (string) self::$last_db_error;
    }

    protected static function clear_last_db_error()
    {
        self::$last_db_error = '';
    }

    protected static function capture_last_db_error()
    {
        global $wpdb;
        if (!empty($wpdb->last_error)) {
            self::$last_db_error = (string) $wpdb->last_error;
        }
    }

    /**
     * @return array
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

    public static function get_slots($professional_id, $date)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            'SELECT * FROM ' . self::table() . ' 
             WHERE professional_id = %d 
               AND date = %s 
               AND status = %s 
             ORDER BY start_time ASC',
            (int) $professional_id,
            $date,
            'available'
        );

        return $wpdb->get_results($sql);
    }

    public static function get_all_slots($professional_id, $date)
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            'SELECT * FROM ' . self::table() . ' 
             WHERE professional_id = %d 
               AND date = %s 
             ORDER BY start_time ASC',
            (int) $professional_id,
            $date
        );

        return $wpdb->get_results($sql);
    }

    public static function get_slot($slot_id)
    {
        global $wpdb;

        $slot_id = (int)$slot_id;
        if ($slot_id <= 0) return null;

        $sql = $wpdb->prepare('SELECT * FROM ' . self::table() . ' WHERE id = %d', $slot_id);
        return $wpdb->get_row($sql);
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

    /**
     * @param int    $professional_id
     * @param string $date
     * @return int|false
     */
    public static function delete_slots_by_date($professional_id, $date)
    {
        global $wpdb;

        $professional_id = (int) $professional_id;
        $date = sanitize_text_field($date);

        if ($professional_id <= 0 || empty($date)) {
            return false;
        }

        return $wpdb->delete(
            self::table(),
            array(
                'professional_id' => $professional_id,
                'date'            => $date,
            ),
            array('%d', '%s')
        );
    }

    public static function get_month_overview($professional_id, $year, $month)
    {
        global $wpdb;

        $professional_id = (int) $professional_id;
        $year  = (int) $year;
        $month = (int) $month;

        if ($professional_id <= 0 || $year <= 0 || $month < 1 || $month > 12) {
            return array();
        }

        $first = sprintf('%04d-%02d-01', $year, $month);
        $dt = DateTime::createFromFormat('Y-m-d', $first);
        if (!$dt) {
            return array();
        }
        $last = $dt->format('Y-m-t');

        $sql = $wpdb->prepare(
            "SELECT date,
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available
               FROM " . self::table() . "
              WHERE professional_id = %d
                AND date BETWEEN %s AND %s
              GROUP BY date",
            $professional_id,
            $first,
            $last
        );

        $rows = $wpdb->get_results($sql);
        if (empty($rows)) {
            return array();
        }

        $out = array();
        foreach ($rows as $r) {
            $total = (int) $r->total;
            $available = (int) $r->available;
            $out[$r->date] = array(
                'total'     => $total,
                'available' => $available,
                'blocked'   => max(0, $total - $available),
            );
        }

        return $out;
    }

    /**
     * @param int    $professional_id
     * @param string $date_from
     * @param string $date_to
     * @param string $start_time
     * @param string $end_time
     * @param int    $duration
     * @param array  $weekdays
     * @param string $status
     *
     * @return int
     */
    public static function generate_slots_range($professional_id, $date_from, $date_to, $start_time, $end_time, $duration = 30, $weekdays = array(), $status = 'available')
    {
        global $wpdb;

        self::clear_last_db_error();

        $professional_id = (int) $professional_id;
        $date_from = sanitize_text_field($date_from);
        $date_to   = sanitize_text_field($date_to);

        $start_time = self::normalize_time($start_time);
        $end_time   = self::normalize_time($end_time);

        $duration = (int) $duration;
        if ($duration <= 0) {
            $duration = 30;
        }

        $allowed_status = array('available', 'blocked', 'break');
        $status = sanitize_text_field($status);
        if (!in_array($status, $allowed_status, true)) {
            $status = 'available';
        }

        $weekdays = array_map('intval', (array) $weekdays);
        $weekdays = array_values(array_filter($weekdays, function ($v) {
            return $v >= 1 and $v <= 7;
        }));

        if ($professional_id <= 0 || empty($date_from) || empty($date_to) || empty($start_time) || empty($end_time)) {
            return 0;
        }

        $from = DateTime::createFromFormat('Y-m-d', $date_from);
        $to   = DateTime::createFromFormat('Y-m-d', $date_to);
        if (!$from || !$to) {
            return 0;
        }

        if ($from > $to) {
            return 0;
        }

        try {
            $test_date = $from->format('Y-m-d');
            $t_start = new DateTime($test_date . ' ' . $start_time);
            $t_end   = new DateTime($test_date . ' ' . $end_time);
        } catch (Exception $e) {
            return 0;
        }

        if ($t_start >= $t_end) {
            return 0;
        }

        $inserted = 0;
        $now = current_time('mysql');

        $cur_day = clone $from;
        while ($cur_day <= $to) {
            $date_str = $cur_day->format('Y-m-d');

            if (!empty($weekdays)) {
                $dow = (int) $cur_day->format('N');
                if (!in_array($dow, $weekdays, true)) {
                    $cur_day->modify('+1 day');
                    continue;
                }
            }

            try {
                $slot_start = new DateTime($date_str . ' ' . $start_time);
                $day_end    = new DateTime($date_str . ' ' . $end_time);
            } catch (Exception $e) {
                $cur_day->modify('+1 day');
                continue;
            }

            while ($slot_start < $day_end) {
                $slot_end = clone $slot_start;
                $slot_end->modify('+' . $duration . ' minutes');

                if ($slot_end > $day_end) {
                    break;
                }

                $st = $slot_start->format('H:i:s');
                $et = $slot_end->format('H:i:s');

                $existing_id = $wpdb->get_var(
                    $wpdb->prepare(
                        'SELECT id FROM ' . self::table() . ' WHERE professional_id = %d AND date = %s AND start_time = %s AND end_time = %s LIMIT 1',
                        $professional_id,
                        $date_str,
                        $st,
                        $et
                    )
                );

                if (!empty($existing_id)) {
                    $data = array('status' => $status);
                    $format = array('%s');

                    if (self::has_column('updated_at')) {
                        $data['updated_at'] = $now;
                        $format[] = '%s';
                    }

                    $updated = $wpdb->update(
                        self::table(),
                        $data,
                        array('id' => (int) $existing_id),
                        $format,
                        array('%d')
                    );

                    if ($updated === false) {
                        self::capture_last_db_error();
                    }
                } else {
                    $data = array(
                        'professional_id' => $professional_id,
                        'date'            => $date_str,
                        'start_time'      => $st,
                        'end_time'        => $et,
                        'status'          => $status,
                    );

                    $format = array('%d', '%s', '%s', '%s', '%s');

                    if (self::has_column('created_at')) {
                        $data['created_at'] = $now;
                        $format[] = '%s';
                    }
                    if (self::has_column('updated_at')) {
                        $data['updated_at'] = $now;
                        $format[] = '%s';
                    }

                    $ok = $wpdb->insert(self::table(), $data, $format);
                    if ($ok === false) {
                        self::capture_last_db_error();
                    } else {
                        $inserted++;
                    }
                }

                $slot_start = $slot_end;
            }

            $cur_day->modify('+1 day');
        }

        return (int) $inserted;
    }

    /**
     * @param int $professional_id
     * @param string $date
     * @param string $start_time
     * @return int
     */
    public static function get_slot_id_by_start_time($professional_id, $date, $start_time)
    {
        global $wpdb;

        $professional_id = (int) $professional_id;
        $date = sanitize_text_field($date);
        $start_time = self::normalize_time($start_time);

        if ($professional_id <= 0 || empty($date) || empty($start_time)) {
            return 0;
        }

        $id = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . self::table() . ' WHERE professional_id = %d AND date = %s AND start_time = %s ORDER BY id ASC LIMIT 1',
                $professional_id,
                $date,
                $start_time
            )
        );

        return (int) $id;
    }

    /**
     * @return int
     */
    public static function force_book_range($professional_id, $date, $start_time, $end_time, $fallback_minutes = 15)
    {
        global $wpdb;

        self::clear_last_db_error();

        $professional_id = (int) $professional_id;
        $date = sanitize_text_field($date);
        $start_time = self::normalize_time($start_time);
        $end_time   = self::normalize_time($end_time);

        $fallback_minutes = (int) $fallback_minutes;
        if ($fallback_minutes <= 0) {
            $fallback_minutes = 15;
        }

        if ($professional_id <= 0 || empty($date) || empty($start_time) || empty($end_time)) {
            return 0;
        }

        try {
            $dt_start = new DateTime($date . ' ' . $start_time);
            $dt_end   = new DateTime($date . ' ' . $end_time);
        } catch (Exception $e) {
            return 0;
        }

        if ($dt_start >= $dt_end) {
            return 0;
        }

        $slot_len = 0;
        $existing = self::get_all_slots($professional_id, $date);
        if (!empty($existing)) {
            $slot_len = self::slot_length_minutes($existing[0]);
        }
        if ($slot_len <= 0) {
            $slot_len = $fallback_minutes;
        }

        $now = current_time('mysql');
        $ops = 0;

        $cursor = clone $dt_start;
        while ($cursor < $dt_end) {
            $next = clone $cursor;
            $next->modify('+' . $slot_len . ' minutes');

            if ($next > $dt_end) {
                break;
            }

            $st = $cursor->format('H:i:s');
            $et = $next->format('H:i:s');

            $existing_id = $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT id FROM ' . self::table() . ' WHERE professional_id = %d AND date = %s AND start_time = %s AND end_time = %s LIMIT 1',
                    $professional_id,
                    $date,
                    $st,
                    $et
                )
            );

            if (!empty($existing_id)) {
                $data = array('status' => 'booked');
                $format = array('%s');
                if (self::has_column('updated_at')) {
                    $data['updated_at'] = $now;
                    $format[] = '%s';
                }

                $updated = $wpdb->update(
                    self::table(),
                    $data,
                    array('id' => (int) $existing_id),
                    $format,
                    array('%d')
                );

                if ($updated === false) {
                    self::capture_last_db_error();
                } else {
                    $ops++;
                }
            } else {
                $data = array(
                    'professional_id' => $professional_id,
                    'date'            => $date,
                    'start_time'      => $st,
                    'end_time'        => $et,
                    'status'          => 'booked',
                );
                $format = array('%d', '%s', '%s', '%s', '%s');

                if (self::has_column('created_at')) {
                    $data['created_at'] = $now;
                    $format[] = '%s';
                }
                if (self::has_column('updated_at')) {
                    $data['updated_at'] = $now;
                    $format[] = '%s';
                }

                $ok = $wpdb->insert(self::table(), $data, $format);
                if ($ok === false) {
                    self::capture_last_db_error();
                } else {
                    $ops++;
                }
            }

            $cursor = $next;
        }

        return (int) $ops;
    }

    protected static function slot_length_minutes($slot)
    {
        try {
            $s = new DateTime($slot->date . ' ' . $slot->start_time);
            $e = new DateTime($slot->date . ' ' . $slot->end_time);
        } catch (Exception $ex) {
            return 0;
        }

        $diff = $e->getTimestamp() - $s->getTimestamp();
        return (int) round($diff / 60);
    }

    protected static function take_consecutive_available_slots($all_slots, $start_index, $needed)
    {
        $picked = array();

        for ($i = 0; $i < $needed; $i++) {
            $idx = $start_index + $i;
            if (!isset($all_slots[$idx])) {
                return array();
            }

            $cur = $all_slots[$idx];

            if ($cur->status !== 'available') {
                return array();
            }

            if ($i > 0) {
                $prev = $picked[$i - 1];
                if ($cur->start_time !== $prev->end_time) {
                    return array();
                }
            }

            $picked[] = $cur;
        }

        return $picked;
    }

    public static function get_start_slots_for_duration($professional_id, $date, $service_minutes)
    {
        $professional_id = (int)$professional_id;
        $service_minutes = (int)$service_minutes;

        if ($professional_id <= 0 || empty($date)) {
            return array();
        }

        if ($service_minutes <= 0) {
            $service_minutes = 1;
        }

        $all = self::get_all_slots($professional_id, $date);
        if (empty($all)) {
            return array();
        }

        $slot_len = self::slot_length_minutes($all[0]);
        if ($slot_len <= 0) {
            return array();
        }

        $needed = (int) ceil($service_minutes / $slot_len);
        if ($needed <= 0) $needed = 1;

        $starts = array();

        for ($i = 0; $i < count($all); $i++) {
            $candidate = $all[$i];

            if ($candidate->status !== 'available') {
                continue;
            }

            $block = self::take_consecutive_available_slots($all, $i, $needed);
            if (empty($block)) {
                continue;
            }

            $first = $block[0];
            $last  = $block[count($block) - 1];

            $starts[] = (object) array(
                'id'         => (int)$first->id,
                'start_time' => $first->start_time,
                'end_time'   => $last->end_time,
            );
        }

        return $starts;
    }

    public static function reserve_slots_for_duration($start_slot_id, $professional_id, $date, $service_minutes)
    {
        global $wpdb;

        $start_slot_id   = (int)$start_slot_id;
        $professional_id = (int)$professional_id;
        $service_minutes = (int)$service_minutes;

        if ($start_slot_id <= 0 || $professional_id <= 0 || empty($date)) {
            return false;
        }

        if ($service_minutes <= 0) {
            $service_minutes = 1;
        }

        $all = self::get_all_slots($professional_id, $date);
        if (empty($all)) return false;

        $start_index = null;
        foreach ($all as $idx => $slot) {
            if ((int)$slot->id === $start_slot_id) {
                $start_index = $idx;
                break;
            }
        }
        if ($start_index === null) return false;

        $slot_len = self::slot_length_minutes($all[$start_index]);
        if ($slot_len <= 0) return false;

        $needed = (int) ceil($service_minutes / $slot_len);
        if ($needed <= 0) $needed = 1;

        $block = self::take_consecutive_available_slots($all, $start_index, $needed);
        if (empty($block)) {
            return false;
        }

        $booked_ids = array();
        $now = current_time('mysql');

        foreach ($block as $slot) {
            $data = array('status' => 'booked');
            $format = array('%s');

            if (self::has_column('updated_at')) {
                $data['updated_at'] = $now;
                $format[] = '%s';
            }

            $updated = $wpdb->update(
                self::table(),
                $data,
                array(
                    'id'              => (int) $slot->id,
                    'professional_id' => $professional_id,
                    'date'            => $date,
                    'status'          => 'available',
                ),
                $format,
                array('%d', '%d', '%s', '%s')
            );

            if ($updated === false || $updated < 1) {
                foreach ($booked_ids as $bid) {
                    self::release_slot((int)$bid);
                }
                return false;
            }

            $booked_ids[] = (int)$slot->id;
        }

        $first = $block[0];
        $last  = $block[count($block) - 1];

        return array(
            'slot_ids'   => $booked_ids,
            'start_time' => $first->start_time,
            'end_time'   => $last->end_time,
        );
    }

    public static function release_range($professional_id, $date, $start_time, $end_time)
    {



        global $wpdb;

        $professional_id = (int) $professional_id;
        if ($professional_id <= 0 || empty($date) || empty($start_time) || empty($end_time)) {
            return false;
        }

        if (!self::has_column('updated_at')) {
            $sql = $wpdb->prepare(
                "UPDATE " . self::table() . "
                 SET status = %s
                 WHERE professional_id = %d
                   AND date = %s
                   AND status = %s
                   AND start_time >= %s
                   AND end_time <= %s",
                'available',
                $professional_id,
                $date,
                'booked',
                $start_time,
                $end_time
            );
            return $wpdb->query($sql);
        }

        $sql = $wpdb->prepare(
            "UPDATE " . self::table() . "
             SET status = %s, updated_at = %s
             WHERE professional_id = %d
               AND date = %s
               AND status = %s
               AND start_time >= %s
               AND end_time <= %s",
            'available',
            current_time('mysql'),
            $professional_id,
            $date,
            'booked',
            $start_time,
            $end_time
        );

        return $wpdb->query($sql);
    }

    public static function reserve_range_for_existing_appointment($slot_id, $professional_id, $date, $service_minutes)
    {
        return self::reserve_slots_for_duration($slot_id, $professional_id, $date, $service_minutes);
    }

    public static function reserve_slot($slot_id, $professional_id, $date = null, $start_time = null, $end_time = null)
    {

        global $wpdb;

        $slot_id         = (int) $slot_id;
        $professional_id = (int) $professional_id;

        if ($slot_id <= 0 || $professional_id <= 0) {
            return false;
        }

        $where = array(
            'id'              => $slot_id,
            'professional_id' => $professional_id,
            'status'          => 'available',
        );
        $where_format = array('%d', '%d', '%s');

        if (! empty($date)) {
            $where['date']  = $date;
            $where_format[] = '%s';
        }

        if (! empty($start_time)) {
            $where['start_time'] = $start_time;
            $where_format[]      = '%s';
        }

        if (! empty($end_time)) {
            $where['end_time'] = $end_time;
            $where_format[]    = '%s';
        }

        $data = array('status' => 'booked');
        $data_format = array('%s');

        if (self::has_column('updated_at')) {
            $data['updated_at'] = current_time('mysql');
            $data_format[] = '%s';
        }

        $updated = $wpdb->update(
            self::table(),
            $data,
            $where,
            $data_format,
            $where_format
        );

        return ($updated !== false && $updated > 0);
    }

    public static function release_slot($slot_id)
    {

        global $wpdb;

        $slot_id = (int) $slot_id;
        if ($slot_id <= 0) {
            return false;
        }

        $data = array('status' => 'available');
        $data_format = array('%s');

        if (self::has_column('updated_at')) {
            $data['updated_at'] = current_time('mysql');
            $data_format[] = '%s';
        }

        $updated = $wpdb->update(
            self::table(),
            $data,
            array('id' => $slot_id, 'status' => 'booked'),
            $data_format,
            array('%d', '%s')
        );

        return ($updated !== false && $updated > 0);
    }

    public static function reconcile_slots($date_from = null, $professional_id = null)
    {
        global $wpdb;

        if (empty($date_from)) {
            $date_from = current_time('Y-m-d');
        }

        $agenda_table = self::table();
        $app_table    = WPAS_DB::table('appointments');
        $now = current_time('mysql');

        $where_sql = "s.date >= %s AND s.status IN ('available','booked')";
        $params    = array($date_from);

        if (! empty($professional_id)) {
            $where_sql .= " AND s.professional_id = %d";
            $params[]   = (int) $professional_id;
        }

        $set_updated = self::has_column('updated_at');

        $sql = "
            UPDATE {$agenda_table} s
            LEFT JOIN {$app_table} a
              ON a.slot_id = s.id
             AND a.status IN (%s,%s)
            SET
              s.status = CASE
                WHEN a.id IS NULL THEN 'available'
                ELSE 'booked'
              END" . ($set_updated ? ",
              s.updated_at = %s" : "") . "
            WHERE {$where_sql}
        ";

        $prepare_params = array('pending', 'confirmed');
        if ($set_updated) {
            $prepare_params[] = $now;
        }
        $prepare_params = array_merge($prepare_params, $params);

        $prepared = $wpdb->prepare($sql, $prepare_params);
        if (empty($prepared)) {
            return false;
        }

        return $wpdb->query($prepared);
    }
}
