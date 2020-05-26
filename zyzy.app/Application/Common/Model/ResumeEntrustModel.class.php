<?php
namespace Common\Model;
use Think\Model;
class ResumeEntrustModel extends Model{
	protected $_validate = array(
		array('resume_id,uid,fullname,entrust_start,entrust_end,resume_addtime','identicalNull','',1,'callback'),
		array('resume_id,uid,entrust,entrust_start,entrust_end,resume_addtime','identicalEnum','',1,'callback'),
	);
	protected $_auto = array (
		array('entrust',1),//是否自动投递
		array('isshield',0),//是否自动投递
	);
	/**
	 * [set_resume_entrust 简历自动投递]
	 * @param [intval] $resume_id  [简历ID]
	 * @param [intval] $uid        [用户ID]
	 * @param [array]  $options    [自动投递参数]
	 */
	public function set_resume_entrust($pid,$uid,$options=array()){
		if(!$pid) return '简历ID不能为空！';
		if(!$uid) return '用户ID不能为空！';
		$data = array('entrust'=>3,'entrust_start'=>time(),'entrust_end'=>time()+3600*24*3,'isshield'=>0);
		$options = array_merge($data,$options);
		$resume = M('Resume')->field('id,uid,audit,fullname,addtime')->where(array('id'=>$pid,'uid'=>$uid))->find();
		if((C('qscms_resume_display') == 1 && $resume["audit"]=='1') || C('qscms_resume_display') == 2 && ($resume["audit"] == 1 || $resume["audit"] == 2)){
			//查看这份简历是否是委托过
			if(!$this->where(array('resume_id'=>$pid,'uid'=>$resume['uid']))->getField('id')){
				$options['resume_id'] = $resume['id'];
				$options['uid'] = $resume['uid'];
				$options['fullname'] = $resume['fullname'];
				$options['resume_addtime'] = $resume['addtime'];
				if(false !== $this->create($options)){
					if(false !== $this->add()) return true;
					return L('resume_entrust_failure');
				}
				return $this->getError();
			}
		}else{
			$this->where(array('resume_id'=>$pid,'uid'=>$uid))->delete();
            return '您的简历尚未通过审核，不能委托投递！';
		}
		return true;
	}
}
?>