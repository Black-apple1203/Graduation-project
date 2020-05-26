<?php 
namespace Common\Model;
use Think\Model\RelationModel;
class ArticleModel extends RelationModel
	{
		protected $_validate = array(
			array('title,parentid','identicalNull','',0,'callback'),
			array('title','1,100','{%article_length_error_title}',0,'length'),
			array('type_id,parentid','identicalEnum','',0,'callback'),
		);
		protected $_auto = array (
			array('is_display',1),
			array('article_order',255),
			array('click',1),
			array('robot',1),
		);
		protected $_link = array(
	        'category' => array(
	            'mapping_type' => self::BELONGS_TO,
	            'class_name' => 'ArticleCategory',
	            'foreign_key' => 'type_id',
	        ),
	        'parent' => array(
	            'mapping_type' => self::BELONGS_TO,
	            'class_name' => 'ArticleCategory',
	            'foreign_key' => 'parentid',
	        ),
	        'property' => array(
	            'mapping_type' => self::BELONGS_TO,
	            'class_name' => 'ArticleProperty',
	            'foreign_key' => 'focos',
	        )
	    );
		/**
		 * [get_article_list 获取新闻列表]
		 * @param  	data 	搜索条件 (关键字 显示状态 资讯类型等)
		 * @param 	page 	是否开启分页 (1=>开启 0=>不开启)
		 * @param 	pagesize 若开启分页 则表示 一页显示条数 ; 若没有开启分页 则表示 要显示条数
		 * @return 	result   新闻数据 (list:数据  page:分页)
		 */
		public function get_article_list($data,$page=1,$pagesize=10){
			// 开启分页
			if($page){
				$count = $this->where($data)->count();
				$pager =  pager($count,$pagesize);
				$article_list = $this->where($data)->order('article_order desc,addtime desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
				$result['page']=$pager->fshow();
				$result['next_page']=$pager->ajax_show(0);
			}else{
				$article_list = $this->where($data)->order('article_order desc,addtime desc')->limit($pagesize)->select();
			}
			// 处理数据信息
			foreach ($article_list as $key => $val){
				$val['addtime'] = daterange(time(),$val['addtime'],'Y-m-d',"#FF3300");
				$page?$result['list'][$key] = $val:$result[$key] = $val;
			}
			return $result;
		}
		/**
		 * [get_article_one 获取新闻资讯详细信息]
		 * @param  	data 	查询条件 (关键字 显示状态 资讯类型等)
		 * @return 	result   新闻资讯详情数据
		 */
		public function get_article_one($data)
		{
			$val = $this->where($data)->find();
			if(!$val) return false;
			// 处理 添加时间 数据信息
			$val['addtime'] = daterange(time(),$val['addtime'],'Y-m-d',"#FF3300");
			//资讯类型
			$type_cn = M('ArticleCategory')->field('categoryname')->where(array('id'=>$val['type_id']))->find();
			$val['type_cn'] = $type_cn['categoryname'];
			return $val;
		}
	}
?>