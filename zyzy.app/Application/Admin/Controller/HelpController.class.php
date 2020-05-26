<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class HelpController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [_before_index 帮助列表]
     */
    public function _before_index(){
        if(false === $category = F('help_category')) $category = D('HelpCategory')->category_cache();
    	$this->list_relation = true;
        $this->assign('parentid',I('get.parentid',0,'intval'));
        $this->assign('category',$category);
        $this->order = 'ordid desc,addtime desc';
    }
    /**
     * [_before_search 查询条件]
     */
    public function _before_search($data){
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $data['title'] = array('like','%'.$key.'%');
                    break;
                case 2:
                    $data['id'] = intval($key);
                    break;
            }
        }
        return $data;
    }
    /**
     * [_before_add 添加帮助]
     */
    public function _before_add(){
        if(false === $category = F('help_category')) $category = D('HelpCategory')->category_cache();
    	if(IS_POST){
            $type_id = I('request.type_id',0,'intval');
            if($category[$type_id]){
                $_POST['parentid'] = $type_id;
                $_POST['type_id'] = 0;
            }else{
                $_POST['parentid'] = D('HelpCategory')->where(array('id'=>$type_id))->getfield('parentid');
            }
    		
    	}else{
            $this->assign('category',$category);
        }
    }
    /**
     * [_before_edit 修改帮助信息]
     */
    public function _before_edit(){
    	$this->_before_add();
    }
}
?>