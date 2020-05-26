<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class NewsController extends FrontendController{
	public function _initialize() {
		parent::_initialize();
		/*
		//标题、描述、关键词
		$this->_config_seo();*/
	}
	//新闻资讯
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'News','a'=>'index')));
		}
		C('TOKEN_ON',true);
		$this->display();
	}
	
	//新闻详情
	public function news_show(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'News','a'=>'show','params'=>'id='.intval($_GET['id']))));
		}
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
		$this->display();
	}
	//资讯列表
	public function news_list(){
		if (!M('Article')->autoCheckToken($_POST)){
            $this->error("验证错误！");
        }
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'News','a'=>'index')));
		}
		C('TOKEN_ON',true);
		$this->display();
	}
	public function ajax_new_article_list(){
		$page = I('get.page',0,'intval');
		$start = $page*5;
		$this->assign('start',$start);
		$html = $this->fetch('ajax_new_article_list');
		if($html){
			$this->ajaxReturn(1,'',$html);
		}else{
			$this->ajaxReturn(0);
		}
	}
}
?>