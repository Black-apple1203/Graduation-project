<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class MembersController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login) {
            if (in_array(ACTION_NAME, array('index', 'pms', 'sign_in'))) {
                IS_AJAX && $this->ajaxReturn(0, L('login_please'), '', 1);
                //非ajax的跳转页面
                $this->redirect('members/login');
            }
        } else {
            $urls = array('1' => 'company/index', '2' => 'personal/index');
                !IS_AJAX && !in_array(ACTION_NAME, array('logout', 'choose_members', 'switch_utype')) && $this->redirect($urls[C('visitor.utype')], array('uid' => $this->visitor->info['uid']));
        }
    }

    /**
     * [login 用户登录]
     */
    public function login() {
        if (!I('get.org', '', 'trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']) {
            redirect(build_mobile_url(array('c' => 'Members', 'a' => 'login')));
        }
		
        if (IS_AJAX && IS_POST) {
            $expire = I('post.expire', 1, 'intval');
            $url = I('post.url', '', 'trim');
            //后台开启了极验，并且开启了会员登录验证
            if (C('qscms_captcha_open') == 1 && (C('qscms_captcha_config.user_login') == 0 || (session('?error_login_count') && session('error_login_count') >= C('qscms_captcha_config.user_login')))) {
                if (true !== $reg = \Common\qscmslib\captcha::verify()) $this->ajaxReturn(0, $reg);
            }
			
            $passport = $this->_user_server();
            if ($mobile = I('post.mobile', '', 'trim')) {//手机验证码登录
                !fieldRegex($mobile, 'mobile') && $this->ajaxReturn(0, '手机号格式错误！');
                $smsVerify = session('login_smsVerify');
                if(true !== $tip = verify_mobile($mobile,$smsVerify,I('post.mobile_vcode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
                $user = M('Members')->where(array('mobile'=>$smsVerify['mobile']))->find();
                if($user){
                    $uid = $user['uid'];
                    if ($user['utype'] == 1 && !$user['sitegroup_uid']) {
                        $company = M('CompanyProfile')->field('companyname,contact,landline_tel')->where(array('uid' => $user['uid']))->find();
                        $user = array_merge($user, $company);
                    }
                    if (!$user['sitegroup_uid'] && $passport->is_sitegroup()) {
                        $temp = $passport->uc('sitegroup')->register($user);
                        $temp && M('Members')->where(array('uid' => $user['uid']))->setfield('sitegroup_uid', $temp['sitegroup_uid']);
                    }
                    session('login_smsVerify', null);
                } elseif ($passport->is_sitegroup() && false !== $sitegroup_user = $passport->uc('sitegroup')->get($smsVerify['mobile'], 'mobile')) {
                    $this->_sitegroup_register($sitegroup_user, 'mobile');
                } else {
                    $err = '帐号不存在！';
                }
            } else {//用户名登录
			
                $username = I('post.username', '', 'trim');
                $password = I('post.password', '', 'trim');
                if (false === $uid = $passport->uc('default')->auth($username, $password)) {
                    $err = $passport->get_error();
                    if ($err == L('auth_null')) {
                        if ($passport->is_sitegroup()) {
                            if (false === $passport->uc('sitegroup')->auth($username, $password)) {
                                $err = $passport->get_error();
                            } else {
                                $this->_sitegroup_register($passport->_user);
                            }
                        }
                    }
                } else {
                    $user = $passport->_user;
                    if ($user['utype'] == 1 && (!$user['sitegroup_uid'])) {
                        $company = M('CompanyProfile')->field('companyname,contact,landline_tel')->where(array('uid' => $user['uid']))->find();
                        $user = array_merge($user, $company);
                    }
                    if (!$user['sitegroup_uid'] && $passport->is_sitegroup()) {
                        $temp = $passport->uc('sitegroup')->register($user);
                        $temp && M('Members')->where(array('uid' => $user['uid']))->setfield('sitegroup_uid', $temp['sitegroup_uid']);
                    }
                }
            }
			
            if ($uid) {
                if (false === $this->visitor->login($uid, $expire)) $this->ajaxReturn(0, $this->visitor->getError());
                $urls = array('1' => 'company/index', '2' => 'personal/index');
                $login_url = $url ? $url : U($urls[$this->visitor->info['utype']], array('uid' => $this->visitor->info['uid']));
                $this->ajaxReturn(1, '登录成功！', $login_url);
            }
            //记录登录错误次数
            if (C('qscms_captcha_open') == 1) {
                if (C('qscms_captcha_config.user_login') > 0) {
                    $error_login_count = session('?error_login_count') ? (session('error_login_count') + 1) : 1;
                    session('error_login_count', $error_login_count);
                    if (session('error_login_count') >= C('qscms_captcha_config.user_login')) {
                        $verify_userlogin = 1;
                    } else {
                        $verify_userlogin = 0;
                    }
                } else {
                    $verify_userlogin = 1;
                }
            } else {
                $verify_userlogin = 0;
            }
            $this->ajaxReturn(0, $err, $verify_userlogin);
        } else {
            if ($this->visitor->is_login) {
                $urls = array('1' => 'company/index', '2' => 'personal/index');
                $this->redirect($urls[C('visitor.utype')], array('uid' => $this->visitor->info['uid']));
            }
            if (false === $oauth_list = F('oauth_list')) {
                $oauth_list = D('Oauth')->oauth_cache();
            }
            $this->assign('verify_userlogin', $this->check_captcha_open(C('qscms_captcha_config.user_login'), 'error_login_count'));
            $this->assign('oauth_list', $oauth_list);
            $this->assign('title', '会员登录 - ' . C('qscms_site_name'));
            $this->display();
        }
    }
    /**
     * 用户退出
     */
    public function logout() {  
        $this->visitor->logout();
        //同步退出
        $passport = $this->_user_server();
        $synlogout = $passport->synlogout();
        $this->redirect('members/login');
    }
    /**
     * 会员中心关注微信公众号弹框是否弹出
     */
    public function sign_wx(){
      $this->visitor->wx_frame_status(); 
      $this->ajaxReturn(1, '');
    }
    /**
     * 若会员类型utype=0，则先选择会员类型
     */
    public function choose_members() {
        $this->display();
    }

    /**
     * [register 会员注册]
     */
    public function register() {
        if (!I('get.org', '', 'trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']) {
            redirect(build_mobile_url(array('c' => 'Members', 'a' => 'register')));
        }
        if (C('qscms_closereg')) {
            IS_AJAX && $this->ajaxReturn(0, '网站暂停会员注册，请稍后再次尝试！');
            $this->error("网站暂停会员注册，请稍后再次尝试！");
        }
        if (IS_POST && IS_AJAX) {
            $data['utype'] = I('post.utype', 0, 'intval');
            $data['mobile'] = I('post.mobile', '', 'trim');
            !$data['mobile'] && $this->ajaxReturn(0, '请填写手机号!');
            $smsVerify = session('reg_smsVerify');
            if(true !== $tip = verify_mobile($data['mobile'],$smsVerify,I('post.mobile_vcode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
            //后台开启注册填写密码
            if (C('qscms_register_password_open')) {
                $data['password'] = I('post.password', '', 'trim');
                !$data['password'] && $this->ajaxReturn(0, '请输入密码!');
                $passwordVerify = I('post.passwordVerify', '', 'trim');
                $data['password'] != $passwordVerify && $this->ajaxReturn(0, '两次密码输入不一致!');
            }
            //注册帐号
            $passport = $this->_user_server('');
            if (false === $data = $passport->register($data)) {
                $this->ajaxReturn(0, $passport->get_error());
            }
            
            //第三方帐号注册
            if ('bind' == I('post.org', '', 'trim') &&  cookie('members_bind_info')) {
                $user_bind_info = object_to_array(cookie('members_bind_info'));
                $user_bind_info['uid'] = $data['uid'];
                $oauth = new \Common\qscmslib\oauth($user_bind_info['type']);
                $oauth->bindUser($user_bind_info);
                $this->_save_avatar($user_bind_info['temp_avatar'], $data['uid']);//临时头像转换
                cookie('members_bind_info', NULL);//清理绑定COOKIE
                session('members_bind_info', NULL);//清理绑定session
            }
            session('reg_smsVerify', null);
            D('Members')->user_register($data);//积分、套餐、分配客服等初始化操作
            $this->_correlation($data);//同步登录
            $points_rule = D('Task')->get_task_cache($data['utype'], 'reg');
            $urls = array('0' => U('members/choose_members'),
                          '1' => U('company/index'),
                          '2' => U('personal/resume_add', array('points' => $points_rule['points'], 'first' => 1)));
            $result['url'] = $urls[$data['utype']];
			/*
			******************chm    strat*********
			*/
			if((C('qscms_open_give_gift')==1) && (C('qscms_is_give_gift')==1) && (C('qscms_is_give_gift_value')<>'')){
				$is_give_gift_value = C('qscms_is_give_gift_value');
				$gift_id =explode(',',$is_give_gift_value);
				$issue_data['gift_type']=2;//1单发企业=专享优惠券 ； 2新用户开了送=新用户专享； 3活动批量发=活动专享
				$static_data['admin_id']=1;
				$static_data['gift_id']=implode(",",$gift_id);
				//$static_data['issue_id']=substr($str,0,-1);
				$static_data['uid']=$data['uid'];
				$static_data['addtime']=time();
				$static_id = M('GiftStatic')->add($static_data);
				$succ=0;$fals=0;$str="";
				$gift_issue = M('GiftIssue')->getField('id,issue_num');
				$max_issue_num_id = M('GiftIssue')->max('id');
				$max_issue_num = $gift_issue[$max_issue_num_id];
				$gifts = M("Gift")->getField('id,gift_name,price,setmeal_name,setmeal_id,effectivetime');
				foreach($gifts as $k=>$v){
					$id=$v['id'];
					$gift_arr[$id]=$v;
				}
				$now_issue_num_id=$max_issue_num_id+1;	
				foreach($gift_id as $keys=>$vals){
					$gift_where['id'] = $vals;
					$issue_data['gift_id']=$vals;
					$issue_data['gift_setmeal_id']=$gift_arr[$vals]['setmeal_id'];
					$issue_data['admin_id']=1;
					$issue_data['is_used']=2;
					$issue_data['addtime']=time();
					$issue_data['static_id']=$static_id;
					$issue_data['deadtime']=$issue_data['addtime']+$gift_arr[$vals]['effectivetime']*60*60*24;			
					if(strlen($now_issue_num_id)<10){
						$issue_num = $now_issue_num_id;
						$len = strlen($val['id']);
						for($i=8;$i>=$len;$i--){
							$issue_num = '0'.$issue_num;
						}
					}else{
						$issue_num = $now_issue_num_id;
					}
					$now_issue_num_id++;
					$issue_data['issue_num'] = $issue_num;
					$issue_data['uid']=$data['uid'];
					$insertid = M('GiftIssue')->add($issue_data);
					if($insertid){
						$succ++;
						$str.=$insertid.",";
					}else{
						$fals++;
					}
				}
				if($succ>0){
                    $user_info = D('Members')->find($uid);
					//站内信
                    $setsqlarr_pms['message'] = "恭喜您获得".$succ."张套餐优惠券！";
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
							$r = D('Sms')->sendSms('notice', array('mobile' => $user_info['mobile'], 'tpl' => 'set_gift', 'data' => array('username' => $user_info['username'],'succ' => $succ)));
                            if ($r === true) {
                                D('Members')->where(array('uid' => $uid))->setDec('sms_num');
                            }
                        }
                    }
					//微信
                    if (false === $module_list = F('apply_list')) $module_list = D('Apply')->apply_cache();
                    if ($module_list['Weixin']) {
                        $map['uid'] = $data['uid'];		
						$remind = M('GiftIssue')->where($map)->order('addtime desc')->find();
						D('Weixin/TplMsg')->set_gift($val, '套餐优惠券', date('Y-m-d H:i',$remind['addtime']).'至'.date('Y-m-d H:i',$remind['deadtime']));
                    }
				}
			}
			/*
			******************chm    end*********
			*/
            if(!C('qscms_register_password_open')){
                $sendSms['tpl']='set_register_resume';
                $sendSms['data']=array('username'=>$data['username'].'','password'=>$data['password']);
                $sendSms['mobile']=$data['mobile'];
                D('Sms')->sendSms('captcha',$sendSms);
            }
            $this->ajaxReturn(1, '会员注册成功！', $result);
        }
    }

    /**
     * [send_sms 注册验证短信]
     */
    public function verify_sms() {
        $mobile = I('get.mobile', '', 'trim');
        if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机号格式错误！');
        $smsVerify = session('reg_smsVerify');
        if ($mobile != $smsVerify('mobile')) $this->ajaxReturn(0, '手机号不一致！');//手机号不一致
        if (time() > $smsVerify('time') + 600) $this->ajaxReturn(0, '验证码过期！');//验证码过期
        $vcode_sms = I('get.mobile_vcode', 0, 'intval');
        $mobile_rand = substr(md5($vcode_sms), 8, 16);
        if ($mobile_rand != $smsVerify('rand')) $this->ajaxReturn(0, '验证码错误！');//验证码错误！
        $this->ajaxReturn(1, '手机验证成功！');
    }

    // 注册发送短信/找回密码 短信
    public function reg_send_sms() {
        if (C('qscms_captcha_open') && C('qscms_captcha_config.varify_mobile') && true !== $reg = \Common\qscmslib\captcha::verify()) $this->ajaxReturn(0, $reg);
        if ($uid = I('post.uid', 0, 'intval')) {
            $mobile = M('Members')->where(array('uid' => $uid))->getfield('mobile');
            !$mobile && $this->ajaxReturn(0, '用户不存在！');
        } else {
            $mobile = I('post.mobile', '', 'trim');
            !$mobile && $this->ajaxReturn(0, '请填手机号码！');
        }
        if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机号错误！');
        $sms_type = I('post.sms_type', 'reg', 'trim');
        $rand = getmobilecode();
        switch ($sms_type) {
            case 'reg':
                $sendSms['tpl'] = 'set_register';
                $sendSms['data'] = array('rand' => $rand . '', 'sitename' => C('qscms_site_name'));
                break;
            case 'gsou_reg':
                $sendSms['tpl'] = 'set_register';
                $sendSms['data'] = array('rand' => $rand . '', 'sitename' => C('qscms_site_name'));
                break;
            case 'getpass':
                $sendSms['tpl'] = 'set_retrieve_password';
                $sendSms['data'] = array('rand' => $rand . '', 'sitename' => C('qscms_site_name'));
                break;
            case 'login':
                if (!$uid = M('Members')->where(array('mobile' => $mobile))->getfield('uid')) {
                    if (false === $sitegroup_user = $passport->uc('sitegroup')->get($smsVerify['mobile'], 'mobile')) {
                        $this->ajaxReturn(0, '您输入的手机号未注册会员');
                    }
                }
                $sendSms['tpl'] = 'set_login';
                $sendSms['data'] = array('rand' => $rand . '', 'sitename' => C('qscms_site_name'));
                break;
        }
        $smsVerify = session($sms_type . '_smsVerify');
        if ($smsVerify && $smsVerify['mobile'] == $mobile && time() < $smsVerify['time'] + 60) $this->ajaxReturn(0, '60秒内仅能获取一次短信验证码,请稍后重试');
        $sendSms['mobile'] = $mobile;
        if (true === $reg = D('Sms')->sendSms('captcha', $sendSms)) {
            session($sms_type . '_smsVerify', array('rand' => substr(md5($rand), 8, 16), 'time' => time(), 'mobile' => $mobile));
            session('_verify_num_check',null);
            $this->ajaxReturn(1, '手机验证码发送成功！');
        } else {
            $this->ajaxReturn(0, $reg);
        }
    }

    /**
     * 检测用户信息是否存在或合法
     */
    public function ajax_check() {
        $type = I('post.type', 'trim', 'email');
        $param = I('post.param', '', 'trim');
        if (in_array($type, array('username', 'mobile', 'email'))) {
            $type != 'username' && !fieldRegex($param, $type) && $this->ajaxReturn(0, L($type) . '格式错误！');
            $where[$type] = $param;
            $reg = M('Members')->field('uid,status')->where($where)->find();
            if ($reg['uid'] && $reg['status'] != 0) {
                $this->ajaxReturn(0, L($type) . '已经注册');
            } else {
                $passport = $this->_user_server();
                $name = 'check_' . $type;
                if (false === $passport->$name($param)) {
                    $this->ajaxReturn(0, $passport->get_error());
                }
            }
            $this->ajaxReturn(1);
        } elseif ($type == 'companyname') {
            if (C('qscms_company_repeat') == 0) {
                $reg = M('CompanyProfile')->where(array('companyname' => $param))->getfield('id');
                $reg ? $this->ajaxReturn(0, '企业名称已经注册') : $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(1);
            }
        }
    }

    

    /**
     * [waiting_weixin_bind 循环检测微信是否扫码绑定]
     */
    public function waiting_weixin_bind() {
        $scene_id = session('bind_scene_id');
        if ($openid = F('/weixin/' . ($scene_id % 10) . '/' . $scene_id)) {
            $reg = \Common\qscmslib\weixin::bind($openid, C('visitor'));
            $this->ajaxReturn($reg['state'], $reg['tip']);
        } else {
            $this->ajaxReturn(0, '微信没有绑定！');
        }
    }

    /**
     * [user_getpass 忘记密码]
     */
    public function user_getpass() {
        if (IS_POST) {
            $type = I('post.type', 0, 'intval');
            $array = array(1 => 'mobile', 2 => 'email');
            if (!$reg = $array[$type]) $this->error('请正确选择找回密码方式！');
            $retrievePassword = session('retrievePassword');
            if ($retrievePassword['token'] != I('post.token', '', 'trim')) $this->error('非法参数！');
            $mobile = I('post.mobile', 0, 'trim');
            if (!$user = M('Members')->field('uid,username')->where(array('mobile' => $mobile))->find()) $this->error('该手机号没有绑定帐号！');
            $smsVerify = session('getpass_smsVerify');
            if ($mobile != $smsVerify['mobile']) $this->error('手机号不一致！');//手机号不一致
            if (time() > $smsVerify['time'] + 600) $this->error('验证码过期！');//验证码过期
            $vcode_sms = I('post.mobile_vcode', 0, 'intval');
            $mobile_rand = substr(md5($vcode_sms), 8, 16);
            if ($mobile_rand != $smsVerify['rand']) $this->error('验证码错误！');//验证码错误！
            $tpl = 'user_setpass';
            session('smsVerify', null);
        }
        $token = substr(md5(getmobilecode()), 8, 16);
        session('retrievePassword', array('uid' => $user['uid'], 'token' => $token));
        $this->assign('token', $token);
        $this->_config_seo(array('title' => '找回密码 - ' . C('qscms_site_name')));
        $this->display($tpl);
    }

    /**
     * [find_pwd 重置密码]
     */
    public function user_setpass() {
        if (IS_POST) {
            $retrievePassword = session('retrievePassword');
            if ($retrievePassword['token'] != I('post.token', '', 'trim')) $this->error('非法参数！');
            $user['password'] = I('post.password', '', 'trim,badword');
            !$user['password'] && $this->error('请输入新密码！');
            if ($user['password'] != I('post.password1', '', 'trim,badword')) $this->error('两次输入密码不相同，请重新输入！');
            $passport = $this->_user_server();
            if (false === $passport->edit($retrievePassword['uid'], $user)) $this->error($passport->get_error());
            $tpl = 'user_setpass_sucess';
            session('retrievePassword', null);
        } else {
            parse_str(decrypt(I('get.key', '', 'trim'), C('PWDHASH')), $data);
            !fieldRegex($data['e'], 'email') && $this->error('找回密码失败,邮箱格式错误！', 'user_getpass');
            $end_time = $data['t'] + 24 * 3600;
            if ($end_time < time()) $this->error('找回密码失败,链接过期!', 'user_getpass');
            $key_str = substr(md5($data['e'] . $data['t']), 8, 16);
            if ($key_str != $data['k']) $this->error('找回密码失败,key错误!', 'user_getpass');
            if (!$uid = M('Members')->where(array('email' => $data['e']))->getfield('uid')) $this->error('找回密码失败,帐号不存在!', 'user_getpass');
            $token = substr(md5(getmobilecode()), 8, 16);
            session('retrievePassword', array('uid' => $uid, 'token' => $token));
            $this->assign('token', $token);
        }
        $this->_config_seo(array('title' => '找回密码 - ' . C('qscms_site_name')));
        $this->display($tpl);
    }

    /**
     * 账号申诉
     */
    public function appeal_user() {
        $mod = D('MembersAppeal');
        if (IS_POST && IS_AJAX) {
            if (false === $data = $mod->create()) {
                $this->ajaxReturn(0, $mod->getError());
            }
            if (false !== $mod->add($data)) {
                $this->ajaxReturn(1, L('operation_success'));
            } else {
                $this->ajaxReturn(0, L('operation_failure'));
            }
        }
        $this->_config_seo(array('title' => '账号申诉 - ' . C('qscms_site_name')));
        $this->display();
    }
    /**
     * [binding 第三方绑定]
     */
    public function apilogin_binding() {
        $user_bind_info = object_to_array(cookie('members_bind_info'));
        if (!$this->visitor->is_login && !$user_bind_info) $this->redirect('members/login');
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('third_name', $oauth_list[$user_bind_info['type']]['name']);
        $this->assign('user_bind_info', $user_bind_info);
        $this->_config_seo();
        $this->display();
    }

    /**
     * [oauth_reg 第三方绑定-绑定已有帐号]
     */
    public function oauth_reg() {
        if (cookie('members_bind_info')) {
            $user_bind_info = object_to_array(cookie('members_bind_info'));
        } else {
            $this->error('第三方授权失败，请重新操作！');
        }
        //第三方帐号绑定
        $username = I('post.username', '', 'trim');
        $password = I('post.password', '', 'trim');
        $passport = $this->_user_server();
        if (false === $uid = $passport->uc('default')->auth($username, $password)) {
            if ($passport->is_sitegroup() && false !== $passport->uc('sitegroup')->auth($username, $password)) {
                if (false === $sitegroup_user = $passport->uc('default')->register($passport->_user)) {
                    $this->error($passport->get_error());
                } else {
                    D('Members')->user_register($sitegroup_user);
                    $this->_correlation($sitegroup_user);
                    $login = true;
                }
            } else {
                $this->error($passport->get_error());
            }
        } else {
            $user = $passport->_user;
            if ($user['utype'] == 1 && !$user['sitegroup_uid']) {
                $company = M('CompanyProfile')->field('companyname,contact,landline_tel')->where(array('uid' => $user['uid']))->find();
                $user = array_merge($user, $company);
            }
            if (!$user['sitegroup_uid'] && $passport->is_sitegroup()) {
                $temp = $passport->uc('sitegroup')->register($user);
                $temp && M('Members')->where(array('uid' => $user['uid']))->setfield('sitegroup_uid', $temp['sitegroup_uid']);
            }
        }
        if (!$login) {
            if (false === $this->visitor->login($uid)) $this->error($this->visitor->getError());
            $passport->synlogin($uid);
        }
        $info = M('MembersBind')->where(array('type' => $user_bind_info['type'], 'uid' => $uid))->find();
        if ($info) $this->error('此会员已经绑定过第三方账号！');
        $oauth = new \Common\qscmslib\oauth($user_bind_info['type']);
        $bind_user = $oauth->_checkBind($user_bind_info['type'], $user_bind_info);
        if ($bind_user['uid'] && $bind_user['uid'] != $uid) $this->error('此帐号已经绑定过本站！');
        $user_bind_info['uid'] = $uid;
        if (false === $oauth->bindUser($user_bind_info)) $this->error('帐号绑定失败，请重新操作！');
        if (!$this->visitor->get('avatars')) $this->_save_avatar($user_bind_info['temp_avatar'], $uid);//临时头像转换
        cookie('members_bind_info', NULL);//清理绑定COOKIE
        session('members_bind_info', NULL);//清理绑定session
        $urls = array(1 => 'company/index', 2 => 'personal/index');
        $this->redirect($urls[$this->visitor->info['utype']], array('uid' => $uid));
    }

    /**
     * [_save_avatar 第三方头像保存]
     */
    protected function _save_avatar($avatar, $uid) {
        if (!$avatar) return false;
        $path = C('qscms_attach_path') . 'avatar/temp/' . $avatar;
        $image = new \Common\ORG\ThinkImage();
        $date = date('ym/d/');
        $save_avatar = C('qscms_attach_path') . 'avatar/' . $date;//图片存储路径
        if (!is_dir($save_avatar)) mkdir($save_avatar, 0777, true);
        $savePicName = md5($uid . time()) . ".jpg";
        $filename = $save_avatar . $savePicName;
        $size = explode(',', C('qscms_avatar_size'));
        copy($path, $filename);
        foreach ($size as $val) {
            $image->open($path)->thumb($val, $val, 3)->save("{$filename}._{$val}x{$val}.jpg");
        }
        M('Members')->where(array('uid' => $uid))->setfield('avatars', $date . $savePicName);
        @unlink($path);
    }

    /**
     * [save_username 修改帐户名]
     */
    public function save_username() {
        if (IS_POST) {
            $user['username'] = I('post.username', '', 'trim,badword');
            $passport = $this->_user_server();
            if (false === $passport->edit(C('visitor.uid'), $user)) $this->ajaxReturn(0, $passport->get_error());
            $this->visitor->update();//刷新会话
            $this->ajaxReturn(1, '用户名修改成功！');
        } else {
            $data['html'] = $this->fetch('ajax_modify_uname');
            $this->ajaxReturn(1, '修改用户名弹窗获取成功！', $data);
        }
    }

    /**
     * [save_password 修改密码]
     */
    public function save_password() {
        if (IS_POST) {
            /*$oldpassword = I('post.oldpassword', '', 'trim,badword');
            !$oldpassword && $this->ajaxReturn(0, '请输入原始密码!');*/  //***********chm  注释
            $password = I('post.password', '', 'trim,badword');
            !$password && $this->ajaxReturn(0, '请输入新密码！');
            if ($password != I('post.password1', '', 'trim,badword')) $this->ajaxReturn(0, '两次输入密码不相同，请重新输入！');
            //$data['oldpassword'] = $oldpassword;
            $data['password'] = $password;
            $reg = D('Members')->save_password($data, C('visitor'));
            !$reg['state'] && $this->ajaxReturn(0, $reg['error']);
            $this->ajaxReturn(1, '密码修改成功！');
        } else {
            $data['html'] = $this->fetch('ajax_modify_pwd');
            $this->ajaxReturn(1, '修改密码弹窗获取成功！', $data);
        }
    }

    /**
     * [user_mobile 获取手机验证弹窗]
     */
    public function user_mobile() {
        $tpl = $this->fetch('ajax_auth_mobile');
        $this->ajaxReturn(1, '手机验证弹窗获取成功！', $tpl);
    }

    /**
     * [send_mobile_code 发送手机验证码]
     */
    public function send_mobile_code() {
        $mobile = I('post.mobile', '', 'trim,badword');
        if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机格式错误!');
        $user = M('Members')->field('uid,mobile')->where(array('mobile' => $mobile))->find();
        $user['uid'] && $user['uid'] <> C('visitor.uid') && $this->ajaxReturn(0, '手机号已经存在,请填写其他手机号!');
        if ($user['mobile'] && $user['mobile'] == $mobile) $this->ajaxReturn(0, "你的手机号 {$mobile} 已经通过验证！");
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
     * [verify_mobile_code 验证手机验证码]
     */
    public function verify_mobile_code() {
        $verify = session('verify_mobile');
        if(true !== $tip = verify_mobile($verify['mobile'],$verify,I('post.verifycode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
        $setsqlarr['mobile'] = $verify['mobile'];
        $uid = C('visitor.uid');
        $user = M('Members')->where(array('uid' => $uid, 'mobile' => $verify['mobile']))->find();
        if ($user) $this->ajaxReturn(0, "你的手机 {$verify['mobile']} 已经通过验证！");
        $passport = $this->_user_server();
        if (false === $passport->edit($uid, $setsqlarr)) $this->ajaxReturn(0, '手机验证失败!');
        D('Members')->update_user_info($setsqlarr, C('visitor'));
        write_members_log(C('visitor'), '', '手机验证通过（手机号：' . $verify['mobile'] . '）');
        session('verify_mobile', null);
        $this->ajaxReturn(1, '手机验证通过!', array('mobile' => $verify['mobile']));
    }

    /**
     * [sign_in 签到]
     */
    public function sign_in() {
        if (IS_AJAX) {
            $reg = D('Members')->sign_in(C('visitor'));
            if ($reg['state']) {
                write_members_log(C('visitor'),'points','成功签到');
                $this->ajaxReturn(1, '成功签到！', $reg['points']);
            } else {
                $this->ajaxReturn(0, $reg['error']);
            }
        }
    }
    /**
     * 获取注册协议
     */
    public function members_agreement(){
        $agreement = htmlspecialchars_decode(M('Text')->where(array('name'=>'agreement'))->getField('value'),ENT_QUOTES);
        $this->assign('agreement',$agreement);
        $this->display();
    }
    /**
     * 切换会员类型
     */
    public function switch_utype() {
        //访问者控制
        if (!$this->visitor->is_login) {
            $this->redirect('members/login');
        }
        $utype = I('request.utype', 0, 'intval');
        !in_array($utype, array(1, 2)) && $this->_404('会员类型选择错误！');
        // 防止旧帐号（兼职等模块转入members表中的用户）没有进行注册后的初始化
        $setinfo=D('MembersSetmeal')->get_user_setmeal($this->visitor->info['uid']);
        if (null === $setinfo) {
            D('Members')->user_register($this->visitor->info);//积分、套餐、分配客服等初始化操作
        }else if( $setinfo['setmeal_id']==0){
			//D('MembersSetmeal')->set_members_setmeal($this->visitor->info['uid'], C('qscms_reg_service'));//赠送企业套餐--------chm修改前
			D('MembersSetmeal')->set_members_setmeal($this->visitor->info['uid'], C('qscms_reg_service', 0));//赠送企业套餐--------chm修改后
		}
        if (false !== D('Members')->where(array('uid' => C('visitor.uid')))->setField('utype', $utype)) {
            $this->visitor->update();
            $url_arr = array('1' => 'company/index', '2' => 'personal/index');
            $this->redirect($url_arr[$this->visitor->info['utype']], array('uid' => $this->visitor->info['uid']));
        }
    }
    /**
     * 网络招聘会AJAX切换会员类型
     */
    public function ajax_switch_utype() {
        //访问者控制
        if (!$this->visitor->is_login) {
            $this->redirect('members/login');
        }
        $utype = I('request.utype', 0, 'intval');
        !in_array($utype, array(1, 2)) && $this->_404('会员类型选择错误！');
        // 防止旧帐号（兼职等模块转入members表中的用户）没有进行注册后的初始化
        $setinfo=D('MembersSetmeal')->get_user_setmeal($this->visitor->info['uid']);
        if (null === $setinfo) {
            D('Members')->user_register($this->visitor->info);//积分、套餐、分配客服等初始化操作
        }else if( $setinfo['setmeal_id']==0){
            //D('MembersSetmeal')->set_members_setmeal($this->visitor->info['uid'], C('qscms_reg_service'));//赠送企业套餐--------chm修改前
            D('MembersSetmeal')->set_members_setmeal($this->visitor->info['uid'], C('qscms_reg_service', 0));//赠送企业套餐--------chm修改后
        }
        if (false !== D('Members')->where(array('uid' => C('visitor.uid')))->setField('utype', $utype)) {
            $this->visitor->update();
            $this->ajaxReturn(1,'切换成功');
        }
    }
}

?>
