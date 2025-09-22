<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Uninstaller {
    public static function uninstall() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'gbe_enquiries';

        // delete tabel enquiry
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

        // delete setting email
        delete_option( 'gbe_email_settings' );

        // rewrite rules reset
        flush_rewrite_rules();
    }
}


