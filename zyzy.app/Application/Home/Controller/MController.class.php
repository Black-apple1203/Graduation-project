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
// | ModelName: 
// +----------------------------------------------------------------------
namespace Home\Controller;
use Common\Controller\FrontendController;
class MController extends FrontendController{
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url());
		}
        $type = I('get.type','android','trim');
        if(!in_array($type, array('ios','android','touch','weixin'))){
            $this->error('参数错误！');
        }
        $this->assign('type',$type);
        $this->display('M/'.$type);
    }
}
?>