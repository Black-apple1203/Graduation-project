<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
use Common\ORG\qiniu;
class HrtoolsController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [_before_index hr工具箱列表]
     */
    public function _before_index(){
    	$this->list_relation = true;
        if(false === $category = F('hrtools_category')) $category = D('HrtoolsCategory')->category_cache();
        $this->assign('category',$category);
        $this->order = 'h_order desc,h_id desc';
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
                    $data['h_filename'] = array('like','%'.$key.'%');
                    break;
            }
        }else{
            $parentid = I('get.parentid',0,'intval');
            if($parentid){
                $data['h_typeid'] = array('eq',$parentid);
            }
        }
        return $data;
    }
    /**
     * [_before_add 添加hr工具箱]
     */
    public function _before_add(){
    	if(IS_POST){
    		if(!$_FILES['upfile']['name']){
                $_POST['h_fileurl'] = I('post.url','','trim');
                return false;
            }
            $config_params = array(
                'upload_ok'=>false,
                'url'=>'',
                'info'=>''
            );
            //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
            if(C('qscms_qiniu_open')==1){
                $qiniu = new qiniu(array(
                    'maxSize'=>3000,
                    'exts'=>'doc,docx,ppt,pptx,xls,xlsx,rtf'
                ));
                $img_url = $qiniu->upload($_FILES,'upfile');
                if($img_url){
                    $config_params['upload_ok'] = true;
                    $config_params['url'] = $img_url;
                    $config_params['info'] = '';
                }else{
                    $config_params['info'] = $qiniu->getError();
                }
            }else{
                $date = date('y/m/d/');
                $result = $this->_upload($_FILES['upfile'], 'hrtools/' . $date, array(
                        'maxSize' => 3000,
                        'uploadReplace' => true,
                        'attach_exts' => 'doc,docx,ppt,pptx,xls,xlsx,rtf'
                ));
                if ($result['error']) {
                    $config_params['upload_ok'] = true;
                    $config_params['url'] = $date.$result['info'][0]['savename'];
                    $config_params['info'] = '';
                } else {
                    $config_params['info'] = $result['info'];
                }
            }
            if($config_params['upload_ok']){
                $_POST['h_fileurl'] = $config_params['url'];
            }else{
                $this->error($config_params['info']);
            }
            $_POST['h_strong'] = $_POST['h_strong']?$_POST['h_strong']:0;
    	}else{
            if(false === $category = F('hrtools_category')) $category = D('HrtoolsCategory')->category_cache();
            $this->assign('category',$category);
        }
    }
    /**
     * [_before_edit 修改hr工具箱信息]
     */
    public function _before_edit(){
        $_POST['h_strong'] = $_POST['h_strong']?$_POST['h_strong']:0;
    	$this->_before_add();
    }
    /**
     * [category hr工具箱分类列表]
     */
    public function category(){
        $this->_name = 'HrtoolsCategory';
        $this->order = 'c_order desc,c_id';
        $this->index();
    }
    /**
     * [add_category 添加hr工具箱分类]
     */
    public function add_category(){
        $this->_name = 'HrtoolsCategory';
        $this->add();
    }
    /**
     * [edit_category 修改hr工具箱分类]
     */
    public function edit_category(){
        $this->_name = 'HrtoolsCategory';
        $this->edit();
    }
    /**
     * [del_category 删除hr工具箱分类]
     */
    public function del_category(){
        $this->_name = 'HrtoolsCategory';
        $this->_map['c_adminset'] = array('neq',1);
        $this->delete();
    }
}
?>