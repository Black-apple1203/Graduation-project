<?php

namespace Home\Controller;

use Common\Controller\FrontendController;
use Common\ORG\String;
use Common\qscmslib\captcha\VaptchaLib;

class CaptchaController extends FrontendController {
    public function _empty() {
        $type = I('get.type', 'pc', 'trim');
        \Common\qscmslib\captcha::generate($type);
    }

    /*
	校验验证码
    */
    public function checkCode() {
        $type = I('get.type', 'pc', 'trim');
        if (true === $reg = \Common\qscmslib\captcha::verify($type)) {
            $this->ajaxReturn(1, '验证通过！');
        } else {
            $this->ajaxReturn(0, $reg);
        }
    }

    public function vaptcha_outage() {
        // callback action v challenge
        $captcha = C('qscms_captcha_vaptcha');
        if (!$captcha) {
            exit(json_encode(array('code' => '0104')));
        }
        $vaptcha = new VaptchaLib($captcha['id'], $captcha['key']);
        $challenge = I('param.challenge', null, 'strval') ? I('param.challenge') : String::uuid();
        $callback = I('param.callback');
        if (I('param.action') === 'get') {
			if(isset($_REQUEST['offline_action'])){
				if(isset($_GET['v'])) {
					echo Vaptcha::offline($_GET['offline_action'], $_GET['callback'], $_GET['v'], $_GET['knock']);
				} else {
					echo Vaptcha::offline($_GET['offline_action'], $_GET['callback']);
				}
			} else {
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
					$token = $_POST['token'];
					$scene = $_POST['scene'];
					$result =  Vaptcha::validate($token, $scene);
					exit("{$callback}({$result})");
				}
			}
        }
        if (I('param.action') === 'verify') {
            $v = I('param.v');
            $opt = json_encode($vaptcha->outage_verify($challenge, $v));
            exit("{$callback}({$opt})");
        }

        exit(json_encode(array('code' => '0104')));
    }
}