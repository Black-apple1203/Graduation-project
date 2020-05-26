<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ReportController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }
    public function index(){
        $type = I('get.type',1,'intval');
    	switch($type){
    		case 1:
    			$this->_name = 'Report';
    			break;
    		case 2:
    			$this->_name = 'ReportResume';
    			break;
    	}
        if($settr = I('get.settr',0,'intval')){
            $tmp_addtime = strtotime('-'.$settr.' day');
            $where['addtime'] = array('egt',$tmp_addtime);
        }
        $this->where = $where;
        $this->order = 'audit asc,id desc';
        $this->_after_search_report($this->_name);
        parent::index();
    }
    protected function _after_search_report($mod){
        $count[0] = parent::_pending('Report',array('audit'=>1));
        $count[1] = parent::_pending('ReportResume',array('audit'=>1));
        $this->assign('type_arr',D($mod)->type_arr);
        $this->assign('count',$count);
    }
    public function report_audit(){
    	$id = I('request.id');
    	$type = I('request.type',1,'intval');
		if ($type==1) {
			$this->_mod = D('Report');
			$rid=I('request.jobs_id');
		} else {
			$this->_mod = D('ReportResume');
			$rid=I('request.resume_id');
		}
    	$audit= I('request.audit',0,'intval');
		$audit=intval($_POST['audit']);
		if (empty($id))
		{
		  $this->error("您没有选择项目！");
		}
		if ($num=$this->_mod->report_audit($id,$audit,$rid))
		{
		  $this->success("设置成功！共影响 {$num}行 ");
		}
		else
		{
		  $this->success("设置成功0行！");
		}
    }
    public function delete(){
        if(1 == I('type','','intval')){
            $this->_name = 'Report';
        }else{
            $this->_name = 'ReportResume';
        }
        parent::delete();
    }
}