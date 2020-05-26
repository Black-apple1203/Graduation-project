<?php
/**
 * 用户控制器基类
 *
 * @author andery
 */
namespace Common\Controller;
use Common\Controller\FrontendController;
class UserbaseController extends FrontendController {
    protected $visitor = null;
    public function _initialize() {
        parent::_initialize();
		$this->_total_sql();
		$this->_count_attention_me();
		$this->_company_interview();
		$this->_resume_list();
		$this->_resume_one();
		
    }
    /**
    * 多条简历
    */
    protected function _resume_list() {
       if(false === $resume_list = F('resume_list')){
			$resume_list = D('Resume')->resume_list($this->visitor->info['uid']);
		}
		//print_r($this->visitor->info['uid']);
		 $this->assign('resume_list', $resume_list);
    }
	 /**
    * 单条简历
    */
    protected function _resume_one() {
       if(false === $resume_one = F('resume_one')){
			$resume_one = D('Resume')->resume_one($this->visitor->info['uid']);
		}
		//print_r($this->visitor->info['uid']);
		 $this->assign('resume_one', $resume_one);
    }
	 /**
    * 以申请职位
    */
    protected function _total_sql() {
       if(false === $total_sql = F('total_sql')){
			$total_sql = D('PersonalJobsApply')->total_sql($this->visitor->info['uid']);
		}
		//print_r($this->visitor->info['uid']);
		 $this->assign('total_sql', $total_sql);
    }
	 /**
    * 谁在关注我
    */
    protected function _count_attention_me() {
       if(false === $count_attention_me = F('count_attention_me')){
			$count_attention_me = D('Resume')->count_personal_attention_me($this->visitor->info['uid']);
		}
		//print_r($this->visitor->info['uid']);
		 $this->assign('count_attention_me', $count_attention_me);
    }
	 /**
    * 面试邀请
    */
    protected function _company_interview() {
       if(false === $company_interview = F('company_interview')){
			$count_interview = D('CompanyInterview')->count_interview($this->visitor->info['uid']);
		}
		//print_r($this->visitor->info['uid']);
		 $this->assign('count_interview', $count_interview);
    }
}