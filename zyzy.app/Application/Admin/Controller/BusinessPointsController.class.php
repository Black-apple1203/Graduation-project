<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BusinessPointsController extends BackendController{
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
        $overtime = I('request.overtime',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        $join = array();
        $join[] = 'left join '.$db_pre."members as m on ".$this_t.".uid=m.uid";
        $join[] = 'left join '.$db_pre."members_points as s on ".$this_t.".uid=s.uid";
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
        }
        $this->where = $where;
        $this->field = $this_t.'.*,m.reg_time,m.username,m.mobile,m.email as memail,s.points';
        $this->order = 'field('. $this_t.'.audit,2) desc ,id '.'desc';
        $this->join = $join;
        $this->assign('count',parent::_pending('CompanyProfile',array('audit'=>2)));
        $setmeal = D('Setmeal')->get_setmeal_cache();
        $this->assign('setmeal',$setmeal);
        parent::index();
    }
    /**
     * 格式化列表
     */
    public function _custom_fun($list){
        return $this->_mod->admin_format_company_list($list);
    }
    /**
     * 修改用户积分设置
     */
    public function edit(){
        $uid = I('request.uid',1,'intval');
        if(IS_POST)
        {
        $points_type = I('post.points_type',1,'intval');
        $t=$points_type==1?"+":"-";
        $points = I('post.points',1,'intval');
        D('MembersPoints')->report_deal($uid,$points_type,$points);
        $userinfo=D('Members')->get_user_one(array('uid'=>$uid));
        $user_points=D('MembersPoints')->get_user_points($userinfo['uid']);

        
        //会员积分变更记录。管理员后台修改会员的积分。3表示：管理员后台修改
        if(I('post.is_money',0,'intval') && I('post.log_amount')){
            $amount=round(I('post.log_amount'),2);
            $ismoney=2;
        }else{
            $amount='0.00';
            $ismoney=1;
        }
        $notes="操作人：".C('visitor.username').",说明：修改会员 {$userinfo['username']} ".C('qscms_points_byname')." ({$t}{$points})。收取".C('qscms_points_byname')."金额：{$amount} 元，备注：".I('post.points_notes','','trim');
        write_members_log(array('uid'=>$uid,'utype'=>1,'username'=>''),'points',$notes,false,array(),C('visitor.id'),C('visitor.username'));
        $this->returnMsg(1,'保存成功！');
        }
        else
        {
            $where['uid']=I('get.uid',0,'intval');
            $list = D('MembersHandsel')->get_handsel_list($where);
            $this->assign('list',$list);
            $this->_name = 'Members';
            $company_user=D('Members')->get_user_one(array('uid'=>$uid));
            $this->assign('company_user',$company_user);
            $this->assign('userpoints',D('MembersPoints')->get_user_points($company_user['uid']));
            parent::edit();
        }
    }
}
?>