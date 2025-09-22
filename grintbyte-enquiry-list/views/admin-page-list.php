<div class="wrap">
    <h1><?php esc_html_e( 'Enquiry List', 'gbe' ); ?></h1>

    <table class="fixed widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Product', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Full Name', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Email', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Phone Number', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Company', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Status', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Notes', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Date', 'gbe' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'gbe' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( ! empty( $results ) ) : ?>
            <?php foreach ( $results as $row ) : ?>
                <tr>
                    <td><?php echo intval( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->product ); ?></td>
                    <td><?php echo esc_html( $row->fullname ); ?></td>
                    <td><?php echo esc_html( $row->email ); ?></td>
                    <td><?php echo esc_html( $row->phone_number ); ?></td>
                    <td><?php echo esc_html( $row->company ); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="enquiry_id" value="<?php echo intval( $row->id ); ?>" />
                            <select name="status">
                                <option value="Received" <?php selected( $row->status, 'Received' ); ?>>Received</option>
                                <option value="Followed-up" <?php selected( $row->status, 'Followed-up' ); ?>>Followed-up</option>
                            </select>
                    </td>
                    <td>
                            <textarea name="notes" rows="2" cols="10"><?php echo esc_textarea( $row->notes ); ?></textarea>
                    </td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                            <button type="submit" name="gbe_update_status" class="button button-primary" style="margin-right:2px;margin-bottom:2px;" >Update</button>
                            <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'delete', 'id' => $row->id ) ) ); ?>" class="button button-secondary">Delete</a>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="9"><?php esc_html_e( 'No enquiries found.', 'gbe' ); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
