<?php 
namespace Common\Model;
use Think\Model;
class CompanyImgModel extends Model
	{
		protected $_validate = array(
			array('uid,company_id,img','identicalNull','',0,'callback'),
			array('uid,company_id','identicalEnum','',0,'callback'),
			array('title','2,60','{%company_img_residence_length_error}',2,'length'),
		);
		protected $_auto = array (
			array('addtime','time',1,'function'),
			array('audit',0),
		);
		// 添加企业风采
		public function add_company_img($data,$user)
		{
			//$company_audit = M('CompanyProfile')->where(array('id'=>$data['company_id']))->getField('audit');
			$data['audit']=2;
			
			if(false === $this->create($data))
			{
				return array('state'=>0,'error'=>$this->getError());
			}
			else
			{
				if(false === $insert_id = $this->add())
				{
					return array('state'=>0,'error'=>'数据添加失败！');
				}
			}
			return array('state'=>1,'id'=>$insert_id,'date'=>date('Y-m-d'));
		}
		/**
		 * [edit_company_img 修改]
		 */
		public function edit_company_img($data,$user){
			if(false === $this->create($data)){
				return array('state'=>0,'error'=>$this->getError());
			}else{
				if(false === $this->where(array('id'=>$data['id'],'uid'=>$user['uid']))->save()){
					return array('state'=>0,'error'=>'数据保存失败！');
				}
			}
			return array('state'=>1);
		}
		/**
		 * 审核
		 */
		public function set_audit($id,$audit,$reason,$pms_notice){
			if (!is_array($id))$id=array($id);
			$sqlin=implode(",",$id);
			$return=0;
			if (fieldRegex($sqlin,'in'))
			{
				$return = $this->where(array('id'=>array('in',$sqlin)))->setField('audit',$audit);
				if($return===false){
					return false;
				}
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
						$message="您上传的图片，标题为：{$value['title']},".$note." 其他说明：".$reason;
						D('Pms')->write_pmsnotice($user_info['uid'],$user_info['username'],$message,1);
					}
				}
			}
			return $return;
		}
	}
?>