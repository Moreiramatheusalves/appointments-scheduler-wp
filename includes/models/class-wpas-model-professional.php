<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Model_Professional
{

    protected static function table()
    {
        return WPAS_DB::table('professionals');
    }

    public static function get_all($args = array())
    {
        global $wpdb;

        $defaults = array(
            'active' => null,
            'order'  => 'name ASC',
        );
        $args = wp_parse_args($args, $defaults);

        $where = ' WHERE 1=1 ';
        if (! is_null($args['active'])) {
            $where .= $wpdb->prepare(' AND active = %d ', $args['active']);
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
            'name'       => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'email'      => WPAS_Helpers::sanitize_email($data['email'] ?? ''),
            'phone'      => WPAS_Helpers::sanitize_phone($data['phone'] ?? ''),
            'active'     => isset($data['active']) ? (int) $data['active'] : 1,
            'created_at' => current_time('mysql'),
        );

        $wpdb->insert(self::table(), $insert);

        return $wpdb->insert_id;
    }

    public static function update($id, $data)
    {
        global $wpdb;

        $update = array(
            'name'       => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'email'      => WPAS_Helpers::sanitize_email($data['email'] ?? ''),
            'phone'      => WPAS_Helpers::sanitize_phone($data['phone'] ?? ''),
            'active'     => isset($data['active']) ? (int) $data['active'] : 1,
            'updated_at' => current_time('mysql'),
        );

        return $wpdb->update(self::table(), $update, array('id' => (int) $id));
    }

    public static function delete($id)
    {
        global $wpdb;
        return $wpdb->delete(self::table(), array('id' => (int) $id));
    }
}
