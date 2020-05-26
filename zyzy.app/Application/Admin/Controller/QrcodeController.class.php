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
namespace Admin\Controller;
use Common\Controller\BackendController;
class QrcodeController extends BackendController{
	/**
	 * [index 跟据传值，生成二维码]
	 */
	public function index(){
		if($url = I('get.url','','trim')){
            ob_clean();
            $url = htmlspecialchars_decode($url,ENT_QUOTES);
            $download = I('get.download',0,'intval');
            $px = I('get.px',0,'intval');
            $size = round($px/44.95, 2);
            Vendor('phpqrcode.phpqrcode');   
            $qrcode = new \QRcode();
            ob_clean();
            if($download==1){
                header("Content-type:application/x-png");
                header("Content-Disposition:attachment;filename=二维码.png");
                echo $qrcode::png($url,false, 'H', $size, 2);
            }else{
                $qrcode::png($url,false, 'H', $size, 2);
            }
		}
	}
    /**
     * [get_weixin_qrcode 登录生成二维码 h]
     */
    public function get_weixin_qrcode(){
         if(!C('qscms_weixin_apiopen')) $this->ajaxReturn(0,'未配置微信参数！');
         $type = I('get.type','','trim');
         if($type!='login'){
             $this->ajaxReturn(0,'请正确选择二维码生成类型！');
            }
        $option['type']=$type;
        $option['width']=240;
        $option['height']=240;
        $option['token_admin']=1;
        $this->ajaxReturn(1,'微信登录二维码生成！',\Common\qscmslib\weixin::qrcode_img($option));
       
    }
    /**
     * [get_weixin_qrcode 管理员绑定生成二维码 h]
     */
    public function get_oauth_weixin_qrcode(){
         if(!C('qscms_weixin_apiopen')) $this->ajaxReturn(0,'未配置微信参数！');
         $type = I('get.type','','trim');
        if($type!='bind'){
         $this->ajaxReturn(0,'请正确选择二维码生成类型！');
        }
        $option['type']=$type;
        $option['width']=240;
        $option['height']=240;
        $option['token_admin']=1;
        if(I('request.id','','intval')){
            $option['id']=I('request.id','','intval');
         }else{
             $this->ajaxReturn(0,'未发现管理员！');
         }
        $this->ajaxReturn(1,'微信绑定二维码生成！',\Common\qscmslib\weixin::qrcode_img($option));
    }
}
?>