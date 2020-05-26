<?php
namespace Common\Model;

use Think\Model;

class PersonalJobsApplyModel extends Model {
    public $state_arr = array('1' => '合适', '2' => '不合适', '3'=> '待定', '4' => '未接通');
    protected $_validate = array(
        array('resume_id,resume_name,personal_uid,jobs_id,jobs_name,company_id,company_name,company_uid', 'identicalNull', '', 0, 'callback'),
        array('resume_id,personal_uid,jobs_id,huntet_id,huntet_uid', 'identicalEnum', '', 0, 'callback'),
    );
    protected $_auto = array(
        array('apply_addtime', 'time', 1, 'function'),
        array('personal_look', 1),
    );

    /*
        获取 申请职位列表
        $data  查询条件
        $data['personal_uid']=session('uid');
        $data['resume_id']=$resume_id;
        $data['personal_look']=$personal_look;
        $data['apply_addtime']=array('gt',$time);
        $data['is_reply']=$is_reply;

        返回值 数组
        $rst['list'] 数据列表 array
        $rst['page'] 分页

    */
	 public function get_apply_jobs($data, $utype = 2, $state = 0, $pagesize = 10) {
		  if ($state) {
            $data['is_reply'] = $state;
        }
        if ($utype == 2)//个人查看
        {
            $count = $this->where($data)->count();
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->order('is_reply asc,did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
			foreach($rst['list'] as $k=>$v){
				$jobsid[]=$v['jobs_id'];
			}
			if($jobsid){
				$where_j['id']=array('in',$jobsid);
				$jobs=M('Jobs')->where($where_j)->getField('id,district,company_id,companyname,companyname,company_addtime,minwage,maxwage,district_cn,deadline,refreshtime,click,experience_cn,education_cn');
				$jobs_tmp=M('JobsTmp')->where($where_j)->getField('id,district,company_id,companyname,companyname,company_addtime,minwage,maxwage,district_cn,deadline,refreshtime,click,experience_cn,education_cn');
				foreach ($rst['list'] as $key => $val) {
					$merge=$jobs[$val['jobs_id']]?:$jobs_tmp[$val['jobs_id']];
					if($merge){
						$val=array_merge($val,$merge);
						$val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
						$val['belong_name'] = $val['company_name'];
						$val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
						//答复状态
						if ($val['personal_look'] == '1') {
							$val['reply_status'] = "企业未查看";
						} else {
							if ($val['is_reply'] == '0') {
								$val['reply_status'] = "待反馈";
							} elseif ($val['is_reply'] == '1') {
								$val['reply_status'] = "合适";
							} elseif ($val['is_reply'] == '2') {
								$val['reply_status'] = "不合适";
							} elseif ($val['is_reply'] == '3') {
								$val['reply_status'] = "待定";
							} elseif ($val['is_reply'] == '4') {
								$val['reply_status'] = "未接通";
							}
						} 
						$rst['list'][$key] = $val;
					}
				}
			}
        } elseif ($utype == 1)//企业查看
		{
            $count = $this->where($data)->count();
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->limit($pager->firstRow . ',' . $pager->listRows)->order('did desc')->select();
			foreach($rst['list'] as $k=>$v){
				$jobsid[]=$v['jobs_id'];
				$resumeid[]=$v['resume_id'];
			}
			if($jobsid && $resumeid){
				$where_j['id']=array('in',$jobsid);
				$where_r['id']=array('in',$resumeid);
				$jobs=M('Jobs')->where($where_j)->getField('id,district,company_id,companyname,companyname,company_addtime,minwage,maxwage,district_cn,deadline,refreshtime,click,experience_cn,education_cn');
				$jobs_tmp=M('JobsTmp')->where($where_j)->getField('id,district,company_id,companyname,companyname,company_addtime,minwage,maxwage,district_cn,deadline,refreshtime,click,experience_cn,education_cn');
				$resume=M('Resume')->where($where_r)->getField('id,uid,fullname,display_name,sex_cn,sex,education_cn,experience_cn,major_cn,intention_jobs,district_cn,wage_cn,trade_cn,nature_cn,birthdate,addtime,refreshtime,display,is_quick,photo_img');
				foreach ($rst['list'] as $key => $val) {
					$merge=$jobs[$val['jobs_id']]?:$jobs_tmp[$val['jobs_id']];
					if($merge){
						if ($resume[$val['resume_id']]) {
							$merge_jr=array_merge($merge,$resume[$val['resume_id']]);
							$val=array_merge($val,$merge_jr);
							$val['resume_name'] = $resume[$val['resume_id']]['title'];
						} else {
							$val=array_merge($val,$merge);
							$val['resume_name'] = "该简历已经删除";
						}
						$setmeal = D('MembersSetmeal')->get_user_setmeal($val['company_uid']);
						if ($setmeal['show_apply_contact'] == "0") {
							if ($val['display_name'] == "2") {
								$val['fullname'] = "N" . str_pad($val['id'], 7, "0", STR_PAD_LEFT);
							} elseif ($val['display_name'] == "3") {
								if ($val['sex'] == 1) {
									$val['fullname'] = cut_str($val['fullname'], 1, 0, "先生");
								} elseif ($val['sex'] == 2) {
									$val['fullname'] = cut_str($val['fullname'], 1, 0, "女士");
								}
							}
						}
						$val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
						$val['jobs_name_'] = cut_str($val['jobs_name'], 7, 0, "...");
						$val['specialty_'] = $val['specialty'];
						$val['specialty'] = cut_str($val['specialty'], 30, 0, "...");
						$y = date("Y");
						if (intval($val['birthdate']) == 0) {
							$val['age'] = '';
						} else {
							$val['age'] = $y - $val['birthdate'];
						}
						/* 教育经历 培训经历 */
						// $val['resume_education_list']=M('ResumeEducation')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
						// $val['resume_work_list']=M('ResumeWork')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
						/*
							获取简历标记
						*/
						// $val_state= M('CompanyLabelResume')->where(array('uid'=>$data['company_uid'],'resume_id'=>$val['resume_id']))->find();
						// $val['resume_state']=$val_state['resume_state'];
						// $val['resume_state_cn']=$val_state['resume_state_cn'];
						$rst['list'][$key] = $val;
					}
				}
			}
        }
        $rst['count'] = $count;
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }
    public function get_old_apply_jobs($data, $utype = 2, $state = 0, $pagesize = 10) {
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'personal_jobs_apply';
        foreach ($data as $key => $val) {
            $data[$this_t . '.' . $key] = $val;
            unset($data[$key]);
        }
        if ($state) {
            $data[$this_t . '.is_reply'] = $state;
        }
        if ($utype == 2)//个人查看
        {
            $join = 'left join ' . $db_pre . 'jobs j on j.id=' . $this_t . '.jobs_id';
            $count = $this->where($data)->join($join)->count();
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->join($join)->field($this_t . '.*,j.district,j.addtime,j.company_id,j.companyname,j.company_addtime,j.minwage,j.maxwage,j.district_cn,j.deadline,j.refreshtime,j.click')->order($this_t . '.is_reply asc,did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
            foreach ($rst['list'] as $key => $val) {
                if (empty($val['companyname'])) {
                    $jobs = M('JobsTmp')->where(array('id' => $val['jobs_id']))->find();
                    $val['addtime'] = $jobs['addtime'];
                    $val['companyname'] = $jobs['companyname'];
                    $val['company_addtime'] = $jobs['company_addtime'];
                    $val['company_id'] = $jobs['company_id'];
                    $val['minwage'] = $jobs['minwage'];
                    $val['maxwage'] = $jobs['maxwage'];
                    $val['district'] = $jobs['district'];
                    $val['district_cn'] = $jobs['district_cn'];
                    $val['deadline'] = $jobs['deadline'];
                    $val['refreshtime'] = $jobs['refreshtime'];
                    $val['click'] = $jobs['click'];
                }

                $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
                $val['belong_name'] = $val['company_name'];
                $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
                //答复状态
                if ($val['personal_look'] == '1') {
                    $val['reply_status'] = "企业未查看";
                } else {
                    if ($val['is_reply'] == '0') {
                        $val['reply_status'] = "待反馈";
                    } elseif ($val['is_reply'] == '1') {
                        $val['reply_status'] = "合适";
                    } elseif ($val['is_reply'] == '2') {
                        $val['reply_status'] = "不合适";
                    } elseif ($val['is_reply'] == '3') {
                        $val['reply_status'] = "待定";
                    } elseif ($val['is_reply'] == '4') {
                        $val['reply_status'] = "未接通";
                    }
                }
                $rst['list'][$key] = $val;
            }
        } elseif ($utype == 1)//企业查看
        {
            $join = 'left join ' . $db_pre . 'resume r on r.id=' . $this_t . '.resume_id';
            $join1 = 'left join ' . $db_pre . 'jobs j on j.id=' . $this_t . '.jobs_id';
            $count = $this->where($data)->join($join)->join($join1)->count();
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->join($join)->join($join1)->field($this_t . '.*,j.district,j.company_id,j.companyname,j.company_addtime,j.deadline,j.click,r.id,r.uid as ruid,r.fullname,r.display_name,r.sex_cn,r.sex,r.education_cn,r.experience_cn,r.major_cn,r.intention_jobs,r.district_cn,r.wage_cn,r.trade_cn,r.nature_cn,r.birthdate,r.addtime,r.refreshtime,r.display,r.is_quick')->order('did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
            foreach ($rst['list'] as $key => $val) {
                $resume = M('Resume')->where(array('id' => $val['resume_id']))->find();
                if ($resume) {
                    $val['resume_name'] = $resume['title'];
                } else {
                    $val['resume_name'] = "该简历已经删除";
                }
                if (empty($val['companyname'])) {
                    $jobs = M('JobsTmp')->where(array('id' => $val['jobs_id']))->find();
                    $val['companyname'] = $jobs['companyname'];
                    $val['company_addtime'] = $jobs['company_addtime'];
                    $val['company_id'] = $jobs['company_id'];
                    $val['district'] = $jobs['district'];
                    $val['district_cn'] = $jobs['district_cn'];
                    $val['deadline'] = $jobs['deadline'];
                    $val['click'] = $jobs['click'];
                }
                $setmeal = D('MembersSetmeal')->get_user_setmeal($val['company_uid']);
                if ($setmeal['show_apply_contact'] == "0") {
                    if ($val['display_name'] == "2") {
                        $val['fullname'] = "N" . str_pad($val['id'], 7, "0", STR_PAD_LEFT);
                    } elseif ($val['display_name'] == "3") {
                        if ($val['sex'] == 1) {
                            $val['fullname'] = cut_str($val['fullname'], 1, 0, "先生");
                        } elseif ($val['sex'] == 2) {
                            $val['fullname'] = cut_str($val['fullname'], 1, 0, "女士");
                        }
                    }
                }
                $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
                $val['jobs_name_'] = cut_str($val['jobs_name'], 7, 0, "...");
                $val['specialty_'] = $val['specialty'];
                $val['specialty'] = cut_str($val['specialty'], 30, 0, "...");
                $y = date("Y");
                if (intval($val['birthdate']) == 0) {
                    $val['age'] = '';
                } else {
                    $val['age'] = $y - $val['birthdate'];
                }
                /* 教育经历 培训经历 */
                // $val['resume_education_list']=M('ResumeEducation')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
                // $val['resume_work_list']=M('ResumeWork')->where(array('uid'=>$val['ruid'],'pid'=>$val['resume_id']))->select();
                /*
                    获取简历标记
                */
                // $val_state= M('CompanyLabelResume')->where(array('uid'=>$data['company_uid'],'resume_id'=>$val['resume_id']))->find();
                // $val['resume_state']=$val_state['resume_state'];
                // $val['resume_state_cn']=$val_state['resume_state_cn'];
                $rst['list'][$key] = $val;
            }
        }
        $rst['count'] = $count;
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }

    /*
        删除 申请的职位
        @$del_id 删除id 多个用,分割
        @$user 会员信息 uid,username,utype

        返回值 数组
        @state 删除状态 0 失败，1成功
        @error 错误信息
        @num   删除行数
    */
    public function del_jobs_apply($del_id, $user) {
        if (!is_array($del_id)) $del_id = array($del_id);
        $sqlin = implode(",", $del_id);
        if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/", $sqlin)) return array('state' => 0, 'error' => 'del_id 错误！');
        $where['did'] = array('in', $sqlin);
        if ($user['utype'] == 1) {
            $where['company_uid'] = $user['uid'];
        } elseif ($user['utype'] == 2) {
            $where['personal_uid'] = $user['uid'];
        }
        $num = $this->where($where)->delete();
        if (false === $num) return array('state' => 0, 'error' => '删除失败！');
        //写入会员日志
        write_members_log($user, '', '删除申请职位记录（记录id：' . $sqlin . '）');
        return array('state' => 1, 'num' => $num);
    }

    /*
        获取今天投递简历数
        @$uid 会员uid

        返回值 $total 投递次数 int
    */
    public function get_now_applyjobs_num($uid) {
        $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $where['personal_uid'] = intval($uid);
        $where['apply_addtime'] = array('gt', $now);
        $num = $this->where($where)->count();
        $num1 = M('PersonalHunterJobsApply')->where($where)->count();
        return $num + $num1;
    }

    /*
        企业对申请职位的简历进行 设为已看
    */
    public function set_apply($apply, $user, $setlook) {
        if($apply['personal_look'] != 2){
            $where['did'] = $apply['did'];
            $where['company_uid'] = $user['uid'];
            $data['personal_look'] = intval($setlook);
            $data['personal_look_time'] = time();
            $num = $this->where($where)->save($data);
            if (false === $num) return array('state' => 0, 'error' => '设置失败！');
            //写入会员日志
            write_members_log($user, '', '标记申请职位记录（记录id：' . implode(",", $apply['did']) . '）');
            return array('state' => 1, 'num' => $num);
        }
    }

    /*添加个人申请职位
	 * $data POST 值
	 */
    public function jobs_apply_add($jids, $user, $rid, $is_apply = 2) {
        $points = 0;
        $jids = is_array($jids) ? $jids : explode(",", $jids);
        $count = $this->where(array('personal_uid' => $user['uid'], 'apply_addtime' => array('egt', strtotime('today'))))->count('did');
        if (C('qscms_apply_jobs_max') < $count + count($jids)) return array('state' => 0, 'error' => "您每天可以投递" . C('qscms_apply_jobs_max') . "个职位，今天已投递了{$count}个");
        $field = 'id,uid,fullname,title,district_cn as district,experience,experience_cn,education,intention_jobs as jobs,audit,complete_percent';
        if ($rid) {
            $resume = M('Resume')->field($field)->where(array('id' => $rid, 'uid' => $user['uid']))->find();
            if (!$resume) return array('state' => 0, 'error' => '选择的简历不存在！');
        } else {
            $resume = M('Resume')->field($field)->where(array('uid' => $user['uid']))->order('def desc')->find();
            if (!$resume) return array('state' => 0, 'error' => '请您先填写一份简历！', 'create' => 1);
            $resume_def = D('Resume')->get_resume_list(array('where' => array('uid' => $user['uid']), 'field' => $field, 'limit' => 1, 'order' => 'def desc,id desc', '_audit' => 1));
            if (!$resume_def) return array('state' => 0, 'error' => '您的默认简历尚未审核通过！');

        }
        // 后台设置了申请职位要求的简历完整度 且 默认简历完整度不达标
        if (C('qscms_apply_job_min_percent') && $resume['complete_percent'] < C('qscms_apply_job_min_percent')) {
            return array('state' => 0, 'error' => '您的简历完整度只有 ' . $resume['complete_percent'] . '%，该公司要求达到 ' . C('qscms_apply_job_min_percent') . '% 才可以申请，请继续完善吧~', 'complete' => array('id' => $resume['id'], 'percent' => $resume['complete_percent']));
        }
        $resume['work'] = M('ResumeWork')->where(array('pid' => $resume['id']))->getfield('id');
        $resume['educations'] = M('ResumeEducation')->where(array('pid' => $resume['id']))->getfield('id');
        $jobs = M('Jobs')->where(array('id' => array('in', $jids)))->getfield('id as jobs_id,uid as company_uid,jobs_name,company_id,companyname as company_name,category');

        if (count($jobs) < $count = count($jids)) $jobs_tmpl = M('PersonalFavorites')->where(array('personal_uid' => $user['uid'], 'jobs_id' => array('in', $jids)))->getfield('jobs_id,jobs_name');
        $check_jobs = $this->where(array('jobs_id' => array('in', $jids), 'resume_id' => $resume['id']))->getfield('jobs_id,did,resume_id,apply_addtime');
        foreach ($jids as $val) {
            $jobs_name = $jobs[$val] ? $jobs[$val]['jobs_name'] : $jobs_tmpl[$val];
            $list[$val] = array('id' => $val, 'jobs_name' => $jobs_name, 'company_id' => $jobs[$val]['company_id'], 'company_name' => $jobs[$val]['company_name'], 'status' => 0);
            if (!$jobs[$val]) {
                $list[$val]['tip'] = '该职位已关闭';
                $list[$val]['status'] = 2;
                continue;
            }
            if(C('qscms_apply_jobs_space') > 0){
                $space_time = C('qscms_apply_jobs_space')*24*3600;
                if ($check_jobs[$val] && time()-$check_jobs[$val]['apply_addtime']<=$space_time) {
                    $list[$val]['tip'] = '您已经申请过这个职位了';
                    $list[$val]['status'] = 3;
                    continue;
                }
            }else{
                if ($check_jobs[$val]) {
                    $list[$val]['tip'] = '您已经申请过这个职位了';
                    $list[$val]['status'] = 3;
                    continue;
                }
            }
            $jobs[$val]['resume_id'] = $resume['id'];
            $jobs[$val]['resume_name'] = $resume['title'];
            $jobs[$val]['personal_uid'] = $user['uid'];
            $jobs[$val]['is_apply'] = $is_apply;
            $is_apply == 1 && $jobs[$val]['admin_username'] = C('visitor.username');
            if (false !== $this->create($jobs[$val])) {
                if (false !== $insertid = $this->add()) {
                    $list[$val]['tip'] = '投递成功';
                    $list[$val]['status'] = 1;
                    $success++;
                    $reg = D('TaskLog')->do_task($user, 'submit_resume');//做任务记录积分操作,4:投递简历
                    $reg['state'] && $points += $reg['data'];

                    $jobs_contact = D('JobsContact')->where(array('pid' => $val))->find();
                    $statistics_data['comid'] = $jobs[$val]['company_id'];
                    $statistics_data['jobid'] = $val;
                    $statistics_data['uid'] = $user['uid'];
                    $statistics_data['apply'] = 1;
                    $statistics_data['source'] = C('LOG_SOURCE') == 2 ? 2 : 1;
                    $statistics_model = D('CompanyStatistics');
                    $statistics_model->create($statistics_data);
                    $statistics_model->add();
                    $resume_basicinfo = D('Resume')->get_resume_basic($user['uid'], $resume['id']);
                    $setmeal = D('MembersSetmeal')->get_user_setmeal($jobs[$val]['company_uid']);
                    //站内信
                    $replac_pms['personalfullname'] = $resume_basicinfo['fullname'];
                    if ($setmeal['show_apply_contact'] == "0") {
                        if ($resume_basicinfo['display_name'] == "2") {
                            $replac_pms['personalfullname'] = "N" . str_pad($resume_basicinfo['id'], 7, "0", STR_PAD_LEFT);
                        } elseif ($resume_basicinfo['display_name'] == "3") {
                            if ($resume_basicinfo['sex'] == 1) {
                                $replac_pms['personalfullname'] = cut_str($resume_basicinfo['fullname'], 1, 0, "先生");
                            } elseif ($resume_basicinfo['sex'] == 2) {
                                $replac_pms['personalfullname'] = cut_str($resume_basicinfo['fullname'], 1, 0, "女士");
                            }
                        }
                    }
                    $replac_pms['jobs_url'] = C('qscms_site_domain').url_rewrite('QS_jobsshow', array('id' => $jobs[$val]['jobs_id']), '', false);
                    $replac_pms['resume_url'] = C('qscms_site_domain').url_rewrite('QS_resumeshow', array('id' => $resume['id']), '', false);
                    $setsqlarr_pms['message'] = $replac_pms['personalfullname'] . '申请了您发布的职位：<a href="' . $replac_pms['jobs_url'] . '" target="_blank">【' . $jobs[$val]['jobs_name'] . '】</a>，<a href="' . $replac_pms['resume_url'] . '" target="_blank">点击查看>></a>';
                    $com_user_info = D('Members')->find($jobs[$val]['company_uid']);
                    D('Pms')->write_pmsnotice($com_user_info['uid'], $com_user_info['username'], $setsqlarr_pms['message'],1);

                    //sms
                    $sms = D('SmsConfig')->get_cache();
                    if ($sms['set_applyjobs'] == 1 && $jobs_contact['notify_mobile'] == 1) {
                        $send_sms = true;
                        if (C('qscms_company_sms') == 1) {
                            $user_sms_num = D('Members')->where(array('uid' => $jobs[$val]['company_uid']))->getField('sms_num');
                            if ($user_sms_num == 0) {
                                $send_sms = false;
                            }
                        }
                        if ($send_sms == true) {
                            $usermobile = D('Members')->get_user_one(array('uid' => $jobs[$val]['company_uid']));
                            $r = D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_applyjobs', 'data' => array('jobsname' => $jobs_name, 'personalfullname' => $resume['fullname'])));
                            if ($r === true) {
                                if (C('qscms_company_sms') == 1) {
                                    D('Members')->where(array('uid' => $jobs[$val]['company_uid']))->setDec('sms_num');
                                 }
                            }
                        }
                    }
                    //微信
                    if (false === $module_list = F('apply_list')) $module_list = D('Apply')->apply_cache();
                    if ($module_list['Weixin']) {
                        D('Weixin/TplMsg')->set_applyjobs($jobs[$val]['company_uid'],$jobs[$val]['jobs_id'], $resume['id'], $jobs_name, $resume['fullname']);
                    }

                    //才情start
                    $talent_api = new \Common\qscmslib\talent;
                    $talent_api->act='jobs_apply_add';
                    $talent_api->data = array(
                        'uid'=>$resume['uid'],
                        'jobs_id'=>$jobs[$val]['jobs_id'],
                        'jobs_name'=>$jobs_name,
                        'companyname'=>$jobs[$val]['company_name'],
                        'category'=>$jobs[$val]['category'],
                        'categoryname'=>D('CategoryJobs')->where(array('id'=>$jobs[$val]['category']))->getField('categoryname')
                    );
                    $talent_api->send();
                    //才情end
                    continue;
                }
            }
            $list[$val]['tip'] = $this->getError();//'投递失败';
        }
        return array('state' => 1, 'data' => array('count_jids' => count($jids), 'list' => $list, 'total' => $count, 'points' => $points, 'success' => $success, 'failure' => $count - $success));
    }
	//查看简历是否以投递
	public function jobs_apply_show($jids,$user,$rid,$is_apply=2){
		$jids = is_array($jids)?$jids:explode(",",$jids);
		$resume = M('Resume')->field($field)->where(array('uid'=>$user['uid']))->order('def desc')->find();
		$jobs = M('Jobs')->where(array('id'=>array('in',$jids)))->getfield('id as jobs_id,uid as company_uid,jobs_name,company_id,companyname as company_name');
		if(count($jobs) < $count = count($jids)) $jobs_tmpl = M('PersonalFavorites')->where(array('personal_uid'=>$user['uid'],'jobs_id'=>array('in',$jids)))->getfield('jobs_id,jobs_name');
		$check_jobs = $this->where(array('jobs_id'=>array('in',$jids),'resume_id'=>$resume['id']))->getfield('jobs_id,resume_id');
		
		foreach($jids as $val){
			
			if($check_jobs[$val]){
				$list['tip'] = '您已经申请过这个职位了';
				$list['status'] = 0;
				
			}else{
				$list['tip'] = '请先投递该职位';
				$list['status'] = 1;
				
			}
			
		}
		
		return $list;
	}
    /**
     * 是否显示联系方式
     */
    protected function _get_show_contact($resume, $company_uid, $setmeal) {
        $show_contact = false;
        if (C('qscms_showresumecontact') != 2 || $setmeal['show_apply_contact'] == '1') {
            $show_contact = true;
        }
        if (!$show_contact) {
            //下载过该简历
            $down_resume = D('CompanyDownResume')->check_down_resume($resume['id'], $company_uid);
            if ($down) {
                $show_contact = true;
            }
        }
        return $show_contact;
    }

    /**
     * 获取申请职位总数
     */
    public function count_jobs_apply($uid) {
        return $this->where(array('personal_uid' => $uid))->count();
    }

    /**
     * 检测是否申请过职位
     */
    public function check_jobs_apply($resume_id, $company_uid, $jobs_id = false) {
        $where['resume_id'] = $resume_id;
        $where['company_uid'] = $company_uid;
        $jobs_id && $where['jobs_id'] = $jobs_id;
        return $this->where($where)->find();
    }

    /**
     * 获取申请过职位的简历id
     */
    public function get_jobs_apply_resume_id($rid, $company_uid, $jobs_id = false) {
        $where['resume_id'] = array('in', $rid);
        $where['company_uid'] = $company_uid;
        $jobs_id && $where['jobs_id'] = $jobs_id;
        return $this->where($where)->getField('resume_id,did');
    }
}

?>