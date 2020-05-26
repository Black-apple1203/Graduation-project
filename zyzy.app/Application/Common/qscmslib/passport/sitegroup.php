<?php
class sitegroup_passport
{
    protected $_error = 0;
    protected $synlogin_url = '/index.php?m=home&c=api&a=synLogin';
    protected $syndelete_url = '/index.php?m=home&c=api&a=synDelete';
    protected $synregister_url = '/index.php?m=home&c=api&a=synRegister';
    protected $synedit_url = '/index.php?m=home&c=api&a=synEdit';
    protected $synlogout_url = '/index.php?m=home&c=api&a=synLogout';
    protected $gettoken_url = '/index.php?m=home&c=api&a=getToken';
    protected $check_url = '/index.php?m=home&c=api&a=checkSole';
    protected $auth_url = '/index.php?m=home&c=api&a=auth';
    protected $getuser_url = '/index.php?m=home&c=api&a=getUserInfo';
    protected $unbind_mobile_url = '/index.php?m=home&c=api&a=unbindMobile';
    protected $get_subsite_url = '/index.php?m=home&c=api&a=getSubsite';
    public function __construct() {
        
    }
    /**
     * 获取插件基本信息
     */
    public function get_info() {
        return array(
            'code' => 'sitegroup', //插件代码必须和文件名保持一致
            'name' => '站群系统会员中心', //整合插件名称
            'desc' => 'sitegroup',
            'version' => '4.2.0', //整合插件的版本
            'author' => '74cms' //开发者
        );
    }
    /**
     * 注册新用户
     */
    public function register($data) {
        $user = $data;
        unset($user['uid'],$user['pwd_hash']);
        $reg = $this->https_request($this->synregister_url,$user);
        if(false !== $reg){
            $data['sitegroup_uid'] = $reg['data']['user']['sitegroup_uid'];
            cookie('members_sitegroup_register',$reg['data']['synRegister']);
            return $data;
        }
        return false;
    }
    /**
     * 编辑用户信息
     */
    public function edit($uid,$data,$old_password,$force = false) {
        $user = $data;
        if(!$info = M('Members')->find($uid)){
            $this->_error = L('auth_null');
            return false;
        }
        if(!$info['sitegroup_uid']){
            $this->_error = '帐号还没有关联站群系统，不能同步信息！';
            return false;
        }
        $user['uid'] = $info['sitegroup_uid'];
        $user['old_password'] = $old_password;
        $reg = $this->https_request($this->synedit_url,$user);
        if(false !== $reg){
            cookie('members_sitegroup_edit',$reg['data']);
            return $data;
        }
        return false;
    }
    /**
     * 删除用户
     */
    public function delete($uids,$type = array()) {
        if(!$uids){
            $this->error = '请选择要删除的用户UID！';
            return true;
        }
        $user['uids'] = implode(',',$uids);
        $type && $user = array_merge($user,$type);
        $reg = $this->https_request($this->syndelete_url,$user);
        if(false !== $reg){
            cookie('members_sitegroup_action',$reg['data']);
            return $reg['data'];
        }
        return false;
    }
    public function get($flag, $type = 'username') {
        if ($type == 'uid') {
            if(!$info = M('Members')->find($flag)){
                $this->_error = L('auth_null');
                return false;
            }
            $flag = $info['sitegroup_uid'];
        }
        $data['type'] = $type;
        $data['value'] = $flag;
        $reg = $this->https_request($this->getuser_url,$data);
        if(false !== $reg) return $reg['data'];
        return false;
    }
    /**
     * 验证用户
     */
    public function auth($username, $password) {
        $user['username'] = $username;
        $user['password'] = $password;
        $reg = $this->https_request($this->auth_url,$user);
        if(false !== $reg) return $reg['data'];
        return false;
    }
    /**
     * [unbind_mobile 手机号解绑]
     */
    public function unbind_mobile($mobile){
        $user['mobile'] = $mobile;
        $reg = $this->https_request($this->unbind_mobile_url,$user);
        if(false !== $reg){
            cookie('members_sitegroup_unbind_mobile',$reg['data']);
            return $reg['data'];
        }
        return false;
    }
    /**
     * [get_subsite 获取站群系统分站列表]
     */
    public function get_subsite(){
        $reg = $this->https_request($this->get_subsite_url);
        if(false !== $reg){
            return $reg['data'];
        }
        return false;
    }
    /**
     * 同步登陆
     */
    public function synlogin($uid,$expire=0) {
        $sitegroup_uid = M('Members')->where(array('uid'=>$uid))->getField('sitegroup_uid');
        if(!$sitegroup_uid){
            $this->_error = '帐号还没有关联站群系统，不能同步登录！';
            return false;
        }
        $user['uid'] = $sitegroup_uid;
        $user['expire'] = $expire;
        $reg = $this->https_request($this->synlogin_url,$user);
        if(false !== $reg){
            cookie('members_sitegroup_action',$reg['data']);
            return $reg['data'];
        }
        return false;
    }
    public function synlogout() {
        $reg = $this->https_request($this->synlogout_url);
        if(false !== $reg){
            cookie('members_sitegroup_action',$reg['data']);
            return $reg['data'];
        }
        return false;
    }
    /**
     * 检测用户邮箱唯一
     */
    public function check_email($email) {
        return $this->_check($email,'email');
    }
    /**
     * 检测手机唯一
     */
    public function check_mobile($mobile) {
        return $this->_check($mobile,'mobile');
    }
    /**
     * 检测用户名唯一
     */
    public function check_username($username) {
        return $this->_check($username,'username');
    }
    /**
     * [getToken 获取通讯token]
     */
    protected function _getToken(){
        if($token = S('_sitegroup_token')){
            return $token;
        }else{
            $reg = $this->https_request($this->gettoken_url,array('code'=>encrypt(C('qscms_sitegroup_secret_key'),C('qscms_sitegroup_secret_key'))));
            if(false !== $reg){
                S('_sitegroup_token',$reg['data'],7200);
                return $reg['data'];
            }
            return false;
        }
    }
    /**
     * [_check 验证帐号是否存在]
     */
    protected function _check($data,$type){
        $token = $this->_getToken();
        $reg = $this->https_request($this->check_url,array('type'=>$type,'value'=>$data));
        if(false !== $reg){
            return $reg['data'];
        }
        return false;
    }
    public function https_request($url,$data){
        if(!$data['code']) $data['token'] = $this->_getToken();
        $data['appid'] = C('qscms_sitegroup_id');
        $reg = https_request(C('qscms_sitegroup_domain').$url,$data);
        if(false !== $reg){
            $reg = json_decode($reg,true);
            if($reg['status'] == 1){
                $this->_error = $reg['msg'];
                return $reg;
            }else{
                $this->_error = $reg['msg'];
                return false;
            }
        }else{
            $this->_error = '通讯错误！';
            return false;
        }
    }
    public function get_error() {
        return $this->_error;
    }
}