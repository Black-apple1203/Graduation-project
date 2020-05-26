<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class SetmealController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }
    public function _after_insert($id,$data){
    	$folder = QSCMS_DATA_PATH.'upload/setmeal_img/';
    	if(!file_exists($folder.$id.'.png')){
    		copy($folder.'2.png',$folder.$id.'.png');
    	}
    }
}