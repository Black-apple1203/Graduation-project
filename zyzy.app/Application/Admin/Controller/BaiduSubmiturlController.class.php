<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BaiduSubmiturlController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('BaiduSubmiturl');
    }
    public function index(){
        if(IS_POST){
            foreach (I('post.') as $key => $val) {
                $this->_mod->where(array('name' => $key))->save(array('value' => $val));
            }
            $this->success(L('operation_success'));
            exit;
        }
        $baidudata = $this->_mod->select();
        $info = array();
        foreach ($baidudata as $key => $value) {
            $info[$value['name']] = $value['value'];
        }
        $this->assign('info',$info);
        $this->display();
    }
}