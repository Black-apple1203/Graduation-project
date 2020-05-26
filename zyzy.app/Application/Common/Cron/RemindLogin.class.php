<?php
/*
* 74cms 计划任务 用户未登录邮件提醒
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
class RemindLogin{
	public function run(){
		$site_domain = '';
		if(false === $config = F('config')){
          $config =  D('Config')->config_cache();
        }
		if($config['qscms_per_unlogin_remind_open']==1){
			$time = time();
			$user_unlogin_time = $config['qscms_per_unlogin_remind_cycle']?$config['qscms_per_unlogin_remind_cycle']:30;
			$last_time = strtotime("-".$user_unlogin_time." day");
			$result = D('Members')->where(array('last_login_time'=>array('lt',$last_time),'remind_sms_time'=>array('lt',$time),'remind_sms_ex_time'=>array('lt',$config['qscms_per_unlogin_remind_time'])))->order('uid desc')->limit(10)->select();
			foreach ($result as $key => $row) {
				if(!$row['mobile']){
					continue;
				}
				if($row['utype']==1){
					$sendSms['tpl']='set_com_remind';
				}
				if($row['utype']==2){
					$sendSms['tpl']='set_per_remind';
					$sendSms['data']=array('username'=>$row['username'].'');
				}
                $sendSms['mobile']=$row['mobile'];
                if(true !== $reg = D('Sms')->sendSms('notice',$sendSms)) $this->ajaxReturn(0,$reg);

				//更新短信提醒时间
				$remind_sms_time = strtotime("+".$user_unlogin_time." day");
				$remind_sms_ex_time = intval($row['remind_sms_ex_time']) + 1;
				$data = array('remind_sms_time'=>$remind_sms_time,'remind_sms_ex_time'=>$remind_sms_ex_time);
				D('Members')->where(array('uid'=>$row['uid']))->setField($data);

			}
		}
			
	}
}
?>