<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class HelpController extends FrontendController{
    //帮助首页
    public function index() {
        C('TOKEN_ON',true);
        $this->display();
    }
    //帮助列表页
    public function help_list(){
        $_GET['key'] = I('request.key','','trim');
        if (!M('Help')->autoCheckToken($_POST)){
            $this->error("验证错误！");
        }
        $this->display();
    }
    //帮助详细页
    public function help_show(){
        $this->display();
    }
}