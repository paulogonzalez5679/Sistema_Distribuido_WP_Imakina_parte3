<div class="stm_zoom_reports">
    <h1><?php esc_html_e( 'Reports', 'eroom-zoom-meetings-webinar' ); ?></h1>
    <form method="post">
        <input type="number" name="month" max="12" step="1" placeholder="<?php esc_attr_e( 'Enter month e.g. 03' ); ?>"/>
        <input type="number" name="year" max="<?php echo esc_attr( date( 'Y' ) ); ?>" step="1"
               placeholder="<?php esc_attr_e( 'Enter year e.g. 2020' ); ?>" value="<?php echo esc_attr( date( 'Y' ) ); ?>"/>
        <button type="submit" class="button button-primary"><?php esc_html_e( 'Show', 'eroom-zoom-meetings-webinar' ); ?></button>
    </form>
    <?php
    $settings = get_option( 'stm_zoom_settings', array() );
    $api_key = !empty($settings[ 'api_key' ]) ? $settings[ 'api_key' ] : '';
    $api_secret = !empty($settings[ 'api_secret' ]) ? $settings[ 'api_secret' ] : '';
    $year = !empty( $_POST[ 'year' ] ) ? intval( $_POST[ 'year' ] ) : '';
    $month = !empty( $_POST[ 'month' ] ) ? intval( $_POST[ 'month' ] ) : '';
    if( !empty( $api_key ) && !empty( $api_secret ) && !empty( $year ) && !empty( $month ) ):
        $month = sprintf( "%02d", $month );
        $query = array(
            'year' => $year,
            'month' => $month
        );
        $zoom = new \Zoom\Endpoint\Reports( $api_key, $api_secret );
        $reports = $zoom->dailyReports( $query );
        ?>
        <?php if(!empty($reports) && !empty($reports['message'])): ?>
        <div class="stm_zoom_nonce error"><p><?php echo esc_html($reports['message']); ?></p></div>
    <?php endif; ?>
        <?php if( !empty( $reports ) && !empty( $reports[ 'dates' ] ) ): ?>
        <div class="stm_zoom_table-wrap">
            <table class="stm_zoom_table">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'eroom-zoom-meetings-webinar' ); ?></th>
                    <th><?php esc_html_e( 'New Users', 'eroom-zoom-meetings-webinar' ); ?></th>
                    <th><?php esc_html_e( 'Meetings', 'eroom-zoom-meetings-webinar' ); ?></th>
                    <th><?php esc_html_e( 'Participants', 'eroom-zoom-meetings-webinar' ); ?></th>
                    <th><?php esc_html_e( 'Meeting Minutes', 'eroom-zoom-meetings-webinar' ); ?></th>
                </tr>
                </thead>

                <tbody>
                <?php foreach( $reports[ 'dates' ] as $date ): ?>
                    <tr>
                        <td><?php echo esc_html( $date[ 'date' ] ); ?></td>
                        <td><?php echo esc_html( $date[ 'new_users' ] ); ?></td>
                        <td><?php echo esc_html( $date[ 'meetings' ] ); ?></td>
                        <td><?php echo esc_html( $date[ 'participants' ] ); ?></td>
                        <td><?php echo esc_html( $date[ 'meeting_minutes' ] ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php endif; ?>

</div>
