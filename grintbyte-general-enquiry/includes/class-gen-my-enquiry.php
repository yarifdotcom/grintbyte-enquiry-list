<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle "My General Enquiry" tab in WooCommerce My Account page
 */
class GEN_My_Enquiry {

    public function __construct() {
        // Add new tab endpoint
        add_action( 'init', array( $this, 'add_endpoint' ) );

        // Add query var
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

        // Add new tab label
        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );

        // Show content
        add_action( 'woocommerce_account_my-general-enquiry_endpoint', array( $this, 'render_content' ) );
    }

    /**
     * Register new endpoint
     */
    public function add_endpoint() {
        // URL: /my-account/my-general-enquiry
        add_rewrite_endpoint( 'my-general-enquiry', EP_ROOT | EP_PAGES );
    }

    /**
     * Add new query var
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'my-general-enquiry';
        return $vars;
    }

    /**
     * Add menu item in My Account
     */
    public function add_menu_item( $items ) {
        $logout = $items['customer-logout']; // backup logout
        unset( $items['customer-logout'] );  // remove temporarily
        $items['my-general-enquiry'] = __( 'My General Enquiry', 'gen' ); // add our tab
        $items['customer-logout'] = $logout; // restore logout
        return $items;
    }

    /**
     * Render tab content
     */
    public function render_content() {
        $current_user = wp_get_current_user();

        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gen_enquiries';
    
        // Ambil data enquiry berdasarkan email user login
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_enquiries} WHERE email = %s ORDER BY created_at DESC",
                $current_user->user_email
            )
        );

        echo '<h3>' . esc_html__( 'My General Enquiries', 'gen' ) . '</h3>';

        // Handle delete action (harus di atas output tabel agar redirect bisa jalan sebelum output)
        if ( isset( $_GET['delete_enquiry'] ) ) {
            $this->delete_enquiry( absint( $_GET['delete_enquiry'] ), $current_user->user_email );
        }

        if ( ! empty( $results ) ) {
            echo '<div class="gen-my-enquiry">';
            echo '<table class="shop_table shop_table_responsive my_account_enquiry">';
            echo '<thead><tr>';
            echo '<th>' . __( 'Status', 'gen' ) . '</th>';
            echo '<th>' . __( 'Message / Notes', 'gen' ) . '</th>';
            echo '<th>' . __( 'Action', 'gen' ) . '</th>';
            echo '</tr></thead><tbody>';
            
            foreach ( $results as $row ) {
                echo '<tr>';
                echo '<td>' . esc_html( $row->status ) . '</td>';
                echo '<td>' . esc_html( $row->notes ) . '</td>';
                echo '<td><a href="' . esc_url( add_query_arg( array( 'delete_enquiry' => $row->id ) ) ) . '" class="button">' . __( 'Delete', 'gen' ) . '</a></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<p>' . __( 'You have no general enquiries yet.', 'gen' ) . '</p>';
        }
    }

    /**
     * Delete enquiry by ID and user email
     */
    private function delete_enquiry( $id, $email ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gen_enquiries';

        $wpdb->delete(
            $table_name,
            array(
                'id'    => $id,
                'email' => $email,
            ),
            array( '%d', '%s' )
        );

        wc_add_notice( __( 'Enquiry deleted successfully.', 'gen' ), 'success' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'my-general-enquiry' ) );
        exit;
    }
}
