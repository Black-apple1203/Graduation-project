<?php
namespace Home\Controller;
use Home\Controller\CompanyController;
class AccountMigrationController extends CompanyController{
	public function _initialize(){
        parent::_initialize();
    }
	public function index(){
        if (!$this->cominfo_flge) {
            if (IS_AJAX) {
                $this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
            } else {
                $this->error('为了达到更好的招聘效果，请先完善您的企业资料！', U('company/com_info'));
            }
        }
		$this->_config_seo(array('title'=>'账号迁移 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
	}
	/**
	 * 验证手机号是否存在企业
	 */
	public function check_mobile(){
		session('migration_tmp_mobile',null);
		$mobile = I('post.mobile','','trim');
		!fieldRegex($mobile, 'mobile') && $this->ajaxReturn(0, '手机号格式错误！');
		session('migration_tmp_mobile',$mobile);
		$userinfo = D('Members')->where(array('mobile'=>array('eq',$mobile)))->find();
		if(!$userinfo){
			$this->ajaxReturn(1, '验证通过',U('auth_mobile'));
		}
		$companyinfo = D('CompanyProfile')->where(array('uid'=>$userinfo['uid']))->find();
		if(!$companyinfo){
			$this->ajaxReturn(1, '验证通过',U('auth_mobile'));
		}else{
			$this->ajaxReturn(0, '验证未通过',U('check_mobile_fail'));
		}
	}
	/**
	 * 验证手机号失败
	 */
	public function check_mobile_fail(){
		if(!session('migration_tmp_mobile')){
			$this->redirect('index');die;
		}
    	$this->assign('migration_tmp_mobile',session('migration_tmp_mobile'));
		session('migration_tmp_mobile',null);
		$this->_config_seo(array('title'=>'账号迁移 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
	}
	/**
	 * 获取验证码，验证手机号
	 */
	public function auth_mobile(){
		if(!session('migration_tmp_mobile')){
			$this->redirect('index');die;
		}
		$this->assign('migration_tmp_mobile',session('migration_tmp_mobile'));
		$this->_config_seo(array('title'=>'账号迁移 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
	}
    /**
     * 发送手机验证码]
     */
    public function send_mobile_code() {
        $mobile = I('post.mobile', '', 'trim,badword');
        if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机格式错误!');
        $userinfo = M('Members')->field('uid,mobile')->where(array('mobile' => $mobile))->find();
        if($userinfo){
			$companyinfo = D('CompanyProfile')->where(array('uid'=>$userinfo['uid']))->find();
			if($companyinfo){
				$this->ajaxReturn(0, '该账号已存在企业信息!');
			}
		}
        if (session('verify_mobile.time') && (time() - session('verify_mobile.time')) < 60) $this->ajaxReturn(0, '请60秒后再进行验证！');
        $rand = getmobilecode();
        $sendSms = array('mobile' => $mobile, 'tpl' => 'set_mobile_verify', 'data' => array('rand' => $rand . '', 'sitename' => C('qscms_site_name')));
        if (true === $reg = D('Sms')->sendSms('captcha', $sendSms)) {
            session('verify_mobile',array('mobile'=>$mobile,'rand'=>substr(md5($rand), 8,16),'time'=>time()));
            session('_verify_num_check',null);
            $this->ajaxReturn(1, '验证码发送成功！');
        } else {
            $this->ajaxReturn(0, $reg);
        }
    }
    /**
     * 执行转移
     */
    public function do_migration(){
    	$receive_mobile = session('migration_tmp_mobile');
    	if(!$receive_mobile){
    		$this->ajaxReturn(0,'接收手机号错误');
    	}
    	$verify = session('verify_mobile');
        if(true !== $tip = verify_mobile($verify['mobile'],$verify,I('post.verifycode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
        $userinfo = M('Members')->field('uid,mobile')->where(array('mobile' => $receive_mobile))->find();
        $to_uid = 0;
        if($userinfo){
			$companyinfo = D('CompanyProfile')->where(array('uid'=>$userinfo['uid']))->find();
			if($companyinfo){
				$this->ajaxReturn(0, '该账号已存在企业信息!');
			}
			$to_uid = $userinfo['uid'];
		}else{
			$data['utype'] = 1;
            $data['mobile'] = $receive_mobile;
			$passport = $this->_user_server('');
            if (false === $data = $passport->register($data)) {
                $this->ajaxReturn(0, $passport->get_error());
            }
            if(!C('qscms_register_password_open')){
                $sendSms['tpl']='set_register_resume';
                $sendSms['data']=array('username'=>$data['username'].'','password'=>$data['password']);
                $sendSms['mobile']=$data['mobile'];
                if(true !== $reg = D('Sms')->sendSms('captcha',$sendSms)) $this->ajaxReturn(0,$reg);
            }
            D('Members')->user_register($data);//积分、套餐、分配客服等初始化操作
            $to_uid = $data['uid'];
		}
		D('CompanyProfile')->company_migration(C('visitor.uid'),$to_uid);
        D('CompanyProfile')->company_migration_log(C('visitor.uid'),$to_uid, !$userinfo);
        session('verify_mobile', null);
        $this->ajaxReturn(1,'',U('migration_done'));
    }
    /**
     * 转移完成
     */
    public function migration_done(){
		if(!session('migration_tmp_mobile')){
			$this->redirect('index');die;
		}
    	$this->assign('migration_tmp_mobile',session('migration_tmp_mobile'));
		session('migration_tmp_mobile',null);
		$this->visitor->update();
		$this->_config_seo(array('title'=>'账号迁移 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
    }
    /**
     * 企业账号注销
     */
    public function cancellation(){
    	$check_cancellation = M('CompanyCancellationApply')->where(array('uid'=>C('visitor.uid'),'status'=>0))->find();
    	if($check_cancellation){
    		$this->redirect('apply_finish');die;
    	}
    	$this->assign('members_info', D('Members')->get_user_one(array('uid' => C('visitor.uid'))));
		$this->_config_seo(array('title'=>'企业账号注销 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
    }
    /**
     * 发送手机验证码 - 企业账号注销
     */
    public function send_mobile_code_cancellation() {
        $mobile = I('post.mobile', '', 'trim,badword');
        if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机格式错误!');
        $userinfo = M('Members')->field('uid,mobile')->where(array('mobile' => $mobile))->find();
        if($userinfo){
			$companyinfo = D('CompanyProfile')->where(array('uid'=>$userinfo['uid']))->find();
			if(!$companyinfo){
				$this->ajaxReturn(0, '您还没有填写企业信息!');
			}
		}else{
			$this->ajaxReturn(0, '手机号不存在!');
		}
        if (session('verify_mobile.time') && (time() - session('verify_mobile.time')) < 60) $this->ajaxReturn(0, '请60秒后再进行验证！');
        $rand = getmobilecode();
        $sendSms = array('mobile' => $mobile, 'tpl' => 'set_mobile_verify', 'data' => array('rand' => $rand . '', 'sitename' => C('qscms_site_name')));
        if (true === $reg = D('Sms')->sendSms('captcha', $sendSms)) {
            session('verify_mobile',array('mobile'=>$mobile,'rand'=>substr(md5($rand), 8,16),'time'=>time()));
            session('_verify_num_check',null);
            $this->ajaxReturn(1, '验证码发送成功！');
        } else {
            $this->ajaxReturn(0, $reg);
        }
    }
    /**
     * 提交注销申请
     */
    public function apply_cancellation(){
    	$verify = session('verify_mobile');
        if(true !== $tip = verify_mobile($verify['mobile'],$verify,I('post.verifycode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
        $uid = C('visitor.uid');
        $user = M('Members')->where(array('uid' => $uid, 'mobile' => $verify['mobile']))->find();
        if ($user){
        	$setsqlarr['uid'] = C('visitor.uid');
            $setsqlarr['company_id'] = $this->company_profile['id'];
        	$setsqlarr['companyname'] = $this->company_profile['companyname'];
        	$setsqlarr['addtime'] = time();
        	$setsqlarr['status'] = 0;
        	$setsqlarr['finishtime'] = 0;
        	M('CompanyCancellationApply')->add($setsqlarr);
        	$this->ajaxReturn(1,'',U('apply_finish'));
        }else{
        	$this->ajaxReturn(0,'手机号验证失败');
        }
    }
    public function apply_finish(){
		$this->_config_seo(array('title'=>'企业账号注销 - 企业会员中心 - '.C('qscms_site_name')));
		$this->display();
    }
}
?>