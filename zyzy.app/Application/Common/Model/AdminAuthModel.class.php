<?php
namespace Common\Model;
use Think\Model;
class AdminAuthModel extends Model {
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('console_menu', NULL);
        F('menu_list',NULL);
        $obj_dir = new \Common\ORG\Dir;
        if(false === $role_list = F('admin_role_list')){
            $role_list = D('AdminRole')->role_cache();
        }
        foreach($role_list as $key=>$val){
            is_dir(DATA_PATH."/admin_menu/{$key}") && $obj_dir->del(DATA_PATH."/admin_menu/{$key}");
        }
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        $this->_before_write();
    }
}