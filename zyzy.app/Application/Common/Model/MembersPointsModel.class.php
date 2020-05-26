<?php
namespace Common\Model;
use Think\Model;
class MembersPointsModel extends Model{
	protected $_validate = array(
		array('uid','identicalNull','',0,'callback'),
	);
	protected $_auto = array ( 
	);
	/*
		注册赠送积分
	*/
	public function add_members_points($uid,$points)
	{
		$userinfo = D('Members')->get_user_one(array('uid'=>$uid));
		$setarr_point['uid'] = $uid;
		$setarr_point['points']=$points;
		if(false === $this->create($setarr_point)){
			return array('state'=>false,'error'=>$this->getError());
		}else{
			if(false === $insert_id = $this->add()){
				return array('state'=>false,'error'=>'数据添加失败！');
			}
		}
		return array('state'=>true,'id'=>$insert_id);
	}
	
	/*
		操作积分函数
		@uid 会员uid
		@i_type 操作类型 1,2 
		@points 操作积分数
		返回值 flase or 影响行数

	*/
	public function report_deal($uid,$i_type=1,$points=0)
	{
		$userpoints = $this->get_user_points($uid);
		if($userpoints===false){
			$userpoints = 0;
			$this->add(array('uid'=>$uid,'points'=>0));
		}
		if ($i_type==1)
		{
			return $this->where('uid='.$uid)->setInc('points',$points);
		}
		if ($i_type==2)
		{
			if($userpoints>$points)
			{
				return $this->where('uid='.$uid)->setDec('points',$points);
			}
			else
			{
				return $this->where('uid='.$uid)->setField('points',0);
			}
		}
	}
	/*
		获取用户的积分
		@uid  用户uid
	*/
	public function get_user_points($uid){
		return $this->where('uid='.$uid)->getField('points');
	}
	/**
	 * 赠送积分
	 */
	public function gift_points($uid,$gift,$ptype,$points)
	{
		$operator=$ptype=="1"?"+":"-";
		$time=time();
		if (fieldRegex($uid,'in'))
		{
			$uid=explode(',',$uid);
		}
		if (!is_array($uid))$uid=array($uid);
		if (!empty($uid) && is_array($uid))
		{
			foreach($uid as $vuid)
			{
				$vuid=intval($vuid);
				if ($gift=='companyauth')
				{
					$com=M('MembersHandsel')->field('uid')->where(array('uid'=>array('eq',$vuid),'htype'=>$gift))->find();
					if(empty($com))
					{
						$this->report_deal($vuid,$ptype,$points);
						$user=D('Members')->get_user_one(array('uid'=>$vuid));
						$mypoints=$this->get_user_points($vuid);
						M('MembersHandsel')->add(array('uid'=>$vuid,'htype'=>$gift,'addtime'=>$time));
					}
				}
			}
		}
	}
}
?>