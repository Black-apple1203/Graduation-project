<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class JobsController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Jobs');
    }
    /**
     * 职位列表
     */
    public function index(){
        $jobtype=I('get.jobtype',1,'intval');
        $audit=I('get.audit',0,'intval');
        $deadline=I('get.deadline',0,'intval');
        $invalid=I('get.invalid',0,'intval');
        $settr=I('get.settr',0,'intval');
        $addsettr=I('get.addsettr',0,'intval');
        $promote=I('get.promote',0,'intval');
        $uid=I('get.uid',0,'intval');
        $key = I('get.key','','trim');
        $orderby_str = I('get.orderby','addtime','trim');
        $key_type = I('get.key_type',0,'intval');
        if($jobtype==0){
            $tablename="all";
        }elseif ($jobtype==1){
            $tablename="jobs";
            $audit=$audit != 3 ? $audit : '';
            $deadline=$deadline>2?$deadline:'';
        }elseif($jobtype == 2){
            $tablename="jobs_tmp";
        }
        $order_by = 'field(audit,2,1,3),'.$orderby_str.' desc, id desc';
        $search_member = false;
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where['jobs_name']=array('like','%'.$key.'%');break;
                case 2:
                    $where['companyname']=array('like','%'.$key.'%');break;
                case 3:
                    $where['id']=array('eq',$key);break;
                case 4:
                    $where['company_id']=array('eq',$key);break;
                case 5:
                    $where['uid']=array('eq',$key);break;
                case 6:
                    $search_member = true;break;
            }
            unset($_REQUEST['key']);
            if($jobtype==0){
            $tablename="all";
			}elseif ($jobtype==1){
				$tablename="jobs";
				$audit=$audit != 3 ? $audit : '';
				$deadline=$deadline>2?$deadline:'';
			}elseif($jobtype == 2){
				$tablename="jobs_tmp";
			}
        }else{
            $uid>0 && $where['uid']=array('eq',$uid);
            $audit>0 && $where['audit']=array('eq',$audit);
            if ($settr>0){
                $tmpsettr=strtotime("-".$settr." day");
                $where['refreshtime']=array('gt',$tmpsettr);
                $order_by = 'field(audit,2) desc, refreshtime desc';
            }
            if ($addsettr>0){
                $tmpaddsettr=strtotime("-".$addsettr." day");
                $where['addtime']=array('gt',$tmpaddsettr);
                $order_by = 'field(audit,2) desc, addtime desc';
            }
            switch($invalid){
                case 1:
                    $where['deadline'] = array(array('neq',0),array('lt',time()));
                    $order_by = 'deadline desc';
                    break;
                case 2:
                    $where['setmeal_deadline'] = array(array('neq',0),array('lt',time()));
                    $order_by = 'setmeal_deadline desc';
                    break;
                case 3:
                    $where['display'] = array('eq',2);
                    $order_by = 'refreshtime desc';
                    break;
                case 4:
                    $where['audit'] = array('neq',1);
                    $order_by = 'deadline desc';
                    break;
            }
            switch($deadline){
                case 1:
                    
                    break;
                case 2:
                    
                    break;
                case 3:
                    $where['deadline'] = array('gt',time());
                    $order_by = 'field(audit,2) desc, deadline desc';
                    break;
            }
            if($deadline==1){
                $where['deadline'] = array('lt',time());
                $order_by = 'field(audit,2) desc, deadline desc';
            }elseif($deadline==2){
                $where['deadline'] = array('gt',time());
                $order_by = 'field(audit,2) desc, deadline desc';
            }elseif($deadline>2){
                $tmpsettr=strtotime("+{$deadline} day");
                $where['deadline'] = array('lt',$tmpsettr);
                $order_by = 'field(audit,2) desc, deadline desc';
            }
            switch($promote){
                case -1:
                    $where['recommend'] = array('eq',0);
                    $where['emergency'] = array('eq',0);
                    break;
                case 1:
                    $where['stick'] = array('eq',1);
                    break;
                case 2:
                    $where['emergency'] = array('eq',1);
                    break;
            }
        }
        if($search_member){
            $member_info = D('Members')->where(array('mobile'=>$key))->find();
            if($member_info){
                $where['uid'] = $member_info['uid'];
            }else{
                $where['uid'] = 0;
            }
        }
        if ($tablename=="all"){
            $this->union = M('JobsTmp')->where($where)->buildSql();
            $this->_name = 'Jobs';
        }else if($tablename=="jobs_tmp"){
            $this->_name = 'JobsTmp';
            $this->union = '';
        }else{
            $this->_name = 'Jobs';
            $this->union = '';
        }
        
        $this->where = $where;
        $this->order = $order_by;
        $this->custom_fun = '_format_jobs_list';
        $this->_after_search_jobs($jobtype);
        $this->assign('now',time());
        parent::index();
    }
    public function _after_search_jobs($jobtype){
        $tmp_model = M('JobsTmp');
        $count[1] = parent::_pending('Jobs');
        $count[2] = parent::_pending('JobsTmp');
        $count[0] = $count[1] + $count[2];
        if($jobtype == 0){
            $count[3] = parent::_pending('JobsTmp',array('audit'=>array('eq',1))) + parent::_pending('Jobs',array('audit'=>array('eq',1)));
            $count[4] = parent::_pending('JobsTmp',array('audit'=>array('eq',2))) + parent::_pending('Jobs',array('audit'=>array('eq',2)));
            $count[5] = parent::_pending('JobsTmp',array('audit'=>array('eq',3))) + parent::_pending('Jobs',array('audit'=>array('eq',3)));
        }else{
            if ($jobtype==2){
                $model = 'JobsTmp';
            }else{
                $model = 'Jobs';
            }
            $count[3] = parent::_pending($model,array('audit'=>array('eq',1)));
            $count[4] = parent::_pending($model,array('audit'=>array('eq',2)));
            $count[5] = parent::_pending($model,array('audit'=>array('eq',3)));
        }
        $this->assign('count',$count);
    }
    /**
     * 格式化列表
     */
    public function _format_jobs_list($list){
        return $this->_mod->admin_format_jobs_list($list);
    }
    /**
     * 待审核职位
     */
    public function index_noaudit(){
        $_GET['audit'] = isset($_GET['audit'])?$_GET['audit']:2;
        $this->_tpl = 'index';
        $this->index();
    }
    /**
     * 加载会员详情
     */
    public function ajax_get_user_info(){
        $id = I('get.id',0,'intval');
        $rst = D('Members')->admin_ajax_get_user_info($id);
        exit($rst['msg']);
    }
    /**
     * 删除职位
     */
    public function delete_jobs(){
        $id = I('request.id');
        if(!$id){
            $this->error('请选择职位');
        }
        if($n=$this->_mod->admin_del_jobs($id))
        {
        $this->success("删除成功！共删除{$n}行");
        }
        else
        {
        $this->error("删除失败！");
        }
    }
    /**
     * 审核职位
     */
    public function set_audit(){
        $id = I('request.id');
        $uid = I('request.uid');
        if(!$id){
            $this->error('请选择职位');
        }
        $audit = I('post.audit',0,'intval');
        $reason = I('post.reason','','trim');
        $pms_notice = I('post.pms_notice',0,'intval');
        $result = $this->_mod->admin_edit_jobs_audit($id,$uid,$audit,$reason,C('visitor'),$pms_notice);
        if($result){
            $this->_mod->admin_refresh_jobs($id);
            $this->success("设置成功！");
        }else{
            $this->error('设置失败！');
        }
    }
    /**
     * 刷新职位
     */
    public function refresh_jobs(){
        $id = I('request.id');
        if(!$id){
            $this->error('请选择职位');
        }
        if($n=$this->_mod->admin_refresh_jobs($id))
        {
        $this->success("刷新成功！响应行数 {$n}");
        }
        else
        {
        $this->error("刷新失败！");
        }
    }
    public function edit(){
        $id = I('request.id',0,'intval');
        if(!$id){
            $this->returnMsg(0,'你没有选择职位！');
        }
        if(IS_POST){
            $data = I('post.');
            $this->_update_jobs($data);
        }else{
            $category = D('Category')->get_category_cache();
            $this->assign('category',$category);
            $info = D('Jobs')->find($id);
            !$info && $info = D('JobsTmp')->find($id);
            if($info){
                $info['user'] = D('Members')->get_user_one(array('uid'=>$info['uid']));
                $info['contact'] = D('JobsContact')->where(array('pid'=>$info['id']))->find();
            }else{
                $this->returnMsg(0,'没有找到对应的职位！');
            }
            $telarray = explode('-',$info['contact']['landline_tel']);
            $age_arr = explode('-',$info['age']);
            $info['minage'] = $age_arr[0];
            $info['maxage'] = $age_arr[1];
            $this->assign('info',$info);
            $this->assign('telarray',$telarray);
            $this->display();
        }
    }
    protected function _update_jobs($data){
        $data['negotiable'] = $data['negotiable']?1:0;
        if($data['negotiable'] == 0){
            if(!$data['minwage']){
                $this->returnMsg(0,'请填写最低薪资！');
            }
            if(!$data['maxwage']){
                $this->returnMsg(0,'请填写最高薪资！');
            }
            if($data['minwage'] > $data['maxwage']){
                $this->returnMsg(0,'最低薪资不能大于最高薪资！');
            }
            if($data['maxwage'] > $data['minwage']*2){
                $this->returnMsg(0,'最高薪资不能大于最低薪资的2倍！');
            }
        }
        $data['contact_show'] = $data['contact_show']?1:0;
        $data['email_show'] = $data['email_show']?1:0;
        $data['telephone_show'] = $data['telephone_show']?1:0;
        $data['landline_tel_show'] = $data['landline_tel_show']?1:0;
        $data['landline_tel'] = $data['landline_tel_first'].'-'.$data['landline_tel_next'].'-'.$data['landline_tel_last'];
        if(!$data['landline_tel_next'] && !$data['telephone']){
            $this->returnMsg(0,'固话或手机号必填一项！');
        }
        $userinfo = D('Members')->get_user_one(array('uid'=>$data['uid']));
        $jobcategory = $data['jobcategory'];
        $jobcategory_arr = explode(".", $jobcategory);
        $data['topclass']= $jobcategory_arr[0];
        $data['category']= $jobcategory_arr[1];
        $data['subclass']= $jobcategory_arr[2];
        $rst = D('Jobs')->admin_edit_jobs($data,$userinfo);
        if($rst['state']==0){
            $this->returnMsg(0,$rst['error']);
        }else{
            $this->returnMsg(1,'修改职位成功！',U('Jobs/index'));
        }
    }
}
?>