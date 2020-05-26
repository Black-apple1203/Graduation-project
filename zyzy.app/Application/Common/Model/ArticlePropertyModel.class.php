<?php 
namespace Common\Model;
use Think\Model;
class ArticlePropertyModel extends Model
	{
		protected $_validate = array(
			array('categoryname','require','{%article_property_null_error_categoryname}'),
			array('categoryname','1,80','{%article_property_length_error_categoryname}',0,'length'),
		);
		protected $_auto = array (
			array('category_order',255),
			array('admin_set',0),
		);
		public function article_property_cache(){
			$article_property = $this->order('category_order desc,id')->getfield('id,categoryname');
			F('article_property',$article_property);
			return $article_property;
		}
	}
?>