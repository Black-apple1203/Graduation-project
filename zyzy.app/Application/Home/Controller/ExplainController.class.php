<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class ExplainController extends FrontendController{
    public function explain_show(){
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
        $this->display();
    }
}