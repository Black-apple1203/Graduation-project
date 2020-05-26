<?php
/**
 * 前台控制器基类
 *
 * @author andery
 */
namespace Common\Controller;
use Common\Controller\BaseController;
class FrontendController extends BaseController {
    protected $visitor = null;
    protected $apply = null;
    public function _initialize() {
        parent::_initialize();
        //网站状态
        if (C('qscms_isclose')) {
            header('Content-Type:text/html; charset=utf-8');
            exit('网站关闭:' . C('qscms_close_reason'));
        }
        $this->get_resource_dir();
        $this->_init_visitor();
        $show_backtop = 0;
        $show_backtop_app = 0;
        $show_backtop_weixin = 0;
        if (C('qscms_ios_download') || C('qscms_android_download') || C('qscms_weixin_apiopen') == 1) {
            $show_backtop = 1;
        }
        if ((C('qscms_ios_download') || C('qscms_android_download')) && $show_backtop == 1) {
            $show_backtop_app = 1;
        }
        if (C('qscms_weixin_apiopen') == 1 && $show_backtop == 1) {
            $show_backtop_weixin = 1;
        }
        $this->assign('show_backtop', $show_backtop);
        $this->assign('show_backtop_app', $show_backtop_app);
        $this->assign('show_backtop_weixin', $show_backtop_weixin);
        $isRecommend = 1;
        $passport = $this->_user_server();
		//分站判断
		$subsite = new \Common\qscmstag\subsiteTag($where);
		$subsite_list = $subsite->run();
		$this->assign('subsite_list', $subsite_list);
		$this->assign('subsite',C('subsite_info'));
        
		//end
		if(MODULE_NAME=='Home'){
			if(C('qscms_subsite_open')==1 && count($subsite_list)>1){
				$ipInfos = GetIpLookup();
				foreach($subsite_list as $key => $val){
					similar_text($ipInfos['country'],$val['s_sitename'],$percent);
					if($percent>5){
						$sub=$val;
					}
				}
				$sub['district']=$ipInfos['country'];
				if(!cookie('_subsite_domain') && C('subsite_info.s_id') != $sub['s_id']){
					unset($sub['s_id']);
					$isRecommend = 0;
					$d_domain = $subsite_list[$sub['s_id']]['s_domain'];
					$d_type= $subsite_list[$sub['s_id']]['pc_type'];
					$d_domain=$d_type.$d_domain;
					$sub['domain']=$d_domain;
					$domain = C('PLATFORM')=='mobile' && C('subsite_info.s_m_domain') ? C('subsite_info.s_m_domain') : C('subsite_info.s_domain');
					$this->assign('subsite_org',$sub);
					cookie('_subsite_domain',$domain);
				 }
			}
		}
        // 若点击了取消,则24小时内不再弹框推荐
        if (cookie('recommend_cancel')) {
            $isRecommend = 0;
        }
		
		
        $this->assign('isRecommend', $isRecommend);
        if (C('qscms_im_open') && C('qscms_im_appsecret') && C('qscms_im_appkey')) {
            $this->assign('im_state', 1);
        }
    }

    protected function display($tpl = '') {
        if (!$this->get('page_seo')) {
            $page_seo = D('Page')->get_page();
            $this->_config_seo($page_seo[strtolower(MODULE_NAME) . '_' . strtolower(CONTROLLER_NAME) . '_' . strtolower(ACTION_NAME)], I('request.'));
        }
        if ($synsitegroupregister = cookie('members_sitegroup_register')) {
            $this->assign('synsitegroupregister', $synsitegroupregister);
            cookie('members_sitegroup_register', null);
        } elseif ($synsitegroup = cookie('members_sitegroup_action')) {
            $this->assign('synsitegroup', $synsitegroup);
            cookie('members_sitegroup_action', null);
        }
        if ($synsitegroupunbindmobile = cookie('members_sitegroup_unbind_mobile')) {
            $this->assign('synsitegroupunbindmobile', $synsitegroupunbindmobile);
            cookie('members_sitegroup_unbind_mobile', null);
        }
        if ($synsitegroupedit = cookie('members_sitegroup_edit')) {
            $this->assign('synsitegroupedit', $synsitegroupedit);
            cookie('members_sitegroup_edit', null);
        }
        parent::display($tpl);
        if ($this->visitor->is_login && !IS_AJAX && IS_GET) {
            $this->apply['Analyze'] && $this->record_route();
        }
    }

