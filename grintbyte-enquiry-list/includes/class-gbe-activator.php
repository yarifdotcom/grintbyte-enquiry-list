<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Activator {

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gbe_enquiries';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product varchar(255) NOT NULL,
            fullname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone_number varchar(100) DEFAULT '' NOT NULL,
            company varchar(255) NOT NULL,
            website varchar(255) NOT NULL,
            status varchar(50) DEFAULT 'Received' NOT NULL,
            notes text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // 🔹 Automatically create enquiry page if not exists
        $page_check = get_page_by_path( 'enquiry' );
        if ( ! $page_check ) {
            $page_id = wp_insert_post( array(
                'post_title'     => 'Enquiry',
                'post_name'      => 'enquiry',
                'post_content'   => '[gbe_enquiry_form]', // shortcode will render template
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => get_current_user_id(),
                'comment_status' => 'closed'
            ) );
        }

        // rewrite once
        add_rewrite_rule(
            '^enquiry/([0-9]+)/?',
            'index.php?pagename=enquiry&product_id=$matches[1]',
            'top'
        );
        add_rewrite_tag( '%product_id%', '([0-9]+)' );
        flush_rewrite_rules();

        // Default settings (only if not exist)
        if ( get_option( 'gbe_email_settings' ) === false ) {
            add_option( 'gbe_email_settings', [
                'to'      => get_option( 'admin_email' ), // default ke admin WP
                'subject' => 'New Enquiry Received',
                'body'    => 'You have a new enquiry from {name} about {product} with contact {email} - {phone} - {company} - {website}',
            ] );
        }
    }
}
