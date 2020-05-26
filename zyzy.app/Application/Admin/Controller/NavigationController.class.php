<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class NavigationController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Navigation');
    }
    public function _before_index(){
        $this->sort = 'navigationorder';
        $this->order = 'navigationorder desc,id';
        $_REQUEST['alias'] = I('request.alias','QS_top','trim');
    	if(false === $categroy = F('nav_categroy')) $categroy = D('NavigationCategory')->nav_categroy_cache();
    	$this->assign('categroy',$categroy);
    }
    public function _before_add(){
    	$page = D('Page')->page_cache();
    	$this->assign('page_list',$page);
    	$this->_before_index();
    }
    public function _before_edit(){
        $this->_before_add();
    }
    public function nav_all_save(){
        $save_id = I('post.save_id');
        $title = I('post.title');
        $navigationorder = I('post.navigationorder');
        if(is_array($save_id) && count($save_id)>0){
            foreach($save_id as $k=>$v){
                $setsqlarr['title']=trim($title[$k]);
                $setsqlarr['navigationorder']=intval($navigationorder[$k]);
                if(false === $this->_mod->where(array('id'=>intval($save_id[$k])))->save($setsqlarr)){
                    $this->error('保存失败！');
                }
            }
        }
        $this->success('保存成功！');
    }
    /**
     * [category 导航分类管理]
     */
    public function category(){
        $this->_name = 'NavigationCategory';
        $this->index();
    }
    /**
     * [category_add 导航分类添加]
     */
    public function category_add(){
        if(IS_POST){
            $this->_name = 'NavigationCategory';
            $this->add();
        }else{
            $this->display();
        }
    }
    /**
     * [category_edit 导航分类编辑]
     */
    public function category_edit(){
        $this->_name = 'NavigationCategory';
        $this->edit();
    }
    /**
     * [category_del 导航分类删除]
     */
    public function category_del(){
        $this->_name = 'NavigationCategory';
        $this->delete();
    }
}
?>