<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Activator {

    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // ==============================
        // 1) Main enquiry table
        // ==============================
        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';

        $sql1 = "CREATE TABLE $table_enquiries (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            fullname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone_number varchar(100) DEFAULT '' NOT NULL,
            company varchar(255) DEFAULT '' NOT NULL,
            website varchar(255) DEFAULT '' NOT NULL,
            status varchar(50) DEFAULT 'Received' NOT NULL,
            notes text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // ==============================
        // 2) Enquiry items table
        // ==============================
        $table_items = $wpdb->prefix . 'gbe_enquiry_items';

        $sql2 = "CREATE TABLE $table_items (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            enquiry_id mediumint(9) NOT NULL,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) DEFAULT 0 NOT NULL,
            product_name varchar(255) NOT NULL,
            variant_text varchar(255) DEFAULT '' NOT NULL,
            quantity int(11) DEFAULT 1 NOT NULL,
            PRIMARY KEY  (id),
            KEY enquiry_id (enquiry_id),
            CONSTRAINT fk_enquiry FOREIGN KEY (enquiry_id)
                REFERENCES $table_enquiries (id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql1 );
        dbDelta( $sql2 );

        // 🔹 Automatically create enquiry page if not exists
        $page_check = get_page_by_path( 'enquiry' );
        if ( ! $page_check ) {
            $page_id = wp_insert_post( array(
                'post_title'     => 'Enquiry',
                'post_name'      => 'enquiry',
                'post_content'   => '[gbe_enquiry_form]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => get_current_user_id(),
                'comment_status' => 'closed'
            ) );
        }

        add_rewrite_rule(
            '^enquiry/([0-9]+)/?',
            'index.php?pagename=enquiry&product_id=$matches[1]',
            'top'
        );
        add_rewrite_tag( '%product_id%', '([0-9]+)' );

        // Default settings
        if ( get_option( 'gbe_enquiry_settings' ) === false ) {
            add_option( 'gbe_enquiry_settings', [
                'notify_email'  => get_option( 'admin_email' ),
                'email_subject' => 'New Enquiry Received',
                'email_body'    => 'You have a new enquiry from {name} about {product} with contact {email} - {phone} - {company} - {website}',
            ] );
        }
    }
}