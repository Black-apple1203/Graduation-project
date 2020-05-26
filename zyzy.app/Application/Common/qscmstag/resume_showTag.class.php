<?php
/**
 * 简历详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class resume_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'简历id'			=>	'id',
            '接口'              =>  'api'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}

        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $val = M('Resume')->where($this->map)->find();
        $validation = I('get.validation',0,'intval');
        if(!$this->params['api']){
            if(!$val || ($validation != '1' && ((C('qscms_resume_display') == 1 && $val['audit'] != 1 && C('visitor.uid') != $val['uid']) || (C('qscms_resume_display') == 2 && $val['audit'] == 3 && C('visitor.uid') != $val['uid'])))){
                $controller = new \Common\Controller\BaseController;
                $controller->_empty();
            }
        }
        $val['fullname_']=$val['fullname'];
        $val['education_list']=D('ResumeEducation')->get_resume_education($val['id'],$val['uid']);
        $val['education_list'] = $this->_get_duration($val['education_list']);
        $val['work_list']=D('ResumeWork')->get_resume_work($val['id'],$val['uid']);
        $val['work_list'] = $this->_get_duration($val['work_list']);
        $val['work_duration'] = $this->_get_total_work_duration($val['work_list']);
        $val['work_count'] = count($val['work_list']);
        $val['training_list']=D('ResumeTraining')->get_resume_training($val['id'],$val['uid']);
        $val['training_list'] = $this->_get_duration($val['training_list']);
        $val['project_list']=D('ResumeProject')->get_resume_project($val['id'],$val['uid']);
        $val['project_list'] = $this->_get_duration($val['project_list']);
        $val['language_list']=D('ResumeLanguage')->get_resume_language($val['id'],$val['uid']);
        $val['credent_list']=D('ResumeCredent')->get_resume_credent($val['id'],$val['uid']);
        $map = array('resume_id'=>$val['id'],'uid'=>$val['uid']);
        if(C('qscms_resume_img_display') == 1){
            $map['audit'] = 1;
        }else{
            $map['audit'] = array(array('eq',1),array('eq',2),'or');
        }
        $val['img_list']=D('ResumeImg')->get_resume_img($map);
        $val['age']=date("Y")-$val['birthdate'];
        if ($val['tag_cn'])
        {
            $tag_cn=explode(',',$val['tag_cn']);
            $val['tag_cn']=$tag_cn;
        }
        else
        {
        $val['tag_cn']=array();
        }
        $val['refreshtime_cn']=daterange(time(),$val['refreshtime'],'Y-m-d',"#FF3300");
        //判断手机、微信、邮箱是否验证
        $is_audit_phone = D('Members')->where(array('uid'=>array('eq',$val['uid'])))->find();
        if($val['word_resume']){
            $val['_word_resume'] = attach($val['word_resume'],'word_resume');
            $val['word_resume'] = U('Home/download/word_resume',array('id'=>$val['id']));
        }
		if(C('qscms_subsite_open')==1 && C('subsite_info.s_id')>0){
			$val['qrcode_url'] = C('subsite_info.pc_type').C('subsite_info.s_domain') .C('qscms_site_dir').'index.php?c=Resume&a=resume_show&id='.$val['id'];
		}else{
			$val['qrcode_url'] = C('qscms_site_domain').C('qscms_site_dir').'index.php?c=Resume&a=resume_show&id='.$val['id'];
		}
        $val['qrcode_url'] = rtrim($val['qrcode_url'],C('URL_HTML_SUFFIX'));
        $val['qrcode_url'] = C('qscms_site_dir').'index.php?m=Home&c=Qrcode&a=index&url='.urlencode($val['qrcode_url']);
        $val['label_id'] = 0;
        $val['label_type'] = 0;
        $val['label_resume'] = '';
        $jobs_id = I('get.jobs_id',0,'intval');
		//屏蔽对应企业的处理
		if(C('visitor.utype') == 1){
            $companyname = M('CompanyProfile')->where(array('uid'=>C('visitor.uid')))->getfield('companyname');
            $uids = M('PersonalShieldCompany')->where(array('comkeyword'=>array('like',$companyname)))->getfield('uid');
        }
		$where['id']=$this->params['id'];
		$where['uid']=$uids;
		$ls=D('Resume')->where($where)->getfield('id');
		if($ls){
			$val['ls']=1;
			//check_url('404');
		}else{
			$val['ls']=0;
		}
		
		if(C('visitor.utype')==1){
            $down_resume = D('CompanyDownResume')->check_down_resume($val['id'],C('visitor.uid'));
            if($jobs_id){
                $jobs_apply = D('PersonalJobsApply')->check_jobs_apply($val['id'],C('visitor.uid'),$jobs_id);
            }else{
                $jobs_apply = D('PersonalJobsApply')->check_jobs_apply($val['id'],C('visitor.uid'));
            }
        }else{
            $down_resume = false;
            $jobs_apply = false;
        }
        $val['show_contact'] = $this->_get_show_contact($val,$down_resume,$jobs_apply,$val['label_id'],$jobs_id);
        $val['label_arr'] = array();
        if(I('get.from_apply',0,'intval')==1){
            $val['label_arr'] = D('PersonalJobsApply')->state_arr;
            $val['label_resume'] = 'apply';
            $val['label_type'] = 2;
            $val['label_id'] = $jobs_apply['is_reply'];
        }elseif(I('get.from_down',0,'intval')==1){
            $val['label_arr'] = D('CompanyDownResume')->state_arr;
            $val['label_resume'] = 'down';
            $val['label_type'] = 1;
            $val['label_id'] = $down_resume['is_reply'];
        }
        if(!$jobs_id && $val['label_resume']=='apply'){
            $val['label_resume'] = '';
        }
        
        if($val['show_contact']===false){
            if ($val['display_name']=="2")
            {
                $val['fullname']="N".str_pad($val['id'],7,"0",STR_PAD_LEFT); 
            }
            elseif($val['display_name']=="3")
            {
                if($val['sex']==1){
                    $val['fullname']=cut_str($val['fullname'],1,0,"先生");
                }elseif($val['sex'] == 2){
                    $val['fullname']=cut_str($val['fullname'],1,0,"女士");
                }
            }
            $val['telephone'] = contact_hide($val['telephone'],2);
            $val['email'] = contact_hide($val['email'],3);
            $val['qq'] = contact_hide($val['qq'],4);
            $val['weixin'] = contact_hide($val['weixin'],5);
        }

        $avatar_default = $val['sex']==1?'no_photo_male.png':'no_photo_female.png';
        //1.照片为空
        //2.照片主动不显示
        //3.照片审核不通过
        //4.先审核后显示：照片不是审核通过的（不等于1）
        //5.先显示后审核：照片是审核不通过的（等于3）
        if($val['photo_img']=='' || $val['photo_display']==2 || $val['photo_audit']==3 || (C('qscms_resume_img_display') == 1 && $val['photo_audit']!=1) || (C('qscms_resume_img_display') == 2 && $val['photo_audit']==3)){
            //不显示
            $val['photosrc']=attach($avatar_default,'resource');
        }else{
            //显示
            $val['photosrc']=attach($val['photo_img'],'avatar');
        }
        $category = D('Category')->get_category_cache('QS_wage');
        if(C('qscms_wage_unit') == 2){
            $val['wage_cn'] = $category[$val['wage']];
        }
        $val['telephone_'] = $val['telephone'];
        $val['weixin_'] = $val['weixin'];
        $val['email_'] = $val['email'];
        if(C('qscms_contact_img_resume') == 2){
            $val['telephone'] = '<img src="'.C('qscms_site_domain').U('Home/Qrcode/get_font_img',array('str'=>encrypt($val['telephone'],C('PWDHASH'))),'','',true).'"/>';
            $val['email'] = '<img src="'.C('qscms_site_domain').U('Home/Qrcode/get_font_img',array('str'=>encrypt($val['email'],C('PWDHASH'))),'','',true).'"/>';
            $val['qq'] && $val['qq'] = '<img src="'.C('qscms_site_domain').U('Home/Qrcode/get_font_img',array('str'=>encrypt($val['qq'],C('PWDHASH'))),'','',true).'"/>';
            $val['weixin'] && $val['weixin'] = '<img src="'.C('qscms_site_domain').U('Home/Qrcode/get_font_img',array('str'=>encrypt($val['weixin'],C('PWDHASH'))),'','',true).'"/>';
        }
        if(C('visitor.utype') == 1){
            $view_log = M('ViewResume')->where(array('uid'=>C('visitor.uid'),'resumeid'=>$val['id']))->find();
            if($view_log){
                M('ViewResume')->where(array('uid'=>C('visitor.uid'),'resumeid'=>$val['id']))->setField('addtime',time());
            }else{
                M('ViewResume')->add(array('uid'=>C('visitor.uid'),'resumeid'=>$val['id'],'resume_uid'=>$val['uid'],'addtime'=>time()));
            }
            if($_GET['jobs_id']){
                $apply_log = D('PersonalJobsApply')->check_jobs_apply($val['id'],C('visitor.uid'),intval($_GET['jobs_id']));
                $apply_log && D('PersonalJobsApply')->set_apply($apply_log,C('visitor'),2);
            }
        }
        $val['strong_tag'] = $val['strong_tag']>0?M('PersonalServiceTagCategory')->where(array('id'=>$val['strong_tag']))->getField('name'):'';
        //检测是否已收藏
        $val['favor'] = $this->_check_favor($val['id'],C('visitor.uid'));
        return $val;
    }
    protected function _get_duration($list){
        if(!empty($list)){
            foreach ($list as $key => $value) {
                $start = $value['startyear'].'-'.$value['startmonth'];
                $end = $value['todate']==1?date('Y-m'):($value['endyear'].'-'.$value['endmonth']);
                $list[$key]['duration'] = ddate($start,$end);
            }
        }
        return $list;
    }
    protected function _get_total_work_duration($list){
        $total_year = 0;
        $total_month = 0;
        $return = '';
        if(!empty($list)){
            foreach ($list as $key => $value) {
                $current_duration = strpos($value['duration'],'年');
                if($current_duration===false){
                    $total_month += intval($value['duration']);
                }else{
                    $arr = explode("年", $value['duration']);
                    $total_year += intval($arr[0]);
                    $total_month += intval($arr[1]);
                }
            }
        }
        $add_year = intval($total_month/12);
        $total_year += $add_year;
        $total_month = intval($total_month%12);
        if($total_year>0){
            $return .= $total_year.'年';
        }
        if($total_month>0){
            $return .= $total_month.'个月';
        }
        return $return;
    }
    /**
     * 是否显示联系方式
     */
    protected function _get_show_contact($val,$down,$apply,&$label_id,$jobs_id){
        $show_contact = false;
        //情景1：游客访问
        if(!C('visitor')){
			if(MODULE_NAME == 'Home' && C('qscms_showresumecontact')==0){
                $show_contact = true;
            }
			if(MODULE_NAME == 'Mobile' && C('qscms_showresumecontact_wap')==0){
                $show_contact = true;
            }
        }
        //情景2：个人会员访问并且是该简历发布者
        else if(C('visitor.utype')==2 && C('visitor.uid')==$val['uid'])
        {
            $show_contact = true;
        }
        //情景3：企业会员访问
        else if(C('visitor.utype')==1)
        {
            //情景3-1：其他企业会员
            if(MODULE_NAME == 'Home' && C('qscms_showresumecontact')==1){
                $show_contact = true;
            }
            if(MODULE_NAME == 'Mobile' && C('qscms_showresumecontact_wap')==1){
                $show_contact = true;
            }
            //情景3-2：下载过该简历
            if($down){
                $show_contact = true;
            }
            //情景3-3：该简历申请过当前企业发布的职位
            $setmeal=D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
            if($apply && $setmeal['show_apply_contact']=='1'){
                $show_contact = true;
            }
        }
        return $show_contact;
    }
    //检测是否已收藏
    protected function _check_favor($resume_id,$uid){
        $r = D('CompanyFavorites')->where(array('resume_id'=>$resume_id,'company_uid'=>$uid))->find();
        if($r){
            return 1;
        }else{
            return 0;
        }
    }
}