<?php

function wcb_delete_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'whatsapp_numbers'; // Ganti dengan nama tabel yang sesuai

    // Hapus tabel jika sudah ada
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    }
}
