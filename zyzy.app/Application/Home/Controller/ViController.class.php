<?php

namespace Home\Controller;

use Common\Controller\FrontendController;

class ViController extends FrontendController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	/**
	 * 专题页
	 */
	public function index()
	{
		if (C('qscms_video_interview_open') == 0) {
			$this->error('视频面试功能未开启', U('index/index'));
		}
		$this->assign('page_seo', array('title' => '视频面试专区 - ' . C('qscms_site_name')));
		$this->display();
	}
	public function ajax_joblist()
	{
		$where = array();
		$where['display'] = 1;
		if (C('subsite_info') && C('subsite_info.s_id') != 0) {
			$where['subsite_id'] = C('subsite_info.s_id');
		}
		$company_uids = D('MembersSetmeal')->where(array('enable_video' => 1))->getField('uid', true);
		if (!$company_uids) {
			$this->ajaxReturn(1, '获取数据成功', array('items' => array(), 'total_page' => 1));
		}
		$where['uid'] = array('in', $company_uids);
		$keyword = I('get.keyword', '', 'trim');
		if ($keyword != '') {
			$where['jobs_name'] = array('like', '%' . $keyword . '%');
		}
		$page = I('get.page', 1, 'intval');
		if (C('qscms_jobs_display') == 1) {
			$where['audit'] = 1;
		} else {
			$where['audit'] = array('neq', 3);
		}
		$pagesize = I('get.pagesize', 20, 'intval');
		$total = D('Jobs')->where($where)->count();
		if ($total > 300) {
			$total = 300;
		}
		$offset = ($page - 1) * $pagesize;
		$list = D('Jobs')->field('id,jobs_name,company_id,companyname,negotiable,minwage,maxwage,education_cn,experience_cn')->where($where)->order('refreshtime desc')->limit($offset . ',' . $pagesize)->select();
		foreach ($list as $key => $value) {
			$value = $this->handle_wage($value);
			$value['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $value['id']));
			$value['company_url'] = url_rewrite('QS_companyshow', array('id' => $value['company_id']));
			$list[$key] = $value;
		}
		$total_page = ceil($total / $pagesize);
		$this->ajaxReturn(1, '获取数据成功', array('items' => $list, 'total_page' => $total_page));
	}
	/**
	 * 滚动动态
	 */
	public function ajax_scroll()
	{
		$interview_arr = M('VideoInterview')->order('addtime DESC')->limit(5)->getField("id,company_uid,personal_uid,jobs_id,jobs_name,interview_time,addtime as time");
		$company_arr = $company_uid_arr = array();
		$job_arr = $job_id_arr = array();
		$resume_arr = $resume_uid_arr = array();
		foreach ($interview_arr as $key => $value) {
			$company_uid_arr[] = $value['company_uid'];
			$job_id_arr[] = $value['jobs_id'];
			$resume_uid_arr[] = $value['personal_uid'];
		}
		if (!empty($company_uid_arr)) {
			$company_arr = D('CompanyProfile')->where(array('uid' => array('in', $company_uid_arr)))->getField('uid,id,companyname', true);
		}
		if (!empty($job_id_arr)) {
			$job_arr = D('Jobs')->where(array('id' => array('in', $job_id_arr)))->getField('id,minwage,maxwage,negotiable', true);
		}
		if (!empty($resume_uid_arr)) {
			$resume_arr = D('Resume')->where(array('def' => 1, 'uid' => array('in', $resume_uid_arr)))->getField('uid,sex,fullname', true);
		}
		foreach ($interview_arr as $val) {
			$cominfo = $company_arr[$val['company_uid']];
			$val['company_url'] = url_rewrite('QS_companyshow', array('id' => $cominfo['id']));
			$val['companyname'] = $cominfo['companyname'];
			$val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
			$resumeinfo = $resume_arr[$val['personal_uid']];
			$val['fullname'] = $resumeinfo['fullname'];
			if ($resumeinfo['display_name'] == "2") {
				$val['fullname'] = "N" . str_pad($resumeinfo['id'], 7, "0", STR_PAD_LEFT);
			} elseif ($resumeinfo['display_name'] == "3") {
				if ($resumeinfo['sex'] == 1) {
					$val['fullname'] = cut_str($resumeinfo['fullname'], 1, 0, "先生");
				} elseif ($resumeinfo['sex'] == 2) {
					$val['fullname'] = cut_str($resumeinfo['fullname'], 1, 0, "女士");
				}
			}
			$jobinfo = $job_arr[$val['jobs_id']];
			$val['minwage'] = $jobinfo['minwage'];
			$val['maxwage'] = $jobinfo['maxwage'];
			$val['interview_time'] = date('m月d日');
			$val = $this->handle_wage($val);
			$val['type'] = 'interview';
			$interview[] = $val;
		}

		$map_company_uids = D('MembersSetmeal')->where(array('enable_video' => 1))->getField('uid', true);
		if (!$map_company_uids) {
			$job = null;
		} else {
			$where['display'] = 1;
			if (C('qscms_jobs_display') == 1) {
				$where['audit'] = 1;
			} else {
				$where['audit'] = array('neq', 3);
			}
			//fenzhan
			if (C('subsite_info') && C('subsite_info.s_id') != 0) {
				$where['subsite_id'] = C('subsite_info.s_id');
			}
			$where['uid'] = array('in', $map_company_uids);
			$add_job = M('Jobs')->where($where)->order('addtime DESC')->limit(5)->getField("id,jobs_name,companyname,company_id,addtime as time");
			if ($add_job) {
				$where['id'] = array('not in', array_keys($add_job));
				$refresh_job = M('Jobs')->where($where)->order('refreshtime DESC')->limit(5)->getField("id,jobs_name,companyname,company_id,refreshtime as time");
				if ($refresh_job) {
					$job_arr = array_merge($add_job, $refresh_job);
				} else {
					$job_arr = $add_job;
				}
				foreach ($job_arr as $val) {
					$val = $this->handle_wage($val);
					$val['companyname'] = cut_str($val['companyname'], 6, 0, '...');
					$val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['id']));
					$val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
					$val['type'] = 'addjob';
					$job[] = $val;
				}
			}
		}

		if ($interview != NULl && $job != NUll) {
			$tmp = array_merge($interview, $job);
		} elseif ($interview != NULl && $job == NUll) {
			$tmp = $interview;
		} elseif ($interview == NULl && $job != NUll) {
			$tmp = $job;
		} else {
			$this->ajaxReturn(0, '暂无数据！');
		}
		//$time = array_column($tmp,'time');
		foreach ($tmp as $item) {
			$time[] = $item['time'];
		}
		rsort($time);
		foreach ($time as $val) {
			foreach ($tmp as $k => $v) {
				if ($val == $v['time']) {
					$data[] = $v;
					unset($tmp[$k]);
				}
			}
		}
		count($data) == 0 && $this->ajaxReturn(0, '暂无数据！');
		$this->ajaxReturn(1, '获取动态成功！', $data);
	}
	/**
	 * 设备检测页面
	 */
	public function test()
	{
		$code = I('get.code', '', 'trim');
		if (C('PLATFORM') == 'mobile') {
			redirect(U('Mobile/Vi/main', array('code' => $code)));
			die;
		}
		if (C('qscms_video_interview_open') == 0) {
			$this->error('视频面试功能未开启', U('index/index'));
		}
		$main_url = U('vi/main', array('code' => $code));
		$test_url = U('vi/test', array('code' => $code));
		$userid = "test_user_" . substr(md5(time()), 0, 8) . rand(10000, 99999);
		$tencent = new \Common\ORG\TLSSigAPIv2(C('qscms_trtc_appid'), C('qscms_trtc_appsecret'));
		$sig = $tencent->genSig($userid);
		$this->assign('userid', $userid);
		$this->assign('sig', $sig);
		$this->assign('code', $code);
		$this->assign('main_url', $main_url);
		$this->assign('test_url', $test_url);
		$this->assign('page_seo', array('title' => '设备检测 - 视频面试 - ' . C('qscms_site_name')));
		$this->display();
	}
	/**
	 * 视频面试页面
	 */
	public function main()
	{
		$code = I('get.code', '', 'trim');
		if (C('PLATFORM') == 'mobile') {
			redirect(U('Mobile/Vi/main', array('code' => $code)));
			die;
		}
		if (C('qscms_video_interview_open') == 0) {
			$this->error('视频面试功能未开启', U('index/index'));
		}
		if (!D('VideoInterview')->checkCodeLegal($code)) {
			$this->error('非法请求');
			die;
		}
		if (false === $interview_info = D('VideoInterview')->getInterviewByCode($code)) {
			$this->error('没有找到面试信息');
		}
		if ($interview_info['info']['deadline'] < time()) {
			$room_status = 'overtime';
		} else {
			$interview_daytime = strtotime(date('Y-m-d', $interview_info['info']['interview_time']));
			if (time() < $interview_daytime) {
				$room_status = 'nostart';
			} else {
				$room_status = 'opened';
			}
		}


		$resume_info = D('Resume')->where(array('uid' => $interview_info['info']['personal_uid']))->find();
		if ($resume_info['display_name'] == "2") {
			$resume_info['fullname'] = "N" . str_pad($resume_info['resumeid'], 7, "0", STR_PAD_LEFT);
		} elseif ($resume_info['display_name'] == "3") {
			if ($resume_info['sex'] == 1) {
				$resume_info['fullname'] = cut_str($resume_info['fullname'], 1, 0, "先生");
			} elseif ($resume_info['sex'] == 2) {
				$resume_info['fullname'] = cut_str($resume_info['fullname'], 1, 0, "女士");
			}
		}
		$y = date("Y");
		if (intval($resume_info['birthdate']) == 0) {
			$resume_info['age'] = '';
		} else {
			$resume_info['age'] = $y - $resume_info['birthdate'];
		}


		$jobs_info = D('Jobs')->where(array('id' => $interview_info['info']['jobs_id']))->find();
		$jobs_info = $this->handle_wage($jobs_info);
		$main_url = U('vi/main', array('code' => $code));
		$test_url = U('vi/test', array('code' => $code));

		$userid = $interview_info['utype'] == 1 ? $interview_info['info']['company_uid'] : $interview_info['info']['personal_uid'];
		$userid = 'user_' . $userid . '_splpc';

		$tencent = new \Common\ORG\TLSSigAPIv2(C('qscms_trtc_appid'), C('qscms_trtc_appsecret'));
		$sig = $tencent->genSig($userid);


		$this->assign('sig', $sig);
		$this->assign('code', $code);
		$this->assign('room_status', $room_status);
		$this->assign('resume_info', $resume_info);
		$this->assign('jobs_info', $jobs_info);
		$this->assign('main_url', $main_url);
		$this->assign('test_url', $test_url);
		$this->assign('utype', $interview_info['utype']);
		$this->assign('info', $interview_info['info']);
		$this->assign('userid', $userid);
		$this->assign('roomid', $interview_info['info']['id']);
		$this->assign('PersonalRemindNum',session('personal_remind.num')?:0);
		$this->assign('companyRemindNum',session('company_remind.num')?:0);
		$this->assign('page_seo', array('title' => '视频面试 - ' . C('qscms_site_name')));
		$this->display();
	}
	/**
	 * 提醒个人
	 */
	public function ajax_notice_persoal()
	{
		if (C('qscms_video_interview_open') == 0) {
			$this->ajaxReturn(0, '视频面试功能未开启');
		}
		$code = I('request.code', '', 'trim');
		!$code && $this->ajaxReturn(0, '请正确选择面试信息！');
		if($remind = session('personal_remind')){
			if (C('qscms_captcha_open') && $remind['num'] > 3 && true !== $reg = \Common\qscmslib\captcha::verify()) $this->ajaxReturn(0, $reg);
			if(time() < $remind['time'] + 180) $this->ajaxReturn(0, '180秒内仅能提醒一次,请稍后重试');
		}else{
			$remind = array('num'=>0,'time'=>0);
		}
		$interview = D('VideoInterview')->where(array('company_code' => $code))->find();
		!$interview && $this->ajaxReturn(0, '面试信息不存在！');
		D('VideoInterview')->notice_personal($interview);
		session('personal_remind', array('num' => $remind['num'] + 1, 'time' => time()));
		$this->ajaxReturn(1, '提醒成功');
	}
	/**
	 * 提醒企业
	 */
	public function ajax_notice_company()
	{
		if (C('qscms_video_interview_open') == 0) {
			$this->ajaxReturn(0, '视频面试功能未开启');
		}
		$code = I('request.code', '', 'trim');
		!$code && $this->ajaxReturn(0, '请正确选择面试信息！');
		if($remind = session('company_remind')){
			if (C('qscms_captcha_open') && $remind['num'] > 3 && true !== $reg = \Common\qscmslib\captcha::verify()) $this->ajaxReturn(0, $reg);
			if(time() < $remind['time'] + 180) $this->ajaxReturn(0, '180秒内仅能提醒一次,请稍后重试');
		}else{
			$remind = array('num'=>0,'time'=>0);
		}
		$interview = D('VideoInterview')->where(array('personal_code' => $code))->find();
		!$interview && $this->ajaxReturn(0, '面试信息不存在！');
		D('VideoInterview')->notice_company($interview);
		session('company_remind', array('num' => $remind['num'] + 1, 'time' => time()));
		$this->ajaxReturn(1, '提醒成功');
	}
	/**
	 * 检测网络
	 */
	public function network_check()
	{
		if (C('qscms_video_interview_open') == 0) {
			$this->ajaxReturn(0, '视频面试功能未开启');
		}
		$this->ajaxReturn(1, '网络正常');
	}
	protected function handle_wage($value)
	{
		if ($value['negotiable'] == 0) {
			if (C('qscms_wage_unit') == 1) {
				$value['minwage'] = $value['minwage'] % 1000 == 0 ? (($value['minwage'] / 1000) . 'K') : (round($value['minwage'] / 1000, 1) . 'K');
				$value['maxwage'] = $value['maxwage'] ? ($value['maxwage'] % 1000 == 0 ? (($value['maxwage'] / 1000) . 'K') : (round($value['maxwage'] / 1000, 1) . 'K')) : 0;
			} elseif (C('qscms_wage_unit') == 2) {
				if ($value['minwage'] >= 10000) {
					if ($value['minwage'] % 10000 == 0) {
						$value['minwage'] = ($value['minwage'] / 10000) . '万';
					} else {
						$value['minwage'] = round($value['minwage'] / 10000, 1);
						$value['minwage'] = strpos($value['minwage'], '.') ? str_replace('.', '万', $value['minwage']) : $value['minwage'] . '万';
					}
				} else {
					if ($value['minwage'] % 1000 == 0) {
						$value['minwage'] = ($value['minwage'] / 1000) . '千';
					} else {
						$value['minwage'] = round($value['minwage'] / 1000, 1);
						$value['minwage'] = strpos($value['minwage'], '.') ? str_replace('.', '千', $value['minwage']) : $value['minwage'] . '千';
					}
				}
				if ($value['maxwage'] >= 10000) {
					if ($value['maxwage'] % 10000 == 0) {
						$value['maxwage'] = ($value['maxwage'] / 10000) . '万';
					} else {
						$value['maxwage'] = round($value['maxwage'] / 10000, 1);
						$value['maxwage'] = strpos($value['maxwage'], '.') ? str_replace('.', '万', $value['maxwage']) : $value['maxwage'] . '万';
					}
				} elseif ($value['maxwage']) {
					if ($value['maxwage'] % 1000 == 0) {
						$value['maxwage'] = ($value['maxwage'] / 1000) . '千';
					} else {
						$value['maxwage'] = round($value['maxwage'] / 1000, 1);
						$value['maxwage'] = strpos($value['maxwage'], '.') ? str_replace('.', '千', $value['maxwage']) : $value['maxwage'] . '千';
					}
				} else {
					$value['maxwage'] = 0;
				}
			}
			if ($value['maxwage'] == 0) {
				$value['wage_cn'] = '面议';
			} else {
				if ($value['minwage'] == $value['maxwage']) {
					$value['wage_cn'] = $value['minwage'] . '/月';
				} else {
					$value['wage_cn'] = $value['minwage'] . '-' . $value['maxwage'] . '/月';
				}
			}
		} else {
			$value['wage_cn'] = '面议';
		}
		return $value;
	}
}
