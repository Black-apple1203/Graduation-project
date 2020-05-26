<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class JobsController extends FrontendController{
	public function _initialize(){
		parent::_initialize();
	}
	/**
	 * [com 公司首页]
	 */
	public function com_show(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'comshow','params'=>'id='.intval($_GET['id']))));
		}
		if(I('get.style')){
			$tpl = I('get.style','','trim');
		}else{
			$company=D('company_profile')->field('tpl')->where(array('id'=>I('get.id')))->select(); 
			$tpl = $company[0]['tpl']?$company[0]['tpl']:C('qscms_tpl_company');	
		}
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
		$this->display(MODULE_PATH.'View/tpl_company/'.$tpl.'/com_show.html');
	}
	/**
	 * [com_jobs_list 企业职位列表]
	 */
	public function com_jobs_list(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'comshow','params'=>'id='.intval($_GET['id']))));
		}
		if(I('get.style')){
			$tpl = I('get.style','','trim');
		}else{
			$company=D('company_profile')->field('tpl')->where(array('id'=>I('get.id')))->select(); 
			$tpl = $company[0]['tpl']?$company[0]['tpl']:C('qscms_tpl_company');	
		}
		$this->display(MODULE_PATH.'View/tpl_company/'.$tpl.'/com_jobs_list.html');
	}
	/**
	 * [jobs_show 职位详情]
	 */
	public function jobs_show(){
		
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'show','params'=>'id='.intval($_GET['id']))));
		}
		if(I('get.style')){
			$tpl = I('get.style','','trim');
		}else{
			$company=D('company_profile')->field('tpl')->where(array('id'=>I('get.id')))->select(); 
			$tpl = $company[0]['tpl']?$company[0]['tpl']:C('qscms_tpl_company');	
		}
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
        $this->display(MODULE_PATH.'View/tpl_company/'.$tpl.'/jobs_show.html');
	}
	/**
	 * [jobs_list 职位列表]
	 */
	public function jobs_list(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'index','params'=>http_build_query(I('get.')))));
		}
		$citycategory = I('get.citycategory','','trim');
		$where = array(
    		'类型' => 'QS_citycategory',
    		'地区分类' => $citycategory
    	);
		$classify = new \Common\qscmstag\classifyTag($where);
    	$city = $classify->run();
		$jobcategory = I('get.jobcategory','','trim');
		$where = array(
    		'类型' => 'QS_jobcategory',
    		'职位分类' => $jobcategory
    	);
		$classify = new \Common\qscmstag\classifyTag($where);
    	$jobs = $classify->run();
		$seo = array('jobcategory'=>$jobs['select']['categoryname'],'citycategory'=>$city['select']['categoryname'],'key'=>I('request.key'));
		$page_seo = D('Page')->get_page();
		$this->_config_seo($page_seo[strtolower(MODULE_NAME).'_'.strtolower(CONTROLLER_NAME).'_'.strtolower(ACTION_NAME)],$seo);
		$this->display();
	}
    /**
     * [jobs_list 职位列表]
     */
    public function company_list(){
        if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'company_list','params'=>http_build_query(I('get.')))));
		}
        $this->display();
    }
	/**
	 * [jobs_list 地图职位列表]
	 */
	public function jobs_map(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'index')));
		}
		$this->display();
	}
	/**
	 * [index 招聘首页]
	 */
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
			redirect(build_mobile_url(array('c'=>'Jobs','a'=>'index')));
		}
		$this->display();
	}
}
?>