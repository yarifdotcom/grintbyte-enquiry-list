<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prefill user data if login
$current_user = wp_get_current_user();
$fullname     = $current_user->exists() ? trim( $current_user->user_firstname . ' ' . $current_user->user_lastname ) : '';
$email        = $current_user->exists() ? $current_user->user_email : '';
?>

<div class="gen-enquiry-form-wrapper">

    <form id="gen-enquiry-form" class="gen-enquiry-form" method="post">
        <input type="hidden" name="action" value="gen_submit_enquiry" />
        <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'gen_enquiry_nonce' ) ); ?>">

        <div class="form-row form-row-wide">
            <label for="gen-fullname"><?php esc_html_e( 'Full Name', 'gen' ); ?> <span class="required">*</span></label>
            <input type="text" id="gen-fullname" name="fullname" value="<?php echo esc_attr( $fullname ); ?>" required>
        </div>

        <div class="form-row form-row-wide">
            <label for="gen-email"><?php esc_html_e( 'Email Address', 'gen' ); ?> <span class="required">*</span></label>
            <input type="email" id="gen-email" name="email" value="<?php echo esc_attr( $email ); ?>" required>
        </div>

        <div class="form-row form-row-wide">
            <label for="gen-phone"><?php esc_html_e( 'Phone Number', 'gen' ); ?></label>
            <input type="text" id="gen-phone" name="phone" pattern="[0-9+ ]*" title="<?php esc_attr_e( 'Only numbers, spaces, and + allowed', 'gen' ); ?>">
        </div>

        <div class="form-row form-row-wide">
            <label for="gen-company"><?php esc_html_e( 'Company', 'gen' ); ?></label>
            <input type="text" id="gen-company" name="company">
        </div>

        <div class="form-row form-row-wide">
            <label for="gen-website"><?php esc_html_e( 'Website', 'gen' ); ?></label>
            <input type="text" id="gen-website" name="website">
        </div>

        <div class="form-row form-row-wide">
            <label for="gen-message"><?php esc_html_e( 'Notes', 'gen' ); ?> <span class="required">*</span></label>
            <textarea id="gen-message" name="message" rows="5" required></textarea>
        </div>

        <div id="gen-inline-message"></div>

        <p class="form-row">
            <button type="submit" id="gen-enquiry-submit" class="button alt">
                <?php esc_html_e( 'Send Enquiry', 'gen' ); ?>
            </button>
        </p>
    </form>
</div>