    /**
     * SEO设置
     */
    public function _config_seo($seo_info = array(), $data = array()) {
        $page_seo = array(
            'title'       => '',
            'keywords'    => '',
            'description' => '',
        );
        $page_seo = array_merge($page_seo, $seo_info);
        //开始替换
        $searchs = array('{site_name}', '{site_domain}', '{site_title}', '{site_keywords}', '{site_description}');
		if(C('subsite_info.s_id') > 0){
            if(MODULE_NAME == 'Mobile'){
                $replaces = array(C('subsite_info.s_sitename'),C('subsite_info.s_m_domain'),C('subsite_info.s_title'), C('subsite_info.s_keywords'), C('subsite_info.s_description'));
            }else{
               $replaces = array(C('subsite_info.s_sitename'), C('subsite_info.s_domain'),C('subsite_info.s_title'), C('subsite_info.s_keywords'), C('subsite_info.s_description')); 
            }
        }else{
           $replaces = array(C('qscms_site_name'), C('qscms_site_domain'),'', '', ''); 
        }
        preg_match_all("/\{([a-z0-9_-]+?)\}/", implode(' ', array_values($page_seo)), $pageparams);
        if ($pageparams) {
            $data['key'] && $data['key'] = urldecode(urldecode($data['key']));
            foreach ($pageparams[1] as $var) {
                $searchs[] = '{' . $var . '}';
                $replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
            }
            //符号
            $searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
            $replacespace = array('-', ',', '|', ' ', '_');
            foreach ($page_seo as $key => $val) {
				if((MODULE_NAME == 'Home' || MODULE_NAME == 'Mobile') && CONTROLLER_NAME=='Index'  && C('subsite_info.s_id') > 0){
					if($replaces[2]){
						$page_seo['title']=trim(preg_replace($searchspace, $replacespace, $replaces[2]), ' ,-|_');
					}
					if($replaces[3]){
						$page_seo['keywords']=trim(preg_replace($searchspace, $replacespace, $replaces[3]), ' ,-|_');
					}
					if($replaces[4]){
						$page_seo['description']=trim(preg_replace($searchspace, $replacespace, $replaces[4]), ' ,-|_');
					}
				}else{
					$page_seo[$key] = trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $val)), ' ,-|_');
				}
            }
        }
        $this->assign('page_seo', $page_seo);//创建模板变量page_seo并赋值
        return $page_seo;
    }

    /**
     * 初始化访问者
     */
    protected function _init_visitor() {
        $this->visitor = new \Common\qscmslib\user_visitor();
        $visitor = $this->visitor->info;
        if ($this->visitor->is_login) {
            $is_login = 1;
            if (!IS_AJAX && !$visitor['utype'] && !in_array(ACTION_NAME, array('logout', 'choose_members', 'switch_utype')) && MODULE_NAME != 'Admin' && CONTROLLER_NAME != 'Admin') $this->redirect('members/choose_members');
            $user = $this->visitor->get();
			//h  新增账号状态判断
            if (!$user || $user['status']==2) {
                $this->visitor->logout();
                //同步退出
                $passport = $this->_user_server();
                $synlogout = $passport->synlogout();
                $this->redirect('members/login');
            }
            $user_info = D('Resume')->where(array('uid' => $user['uid'], 'def' => 1))->find();
            $avatar_default = $user_info['sex'] == 1 ? 'no_photo_male.png' : 'no_photo_female.png';
            if ($user['avatars']) {
                $visitor['avatar'] = $user['avatars'];
                $visitor['avatars'] = attach($user['avatars'], 'avatar');
                $visitor['is_avatars'] = 1;
            } else {
                $visitor['avatars'] = attach($avatar_default, 'resource');
                $visitor['is_avatars'] = 0;
            }
            $visitor['reg_time'] = $user['reg_time'];
            $visitor['weixin_audit'] = $user['weixin_audit'];
            $visitor['sms_num'] = $user['sms_num'];
            $visitor['status'] = $user['status'];
            $visitor['consultant'] = $user['consultant'];
            $visitor['major'] = $user_info['major'];
            $visitor['marriage'] = $user_info['marriage'];
            $visitor['householdaddress'] = $user_info['householdaddress'];
            $visitor['residence'] = $user_info['residence'];
            $visitor['fullname'] = $user_info['fullname'];
            $visitor['complete_percent'] = $user_info['complete_percent'];
            $visitor['level'] = $user_info['level'];
            $visitor['points'] = D('MembersPoints')->get_user_points($user['uid']);
            $issign = D('MembersHandsel')->check_members_handsel_day(array('uid' => $user['uid'], 'htype' => 'task_sign'));
            $visitor['issign'] = $issign ? 1 : 0;
            $this->_msgtip();
        } else {
            $is_login = 0;
            $this->assign('verify_userlogin', $this->check_captcha_open(C('qscms_captcha_config.user_login'), 'error_login_count'));
        }
        C('visitor', $visitor);
        $this->assign('visitor', $visitor);
        $this->assign('is_login',$is_login);
    }

    /**
     * [_msgtip 用户消息状态]
     * 返回已读和未读消息数量
     */
    protected function _msgtip($time) {
        if ($msgtip = M('MembersMsgtip')->where(array('uid' => $this->visitor->info['uid']))->getfield('type,update_time,unread')) {
            foreach ($msgtip as $key => $val) {
                $unread += $val['unread'];
            }
        }
        if (false === $sysMgs = F('sysMsg')) {
            $sysMgs = D('PmsSys')->sys_cache();
        }
        if (isset($msgtip[1])) {
            $time = $msgtip[1]['update_time'];
        } else {
            $time = 0;
        }
        if ($time < $sysMgs['time']) {
            $where = array('spms_usertype' => array('in', array(0, $this->visitor->info['utype'])), 'dateline' => array('gt', $time));
            $sys_list = M('PmsSys')->field('message,dateline,spms_usertype')->where($where)->select();
            if ($sys_list) {
                foreach ($sys_list as $key => $val) {
                    $sys_list[$key]['msgtype'] = 1;
                    $sys_list[$key]['msgtouid'] = $this->visitor->info['uid'];
                    $sys_list[$key]['mutually'] = 3;
                    $sys_list[$key]['dateline'] = time();
                    $sys_list[$key]['new'] = 1;
                    $sys_list[$key]['msgtoutype'] = $val['spms_usertype'];
                }
                if (M('Pms')->addAll($sys_list)) {
                    $sys_unread = count($sys_list);
                    if ($time) {
                        M('MembersMsgtip')->where(array('uid' => $this->visitor->info['uid']))->save(array('update_time' => $sysMgs['time'], 'unread' => array('exp', 'unread+' . $sys_unread)));
                    } else {
                        M('MembersMsgtip')->add(array('uid' => $this->visitor->info['uid'], 'type' => 1, 'update_time' => $sysMgs['time'], 'unread' => $sys_unread));
                    }
                    $unread += $sys_unread;
                }
            }
        }
        $this->assign('msgtip', array('unread' => $unread, 'msgtype' => $msgtip));
    }

    /**
     * 连接用户中心
     */
    protected function _user_server($type = '') {
        $passport = new \Common\qscmslib\passport($type);
        return $passport;
    }

    /**
     * 检查当前url中是否有get参数
     */
    protected function check_params() {
        $get = I('get.');
        unset($get['_URL_']);
        $hasget = false;
        foreach ($get as $key => $value) {
            if ($value != '' && $value != 0) {
                $hasget = true;
                break;
            }
        }
        $this->assign('hasget', $hasget);
    }

    /**
     * 弹框提示
     */
    public function ajax_warning($tip, $description = '', $hidden_val = '') {
        $this->assign('tip', $tip);
        $this->assign('description', $description);
        $this->assign('hidden_val', $hidden_val);
        $html = $this->fetch('Members/ajax_warning');
        $this->ajaxReturn(1, '获取数据成功', array('html' => $html, 'hidden_val' => $hidden_val));
    }

    /**
     * 记录路由
     */
    public function record_route() {
        $except_controller_arr = array('public', '__homepublic__', 'undefined', 'application', 'qrcode');
        $except_action_arr = array('login', 'undefined', 'waiting_weixin_bind', 'get_header_min', 'ajax_user_info');
        $current_module = strtolower(MODULE_NAME);
        $current_controller = CONTROLLER_NAME ? strtolower(CONTROLLER_NAME) : 'index';
        $current_action = ACTION_NAME ? strtolower(ACTION_NAME) : 'index';
        $current_page_info['page_alias'] = $current_module . '_' . $current_controller . '_' . $current_action;
        $cn_arr = D('Analyze/MembersRoute')->cn_arr;
        if (session('route_group_id') && isset($cn_arr[$current_page_info['page_alias']]) && !in_array($current_action, $except_action_arr) && !in_array($current_controller, $except_controller_arr)) {
            //上个页面信息
            $last_page_info = session('last_page_info');
            if (!empty($last_page_info)) {
                $last_check_in_time = $last_page_info['addtime'];
                $last_stay_duration = time() - $last_page_info['addtime'];
                M('MembersRoute')->where(array('page_alias' => $last_page_info['page_alias'], 'uid' => C('visitor.uid'), 'addtime' => $last_page_info['addtime']))->setField('page_during', $last_stay_duration);
                M('MembersRouteGroup')->where(array('id' => session('route_group_id')))->setInc('during', $last_stay_duration);
                M('MembersRouteGroup')->where(array('id' => session('route_group_id')))->setField('endtime', time());
            }
            $current_page_info['page_name'] = $cn_arr[$current_page_info['page_alias']];
            $current_page_info['uid'] = C('visitor.uid');
            $current_page_info['addtime'] = time();
            $current_page_info['page_during'] = 0;
            $current_page_info['gid'] = session('route_group_id');
            M('MembersRoute')->add($current_page_info);
            session('last_page_info', $current_page_info);
        }
    }

    protected function get_resource_dir() {
        $config['TPL_PUBLIC_DIR'] = __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/View/' . C('DEFAULT_THEME') . '/public';
        $config['TPL_HOME_PUBLIC_DIR'] = __ROOT__ . '/' . APP_NAME . '/Home/View/' . C('DEFAULT_THEME') . '/public';
        $config['TPL_MOBILE_PUBLIC_DIR'] = __ROOT__ . '/' . APP_NAME . '/Mobile/View/' . C('DEFAULT_THEME') . '/public';
        $config['TPL_COMPANY_DIR'] = __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/View/tpl_company';
        $config['TPL_RESUME_DIR'] = __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/View/tpl_resume';
        $config['TPL_DEFAULTTHEME'] = 'Home@' . C('DEFAULT_THEME') . '/';
        C($config);
    }

    protected function apply_login($mobile, $expire) {
        $passport = $this->_user_server();
        if (!$passport->is_sitegroup()) return false;
        $website_userinfo = D('Members')->where(array('mobile' => $mobile))->find();
        if (!$website_userinfo) {
            if ($passport->is_sitegroup() && false !== $sitegroup_user = $passport->uc('sitegroup')->get($mobile, 'mobile')) {
                $this->_sitegroup_register($sitegroup_user, 'mobile', false);
            } else {
                return false;
            }
        } else {
                $this->visitor->login($website_userinfo['uid'], $expire);
                //同步登陆
                $passport->synlogin($website_userinfo['uid'], $expire);
        }
    }

    /**
     * [_sitegroup_register 站群用户注册本地]
     */
    protected function _sitegroup_register($sitegroup_user, $reg_type, $continue = true) {
        if ($sitegroup_user['utype'] == 1) {
            $company_mod = D('CompanyProfile');
            $company_mod->create($sitegroup_user);
        }
        $passport = $this->_user_server();
        if (false === $sitegroup_user = $passport->uc('default')->register($sitegroup_user)) {
            if ($user = $passport->get_status()) {
                if ($continue) $this->ajaxReturn(1, '会员登录成功！', U('members/reg_email_activate', array('uid' => $user['uid'])));
                return true;
            }
            if ($continue) $this->ajaxReturn(0, $passport->get_error());
            return false;
        }
        // 添加企业信息
        if ($sitegroup_user['utype'] == 1) {
            $company_mod->uid = $sitegroup_user['uid'];
            $insert_company_id = $company_mod->add();
            if ($insert_company_id) {
                switch ($com_setarr['audit']) {
                    case 1:
                        $audit_str = '认证通过';
                        break;
                    case 2:
                        $audit_str = '认证中';
                        break;
                    case 3:
                        $audit_str = '认证未通过';
                        break;
                    default:
                        $audit_str = '';
                        break;
                }
                if ($audit_str) {
                    $auditsqlarr['company_id'] = $insert_company_id;
                    $auditsqlarr['reason'] = '自动设置';
                    $auditsqlarr['status'] = $audit_str;
                    $auditsqlarr['addtime'] = time();
                    $auditsqlarr['audit_man'] = '系统';
                    M('AuditReason')->data($auditsqlarr)->add();
                }
            }
        }
        D('Members')->user_register($sitegroup_user);
        $this->_correlation($sitegroup_user, $continue);
        $points_rule = D('Task')->get_task_cache($sitegroup_user['utype'], 'reg');
        $url = $sitegroup_user['utype'] == 2 ? U('personal/resume_add', array('points' => $points_rule['points'], 'first' => 1)) : U('members/index');
        if ($continue) $this->ajaxReturn(1, '会员登录成功！', $url);
        return true;
    }

    /**
     * [_correlation 用户注册相关]
     */
    protected function _correlation($data, $continue = true) {
        if (false === $this->visitor->login($data['uid'])) {
            if ($continue) {
                IS_AJAX && $this->ajaxReturn(0, $this->visitor->getError());
                $this->error($this->visitor->getError(), 'register');
            }
            return false;
        }
        //同步登陆
        $this->_user_server()->synlogin($data['uid']);
    }
}