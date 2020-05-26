<?php
/*
* 74cms 计划任务 限时推广清理
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
class ClearExpiredSetmeal{
	public function run(){
		$map['endtime'] = array(array('elt',time()),array('neq',0));
		$map['setmeal_id'] = array('gt',1);
		$list = M('MembersSetmeal')->where($map)->select();
		foreach ($list as $key => $value) {
			D('MembersSetmeal')->set_members_setmeal($value['uid'],1);
		}
	}
}
?>