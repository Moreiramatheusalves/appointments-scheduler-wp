<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Model_Category
{

    protected static function table()
    {
        return WPAS_DB::table('categories');
    }

    public static function get_all()
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . self::table() . ' ORDER BY name ASC';
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
            'name'        => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'created_at'  => current_time('mysql'),
        );

        $wpdb->insert(self::table(), $insert);
        return $wpdb->insert_id;
    }

    public static function update($id, $data)
    {
        global $wpdb;

        $update = array(
            'name'        => WPAS_Helpers::sanitize_text($data['name'] ?? ''),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
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
