<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class OrderController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Order');
    }
    /**
     * 企业订单
     */
    public function index(){
        $this->_name = 'Order';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'order';
        $this->order = 'field('.$table_name.'.is_paid,1,3,2),'.$table_name.'.addtime '.'desc';
        $type = I('get.type','','trim');
        $key = I('get.key','','trim');
        $key_type = I('get.key_type',0,'intval');
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where['c.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where['m.username']=array('eq',$key);break;
                case 3:
                    $where[$table_name.'.oid']=array('eq',$key);break;
            }
        }
        if ($settr = I('get.settr',0,'intval')){
            $tmpsettr=strtotime("-".$settr." day");
            $where[$table_name.'.addtime'] = array('gt',$tmpsettr);
        }
        if ($type){
            $where[$table_name.'.order_type'] = array('in',$type);
        }
        $where[$table_name.'.utype'] = 1;
        $this->where = $where;
        $this->field = $table_name.'.*,m.username,c.companyname';
        $joinsql[0] = $db_pre.'members as m on '.$table_name.'.uid=m.uid';
        $joinsql[1] = $db_pre.'company_profile as c on '.$table_name.'.uid=c.uid';
        $this->join = $joinsql;
        $payment_list=M('Payment')->where(array('p_install'=>2))->order('listorder desc')->select();
        $this->assign('payment_list',$payment_list);
        $this->assign('count1',parent::_pending('Order',array('utype'=>1,'is_paid'=>2)));
        $this->assign('count2',parent::_pending('Order',array('utype'=>1,'is_paid'=>1)));
        $this->assign('count3',parent::_pending('Order',array('utype'=>1,'is_paid'=>3)));
        parent::index();
    }
    /**
     * 个人订单
     */
    public function index_per(){
        $this->_name = 'Order';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'order';
        $type = I('get.type','','trim');
        $key = I('get.key','','trim');
        $key_type = I('get.key_type',0,'intval');
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where['m.username']=array('eq',$key);break;
                case 2:
                    $where[$table_name.'.oid']=array('eq',$key);break;
            }
        }
        if ($settr = I('get.settr',0,'intval')){
            $tmpsettr=strtotime("-".$settr." day");
            $where[$table_name.'.addtime'] = array('gt',$tmpsettr);
        }
        if ($type){
            $where[$table_name.'.order_type'] = array('in',$type);
        }
        $where[$table_name.'.utype'] = 2;
        $this->where = $where;
        $this->field = $table_name.'.*,m.username';
        $this->join = $db_pre.'members as m on '.$table_name.'.uid=m.uid';
        $this->order = $table_name.'.addtime '.'desc';
        $payment_list=M('Payment')->where(array('p_install'=>2))->order('listorder desc')->select();
        $this->assign('payment_list',$payment_list);
        parent::index();
    }
    /**
     * 企业订单设置
     */
    public function order_set(){
        if(IS_POST){
            if ($this->_mod->admin_order_paid(I('request.id',0,'intval')))
            {
                D('Order')->where(array('id'=>array('eq',I('request.id',0,'intval'))))->setField('notes',I('request.notes','','trim'));
                $this->returnMsg(1,'操作成功！');exit;
            }
            else
            {
                $this->returnMsg(0,'操作失败！');
            }
        }
        parent::edit();
    }
    /**
     * 个人订单设置
     */
    public function order_set_per(){
        $this->order_set();
    }
    /**
     * 企业订单详情
     */
    public function order_show(){
        if(IS_POST){
            D('Order')->where(array('id'=>array('eq',I('post.id',0,'intval'))))->setField('notes',I('notes','','trim'));
            $this->success('操作成功！');exit;
        }
        parent::edit();
    }
    /**
     * 个人订单详情
     */
    public function order_show_per(){
        $this->order_show();
    }
    public function _after_select($info){
        $user = D('Members')->where(array('uid'=>$info['uid']))->find();
        $info['userinfo'] = $user;
        return $info;
    }
    /**
     * 企业取消订单
     */
    public function order_cancel(){
        $id = I('request.id','','trim');
		$order_list = $this->_mod->where(array('id'=>array('in',$id)))->select();
		foreach($order_list as $key=>$val){
			if($val['order_type']==1 && intval($val['pay_gift'])>0){
				$issuedata['is_used']=2;
				$issuedata['usetime']=0;
				M('GiftIssue')->where(array('id'=>$val['gift_id']))->save($issuedata);
			}
		}
        $rst = $this->_mod->where(array('id'=>array('in',$id)))->setField('is_paid',3);
        if($rst){
            $this->success('成功取消'.$rst.'条订单！');
        }else{
            $this->error('取消订单失败！');
        }
    }
    /**
     * 个人取消订单
     */
    public function order_cancel_per(){
        $this->order_cancel();
    }
}