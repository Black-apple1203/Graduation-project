<?php
namespace Common\Model;
use Think\Model;
class MembersSetmealModel extends Model{
	protected $_validate = array(
		array('uid,setmeal_id,setmeal_name','identicalNull','',0,'callback'),
		array('uid,setmeal_id,jobs_meanwhile,refresh_jobs_free,download_resume,download_resume_max,endtime','identicalEnum','',0,'callback'),
		array('setmeal_name','identicalLength_200','',0,'callback'),
	);
	protected $_auto = array ( 
		array('expire',0),//是否是过期的
		array('change_templates',0),//换模板
		array('map_open',0),//开通地图
		array('refresh_jobs_space',0),//刷新职位间隔
		array('refresh_jobs_time',0),//刷新职位次数
		array('show_apply_contact',0),//主动申请的简历是否可以直接查看联系方式
        array('show_contact_direct',0),//直接显示联系方式
	);

	/**
	 * 验证会员套餐表字段合法性
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_200($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=200) return 'members_setmeal_length_error_'.$key;
		}
		return true;
	}
	/*
		注册赠送套餐
	*/
	public function add_members_setmeal($uid,$setmeal_id)
	{
		$userinfo = D('Members')->get_user_one(array('uid'=>$uid));
		$setsqlarr['uid']=$uid;
		if($setmeal_id>0){
			$setmeal = D('Setmeal')->get_setmeal_one($setmeal_id);
			$setsqlarr['expire']=0;
			$setsqlarr['setmeal_id']=$setmeal['id'];
			$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
			$setsqlarr['starttime']=time();
			$setsqlarr['days']=$setmeal['days'];
			if ($setmeal['days']>0)
			{
			$setsqlarr['endtime']=strtotime("".$setmeal['days']." days");
			}
			else
			{
			$setsqlarr['endtime']="0";	
			}
			$setsqlarr['expense']=$setmeal['expense'];
			$setsqlarr['jobs_meanwhile']=$setmeal['jobs_meanwhile'];
			$setsqlarr['refresh_jobs_free']=$setmeal['refresh_jobs_free'];
			$setsqlarr['download_resume']=$setmeal['download_resume'];
			$setsqlarr['download_resume_max']=$setmeal['download_resume_max'];
			$setsqlarr['added']=$setmeal['added'];
			$setsqlarr['setmeal_img']=$setmeal['setmeal_img'];
			$setsqlarr['show_apply_contact']=$setmeal['show_apply_contact'];
			$setsqlarr['show_contact_direct']=$setmeal['show_contact_direct'];
			$setsqlarr['is_free']=$setmeal['is_free'];
			$setsqlarr['discount_download_resume']=$setmeal['discount_download_resume'];
			$setsqlarr['discount_sms']=$setmeal['discount_sms'];
			$setsqlarr['discount_stick']=$setmeal['discount_stick'];
			$setsqlarr['discount_emergency']=$setmeal['discount_emergency'];
			$setsqlarr['discount_tpl']=$setmeal['discount_tpl'];
			$setsqlarr['discount_auto_refresh_jobs']=$setmeal['discount_auto_refresh_jobs'];
			$setsqlarr['allow_look']=$setmeal['allow_look'];
			$setsqlarr['enable_video']=$setmeal['enable_video'];
		}
		
		if(false === $this->create($setsqlarr))
		{
			return array('state'=>false,'error'=>$this->getError());
		}
		else
		{
			if(false === $insert_id = $this->add())
			{
				return array('state'=>false,'error'=>'数据添加失败！');
			}else{
				//如果套餐有赠送积分，则直接更新用户积分表
				if($setmeal['set_points']){
					D('MembersPoints')->report_deal($uid,1,$setmeal['set_points']);
					// 写入会员积分操作日志
					$handsel['uid'] = $uid;
					$handsel['htype'] = 'setmeal_gifts';
					$handsel['htype_cn'] = '套餐内赠送';
					$handsel['operate'] = 1;
					$handsel['points'] = $setmeal['set_points'];
					$handsel['addtime'] = time();
					D('MembersHandsel')->members_handsel_add($handsel);
				}
				//如果套餐有赠送短信，则直接更新用户表的短信条数字段
				if($setmeal['set_sms']){
					D('Members')->where(array('uid'=>$uid))->setInc('sms_num',$setmeal['set_sms']);
				}
				$setmeal['id'] && M('CompanyProfile')->where(array('uid'=>$uid))->save(array('setmeal_id'=>$setmeal['id'],'setmeal_name'=>$setmeal['setmeal_name']));
			}
		}
		if($setmeal_id>0){
			// 套餐变更记录
			write_members_log($userinfo,'setmeal','注册会员系统自动赠送：'.$setmeal['setmeal_name']);
		}
		return array('state'=>true,'id'=>$insert_id);
	}
	/*
		获取用户的套餐
		@uid  用户uid
	*/
	public function get_user_setmeal($uid)
	{
		return $this->where(array('uid'=>$uid))->find();
	}
	/*
		重新开通套餐
		@$uid 会员UID
		@$id  套餐ID 

	*/
	public function set_members_setmeal($uid,$id,$money_item=0)
	{
		$setmeal=M('Setmeal')->where(array('id'=>$id,'display'=>1))->find();
		if(!$setmeal) return array('state'=>0,'error'=>'请选择正确的套餐！');
		$user_setmeal = $this->get_user_setmeal(intval($uid));
		$timestamp = time();
		$setsqlarr['expire']=$setmeal['is_free']==1?1:0;//如果$setmeal['is_free']==1，说明是到期自动变为免费会员，标记过期
		$setsqlarr['setmeal_id']=$setmeal['id'];
		$setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
		$setsqlarr['days']=$setmeal['days'];
		$setsqlarr['starttime']=$timestamp;
		if ($setmeal['days']>0)
		{
			//如果套餐未到期，判断是否叠加套餐服务时间
			if($user_setmeal['endtime']>$timestamp && C('qscms_is_superposition_time') == 1){
				$setsqlarr['endtime'] = $user_setmeal['endtime'] + $setmeal['days']*3600*24;
			}else{
				$setsqlarr['endtime']=strtotime("".$setmeal['days']." days");
			}
		}
		else
		{
			$setsqlarr['endtime']="0";	
		}
		$setsqlarr['expense']=$setmeal['expense'];
		$setsqlarr['jobs_meanwhile']=$setmeal['jobs_meanwhile'];
		$setsqlarr['refresh_jobs_free']=$setmeal['refresh_jobs_free'];
		$setsqlarr['download_resume']=$setmeal['download_resume'];
		$setsqlarr['download_resume']=C('qscms_is_superposition')==1?($user_setmeal['download_resume']+$setmeal['download_resume']):$setmeal['download_resume'];
		$setsqlarr['download_resume_max']=$setmeal['download_resume_max'];
		$setsqlarr['added']=$setmeal['added'];
		$setsqlarr['set_sms']=$user_setmeal['set_sms'] + $setmeal['set_sms'];
		$setsqlarr['setmeal_img']=$setmeal['setmeal_img'];
		$setsqlarr['show_apply_contact']=$setmeal['show_apply_contact'];
		$setsqlarr['show_contact_direct']=$setmeal['show_contact_direct'];
		$setsqlarr['is_free']=$setmeal['is_free']; 
		$setsqlarr['money']=$money_item; //-------chm修改后
		$setsqlarr['discount_download_resume']=$setmeal['discount_download_resume'];
		$setsqlarr['discount_sms']=$setmeal['discount_sms'];
		$setsqlarr['discount_stick']=$setmeal['discount_stick'];
		$setsqlarr['discount_emergency']=$setmeal['discount_emergency'];
		$setsqlarr['discount_tpl']=$setmeal['discount_tpl'];
		$setsqlarr['discount_auto_refresh_jobs']=$setmeal['discount_auto_refresh_jobs'];
		$setsqlarr['allow_look']=$setmeal['allow_look'];
		$setsqlarr['enable_video']=$setmeal['enable_video'];
		$setmeal_jobs['setmeal_deadline']=$setsqlarr['endtime'];
		$setmeal_jobs['deadline']=$setmeal_jobs['setmeal_deadline'];
		$setmeal_jobs['setmeal_id']=$setsqlarr['setmeal_id'];
		$setmeal_jobs['setmeal_name']=$setsqlarr['setmeal_name'];
			
		// 插入数据
		if(false === $this->create($setsqlarr))
		{
			return array('state'=>false,'error'=>$this->getError());
		}
		else
		{
			if(false === $this->where(array('uid'=>$uid))->save())
			{
				return array('state'=>false,'error'=>'设置套餐失败！');
			}else{
				//如果套餐有赠送积分，则直接更新用户积分表
				if($setmeal['set_points']){
					D('MembersPoints')->report_deal($uid,1,$setmeal['set_points']);
					// 写入会员积分操作日志
					$handsel['uid'] = $uid;
					$handsel['htype'] = 'setmeal_gifts';
					$handsel['htype_cn'] = '套餐内赠送';
					$handsel['operate'] = 1;
					$handsel['points'] = $setmeal['set_points'];
					$handsel['addtime'] = time();
					D('MembersHandsel')->members_handsel_add($handsel);
				}
				//如果套餐有赠送短信，则直接更新用户表的短信条数字段
				if($setmeal['set_sms']){
					D('Members')->where(array('uid'=>$uid))->setInc('sms_num',$setmeal['set_sms']);
				}
				M('CompanyProfile')->where(array('uid'=>$uid))->save(array('setmeal_id'=>$setsqlarr['setmeal_id'],'setmeal_name'=>$setsqlarr['setmeal_name']));
				D('Jobs')->jobs_setfield(array('uid'=>$uid),$setmeal_jobs);
				//检查在招职位数是否超出限额，并根据后台配置做关闭处理
				$this->check_jobs_meanwhile($uid,$setmeal['jobs_meanwhile']);
				return array('state'=>1);
			}
		}
	}
	/*
		$uid 会员uid
		$actio 套餐项
		$type 套餐项加减 (主要针对 发布职位默认为1是加,2为减)
	*/
	public function action_user_setmeal($uid,$action,$type=1,$num=1)
	{
		if ($type==1)
		{
			return $this->where('uid='.$uid)->setInc($action,$num);
		}
		if ($type==2)
		{
			$usersetmeal = $this->get_user_setmeal($uid);
			if($usersetmeal[$action] > 0)
			{
				return $this->where('uid='.$uid)->setDec($action,$num);
			}
			else
			{
				return $this->where('uid='.$uid)->setField($action,0);
			}
		}
	}
	public function edit_setmeal_notes($setarr,$setmeal){
		$diff_arr= array_diff_assoc($setarr,$setmeal);
		if($diff_arr){
			foreach($diff_arr as $key=>$value){
				if($key=='jobs_meanwhile'){
					$str.="发布职位：{$setarr['jobs_meanwhile']}(原{$setmeal['jobs_meanwhile']}),";
				}elseif($key=='refresh_jobs_free'){
					$str.="每天免费刷新职位：{$setarr['refresh_jobs_free']}(原{$setmeal['refresh_jobs_free']}),";
				}elseif($key=='download_resume'){
					$str.="下载简历点数：{$setarr['download_resume']}(原{$setmeal['download_resume']}),";
				}elseif($key=='download_resume_max'){
					$str.="每天下载简历数：{$setarr['download_resume_max']}(原{$setmeal['download_resume_max']}),";
				}elseif($key=='endtime'){
					if($setarr['endtime']=='1970-01-01') $setarr['endtime']='无限期';
					$str.="修改套餐到期时间：{$setarr['endtime']}(原{$setmeal['endtime']}),";
				}elseif($key=='money' && $value){
					$str.="收取套餐金额：{$setarr['money']} 元,";
				}
			}
			$strend=$str?$str:'';
			return $strend;
		}else{
			return '';
		}
	}
	/**
	 * 检查在招职位数是否超出限额，并根据后台配置做关闭处理
	 */
	public function check_jobs_meanwhile($uid,$num){
		if(C('qscms_hold_beyond_jobs')==0){
			$yid = array();
			$jobs = D('Jobs')->where(array('uid'=>$uid))->order('refreshtime desc')->select();
			if(count($jobs)>$num){
				foreach ($jobs as $key => $value) {
					if($key>=$num){
						$yid[] = $value['id'];
					}
				}
			}
			if(!empty($yid)){
				$data['user'] = D('Members')->get_user_one(array('uid'=>$uid));
				$data['yid'] = $yid;
				D('Jobs')->jobs_close($data);
			}
		}
	}
}
?>