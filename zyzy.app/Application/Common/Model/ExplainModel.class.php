<?php 
namespace Common\Model;
use Think\Model\RelationModel;
class ExplainModel extends RelationModel
{
	protected $_validate = array(
		array('type_id,title,content','identicalNull','',1,'callback'),
	);
	protected $_auto = array (
		array('addtime','time',1,'function'),
		array('click',1),
		array('show_order',0),
	);
	protected $_link = array(
        'category' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'ExplainCategory',
            'foreign_key' => 'type_id',
        ),
    );
	/**
	 * [explain_cache 读取页面导航写入缓存]
	 * @return [array] [description]
	 */
	public function explain_cache(){
		$explain_list = $this->where(array('is_display'=>1))->order('show_order')->getfield('id,title,is_url');
		F('explain_list',$explain_list);
		return $explain_list;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('explain_list', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('explain_list', NULL);
    }
}	
?>