<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle "My Enquiry" tab in WooCommerce My Account page
 */
class GBE_My_Enquiry {

    public function __construct() {
        // Add new tab endpoint
        add_action( 'init', array( $this, 'add_endpoint' ) );

        // Add query var
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

        // Add new tab label
        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );

        // Show content
        add_action( 'woocommerce_account_my-enquiry_endpoint', array( $this, 'render_content' ) );
    }

    /**
     * Register new endpoint
     */
    public function add_endpoint() {
        add_rewrite_endpoint( 'my-enquiry', EP_ROOT | EP_PAGES );
    }

    /**
     * Add new query var
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'my-enquiry';
        return $vars;
    }

    /**
     * Add menu item in My Account
     */
    public function add_menu_item( $items ) {
        $logout = $items['customer-logout']; // backup logout
        unset( $items['customer-logout'] );  // remove it
        $items['my-enquiry'] = __( 'My Enquiry', 'gbe' ); // add our tab
        $items['customer-logout'] = $logout; // put logout back
        return $items;
    }

    /**
     * Render tab content
     */
    public function render_content() {
        $current_user = wp_get_current_user();

        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';
        $table_items = $wpdb->prefix . 'gbe_enquiry_items';

         // Get enquiries by email
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.*, i.product_name, i.variant_text FROM $table_enquiries e 
                 LEFT JOIN $table_items i ON e.id = i.enquiry_id
                 WHERE e.email = %s ORDER BY e.created_at DESC",
                $current_user->user_email
            )
        );

        echo '<h3>' . esc_html__( 'My Enquiries', 'gbe' ) . '</h3>';

        if ( ! empty( $results ) ) {
            echo '<div class="gbe-my-enquiry">';
            echo '<table class="shop_table shop_table_responsive my_account_enquiry">';
            echo '<thead><tr>';
            echo '<th>' . __( 'Product', 'gbe' ) . '</th>';
            echo '<th>' . __( 'Status', 'gbe' ) . '</th>';
            echo '<th>' . __( 'Notes', 'gbe' ) . '</th>';
            echo '<th>' . __( 'Action', 'gbe' ) . '</th>';
            echo '</tr></thead><tbody>';
            
            foreach ( $results as $row ) {
                $product_display = $row->product_name;
                if ( !empty($row->variant_text) ) {
                    $product_display .= ' (' . $row->variant_text . ')';
                }

                echo '<tr>';
                echo '<td>' . esc_html( $product_display ) . '</td>';
                echo '<td>' . esc_html( $row->status ) . '</td>';
                echo '<td>' . esc_html( $row->notes ) . '</td>';
                echo '<td><a href="' . esc_url( add_query_arg( array( 'delete_enquiry' => $row->id ) ) ) . '" class="button">' . __( 'Delete', 'gbe' ) . '</a></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
            
        } else {
            echo '<p>' . __( 'You have no enquiries yet.', 'gbe' ) . '</p>';
        }

        // Handle delete action
        if ( isset( $_GET['delete_enquiry'] ) ) {
            $this->delete_enquiry( absint( $_GET['delete_enquiry'] ), $current_user->user_email );
        }
    }

    /**
     * Delete enquiry by ID and user email
     */
    private function delete_enquiry( $id, $email ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gbe_enquiries';

        $wpdb->delete(
            $table_name,
            array(
                'id'    => $id,
                'email' => $email,
            ),
            array( '%d', '%s' )
        );

        wc_add_notice( __( 'Enquiry deleted successfully.', 'gbe' ), 'success' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'gbe-enquiries' ) );
        exit;
    }
}
