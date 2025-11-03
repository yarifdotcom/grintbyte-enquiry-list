<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Page for General Enquiry Plugin
 */
class GEN_Admin_Page {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Register main menu and submenus
     */
    public function register_menu() {
        add_menu_page(
            __( 'General Enquiry', 'gen' ),
            __( 'General Enquiry', 'gen' ),
            'manage_woocommerce',
            'gen-enquiry-list',
            [ $this, 'render_admin_page' ],
            'dashicons-format-chat',
            56
        );

        add_submenu_page(
            'gen-enquiry-list',
            __( 'Settings', 'gen' ),
            __( 'Settings', 'gen' ),
            'manage_woocommerce',
            'gen-enquiry-settings',
            [ $this, 'render_settings_page' ]
        );

         add_submenu_page(
            'gen-enquiry-list',
            __( 'How-To', 'gen' ),
            __( 'How-To', 'gen' ),
            'manage_woocommerce',
            'gen-enquiry-howto',
            [ $this, 'render_howto_page' ]
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        if ( $hook === 'toplevel_page_gen-enquiry-list' ) {
            wp_enqueue_style(
                'gen-admin',
                GEN_PLUGIN_URL . 'css/gen-admin.css',
                [],
                GEN_VERSION
            );
        }
    }

    /**
     * Render main admin page (list enquiries)
     */
    public function render_admin_page() {
        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gen_enquiries';

        // === Handle update status ===
        if ( isset( $_POST['gen_update_status'], $_POST['enquiry_id'] ) ) {
            $wpdb->update(
                $table_enquiries,
                [
                    'status' => sanitize_text_field( $_POST['status'] ),
                    'notes'  => sanitize_textarea_field( $_POST['notes'] ),
                ],
                [ 'id' => intval( $_POST['enquiry_id'] ) ]
            );

            // Redirect for clear URL
            wp_safe_redirect( add_query_arg( 'updated', '1', remove_query_arg( array( 'action', 'id' ) ) ) );
            exit;
        }

        // === Handle delete ===
        if ( isset( $_GET['action'], $_GET['id'] ) && $_GET['action'] === 'delete' ) {
            $deleted = $wpdb->delete( $table_enquiries, [ 'id' => intval( $_GET['id'] ) ] );

            // Redirect with status delete
            $status = $deleted ? '1' : '0';
            wp_safe_redirect( add_query_arg( 'deleted', $status, remove_query_arg( array( 'action', 'id' ) ) ) );
            exit;
        }

        // === Fetch data ===
        $results = $wpdb->get_results( "
            SELECT id, fullname, email, phone_number, company, website, status, notes, created_at
            FROM {$table_enquiries}
            ORDER BY created_at DESC
        " );

        include GEN_PLUGIN_DIR . 'views/admin-page-list.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {

        // === Handle settings save ===
        if ( isset( $_POST['gen_save_settings'] ) ) {

            if (
                ! isset( $_POST['gen_save_settings_nonce'] ) ||
                ! wp_verify_nonce( $_POST['gen_save_settings_nonce'], 'gen_save_settings_action' )
            ) {
                wp_die( esc_html__( 'Security check failed.', 'gen' ) );
            }

            $settings = [
                'notify_email' => sanitize_email( $_POST['notify_email'] ?? '' ),
                'email_subject'=> sanitize_text_field( $_POST['email_subject'] ?? '' ),
                'email_body'   => wp_kses_post( $_POST['email_body'] ?? '' ),
            ];

            // Fallback if null
            if ( empty( $settings['notify_email'] ) ) {
                $settings['notify_email'] = get_option( 'admin_email' );
            }

            update_option( 'gen_enquiry_settings', $settings );

            add_settings_error(
                'gen_settings_messages',
                'gen_settings_saved',
                __( 'Settings saved successfully.', 'gen' ),
                'updated'
            );

            // Refresh rewrite rules if struktur changes
            flush_rewrite_rules();
        }

        // === Load current settings or defaults ===
        $defaults = [
            'notify_email'  => get_option( 'admin_email' ),
            'email_subject' => 'New General Enquiry Received',
            'email_body'    => 'You have a new enquiry from {name} with contact {email} - {phone} - {company} - {website}',
        ];

        $settings = wp_parse_args( get_option( 'gen_enquiry_settings', [] ), $defaults );

        include GEN_PLUGIN_DIR . 'views/admin-page-setting.php';
    }

    public function render_howto_page() {
        include GEN_PLUGIN_DIR . 'views/admin-page-howto.php';
    }
}
