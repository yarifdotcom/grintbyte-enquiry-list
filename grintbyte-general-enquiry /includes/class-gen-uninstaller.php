<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GEN_Uninstaller {
    public static function uninstall() {
        global $wpdb;

        $table_enquiries = $wpdb->prefix . 'gen_enquiries';

        // delete main table
        $wpdb->query( "DROP TABLE IF EXISTS {$table_enquiries}" );

        // delete plugin options (clean uninstall)
        delete_option( 'gen_enquiry_settings' );

        // reset rewrite rules
        flush_rewrite_rules();
    }
}
