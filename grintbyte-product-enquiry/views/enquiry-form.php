<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Detect product ID dari query var atau $_GET
global $wp_query;

$product_id = 0;
if ( isset( $wp_query->query_vars['product_id'] ) ) {
    $product_id = intval( $wp_query->query_vars['product_id'] );
} elseif ( isset( $_GET['product_id'] ) ) {
    $product_id = intval( $_GET['product_id'] );
}

$product = $product_id ? wc_get_product( $product_id ) : false;

// Prefill user data jika login
$current_user = wp_get_current_user();
$first_name   = $current_user->exists() ? $current_user->user_firstname : '';
$last_name    = $current_user->exists() ? $current_user->user_lastname  : '';
$email        = $current_user->exists() ? $current_user->user_email     : '';

// Action URL not redirect 302
$form_action = esc_url( get_permalink() );
?>

<div class="gbe-enquiry-form-wrapper">
    
    <?php if ( $product ) : ?>
        <form id="gbe-enquiry-form" class="gbe-enquiry-form">
            <input type="hidden" name="action" value="gbe_submit_enquiry" />
            <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
        
            <div class="form-row form-row-wide">
                <label><?php esc_html_e( 'Product Selected', 'gbe' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $product->get_name() ); ?>" disabled>
            </div>

            <?php if ( $product->is_type( 'variable' ) ) : 
                $variations = $product->get_available_variations();
                ?>
                <div class="form-row form-row-wide">
                    <label for="gbe-variation-id"><?php esc_html_e( 'Select Variations', 'gbe' ); ?> <span class="required">*</span></label>
                    <select id="gbe-variation-id" name="variation_id[]" multiple="multiple" required class="gbe-select2">
                        <?php foreach ( $variations as $variation ) : 
                            $variation_obj = wc_get_product( $variation['variation_id'] );
                            $variation_name = wc_get_formatted_variation( $variation_obj, true );
                            ?>
                            <option value="<?php echo esc_attr( $variation['variation_id'] ); ?>">
                                <?php echo esc_html( $variation_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else : ?>
                <input type="hidden" name="variation_id[]" value="0">
            <?php endif; ?>
            
            <div class="form-row-group">
                <div class="form-row form-row-first">
                    <label for="gbe-first-name"><?php esc_html_e( 'First Name', 'gbe' ); ?> <span class="required">*</span></label>
                    <input type="text" id="gbe-first-name" name="first_name" value="<?php echo esc_attr( $first_name ); ?>" required>
                </div>

                <div class="form-row form-row-last">
                    <label for="gbe-last-name"><?php esc_html_e( 'Last Name', 'gbe' ); ?> <span class="required">*</span></label>
                    <input type="text" id="gbe-last-name" name="last_name" value="<?php echo esc_attr( $last_name ); ?>" required>
                </div>
            </div>

            <div class="form-row form-row-wide">
                <label for="gbe-email"><?php esc_html_e( 'Email Address', 'gbe' ); ?> <span class="required">*</span></label>
                <input type="email" id="gbe-email" name="email" value="<?php echo esc_attr( $email ); ?>" required>
            </div>

            <div class="form-row form-row-wide">
                <label for="gbe-phone"><?php esc_html_e( 'Phone Number', 'gbe' ); ?></label>
                <input type="text" id="gbe-phone" name="phone" pattern="[0-9+ ]*" title="Only numbers, spaces, and + allowed">
            </div>

            <div class="form-row form-row-wide">
                <label for="gbe-company"><?php esc_html_e( 'Company', 'gbe' ); ?></label>
                <input type="text" id="gbe-company" name="company">
            </div>

            <div class="form-row form-row-wide">
                <label for="gbe-website"><?php esc_html_e( 'Website', 'gbe' ); ?></label>
                <input type="text" id="gbe-website" name="website">
            </div>

            <div class="form-row form-row-wide">
                <label for="gbe-message"><?php esc_html_e( 'Notes', 'gbe' ); ?></label>
                <textarea id="gbe-message" name="message" rows="4"></textarea>
            </div>

            <div id="gbe-inline-message"></div>

            <p class="form-row">
                <button type="submit" id="gbe-enquiry-submit" class="button alt">
                    <?php esc_html_e( 'Submit Enquiry', 'gbe' ); ?>
                </button>
            </p>
        </form>
    <?php else : ?>
        <p><?php esc_html_e( 'Invalid product. Please return to shop.', 'gbe' ); ?></p>
    <?php endif; ?>

</div>
