<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Validator
{
    public static function required($value)
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return ! empty($value);
        }

        return ! empty($value);
    }

    public static function email($value)
    {
        return empty($value) || is_email($value);
    }

    public static function min_length($value, $min)
    {
        $value = (string) $value;
        return mb_strlen(trim($value)) >= (int) $min;
    }

    public static function max_length($value, $max)
    {
        $value = (string) $value;
        return mb_strlen(trim($value)) <= (int) $max;
    }

    public static function length_between($value, $min, $max)
    {
        $len = mb_strlen(trim((string) $value));
        return ($len >= (int) $min && $len <= (int) $max);
    }

    public static function numeric($value)
    {
        return is_numeric($value);
    }

    public static function integer($value)
    {
        if (is_int($value)) {
            return true;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
            return true;
        }

        return false;
    }

    public static function positive_integer($value)
    {
        return self::integer($value) && (int) $value > 0;
    }

    public static function in_list($value, $list)
    {
        return in_array($value, (array) $list, true);
    }

    public static function date($value)
    {
        if (empty($value)) {
            return true;
        }

        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    public static function time($value)
    {
        if (empty($value)) {
            return true;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        $t = DateTime::createFromFormat('H:i:s', $value);
        return $t && $t->format('H:i:s') === $value;
    }

    public static function phone($value)
    {
        if (empty($value)) {
            return true;
        }

        $digits = preg_replace('/\D+/', '', $value);
        return strlen($digits) >= 8;
    }

    public static function validate($data, $rules, $messages = array())
    {
        $errors = array();

        foreach ($rules as $field => $field_rules) {
            $value = isset($data[$field]) ? $data[$field] : null;

            foreach ((array) $field_rules as $rule) {
                $rule_name = $rule;
                $params    = array();

                if (strpos($rule, ':') !== false) {
                    list($rule_name, $param_str) = explode(':', $rule, 2);
                    $params = explode(',', $param_str);
                }

                $method = null;

                switch ($rule_name) {
                    case 'required':
                        if (! self::required($value)) {
                            $errors[$field][] = self::message($field, 'required', $messages, __('Campo obrigatório.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'email':
                        if (! self::email($value)) {
                            $errors[$field][] = self::message($field, 'email', $messages, __('E-mail inválido.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'min':
                        $min = isset($params[0]) ? (int) $params[0] : 0;
                        if (! self::min_length($value, $min)) {
                            $errors[$field][] = self::message($field, 'min', $messages, sprintf(__('Mínimo de %d caracteres.', 'wp-appointments-scheduler'), $min));
                        }
                        break;

                    case 'max':
                        $max = isset($params[0]) ? (int) $params[0] : 0;
                        if (! self::max_length($value, $max)) {
                            $errors[$field][] = self::message($field, 'max', $messages, sprintf(__('Máximo de %d caracteres.', 'wp-appointments-scheduler'), $max));
                        }
                        break;

                    case 'numeric':
                        if (! self::numeric($value)) {
                            $errors[$field][] = self::message($field, 'numeric', $messages, __('Valor deve ser numérico.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'integer':
                        if (! self::integer($value)) {
                            $errors[$field][] = self::message($field, 'integer', $messages, __('Valor deve ser inteiro.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'positive':
                        if (! self::positive_integer($value)) {
                            $errors[$field][] = self::message($field, 'positive', $messages, __('Valor deve ser inteiro positivo.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'date':
                        if (! self::date($value)) {
                            $errors[$field][] = self::message($field, 'date', $messages, __('Data inválida.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'time':
                        if (! self::time($value)) {
                            $errors[$field][] = self::message($field, 'time', $messages, __('Horário inválido.', 'wp-appointments-scheduler'));
                        }
                        break;

                    case 'phone':
                        if (! self::phone($value)) {
                            $errors[$field][] = self::message($field, 'phone', $messages, __('Telefone inválido.', 'wp-appointments-scheduler'));
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return $errors;
    }

    protected static function message($field, $rule, $messages, $default)
    {
        $key_specific = $field . '.' . $rule;
        if (isset($messages[$key_specific])) {
            return $messages[$key_specific];
        }

        if (isset($messages[$field]) && is_string($messages[$field])) {
            return $messages[$field];
        }

        return $default;
    }
}
