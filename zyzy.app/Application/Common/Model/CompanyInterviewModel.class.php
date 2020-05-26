<?php
namespace Common\Model;

use Think\Model;

class CompanyInterviewModel extends Model {
    protected $_validate = array(
        array('resume_id,resume_name,resume_addtime,resume_uid,jobs_id,jobs_name,jobs_addtime,company_id,company_name,company_addtime,company_uid,interview_time,address,contact,telephone', 'identicalNull', '', 1, 'callback'),
        array('resume_id,resume_addtime,resume_uid,jobs_id,jobs_addtime,company_id,company_addtime,company_uid', 'identicalEnum', '', 1, 'callback'),
        array('telephone', '_telephone', '{%company_interview_format_error_telephone}', 2, 'callback'),
    );
    protected $_auto = array(
        array('interview_addtime', 'time', 1, 'function'),
        array('personal_look', 1, 1),
    );

    protected function _telephone($data) {
        if (!fieldRegex($data, 'tel') && !fieldRegex($data, 'mobile')) return false;
        return true;
    }

    /*
        面试邀请
        个人查看
        @data 查询条件 array('personal_look'=>$look,'resume_uid'=>$resume_uid,'resume_id'=>resume_id)
        企业查看
        @data 查询条件 array('personal_look'=>$look,'company_uid'=>$company_uid,'jobs_id'=>jobs_id)
        返回值 array list 列表数据  page 分页
    */
    public function get_invitation_pre($data, $utype = 2, $pagesize = 10) {
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'company_interview';
        // 个人查看
        if ($utype == 2) {
            $join = 'left join ' . $db_pre . 'jobs j on j.id=' . $this_t . '.jobs_id';
            $count = $this->where($data)->join($join)->count();
            $rst['count'] = $count;
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->join($join)->field($this_t . '.*,j.district,j.addtime,j.companyname belong_name,j.company_addtime,j.district_cn,j.minwage,j.maxwage,j.deadline,j.refreshtime,j.click,j.education_cn,j.experience_cn')->order('did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
            foreach ($rst['list'] as $key => $val) {
                if (empty($val['belong_name'])) {
                    $jobs = M('JobsTmp')->where(array('id' => $val['jobs_id']))->find();
                    $val['addtime'] = $jobs['addtime'];
                    $val['companyname'] = $jobs['companyname'];
                    $val['belong_name'] = $jobs['companyname'];
                    $val['company_addtime'] = $jobs['company_addtime'];
                    $val['minwage'] = $jobs['minwage'];
                    $val['maxwage'] = $jobs['maxwage'];
                    $val['district_cn'] = $jobs['district_cn'];
                    $val['education_cn'] = $jobs['education_cn'];
                    $val['experience_cn'] = $jobs['experience_cn'];
                    $val['deadline'] = $jobs['deadline'];
                    $val['refreshtime'] = $jobs['refreshtime'];
                    $val['click'] = $jobs['click'];
                }
				$profile = M('CompanyProfile')->where(array('id' => $val['company_id']))->getField('logo');//添加企业logo调取
                if ($profile['logo'])
                {
                    $val['logo']=attach($profile['logo'],'company_logo');
                }
                else
                {
                    $val['logo']=attach('no_logo.png','resource');
                }
                $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
                $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));
                if ($val['negotiable'] == 0) {
                    $val['minwage'] = $val['minwage'] % 1000 == 0 ? ($val['minwage'] / 1000) : round($val['minwage'] / 1000, 1);
                    $val['maxwage'] = $val['maxwage'] ? ($val['maxwage'] % 1000 == 0 ? ($val['maxwage'] / 1000) : round($val['maxwage'] / 1000, 1)) : 0;
                    if ($val['maxwage'] == 0) {
                        $val['wage_cn'] = '面议';
                    } else {
                        if ($val['minwage'] == $val['maxwage']) {
                            $val['wage_cn'] = $val['minwage'] . 'K/月';
                        } else {
                            $val['wage_cn'] = $val['minwage'] . 'K-' . $val['maxwage'] . 'K/月';
                        }
                    }
                } else {
                    $val['wage_cn'] = '面议';
                }
                $rst['list'][$key] = $val;
            }
        } // 企业查看
        elseif ($utype == 1) {
            $join = 'left join ' . $db_pre . 'resume r on r.id=' . $this_t . '.resume_id';
            $count = $this->where($data)->join($join)->count();
            $rst['count'] = $count;
            $pager = pager($count, $pagesize);
            $rst['list'] = $this->where($data)->join($join)->field($this_t . '.*,r.display_name,r.wage,r.wage_cn,r.fullname,r.sex,r.sex_cn,r.birthdate,r.education_cn,r.experience_cn')->order('did desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
            foreach ($rst['list'] as $key => $val) {
                if ($val['display_name'] == "2") {
                    $val['fullname'] = "N" . str_pad($val['resume_id'], 7, "0", STR_PAD_LEFT);
                } elseif ($val['display_name'] == "3") {
                    if ($val['sex'] == 1) {
                        $val['fullname'] = cut_str($val['fullname'], 1, 0, "先生");
                    } elseif ($val['sex'] == 2) {
                        $val['fullname'] = cut_str($val['fullname'], 1, 0, "女士");
                    }
                }
                $val['jobs_name_'] = cut_str($val['jobs_name'], 7, 0, "...");
                $val['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume_id'], 'apply' => 1));
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
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }

