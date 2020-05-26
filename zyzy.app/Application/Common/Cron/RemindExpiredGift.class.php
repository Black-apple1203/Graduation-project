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
class RemindExpiredGift{
	public function run(){
		if(C('qscms_open_give_gift')==1){			
			$day = C('qscms_gift_min_remind');
			$starttime = time()+($day-1)*24*60*60;
			$endtime = time()+$day*24*60*60;
			$map['deadtime'] = array('between',$starttime.','.$endtime);
			$map['is_used'] = 2;
			$list = M('GiftIssue')->where($map)->group("uid")->select();
			foreach ($list as $key => $value) {
				$uid=$value['uid'];				
				$user_info = D('Members')->find($uid);
				//站内信
				$setsqlarr_pms['message'] = "您有套餐优惠券即将过期了，请及时使用哦！";
				D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $setsqlarr_pms['message'],1);
				//sms
				$sms = D('SmsConfig')->get_cache();
				if ($sms['set_gift'] == 1) {
					$send_sms = true;
					if (C('qscms_company_sms') == 1) {
						if ($user_info['sms_num'] == 0) {
							$send_sms = false;
						}
					}
					if ($send_sms == true) {
						$r = D('Sms')->sendSms('notice', array('mobile' => $user_info['mobile'], 'tpl' => 'set_gift_expire_remind', 'data' => array('username' => $user_info['username'])));
						if ($r === true) {
							D('Members')->where(array('uid' => $uid))->setDec('sms_num');
						}
					}
				}
				//微信
				$map['uid'] = $value['uid'];			
				$remind = M('GiftIssue')->where($map)->order('deadtime asc')->find();
				$user = D('MembersBind')->get_members_bind(array('uid'=>$map['uid'],'type'=>'weixin'));
				if (false === $module_list = F('apply_list')) $module_list = D('Apply')->apply_cache();
				if ($module_list['Weixin']) {
					D('Weixin/TplMsg')->set_gift_expire_remind($uid, $remind['issue_num'], '套餐优惠券', date('Y-m-d H:i',$remind['deadtime']));
				}
			}	
		}
	}
}
?>