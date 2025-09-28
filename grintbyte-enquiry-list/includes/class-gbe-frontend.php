<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend class for GBE Enquiry plugin
 */
class GBE_Frontend {

    public function __construct() {
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_enquiry_button' ), 35 );

        // Register shortcode
        add_shortcode( 'gbe_enquiry_form', array( $this, 'render_enquiry_shortcode' ) );

        // Enqueue scripts & styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Register rewrite rules (selain di activator)
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );

        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        // Redirect after login
        add_filter( 'woocommerce_login_redirect', [ $this, 'redirect_after_login' ], 10, 2 );

        // Hook ajax
        add_action( 'wp_ajax_gbe_submit_enquiry', [ $this, 'ajax_submit_enquiry' ] );
        add_action( 'wp_ajax_nopriv_gbe_submit_enquiry', [ $this, 'ajax_submit_enquiry' ] );

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
            GBE_PLUGIN_URL . 'css/enquiry-form.css',
            array(),
            GBE_VERSION
        );

        wp_enqueue_script(
            'gbe-enquiry-form',
            GBE_PLUGIN_URL . 'js/enquiry-form.js',
            array( 'jquery' ),
            GBE_VERSION,
            true
        );

        wp_localize_script( 'gbe-enquiry-form', 'gbe_ajax', [
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'gbe_enquiry_nonce' ),
        ] );
    }

    /**
     * Render enquiry button on single product.
     */
    public function render_enquiry_button() {
        global $product;

        if ( ! $product || $product->is_in_stock() ) {
            return;
        }
    
        $product_id = $product->get_id();

        $args = array(
            'product_id' => $product_id,
        );
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
    
    public function ajax_submit_enquiry() {
        check_ajax_referer( 'gbe_enquiry_nonce', 'security' );

        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gbe_enquiries';
        $table_items     = $wpdb->prefix . 'gbe_enquiry_items';

        $email      = sanitize_email( $_POST['email'] ?? '' );

        // check valid user
        if ( ! is_user_logged_in() ) {
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                wp_send_json( [
                    'status'  => 'error',
                    'message' => __( 'This email is registered. Please login to continue your enquiry.', 'gbe' ),
                    'redirect'=> wc_get_page_permalink( 'myaccount' ),
                ] );
            }
        }

        $fullname   = sanitize_text_field( trim( ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '') ) );
        $message    = sanitize_textarea_field( $_POST['message'] ?? '' );
        $phone      = sanitize_text_field( $_POST['phone'] ?? '' );
        $company    = sanitize_text_field( $_POST['company'] ?? '' );
        $website    = sanitize_text_field( $_POST['website'] ?? '' );
        $product_id = absint( $_POST['product_id'] ?? 0 );     
        $variation_id = absint( $_POST['variation_id'] ?? 0 );   

        // Insert main enquiry
        $inserted = $wpdb->insert(
            $table_enquiries,
            [
                'fullname'     => $fullname,
                'email'        => $email,
                'phone_number' => $phone,
                'company'      => $company,
                'website'      => $website,
                'notes'        => $message,
                'status'       => 'Received',
                'created_at'   => current_time( 'mysql' ),
            ]
        );

        if ( ! $inserted ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => __( 'Failed to save enquiry.', 'gbe' ),
                'error'   => $wpdb->last_error,
            ] );
        }

        $enquiry_id = $wpdb->insert_id;

        // Insert product item
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => __( 'Invalid product.', 'gbe' ),
            ] );
        }

        $variant_text = '';

        if ( $product->is_type( 'variable' ) && $variation_id ) {
            $variation = wc_get_product( $variation_id );
            if ( $variation ) {
                $variant_text = wc_get_formatted_variation( $variation, true );
            }
        } else {
            $variation_id = 0; // Set to 0 for simple products
        }

        $wpdb->insert(
            $table_items,
            [
                'enquiry_id'   => $enquiry_id,
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'product_name' => $product->get_name(),
                'variant_text' => $variant_text,
                'quantity'     => 1,
            ]
        );

        // Send email notification
        $this->send_email_notification( [
            'fullname'     => $fullname,
            'email'        => $email,
            'phone_number' => $phone,
            'company'      => $company,
            'website'      => $website,
            'notes'        => $message,
            'product'      => $product->get_name(),
            'variation'    => $variant_text,
            'created_at'   => current_time( 'mysql' ),
        ] );

        $redirect_url = $product_id ? get_permalink( $product_id ) : wc_get_page_permalink( 'shop' );


        wp_send_json( [
            'status'   => 'success',
            'message'  => __( 'Your enquiry has been submitted successfully!', 'gbe' ),
            'redirect' => $redirect_url,
        ] );
    }

     /**
     * Send email notification after enquiry
     */
    private function send_email_notification( $raw_data ) {

        if ( empty($raw_data) ) return;

        // Settings
        $settings = get_option( 'gbe_enquiry_settings', [] );
        $to       = $settings['notify_email'] ?? get_option( 'admin_email' );
        $subject  = $settings['email_subject'] ?? 'New Enquiry Received';
        $body     = $settings['email_body'] ?? 'You have a new enquiry from {name} about {product} with contact {email} - {phone}.';

        $replacements = [
            '{name}'    => $raw_data['fullname'],
            '{email}'   => $raw_data['email'],
            '{phone}'   => $raw_data['phone_number'],
            '{company}' => $raw_data['company'],
            '{website}' => $raw_data['website'],
            '{message}' => $raw_data['notes'],
            '{product}' => $raw_data['product'],
            '{variation}' => $raw_data['variation'] ?? '',
            '{date}'    => $raw_data['created_at'],
        ];

        $body = str_replace( array_keys( $replacements ), array_values( $replacements ), $body );

        return wp_mail( $to, $subject, $body );
    }

    /**
     * Fix wp engine
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^enquiry/([0-9]+)/?',
            'index.php?pagename=enquiry&product_id=$matches[1]',
            'top'
        );
        add_rewrite_tag( '%product_id%', '([0-9]+)' );
    }
}
