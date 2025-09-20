<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Admin_Page {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    // Register top-level menu
    public function register_menu() {
        add_menu_page(
            __( 'Enquiry List', 'gbe' ),
            __( 'Enquiry List', 'gbe' ),
            'manage_woocommerce',
            'gbe-enquiry-list',
            array( $this, 'render_admin_page' ),
            'dashicons-email-alt2',
            56
        );

        add_submenu_page(
            'gbe-enquiry-list',
            __( 'Settings', 'gbe' ),
            __( 'Settings', 'gbe' ),
            'manage_woocommerce',
            'gbe-enquiry-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Render Enquiry List Page
     */

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gbe_enquiries';

        // Handle actions (delete, update)
        if ( isset($_POST['gbe_update_status']) && isset($_POST['enquiry_id']) ) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => sanitize_text_field($_POST['status']),
                    'notes' => sanitize_textarea_field($_POST['notes'])
                ),
                array( 'id' => intval($_POST['enquiry_id']) )
            );
        }

        if ( isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) ) {
            $wpdb->delete( $table_name, array( 'id' => intval($_GET['id']) ) );
        }

        // Fetch data
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );

        include GBE_PLUGIN_DIR . 'views/admin-page-list.php';
    }

    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        if ( isset($_POST['gbe_save_settings']) ) {
            if ( ! isset($_POST['gbe_save_settings_nonce']) || 
                ! wp_verify_nonce($_POST['gbe_save_settings_nonce'], 'gbe_save_settings_action') ) {
                wp_die( __( 'Security check failed.', 'gbe' ) );
            }
            
            $settings = [
                'notify_email' => sanitize_email( $_POST['notify_email'] ?? '' ),
                'email_subject'=> sanitize_text_field( $_POST['email_subject'] ?? '' ),
                'email_body'   => wp_kses_post( $_POST['email_body'] ?? '' ),
            ];
            update_option( 'gbe_enquiry_settings', $settings );
            
            add_settings_error(
                'gbe_settings_messages',
                'gbe_settings_saved',
                __( 'Settings saved.', 'gbe' ),
                'updated'
            );

        }

        // Default settings
        $defaults = [
            'notify_email'  => get_option( 'admin_email' ),
            'email_subject' => 'New Enquiry Received',
            'email_body'    => 'You have a new enquiry from {name} about {product} with contact {email} - {phone}',
        ];

         // merge setting and default
        $settings = wp_parse_args( get_option( 'gbe_enquiry_settings', [] ), $defaults );

        include GBE_PLUGIN_DIR . 'views/admin-page-setting.php';
    }
}