    /*
        面试邀请	设置查看状态
        @$did id
        @$user  个人会员信息 uid,utype,username 等
        @$setlook 查看状态

        返回值 数组
        @state 状态 0 失败 1 成功
        @error 错误信息
        @num   修改条数
    */
    public function set_invitation($did, $user, $setlook) {
        if (!is_array($did)) $did = array($did);
        $sqlin = implode(",", $did);
        if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/", $sqlin)) return array('state' => 0, 'error' => 'did 错误！');
        $where['did'] = array('in', $sqlin);
        $where['resume_uid'] = $user['uid'];
        $num = $this->where($where)->setField('personal_look', intval($setlook));
        if (false === $num) return array('state' => 0, 'error' => '设置失败！');
        //写入会员日志
        write_members_log($user, '', '标记面试邀请记录（记录id：' . $sqlin . '）');
        return array('state' => 1, 'num' => $num);
    }

    /*
        删除 面试邀请
        @$did id
        @$user 会员信息 uid，utype，username 等

        返回值 数组
        @state 状态 0 失败 1 成功
        @error 错误信息
        @num   修改条数
    */
    public function del_interview($did, $user) {
        if (!is_array($did)) $did = array($did);
        $sqlin = implode(",", $did);
        if (!fieldRegex($sqlin, 'in')) return array('state' => 0, 'error' => 'did 错误！');
        $where['did'] = array('in', $sqlin);
        if ($user['utype'] == 2) {
            $where['resume_uid'] = $user['uid'];
        } else {
            $where['company_uid'] = $user['uid'];
        }
        $num = $this->where($where)->delete();
        if (false === $num) return array('state' => 0, 'error' => '删除失败！');
        //写入会员日志
        write_members_log($user, '', '删除面试邀请记录（记录id：' . $sqlin . '）');
        return array('state' => 1, 'num' => $num);
    }

