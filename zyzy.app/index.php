<?php
// 应用入口文件 
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
if (!is_file('./data/install.lock')) {
    header('Location: ./install.php');
    exit;
}
define('QSCMS_PRE', 'qs_');
// 定义应用名称
define('APP_NAME','Application');
// 定义应用目录
define('APP_PATH','./Application/');
/* 数据目录*/
define('QSCMS_DATA_PATH', './data/');
/*项目自定义类库*/
define('QSCMSLIB_PATH', APP_PATH . 'Common/qscmslib/');
define('QSCMS_APPAPI_PATH', APP_PATH . 'Appapi/Install/');
/* 数据库备份存放目录*/
define('DATABASE_BACKUP_PATH', QSCMS_DATA_PATH . 'backup/database/');
/* 模板备份存放目录*/
define('TPL_BACKUP_PATH', QSCMS_DATA_PATH . 'backup/tpl/');
/* 缓存目录*/
define('RUNTIME_PATH', QSCMS_DATA_PATH . 'Runtime/');
/* SESSION目录*/
define('SESSION_PATH', realpath(rtrim(dirname(__FILE__), '/\\') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'session'));
/* COOKIE目录*/
define('COOKIE_SAVE_PATH', QSCMS_DATA_PATH . 'cookie/');
/* HTML静态文件目录*/
define('HTML_PATH', QSCMS_DATA_PATH . 'html/');
/* 伪静态文件目录*/
define('REWRITE_PATH', QSCMS_DATA_PATH . 'rewrite/');
/* 计划任务日志目录*/
define('CRON_LOG_PATH', QSCMS_DATA_PATH . 'cron_log/');
/* SQL逾时日志目录*/
define('SQL_LOG_PATH', QSCMS_DATA_PATH . 'sqllog/');
/* 招聘效果统计数据缓存目录*/
define('STATISTICS_PATH', QSCMS_DATA_PATH . 'statistics/');
/*MOBILE配置文件路径*/
define('MOBILE_CONFIG_PATH', APP_PATH . 'Mobile/Conf/');
/*home配置文件路径*/
define('HOME_CONFIG_PATH', APP_PATH . 'Home/Conf/');
define('ONLINE_UPDATER_PATH', QSCMS_DATA_PATH . 'online_updater');
define('ONLINE_ROLLBACK_PATH', QSCMS_DATA_PATH . 'online_rollback');
define('APP_SPELL',true);
define('APP_DEVELOPER',false);//如对系统构架未做深入了解，请务开启此项，已免产生不可回逆的系统错误！
define('APP_DEBUG', true);// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_UPDATER',true);// 开启在线升级 系统经过二次开发不建议开启此功能，升级会覆盖原有文件。
define('QISHI_CHARSET','utf8');
require './ThinkPHP/ThinkPHP.php';