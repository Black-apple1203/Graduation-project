<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SafetyController extends ConfigbaseController {
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * 验证设置
     */
    public function index(){
        $this->_edit();
        $this->display();
    }
    /**
     * 禁止ip设置
     */
    public function ip_filter(){
        $this->_edit();
        $this->display();
    }
    /**
     * 敏感词列表
     */
    public function badword_index(){
        $this->_name = 'Badword';
        if(IS_POST){
            $this->_edit();
            exit;
        }
        $this->_tpl = 'badword_index';
        parent::index();
    }
    /**
     * 添加敏感词
     */
    public function badword_add(){
        $this->_name = 'Badword';
        $this->add();
    }
    /**
     * 编辑敏感词
     */
    public function badword_edit(){
        $this->_name = 'Badword';
        $this->edit();
    }
    /**
     * 删除敏感词
     */
    public function badword_delete(){
        $this->_name = 'Badword';
        $this->delete();
    }
    /**
     * 后台访问ip白名单设置
     */
    public function backend_allow_ip(){
        $this->_edit();
        $this->display();
    }
}