    /*
     * 增加面试邀请
     * $addarr  传入要添加的数组
     *
     * 返回值 数组
        @state 状态 0 失败 1 成功
        @error 错误信息
        @num   修改条数
     */
    public function add_interview($addarr, $user) {
        if ($user['utype'] != 1) return array('state' => 0, 'msg' => '必须是企业会员才可以邀请面试');
        if ($user['status'] == 2) return array('state' => 0, 'msg' => '您的账号处于暂停状态，请联系管理员设为正常后进行操作');
        if (!$addarr['resume_id']) return array('state' => 0, 'msg' => '请选择简历');
        $user_jobs = D('Jobs')->count_auditjobs_num($user['uid']);
        if ($user_jobs == 0) return array('state' => 0, 'msg' => '邀请失败，你没有发布招聘信息或者信息没有审核通过');
        $check_interview = $this->check_interview($addarr['resume_id'], $user['uid'], $addarr['jobs_id']);
        if ($check_interview) return array('state' => 0, 'msg' => '您已对该简历进行过面试邀请,不能重复邀请');
        $setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);
        $resume = D('Resume')->get_resume_one($addarr['resume_id']);
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
        $_resume_fullname = $resume['fullname'];
        if ($resume['display_name'] == 2) {
            $addarr['resume_name'] = "N" . str_pad($resume['id'], 7, "0", STR_PAD_LEFT);
        } elseif ($resume['display_name'] == 3) {
            if ($resume['sex'] == 1) {
                $addarr['resume_name'] = cut_str($resume['fullname'], 1, 0, "先生");
            } elseif ($resume['sex'] == 2) {
                $addarr['resume_name'] = cut_str($resume['fullname'], 1, 0, "女士");
            }
        } else {
            $addarr['resume_name'] = $resume['fullname'];
        }
        $addarr['resume_addtime'] = $resume['addtime'];
        $addarr['resume_uid'] = $resume['uid'];
        $addarr['jobs_name'] = $job_info['jobs_name'];
        $addarr['jobs_addtime'] = $job_info['addtime'];
        $addarr['company_id'] = $job_info['company_id'];
        $addarr['company_name'] = $job_info['companyname'];
        $addarr['company_addtime'] = $job_info['company_addtime'];
        $addarr['company_uid'] = $user['uid'];
        if (false === $this->create($addarr)) return array('state' => 0, 'msg' => $this->getError());
        if (false === $insertid = $this->add()) return array('state' => 0, 'msg' => '邀请面试失败');
        M('PersonalJobsApply')->where(array('resume_id' => $addarr['resume_id'], 'jobs_id' => $addarr['jobs_id'], 'company_uid' => $user['uid']))->save(array('is_reply' => 1, 'personal_look' => 2));
        //写入会员日志
        write_members_log($user, '', '邀请面试（职位id：' . $addarr['jobs_id'] . '；简历id：' . $addarr['resume_id'] . '）');
        //====================各种提醒====================
        //发送站内信
        $replac_pms['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $job_info['id']), '', false);
        $replac_pms['company_url'] = url_rewrite('QS_companyshow', array('id' => $job_info['company_id']), '', false);
        $setsqlarr_pms['message'] = $job_info['companyname'] . '邀请您参加公司面试，面试职位：<a href="' . $replac_pms['jobs_url'] . '" target="_blank">【' . $job_info['jobs_name'] . '】</a>，<a href="' . $replac_pms['company_url'] . '" target="_blank">点击查看公司详情>></a>';
        $per_user_info = D('Members')->find($resume['uid']);
        D('Pms')->write_pmsnotice($per_user_info['uid'], $per_user_info['username'], $setsqlarr_pms['message'],2);
        //sms
        $sms = D('SmsConfig')->get_cache();
		
        if ($sms['set_invite'] == 1 && $addarr['sms_notice'] == 1) {
            $send_sms = true;
            if (C('qscms_company_sms') == 1) {
                $user_sms_num = D('Members')->where(array('uid' => $user['uid']))->getField('sms_num');
                if ($user_sms_num == 0) {
                    $send_sms = false;
                }
            }
			
            if ($send_sms == true) {
                $usermobile = D('Members')->get_user_one(array('uid' => $resume['uid']));
                $sms_data['companyname'] = $job_info['companyname'];
                $sms_data['jobsname'] = $job_info['jobs_name'];
                $sms_data['mobile'] = $addarr['telephone'];
                $sms_data['contact'] = $addarr['contact'];
                $sms_data['address'] = $addarr['address'];
                $r = D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_invite', 'data' => $sms_data));
                if($r === true) {
					if (C('qscms_company_sms') == 1) {
						D('Members')->where(array('uid' => $user['uid']))->setDec('sms_num');
					}
                    
                }
            }
        }
        //微信
        if (false === $module_list = F('apply_list')) $module_list = D('Apply')->apply_cache();
        if ($module_list['Weixin']) {
            D('Weixin/TplMsg')->set_invite($addarr['resume_uid'], $addarr['jobs_id'], $addarr['company_name'], $addarr['jobs_name'], date('Y-m-d H:i', $addarr['interview_time']), $addarr['address'], $addarr['contact'], $addarr['telephone'], $addarr['notes']);
        }
        return array('state' => 1, 'msg' => '邀请面试成功');//增加面试邀请
    }

    /**
     * 检测是否邀请面试过
     */
    public function check_interview($resume_id, $company_uid, $jobs_id) {
        $where['resume_id'] = $resume_id;
        $where['company_uid'] = $company_uid;
        $where['jobs_id'] = $jobs_id;
        return $this->field('did')->where($where)->find();
    }
}

?>