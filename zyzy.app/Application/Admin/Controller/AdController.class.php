<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
use Common\ORG\qiniu;
class AdController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Ad');
    }
    public function _before_index(){
        $tpl = C('qscms_template_dir');
        $this->order = 'show_order desc';
    	if(false === $category_list = F('ad_cate_list_'.$tpl)) $category_list = D('AdCategory')->ad_cate_cache($tpl);
    	$this->assign('category_list',$category_list);
    }
    public function _before_add(){
        if(false === $category_list = F('ad_cate_list_'.$tpl.$cache)) $category_list = D('AdCategory')->ad_cate_cache($tpl);
        $this->assign('category_list',$category_list);
    }
    public function _before_insert($data){
		if($data['type_id'] == 2 || $data['type_id'] == 4 ||  $data['type_id'] == 5){
            if($_POST['attach_file']){
                $data['content'] = I('post.attach_file','','trim');
            }else{
                $data['content'] = I('post.attach_path','','trim');
            }
        }
        $data['theme'] = C('qscms_template_dir');
    	return $data;
    }
    public function _before_edit(){
        $this->_before_add();
    }
    public function _before_update($data){
        return $this->_before_insert($data);
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
            }
        }else{
            $settr=I('get.settr');
            if($settr =='0'){
                $data['deadline']=array(array('neq',0),array('lt',time()),'and');
            }elseif($settr > 0){
                $data['deadline']=array(array('neq',0),array('elt',strtotime("+".$settr." day")),array('gt',time()),'and');
            }
        }
        return $data;
    }
    /**
     * [category 广告位管理]
     */
    public function category(){
        $this->_name = 'AdCategory';
        $this->index();
    }
    /**
     * [category_add 广告位添加]
     */
    public function category_add(){
        if(IS_POST){
            $this->_name = 'AdCategory';
            $this->add();
        }else{
            $this->display();
        }
    }
    /**
     * [category_edit 广告位编辑]
     */
    public function category_edit(){
        $this->_name = 'AdCategory';
        $this->edit();
    }
    /**
     * [category_del 广告位删除]
     */
    public function category_del(){
        $this->_name = 'AdCategory';
        $this->delete();
    }
}
?>