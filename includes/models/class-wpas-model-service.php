<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Model_Service
{

    protected static function table()
    {
        return WPAS_DB::table('services');
    }

    public static function get_all($args = array())
    {
        global $wpdb;

        $defaults = array(
            'active'      => null,
            'category_id' => null,
            'order'       => 'name ASC',
        );
        $args = wp_parse_args($args, $defaults);

        $where = ' WHERE 1=1 ';

        if (! is_null($args['active'])) {
            $where .= $wpdb->prepare(' AND active = %d ', (int) $args['active']);
        }

        if (! is_null($args['category_id']) && (int) $args['category_id'] > 0) {
            $where .= $wpdb->prepare(' AND category_id = %d ', (int) $args['category_id']);
        }

        $order = ' ORDER BY ' . esc_sql($args['order']);

        $sql = 'SELECT * FROM ' . self::table() . $where . $order;

        return $wpdb->get_results($sql);
    }

    public static function get($id)
    {
        global $wpdb;
        $sql = $wpdb->prepare('SELECT * FROM ' . self::table() . ' WHERE id = %d', $id);
        return $wpdb->get_row($sql);
    }

    public static function create($data)
    {
        global $wpdb;

        $insert = array(
            'category_id' => ! empty($data['category_id']) ? (int) $data['category_id'] : null,
            'name'        => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'price'       => isset($data['price']) ? (float) str_replace(',', '.', $data['price']) : 0,
            'duration'    => isset($data['duration']) ? (int) $data['duration'] : 30,
            'active'      => isset($data['active']) ? (int) $data['active'] : 1,
            'created_at'  => current_time('mysql'),
        );

        $wpdb->insert(self::table(), $insert);
        return $wpdb->insert_id;
    }

    public static function update($id, $data)
    {
        global $wpdb;

        $update = array(
            'category_id' => ! empty($data['category_id']) ? (int) $data['category_id'] : null,
            'name'        => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'price'       => isset($data['price']) ? (float) str_replace(',', '.', $data['price']) : 0,
            'duration'    => isset($data['duration']) ? (int) $data['duration'] : 30,
            'active'      => isset($data['active']) ? (int) $data['active'] : 1,
            'updated_at'  => current_time('mysql'),
        );

        return $wpdb->update(self::table(), $update, array('id' => (int) $id));
    }

    public static function delete($id)
    {
        global $wpdb;
        return $wpdb->delete(self::table(), array('id' => (int) $id));
    }
}
