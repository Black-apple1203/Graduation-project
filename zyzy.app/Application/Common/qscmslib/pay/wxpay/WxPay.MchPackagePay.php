<?php
require_once "WxPay.Config.php";
class MchPackagePay{
    protected $_payment_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    protected $_curl_timeout = 30;
    protected $_error = '';
    public function __construct(){}
    /**
     * API 参数
     * @var array
     * 'mch_appid'         # 公众号APPID
     * 'mchid'             # 商户号
     * 'device_info'       # 设备号
     * 'nonce_str'         # 随机字符串
     * 'partner_trade_no'  # 商户订单号
     * 'openid'            # 收款用户openid
     * 'check_name'        # 校验用户姓名选项 针对实名认证的用户
     * 're_user_name'      # 收款用户姓名
     * 'amount'            # 付款金额
     * 'desc'              # 企业付款描述信息
     * 'spbill_create_ip'  # Ip地址
     * 'sign'              # 签名
     */
    public function postXmlSSL($parameters){
        $parameters['wxappid'] = WxPayConfig::$appid;
        $parameters['mch_id']     = WxPayConfig::$mchid;
        $xml = $this->createXml($parameters);
        $response = $this->postXmlSSLCurl($xml,$this->_payment_url,$this->_curl_timeout);
        return $response;
    }
    /**
     * 生成请求xml数据
     * @return string
     */
    protected function createXml($parameters){
        $parameters['nonce_str'] = $this->createNoncestr();
        $parameters['sign']      = $this->getSign($parameters);
        return $this->arrayToXml($parameters);
    }
    /**
     *  作用：产生随机字符串，不长于32位
     */
    protected function createNoncestr( $length = 32 ){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        }  
        return $str;
    }
    /**
     *  作用：生成签名
     */
    protected function getSign($Obj){
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".WxPayConfig::$key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    /**
     *  作用：格式化参数，签名过程需要使用
     */
    protected function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
               $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) 
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    /**
     *  作用：array转xml
     */
    protected function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">"; 

             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
    /**
     *     作用：使用证书，以post方式提交xml到对应的接口url
     */
    protected function postXmlSSLCurl($xml,$url,$second=30){
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, getcwd().'/data/cert/apiclient_cert.pem');
        //默认格式为PEM，可以注释
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, getcwd().'/data/cert/apiclient_key.pem');
        // curl_setopt($ch,CURLOPT_CAINFO, getcwd().'/data/cert/rootca.pem');
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            $xml_array =  (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($xml_array['result_code']!='SUCCESS'){
                $this->_error = $xml_array['err_code_des'];
                return false;
            }else{
                return true;
            }
        }else {
            $error = curl_errno($ch);
            $this->_error = "curl出错，错误码:".$error;
            curl_close($ch);
            return false;
        }
    }
    public function getError(){
        return $this->_error;
    }
}