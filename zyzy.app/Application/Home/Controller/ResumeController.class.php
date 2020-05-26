<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class ResumeController extends FrontendController{
	public function _initialize() {
        parent::_initialize();
    }
	  /**
     * [resume_list 简历首页]
     */
    public function index(){
        if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'Resume','a'=>'index')));
        }
    	$this->display();
    }
    /**
     * [resume_list 简历列表页]
     */
    public function resume_list(){
        if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'Resume','a'=>'index','params'=>http_build_query(I('get.')))));
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
        $search_type = I('request.search_type','','trim');
        $search_type = $search_type?$search_type:(C('qscms_resumesearch_key_first_choice')?'precise':'full');
        $this->assign('search_type',$search_type);
    	$this->display();
    }
    public function resume_show(){
        if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url(array('c'=>'Resume','a'=>'show','params'=>'id='.intval($_GET['id']))));
        }
        if(I('get.style')){
			$tpl = I('get.style','','trim');
		}else{
			$company=D('resume')->field('tpl')->where(array('id'=>I('get.id')))->select(); 
			$tpl = $company[0]['tpl']?$company[0]['tpl']:C('qscms_tpl_personal');	
		}
        $ids = I('request.ids','','trim');
        if($ids && fieldRegex($ids,'in')){
            $resumeList = M('Resume')->where(array('id'=>array('in',$ids)))->order('field(id,'.$ids.')')->getfield('id,uid,fullname');
            $this->assign('resumeList',$resumeList);
            $this->assign('ids',$ids);
        }
		//添加meta的链接
		$canonical=C('qscms_site_domain').$_SERVER['REQUEST_URI'];
		$this->assign('canonical',$canonical);
		//end
        $this->display(MODULE_PATH.'View/tpl_resume/'.$tpl.'/resume_show.html');
    }
    /**
     * 将简历保存为word
     */
    public function resume_doc($id){
        if (!$this->visitor->is_login) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'),'',1);
            //非ajax的跳转页面
            $this->redirect('members/login');
        }
        if(!$id) $this->error('请选择简历！'); 
        D('Resume')->save_as_doc_word($id,'',C('visitor'));
    }
}
?>