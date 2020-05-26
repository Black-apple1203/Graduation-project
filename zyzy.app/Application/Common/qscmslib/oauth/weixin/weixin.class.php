<?php
/**
 * 微信
 */
class WeiXinTOAuthV2 {
    public $appid = '';
    public $appSecret = '';
    public $scope = 'snsapi_login';
    private $_authorize_url = 'https://open.weixin.qq.com/connect/qrconnect';
    private $_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    private $_user_url = 'https://api.weixin.qq.com/sns/userinfo';
    function __construct($appid, $appSecret) {
        $this->appid = $appid;
        $this->appSecret = $appSecret;
    }
    function getAuthorizeURL($callback) {
        if(C('PLATFORM') == 'mobile'){
            $state = 'mobile';
        }
        $url = $this->_authorize_url . "?appid=".$this->appid . "&redirect_uri=" . urlencode($callback)."&response_type=code&scope=".$this->scope."&state=" . $state."#wechat_redirect";
        return $url;
    }
    function getAccessToken($keys) {
        $token_url = $this->_token_url."?appid=".$this->appid."&secret=".$this->appSecret."&code=".$keys["code"]."&grant_type=authorization_code";
        $response = $this->get_url_contents($token_url);
        $response = json_decode($response, true);
        if(!$response) exit('system error');
        if(!$response['access_token'] && $response['errcode']) exit($response['errmsg']);
        return $response;
    }
    function getUserInfo($access_token, $openid) {
        $get_user_info = $this->_user_url . "?access_token=".$access_token . "&openid=".$openid;
        $info = $this->get_url_contents($get_user_info);
        $info = preg_replace("/(\\\ue[0-9a-f]{3})/ie","addslashes('\\1')",$info);
        $arr = json_decode($info, true);
        return $arr;
    }
    function do_post($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    function get_url_contents($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $result =  curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}