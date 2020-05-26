<?php
namespace Common\Model;
use Think\Model;
class NavigationModel extends Model{
	protected $_validate = array(
		array('alias,title,pagealias','identicalNull','',0,'callback'),
		//array('alias,title,pagealias,tag,target','identicalLength_100','',0,'callback'),
		//array('url','0,200','{%navigation_length_error_url}',0,'length'),

	);
	protected $_auto = array ( 
		array('urltype',0),//导航类型 系统内容0，其他链接1
		array('display',0),
		array('navigationorder',0),
		array('target','_blank'),
	);
	/**
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_100($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=100) return 'navigation_length_error_'.$key;
		}
		return true;
	}
	/**
	 * [nav_cache 读取页导航写入缓存]
	 * @return [array] [description]
	 */
	public function nav_cache(){
		$nav_data = $this->field('alias,title,urltype,pagealias,tag,target,color,list_id,url')->where(array('display'=>1))->order('navigationorder desc,id')->select();
		foreach ($nav_data as $val) {
			$k = $val['alias'];
			unset($val['alias']);
			$nav_list[$k][] = $val;
		}
		F('nav_list',$nav_list);
		return $nav_list;
	}
	/**
	 * 个人会员中心项部菜单导航缓存
	 */
	public function personal_nav_cache(){
		$personal_nav_list = $this->field('title,pagealias,tag,target,color,list_id,url')->where(array('alias'=>'QS_top','is_personal'=>1,'display'=>1))->order('navigationorder desc,id')->select();
		F('personal_nav_list',$personal_nav_list);
		return $personal_nav_list;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('nav_list', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('nav_list', NULL);
    }
}
?>