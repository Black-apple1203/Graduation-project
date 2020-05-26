<?php
/**
 * 访问者
 *
 * @author andery
 */
namespace Common\qscmslib;
class user_visitor {
    protected $_session;
    public $is_login = false; //登陆状态
    public $info = null;
    public function __construct() {
        $this->_session = '_qscms'.md5(C('PWDHASH'));
        if (session('?'.$this->_session)) {
        	//已经登陆
        	$this->info = session($this->_session);
        	$this->is_login = true;
        } elseif ($user_info = (array)cookie($this->_session)) {
            $field = 'uid,utype,username,email,mobile,password,last_login_ip,terminal,registration_id';
            $user_info = M('Members')->field($field)->where(array('uid'=>$user_info[md5('uid')], 'password'=>$user_info[md5('password')]))->find();
            if ($user_info) {
                $user_info['last_login_time'] = time();
                M('Members')->where(array('uid'=>$user_info['uid']))->setField('last_login_time',$user_info['last_login_time']);
                //记住登陆状态
                $this->assign_info($user_info);
                $this->is_login = true;
            }else{
                $this->is_login = false;
            }
        } else {
            $this->is_login = false;
        }
    }
    /**
     * 登陆会话
     */
    public function assign_info($user_info) {
        session($this->_session, $user_info);
        $this->info = $user_info;
        $this->is_login = true;
        // 登录自动刷新简历
        if (C('qscms_login_refresh_resume') && $user_info['utype'] == 2){
            $where['uid'] = $user_info['uid'];
            $where['def'] = 1;
            $where['audit'] = array('neq',3);
            $where['display'] = 1;
            $rid = M('Resume')->where($where)->getField('id');
            $rid && D('Resume')->refresh_resume($rid,$user_info);
        }
    }
    /**
     * 增加会话
     */
    public function assign_add($key,$val) {
    	$this->info[$key] = $val;
    	session($this->_session,$this->info);
    }
    /**
     * 记住密码
     */
    public function remember($user_info, $remember = true) {
        if ($remember) {
            $day = is_int($remember)?$remember:7;
            $time = 3600 * 24 * $day; //7天记住密码
            cookie($this->_session, array(md5('uid')=>$user_info['uid'], md5('password')=>$user_info['password']), $time);
        }
    }
    /**
     * 获取用户信息
     */
    public function get($key = null) {
        $info = null;
        if (is_null($key) && $this->info['uid']) {
            $info = M('Members')->find($this->info['uid']);
        } else {
            if (isset($this->info[$key])) {
                return $this->info[$key];
            } else {
                //获取用户表字段
                $fields = M('Members')->getDbFields();
                if (!is_null(array_search($key, $fields))) {
                    $info = M('Members')->where(array('uid' => $this->info['uid']))->getField($key);
                }
            }
        }
        return $info;
    }
    /**
     * 登陆
     */
    public function login($uid,$remember = true) {
        $user_mod = M('Members');
        //更新用户信息
        $data = array(
            'last_login_time'=>time(),
            'last_login_ip'=> get_client_ip()
        );
        $terminal = I('request.terminal','','trim');
        $registration_id = I('request.registration_id','','trim');
        if($terminal = I('request.terminal','','trim')){
            if(in_array($terminal,array('android','ios','winphone'))){
                $data['terminal'] = $terminal;
                $data['registration_id'] = I('request.registration_id','','trim');
                $user_mod->where(array('registration_id' => $data['registration_id']))->save(array('registration_id'=>'','terminal'=>''));//zxr新增
            }
        }
        $user_mod->where(array('uid' => $uid))->save($data);
        $field = 'uid,utype,username,email,mobile,password,last_login_time,last_login_ip,terminal,registration_id';
        if(!$user_info = $user_mod->field($field)->find($uid)){
            $this->_error = L('login_failed');
            return false;
        }
		//h 套餐问题
		$setinfo=D('MembersSetmeal')->get_user_setmeal($user_info['uid']);
        if (null === $setinfo) {
            D('Members')->user_register($user_info);//积分、套餐、分配客服等初始化操作
        }else if( $setinfo['setmeal_id']==0){
			//D('MembersSetmeal')->set_members_setmeal($user_info['uid'], C('qscms_reg_service'));//赠送企业套餐--------chm修改前
			D('MembersSetmeal')->set_members_setmeal($user_info['uid'], C('qscms_reg_service'), 0);//赠送企业套餐--------chm修改后
		}
        //保持状态
        $this->assign_info($user_info);
        $this->remember($user_info, $remember);
        //写入会员日志
        
        $log_source = C('PLATFORM')=='mobile' ? ($data['terminal']?'APP':'触屏版') : '网页版';
        write_members_log($user_info,'login','用户登录',$log_source);
        //记录路由
        C('apply.Analyze') && $this->record_route_group($user_info);
    }
    /**
     * 获取“用户名”，优先显示企业名称，没有企业信息显示求职者姓名
     */
    private function exchange_username($user){
        $uid = $user['uid'];
        $company_info = D('CompanyProfile')->where(array('uid'=>$uid))->find();
        if($company_info){
            return $company_info['companyname'];
        }else{
            $resume = D('Resume')->where(array('uid'=>$uid))->find();
            if($resume){
                return $resume['fullname'];
            }else{
                return $user['username'];
            }
        }
    }
    /**
     * 记录路由组
     */
    public function record_route_group($user){
        session('last_page_info',null);
        session('route_group_id',null);
        $logincount = D('MembersLog')->where(array('log_uid'=>$user['uid'],'log_type'=>'login','log_addtime'=>array('between',array(strtotime('today'),strtotime('tomorrow')))))->count();
        $data['utype'] = $user['utype'];
        $data['name'] = date('Y年m月d日').'第'.$logincount.'次登录';
        $data['uid'] = $user['uid'];
        $data['username'] = $this->exchange_username($user);
        $data['addtime'] = time();
        $data['endtime'] = 0;
        $data['during'] = 0;
        $insert_id = M('MembersRouteGroup')->add($data);
        if($insert_id){
            session('route_group_id',$insert_id);
        }
    }
    /**
     * [assign_update 刷新session]
     */
    public function update(){
        $field = 'uid,utype,username,password,email,mobile,last_login_time,terminal,registration_id';
        $user_info = M('Members')->field($field)->where(array('uid'=>$this->info['uid']))->find();
        if($user_info) {
            //记住登陆状态
            $this->assign_info($user_info);
            cookie($this->_session,array(md5('uid')=>$user_info['uid'], md5('password')=>$user_info['password']));
        }
    }
    /**
     * 会员中心关注微信公众号弹框是否弹出
     */
    public function wx_frame_status() {
        $cookie = cookie($this->_session);
        $cookie['wx_frame_status'] =1;
        cookie($this->_session,$cookie);
        $session = session($this->_session);
        $session['wx_frame_status'] =1;
        session($this->_session,$session);

    }
    public function getError(){
        return $this->_error;
    }
    /**
     * 退出
     */
    public function logout() {
        C('visitor',null);
        $this->info = null;
        $this->is_login = false;
        session($this->_session, null);
        cookie($this->_session, null);
    }
}