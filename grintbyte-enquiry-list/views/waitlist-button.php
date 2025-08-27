<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

if ( ! $product ) {
    return; 
}

$product_id = $product->get_id();
?>

<div class="gbe-enquiry-button">
    <!-- <php if ( ! is_user_logged_in() ) : ?> <a href="<php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" -->
    
    <a href="<?php echo esc_url( home_url( '/enquiry/' . $product_id . '/' ) ); ?>" 
        class="button alt"
        style="text-decoration: none !important; margin-bottom: 15px;">
        <?php esc_html_e( 'Send Enquiry', 'gbe' ); ?>
    </a>

</div>
