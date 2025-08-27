<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Detect product ID from URL or query string
global $wp_query;

$product_id = 0;
if ( isset( $wp_query->query_vars['product_id'] ) ) {
    $product_id = intval( $wp_query->query_vars['product_id'] );
} elseif ( isset( $_GET['product_id'] ) ) {
    // fallback if query var is missing
    $product_id = intval( $_GET['product_id'] );
}

$product = $product_id ? wc_get_product( $product_id ) : false;

// Prefill user data if logged in
$current_user = wp_get_current_user();
$first_name   = $current_user->exists() ? $current_user->user_firstname : '';
$last_name    = $current_user->exists() ? $current_user->user_lastname  : '';
$email        = $current_user->exists() ? $current_user->user_email     : '';
?>

<div class="gbe-enquiry-form-wrapper">
    <h2>
        <?php echo esc_html( $product ? sprintf( __( 'Enquiry for: %s', 'gbe' ), $product->get_name() ) : __( 'Enquiry Form', 'gbe' ) ); ?>
    </h2>

    <?php if ( $product ) : ?>
        <form method="post" class="gbe-enquiry-form">
            <?php wp_nonce_field( 'gbe_enquiry_form_action', 'gbe_enquiry_nonce' ); ?>
            <input type="hidden" name="product" value="<?php echo esc_attr( $product_id ); ?>">

            <p class="form-row form-row-first">
                <label for="gbe-first-name"><?php esc_html_e( 'First Name', 'gbe' ); ?> <span class="required">*</span></label>
                <input type="text" id="gbe-first-name" name="first_name" value="<?php echo esc_attr( $first_name ); ?>" required>
            </p>

            <p class="form-row form-row-last">
                <label for="gbe-last-name"><?php esc_html_e( 'Last Name', 'gbe' ); ?> <span class="required">*</span></label>
                <input type="text" id="gbe-last-name" name="last_name" value="<?php echo esc_attr( $last_name ); ?>" required>
            </p>

            <p class="form-row form-row-wide">
                <label for="gbe-email"><?php esc_html_e( 'Email', 'gbe' ); ?> <span class="required">*</span></label>
                <input type="email" id="gbe-email" name="email" value="<?php echo esc_attr( $email ); ?>" required>
            </p>

            <p class="form-row form-row-wide">
                <label for="gbe-phone"><?php esc_html_e( 'Phone', 'gbe' ); ?></label>
                <input type="text" id="gbe-phone" name="phone">
            </p>

            <p class="form-row">
                <button type="submit" name="gbe_enquiry_submit" class="button alt">
                    <?php esc_html_e( 'Submit Enquiry', 'gbe' ); ?>
                </button>
            </p>
        </form>
    <?php else : ?>
        <p><?php esc_html_e( 'Invalid product. Please return to shop.', 'gbe' ); ?></p>
    <?php endif; ?>
</div>