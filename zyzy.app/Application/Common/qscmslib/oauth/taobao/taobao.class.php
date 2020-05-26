<?php

class TaobaoTOAuthV2 {

    public $api_key = '';
    public $secret_key = '';
    private $_authorize_url = 'https://oauth.taobao.com/authorize';
    private $_accesstoken_url = 'https://oauth.taobao.com/token';

    function __construct($api_key, $secret_key) {
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
    }

    function getAuthorizeURL($callback) {
        if(C('PLATFORM') == 'mobile'){
            $mobile = '&view=wap&state=mobile';
        }
        $url = $this->_authorize_url . '?response_type=code&client_id=' . $this->api_key . '&redirect_uri=' . urlencode($callback).$mobile;
        return $url;
    }

    function getAccessToken($keys) {
        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->api_key,
            'client_secret' => $this->secret_key,
            'code' => $keys['code'],
            'redirect_uri' => $keys['redirect_uri']
        );
        $token = json_decode($this->curl($this->_accesstoken_url,$params), true);
        return $token;
    }

    function curl($url, $postFields = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (is_array($postFields) && 0 < count($postFields)) {
            $postBodyString = "";
            foreach ($postFields as $k => $v) {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
        }
        $reponse = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }

}