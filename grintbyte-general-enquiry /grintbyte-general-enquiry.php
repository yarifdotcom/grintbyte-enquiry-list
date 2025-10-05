<?php
/**
 * Plugin Name: GrintByte General Enquiry
 * Plugin URI: https://grintbyte.com
 * Description: A general enquiry management system for WooCommerce with a top-level admin menu.
 * Version: 1.0.0
 * Author: GrintByte
 * Author URI: https://grintbyte.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// =======================================
// Define plugin constants
// =======================================
define( 'GEN_VERSION', '1.0.0' );
define( 'GEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// =======================================
// Include required files
// =======================================
require_once GEN_PLUGIN_DIR . 'includes/class-gen-activator.php';
require_once GEN_PLUGIN_DIR . 'includes/class-gen-deactivator.php';
require_once GEN_PLUGIN_DIR . 'includes/class-gen-uninstaller.php';
require_once GEN_PLUGIN_DIR . 'includes/class-gen-admin-page.php';
require_once GEN_PLUGIN_DIR . 'includes/class-gen-frontend.php';
require_once GEN_PLUGIN_DIR . 'includes/class-gen-my-enquiry.php';

// =======================================
// Activation hook
// =======================================
register_activation_hook( __FILE__, array( 'GEN_Activator', 'activate' ) );

// =======================================
// Deactivation hook
// =======================================
register_deactivation_hook( __FILE__, array( 'GEN_Deactivator', 'deactivate' ) );

// =======================================
// Uninstall hook
// =======================================
register_uninstall_hook( __FILE__, array( 'GEN_Uninstaller', 'uninstall' ) );

// =======================================
// Initialize Admin Page and Frontend
// =======================================
add_action( 'plugins_loaded', function() {
    new GEN_Admin_Page();
    new GEN_Frontend();
});

// =======================================
// WooCommerce integration
// =======================================
add_action( 'woocommerce_init', function() {
    if ( class_exists( 'WooCommerce' ) ) {
        new GEN_My_Enquiry();
    }
});
