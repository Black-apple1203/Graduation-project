<?php
namespace Common\Model;
use Think\Model;
class MembersPointsRuleModel extends Model{
	protected $_validate = array(
		array('title,name,value','identicalNull','',0,'callback'),
		array('value','number','{%members_log_enum_error_value}',1),
		array('title,name','identicalLength_20','',0,'callback'),
	);
	protected $_auto = array ( 
		array('utype',1),//会员类型
		array('operation',1),//操作类型（增加减少）
	);

	/**
	 * 验证会员基本信息表字段合法性
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_100($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=100) return 'members_points_rule_length_error_'.$key;
		}
		return true;
	}
	/**
     * 读取系统参数生成缓存文件
     */
    public function config_cache() {
        $config = array();
        $res = $this->field('title,name,operation,value,utype')->select();
        foreach ($res as $key=>$val) {
        	$config[$val['name']] = array('title'=>$val['title'],'type'=>$val['operation'],'value'=>$val['value'],'utype'=>$val['utype']);
        }
        F('members_points_rule', $config);
        return $config;
    }
    // 读取积分规则配置
    public function get_cache($utype=null){
        if(false === $points = F('members_points_rule')){
            $points = $this->config_cache();
        }
        if($utype)
        {
            foreach ($points as $key => $value) {
                if($value['utype']==$utype)
                {
                   $points_[$key]=$value; 
                }
            }
            return $points_;
        }
        return $points;
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('members_points_rule', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('members_points_rule', NULL);
    }
}
?>