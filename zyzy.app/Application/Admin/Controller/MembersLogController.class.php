<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class MembersLogController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('MembersLog');
    }
    public function _after_search(){
        $this->assign('type_arr',D('MembersLog')->type_arr);
    }
    public function _before_search($data){
        if($settr = I('request.settr',0,'intval')){
            $data['log_addtime'] = array('gt',strtotime("-".$settr." day"));
        }
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $data['log_username'] = array('like','%'.$key.'%');
                    break;
                case 2:
                    $data['log_uid'] = $key;
                    break;
            }
        }
        return $data;
    }
}
?>