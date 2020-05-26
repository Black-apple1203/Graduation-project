<?php
/**
 * 腾讯防水墙
 *
 * @author Tanxu
 */
namespace Common\qscmslib\captcha;
class TencentLib {
    protected $verify_url = 'https://ssl.captcha.qq.com/ticket/verify';
    public function __construct($Appid, $AppSecretKey) {
        $this->Appid = $Appid;
        $this->AppSecretKey  = $AppSecretKey;
    }
    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
    */
    private static function txcurl($url,$params=false,$ispost=0){
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // 关闭SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if( $ispost )
        {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }
    public function captcha($Ticket,$Randstr){
        $params = array(
            "aid" => $this->Appid,
            "AppSecretKey" => $this->AppSecretKey,
            "Ticket" => $Ticket,
            "Randstr" => $Randstr,
            "UserIP" => get_client_ip()
        );
        $paramstring = http_build_query($params);
        $content = self::txcurl($this->verify_url,$paramstring);
        $result = json_decode($content,true);
        if($result){
            if($result['response'] == 1){
                return true;
            }else{
                return $result['response'].":".$result['err_msg'];
            }
        }else{
            return "请求失败";
        }
    }
    public function get_config(){
        return array(
            'vid' => $this->Appid,  // 验证单元的VID
            'verify_type' => 'tencent',
        );
    }
}