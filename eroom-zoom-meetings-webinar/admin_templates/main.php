<?php
if(!defined('STM_ZOOM_PRO_PATH')){
    include STM_ZOOM_PATH . '/admin_templates/notices/pro_popup.php';
    include STM_ZOOM_PATH . '/admin_templates/notices/pro_notice.php';
}

if(!empty($_GET['page']) && $_GET['page'] === 'stm_zoom_users') {
    require_once STM_ZOOM_PATH . '/admin_templates/users.php';
}
else if(!empty($_GET['page']) && $_GET['page'] === 'stm_zoom_add_user') {
    require_once STM_ZOOM_PATH . '/admin_templates/add_users.php';

}
else if(!empty($_GET['page']) && $_GET['page'] === 'stm_zoom_reports') {
    require_once STM_ZOOM_PATH . '/admin_templates/reports.php';
}
else if(!empty($_GET['page']) && $_GET['page'] === 'stm_zoom_assign_host_id') {
    require_once STM_ZOOM_PATH . '/admin_templates/assign_host.php';
}