<?php
/*
* 74cms 计划任务 每天处理未支付订单
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
class AutoSubscriptionPushBaidu{
	public function run(){
		$info = D('BaiduSubscribe')->select();
		foreach ($info as $key => $value) {
				$template = array(
					"touser_openId"=> $value['open_id'], 
					"page"=> $value['url'],
					'scene_id' => $value['scene_id'],
					'scene_type' => 1,
					'data' => json_encode(array(
						'keyword1' =>array('value' =>$value['intention_jobs'],
							),
						'keyword2' => array('value' =>'这是您本周的订阅职位',
							),
					)),

					'template_id' =>C('qscms_baidu_template_id')
				);
				
				\Common\qscmslib\weixin::build_tpl_baidusubmsg(http_build_query($template));
		}
	}

}
?>