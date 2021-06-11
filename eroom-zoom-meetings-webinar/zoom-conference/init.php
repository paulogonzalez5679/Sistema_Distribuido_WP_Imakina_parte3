<?php
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoom.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomAdminMenus.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomAdminNotices.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomPostTypes.php';

// Create objects
new StmZoom;
new StmZoomAdminMenus;
new StmZoomAdminNotices;
new StmZoomPostTypes;
