<?php
namespace Common\Model;
use Think\Model;
class PersonalServiceTagLogModel extends Model{
	protected $_validate = array(
		array('resume_id,resume_uid,days,tag_id','identicalNull','',0,'callback'),
		array('resume_id,resume_uid,days,tag_id','identicalEnum','',0,'callback'),
	);
	protected $_auto = array ( 
		array('addtime','time',1,'function'),
	);
	public function add_tag_log($data){
		if($this->check_tag_log($data)){
			return array('state'=>0,'error'=>'您已购买过此服务');
		}
		if(false === $this->create($data)) return array('state'=>0,'error'=>$this->getError());
		if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'添加数据失败！');
		return array('state'=>1,'id'=>$insert_id);
	}
	public function check_tag_log($data){
		return $this->where(array('resume_id'=>$data['resume_id']))->find();
	}

	public function cancel_tag($resume_id){
		D('Resume')->where(array('id'=>$resume_id))->setField('strong_tag',0);
		return true;
	}
	/**
	 * [del_promotion_tag 取消标签推广]
	 */
	public function del_promotion_tag($ids){
		if (!is_array($ids)) $ids=array($ids);
		$sqlin=implode(",",$ids);
		$return = false;
		if (fieldRegex($sqlin,'in')){
			$result = $this->where(array('id'=>array('in',$sqlin)))->getfield('id,resume_id');
			foreach ($result as $key => $val) {
				$this->cancel_tag($val);
				$proid[] = $key;
			}
			if ($proid){
				$return = $this->where(array('id'=>array('in',$proid)))->delete();
			}
		}
		return $return;
	}
}
?>