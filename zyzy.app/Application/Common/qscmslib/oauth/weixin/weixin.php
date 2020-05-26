<?php
require_once dirname(__FILE__) . '/weixin.class.php';
class weixin_oauth
{
    private $_need_request = array('code', 'state');
    public function __construct($setting) {
        //组装回调地址
        $dir = str_replace('/','',C('qscms_site_dir'));
        $dir = $dir ? C('qscms_site_dir') : '';
        if(C('PLATFORM') == 'mobile'){
            $this->redirect_uri = C('qscms_site_domain').$dir.U('mobile/callback/oauth', array('mod'=>'weixin'),'','',true);
        }else{
            $this->redirect_uri = C('qscms_site_domain').$dir.U('callback/oauth', array('mod'=>'weixin'),'','',true);
        }
        $this->redirect_uri = str_replace('/index.php','',$this->redirect_uri);
        $this->setting = $setting;
    }
    /**
     * 获取授权地址
     */
    function getAuthorizeURL() {
        $oauth = new WeiXinTOAuthV2($this->setting['app_id'], $this->setting['app_secret']);
        return $oauth->getAuthorizeURL($this->redirect_uri);
    }
    /**
     * 获取用户信息
     */
    public function getUserInfo($request_args) {
        $oauth = new WeiXinTOAuthV2($this->setting['app_id'], $this->setting['app_secret']);
        $keys = array('code'=>$request_args['code'], 'state'=>$request_args['state'], 'redirect_uri'=>$this->redirect_uri);
        $token = $oauth->getAccessToken($keys);
        $user = $oauth->getUserInfo($token["access_token"], $token['openid']);
        $result['keyid'] = $user['openid'];
        $result['keyname'] = emoji_unicode($user['nickname']);
        $result['keyavatar_big'] = $user['headimgurl'];
        $result['unionid'] = $user['unionid'];
        $result['bind_info'] = array('keyid'=>$user['openid'],'keyname'=>$user['nickname'],'sex'=>$user['sex'],'city'=>$user['city'],'province'=>$user['province'],'country'=>$user['country'],'headimgurl'=>$user['headimgurl']);
        return $result;
    }
    public function NeedRequest() {
        return $this->_need_request;
    }
}