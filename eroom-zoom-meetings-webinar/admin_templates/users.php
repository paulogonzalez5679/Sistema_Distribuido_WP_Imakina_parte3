<?php
if ( ! empty( $_GET[ 'delete_zoom_users_cache' ] ) ) {
    delete_transient('stm_zoom_users');
}
$users = StmZoom::stm_zoom_get_users();
?>
<div class="report_wrap">
    <h1><?php esc_html_e( 'Users', 'eroom-zoom-meetings-webinar' ); ?></h1>
    <div>
        <a href="<?php echo add_query_arg(array('delete_zoom_users_cache' => '1')); ?>"><?php esc_html_e('Delete cache', 'eroom-zoom-meetings-webinar'); ?></a>
    </div>
    <div class="stm_zoom_table-wrap">
        <table class="stm_zoom_table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Host ID', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Email', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Name', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Last name', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Created on', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Last login', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Last Client', 'eroom-zoom-meetings-webinar' ); ?></th>
                <th><?php esc_html_e( 'Status', 'eroom-zoom-meetings-webinar' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if( !empty( $users ) && is_array($users) ): ?>
                <?php foreach( $users as $user ): ?>
                    <?php
                    $id = !empty($user['id']) ? $user['id'] : '';
                    $email = !empty($user['email']) ? $user['email'] : '';
                    $first_name = !empty($user['first_name']) ? $user['first_name'] : '';
                    $last_name = !empty($user['last_name']) ? $user['last_name'] : '';
                    $created_at = !empty($user['created_at']) ? $user['created_at'] : '';
                    $last_login_time = !empty($user['last_login_time']) ? $user['last_login_time'] : '';
                    $last_client_version = !empty($user['last_client_version']) ? $user['last_client_version'] : '';
                    $status = !empty($user['status']) ? $user['status'] : '';
                    ?>
                    <tr>
                        <td><?php echo esc_html($id); ?></td>
                        <td><?php echo esc_html($email); ?></td>
                        <td><?php echo esc_html($first_name); ?></td>
                        <td><?php echo esc_html($last_name); ?></td>
                        <td><?php echo esc_html($created_at); ?></td>
                        <td><?php echo esc_html($last_login_time); ?></td>
                        <td><?php echo esc_html($last_client_version); ?></td>
                        <td><?php echo esc_html($status); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
