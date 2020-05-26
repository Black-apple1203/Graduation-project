<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
use Common\ORG\qiniu;
class ArticleController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('ArticleCategory');
    }
    /**
     * [_before_index 资讯列表]
     */
    public function _before_index(){
    	$article_category = $this->_mod->get_article_category_cache('all');
    	if(false === $article_property = F('article_property')){
    		$article_property = D('ArticleProperty')->article_property_cache();
    	}
    	$this->assign('article_property',$article_property);
    	$this->assign('article_category',$article_category);
    	$this->list_relation = true;
    	$this->assign('parentid',I('get.parentid',0,'intval'));
        $this->order = 'article_order desc, addtime desc';
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
        }else{
            if($settr=I('get.settr',0,'intval')){
                $data['addtime']=array('gt',strtotime("-".$settr." day"));
            }
        }
        return $data;
    }
    /**
     * [_before_add 添加资讯]
     */
    public function _before_add(){
        $article_category = $this->_mod->get_article_category_cache('all');
    	if(IS_POST){
            $type_id = I('request.type_id',0,'intval');
            if($article_category[$type_id]){
                $_POST['parentid'] = $type_id;
                $_POST['type_id'] = $type_id;
            }else{
                $_POST['parentid'] = $this->_mod->where(array('id'=>$type_id))->getfield('parentid');
            }
            if($addtime = I('request.addtime','','trim')){
                if(date('Y-m-d') == $addtime){
                    $_POST['addtime'] = time();
                }else{
                    $_POST['addtime'] = strtotime($addtime);
                }
            }else{
                $_POST['addtime'] = time();
            }
    	}else{
	    	if(false === $article_property = F('article_property')){
	    		$article_property = D('ArticleProperty')->article_property_cache();
	    	}
	    	$this->assign('article_property',$article_property);
	    	$this->assign('article_category',$article_category);
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
     * [del_img 删除缩略图]
     */
    public function del_img(){
    	$id = I('get.id',0,'intval');
    	$small_img = D('Article')->where(array('id'=>$id))->getfield('small_img');
    	false === $small_img && $this->error('新闻不存在或已经删除！');
    	if($small_img){
    		$reg = D('Article')->where(array('id'=>$id))->setfield('small_img','');
	    	if(false !== $reg){
	    		@unlink(C('qscms_attach_path')."images/".$small_img);
	    	}else{
	    		$this->error('缩略图删除失败，请重新操作！');
	    	}
    	}
    	$this->success('缩略图删除成功！');
    }
    /**
     * [property 资讯属性列表]
     */
    public function property(){
    	$this->_name='ArticleProperty';
        $this->order = 'category_order desc,id';
    	$this->index();
    }
    /**
     * [add_property 添加资讯属性]
     */
    public function add_property(){
    	$this->_name = 'ArticleProperty';
    	$this->add();
    }
    /**
     * [add_property 修改资讯属性]
     */
    public function edit_property(){
    	$this->_name = 'ArticleProperty';
    	$this->edit();
    }
    /**
     * [del_property 删除资讯属性]
     */
    public function del_property(){
    	$this->_name = 'ArticleProperty';
        $this->_map['admin_set'] = array('neq',1);
    	$this->delete();
    }
}
?>