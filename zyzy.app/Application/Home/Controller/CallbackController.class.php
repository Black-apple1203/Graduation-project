<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ActionName: 系统第三方回调
// +----------------------------------------------------------------------
namespace Home\Controller;
use Common\Controller\FrontendController;
class CallbackController extends FrontendController{
	/**
     * 第三方帐号登陆和绑定
     * @mod qq sina taobao  weixin
     * @type 操作类型 login bind unbind
     */
    public function index() {
    	$mod = I('get.mod','','trim');
    	$type = I('get.type','login','trim');
    	!$mod && $this->_error('请选择正确的第三方服务！');
        if ('unbind' == $type) {
            !$this->visitor->is_login && $this->redirect('members/login');
            if($mod == 'weixin'){
                if(false === M('MembersBind')->where(array('uid'=>C('visitor.uid'), 'type'=>$mod))->save(array('uid'=>0,'is_bind'=>0,'bindingtime'=>0))) $this->error('解除绑定失败，请重新操作！');
            }else{
                if(false === M('MembersBind')->where(array('uid'=>C('visitor.uid'), 'type'=>$mod))->delete()) $this->error('解除绑定失败，请重新操作！');
            }
            $urls = array('1'=>'company/user_security','2'=>'personal/user_safety');
            $this->redirect($urls[C('visitor.utype')]);
        }else{
            $oauth = new \Common\qscmslib\oauth($mod);
            cookie('callback_type', $type);
            cookie('inviter_type', $keys);
            return $oauth->authorize();
        }
    }
	/**
	 * 第三方登录和绑定回调
	 */
	public function oauth(){
        $mod = I('get.mod','','trim');
        if(I('get.error_uri','','trim') || I('get.error','','trim') || I('get.error_code','','trim')){
            $this->redirect('members/index');
        }
		$mod = I('get.mod','','trim');
        !$mod && $this->error('请选择正确的第三方服务！');
        $callback_type = cookie('callback_type');
        $oauth = new \Common\qscmslib\oauth($mod);
        $rk = $oauth->NeedRequest();
        $request_args = array();
        foreach ($rk as $v) {
            $request_args[$v] =I('get.'.$v);
        }
        switch ($callback_type) {
            case 'login':
                $url = $oauth->callbackLogin($request_args);
                break;
            case 'bind':
                $url = $oauth->callbackbind($request_args);
                break;
            default:
                $url = __ROOT__;
                break;
        }
        cookie('callback_type', null);
        if(false === $url) $this->error($oauth->getError(),'/');
        redirect($url);
	}
    /**
     * [binding 微信绑定]
     */
    public function weixin_bind(){

    }
    /**
     * [weixin_login 微信登录]
     */
    public function weixin_login(){
        $event_key = I('get.event_key',0,'intval');
        $success = 0;
        if(false !== F('/weixin/'.($event_key%10).'/'.$event_key)){
            $where['uid'] = I('get.uid',0,'intval');
            $where['openid'] = I('get.openid','','trim');
            $where['type'] = 'weixin';
            if($user = M('MembersBind')->where($where)->find()){
                $success = 1;
                F('/weixin/'.($event_key%10).'/'.$event_key,$user['uid']);
            }
        }
        $this->assign('success',$success);
        $this->display('wx_scan_success');
    }
	/*
		支付宝回调
		alipay_notify_url alipay_return_url
	*/
	public function alipay_notify_url()
	{	
		$pay = new \Common\qscmslib\pay('alipay');
        $verify_result = $pay->alipayNotify();
        if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$out_trade_no   = I('post.out_trade_no');      //商户订单号
			$trade_no       = I('post.trade_no');          //支付宝交易号
			$trade_status   = I('post.trade_status');      //交易状态
			$total_fee      = I('post.total_fee');         //交易金额
			$notify_id      = I('post.notify_id');         //通知校验ID。
			$notify_time    = I('post.notify_time');       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
			$buyer_email    = I('post.buyer_email');       //买家支付宝帐号；
			if(I('post.trade_status') == 'TRADE_FINISHED' || I('post.trade_status') == 'TRADE_SUCCESS') {
				/*付款后开通相关的 内容*/
                $orderinfo = D('Order')->where(array('oid'=>$out_trade_no))->find();
                if(!$orderinfo){
                    $out_type = explode('-',$out_trade_no);
                    $type =$out_type[1];
                    if($type == 'SHARE'){
                        D('ShareAllowance')->set_share_allowance($out_trade_no);
                    }elseif($type == 'INVITE'){
                        D('InviteAllowance')->set_invite_allowance($out_trade_no);
                    }else{
                        $allowance_info = D('Allowance/AllowanceInfo')->where(array('oid'=>$out_trade_no))->find();
                        if($allowance_info['status']!=1){
                            D('Allowance/AllowanceInfo')->set_allowance_job($out_trade_no);
                        }
                    }
                }else{
                    $order = D('Order')->where(array('oid'=>$out_trade_no))->find();
                    if($order['is_paid']!=2){
                        D('Order')->order_paid($out_trade_no,strtotime($notify_time));
                    }          
                }
			}
	        echo "success";
	    }
	    else 
	    {
            //验证失败
            echo "fail";
	    }
	}
	public function alipay_return_url()
	{
        $pay = new \Common\qscmslib\pay('alipay');
        $verify_result = $pay->alipayNotifyReturn();
        $exterface = I('request.exterface','','trim');
		if($verify_result) {
			//验证成功
			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$out_trade_no   = I('get.out_trade_no');      //商户订单号
			$trade_no       = I('get.trade_no');          //支付宝交易号
			$trade_status   = I('get.trade_status');      //交易状态
			$total_fee      = I('get.total_fee');         //交易金额
			$notify_id      = I('get.notify_id');         //通知校验ID。
			$notify_time    = I('get.notify_time');       //通知的发送时间。
			$buyer_email    = I('get.buyer_email');       //买家支付宝帐号；
			if(I('get.trade_status') == 'TRADE_FINISHED' || I('get.trade_status') == 'TRADE_SUCCESS') 
			{	
                /*付款后开通相关的 内容*/
                $orderinfo = D('Order')->where(array('oid'=>$out_trade_no))->find();
                if(!$orderinfo){
                    $out_type = explode('-',$out_trade_no);
                    $type = $out_type[1];
                    if($type == 'SHARE'){
                        D('ShareAllowance')->set_share_allowance($out_trade_no);
                        if($exterface=='create_direct_pay_by_user'){
                            redirect(U('Home/CompanyService/share_allowance_list'));
                        }else{
                            redirect(U('Mobile/CompanyService/share_allowance_list'));
                        }
                    }elseif($type == 'INVITE'){
                        D('InviteAllowance')->set_invite_allowance($out_trade_no);
                        if($exterface=='create_direct_pay_by_user'){
                            redirect(U('Home/CompanyService/invite_allowance_list'));
                        }else{
                            redirect(U('Mobile/CompanyService/invite_allowance_list'));
                        }
                    }else{
                        $allowance_info = D('Allowance/AllowanceInfo')->where(array('oid'=>$out_trade_no))->find();
                        if($allowance_info['status']==0){
                            D('Allowance/AllowanceInfo')->set_allowance_job($out_trade_no);
                        }
                        if($exterface=='create_direct_pay_by_user'){
                            redirect(U('Home/Company/jobs_list'));
                        }else{
                            redirect(U('Mobile/Company/jobs_list'));
                        }
                    }
                }else{			
                    $order = D('Order')->where(array('oid'=>$out_trade_no))->find();
                    if($order['is_paid']==1){
        				D('Order')->order_paid($out_trade_no,strtotime($notify_time));
                    }
                    if(C('visitor.utype')==1){
                        $order['params'] = unserialize($order['params']);
                        if(!$order['params']['type']){
                            if($exterface=='create_direct_pay_by_user'){
                                redirect(U('CompanyService/order_detail',array('id'=>$order['id'])));
                            }else{
                                redirect(build_mobile_url(array('c'=>'CompanyService','a'=>'order_detail','params'=>'order_id='.$order['id'])));
                            }
                        }elseif($order['params']['type'] == 'jobs_refresh'){
                            redirect(url_rewrite('QS_jobsshow',array('id'=>$order['params']['jobs_id'][0])));
                        }elseif($order['params']['type'] == 'resume_download'){
                            redirect(url_rewrite('QS_resumeshow',array('id'=>$order['params']['resume_id'][0])));
                        }
                    }else{
                        if($exterface=='create_direct_pay_by_user'){
                            redirect(U('PersonalService/order_detail',array('id'=>$order['id'])));
                        }else{
                            redirect(build_mobile_url(array('c'=>'PersonalService','a'=>'order_detail','params'=>'order_id='.$order['id'])));
                        }
                    }
                }
				// === $this->redirect();//跳转到配置项中配置的支付成功页面；
			}else {
				echo "trade_status=".I('get.trade_status');
				// === $this->redirect();//跳转到配置项中配置的支付失败页面；
			}
		}
		else 
		{
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
            $order = D('Order')->where(array('oid'=>I('get.out_trade_no')))->find();
            if(!$order){
                redirect(U('Home/Company/jobs_list'));
            }else{
                if(C('visitor.utype')==1){
                    $order['params'] = unserialize($order['params']);
                    if(!$order['params']['type']){
                        if($exterface=='create_direct_pay_by_user'){
                            redirect(U('CompanyService/order_detail',array('id'=>$order['id'])));
                        }else{
                            redirect(build_mobile_url(array('c'=>'CompanyService','a'=>'order_detail','params'=>'order_id='.$order['id'])));
                        }
                    }elseif($order['params']['type'] == 'jobs_refresh'){
                        redirect(url_rewrite('QS_jobsshow',array('id'=>$order['params']['jobs_id'][0])));
                    }elseif($order['params']['type'] == 'resume_download'){
                        redirect(url_rewrite('QS_resumeshow',array('id'=>$order['params']['resume_id'][0])));
                    }
                }else{
                    if($exterface=='create_direct_pay_by_user'){
                        redirect(U('PersonalService/order_detail',array('id'=>$order['id'])));
                    }else{
                         redirect(build_mobile_url(array('c'=>'PersonalService','a'=>'order_detail','params'=>'order_id='.$order['id'])));
                    }
                }
            }
			// === $this->redirect();//跳转到配置项中配置的支付失败页面；
		}
	}
    /**
     * [alipay_notify_url_app 支付宝APP支付异步回调]
     */
    public function alipay_notify_url_app(){
        $pay_type = D('Payment')->get_cache();
        $setting = $pay_type['alipay'];
        $aop = new \Common\qscmslib\pay\alipay\AppPay\aop\AopClient();
        $aop->alipayrsaPublicKey = $setting['alipayrsaPublicKey'];
        if ($flag = $aop->rsaCheckV1($_POST, NULL, "RSA2")){
            $out_trade_no = I('post.out_trade_no','','trim');
            /*付款后开通相关的 内容*/
            $orderinfo = D('Order')->where(array('oid'=>$out_trade_no))->find();
            if(!$orderinfo){
                $out_type = explode('-',$out_trade_no);
                $type =$out_type[1];
                if($type == 'SHARE'){
                    D('ShareAllowance')->set_share_allowance($out_trade_no);
                }elseif($type == 'INVITE'){
                    D('InviteAllowance')->set_invite_allowance($out_trade_no);
                }else{
                    $allowance_info = D('Allowance/AllowanceInfo')->where(array('oid'=>$out_trade_no))->find();
                    if($allowance_info['status']!=1){
                        D('Allowance/AllowanceInfo')->set_allowance_job($out_trade_no);
                    }
                }
            }else{
                if($orderinfo['is_paid']!=2){
                    $notify_time = I('post.notify_time','','trim');
                    D('Order')->order_paid($out_trade_no,strtotime($notify_time));
                }          
            }
        }
    }
	/**
	 * [wxpay 微信支付回调]
	 * @return [type] [description]
	 */
	public function wxpay(){
        $pay_resource = I('get.pay_resource','','trim');
		Vendor('WxPayPubHelper.WxPayPubHelper');
		//使用通用通知接口
        $pay_type = D('Payment')->get_cache();
        $w_data = $pay_type['wxpay'];
        $notify = new \Notify_pub($w_data);
         
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
         
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
         
        //以log文件形式记录回调信息
        //         $log_ = new Log_();
        //$log_name= __ROOT__."/Public/notify_url.log";//log文件路径
         
        //$this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
         
        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                //log_result($log_name,"【通信出错】:\n".$xml."\n");
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                //log_result($log_name,"【业务出错】:\n".$xml."\n");
            }
            else{
                //此处应该更新一下订单状态，商户自行增删操作
                //log_result($log_name,"【支付成功】:\n".$xml."\n");
                $out_trade_no = $notify->data['out_trade_no'];
                if($pay_resource=='allowance'){
                    D('Allowance/AllowanceInfo')->set_allowance_job($out_trade_no);
                }elseif($pay_resource=='share_allowance'){
                    D('ShareAllowance')->set_share_allowance($out_trade_no);
                }else{
                    D('Order')->order_paid($out_trade_no,time());
                }
                @unlink(QSCMS_DATA_PATH.'wxpay/'.$out_trade_no.'.tmp');
                return true;
            }
            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
        }
	}
}
?>