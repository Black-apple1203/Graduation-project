<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class IndexController extends FrontendController{
	public function _initialize() {
        parent::_initialize();
    }
	/**
	 * [index 首页]
	 */
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url());
		}
		if(false === $oauth_list = F('oauth_list')){
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('verify_userlogin',$this->check_captcha_open(C('qscms_captcha_config.user_login'),'error_login_count'));
        $count =rand(99, 120);
        $this->assign('count',$count);
		$this->assign('oauth_list',$oauth_list);
		$this->display();
	}
	/**
	 * [ajax_user_info ajax获取用户登录信息]
	 */
	public function ajax_user_info(){
		if(IS_AJAX){
			!$this->visitor->is_login && $this->ajaxReturn(0,'请登录！');
			$uid = C('visitor.uid');
			if(C('visitor.utype') == 1){
				$info = M('CompanyProfile')->field('companyname,logo')->where(array('uid'=>$uid))->find();
				$views = M('ViewJobs')->where(array('jobs_uid'=>C('visitor.uid')))->group('uid')->getfield('uid',true);
				$info['views'] = count($views);
				$join = 'join '.C('DB_PREFIX') .'jobs j on j.id='.C('DB_PREFIX').'personal_jobs_apply.jobs_id';
				$info['apply'] = M('PersonalJobsApply')->join($join)->where(array('company_uid'=>$uid,'is_reply'=>array('eq',0)))->count();
			}else{
				$info['realname'] = M('Resume')->where(array('uid'=>$uid,'def'=>1))->limit(1)->getfield('fullname');
				$info['pid'] = M('Resume')->where(array('uid'=>$uid,'def'=>1))->getfield('id');
				$info['countinterview'] = M('CompanyInterview')->where(array('resume_uid'=>$uid))->count();
				//谁看过我
				$rids = M('Resume')->where(array('uid'=>$uid))->getField('id',true);
				if($rids){
					$info['views'] = M('ViewResume')->where(array('resumeid'=>array('in',$rids)))->count();
				}else{
					$info['views'] = 0;
				}
			}
			$issign = D('MembersHandsel')->check_members_handsel_day(array('uid'=>$uid,'htype'=>'task_sign'));
        	$this->assign('issign',$issign ? 1 : 0);
			$this->assign('info',$info);
			$hour=date('G');
			if($hour<11){
				$am_pm = '早上好';
	        }
	        else if($hour<13)
	        {
	        	$am_pm = '中午好';
	        }
	        else if($hour<17)
	        {
	        	$am_pm = '下午好';
	        }
	        else
	        {
	        	$am_pm = '晚上好';
	        }
	        $this->assign('am_pm',$am_pm);
			$data['html'] = $this->fetch('ajax_user_info');
        	$this->ajaxReturn(1,'',$data);
		}
	}
	/**
	 * [index 首页搜索跳转]
	 */
	public function search_location(){
		$act = I('post.act','','trim');
		$key = I('post.key','','trim');
		$this->ajaxReturn(1,'',url_rewrite($act,array('key'=>$key)));
	}
	/**
	 * 保存到桌面
	 */
	public function shortcut(){
		$Shortcut = "[InternetShortcut]
		URL=".C('qscms_site_domain').C('qscms_site_dir')."?lnk
		IDList= 
		IconFile=".C('qscms_site_domain').C('qscms_site_dir')."favicon.ico
		IconIndex=100
		[{000214A0-0000-0000-C000-000000000046}]
		Prop3=19,2";
		header("Content-type: application/octet-stream"); 
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename=C('qscms_site_name').'.url';
		$filename = urlencode($filename);
		$filename = str_replace("+", "%20", $filename);
		if (preg_match("/MSIE/", $ua)) {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		exit($Shortcut);
	}
	/**
	 * 分站更多
	 */
	public function subsite(){
		$pinyin=array(
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
		/* 'A'=>1,
		'B'=>2,
		'C'=>3,
		'D'=>4,
		'E'=>5,
		'F'=>6,
		'G'=>7,'H'=>8,
		'I'=>9,'J'=>10,'K'=>11,'L'=>12,'M'=>13,'N'=>14,'O'=>15,'P'=>16,'Q'=>17,'R'=>18,'S'=>19,'T'=>20,'U'=>21,'V'=>22,'W'=>23,'X'=>24,'Y'=>25,'Z'=>26, */
		);
		if(false === $subsite = F('subsite_tpl_list')){
			$subsite=D('Subsite')->get_subsite_tpl();
		}
		$list=array();
		foreach($pinyin as $key=>$val){
			 foreach($subsite as $k=>$v){
				 if($v['s_first']==$val){
					   $list[$key]['pinyin']=$val; 
					   $list[$key]['info'][]=$v;
				 }
			 }
		}
		$this->assign('subsite',$list);
		$this->display();
		
	}
	/**
     * 首页滚动动态
     */
	public function ajax_scroll(){
	    $where['display'] = 1;
	    $where['audit'] = array('eq',1);
		//fenzhan
		if(C('subsite_info') && C('subsite_info.s_id')!=0){
			 $where['subsite_id'] = C('subsite_info.s_id');
		}
        $add_resume = M('Resume')->where($where)->order('addtime DESC')->limit(5)->getField("id,fullname,sex,addtime as time,'add' as type");
        if($add_resume){
			$where['id'] = array('not in',array_keys($add_resume));
			$refresh_resume = M('Resume')->where($where)->order('refreshtime DESC')->limit(5)->getField("id,fullname,sex,refreshtime  as time,'refresh' as type");
			if($refresh_resume){
				$resume_arr=array_merge($add_resume,$refresh_resume);
			}else{
				$resume_arr=$add_resume;
			}
			foreach($resume_arr as $val){
				if($val['sex']==1){
					$val['fullname']=cut_str($val['fullname'],1,0,"先生");
				}elseif($val['sex'] == 2){
					$val['fullname']=cut_str($val['fullname'],1,0,"女士");
				}else{
					$val['fullname']=cut_str($val['fullname'],1,0,"**");
				}
				$val['utype'] = 2;
				$val['url'] = url_rewrite('QS_resumeshow',array('id'=>$val['id']));
				$val['time_cn'] = daterange(time(),$val['time'],'Y-m-d');
				$resume[] = $val;
			}
		}
        unset($where['id']);
        $add_job = M('Jobs')->where($where)->order('addtime DESC')->limit(5)->getField("id,jobs_name,companyname,company_id,addtime as time,'add' as type");
         if($add_job){
			$where['id'] = array('not in',array_keys($add_job));
			$refresh_job = M('Jobs')->where($where)->order('refreshtime DESC')->limit(5)->getField("id,jobs_name,companyname,company_id,refreshtime as time,'refresh' as type");
			if($refresh_job){
				$job_arr=array_merge($add_job,$refresh_job);
			}else{
				$job_arr=$add_job;
			}
			foreach($job_arr as $val){
				$val['utype'] = 1;
				$val['companyname']=cut_str($val['companyname'],6,0,'...');
				$val['job_url'] = url_rewrite('QS_jobsshow',array('id'=>$val['id']));
				$val['company_url'] = url_rewrite('QS_companyshow',array('id'=>$val['company_id']));
				$val['time_cn'] = daterange(time(),$val['time'],'Y-m-d');
				$job[] = $val;
			}
		}
		if($resume!=NULl && $job!=NUll){
			$tmp=array_merge($resume,$job);
		}elseif($resume!=NULl && $job==NUll){
			$tmp=$resume;
		}elseif($resume==NULl && $job==NUll){
			$tmp=$job;
		}else{
			$this->ajaxReturn(0,'暂无数据！');
		}
        //$time = array_column($tmp,'time');
        foreach($tmp as $item) {
            $time[] = $item['time'];
        }
        rsort($time);
        foreach($time as $val) {
            foreach($tmp as $k => $v) {
                if($val == $v['time']) {
                    $data[] = $v;
                    unset($tmp[$k]);
                }
            }
        }
        count($data)==0 && $this->ajaxReturn(0,'暂无数据！');
        $this->ajaxReturn(1,'获取动态成功！',$data);
	}
	/**
     * [ajax_recommend_resume ajax推荐简历]
     */
    public function ajax_recommend_resume() {
        $jobs = M('Jobs')->field('topclass,category')->where(array('uid' => C('visitor.uid')))->limit(10)->select();
        $company = M('CompanyProfile')->field('companyname,logo,id')->where(array('uid' => C('visitor.uid')))->find();
        $this->assign('company',$company);
        foreach ($jobs as $key => $val) {
            $topclass[] = $val['topclass'];
            $category[] = $val['category'];
        }
        $resume = new \Common\qscmstag\resume_listTag(array('职位大类' => $topclass, '职位中类' => $category, '显示数目' => 6));
        $resume_list = $resume->run();
        $this->assign('info',$resume_list['list']);
        $data['html'] = $this->fetch('ajax_recommend_resume');
        $this->ajaxReturn(1,'获取动态成功！',$data);
    }
    /**
     * [ajax_recommend_jobs ajax推荐简历]
     */
    public function ajax_recommend_jobs() {
        $resume = M('resume')->where(array('uid' => C('visitor.uid')))->find();
        $this->assign('resume',$resume);
        $jobs = new \Common\qscmstag\jobs_listTag(array('显示数目' => 6));
        $jobs_list = $jobs->run();
        $this->assign('info',$jobs_list['list']);
        $hour=date('G');
			if($hour<11){
				$am_pm = '早上好';
	        }
	        else if($hour<13)
	        {
	        	$am_pm = '中午好';
	        }
	        else if($hour<17)
	        {
	        	$am_pm = '下午好';
	        }
	        else
	        {
	        	$am_pm = '晚上好';
	        }
	    $this->assign('am_pm',$am_pm);
        $data['html'] = $this->fetch('ajax_recommend_jobs');
        $this->ajaxReturn(1,'获取动态成功！',$data);
    }
}
?>