<?php
// 行为插件
return array(
/**
+------------------------------------------------------------------------------
| 系统标签
+------------------------------------------------------------------------------
*/
		'app_begin' => array(
			'Common\Behavior\IsSslBehavior',//是否是ssl
			'Common\Behavior\CheckIpbanBehavior', //禁止IP
			'Common\Behavior\CheckLangBehavior', //语言
			'Common\Behavior\CronRunBehavior',//定时任务
			'Common\Behavior\IsMobileBehavior',
		),
		'view_filter' => array(
			'Common\Behavior\ContentReplaceBehavior', //路径替换
			'Behavior\TokenBuildBehavior',
		)
);