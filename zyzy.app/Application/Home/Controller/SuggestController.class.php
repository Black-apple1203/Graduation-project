<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class SuggestController extends FrontendController{
    //意见建议页
    public function index(){
        if(IS_POST){
            $data = I('post.');
            $r = D('Feedback')->addeedback($data);
            $this->ajaxReturn($r['state'],$r['msg']);
        }
        if (C('qscms_captcha_config.varify_suggest')==1 && C('qscms_captcha_open')==1){
        	$varify_suggest = 1;
        }else{
        	$varify_suggest = 0;
        }
        $this->assign('varify_suggest',$varify_suggest);
        $this->display();
    }
}    