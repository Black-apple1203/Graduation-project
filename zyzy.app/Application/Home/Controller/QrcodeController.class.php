<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 二维码生成
// +----------------------------------------------------------------------
namespace Home\Controller;
use Common\Controller\FrontendController;
class QrcodeController extends FrontendController{
	/**
	 * [index 跟据传值，生成二维码]
	 */
	public function index(){
		if($url = I('get.url','','trim')){
            ob_clean();
            $url = htmlspecialchars_decode($url,ENT_QUOTES);
            $download = I('get.download',0,'intval');
            Vendor('phpqrcode.phpqrcode');   
            $qrcode = new \QRcode();
            ob_clean();
            if($download==1){
                header("Content-type:application/x-png");
                header("Content-Disposition:attachment;filename=二维码.png");
                echo $qrcode::png($url,false, 'H', 8, 2);
            }else{
                $qrcode::png($url,false, 'H', 8, 2);
            }
		}
	}
    public function get_font_img(){
        $str = I('request.str','','trim');
        $str = decrypt($str,C('PWDHASH'));
        \Common\ORG\Image::buildString($str,array(100,50),'','png',0,false);
    }
}
?>