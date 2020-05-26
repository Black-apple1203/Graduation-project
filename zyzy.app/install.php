<?php
// 应用入口文件
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('QSCMS_PRE', 'qs_');
define('MAIN_PROJECT_PATH','./'); 
define('APP_PATH', './install/');
define('QSCMS_DATA_PATH', APP_PATH.'Data/');
define('APP_DEBUG', true);
define('QISHI_CHARSET','utf8');
define('SETUP_STATUS',true);
require './ThinkPHP/ThinkPHP.php';