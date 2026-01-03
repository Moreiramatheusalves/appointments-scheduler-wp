<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Helpers
{
    public static function sanitize_text($value)
    {
        return sanitize_text_field($value);
    }

    public static function sanitize_email($value)
    {
        return sanitize_email($value);
    }

    public static function sanitize_phone($value)
    {
        return preg_replace('/[^0-9\+\-\(\) ]/', '', $value);
    }

    public static function sanitize_deep($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::sanitize_deep($v);
            }
            return $value;
        }

        if (is_string($value)) {
            return self::sanitize_text($value);
        }

        return $value;
    }

    public static function format_price($price, $with_symbol = false)
    {
        $price = (float) $price;
        $formatted = number_format($price, 2, ',', '.');
        return $with_symbol ? 'R$ ' . $formatted : $formatted;
    }

    public static function parse_price($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        $value = trim((string) $value);

        $value = str_replace(array(' ', '.'), array('', ''), $value);
        $value = str_replace(',', '.', $value);

        if (! is_numeric($value)) {
            return 0.0;
        }

        return (float) $value;
    }

    public static function format_date_br($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }

        $timestamp = strtotime($date);
        if (! $timestamp) {
            return $date;
        }

        return date_i18n('d/m/Y', $timestamp);
    }

    public static function format_time($time)
    {
        if (empty($time) || $time === '00:00:00') {
            return '';
        }

        return substr($time, 0, 5);
    }

    public static function combine_date_time($date, $time)
    {
        if (empty($date) || empty($time)) {
            return '';
        }

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $date . ' ' . $time;
    }

    public static function get_request_var($key, $default = null, $method = 'REQUEST')
    {
        $source = $_REQUEST;

        if ('GET' === strtoupper($method)) {
            $source = $_GET;
        } elseif ('POST' === strtoupper($method)) {
            $source = $_POST;
        }

        if (isset($source[$key])) {
            return $source[$key];
        }

        return $default;
    }

    public static function safe_redirect($url, $status = 302)
    {
        wp_safe_redirect(esc_url_raw($url), $status);
        exit;
    }

    public static function current_admin_url()
    {
        $screen = get_current_screen();
        if (isset($screen->id)) {
            $base = remove_query_arg(array('_wpnonce', 'wp_http_referer', 'updated', 'deleted'));
            return $base;
        }

        return admin_url();
    }

    public static function result($success, $message = '', $extra = array())
    {
        return array_merge(
            array(
                'success' => (bool) $success,
                'message' => $message,
            ),
            $extra
        );
    }

    public static function json_success($data = array())
    {
        wp_send_json_success($data);
    }

    public static function json_error($message, $data = array())
    {
        $data['message'] = $message;
        wp_send_json_error($data);
    }
}
