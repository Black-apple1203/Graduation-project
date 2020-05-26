<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CronsController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Crons');
    }
    /**
     * 计划任务列表
     */
    public function index(){
        $cache = $this->_mod->get_cron_cache();
        $this->assign('cache',$cache);
        parent::index();
    }
    /**
     * 执行
     */
    public function execution(){
        $cronid = I('get.cronid',0,'intval');
        $cron_info = D('Crons')->where(array('cronid'=>$cronid))->find();
        import('Common.Cron.'.$cron_info['filename']);
        $class_name = '\\'.$cron_info['filename'];
    	$model = new $class_name;
        $model->run();
    	$this->success(L('operation_success'));
    }
    // /**
    //  * 历史记录
    //  */
    // public function log(){
    // 	$file=scandir(CRON_LOG_PATH);
    // 	$file = array_diff($file,array('..','.','.svn'));
    // 	$list = array();
    // 	if($file){
    // 		foreach ($file as $key => $value) {
	   //  		$list[] = file_get_contents(CRON_LOG_PATH.$value);
	   //  	}
    // 	}
    // 	var_dump($list);die;
    //     $count      = count($list);// 查询满足要求的总记录数 $map表示查询条件
	   //  $Page       = pager($count,10);// 实例化分页类 传入总记录数
	   //  $show       = $Page->fshow();// 分页显示输出
	   //  // 进行分页数据查询
	   //  $list = array_slice($list,$Page->firstRow,$Page->listRows);
	   //  $this->assign('list',$list);// 赋值数据集
	   //  $this->assign('page',$show);// 赋值分页输出
	   //  $this->display();
    // }
}