<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BusinessSetmealController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CompanyProfile');
    }
    /**
     * 业务管理
     */
    public function index(){
        $this->_name = 'CompanyProfile';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'company_profile';
        $has_overtime = I('request.has_overtime','','trim');
        $sort = I('request.sortby','starttime','trim');
        $overtime = I('request.overtime',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        $join = array();
        $join[] = 'left join '.$db_pre."members as m on ".$this_t.".uid=m.uid";
        $join[] = 'left join '.$db_pre."members_setmeal as s on ".$this_t.".uid=s.uid";
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where[$this_t.'.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where[$this_t.'.id']=array('eq',$key);break;
                case 3:
                    $where['m.username']=array('like','%'.$key.'%');break;
                case 4:
                    $where[$this_t.'.uid']=array('eq',$key);break;
                case 5:
                    $where[$this_t.'.address']=array('like','%'.$key.'%');break;
                case 6:
                    $where[$this_t.'.telephone']=array('like','%'.$key.'%');break;
            }
        }else{
            if($settr=I('get.settr',0,'intval')){
                $where['addtime']=array('gt',strtotime("-".$settr." day"));
            }
            if($overtime>0){
                $where['s.endtime']=array(array('neq',0),array('elt',strtotime("+".$overtime." day")),'and');
            }else if($overtime==-1){
                $where['s.expire']=array('eq',1);
            }
            if($has_overtime=='1'){
                $where['s.expire']=array('eq',1);
            }else if($has_overtime=='0'){
                $where['s.expire']=array('eq',0);
            }
        }
        $this->where = $where;
        $this->field = $this_t.'.*,m.username,m.mobile,m.email as memail,s.starttime,s.endtime,if(s.endtime>0,s.endtime,(s.endtime+10000000000)) as sort_endtime';
        $this->order = $sort=='endtime'?'sort_endtime asc ,id asc':$sort.' desc ,id asc';
        $this->join = $join;
        $this->assign('count',parent::_pending('CompanyProfile',array('audit'=>2)));
        $setmeal = D('Setmeal')->get_setmeal_cache();
        $setmeal_arr = array();
        foreach ($setmeal as $key => $value) {
            $arr['name'] = $value;
            $arr['count'] = D('CompanyProfile')->where(array('setmeal_id'=>$key))->count();
            $setmeal_arr[] = $arr;
        }
        $count1 = D('CompanyProfile')->join('left join '.$db_pre."members_setmeal as s on ".$this_t.".uid=s.uid")->where(array('s.expire'=>1))->count();
        $count2 = D('CompanyProfile')->join('left join '.$db_pre."members_setmeal as s on ".$this_t.".uid=s.uid")->where(array('s.expire'=>0))->count();
        $this->assign('setmeal_arr',$setmeal_arr);
        $this->assign('setmeal',$setmeal);
        $this->assign('count1',$count1);
        $this->assign('count2',$count2);
        parent::index();
    }
    /**
     * 格式化列表
     */
    public function _custom_fun($list){
        $list = $this->_mod->admin_format_company_list($list);
        foreach ($list as $key => $value) {
            $list[$key]['leave_days'] = $value['endtime']==0?'-':intval(($value['endtime']-time())/86400);
        }
        return $list;
    }
    /**
     * 修改用户套餐设置
     */
    public function edit(){
        $uid = I('request.uid',1,'intval');
        $userinfo=D('Members')->get_user_one(array('uid'=>$uid));
        if(IS_POST)
        {
            $setsqlarr = I('post.');
            if (I('post.setendtime','','trim')<>"")
            {
                $setendtime=strtotime(I('post.setendtime','','trim'));
                if ($setendtime=='')
                {
                $this->returnMsg(0,'日期格式错误！');
                }
                else
                {
                $setsqlarr['endtime']=$setendtime;
                }
            }
            else
            {
            $setsqlarr['endtime']=0;
            }
            if (I('post.days','','trim')<>"")
            {
                $days = I('post.days',0,'intval');
                    if ($days<>0)
                    {
                        $oldendtime=I('post.oldendtime',0,'intval');
                        $setsqlarr['endtime']=strtotime("".$days." days",$oldendtime==0?time():$oldendtime);
                    }
                    if ($days==0)
                    {
                        $setsqlarr['endtime']=0;
                    }
            }
            $setmealtime=$setsqlarr['endtime'];
            if ($uid)
            {
                $setmeal=D('MembersSetmeal')->get_user_setmeal($uid);
                if(false === D('MembersSetmeal')->create($setsqlarr))
                {
                    $this->returnMsg(0,D('MembersSetmeal')->getError());
                }
                else
                {
                    if(false === D('MembersSetmeal')->where(array('uid'=>array('eq',$uid)))->save())
                    {
                        $this->returnMsg(0,'设置套餐失败！');
                    }
                }
                //会员套餐变更记录。管理员后台修改会员套餐：修改会员。3表示：管理员后台修改
                $setmeal['endtime']=date('Y-m-d',$setmeal['endtime']);
                $setsqlarr['endtime']=date('Y-m-d',$setsqlarr['endtime']);
                $setsqlarr['expense']=round($_POST['expense']);
                $notes=D('MembersSetmeal')->edit_setmeal_notes($setsqlarr,$setmeal);
                if ($setsqlarr['endtime']<>"")
                {
                    $jobs_deadline['setmeal_deadline']=$setmealtime;
                    $jobs_deadline['deadline']=$setmealtime;
                    M('Jobs')->where(array('uid'=>$uid))->save($jobs_deadline);
                    M('JobsTmp')->where(array('uid'=>$uid))->save($jobs_deadline);
                    D('Jobs')->distribution_jobs_uid($uid);
                }
                if(I('post.sms_num','','trim')<>""){
                    $sms_num = I('post.sms_num',0,'intval');
                    D('Members')->where(array('uid'=>$uid))->setField('sms_num',$sms_num);
                }
            }
            write_members_log(array('uid'=>$uid,'utype'=>1,'username'=>''),'setmeal',"操作人：".C('visitor.username').",说明：为会员 {$userinfo['username']} 修改套餐。".($notes?$notes:""),false,array(),C('visitor.id'),C('visitor.username'));
            $this->returnMsg(1,'操作成功！');
        }
        else
        {
            $where['log_type'] = 'setmeal';
            $where['log_uid'] = $uid;
            $log = D('MembersLog')->where($where)->select();
            $this->assign('log',$log);
            $this->_name = 'Members';
            $company_user=D('Members')->get_user_one(array('uid'=>$uid));
            $this->assign('company_user',$company_user);
            $this->assign('setmeal',D('MembersSetmeal')->get_user_setmeal($company_user['uid']));
            $this->assign('givesetmeal',D('Setmeal')->where(array('display'=>1))->order('show_order desc,id')->getField('id,setmeal_name'));
            parent::edit();
        }
    }
    /**
     * 重新开通用户套餐
     */
    public function user_setmeal_set(){
        $reg_service = I('post.reg_service',0,'intval');
		if(I('post.is_money',0,'intval') && I('post.money')){  //money
			$amount=round(I('post.money'),2);
			$ismoney=2;
		}else{
			$amount='0.00';
			$ismoney=1;
		}		
        if($reg_service==0){
            $this->returnMsg(0,'请选择套餐');
        }
        $uid = I('post.uid',1,'intval');
        $rst = D('MembersSetmeal')->set_members_setmeal($uid,$reg_service,$amount);
        if($rst['state']==1){
            //会员套餐变更记录。管理员后台修改会员套餐：重新开通套餐。3表示：管理员后台修改
            $userinfo=D('Members')->get_user_one(array('uid'=>$uid));
            $setmeal=M('Setmeal')->where(array('id'=>$reg_service))->find();
            $notes="操作人：".C('visitor.username').",说明：为会员 {$userinfo['username']} 重新开通【{$setmeal['setmeal_name']}】服务，收取服务金额：{$amount}元，服务ID：{$reg_service}。";
            write_members_log(array('uid'=>$uid,'utype'=>1,'username'=>$userinfo['username']),'setmeal',$notes,false,array(),C('visitor.id'),C('visitor.username'));
            $this->returnMsg(1,'修改成功!');
        }else{
            $this->returnMsg(0,$rst['error']);
        }
    }
}
?>