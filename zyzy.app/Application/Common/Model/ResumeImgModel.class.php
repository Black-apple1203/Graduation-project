<?php
namespace Common\Model;
use Think\Model;
class ResumeImgModel extends Model{
	protected $_validate = array(
		array('uid,resume_id,img','identicalNull','',1,'callback'),
		array('uid,resume_id','identicalEnum','',1,'callback'),
		array('title','0,40','{%resume_img_title_length_error}',0,'length'),
		array('img','1,255','{%resume_img_img_length_error}',0,'length'),
	);
	protected $_auto = array (
		array('addtime','time',1,'function'),//附件上传时间
		array('audit',2,3),
	);
	/**
	 * [save_resume_img 简历添加附件]
	 */
	public function save_resume_img($data,$user){
		if(false === $this->create($data)) return array('state'=>0,'error'=>$this->getError());
		if($data['id']){
			if(false === $this->where(array('id'=>$data['id'],'uid'=>$data['uid'],'resume_id'=>$data['resume_id']))->save()) return array('state'=>0,'error'=>'附件修改失败，请重新操作！');
			$id = $data['id'];
		}else{
			if(false === $id = $this->add()) return array('state'=>0,'error'=>'附件添加失败，请重新操作！');
		}
		//写入会员日志
		write_members_log($user,'resume','添加简历附件（简历id：'.$data['resume_id'].'）',false,array('resume_id'=>$data['resume_id']));
		return array('state'=>1,'id'=>$id);
	}
	public function get_resume_img($where)
	{
		$list = $this->where($where)->select();
		foreach ($list as $key => $value) {
			$list[$key]['img'] = attach($value['img'],'resume_img');
		}
		return $list;
	}
	/**
	 * [set_audit 简历照片审核]
	 */
	public function set_audit($id,$audit,$reason,$pms_notice){
		if (!is_array($id))$id=array($id);
		$sqlin=implode(',',$id);
		$return=0;
		if (fieldRegex($sqlin,'in')){
			$return = $this->where(array('id'=>array('in',$sqlin)))->setField('audit',$audit);
			if($return===false) return false;
			//发送站内信
			if($audit=='1') {
				$note='成功通过网站管理员审核!';
			}elseif($audit=='2'){
				$note='正在审核中!';
			}elseif($audit=='3'){
				$note='未通过网站管理员审核！';
			}
			$reason = $reason==''?'无':$reason;
			if ($pms_notice){
				$result = $this->where(array('id'=>array('in',$sqlin)))->field('uid,title,img')->select();
				foreach ($result as $key => $value) {
					$value['title']=$value['title']==''?$value['img']:$value['title'];
					$user_info=D('Members')->get_user_one(array('uid'=>$value['uid']));
					$message="您上传的图片，标题为：{$value['title']}，".$note." 其他说明：".$reason;
					D('Pms')->write_pmsnotice($user_info['uid'],$user_info['username'],$message,2);
				}
			}
		}
		return $return;
	}
}
?>