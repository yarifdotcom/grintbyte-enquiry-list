<div class="wrap">
    <h1><?php esc_html_e( 'General Enquiry List', 'gen' ); ?></h1>
    <div class="gbe-table-wrapper">
        <?php if ( isset( $_POST['gen_update_status'] ) ) : ?>
            <div class="updated notice">
                <p><?php esc_html_e( 'Enquiry updated successfully.', 'gen' ); ?></p>
            </div>
        <?php endif; ?>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Full Name', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Phone Number', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Company', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Website', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Notes', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'gen' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'gen' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $results ) ) : ?>
                    <?php foreach ( $results as $row ) : ?>
                        <tr>
                            <td><?php echo esc_html( $row->fullname ); ?></td>
                            <td><?php echo esc_html( $row->email ); ?></td>
                            <td><?php echo esc_html( $row->phone_number ); ?></td>
                            <td><?php echo esc_html( $row->company ); ?></td>
                            <td><?php echo esc_html( $row->website ); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="enquiry_id" value="<?php echo intval( $row->id ); ?>" />
                                    <select name="status" style="width:100%">
                                        <option value="Received" <?php selected( $row->status, 'Received' ); ?>>Received</option>
                                        <option value="Followed-up" <?php selected( $row->status, 'Followed-up' ); ?>>Followed-up</option>
                                    </select>
                            </td>
                            <td>
                                    <textarea name="notes" rows="2" cols="10" style="width:100%"><?php echo esc_textarea( $row->notes ); ?></textarea>
                            </td>
                            <td><?php echo esc_html( $row->created_at ); ?></td>
                            <td>
                                    <button type="submit" name="gen_update_status" class="button button-primary" style="margin-right:2px;margin-bottom:2px;" >Update</button>
                                    <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'delete', 'id' => $row->id ) ) ); ?>" class="button button-secondary">Delete</a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9"><?php esc_html_e( 'No enquiries found.', 'gen' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>