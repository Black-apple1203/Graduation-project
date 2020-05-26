<?php 
namespace Common\Model;
use Think\Model;
class HotwordModel extends Model
{
	protected $_validate = array(
		array('w_word','identicalNull','',1,'callback'),
	);
	protected $_auto = array (
		array('w_hot',1),
	);
	public function get_hotword($word){
		$hotword = $this->where(array('w_word'=>array('like','%'.$word.'%')))->order('w_hot desc')->limit(10)->select();
		$this->set_inc($word);
		return $hotword ? array('key'=>$word,'list'=>$hotword) : false;
	}
	/**
	 * 搜索次数增加1,如果不存在则增加一条数据
	 */
	public function set_inc($word){
		$word = trim($word);
		$word = substr($word,0,120);
		$word_info = $this->where(array('w_word'=>array('eq',$word)))->find();
		if($word_info){
			$this->where(array('w_id'=>$word_info['w_id']))->setInc('w_hot');
		}else{
			$this->add(array('w_word'=>$word));
		}
		return true;
	}
	public function set_inc_batch($word){
		!is_array($word) && $word = array($word);
		foreach ($word as $key => $value) {
			$value = ltrim($value,'0');
			$value = rtrim($value,'0');
			$this->set_inc($value);
		}
		return true;
	}
}	
?>