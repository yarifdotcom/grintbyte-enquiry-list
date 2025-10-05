<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e( 'General Enquiry Settings', 'gen' ); ?></h1>

    <?php settings_errors( 'gen_settings_messages' ); ?>

    <form method="post">
        <?php wp_nonce_field( 'gen_save_settings_action', 'gen_save_settings_nonce' ); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="notify_email"><?php esc_html_e( 'Notification Email', 'gen' ); ?></label>
                    </th>
                    <td>
                        <input type="email"
                               name="notify_email"
                               id="notify_email"
                               value="<?php echo esc_attr( $settings['notify_email'] ?? '' ); ?>"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e( 'Email address that will receive new enquiry notifications.', 'gen' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="email_subject"><?php esc_html_e( 'Email Subject', 'gen' ); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               name="email_subject"
                               id="email_subject"
                               value="<?php echo esc_attr( $settings['email_subject'] ?? '' ); ?>"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e( 'Subject line for the enquiry notification email.', 'gen' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="email_body"><?php esc_html_e( 'Email Body', 'gen' ); ?></label>
                    </th>
                    <td>
                        <textarea name="email_body"
                                  id="email_body"
                                  rows="8"
                                  class="large-text code"><?php echo esc_textarea( $settings['email_body'] ?? '' ); ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Available placeholders you can use in the email body:', 'gen' ); ?><br>
                            <code>{name}</code>,
                            <code>{email}</code>,
                            <code>{phone}</code>,
                            <code>{company}</code>,
                            <code>{website}</code>,
                            <code>{message}</code>,
                            <code>{date}</code>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( __( 'Save Settings', 'gen' ), 'primary', 'gen_save_settings' ); ?>
    </form>
</div>
