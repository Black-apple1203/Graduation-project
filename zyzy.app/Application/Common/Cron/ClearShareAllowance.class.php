<?php
/*
* 74cms 计划任务 到期红包任务,微信通知
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
class ClearShareAllowance{
	public function run(){
		if(false === $apply = F('apply_list')) $apply = D('Apply')->apply_cache();
		if(!$apply['Weixin']) return;
		if(false === $config = F('config')) $config = D('Config')->config_cache();
        C($config);
        $where = array('deadline'=>array('elt',time()),'status'=>0);
		if($partake = M('ShareAllowancePartake')->field('id,uid,jobs_name')->where($where)->select()){
            foreach($partake as $val){
            	D('WeixinTplMsg')->set_share_allowance_deadline($val['uid'], $val['jobs_name']);
            }
            M('ShareAllowancePartake')->where($where)->setfield('status',2);
		}
	}
}
?>