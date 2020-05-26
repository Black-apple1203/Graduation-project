<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class FeedbackController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Feedback');
    }
    public function _before_index(){
    	$this->order = 'audit asc,addtime desc';
        $this->assign('count',parent::_pending('Feedback',array('audit'=>1)));
    }
    public function set_audit(){
        $id = I('request.id');
        $audit = I('request.audit',0,'intval');
        if(empty($id)){
            $this->error('请选择记录！');
        }
        !is_array($id) && $id = array($id);
        $r = $this->_mod->where(array('id'=>array('in',$id)))->setField('audit',$audit);
        if($r){
            $this->success('设置成功！响应行数'.$r);
        }else{
            $this->error('设置失败！');
        }
    }
}
?>