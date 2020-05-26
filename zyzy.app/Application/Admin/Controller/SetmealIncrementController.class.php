<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class SetmealIncrementController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_name = 'SetmealIncrement';
        $this->_mod = D('SetmealIncrement');
    }
    public function _before_index(){
        $this->pagesize = 100;
        $this->order = 'sort desc';
        $this->assign('cate_arr',$this->_mod->cate_arr);
        $this->assign('rule',C('qscms_setmeal_increment_pay_points_rule'));
    }
    public function _after_search(){
        $this->assign('service_type',D('SetmealIncrement')->cate_arr);
    }
    public function add(){
    	if(!IS_POST){
            $this->_mod->service_unit['auto_refresh_jobs'] = '天';
    		$this->assign('cate_arr',$this->_mod->cate_arr);
            $this->assign('unit_arr',$this->_mod->service_unit);
    		$this->assign('setmeal',D('Setmeal')->get_setmeal_cache());
    	}else{
    		$info = I('post.');
    		$info['discount'] = serialize($info['discount']);
    		$r = $this->_mod->create($info);
    		if($r){
    			$insert_id = $this->_mod->add();
    		}else{
    			$this->error($this->_mod->getError());
    		}
    		$this->returnMsg(1,'保存成功！');
    		exit;
    	}
    	$this->display();
    }
    public function edit(){
    	$id = I('request.id',0,'intval');
    	if(!$id){
            $this->returnMsg(0,'参数错误！');
    	}
    	if(!IS_POST){
            $this->_mod->service_unit['auto_refresh_jobs'] = '天';
    		$this->assign('cate_arr',$this->_mod->cate_arr);
            $this->assign('unit_arr',$this->_mod->service_unit);
    		$this->assign('info',$this->_mod->getone($id));
    		$this->assign('setmeal',D('Setmeal')->get_setmeal_cache());
    	}else{
    		$info = I('post.');
    		$info['discount'] = serialize($info['discount']);
    		$r = $this->_mod->create($info);
    		if($r){
    			$this->_mod->save();
    		}else{
    			$this->error($this->_mod->getError());
    		}
            $this->returnMsg(1,'保存成功！');
    		exit;
    	}

    	$this->display();
    }
    public function save_sort(){
        $id = I('post.id');
        $sort = I('post.sort');
        foreach ($id as $key => $value) {
            D('SetmealIncrement')->where(array('id'=>array('eq',intval($value))))->setField('sort',$sort[$key]);
        }
        $this->returnMsg(1,'保存成功！');
    }
    public function save_rule(){
        $post_data = I('post.');
        D('Config')->where(array('name'=>'setmeal_increment_pay_points_rule'))->setField('value',serialize($post_data));
        $this->returnMsg(1,'保存成功！');
    }
}