<div class="wrap">
    <h1><?php esc_html_e( 'Enquiry Settings', 'gbe' ); ?></h1>
    <?php settings_errors( 'gbe_settings_messages' ); ?>

    <form method="post">
        <?php wp_nonce_field( 'gbe_save_settings_action', 'gbe_save_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="notify_email">Notification Email</label></th>
                <td><input type="email" name="notify_email" value="<?php echo esc_attr( $settings['notify_email'] ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="email_subject">Email Subject</label></th>
                <td><input type="text" name="email_subject" value="<?php echo esc_attr( $settings['email_subject'] ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="email_body">Email Body</label></th>
                <td>
                    <textarea name="email_body" rows="8" class="large-text"><?php echo esc_textarea( $settings['email_body'] ?? '' ); ?></textarea>
                    <p class="description">Available placeholders: {name}, {email}, {phone}, {company}, {message}, {product}, {date}</p>
                </td>
            </tr>
        </table>
        <?php submit_button( 'Save Settings', 'primary', 'gbe_save_settings' ); ?>
    </form>
</div>