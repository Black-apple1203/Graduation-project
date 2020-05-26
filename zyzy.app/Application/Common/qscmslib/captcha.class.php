<?php

/**
 * 第三方验证码
 *
 * @author andery
 */
namespace Common\qscmslib;
use Common\ORG\String;
use Common\qscmslib\captcha\GeetestLib;
use Common\qscmslib\captcha\VaptchaLib;
use Common\qscmslib\captcha\TencentLib;
class captcha{
    public static function generate($type = 'pc', $verifyName = 'verify'){
        switch (C('qscms_captcha_type')) {  // vaptcha|geetest
            case 'geetest':
                if(!$captcha = C('qscms_captcha_geetest')) exit('验证码错误，请先配置验证参数！');
                self::generate_geetest($captcha, $type, $verifyName);
                break;
            case 'vaptcha':
                if(!$captcha = C('qscms_captcha_vaptcha')) exit('验证码错误，请先配置验证参数！');
                self::generate_vaptcha($captcha, $type, $verifyName);
                break;
            case 'tencent':
                if(!$captcha = C('qscms_captcha_tencent')) exit('验证码错误，请先配置验证参数！');
                $vaptcha = new TencentLib($captcha['id'], $captcha['key']);
                exit(json_encode($vaptcha->get_config()));
                break;
            default:
                exit('请选择正确的第三方验证码服务商！');
        }
    }
    private static function generate_geetest($captcha, $type, $verifyName){
        $GtSdk = new GeetestLib($captcha['id'], $captcha['key']);
        $randval = String::randString(4);
        $data = array(
            'user_id' => $randval,
            'client_type' => $type == 'pc' ? 'web' : 'h5',
            #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address' => get_client_ip()
        );
        session($verifyName, array('id' => $randval, 'gtserver' => $GtSdk->pre_process($data, 1)));
        echo $GtSdk->get_response_str();
    }
    private static function generate_vaptcha($captcha, $type, $verifyName){
        $vaSdk = new VaptchaLib($captcha['id'], $captcha['key']);
        echo json_encode($vaSdk->get_config('click'));
    }
    public static function verify($type = 'pc', $verifyName = 'verify'){
        switch (C('qscms_captcha_type')) {  // vaptcha|geetest
            case 'geetest':
                if(!$captcha = C('qscms_captcha_geetest')) exit('验证码错误，请先配置验证参数！');
                return self::verify_geetest($captcha, $type, $verifyName);
                break;
            case 'vaptcha':
                if(!$captcha = C('qscms_captcha_vaptcha')) exit('验证码错误，请先配置验证参数！');
                return self::verify_vaptcha($captcha, $type, $verifyName);
                break;
            case 'tencent':
                if(!$captcha = C('qscms_captcha_tencent')) exit('验证码错误，请先配置验证参数！');
                return self::vaptcha_tencent($captcha, $type, $verifyName);
                break;
            default:
                exit('请选择正确的第三方验证码服务商！');
        }
    }
    /**
     * @return bool|string
     */
    private static function verify_geetest($captcha, $type, $verifyName){
        $GtSdk = new GeetestLib($captcha['id'], $captcha['key']);
        $session = session($verifyName);
        $data = array(
            'user_id' => $session['id'],
            'client_type' => $type == 'pc' ? 'web' : 'h5',
            #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address' => get_client_ip()
        );
        if ($session['gtserver'] == 1) {
            if ($result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'],
                $_POST['geetest_seccode'], $data)) {
                session($verifyName, null);
                return true;
            } else {
                return '验证码错误！';
            }
        } else {
            if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'],
                $_POST['geetest_seccode'])) {
                session($verifyName, null);
                return true;
            } else {
                return '验证码错误！';
            }
        }
    }
    private static function verify_vaptcha($captcha, $type, $verifyName){
        $vaSdk = new VaptchaLib($captcha['id'], $captcha['key']);
        return $vaSdk->validate($_POST['token'], '') ? true : false;
    }
    private static function vaptcha_tencent($captcha, $type, $verifyName){
        $vaptcha = new TencentLib($captcha['id'], $captcha['key']);
        return $vaptcha->captcha($_REQUEST['Ticket'],$_REQUEST['Randstr']);
    }
}