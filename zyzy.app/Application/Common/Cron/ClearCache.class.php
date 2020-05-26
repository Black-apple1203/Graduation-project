<?php
/*
* 74cms 计划任务 每周清除缓存文件
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
defined('THINK_PATH') or exit();
ignore_user_abort(true);
class ClearCache{
	public function run(){
		rmdirs(RUNTIME_PATH);
        rmdirs(QSCMS_DATA_PATH.'static');
        rmdirs(STATISTICS_PATH);
        rmdirs(QSCMS_DATA_PATH.'wxpay');
	}
}
?>