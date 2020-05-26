<?php
namespace Common\Model;
use Think\Model;
class WeixinMenuModel extends Model{
	protected $_validate = array(
        array('title', 'require', '{%menu_name_require}'), //菜单名称为必须
        array('type', array('click','view','miniprogram'),'{%menu_type_error}',1,'in'), 
        array('url', '0,255', '{%menu_url_length}',2,'length'), //方法名称必须
        array('parentid','number','{%menu_parentid_error}',2,'regex'),
        array('menu_order','number','{%menu_order_error}',2,'regex'),
    );
    protected $_auto = array (
        array('menu_order',255),
        array('status',1),
    );
	/**
	 * [menu_chche 微信菜单缓存]
	 */
	public function menu_chche(){
		$menu_data = $this->order('menu_order desc,id')->getfield('id,parentid,title');
		foreach ($menu_data as $key => $val) {
			$menu_list[$val['parentid']][$val['id']] = $val['title'];
		}
		F('weixin_menu_list',$menu_list);
		return $menu_list;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('weixin_menu_list', NULL);
    }
	/**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        $options['where']['id'][1] && $this->where(array('parentid'=>array('in',$options['where']['id'][1])))->delete();
        $this->_before_write();
    }
}
?>