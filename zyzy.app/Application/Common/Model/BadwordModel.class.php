<?php
/**
 * 检测敏感词
 */
namespace Common\Model;
use Think\Model;
class BadwordModel extends Model{
    protected $_auto = array (
    		array('add_time','time',1,'function'),//自动添加时间
    );
    /**
     * 创建敏感词索引
     * 读取敏感词库中的全部词汇，取每条词汇的第一个字，进行索引。此方法可以减少对文章的循环检查次数
     */
    public function create_cache(){
    	$data = $this->field('badword,replace')->where(array('status'=>1))->select();
    	$badword=array();
    	foreach($data as $key=>$val){
    		$str = mb_substr($val['badword'],0,1,'utf-8');
    		$index = array_search($str,$badword['index']);
    		if($index === false || is_null($index)){//array_search函数在数组中搜索给定的值，如果成功则返回相应的键名，否则返回 FALSE
    			$badword['index'][] = $str;
    			end($badword['index']);
    			$index = key($badword['index']);
    		}
    		$badword[$index][] = $val;
    	}
    	F('badword', $badword);
    	return $badword;
    }
    /**
     * 检测敏感词
     * $type 0:合法  1:禁用  2:替换
     * 返回码code 0:合法/替换 1:禁用
     */
    public function check($content) {
    	if(is_null($content)) return '';
    	if (false === $badword = F('badword')) {
    		$badword = $this->create_cache();
    	}
    	foreach($badword['index'] as $key=>$val){
    		if($legal = strstr($content,$val)){
    			foreach($badword[$key] as $_val){
    				if($_legal = strstr($content,$_val['badword'])){
    					if(!$_val['replace']){
                            $count = mb_strlen($_val['badword'],'utf-8');
                            $_val['replace'] = str_repeat('*',$count);
                        }
                        $content = str_replace($_val['badword'], $_val['replace'], $content);
    				}
    			}
    		}
    	}
    	return $content;
    }
    /**
     * 是否存在
     */
    public function name_exists($name){
        if ($result = $this->field('id')->where(array('badword'=>$name))->find()) {
            return true;
        } else {
            return false;
        }
    }
}