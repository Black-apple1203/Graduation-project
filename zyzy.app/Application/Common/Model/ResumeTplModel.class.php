<?php 
namespace Common\Model;
use Think\Model;
class ResumeTplModel extends Model{
		protected $_validate = array(
			array('uid,tplid','identicalNull','',0,'callback'),
			array('uid,tplid','identicalEnum','',0,'callback'),
		);
		public function add_resume_tpl($data){
			if($this->check_tpl($data)){
				return array('state'=>0,'error'=>'您已购买过此模板');
			}
			if(false === $this->create($data)) return array('state'=>0,'error'=>$this->getError());
			if(false === $insert_id = $this->add()) return array('state'=>0,'error'=>'添加数据失败！');
			return array('state'=>1,'id'=>$insert_id);
		}
		public function check_tpl($data){
			return $this->where(array('uid'=>$data['uid'],'tplid'=>$data['tplid']))->find();
		}
	}
?>