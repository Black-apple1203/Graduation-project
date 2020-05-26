<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class PromotionController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Promotion');
    }
    public function index(){
        $this->_name = 'Promotion';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'promotion';
        $key = I('get.key','','trim');
        $key_type = I('get.key_type',0,'intval');
        $uid = I('get.uid',0,'intval');
        $ptype = I('get.ptype','','trim');
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where['j.jobs_name']=array('like','%'.$key.'%');break;
                case 2:
                    $where['j.companyname']=array('like','%'.$key.'%');break;
                case 3:
                    $where['j.id']=array('eq',$key);break;
                case 4:
                    $where['j.company_id']=array('eq',$key);break;
                case 5:
                    $where[$table_name.'.cp_uid']=array('eq',$key);break;
            }
        }
        if ($settr = I('get.settr','','intval')){
            $days=intval($settr);
            $settr=strtotime($days." day");
            if ($days===0)
            {
                $where[$table_name.'.cp_endtime'] = array('lt',time());
            }
            else
            {
                $where[$table_name.'.cp_endtime'] = array('lt',$settr);
            }
        }
        $uid>0 && $where[$table_name.'.cp_uid'] = array('eq',$uid);
        $ptype!='' && $where[$table_name.'.cp_ptype'] = array('eq',$ptype);
        $this->where = $where;
        $this->order = $table_name.'.cp_id '.'desc';
        $this->join = 'inner join '.$db_pre.'jobs as j on '.$table_name.'.cp_jobid=j.id';
        $this->custom_fun = '_format_list';
        parent::index();
    }
    /**
     * 格式化列表
     */
    public function _format_list($list){
        return $this->_mod->format_list($list);
    }
    /**
     * 添加推广方案
     */
    public function add(){
        if(IS_POST){
            $data = I('post.');
            if($data['cp_ptype']=='-1' || !$data['cp_ptype']){
                $this->error("请选择推广类型！");
            }
            if(!$data['cp_days']){
                $this->error("请填写推广期限！");
            }
            if ($this->_mod->check_promotion($data['cp_jobid'],$data['cp_ptype']))
            {
                $this->error("此职位正在执行此推广！请选择其他职位或者其他推广方案");
            }
        }else{
            $this->assign('now',time());
        }
        parent::add();
    }

    public function _before_insert($data){
        $data['cp_starttime']=time();
        $data['cp_endtime']=strtotime("{$data['cp_days']} day");
        $jobs=D('Jobs')->get_jobs_one(array('id'=>$data['cp_jobid']));
        $data['cp_uid']=$jobs['uid'];
        return $data;
    }
    public function _after_insert($id,$data){
        $u=D('Members')->get_user_one(array('uid'=>$data['cp_uid']));
        $promotion = $data['cp_ptype']=='stick'?'职位置顶':'职位紧急';
        
        $this->_mod->set_job_promotion($data['cp_jobid'],$data['cp_ptype']);
        write_members_log(array('uid'=>$data['cp_uid'],'utype'=>1,'username'=>''),'promotion',$promotion,false,array('jobs_id'=>$data['cp_jobid']),C('visitor.id'),C('visitor.username'));
    }

    /**
     * ajax获取职位
     */
    public function ajax_get_jobs(){
        $type=I('get.type','','trim');
        $key=I('get.key','','trim');
        switch($type){
            case 'get_id':
                $id=intval($key);
                $where = array('id'=>array('eq',$id));
                $limit = 1;
                break;
            case 'get_jobname':
                $where = array('jobs_name'=>array('like','%'.$key.'%'));
                $limit = 30;
                break;
            case 'get_comname':
                $where = array('companyname'=>array('like','%'.$key.'%'));
                $limit = 30;
                break;
            case 'get_uid':
                $uid=intval($key);
                $where = array('uid'=>array('eq',$uid));
                $limit = 30;
                break;
        }
        $result = D('Jobs')->where($where)->limit($limit)->select();
        $info = array();
        foreach ($result as $key => $value) {
            $value['addtime']=date("Y-m-d",$value['addtime']);
            $value['deadline']=date("Y-m-d",$value['deadline']);
            $value['refreshtime']=date("Y-m-d",$value['refreshtime']);
            $value['company_url']=url_rewrite('QS_companyshow',array('id'=>$value['company_id']));
            $value['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$value['id']));
            $info[]=$value['id']."%%%".$value['jobs_name']."%%%".$value['jobs_url']."%%%".$value['companyname']."%%%".$value['company_url']."%%%".$value['addtime']."%%%".$value['deadline']."%%%".$value['refreshtime']."%%%";
        }
        if (!empty($info))
        {
        exit(implode('@@@',$info));
        }
        else
        {
        exit();
        }
    }
    /**
     * 取消推广
     */
    public function delete(){
        $id = I('post.id','','trim');
        if(!$id){
            $this->error('你没有选择职位！');
        }
        if (false===$this->_mod->del_promotion($id))
        {
            $this->error('取消推广失败！');
        }
        $this->success('取消推广成功');
    }
    /**
     * 修改推广
     */
    public function edit(){
        $this->assign('now',time());
        parent::edit();
    }
    public function _after_select($info){
        $jobs = D('Jobs')->get_jobs_one(array('id'=>$info['cp_jobid']));
        $jobs['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$jobs['id']));
        $jobs['company_url']=url_rewrite('QS_companyshow',array('id'=>$jobs['company_id']));
        $jobs['user']=D('Members')->get_user_one(array('uid'=>$jobs['uid']));
        $jobs['contact']=D('JobsContact')->where(array('pid'=>array('eq',$jobs['id'])))->find();
        $info['jobs'] = $jobs;
        return $info;
    }
    public function _before_update($data){
        $days=intval($data['cp_days']);
        if ($days>0)
        {
        $endtime=I('post.cp_endtime',0,'intval');
        $data['cp_endtime']=$endtime>time()?$endtime+($days*(3600*24)):strtotime("".$days." day");
        }
        return $data;
    }
}
?>