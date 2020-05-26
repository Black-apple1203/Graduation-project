<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
use Common\ORG\qiniu;
class LinkController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Link');
    }
    public function index(){
        $this->_name = 'Link';
        $db_pre = C('DB_PREFIX');
        $tablename = $db_pre.'link';
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $where[$tablename.'.link_name'] = array('like','%'.$key.'%');
                    break;
                case 2:
                    $where[$tablename.'.link_url'] = array('like','%'.$key.'%');
                    break;
            }
        }
        $this->order = 'show_order desc,link_id';
        $this->where = $where;
        $this->join = $db_pre.'link_category AS c ON '.$tablename.'.alias=c.c_alias';
        $category = D('LinkCategory')->select();
        $this->assign('category',$category);
        parent::index();
    }
    public function _before_add(){
        if(!IS_POST){
            $category = D('LinkCategory')->select();
            $this->assign('category',$category);
        }
    }

    public function _before_edit(){
        $this->_before_add();
    }
    public function _before_del($data){
        foreach ($data as $key => $value) {
            @unlink(C('qscms_attach_path')."link_logo/".$value['link_logo']);
        }
    }
    public function _before_insert($data){
        if($_POST['link_logo']){
            $data['link_logo'] = I('post.link_logo','','trim');
        }else{
            $data['link_logo'] = I('post.attach_path','','trim');
        }
        $data['theme'] = C('qscms_template_dir');
        return $data;
    }
    public function category(){
        $this->_name = 'LinkCategory';
        parent::index();
    }
    
    public function category_add(){
        $this->_name = 'LinkCategory';
        parent::add();
    }
    
    public function category_edit(){
        $this->_name = 'LinkCategory';
        parent::edit();
    }
    
    public function category_del(){
        $this->_name = 'LinkCategory';
        $this->_map['c_sys'] = array('neq',1);
        parent::delete();
    }
}