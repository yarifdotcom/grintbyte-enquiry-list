<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend class for General Enquiry plugin
 */
class GEN_Frontend {

    public function __construct() {
       // Shortcode for form
       add_shortcode( 'gen_enquiry_form', array( $this, 'render_enquiry_shortcode' ) );

       // Shortcode for button
       add_shortcode( 'gen_enquiry_button', array( $this, 'render_enquiry_button' ) );

        // Enqueue scripts & styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Ajax submission
        add_action( 'wp_ajax_gen_submit_enquiry', [ $this, 'ajax_submit_enquiry' ] );
        add_action( 'wp_ajax_nopriv_gen_submit_enquiry', [ $this, 'ajax_submit_enquiry' ] );
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'gen-enquiry-form',
            GEN_PLUGIN_URL . 'css/enquiry-form.css',
            array(),
            GEN_VERSION
        );

        wp_enqueue_script(
            'gen-enquiry-form',
            GEN_PLUGIN_URL . 'js/enquiry-form.js',
            array( 'jquery' ),
            GEN_VERSION,
            true
        );

        wp_localize_script( 'gen-enquiry-form', 'gen_ajax', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'gen_enquiry_nonce' ),
        ] );
    }

    /**
     * Render the enquiry form shortcode.
     */
    public function render_enquiry_shortcode() {
        $template = $this->locate_template( 'enquiry-form.php' );

        ob_start();
        if ( $template ) {
            include $template;
        } else {
            esc_html_e( 'Enquiry form template not found.', 'gen' );
        }
        return ob_get_clean();
    }

    /**
     * Render the enquiry button shortcode.
     * Usage: [gen_enquiry_button label="Ask Us" class="btn btn-primary"]
     */
    public function render_enquiry_button( $atts = [] ) {
        $atts = shortcode_atts( [
            'label' => __( 'General Enquiry', 'gen' ),
            'class' => 'gen-enquiry-btn',
        ], $atts, 'gen_enquiry_button' );

        $url = home_url( '/general-enquiry' );

        ob_start();
        ?>
        <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $atts['class'] ); ?>">
            <?php echo esc_html( $atts['label'] ); ?>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Locate template (child theme → parent theme → plugin)
     */
    public function locate_template( $template_name ) {
        $template = locate_template( array(
            'general-enquiry/' . $template_name,
            $template_name
        ) );

        if ( ! $template ) {
            $plugin_path = GEN_PLUGIN_DIR . 'views/' . $template_name;
            if ( file_exists( $plugin_path ) ) {
                $template = $plugin_path;
            }
        }

        return $template;
    }

    /**
     * Handle AJAX enquiry submission.
     */
    public function ajax_submit_enquiry() {
        check_ajax_referer( 'gen_enquiry_nonce', 'security' );

        global $wpdb;
        $table_enquiries = $wpdb->prefix . 'gen_enquiries';

        $email    = sanitize_email( $_POST['email'] ?? '' );
        $fullname = sanitize_text_field( $_POST['fullname'] ?? '' );
        $phone    = sanitize_text_field( $_POST['phone'] ?? '' );
        $company  = sanitize_text_field( $_POST['company'] ?? '' );
        $website  = sanitize_text_field( $_POST['website'] ?? '' );
        $message  = sanitize_textarea_field( $_POST['message'] ?? '' );

        if ( empty( $fullname ) || empty( $email ) || empty( $message ) ) {
            wp_send_json( [
                'status'  => 'error',
                'message' => __( 'Please fill in all required fields.', 'gen' ),
            ] );
        }

        // Cek apakah user sudah terdaftar
        if ( ! is_user_logged_in() ) {
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                wp_send_json( [
                    'status'  => 'error',
                    'message' => __( 'This email is registered. Please login to continue your enquiry.', 'gen' ),
                    'redirect'=> wc_get_page_permalink( 'myaccount' ),
                ] );
            }
        }

        // Insert ke database
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
                'message' => __( 'Failed to save enquiry.', 'gen' ),
                'error'   => $wpdb->last_error,
            ] );
        }

        // Send email notification
        $this->send_email_notification( [
            'fullname'     => $fullname,
            'email'        => $email,
            'phone_number' => $phone,
            'company'      => $company,
            'website'      => $website,
            'notes'        => $message,
            'created_at'   => current_time( 'mysql' ),
        ] );

        wp_send_json( [
            'status'   => 'success',
            'message'  => __( 'Your enquiry has been submitted successfully!', 'gen' ),
            'redirect' => wp_get_referer() ?: home_url(), // back to page before
        ] );
    }

    /**
     * Send email notification
     */
    private function send_email_notification( $data ) {
        if ( empty( $data ) ) return;

        $settings = get_option( 'gen_enquiry_settings', [] );

        $to       = $settings['notify_email'] ?? get_option( 'admin_email' );
        $subject  = $settings['email_subject'] ?? __( 'New Enquiry Received', 'gen' );
        $body     = $settings['email_body'] ?? 'You have a new enquiry from {name} ({email}) - {phone} - {company} - {website}. Message: {message}';

        $replacements = [
            '{name}'    => $data['fullname'],
            '{email}'   => $data['email'],
            '{phone}'   => $data['phone_number'],
            '{company}' => $data['company'],
            '{website}' => $data['website'],
            '{message}' => $data['notes'],
            '{date}'    => $data['created_at'],
        ];

        $body = str_replace( array_keys( $replacements ), array_values( $replacements ), $body );

        return wp_mail( $to, $subject, $body );
    }
}
