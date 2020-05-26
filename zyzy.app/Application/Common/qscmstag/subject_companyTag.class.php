<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class subject_companyTag {
    protected $params = array();
    protected $map = array();
    public function __construct($options) {
        $array = array(
            '列表名'           =>  'listname',
            '显示数目'          =>  'row',
            '专题公司id'          =>  'id',
            '关键字'          =>  'key',
            '关键字类型'          =>  'keytype',
            '分页显示'              =>  'page'

        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['subject_id'] = array('eq',intval($this->params['id']));
        $this->limit = isset($this->params['row'])?intval($this->params['row']):5;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
    }
    public function run(){
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'subject_company';
        if(!empty($this->params['key'])){
            $this->params['key'] = urldecode(urldecode($this->params['key']));
            $key = trim($this->params['key']);
            switch($this->params['keytype']){
                case 'job':
                    $list_map['jobs_name'] = array('like','%'.$key.'%');
                    break;
                case 'com':
                    $com_list_map['c.companyname'] = array('like','%'.$key.'%');
                    break;
            }
        }
        if($this->params['keytype'] == '' || $this->params['keytype'] == 'com'){
            $com_list_map[$this_t.'.s_audit'] = 1;
            $com_list_map[$this_t.'.subject_id'] = $this->map['subject_id'];
            $field = $this_t.".*,c.id as com_id,c.uid,c.audit,c.companyname,c.addtime,c.refreshtime,c.logo,c.short_name,c.contact,c.address,c.district_cn";
            $join_c = 'left join '.$db_pre."company_profile as c on ".$this_t.".company_uid=c.uid";
            if($this->params['page']){
                $total = M('SubjectCompany')->where($com_list_map)->join($join_c)->count();
                $pager = pager($total, $this->limit);
                $page = $pager->fshow();
                $this->limit = $pager->firstRow.','.$pager->listRows;
                $page_params = $pager->get_page_params();
            }else{
                $this->limit = $this->params['start'].','.$this->limit;
                $total = 0;
                $page = '';
                $page_params = array();
            }
            $company_list = M('SubjectCompany')->where($com_list_map)->join($join_c)->field($field)->limit($this->limit)->order('c_order desc,c.refreshtime desc')->select();
            $cids = array();
            foreach ($company_list as $key=>$val) {
                $jobs = M('Jobs')->where(array('company_id'=>$val['com_id']))->select();
                if($jobs){
                    $company[$val['com_id']]['wx_photo'] = $val['wx_photo'];
                    $company[$val['com_id']]['add_status'] = $val['add_status'];
                    $company[$val['com_id']]['companyname'] = $val['companyname'];
                    $company[$val['com_id']]['contact'] = $val['contact'];
                    $company[$val['com_id']]['address'] = $val['address'];
                    if ($val['logo'])
                    {
                        $company[$val['com_id']]['logo']=attach($val['logo'],'company_logo');
                    }
                    else
                    {
                        $company[$val['com_id']]['logo']=attach('no_logo.png','resource');
                    }
                    $company[$val['com_id']]['company_url']=url_rewrite('QS_companyshow',array('id'=>$val['com_id']));
                    $company[$val['com_id']]['company_jobs_url']=url_rewrite('QS_companyjobs',array('id'=>$val['com_id']));
                    $jobs_num = M('Jobs')->where(array('company_id'=>$val['com_id']))->count();
                    $company[$val['com_id']]['jobs_num'] = $jobs_num;
                    $cids[] = $val['com_id'];
                }
            }
            if($cids){
                $list_map['company_id'] = array('in',$cids);
                if(C('qscms_jobs_display')==1){
                        $list_map['audit'] = 1;
                    }
                $jobs_list = M('Jobs')->field('id,company_id,companyname,jobs_name,minwage,maxwage,company_id,negotiable,district_cn,education_cn,experience_cn')->where($list_map)->order('refreshtime desc')->select();
                foreach ($jobs_list as $k => $val) {
                    if(count($company[$val['company_id']]['jobs']) >= 3) continue;
                    $val['jobs_url'] = url_rewrite('QS_jobsshow',array('id'=>$val['id']));
                    if($val['negotiable']==0){
                        if(C('qscms_wage_unit') == 1){
                            $val['minwage'] = $val['minwage']%1000==0?(($val['minwage']/1000).'K'):(round($val['minwage']/1000,1).'K');
                            $val['maxwage'] = $val['maxwage']?($val['maxwage']%1000==0?(($val['maxwage']/1000).'K'):(round($val['maxwage']/1000,1).'K')):0;
                        }elseif(C('qscms_wage_unit') == 2){
                            if($val['minwage']>=10000){
                                if($val['minwage']%10000==0){
                                   $val['minwage'] = ($val['minwage']/10000).'万';
                                }else{
                                    $val['minwage'] = round($val['minwage']/10000,1);
                                    $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','万',$val['minwage']) : $val['minwage'].'万';
                                }
                            }else{
                                if($val['minwage']%1000==0){
                                    $val['minwage'] = ($val['minwage']/1000).'千';
                                }else{
                                    $val['minwage'] = round($val['minwage']/1000,1);
                                    $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','千',$val['minwage']) : $val['minwage'].'千';
                                }
                            }
                            if($val['maxwage']>=10000){
                                if($val['maxwage']%10000==0){
                                   $val['maxwage'] = ($val['maxwage']/10000).'万';
                                }else{
                                    $val['maxwage'] = round($val['maxwage']/10000,1);
                                    $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','万',$val['maxwage']) : $val['maxwage'].'万';
                                }
                            }elseif($val['maxwage']){
                                if($val['maxwage']%1000==0){
                                   $val['maxwage'] = ($val['maxwage']/1000).'千';
                                }else{
                                    $val['maxwage'] = round($val['maxwage']/1000,1);
                                    $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','千',$val['maxwage']) : $val['maxwage'].'千';
                                }
                            }else{
                                $val['maxwage'] = 0;
                            }
                        }
                        if($val['maxwage']==0){
                            $val['wage_cn'] = '面议';
                        }else{
                            if($val['minwage']==$val['maxwage']){
                                $val['wage_cn'] = $val['minwage'].'/月';
                            }else{
                                $val['wage_cn'] = $val['minwage'].'-'.$val['maxwage'].'/月';
                            }
                        }
                    }else{
                        $val['wage_cn'] = '面议';
                    }
                    $r = D('PersonalJobsApply')->where(array('jobs_id'=>$val['id'],'personal_uid'=>C('visitor.uid')))->find();
                    if($r){
                        $val['is_apply'] = 1;
                    }else{
                        $val['is_apply'] = 0;
                    }
                    $company[$val['company_id']]['jobs'][] = $val;
                }
            }
        }elseif($this->params['keytype'] == 'job'){
            $where[$this_t.'.subject_id'] = array('eq',intval($this->params['id']));
            $where[$this_t.'.s_audit'] = 1;
            $where['j.jobs_name'] = $list_map['jobs_name'];
            if(C('qscms_jobs_display')==1){
                    $where['j.audit'] = 1;
                }
            $field = $this_t.".*,j.id as jid,j.jobs_name,j.companyname,j.company_id,j.minwage,j.maxwage,j.company_id,j.negotiable,j.district_cn,j.education_cn,j.experience_cn";
            $join_j = 'left join '.$db_pre."jobs as j on ".$this_t.".company_uid=j.uid";
            $total = M('SubjectCompany')->where($where)->join($join_j)->count();
            $pager = pager($total, $this->limit);
            $page = $pager->fshow();
            $this->limit = $pager->firstRow.','.$pager->listRows;
            $page_params = $pager->get_page_params();
            $jobs_list = M('SubjectCompany')->where($where)->join($join_j)->field($field)->order('j.refreshtime desc')->limit($this->limit)->select();
            foreach ($jobs_list as $k => $val) {
                $info = M('CompanyProfile')->where(array('id'=>$val['company_id']))->field('logo')->find();
                $val['company_url']=url_rewrite('QS_companyshow',array('id'=>$val['company_id']));
                $val['company_jobs_url']=url_rewrite('QS_companyjobs',array('id'=>$val['company_id']));
                if ($info['logo'])
                {
                    $val['logo']=attach($info['logo'],'company_logo');
                }
                else
                {
                    $val['logo']=attach('no_logo.png','resource');
                }
                $val['jobs_url'] = url_rewrite('QS_jobsshow',array('id'=>$val['jid']));
                if($val['negotiable']==0){
                    if(C('qscms_wage_unit') == 1){
                        $val['minwage'] = $val['minwage']%1000==0?(($val['minwage']/1000).'K'):(round($val['minwage']/1000,1).'K');
                        $val['maxwage'] = $val['maxwage']?($val['maxwage']%1000==0?(($val['maxwage']/1000).'K'):(round($val['maxwage']/1000,1).'K')):0;
                    }elseif(C('qscms_wage_unit') == 2){
                        if($val['minwage']>=10000){
                            if($val['minwage']%10000==0){
                               $val['minwage'] = ($val['minwage']/10000).'万';
                            }else{
                                $val['minwage'] = round($val['minwage']/10000,1);
                                $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','万',$val['minwage']) : $val['minwage'].'万';
                            }
                        }else{
                            if($val['minwage']%1000==0){
                                $val['minwage'] = ($val['minwage']/1000).'千';
                            }else{
                                $val['minwage'] = round($val['minwage']/1000,1);
                                $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','千',$val['minwage']) : $val['minwage'].'千';
                            }
                        }
                        if($val['maxwage']>=10000){
                            if($val['maxwage']%10000==0){
                               $val['maxwage'] = ($val['maxwage']/10000).'万';
                            }else{
                                $val['maxwage'] = round($val['maxwage']/10000,1);
                                $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','万',$val['maxwage']) : $val['maxwage'].'万';
                            }
                        }elseif($val['maxwage']){
                            if($val['maxwage']%1000==0){
                               $val['maxwage'] = ($val['maxwage']/1000).'千';
                            }else{
                                $val['maxwage'] = round($val['maxwage']/1000,1);
                                $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','千',$val['maxwage']) : $val['maxwage'].'千';
                            }
                        }else{
                            $val['maxwage'] = 0;
                        }
                    }
                    if($val['maxwage']==0){
                        $val['wage_cn'] = '面议';
                    }else{
                        if($val['minwage']==$val['maxwage']){
                            $val['wage_cn'] = $val['minwage'].'/月';
                        }else{
                            $val['wage_cn'] = $val['minwage'].'-'.$val['maxwage'].'/月';
                        }
                    }
                }else{
                    $val['wage_cn'] = '面议';
                }
                $r = D('PersonalJobsApply')->where(array('jobs_id'=>$val['id'],'personal_uid'=>C('visitor.uid')))->find();
                if($r){
                    $val['is_apply'] = 1;
                }else{
                    $val['is_apply'] = 0;
                }
                $company['jobs'][] = $val;
            }
        }
        $return['page'] = $page;
        $return['list'] = $company;
        return $return;
    }
}