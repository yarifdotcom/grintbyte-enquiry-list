<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend class for GBE Enquiry plugin
 */
class GBE_Frontend {

    public function __construct() {
        add_action( 'template_redirect', array( $this, 'handle_form_submission' ) );
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_enquiry_button' ), 35 );

        // Register shortcode
        add_shortcode( 'gbe_enquiry_form', array( $this, 'render_enquiry_shortcode' ) );

        // Enqueue scripts & styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

         // Redirect after login
         add_filter( 'woocommerce_login_redirect', [ $this, 'redirect_after_login' ], 10, 2 );
    }

    /**
     * Add custom query vars
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'product_id';
        return $vars;
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'gbe-enquiry-form',
            GBE_PLUGIN_URL . 'assets/css/enquiry-form.css',
            array(),
            GBE_VERSION
        );

        wp_enqueue_script(
            'gbe-enquiry-form',
            GBE_PLUGIN_URL . 'assets/js/enquiry-form.js',
            array( 'jquery' ),
            GBE_VERSION,
            true
        );
    }

    /**
     * Render enquiry button on single product.
     */
    public function render_enquiry_button() {
        global $product;
    
        // Cek produk valid & hanya tampilkan jika stok habis
        if ( ! $product || $product->is_in_stock() ) {
            return;
        }
    
        $product_id = $product->get_id();
    
        // Variabel yang akan dipakai di view
        $args = array(
            'product_id' => $product_id,
        );
    
        // Extract biar di view bisa langsung pakai $product_id
        extract( $args );
    
        // Load template
        $template = GBE_PLUGIN_DIR . 'views/waitlist-button.php';
    
        if ( file_exists( $template ) ) {
            include $template;
        }
    }
    
    
    /**
     * Shortcode callback.
     */
    public function render_enquiry_shortcode() {

        global $wp_query;

        //  product_id from query var or GET
        $product_id = 0;
        if ( isset( $wp_query->query_vars['product_id'] ) ) {
            $product_id = intval( $wp_query->query_vars['product_id'] );
        } elseif ( isset( $_GET['product_id'] ) ) {
            $product_id = intval( $_GET['product_id'] );
        }

        $template = $this->locate_template( 'enquiry-form.php' );

        ob_start();
        if ( $template ) {
            include $template;
        } else {
            esc_html_e( 'Enquiry form template not found.', 'gbe' );
        }
        return ob_get_clean();
    }

    /**
     * Locate template (child theme → parent theme → plugin).
     */
    public function locate_template( $template_name ) {
        $template = locate_template( array(
            'grintbyte-enquiry-list/' . $template_name,
            $template_name
        ) );

        if ( ! $template ) {
            $plugin_path = GBE_PLUGIN_DIR . 'views/' . $template_name;
            if ( file_exists( $plugin_path ) ) {
                $template = $plugin_path;
            }
        }

        return $template;
    }

    /**
     * Redirect after login
     */
    public function redirect_after_login( $redirect, $user ) {
        if ( isset( $_REQUEST['redirect_to'] ) ) {
            return esc_url_raw( $_REQUEST['redirect_to'] );
        }
        return $redirect;
    }



    /**
     * Handle enquiry form submission.
     */
    
    public function handle_form_submission() {
        if ( ! isset( $_POST['gbe_enquiry_submit'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! isset( $_POST['gbe_enquiry_nonce'] ) || ! wp_verify_nonce( $_POST['gbe_enquiry_nonce'], 'gbe_enquiry_form_action' ) ) {
            wc_add_notice( __( 'Invalid form submission.', 'gbe' ), 'error' );
            wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
            exit;
        }

        // Validate login status
        if ( ! is_user_logged_in() ) {
            $email = sanitize_email($_POST['email']);
            $user  = get_user_by('email', $email);

            if ( $user ) {
                $_SESSION['gbe_message'] = [
                    'type'    => 'error',
                    'message' => __( 'This email is registered. Please login to continue your enquiry.', 'gbe' ),
                ];
                
              
                exit;
            }
            
        }

        $fullname       = sanitize_text_field( trim( ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '') ) );
        $message    = sanitize_textarea_field( $_POST['message'] ?? '' );
        $phone      = sanitize_text_field( $_POST['phone'] ?? '' );
        $product_id = absint( $_POST['product_id'] ?? 0 );        

        global $wpdb;
        $table_name = $wpdb->prefix . 'gbe_enquiries';

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'product' => $product_id,
                // 'user_id'    => get_current_user_id(),
                'fullname'   => $fullname,
                'email'      => sanitize_email($_POST['email']),
                'phone_number' => $phone,
                'notes'    => $message,
                'status'   => 'Received',
                'created_at' => current_time( 'mysql' ),
            )
        );        

        if ( $inserted ) {
            // Fire action hook for developers
            do_action( 'gbe_enquiry_submitted', $wpdb->insert_id, $_POST );

            var_dump( $inserted );

            $_SESSION['gbe_message'] = [
                'type'    => 'success',
                'message' => __( 'Your enquiry has been submitted successfully!', 'gbe' ),
            ];

            //Redirect back to product page if available
            if ( $product_id  && ( $product_url = get_permalink( $product_id ) )) {
                wp_safe_redirect( $product_url );
                exit;
            }

            // Fallback redirect to shop
            wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
            exit;
        } else {
            error_log( 'Insert failed: ' . $wpdb->last_error );
            error_log( 'Query: ' . $wpdb->last_query );
            var_dump( $wpdb->last_error );
            var_dump( $wpdb->last_query );

            $_SESSION['gbe_message'] = [
                'type'    => 'error',
                'message' => __( 'There was an error submitting your enquiry. Please try again.', 'gbe' ),
            ];

            // Redirect back to enquiry page
            $redirect = get_permalink( get_option( 'gbe_enquiry_page_id' ) );
            wp_safe_redirect( $redirect ? $redirect : wc_get_page_permalink( 'shop' ) );
            exit;
        }
    }

    /**
     * Handle toast message in enquiry-form.
     */

     public function render_inline_message() {
        if ( isset( $_SESSION['gbe_message'] ) ) {
            $msg = $_SESSION['gbe_message'];
            unset( $_SESSION['gbe_message'] ); // show once
    
            if ( ! empty( $msg['message'] ) ) {
                $type    = $msg['type'] ?? 'notice'; // success | error | notice
                $redirect = $msg['redirect'] ?? ''; // optional redirect url
                ?>
                <div 
                    class="gbe-inline-message gbe-<?php echo esc_attr( $type ); ?>" 
                    data-redirect="<?php echo esc_url( $redirect ); ?>"
                >
                    <?php echo esc_html( $msg['message'] ); ?>
                </div>
                <?php
            }
        }
    }
    

}
