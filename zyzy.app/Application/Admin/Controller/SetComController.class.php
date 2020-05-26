<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SetComController extends ConfigbaseController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
        $this->_name = 'Config';
    }
    public function index(){
        if(IS_POST){
            parent::_edit();
        }else{
            $setmeal = D('Setmeal')->select();
            $this->assign('setmeal',$setmeal);
            $increment = D('SetmealIncrement')->select();
            $this->assign('increment',$increment);
            $this->assign('cate_arr',D('SetmealIncrement')->cate_arr);
            parent::edit();
        }
    }
    public function set_task(){
        $model = D('Task');
        if(IS_POST){
            $idArr = I('post.id');
            $pointsArr = I('post.points');
            $timesArr = I('post.times');
            foreach ($idArr as $key => $val) {
                $data['points'] = $pointsArr[$key];
                $data['times'] = $timesArr[$key];
                $data['status'] = intval($_POST['status_'.$val]);
                $model->where(array('id' => $val))->save($data);
                unset($data);
            }
            $this->returnMsg(1,L('operation_success'));
            exit;
        }
        $list = $model->where(array('utype' => array('eq', 1)))->select();
		$commonlist = $model->where(array('utype' => array('eq', 0)))->select();//公共任务
        $this->assign('list',$list);
		$this->assign('commonlist',$commonlist);//公共任务
        $this->display();
    }
    public function login_remind(){
        parent::_edit();
        $this->display();
    }
	 public function search(){
        parent::_edit();
        $this->display();
    }
	public function set_points(){
        parent::_edit();
        $this->display();
    }
	public function set_meal(){
        if(IS_POST){
            parent::_edit();
        }else{
            $setmeal_show = D('Setmeal')->where(array('display'=>1))->order('show_order desc,id')->select();
            $setmeal = D('Setmeal')->order('show_order desc,id')->select();
            $this->assign('setmeal_show',$setmeal_show);
            $this->assign('setmeal',$setmeal);
        }
        $this->display();
    }
	public function set_audit(){
        parent::_edit();
        $this->display();
    }
	public function set_quick(){
        parent::_edit();
        $this->display();
    }
}