<?php
namespace Common\Model;

use Think\Model;

class GiftModel extends Model {
    protected $_validate = array(
		array('gift_name,price,setmeal_id,effectivetime','identicalNull','',0,'callback'),
	);
	/**
	 * [get_article_list 获取新闻列表]
	 * @param  	data 	搜索条件 (关键字 显示状态 资讯类型等)
	 * @param 	page 	是否开启分页 (1=>开启 0=>不开启)
	 * @param 	pagesize 若开启分页 则表示 一页显示条数 ; 若没有开启分页 则表示 要显示条数
	 * @return 	result   新闻数据 (list:数据  page:分页)
	 */
	public function get_gift_list($data,$page=1,$pagesize=10){
		// 开启分页
		if($page){
			$count = $this->where($data)->count();
			$pager =  pager($count,$pagesize);
			$article_list = $this->where($data)->order('addtime desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
			$result['page']=$pager->fshow();
			$result['next_page']=$pager->ajax_show(0);
		}else{
			$article_list = $this->where($data)->order('addtime desc')->limit($pagesize)->select();
		}
		// 处理数据信息
		foreach ($article_list as $key => $val){
			$val['addtime'] = daterange(time(),$val['addtime'],'Y-m-d',"#FF3300");
			$page?$result['list'][$key] = $val:$result[$key] = $val;
		}
		return $result;
	}
}

?>