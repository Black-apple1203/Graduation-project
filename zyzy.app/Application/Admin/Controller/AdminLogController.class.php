<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class AdminLogController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('AdminLog');
    }
    /**
     * [_before_search 查询条件]
     */
    public function _before_search($data){
    	$data['operater'] = array('eq',$_GET['admin_name']);
        $settr = I('request.settr',0,'intval');
        $settr>0 && $data['log_addtime'] = array('egt',strtotime('-'.$settr.' day'));
        return $data;
    }
}
?>