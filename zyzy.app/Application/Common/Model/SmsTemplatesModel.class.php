<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 短信模板表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class SmsTemplatesModel extends Model{
	protected $_validate = array(
		//模板不能为空
		array('value','require','{%sms_template_null_error_value}',1),
	);
	protected $_auto = array (
		array('variate','_serialize',3,'callback'),
	    array('status','1'),  // 新增的时候把status字段设置为1
	    array('update_time','time',3,'function'), // 对create_time字段在更新的时候写入当前时间戳
	);
	protected function _serialize($data){
		return $data?serialize($data):'';
	}
	/**
	 * [sms_tpl_cache 短信模板缓存生成]
	 * @return [array]
	 */
	public function sms_tpl_cache($service=''){
		if(!$service) return false;
		if(false === $smsTplList = F('sms_tpl/tpl_'.$service)){
			$smsTplList = $this->where(array('type'=>$service))->getfield('alias,id,name,tpl_id,value');
		}
		F('sms_tpl/tpl_'.$service,$smsTplList);
		return $smsTplList;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('sms_tpl', NULL);
        if(false === $sms_list = F('sms_list')){
			$sms_list = D('Sms')->sms_cache();
		}
		foreach ($sms_list as $key => $val) {
			F('sms_tpl/tpl_'.$key, NULL);
		}
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
		$this->_before_write();
    }
}
?>