<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_DB
{

    public static function table($name)
    {
        global $wpdb;
        return $wpdb->prefix . 'wpas_' . $name;
    }

    public static function create_tables()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $table_prof = self::table('professionals');
        $sql_prof = "CREATE TABLE {$table_prof} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            email VARCHAR(191) NULL,
            phone VARCHAR(50) NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        $table_cat = self::table('categories');
        $sql_cat = "CREATE TABLE {$table_cat} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        $table_serv = self::table('services');
        $sql_serv = "CREATE TABLE {$table_serv} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id BIGINT(20) UNSIGNED NULL,
            name VARCHAR(191) NOT NULL,
            description TEXT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            duration INT(11) NOT NULL DEFAULT 30,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY category_id (category_id)
        ) {$charset_collate};";

        $table_prof_serv = self::table('professional_service');
        $sql_prof_serv = "CREATE TABLE {$table_prof_serv} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            professional_id BIGINT(20) UNSIGNED NOT NULL,
            service_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            KEY professional_id (professional_id),
            KEY service_id (service_id)
        ) {$charset_collate};";

        $table_agenda = self::table('agenda_slots');
        $sql_agenda = "CREATE TABLE {$table_agenda} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            professional_id BIGINT(20) UNSIGNED NOT NULL,
            service_id BIGINT(20) UNSIGNED NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'available',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY professional_id (professional_id),
            KEY date (date),
            KEY status (status),
            KEY professional_date_status (professional_id, date, status),
            UNIQUE KEY unique_slot (professional_id, date, start_time, end_time)
        ) {$charset_collate};";

        $table_app = self::table('appointments');
        $sql_app = "CREATE TABLE {$table_app} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            public_id VARCHAR(32) NULL,
            customer_name VARCHAR(191) NOT NULL,
            customer_email VARCHAR(191) NULL,
            customer_phone VARCHAR(50) NULL,
            professional_id BIGINT(20) UNSIGNED NOT NULL,
            service_id BIGINT(20) UNSIGNED NOT NULL,
            slot_id BIGINT(20) UNSIGNED NULL,
            date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY public_id (public_id),
            KEY professional_id (professional_id),
            KEY service_id (service_id),
            KEY date (date),
            KEY slot_id (slot_id),
            KEY status (status),
            KEY professional_date_status (professional_id, date, status)
        ) {$charset_collate};";

        dbDelta($sql_prof);
        dbDelta($sql_cat);
        dbDelta($sql_serv);
        dbDelta($sql_prof_serv);
        dbDelta($sql_agenda);
        dbDelta($sql_app);

        self::ensure_agenda_unique_slot_index();
        self::ensure_appointments_public_id_support();
    }

    public static function ensure_agenda_unique_slot_index()
    {
        global $wpdb;

        $table = self::table('agenda_slots');

        $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM {$table} WHERE Key_name = %s", 'unique_slot'));
        if (!empty($indexes)) {
            return;
        }

        $wpdb->query(
            "DELETE t1 FROM {$table} t1
             INNER JOIN {$table} t2
               ON t1.professional_id = t2.professional_id
              AND t1.date = t2.date
              AND t1.start_time = t2.start_time
              AND t1.end_time = t2.end_time
              AND t1.id > t2.id"
        );

        $wpdb->query("ALTER TABLE {$table} ADD UNIQUE KEY unique_slot (professional_id, date, start_time, end_time)");
    }

    public static function ensure_appointments_public_id_support()
    {
        global $wpdb;

        $table = self::table('appointments');

        $col = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
                $table,
                'public_id'
            )
        );

        if (empty($col)) {
            $wpdb->query("ALTER TABLE {$table} ADD COLUMN public_id VARCHAR(32) NULL AFTER id");
        }

        $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM {$table} WHERE Key_name = %s", 'public_id'));
        if (empty($indexes)) {
            $wpdb->query("ALTER TABLE {$table} ADD UNIQUE KEY public_id (public_id)");
        }

        $rows = $wpdb->get_col("SELECT id FROM {$table} WHERE public_id IS NULL OR public_id = '' LIMIT 5000");
        if (!empty($rows)) {
            foreach ($rows as $id) {
                $public_id = substr(wp_generate_password(16, false, false), 0, 16);

                $tries = 0;
                while ($tries < 5) {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE public_id = %s LIMIT 1", $public_id));
                    if (empty($exists)) {
                        break;
                    }
                    $public_id = substr(wp_generate_password(16, false, false), 0, 16);
                    $tries++;
                }

                $wpdb->update(
                    $table,
                    array('public_id' => $public_id),
                    array('id' => (int) $id),
                    array('%s'),
                    array('%d')
                );
            }
        }
    }
}
