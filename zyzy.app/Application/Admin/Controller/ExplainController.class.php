<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ExplainController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [_before_index 资讯列表]
     */
    public function _before_index(){
    	$this->list_relation = true;
        if(false === $category = F('explain_category')) $category = D('ExplainCategory')->category_cache();
        $this->assign('category',$category);
        $this->order = 'show_order desc,id';
    }
    /**
     * [_before_search 查询条件]
     */
    public function _before_search($data){
        if($settr = I('request.settr',0,'intval')){
            $data['addtime'] = array('gt',strtotime("-".$settr." day"));
        }
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $data['title'] = array('like','%'.$key.'%');
                    break;
            }
        }
        return $data;
    }
    /**
     * [_before_add 添加资讯]
     */
    public function _before_add(){
    	if(!IS_POST){
    		if(false === $category = F('explain_category')) $category = D('ExplainCategory')->category_cache();
            $this->assign('category',$category);
    	}
    }
    /**
     * [_before_edit 修改资讯信息]
     */
    public function _before_edit(){
    	$this->_before_add();
    }
	/**
     * [_before_update 加粗是否有值]
     */
	public function _before_update($data){
		$data['tit_b'] = $data['tit_b']?1:0;
		return $data;
	}
    /**
     * [category 说明页分类列表]
     */
    public function category(){
        $this->_name = 'ExplainCategory';
        $this->order = 'category_order desc,id';
        $this->index();
    }
    /**
     * [add_category 添加说明页分类]
     */
    public function add_category(){
        $this->_name = 'ExplainCategory';
        $this->add();
    }
    /**
     * [edit_category 修改说明页分类]
     */
    public function edit_category(){
        $this->_name = 'ExplainCategory';
        $this->edit();
    }
    /**
     * [del_category 删除说明页分类]
     */
    public function del_category(){
        $this->_name = 'ExplainCategory';
        $this->_map['admin_set'] = array('neq',1);
        $this->delete();
    }
}
?>