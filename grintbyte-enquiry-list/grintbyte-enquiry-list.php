<?php
/**
 * Plugin Name: GrintByte Enquiry List
 * Plugin URI: https://grintbyte.com
 * Description: A custom enquiry/waitlist management system for WooCommerce with a top-level admin menu.
 * Version: 1.0.0
 * Author: GrintByte
 * Author URI: https://grintbyte.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'GBE_VERSION', '1.0.0' );
define( 'GBE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GBE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once GBE_PLUGIN_DIR . 'includes/class-gbe-activator.php';
require_once GBE_PLUGIN_DIR . 'includes/class-gbe-admin-page.php';
require_once GBE_PLUGIN_DIR . 'includes/class-gbe-frontend.php';

// Activation hook
register_activation_hook( __FILE__, array( 'GBE_Activator', 'activate' ) );

// Initialize Admin Page
add_action( 'plugins_loaded', function() {
    new GBE_Admin_Page();
    new GBE_Frontend();
});
