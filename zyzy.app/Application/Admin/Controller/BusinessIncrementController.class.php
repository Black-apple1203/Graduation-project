<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BusinessIncrementController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Order');
    }
    /**
     * 业务管理
     */
    public function index(){
        $this->_name = 'Order';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'order';
        $where[$table_name.'.order_type'] = array('gt',5);
        $where[$table_name.'.utype'] = 1;
        $where[$table_name.'.is_paid'] = 2;
        $key = I('get.key','','trim');
        $key_type = I('get.key_type',0,'intval');
        if ($key && $key_type>0)
        {
            switch($key_type){
                case 1:
                    $where['c.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where['m.username']=array('eq',$key);break;
            }
        }
        if ($settr = I('get.settr',0,'intval')){
            $tmpsettr=strtotime("-".$settr." day");
            $where[$table_name.'.addtime'] = array('gt',$tmpsettr);
        }
        if ($order_type = I('get.order_type',0,'intval')){
            $where[$table_name.'.order_type'] = array('eq',$order_type);
        }
        $this->where = $where;
        $this->field = $table_name.'.*,m.username,c.companyname';
        $joinsql[0] = $db_pre.'members as m on '.$table_name.'.uid=m.uid';
        $joinsql[1] = $db_pre.'company_profile as c on '.$table_name.'.uid=c.uid';
        $this->join = $joinsql;
        $this->order = $table_name.'.addtime '.'desc';
        $increment_type = D('Order')->order_type;
        $increment_type_arr = array();
        unset($increment_type[1],$increment_type[2],$increment_type[3],$increment_type[4],$increment_type[5]);
        $this->assign('increment_type',$increment_type);
        foreach ($increment_type as $key => $value) {
            $arr['name'] = $value;
            $arr['count'] = parent::_pending('Order',array('order_type'=>array('eq',$key)));
            $increment_type_arr[] = $arr;
        }
        $this->assign('increment_type_arr',$increment_type_arr);
        $this->assign('count1',parent::_pending('Order',array('order_type'=>array('gt',5),'utype'=>1,'addtime'=>array(array('egt',strtotime('today')),array('lt',strtotime('today')+86400),'and'))));
        $this->assign('count2',parent::_pending('Order',array('order_type'=>array('gt',5),'utype'=>1,'addtime'=>array(array('egt',strtotime('today')-86400),array('lt',strtotime('today')),'and'))));
        $this->assign('count3',parent::_pending('Order',array('order_type'=>array('gt',5),'utype'=>1,'addtime'=>array(array('egt',strtotime('today')-86400*2),array('lt',strtotime('today')-86400),'and'))));
        parent::index();
    }
}
?>