<div class="wrap">
    <h1><?php esc_html_e( 'Product Enquiry List', 'gbe' ); ?></h1>
    <div class="gbe-table-wrapper">
        <?php if ( isset( $_POST['gbe_update_status'] ) ) : ?>
            <div class="updated notice">
                <p><?php esc_html_e( 'Enquiry updated successfully.', 'gen' ); ?></p>
            </div>
        <?php endif; ?>
        <table class="fixed widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Products', 'gbe' ); ?></th>
                    <th style="width:28px"></th>
                    <th><?php esc_html_e( 'Full Name', 'gbe' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'gbe' ); ?></th>
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
                        <td><?php echo esc_html( $row->products ); ?></td>
                        <td>  
                            <a href="#" 
                                class="gbe-preview-link" 
                                data-id="<?php echo intval($row->id); ?>" 
                                title="<?php esc_attr_e( 'Preview Enquiry', 'gbe' ); ?>">
                                <?php _e( 'Detail', 'gbe' ); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html( $row->fullname ); ?></td>
                        <td><?php echo esc_html( $row->email ); ?></td>
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
                                <button type="submit" name="gbe_update_status" class="button button-primary" style="margin-right:2px;margin-bottom:2px;" >Update</button>
                                <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'delete', 'id' => $row->id ) ) ); ?>" class="button button-secondary">Delete</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="11"><?php esc_html_e( 'No enquiries found.', 'gbe' ); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal container -->
    <div id="gbe-preview-modal"></div>

</div>