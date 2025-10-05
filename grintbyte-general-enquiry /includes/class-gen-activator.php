<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GEN_Activator {

    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // ==============================
        // 1) Main enquiry table
        // ==============================
        $table_enquiries = $wpdb->prefix . 'gen_enquiries';

        $sql1 = "CREATE TABLE {$table_enquiries} (
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

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql1 );

        // ==============================
        // 2) Auto-create enquiry page
        // ==============================
        $page_check = get_page_by_path( 'general-enquiry' );
        if ( ! $page_check ) {
            wp_insert_post( array(
                'post_title'     => 'General Enquiry',
                'post_name'      => 'general-enquiry',
                'post_content'   => '[gen_enquiry_form]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => get_current_user_id(),
                'comment_status' => 'closed'
            ) );
        }

        // ==============================
        // 3) Rewrite rule for enquiry page
        // ==============================
        add_rewrite_tag( '%enquiry_id%', '([0-9]+)' );
        add_rewrite_rule(
            '^general-enquiry/([0-9]+)/?',
            'index.php?pagename=general-enquiry&enquiry_id=$matches[1]',
            'top'
        );

        // ==============================
        // 4) Default settings
        // ==============================
        if ( ! get_option( 'gen_enquiry_settings' ) ) {
            add_option( 'gen_enquiry_settings', [
                'notify_email'  => get_option( 'admin_email' ),
                'email_subject' => 'New General Enquiry Received',
                'email_body'    => 'You have a new enquiry from {name} with contact {email} - {phone} - {company} - {website}',
            ] );
        }

        // ==============================
        // 5) Refresh permalink
        // ==============================
        flush_rewrite_rules();
    }
}
