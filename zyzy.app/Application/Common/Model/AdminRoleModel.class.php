<?php
namespace Common\Model;
use Think\Model\RelationModel;
class AdminRoleModel extends RelationModel{
    protected $_link = array(
        'role_priv' => array(
            'mapping_type'  => self::MANY_TO_MANY,
            'class_name'    => 'Menu',
            'foreign_key'   => 'role_id',
            'relation_foreign_key'=>'menu_id',
            'relation_table' => "qs_admin_auth",
            'auto_prefix' => true
        )
    );
    protected $_validate = array(
        array('name','require','{%role_name_empty}'),
        array('name','','{%role_name_exists}',0,'unique',1)
    );
    protected $_auto = array (
        array('ordid',255),
    );
    /**
     * 读取角色表信息
     */
    public function role_cache(){
        $roleList = $this->where(array('status'=>1))->order('ordid')->getfield('id,name');
        F('admin_role_list',$roleList);
        return $roleList;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('admin_role_list',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('admin_role_list',NULL);
    }
}