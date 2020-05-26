<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ArticleCategoryController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->mod = D('ArticleCategory');
    }
    public function index(){
        $data = $this->mod->order('parentid,category_order desc,id')->select();
        foreach ($data as $key => $val) {
            if(!$val['parentid']){
                $list[$val['id']] = $val;
            }else{
                $list[$val['parentid']]['sub'][] = $val;
            }
        }
        $this->assign('list', $list);
        $this->display();
    }
    public function _before_search(){
        $this->sort = 'parentid';
        $this->order = 'id asc';
    }
    public function _after_search(){
        $data = $this->get('list');
        foreach ($data as $key => $val) {
            if(!$val['parentid']){
                $list[$val['id']] = $val;
            }else{
                $list[$val['parentid']]['sub'][] = $val;
            }
        }
        $this->assign('list',$list);
    }
    public function _before_add(){
        $article_category = $this->mod->get_article_category_cache(0);
        $this->assign('article_category',$article_category);
    }
    public function _after_select($data){
        $this->_before_add();
    }
    public function _before_delete(){
        $this->_map['admin_set'] = array('neq',1);
    }
}
?>