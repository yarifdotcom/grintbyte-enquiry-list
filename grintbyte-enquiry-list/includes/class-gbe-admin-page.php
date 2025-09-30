<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Admin_Page {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'wp_ajax_gbe_preview_enquiry', array( $this, 'ajax_preview_enquiry' ) );
    }

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

    public function enqueue_admin_assets( $hook ) {
        if ( $hook === 'toplevel_page_gbe-enquiry-list' ) {
            
            wp_enqueue_style(
                'gbe-admin',
                GBE_PLUGIN_URL . 'css/gbe-admin.css',
                array(),
                GBE_VERSION
            );

            wp_enqueue_script(
                'gbe-admin',
                GBE_PLUGIN_URL . 'js/gbe-admin.js',
                [ 'jquery' ],
                '1.0',
                true
            );
            wp_localize_script(
                'gbe-admin',
                'gbe_admin',
                [ 'nonce' => wp_create_nonce( 'gbe_preview_enquiry' ) ]
            );
        }
    }

    public function render_admin_page() {
        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';
        $table_items     = $wpdb->prefix . 'gbe_enquiry_items';

        // Handle update status
        if ( isset($_POST['gbe_update_status']) && isset($_POST['enquiry_id']) ) {
            $wpdb->update(
                $table_enquiries,
                array(
                    'status' => sanitize_text_field($_POST['status']),
                    'notes' => sanitize_textarea_field($_POST['notes'])
                ),
                array( 'id' => intval($_POST['enquiry_id']) )
            );
        }

        // Handle delete
        if ( isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) ) {
            $wpdb->delete( $table_enquiries, array( 'id' => intval($_GET['id']) ) );
        }

        // Fetch data with product info
        $results = $wpdb->get_results("
            SELECT 
                e.*, 
                GROUP_CONCAT(
                    CASE 
                        WHEN i.variation_id > 0 AND i.variant_text != '' THEN 
                            CONCAT(i.product_name, ' - ', i.variant_text)
                        WHEN i.variation_id > 0 THEN 
                            CONCAT(i.product_name, ' (Variation ID: ', i.variation_id, ')')
                        ELSE 
                            i.product_name
                    END 
                    SEPARATOR ', '
                ) as products
            FROM $table_enquiries e
            LEFT JOIN $table_items i ON e.id = i.enquiry_id
            GROUP BY e.id
            ORDER BY e.created_at DESC
        ");


        include GBE_PLUGIN_DIR . 'views/admin-page-list.php';
    }

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

        $defaults = [
            'notify_email'  => get_option( 'admin_email' ),
            'email_subject' => 'New Enquiry Received',
            'email_body'    => 'You have a new enquiry from {name} about {product} with contact {email} - {phone} - {company} - {website}',
        ];

        $settings = wp_parse_args( get_option( 'gbe_enquiry_settings', [] ), $defaults );

        include GBE_PLUGIN_DIR . 'views/admin-page-setting.php';
    }

    public function ajax_preview_enquiry() {
        check_ajax_referer( 'gbe_preview_enquiry' );
        
        global $wpdb;
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gbe' ) );
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ( ! $id ) {
            wp_send_json_error( __( 'Invalid enquiry ID.', 'gbe' ) );
        }

        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';
        $table_items     = $wpdb->prefix . 'gbe_enquiry_items';

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_enquiries WHERE id = %d", $id ) );
        if ( ! $row ) {
            wp_send_json_error( __( 'Enquiry not found.', 'gbe' ) );
        }

        $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_items WHERE enquiry_id = %d", $id ) );

        ob_start();
        ?>
        <div class="gbe-modal-content" role="dialog" aria-modal="true">
            <header>
                <span class="gbe-close">&times;</span>
                <h1><?php echo esc_html( $row->fullname ); ?></h1>
                <mark class="order-status status-<?php echo sanitize_title( strtolower( $row->status ) ); ?>">
                    <span><?php echo esc_html( $row->status ); ?></span>
                </mark>
            </header>
                <article>
                    <div class="wc-order-preview-address">
                        <h2><?php _e( 'Enquiry details', 'gbe' ); ?></h2>
                        <dl class="gbe-enquiry-details">
                            <dt><?php _e( 'Full Name', 'gbe' ); ?>:</dt>
                            <dd><?php echo esc_html( $row->fullname ); ?></dd>

                            <dt><?php _e( 'Email', 'gbe' ); ?>:</dt>
                            <dd><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></dd>

                            <dt><?php _e( 'Phone', 'gbe' ); ?>:</dt>
                            <dd><a href="tel:<?php echo esc_attr( $row->phone_number ); ?>"><?php echo esc_html( $row->phone_number ); ?></a></dd>

                            <?php if ( $row->company ) : ?>
                                <dt><?php _e( 'Company', 'gbe' ); ?>:</dt>
                                <dd><?php echo esc_html( $row->company ); ?></dd>
                            <?php endif; ?>

                            <?php if ( $row->website ) : ?>
                                <dt><?php _e( 'Website', 'gbe' ); ?>:</dt>
                                <dd><a href="<?php echo esc_url( $row->website ); ?>" target="_blank"><?php echo esc_html( $row->website ); ?></a></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <div class="wc-order-preview-table-wrapper">
                        <table cellspacing="0" class="wc-order-preview-table">
                            <thead>
                                <tr>
                                    <th><?php _e( 'Product', 'gbe' ); ?></th>
                                    <th><?php _e( 'Variant', 'gbe' ); ?></th>
                                    <!-- <th><php _e( 'Quantity', 'gbe' ); ?></th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $items as $item ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $item->product_name ); ?></td>
                                    <td><?php echo esc_html( $item->variant_text ); ?></td>
                                    <!-- <td><php echo esc_html( $item->quantity ); ?></td> -->
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
        
        </div>
        <?php
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }
}