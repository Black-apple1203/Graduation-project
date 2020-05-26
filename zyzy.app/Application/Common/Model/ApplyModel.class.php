<?php
namespace Common\Model;
use Think\Model;
class ApplyModel extends Model{
    /**
     * [apply_info_cache 应用列表配置信息缓存]
     */
	public function apply_info_cache(){
		$apply = $this->getfield('alias,module_name,version,is_create_table,is_insert_data,is_exe,is_delete_data,update_time,setup_time,explain,versioning');
		F('apply_info_list',$apply);
		return $apply;
	}
    /**
     * [apply_cache 当前安装应用模块列表缓存]
     */
    public function apply_cache(){
        $apply = $this->getfield('alias,module_name');
        F('apply_list',$apply);
        return $apply;
    }
    public function update_cache(){
        $this->_before_write();
    }
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('apply_info_list', NULL);
        F('apply_list', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('apply_info_list', NULL);
        F('apply_list', NULL);
    }
}
?>