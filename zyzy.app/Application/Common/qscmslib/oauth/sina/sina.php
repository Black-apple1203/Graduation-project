<?php
require_once dirname(__FILE__) . '/saetv2.ex.class.php';
class sina_oauth
{
    private $_need_request = array('code','state');

    public function __construct($setting) {
        if(C('qscms_site_dir') =='/'){
                $this->redirect_uri = C('qscms_site_domain').U('callback/oauth', array('mod'=>'sina'), '', '', false);
        }else{
                $this->redirect_uri = C('qscms_site_domain').C('qscms_site_dir').U('callback/oauth', array('mod'=>'sina'), '', '', false);
        }
        $this->setting = $setting;
    }
    public function getAuthorizeURL() {
        $oauth = new SaeTOAuthV2($this->setting['app_key'], $this->setting['app_secret']);
        if(C('PLATFORM') == 'mobile'){
            $display = $state = 'mobile';
        }
        return $oauth->getAuthorizeURL($this->redirect_uri,'code',$state,$display);
    }
    //获取新浪帐号信息
    public function getUserInfo($request_args) {
        is_null($request_args['code']) && exit();
        $oauth = new SaeTOAuthV2($this->setting['app_key'], $this->setting['app_secret']);
        $keys = array('code'=>$request_args['code'], 'redirect_uri'=>$this->redirect_uri);
        $token = $oauth->getAccessToken('code', $keys);
        $client = new SaeTClientV2($this->setting['app_key'], $this->setting['app_secret'], $token['access_token']);
        $user = $client->show_user_by_id($token['uid']);
        $result['keyid'] = $user['id'];
        $result['keyname'] = $user['name'];
        $result['keyavatar_small'] = $user['profile_image_url'];
        $result['keyavatar_big'] = $user['avatar_large'];
        $result['bind_info'] = $token;
        return $result;
    }
    /**
     * 获取好友列表 
     */
    public function getFriends($bind_user, $page, $count) {
        $info = unserialize($bind_user['info']);
        $client = new SaeTClientV2($this->setting['app_key'], $this->setting['app_secret'], $info['access_token']);
        $res = $client->bilateral($bind_user['keyid'], $page, $count);
        $friends = $users = array();
        foreach ($res['users'] as $u) {
            $users[] = array(
                'id'    => $u['id'],
                'sid'   => $u['screen_name'], 
                'name'  => $u['name'], 
                'avatar'=> $u['profile_image_url']
            );
        }
        $friends['users'] = $users;
        $friends['total_number'] = $res['total_number'];
        return $friends;
    }
    /**
     * 推送信息
     */
    public function send($bind_user, $data) {
        $info = unserialize($bind_user['info']);
        $client = new SaeTClientV2($this->setting['app_key'], $this->setting['app_secret'], $info['access_token']);
        try {
            isset($data['url']) && $data['content'] = $data['content'] . $data['url'];
            if (isset($data['img']) && !empty($data['img'])) {
                return $client->upload($data['content'], $data['img']);
            } else {
                return $client->update($data['content']);
            }
        }catch(Exception $e){}
    }
    /**
     * 关注
     */
    public function follow($bind_user, $uid) {
        $info = unserialize($bind_user['info']);
        $client = new SaeTClientV2($this->setting['app_key'], $this->setting['app_secret'], $info['access_token']);
        try {
            return $client->follow_by_id($uid);
        }catch(Exception $e){}
    }
    public function NeedRequest() {
        return $this->_need_request;
    }
}