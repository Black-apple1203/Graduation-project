<?php

namespace Common\Model;

use Think\Model;

class VideoInterviewModel extends Model
{
	/**
	 * 检测code合法性
	 */
	public function checkCodeLegal($code)
	{
		if (mb_strlen($code) != 6 || !ctype_alnum($code)) {
			return false;
		}
		return true;
	}
	/**
	 * 查找面试信息
	 */
	public function getInterviewByCode($code)
	{
		$info = $this->where(array('company_code' => array('eq', $code)))->find();
		$utype = 1;
		if (!$info) {
			$info = $this->where(array('personal_code' => array('eq', $code)))->find();
			$utype = 2;
		}
		if (!$info) {
			return false;
		}
		return array('info' => $info, 'utype' => $utype);
	}
	/**
	 * 发起面试邀请
	 */
	public function add_interview($addarr, $user)
	{
		if (C('qscms_video_interview_open') != 1) {
			return array('state' => 0, 'msg' => '视频面试未开启');
		}
		if ($user['utype'] != 1) return array('state' => 0, 'msg' => '必须是企业会员才可以邀请面试');
		if ($user['status'] == 2) return array('state' => 0, 'msg' => '您的账号处于暂停状态，请联系管理员设为正常后进行操作');
		if (!$addarr['resume_id']) return array('state' => 0, 'msg' => '请选择简历');
		$setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);
		if ($setmeal['enable_video'] != 1) {
			return array('state' => 0, 'msg' => '当前套餐等级不能使用视频面试，请先升级套餐');
		}
		$user_jobs = D('Jobs')->count_auditjobs_num($user['uid']);
		if ($user_jobs == 0) return array('state' => 0, 'msg' => '邀请失败，你没有发布招聘信息或者信息没有审核通过');

		$resume = D('Resume')->get_resume_one($addarr['resume_id']);
		$check_unique_map['company_uid'] = $user['uid'];
		$check_unique_map['personal_uid'] = $resume['uid'];
		$check_unique_map['jobs_id'] = $addarr['jobs_id'];
		$check_unique_map['deadline'] = array('gt', time());
		$check_unique = $this->where($check_unique_map)->find();
		if ($check_unique) return array('state' => 0, 'msg' => '您已对该简历进行过面试邀请,不能重复邀请');
		$pass = false;
		//检测是否申请过职位
		$has = D('PersonalJobsApply')->check_jobs_apply($addarr['resume_id'], $user['uid']);
		$has && $pass = true;
		if ($setmeal['show_apply_contact'] == 0 && $pass == true) $pass = false;
		//检测是否下载过简历
		if ($pass == false && $user['uid']) {
			$info = D('CompanyDownResume')->check_down_resume($addarr['resume_id'], $user['uid']);
			$info && $pass = true;
		}
		if (false == $pass) return array('state' => 0, 'msg' => '请先下载简历！');
		$job_info = D('Jobs')->get_auditjobs_one(array('id' => $addarr['jobs_id']));


		$setsqlarr['company_uid'] = $user['uid'];
		$setsqlarr['personal_uid'] = $resume['uid'];
		$setsqlarr['jobs_id'] = $addarr['jobs_id'];
		$setsqlarr['jobs_name'] = $job_info['jobs_name'];
		$setsqlarr['interview_time'] = $addarr['interview_time'];
		$setsqlarr['deadline'] = $setsqlarr['interview_time'] + 3600 * 24 * 15; //过期时间设置为面试时间之后的第15天
		$setsqlarr['contact'] = $addarr['contact'];
		$setsqlarr['contact_tel'] = $addarr['telephone'];
		$setsqlarr['addtime'] = time();
		$setsqlarr['company_code'] = unique_str('company_uid=' . $setsqlarr['company_uid'] . '&addtime=' . $setsqlarr['addtime']);
		$setsqlarr['personal_code'] = unique_str('personal_uid=' . $setsqlarr['personal_uid'] . '&addtime=' . $setsqlarr['addtime']);

		if (false === $this->create($setsqlarr)) return array('state' => 0, 'msg' => $this->getError());
		if (false === $insertid = $this->add()) return array('state' => 0, 'msg' => '邀请面试失败');
		M('PersonalJobsApply')->where(array('resume_id' => $addarr['resume_id'], 'jobs_id' => $addarr['jobs_id'], 'company_uid' => $user['uid']))->save(array('is_reply' => 1, 'personal_look' => 2));
		//写入会员日志
		write_members_log($user, '', '邀请面试（职位id：' . $addarr['jobs_id'] . '；简历id：' . $addarr['resume_id'] . '）');
		//提醒
		$interview = D('VideoInterview')->where(array('id' => $insertid))->find();
		$this->notice_personal($interview);
		$this->notice_company($interview);
		return array('state' => 1, 'msg' => '邀请面试成功'); //增加面试邀请

	}
	/**
	 * 面试邀请提醒个人
	 */
	public function notice_personal($interview)
	{
		$sms = D('SmsConfig')->get_cache();
		if ($sms['set_video_invite'] == 1) {
			$send_sms = true;
			if (C('qscms_company_sms') == 1) {
				$user_sms_num = D('Members')->where(array('uid' => $interview['company_uid']))->getField('sms_num');
				if ($user_sms_num == 0) {
					$send_sms = false;
				}
			}

			if ($send_sms == true) {
				$usermobile = D('Members')->get_user_one(array('uid' => $interview['personal_uid']));
				$r = D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_video_invite', 'data' => array()));
				if ($r === true) {
					if (C('qscms_company_sms') == 1) {
						D('Members')->where(array('uid' => $interview['company_uid']))->setDec('sms_num');
					}
				}
			}
		}
		//微信
		if (false === $module_list = F('apply_info_list')) $module_list = D('Apply')->apply_info_cache();
		if ($module_list['Weixin'] && $module_list['Weixin']['version'] == '6.0.1') {
			D('Weixin/TplMsg')->set_video_invite($interview, $interview['personal_uid']);
		}
	}
	/**
	 * 面试邀请提醒企业
	 */
	public function notice_company($interview)
	{
		$sms = D('SmsConfig')->get_cache();
		if ($sms['set_video_notice_com'] == 1) {
			$usermobile = D('Members')->get_user_one(array('uid' => $interview['company_uid']));
			D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_video_notice_com', 'data' => array()));
		}
		//微信
		if (false === $module_list = F('apply_info_list')) $module_list = D('Apply')->apply_info_cache();
		if ($module_list['Weixin'] && $module_list['Weixin']['version'] == '6.0.1') {
			D('Weixin/TplMsg')->set_video_notice_com($interview, $interview['company_uid']);
		}
	}
}
