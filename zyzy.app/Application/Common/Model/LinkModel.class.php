<?php
namespace Common\Model;
use Think\Model;
class LinkModel extends Model{
	protected $_validate = array(
		array('alias,link_name,link_url','identicalNull','',0,'callback'),
		array('show_order','identicalEnum','',0,'callback'),
		array('link_name,link_url,link_logo,notes','identicalLength_255','',0,'callback'),
		array('alias','1,30','{%link_length_error_alias}',0,'length'), // 调用名称

	);
	protected $_auto = array ( 
		array('display',1),//是否显示
		array('show_order',255),//友情链接排序
	);
	/**
	 * 验证友情链接表字段合法性
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_255($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=255) return 'link_length_error_'.$key;
		}
		return true;
	}
	/**
	 * [link_cache 友情链接缓存]
	 */
	public function link_cache(){
		$link_data = $this->where(array('display'=>1))->order('show_order')->getfield('link_id,alias,link_name,link_url,link_logo');
		foreach ($link_data as $key => $val) {
			$links[$val['alias']][$val['link_id']] = $val;
		}
		F('link_list',$links);
		return $links;
	}
	/**
	 * [get_link_cache 获取友情链接内容]
	 */
	public function get_link_cache($alias='all'){
		if(false === $links = F('link_list')){
			$links = $this->link_cache();
		}
		if($alias === 'all') return $links;
		return $links[$alias];
	}
}
?>