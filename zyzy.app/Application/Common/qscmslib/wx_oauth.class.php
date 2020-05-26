<?php
namespace Common\qscmslib;
class wx_oauth {
  public $access_token;
  public $openid;
    public function GetOpenid()
    {
      //通过code获得openid
      if (!isset($_GET['code'])){
        //触发微信返回code码
        $baseUrl = (C('HTTP_TYPE')?C('HTTP_TYPE'):'http://').$_SERVER['HTTP_HOST'];
        if (isset($_SERVER['REQUEST_URI']))     
          {        
            $baseUrl .= $_SERVER['REQUEST_URI'];    
          }
          else
        {    
          if (isset($_SERVER['argv']))        
          {           
            $baseUrl .= '?'. $_SERVER['argv'][0];
          }         
          else        
          {          
            $baseUrl .= '?'.$_SERVER['QUERY_STRING'];
          }  
          }    
        $baseUrl = rtrim($baseUrl,C('URL_HTML_SUFFIX'));
        $baseUrl = urlencode($baseUrl);
        $url = $this->__CreateOauthUrlForCode($baseUrl);
        Header("Location: $url");
        exit();
      } else {
        //获取code码，以获取openid
        $code = $_GET['code'];
        $openid = $this->getOpenidFromMp($code);
        $this->openid = $openid;
        return $openid;
      }
    }

    /**
     * 
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * 
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
      $urlObj["appid"] = C('qscms_weixin_appid');
      $urlObj["redirect_uri"] = "$redirectUrl";
      $urlObj["response_type"] = "code";
      $urlObj["scope"] = "snsapi_userinfo";
      $urlObj["state"] = "1#wechat_redirect";
      $bizString = $this->ToUrlParams($urlObj);
      return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     * 
     * 拼接签名字符串
     * @param array $urlObj
     * 
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
      $buff = "";
      foreach ($urlObj as $k => $v)
      {
        if($k != "sign"){
          $buff .= $k . "=" . $v . "&";
        }
      }
      
      $buff = trim($buff, "&");
      return $buff;
    }

    /**
     * 
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * 
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
      $url = $this->__CreateOauthUrlForOpenid($code);
      //初始化curl
      $ch = curl_init();
      //设置超时
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      
        // curl_setopt($ch,CURLOPT_PROXY, "0.0.0.0");
        // curl_setopt($ch,CURLOPT_PROXYPORT, 0);
      
      $res = curl_exec($ch);
      curl_close($ch);
      //取出openid
      $data = json_decode($res,true);
      $this->data = $data;
      $openid = $data['openid'];
      return $openid;
    }

    /**
     * 
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * 
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
      $urlObj["appid"] = C('qscms_weixin_appid');
      $urlObj["secret"] = C('qscms_weixin_appsecret');
      $urlObj["code"] = $code;
      $urlObj["grant_type"] = "authorization_code";
      $bizString = $this->ToUrlParams($urlObj);
      return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
}

