<?php
/**
 * 第三方登陆
 *
 * @author andery
 */
namespace Common\qscmslib;
class oauth {
    private $_type = '';
    private $_setting = array();
    private $_error = '';
    public function __construct($name) {
        $this->_type = $name ? $name : C('qscms_oauth_default');
        //加载登陆接口配置
        if(false === $oauth_list = F('oauth_list')){
            $oauth_list = D('Oauth')->oauth_cache();
        }
        if($oauth_list[$this->_type]){
            $this->_setting = unserialize($oauth_list[$this->_type]['config']);
            //导入接口文件
            include_once QSCMSLIB_PATH . 'oauth/' . $this->_type . '/' . $this->_type . '.php';
            $om_class = $this->_type . '_oauth';
            $this->_om = new $om_class($this->_setting);
        }
    }
    /**
     * 跳转到授权页面
     */
    public function authorize() {
        redirect($this->_om->getAuthorizeURL());
    }
    /**
     * 登陆回调
     */
    public function callbackLogin($request_args) {
        $user = $this->_om->getUserInfo($request_args);
        if(!$user){
            $this->_error = '用户信息获取失败！';
            return false;
        }
        $bind_user = $this->_checkBind($this->_type, $user);
        $status = M('Members')->where(array('uid'=>$bind_user['uid']))->field('status')->find();
        if($status['status'] == 2){
            $this->_error = '此帐号已暂停，请联系管理员！';
            return false;
        }
        if ($bind_user) {
            //已经绑定过则更新绑定信息 自动登陆
            $this->_updateBindInfo($user,$bind_user);
            //登陆
            $visitor = $this->_oauth_visitor();
            if(false === $visitor->login($bind_user['uid'])){
                $this->_error = $visitor->getError();
                return false;
            }
            $urls = array('1'=>'company/index','2'=>'personal/index');
            $url = $request_args['state'] == 'mobile'? 'mobile/'.$urls[$visitor->info['utype']] : $urls[$visitor->info['utype']];
        } else {
            //处理用户名
            if(M('Members')->where(array('username' => $user['keyname']))->getfield('uid')){
                $user['username'] = $user['keyname'] . '_' . mt_rand(99, 9999);
            }else{
                $user['username'] = $user['keyname'];
            }
            /*if($user['keyavatar_big']) {
                //下载原始头像到本地临时储存  用日期文件夹分类  方便清理
                $avatar_temp_root = C('qscms_attach_path') . 'avatar/temp/';
                $file_name = md5($user['keyid']) . '.jpg';
                if(!is_dir($avatar_temp_root)) mkdir($avatar_temp_root);
                $image_content = \Common\ORG\Http::fsockopenDownload($user['keyavatar_big']);
                file_put_contents($avatar_temp_root . $file_name, $image_content);
                $user['temp_avatar'] = $file_name;
            }*/
            $user['type'] = $this->_type;
            $user['bind_info']['keyname'] = $user['keyname'];
            $user['bind_info'] = serialize($user['bind_info']);
            //把第三方的数据存到COOKIE
            session('members_bind_info', $user);
            cookie('members_bind_info', $user);
            //$url = $request_args['state'] == 'mobile'&& !C('qscms_wap_domain')? 'mobile/members/apilogin_binding' : 'members/apilogin_binding';
            if($request_args['state'] == 'mobile'){
                $url = 'mobile/members/apilogin_binding';
            }else{
                $url = 'members/apilogin_binding';
            }
        }
        $url = $visitor ? U($url,array('uid'=>$visitor->info['uid'])) : U($url);
        return $url;
    }
    /**
     * 绑定回调
     */
    public function callbackBind($request_args) {
        $visitor = $this->_oauth_visitor();
        if(!$visitor->is_login) return U('members/login');
        $user_info = $visitor->info;
        $user = $this->_om->getUserInfo($request_args);
        $bind_user = $this->_checkBind($this->_type, $user);
        if ($bind_user['uid']) {
            $this->_error = '此帐号已经绑定过本站！';
            return false;
        }
        $user['uid'] = $user_info['uid'];
        $user['bind_info']['keyname'] = $user['keyname'];
        $user['utype'] = $user_info['utype'];
        if(false === $this->bindUser($user)){
            $this->_error = '帐号绑定失败，请重新操作！';
            return false;
        }
        if($request_args['state'] == 'mobile'){
            $urls = array('1'=>'company/com_binding','2'=>'personal/per_binding');
            $url = !C('qscms_wap_domain') ? 'mobile/'.$urls[C('visitor.utype')] : $urls[C('visitor.utype')];
        }else{
            $urls = array('1'=>'company/user_security','2'=>'personal/user_safety');
            $url = $urls[C('visitor.utype')];
        }
        return U($url);
    }
    /**
     * 更新绑定信息
     */
    private function _updateBindInfo($user,$bind_user) {
        $map['keyid'] = $user['keyid'];
        $user['unionid'] && $map['unionid'] = $user['unionid'];
        $map['_logic'] = 'OR';
        $where['_complex'] = $map;
        $where['type'] = $this->_type;
        $info = array_merge(unserialize($bind_user['info']),$user['bind_info']);
        return M('MembersBind')->where($where)->save(array('uid'=>$bind_user['uid'],'unionid'=>$user['unionid'],'keyid'=>$user['keyid'],'info'=>serialize($info)));
    }
    /**
     * 绑定帐号
     */
    public function bindUser($user) {
        $bind_user = array(
            'uid'           => $user['uid'],
            'type'          => $this->_type,
            'keyid'         => $user['keyid']?:'',
            'bindingtime'   => time(),
            'unionid'       => $user['unionid']?:'',
            'is_bind'       => 1
        );
        if($user['unionid'] && $userInfo = M('MembersBind')->where(array('type'=>$this->_type,'unionid'=>$user['unionid']))->find()){
            !is_array($user['bind_info']) && $user['bind_info'] = unserialize($user['bind_info']);
            $bind_user['info'] = serialize(array_merge(unserialize($userInfo['info']),$user['bind_info']));
            $reg = M('MembersBind')->where(array('type'=>$this->_type,'unionid'=>$user['unionid']))->save($bind_user);
        }else{
            $bind_user['info'] = !is_array($user['bind_info'])?$user['bind_info']:serialize($user['bind_info']);
            $info = unserialize($bind_user['info']);
            $infoarr = array('access_token'=>$info['access_token'],'expires_in'=>$info['expires_in'],'refresh_token'=>$info['refresh_token'],'keyname'=>emoji_unicode($info['keyname']));
            $bind_user['info'] = serialize($infoarr); 
            $reg = M('MembersBind')->add($bind_user);
        }
        if(false !== $reg){
            switch($this->_type){
                case 'qq':
                    $task = 'binding_qq';
                    break;
                case 'sina':
                    $task = 'binding_weibo';
                    break;
                case 'weixin':
                    $task = 'binding_weixin';
                    break;
                default:
                    $task = 'binding_qq';
                    break;
            }
            D('TaskLog')->do_task($user,$task);
        }
        return $reg;
    }
    /**
     * 检测用户是否已经绑定过本站
     */
    public function _checkBind($type, $user) {
        $map['keyid'] = $user['keyid'];
		$map['openid'] =$user['openid'];
        $user['unionid'] && $map['unionid'] = $user['unionid'];
        $map['_logic'] = 'OR';
        $where['_complex'] = $map;
        $where['type'] = $type;
        $info = M('MembersBind')->where($where)->find();
        return $info['uid'] ? $info : false;
    }
    /**
     * 访问者
     */
    private function _oauth_visitor() {
        return new \Common\qscmslib\user_visitor();
    }
    /**
     * 返回需要的参数
     */
    public function NeedRequest() {
        return $this->_om->NeedRequest();
    }
    /**
     * 错误
     */
    public function getError(){
        return $this->_error;
    }
}