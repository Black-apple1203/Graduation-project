<?php
/**
 * 腾讯QQ
 */
class QqTOAuthV2 {
    public $appid = '';
    public $appkey = '';
    public $scope = 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo';
    private $_authorize_url = 'https://graph.qq.com/oauth2.0/authorize';
    function __construct($appid, $appkey) {
        $this->appid = $appid;
        $this->appkey = $appkey;
    }
    function getAuthorizeURL($callback) {
        if(C('PLATFORM') == 'mobile'){
            $state = 'mobile';
        }
        $url = $this->_authorize_url . "?response_type=code&client_id=".$this->appid . "&redirect_uri=" . urlencode($callback)."&state=" . $state."&scope=".$this->scope;
        return $url;
    }
    function getAccessToken($keys) {
        $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
          . "client_id=" . $this->appid . "&redirect_uri=" . urlencode($keys['redirect_uri'])
          . "&client_secret=" . $this->appkey . "&code=" . $keys["code"];

        $response = $this->get_url_contents($token_url);
        if (!$response) {
            exit('system error');
        }

        if (strpos($response, "callback") !== false) {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);
            if (isset($msg->error))
            {
                echo "<h3>error:</h3>" . $msg->error;
                echo "<h3>msg  :</h3>" . $msg->error_description;
                exit;
            }
        }

        $params = array();
        parse_str($response, $params);
        return $params;
    }
    function getOpenid($access_token) {
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $access_token;
        $str  = file_get_contents($graph_url);
        if (strpos($str, "callback") !== false) {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
        }
        $user = json_decode($str);
        if (isset($user->error)) {
            echo "<h3>error:</h3>" . $user->error;
            echo "<h3>msg  :</h3>" . $user->error_description;
            exit;
        }
        return $user->openid;
    }
    function getUserInfo($access_token, $openid) {
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=".$access_token
            . "&oauth_consumer_key=".$this->appid
            . "&openid=".$openid
            . "&format=json";
        $info = $this->get_url_contents($get_user_info);
        $arr = json_decode($info, true);
        return $arr;
    }
	function getunionid($access_token) {
        $get_user_info = "https://graph.qq.com/oauth2.0/me?"
            . "access_token=".$access_token
            . "&unionid=1";
        $info = $this->get_url_contents($get_user_info);
		if (strpos($info, "callback") !== false) {
            $lpos = strpos($info, "(");
            $rpos = strrpos($info, ")");
            $info  = substr($info, $lpos + 1, $rpos - $lpos -1);
        }
	
        $arr = json_decode($info);
        return $arr->unionid;
    }

    /**
     * 发说说
     */
    function add_topic($access_token, $openid, $topic) {
        $url  = "https://graph.qq.com/shuoshuo/add_topic";
        $data = "access_token=".$access_token
            ."&oauth_consumer_key=".$this->appid
            ."&openid=".$openid
            ."&format=".$topic["format"]
            ."&richtype=".$topic["richtype"]
            ."&richval=".urlencode($topic["richval"])
            ."&con=".urlencode($topic["con"])
            ."&lbs_nm=".$topic["lbs_nm"]
            ."&lbs_x=".$topic["lbs_x"]
            ."&lbs_y=".$topic["lbs_y"]
            ."&third_source=".$topic["third_source"];
        $ret = $this->do_post($url, $data);
        return $ret;
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
//        if (ini_get("allow_url_fopen") == "1")
//            return file_get_contents($url);

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