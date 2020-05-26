<?php 
namespace Common\Model;
use Think\Model;
class ArticleCategoryModel extends Model
	{
		protected $_validate = array(
			array('categoryname','require','{%article_category_null_error_categoryname}',1),
			array('categoryname','1,60','{%article_category_length_error_categoryname}',1,'length'),
		);
		protected $_auto = array (
			array('category_order',255),
			array('admin_set',0),
		);
		
		/**
		 * [article_category_cache 获取资讯数据写入缓存]
		 */
		public function article_category_cache(){
			$article = array();
			$articleData = $this->field('id,parentid,categoryname')->order('category_order desc,id')->select();
			foreach ($articleData as $key => $val) {
				$article[$val['parentid']][$val['id']] = $val['categoryname'];
			}
			F('article_category',$article);
			return $article;
		}
		/**
		 * [get_article_category_cache 读取资讯数据]
		 */
		public function get_article_category_cache($pid=0){
			if(false === $article = F('article_category')){
				$article = $this->article_category_cache();
			}
			if($pid === 'all') return $article;
			return $article[intval($pid)];
		}
		/**
	     * 后台有更新则删除缓存
	     */
	    protected function _before_write($data, $options) {
	        F('article_category', NULL);
	    }
	    /**
	     * 后台有删除也删除缓存
	     */
	    protected function _after_delete($data,$options){
	        $options['where']['id'][1] && $this->where(array('parentid'=>array('in',$options['where']['id'][1])))->delete();
	        F('article_category', NULL);
	    }
	}
?>