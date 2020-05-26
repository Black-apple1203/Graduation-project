<?php
/**
 * 内置用户中心连接接口
 * author: andery@foxmail.com
 */
class default_passport
{

    private $_error = 0;

    public function __construct() {
        $this->_user_mod = D('Members');
    }

    public function get_info() {
        return array(
            'code' => 'default', //插件代码必须和文件名保持一致
            'name' => '74cms', //整合插件名称
            'desc' => '74cms 默认会员系统',
            'version' => '1.0', //整合插件的版本
            'author' => '74cms', //开发者
        );
    }

    /**
     * 注册新用户
     * $type (mobile:手机号,username:用户名,email:邮箱)
     */
    public function register($data){
        return $data;
    }

    /**
     * 修改用户资料
     */
    public function edit($uid,$data,$old_password='',$force = false) {
        if(!$info = $this->get($uid)){
            $this->_error = L('auth_null');
            return false;
        }
        /*if ($old_password) {//先验证用户合法性
            if ($info['password'] != D('Members')->make_md5_pwd($old_password,$info['pwd_hash'])) {
                $this->_error = '原密码错误！';
                return false;
            }
            if($old_password === $data['password']){
                $this->_error = '原始密码与新密码不能一致！';
                return false;
            }
        }*/ //****************chm 注释
        foreach ($data as $key => $val) {
            if($val != $info[$key]) $data['_log'][] = $key;
        }
        $data['pwd_hash'] = $info['pwd_hash'];
        $data['utype'] = $info['utype'];
        $data['org_uname'] = $info['username'];
        return $data;
    }
    /**
     * 删除用户
     */
    public function delete() {
        return true;
    }

    public function get($flag, $is_name = false) {
        if ($is_name) {
            $map = array('username' => $flag);
        } else {
            $map = array('uid' => intval($flag));
        }
        return M('Members')->where($map)->find();
    }

    /**
     * 登陆验证
     */
    public function auth($username,$password) {
        $mobile_mod = D('Members');
        $account_type='username';
        $account_type_audit = false;
        if (fieldRegex($username,'email'))
        {
            $account_type='email';
        }
        elseif (fieldRegex($username,'mobile'))
        {
            $account_type='mobile';
        }
        $check_map[$account_type] = array('eq',$username);
        if($user = $mobile_mod->where($check_map)->find()){
            $pwd = $mobile_mod->make_md5_pwd($password,$user['pwd_hash']);
            if($pwd == $user['password']){
                if($user['status'] == 2){
                    $this->_error = L('account_disabled');
                    return false;
                }elseif($user['status'] == 0){
                    $this->_error = L('auth_activation');
                    return false;
                }
                $user['password'] = $password;
                return $user;
            }
            $this->_error = L('auth_password_failed');
            return false;
        }
        $this->_error = L('auth_null');
        return false;
    }
    /**
     * [unbind_mobile 手机号解绑]
     */
    public function unbind_mobile($mobile){
        return true;
    }
    /**
     * [get_subsite 获取站群系统分站列表]
     */
    public function get_subsite(){
        return true;
    }
    /**
     * 同步登陆
     */
    public function synlogin() {}

    /**
     * 同步退出
     */
    public function synlogout() {}

    /**
     * 检测用户邮箱唯一
     */
    public function check_email($email) {
        if ($this->_user_mod->field('uid')->where(array('email'=>$email))->find()) {
            return false;
        }
        return $email;
    }
    /**
     * 检测手机唯一
     */
    public function check_mobile($mobile) {
    	if ($this->_user_mod->field('uid')->where(array('mobile'=>$mobile))->find()) {
    		return false;
    	}
    	return $mobile;
    }
    /**
     * 检测用户名唯一
     */
    public function check_username($username) {
        if ($this->_user_mod->field('uid')->where(array('username'=>$username))->find()) {
            return false;
        }
        return $username;
    }

    public function get_error() {
        return $this->_error;
    }
}