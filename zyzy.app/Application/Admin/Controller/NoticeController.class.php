<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class NoticeController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [_before_index 公告列表]
     */
    public function _before_index(){
    	$this->list_relation = true;
        if(false === $category = F('notice_category')) $category = D('NoticeCategory')->category_cache();
        $this->assign('category',$category);
        $this->order = 'sort desc,addtime desc';
    }
    /**
     * [_before_search 查询条件]
     */
    public function _before_search($data){
        if($settr = I('request.settr',0,'intval')){
            $data['addtime'] = array('gt',strtotime("-".$settr." day"));
        }
		 if($this->subsite_id!=""){
			$data['subsite_id'] = $this->subsite_id;
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
     * [_before_add 添加公告]
     */
    public function _before_add(){
    	if(!IS_POST){
    		if(false === $category = F('notice_category')) $category = D('NoticeCategory')->category_cache();
            $this->assign('category',$category);
    	}
    }
    /**
     * [_before_edit 修改公告信息]
     */
    public function _before_edit(){
    	$this->_before_add();
    }
    /**
     * [category 公告分类列表]
     */
    public function category(){
        $this->_name = 'NoticeCategory';
        $this->index();
    }
    /**
     * [add_category 添加公告分类]
     */
    public function add_category(){
        $this->_name = 'NoticeCategory';
        $this->add();
    }
    /**
     * [edit_category 修改公告分类]
     */
    public function edit_category(){
        $this->_name = 'NoticeCategory';
        $this->edit();
    }
	/**
     * [_before_update 加粗是否有值]
     */
	public function _before_update($data){
		$data['tit_b'] = $data['tit_b']?1:0;
		return $data;
	}
    /**
     * [del_category 删除公告分类]
     */
    public function del_category(){
        $this->_name = 'NoticeCategory';
        $this->_map['admin_set'] = array('neq',1);
        $this->delete();
    }
}
?>