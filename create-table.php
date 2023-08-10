<?php
// Create database table when plugin is activated
function wcb_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_name varchar(255) NOT NULL,
        whatsapp_number varchar(20) NOT NULL,
        online_time varchar(5) NOT NULL,
        offline_time varchar(5) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wcb_create_table');
