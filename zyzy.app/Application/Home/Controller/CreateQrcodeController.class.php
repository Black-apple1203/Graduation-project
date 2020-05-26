<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class CreateQrcodeController extends FrontendController{
	public function _initialize() {
        parent::_initialize();
    }
	public function index(){
        $gate_config = require_once(QSCMSLIB_PATH.'gate/gate_config.php');
        $url = 'http://gate.74cms.com/Pad/Index/save_qrcode';
        foreach ($gate_config['terminal_list'] as $key => $value) {
            $scene_str_in = 'gate_in_'.$value['uid'].'_'.$value['secret'];
            $weixin_img_src_in = \Common\qscmslib\weixin::create_forever_qrcode($scene_str_in,true);
            
            $scene_str_out = 'gate_out_'.$value['uid'].'_'.$value['secret'];
            $weixin_img_src_out = \Common\qscmslib\weixin::create_forever_qrcode($scene_str_out,true);
            https_request($url,array('uid'=>$value['uid'],'weixin_img_src_in'=>$weixin_img_src_in,'weixin_img_src_out'=>$weixin_img_src_out));
        }
	}
}
?>