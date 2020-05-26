<?php 
namespace Common\Model;
use Think\Model;
class AuditReasonModel extends Model
	{
		protected $_validate = array(
			array('jobs_id,company_id,resume_id','identicalNull','',0,'callback'),
			array('jobs_id,company_id,resume_id','identicalENum','',0,'callback'),
		);
		protected $_auto = array (
			array('addtime','time',1,'function'),
		);
		public function del($id){
			if (!is_array($id)) $id=array($id);
			$sqlin=implode(",",$id);
			if (!fieldRegex($sqlin,'in')) return false;
			$return = $this->where(array('id'=>array('in',$sqlin)))->delete();
			if($return==0){
				return false;
			}
			return $return;
		}
	}
?>