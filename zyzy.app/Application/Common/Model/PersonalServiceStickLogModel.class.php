<?php
namespace Common\Model;
use Think\Model;
class PersonalServiceStickLogModel extends Model{
	protected $_validate = array(
		array('resume_id,resume_uid,days','identicalNull','',0,'callback'),
		array('resume_id,resume_uid,days','identicalEnum','',0,'callback'),
	);
	protected $_auto = array ( 
		array('addtime','time',1,'function'),
	);
	public function add_stick_log($data){
		if($this->check_stick_log($data)){
			return array('state'=>0,'error'=>'该简历已置顶');
		}
		if(false === $this->create($data)) return array('state'=>0,'error'=>$this->getError());
		if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'添加数据失败！');
		return array('state'=>1,'id'=>$insert_id);
	}
	public function check_stick_log($data){
		return $this->where(array('resume_id'=>$data['resume_id']))->find();
	}
	public function cancel_stick($resume_id){
		$refreshtime = D('Resume')->where(array('id'=>$resume_id))->getfield('refreshtime');
		D('Resume')->where(array('id'=>$resume_id))->save(array('stick'=>0,'stime'=>$refreshtime));
        D('ResumeSearchPrecise')->where(array('id'=>$resume_id))->setField('stime',$refreshtime);
        D('ResumeSearchFull')->where(array('id'=>$resume_id))->setField('stime',$refreshtime);
		return true;
	}
	/**
	 * [del_promotion_stick 取消置顶推广]
	 */
	public function del_promotion_stick($ids){
		if (!is_array($ids)) $ids=array($ids);
		$sqlin=implode(",",$ids);
		$return = false;
		if (fieldRegex($sqlin,'in')){
			$result = $this->where(array('id'=>array('in',$sqlin)))->getfield('id,resume_id');
			foreach ($result as $key => $val) {
				$this->cancel_stick($val);
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