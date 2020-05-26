<?php
namespace Common\Model;
use Think\Model;
class WeixinTplMsgModel extends Model{
	/**
	 * 读取系统参数生成缓存文件
	 */
	public function config_cache() {
		$config = array();
		$res = $this->where()->getField('alias,value,template_id');
		foreach ($res as $key=>$val) {
			$un_result=unserialize($val);
			$config[$key] = $un_result ? $un_result : $val;
		}
		F('weixin_tpl_msg', $config);
		return $config;
	}
	/**
	 * [get_cache 读取微信配置]
	 */
	public function get_cache(){
		if(false === $weixin_config = F('weixin_tpl_msg')) $weixin_config = $this->config_cache();
		return $weixin_config;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('weixin_tpl_msg', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('weixin_tpl_msg', NULL);
    }

	/*
	 * 微信提醒：邀请注册红包
	 * $uid    					会员uid 
	 * $service 				服务名称
	 * $charge 				    手续费
	*/
	public function set_invite_allowance($uid,$service,$charge,$amount)
	{
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_invite_allowance']['value']=='1')
		{
			$user = D('Common/MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid'])
			{
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_invite_allowance']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您所邀请的会员已满足条件，红包已发放到您的零钱中，请注意查收\\n"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode($service),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("红包已发放"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("本次服务收取手续费".$charge."元，实际发放".$amount."元；微信到账时间可能有延迟，如果【48】小时后仍未收到，请及时联系网站客服！"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：加入黑名单 
	 * $uid    					邀请者会员uid 
	*/
	public function set_invite_blacklist($uid)
	{
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_invite_blacklist']['value']=='1')
		{
			$user = D('Common/MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid'])
			{
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_invite_blacklist']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("抱歉，您已被网站管理员限制使用邀请红包功能！\\n"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("邀请红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("禁止使用"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("\\n系统监测到您近期操作异常，如了解详细情况请联系网站客服。"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：企业开启分享红包
	*/
	public function set_share_allowance_publish($uid,$jobs_name,$amount,$count){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_publish']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_publish']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您的职位【{$jobs_name}】分享红包任务成功开启。"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("任务开始"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("您当前职位分享红包任务成功开启，{$amount}元/个，共有{$count}个。"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：推广人完成分享任务并领取红包(个人)
	*/
	public function set_share_allowance_pay_personal($uid,$jobs_name,$charge,$amount){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_pay_personal']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_pay_personal']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您已成功完成【{$jobs_name}】的分享任务，红包已发放到您的零钱中，请注意查收。"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("红包已发放"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("本次服务收取手续费{$charge}元，实际发放{$amount}元；微信到账时间可能有延迟，如果【48】小时后仍未收到，请及时联系网站客服！"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：推广人完成分享任务并领取红包(企业)
	*/
	public function set_share_allowance_pay_company($uid,$jobs_name,$username,$surplus){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_pay_company']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_pay_company']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您发布的职位【{$jobs_name}】分享红包已被【{$username}】成功领取。"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("红包已发放"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("您当前职位剩余 {$surplus} 个红包，感谢您使用红包打赏服务！"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：任务红包全部发放完毕，信息汇总
	*/
	public function set_share_allowance_end($uid,$jobs_name,$complete,$share){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_pay_company']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_pay_company']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您发布的职位【{$jobs_name}】的分享红包已全部发放。"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("红包已发放完毕"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("您当前职位分享红包，{$complete} 人完成分享任务，{$share} 人参与分享活动，感谢您使用红包打赏服务！"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：推广人加入黑名单或任务停止
	*/
	public function set_share_allowance_blacklist($uid){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_blacklist']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_blacklist']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("抱歉，您已被网站管理员限制使用职位分享红包功能！\\n"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("禁止使用"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("系统监测到您近期操作异常，如了解详细情况请联系网站客服。"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
	/*
	 * 微信提醒：推广任务超时，结束单次推广
	*/
	public function set_share_allowance_deadline($uid,$jobs_name){
		$this->wxconfig = $this->get_cache();
		if(C('qscms_weixin_apiopen')==1 && $this->wxconfig['set_share_allowance_deadline']['value']=='1'){
			$user = D('MembersBind')->get_members_bind(array('uid'=>$uid,'type'=>'weixin'));
			if($user['openid']){
				$template = array(
					'touser' => $user['openid'],
					'template_id' => $this->wxconfig['set_share_allowance_deadline']['template_id'],
					'topcolor' => "#7B68EE",
					'data' => array(
						'first' => array('value' => urlencode("您的职位【{$jobs_name}】分享任务已结束。"),
										'color' => "#743A3A",
							),
						'keyword1' => array('value' => urlencode("职位分享红包"),
										'color' => "#743A3A",
							),
						'keyword2' => array('value' => urlencode("任务结束"),
										'color' => "#743A3A",
							),
						'remark' => array('value' => urlencode("您当前职位分享红包任务未在指定时间内完成分享，系统已结束此次任务。"),
										'color' => "#743A3A",
							)
						)
					);
				\Common\qscmslib\weixin::build_tpl_msg(urldecode(json_encode($template)));
			}
		}
	}
}
?>