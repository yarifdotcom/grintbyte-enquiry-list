<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Uninstaller {
    public static function uninstall() {
        global $wpdb;

        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';
        $table_items     = $wpdb->prefix . 'gbe_enquiry_items';

        // delete child table dulu (foreign key constraint)
        $wpdb->query( "DROP TABLE IF EXISTS {$table_items}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$table_enquiries}" );

        // hapus juga option email settings (biar clean uninstall)
        delete_option( 'gbe_email_settings' );

        // reset rewrite rules
        flush_rewrite_rules();
    }
}
