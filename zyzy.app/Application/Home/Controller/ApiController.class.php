<?php
namespace Home\Controller;

use Common\Controller\FrontendController;

class ApiController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        $this->check_token();
    }

    protected function check_token() {
        $token = I('request.token', '', 'trim');
        $auth_success = false;
        if ((session('_jobfair_token') && $token != session('_jobfair_token')) || !session('_jobfair_token')) {
            if (decrypt($token, C('PWDHASH')) == C('PWDHASH')) {
                $auth_success = true;
            }
        } else {
            $auth_success = true;
        }
        if ($auth_success) {
            session('_jobfair_token', $token);
        } else {
            $this->ajaxReturn(0, 'TOKEN验证失败！');
        }
    }

    public function register() {
        if (IS_POST) {
            $data['utype'] = I('post.utype', 0, 'intval');
            !in_array($data['utype'], array(1, 2)) && $this->ajaxReturn(0, '请正确选择会员类型!');
            $data['mobile'] = I('post.mobile', '', 'trim');
            !$data['mobile'] && $this->ajaxReturn(0, '请填写手机号!');
            $smsVerify = session('reg_smsVerify');
            !$smsVerify && $this->ajaxReturn(0, '短信验证码错误！');
            $data['mobile'] != $smsVerify['mobile'] && $this->ajaxReturn(0, '手机号不一致！', $smsVerify);
            (time() > $smsVerify['time'] + 600) && $this->ajaxReturn(0, '短信验证码已过期！');
            $vcode_sms = I('post.mobile_vcode', 0, 'intval');
            $mobile_rand = substr(md5($vcode_sms), 8, 16);
            $mobile_rand != $smsVerify['rand'] && $this->ajaxReturn(0, '短信验证码错误！');
            //后台开启注册填写密码
            if (C('qscms_register_password_open')) {
                $data['password'] = I('post.password', '', 'trim');
                !$data['password'] && $this->ajaxReturn(0, '请输入密码!');
                $passwordVerify = I('post.passwordVerify', '', 'trim');
                $data['password'] != $passwordVerify && $this->ajaxReturn(0, '两次密码输入不一致!');
            }
            //注册帐号
            $passport = $this->_user_server('');
            if (false === $data = $passport->register($data)) {
                $this->ajaxReturn(0, $passport->get_error());
            }
            //生成企业信息
            $company_mod = D('CompanyProfile');
            $com_setarr['audit'] = 0;
            $com_setarr['companyname'] = I('post.companyname', '', 'trim,badword');
            $com_setarr['contact'] = I('post.contact', '', 'trim,badword');
            $com_setarr['telephone'] = I('post.mobile', '', 'trim,badword');
            $data = array_merge($com_setarr, $data);
            if (false === $company_mod->create($com_setarr)) $this->ajaxReturn(0, $company_mod->getError());
            $company_mod->uid = $data['uid'];
            $insert_company_id = $company_mod->add();
            !$insert_company_id && $this->ajaxReturn(0, "企业信息注册失败！");

            session('reg_smsVerify', null);
            D('Members')->user_register($data);
            $this->_correlation($data);//同步登录
            $points_rule = D('Task')->get_task_cache($data['utype'], 'reg');
            $this->ajaxReturn(1, '会员注册成功！', $data);
        }
    }

    /**
     * [login 用户登录]
     */
    public function login() {
        if (IS_POST) {
            $expire = I('post.expire', 1, 'intval');
            $passport = $this->_user_server();
            $field = 'uid,utype,username,email,mobile,password,last_login_time,last_login_ip';
            if ($mobile = I('post.mobile', '', 'trim')) {
                if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机号格式错误！');
                $user = M('Members')->field($field)->where(array('mobile' => $mobile))->find();
                if ($user['utype'] != 2) $this->ajaxReturn(0, '请登录个人帐号！');
                if ($user) {
                    $this->ajaxReturn(1, '登录成功！', $user);
                } else {
                    $err = '帐号不存在！';
                }
            } else {
                $username = I('post.username', '', 'trim');
                $password = I('post.password', '', 'trim');
                if (false === $uid = $passport->uc('default')->auth($username, $password)) {
                    $err = $passport->get_error();
                } elseif ($user = M('Members')->field($field)->where()->find($uid)) {
                    if ($user['utype'] != 2) $this->ajaxReturn(0, '请登录个人帐号！');
                    $this->ajaxReturn(1, '登录成功！', $user);
                } else {
                    $err = '帐号不存在！';
                }
            }
            $this->ajaxReturn(0, $err);
        }
    }

    /**
     * [members_edit 会员编辑]
     */
    public function members_edit() {
        $user_mod = D('Members');
        $uid = I('post.uid', 0, 'intval');
        !$uid && $this->ajaxReturn(0, '请选择用户uid！');
        if (false === $data = $user_mod->create(I('post.'))) $this->ajaxReturn(0, $user_mod->getError());
        if (isset($_POST['password'])) {
            $member = $user_mod->find($uid);
            $data['password'] = $user_mod->make_md5_pwd(I('post.password', '', 'trim'), $member['pwd_hash']);
        }
        if (!$user_mod->where(array('uid' => $uid))->save($data)) $this->ajaxReturn(0, '保存失败！');
        $this->ajaxReturn(1, '保存成功！');
    }

    /**
     * [_is_resume 检测简历是否存在]
     * @return boolean [false || 简历信息(按需要添加字段)]
     */
    protected function _is_resume($pid, $uid) {
        !$pid && $pid = I('request.pid', 0, 'intval');
        !$uid && $uid = I('request.uid', 0, 'intval');
        if (!$pid) $this->ajaxReturn(0, '请正确选择简历！');
        $where['id'] = $pid;
        $uid && $where['uid'] = $uid;
        //$field = 'id,uid,title,fullname,sex,nature,nature_cn,trade,trade_cn,birthdate,residence,height,marriage_cn,experience_cn,district_cn,wage_cn,householdaddress,education_cn,major_cn,tag,tag_cn,telephone,email,intention_jobs,photo_img,complete_percent,current,current_cn,word_resume';
        if (!$reg = D('Resume')->field()->where($where)->find()) return false;
        $reg['height'] = $reg['height'] == 0 ? '' : $reg['height'];
        $this->assign('resume', $reg);
        return $reg;
    }

    /*
    **创建简历-基本信息
    */
    public function resume_add() {
        if (IS_POST) {
            $uid = I('post.uid', 0, 'intval');
            !$uid && $this->ajaxReturn(0, '请选择用户uid！');
            $user = M('Members')->find($uid);
            !$user && $this->ajaxReturn(0, '用户不存在！');
            if (!$user['mobile']) {
                $setsqlarr['mobile'] = I('post.telephone', '', 'trim');
                if (false === $reg = M('Members')->where(array('uid' => $uid))->save($setsqlarr)) $this->ajaxReturn(0, '手机验证失败!');
                D('Members')->update_user_info($setsqlarr, $user);
                write_members_log($user, '', '手机验证通过（手机号：' . $setsqlarr['mobile'] . '）');
                $user['mobile'] = $telephone;
            }
            $ints = array('district', 'sex', 'birthdate', 'education', 'experience', 'nature', 'current', 'wage');
            $trims = array('telephone', 'fullname', 'email', 'intention_jobs_id', 'trade');
            foreach ($ints as $val) {
                $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
            }
            foreach ($trims as $val) {
                $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
            }
            $setsqlarr['def'] = 1;
            $setsqlarr['display_name'] = C('qscms_default_display_name');
            $rst = D('Resume')->add_resume($setsqlarr, $user);
            if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
            $this->ajaxReturn(1, '简历创建成功！', array('id' => $rst['id']));
        }
    }

    /**
     * [resume_del_data 删除简历信息]
     */
    public function resume_del_data() {
        if (IS_POST) {
            $id = I('request.id', 0, 'intval');
            $pid = I('request.pid', 0, 'intval');
            $uid = I('request.uid', 0, 'intval');
            $type = I('request.type', '', 'trim');
            $table = array('ResumeEducation','ResumeWork','ResumeTraining','ResumeLanguage','ResumeCredent');
            if($type && !in_array($type,$table)){
                $this->ajaxReturn(0,'请求参数错误！');
            }
            if (!$pid || !$id || !$type) $this->ajaxReturn(0, '请求缺少参数！');
            $user = M('Members')->find($uid);
            !$user && $this->ajaxReturn(0, '用户不存在！');
            if (M($type)->where(array('id' => $id, 'uid' => $uid, 'pid' => $pid))->delete()) {
                switch ($type) {
                    case 'ResumeEducation':
                        write_members_log($user, 'resume', '删除简历教育经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeWork':
                        write_members_log($user, 'resume', '删除简历工作经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeTraining':
                        write_members_log($user, 'resume', '删除简历培训经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeLanguage':
                        write_members_log($user, 'resume', '删除简历语言能力（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeCredent':
                        write_members_log($user, 'resume', '删除简历证书（简历id：' . $pid . '）');
                        break;
                }
                $resume_mod = D('Resume');
                $resume_mod->check_resume($uid, $pid);//更新简历完成状态
                $this->ajaxReturn(1, '删除成功！');
            } else {
                $this->ajaxReturn(0, '删除失败！');
            }
        }
    }

    /**
     * [resume_save_privacy 隐私设置更新数据库]
     */
    public function resume_save_privacy() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        $setsqlarr['display'] = I('post.display', 0, 'intval');
        $where = array('id' => $resume['id'], 'uid' => $user['uid']);
        if (false !== M('Resume')->where($where)->save($setsqlarr)) {
            $reg = D('Resume')->resume_index($resume['id']);
            if (!$reg['state']) $this->ajaxReturn(0, $reg['error']);
            //写入会员日志
            write_members_log($user, 'resume', '保存显示/隐藏设置（简历id：' . $resume['id'] . '）', false, array('resume_id' => $resume['id']));
            $this->ajaxReturn(1, '显示/隐藏设置成功!');
        } else {
            $this->ajaxReturn(0, '显示/隐藏设置失败，请重新操作!');
        }
    }

    /*
    *	简历-修改 - -基本信息
    */
    public function resume_edit_basis() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        if (IS_POST) {
            $ints = array('sex', 'birthdate', 'education', 'major', 'experience', 'email_notify', 'height', 'marriage');
            $trims = array('telephone', 'fullname', 'residence', 'email', 'householdaddress', 'qq', 'weixin');
            foreach ($ints as $val) {
                $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
            }
            foreach ($trims as $val) {
                $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
            }
            if (C('qscms_audit_edit_resume') != "-1") D('ResumeEntrust')->set_resume_entrust($resume['id'], $user['uid']);//添加简历自动投递功能
            $rst = D('Resume')->save_resume($setsqlarr, $resume['id'], $user);
            if ($rst['state']) $this->ajaxReturn(1, '数据保存成功！');
            $this->ajaxReturn(0, $rst['error']);
        }
    }

    /*
    *	简历-修改 - -求职意向
    */
    public function resume_edit_intent() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        if (IS_POST) {
            $setsqlarr['intention_jobs_id'] = I('post.intention_jobs_id', '', 'trim,badword');
            $setsqlarr['trade'] = I('post.trade', '', 'trim,badword');//期望行业
            $setsqlarr['district'] = I('post.district', '', 'trim,badword');//工作地区
            $setsqlarr['nature'] = I('post.nature', 0, 'intval');//工作性质
            $setsqlarr['current'] = I('post.current', 0, 'intval');
            $setsqlarr['wage'] = I('post.wage', 0, 'intval');//期望薪资
            if (C('qscms_audit_edit_resume') != "-1") D('ResumeEntrust')->set_resume_entrust($resume['id'], $resume['uid']);//添加简历自动投递功能
            $rst = D('Resume')->save_resume($setsqlarr, $resume['id'], $user);
            if ($rst['state']) $this->ajaxReturn(1, '求职意向修改成功！');
            $this->ajaxReturn(0, $rst['error']);
        }
    }

    /*
    *	简历-修改 - -自我描述
    */
    public function resume_edit_description() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        if (IS_POST) {
            $specialty = I('post.specialty', '', 'trim,badword');
            !$specialty && $this->ajaxReturn(0, '请输入自我描述!');
            $rst = D('Resume')->save_resume(array('specialty' => $specialty), $resume['id'], $user);
            if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
            write_members_log($user, 'resume', '保存简历自我描述（简历id：' . $resume['id'] . '）', false, array('resume_id' => $resume['id']));
            $this->ajaxReturn(1, '简历自我描述修改成功');
        }
    }

    /*
    *	简历-修改 - -教育经历
    */
    public function resume_edit_education() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        if (IS_POST) {
            $setsqlarr['uid'] = $user['uid'];
            $setsqlarr['school'] = I('post.school', '', 'trim,badword');
            $setsqlarr['speciality'] = I('post.speciality', '', 'trim,badword');
            $setsqlarr['education'] = I('post.education', 0, 'intval');
            $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
            $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
            $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
            $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
            $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
            // 选择至今就不判断结束时间了
            if ($setsqlarr['todate'] == 1) {
                if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择就读时间！');
                if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
                if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
            } else {
                if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择就读时间！');

                if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于当前时间！');
                if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于当前时间！');
                if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读结束时间不允许大于当前时间！');
                if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '就读结束时间不允许大于当前时间！');

                if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
                if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
            }
            $education = D('Category')->get_category_cache('QS_education');
            $setsqlarr['education_cn'] = $education[$setsqlarr['education']];
            $setsqlarr['pid'] = $resume['id'];
            $education = M('ResumeEducation')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count();//获取教育经历数量
            if (count($education) >= 6) $this->ajaxReturn(0, '教育经历不能超过6条！');
            if ($id) {
                $setsqlarr['id'] = $id;
                $name = 'save_resume_education';
            } else {
                $name = 'add_resume_education';
            }
            $reg = D('ResumeEducation')->$name($setsqlarr, $user);
            if ($reg['state']) {
                if (!$id) {
                    $setsqlarr['id'] = $reg['id'];
                    $data = array($setsqlarr);
                } else {
                    $data = array($reg['data']);
                }
                $this->ajaxReturn(1, '教育经历保存成功！', $data);
            } else {
                $this->ajaxReturn(0, $reg['error']);
            }
        }
    }

    /*
    *	简历-修改 - -工作经历
    */
    public function resume_edit_work() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $user = M('Members')->find($resume['uid']);
        if (IS_POST) {
            $setsqlarr['uid'] = $resume['uid'];
            $setsqlarr['companyname'] = I('post.companyname', '', 'trim,badword');
            $setsqlarr['achievements'] = I('post.achievements', '', 'trim,badword');
            $setsqlarr['jobs'] = I('post.jobs', '', 'trim,badword');
            $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
            $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
            $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
            $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
            $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
            // 选择至今就不判断结束时间了
            if ($setsqlarr['todate'] == 1) {
                if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择工作时间！');
                if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
                if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
            } else {
                if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择工作时间！');

                if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
                if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
                if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');
                if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');

                if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '工作开始时间不允许大于结束时间！');
                if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '工作开始时间需小于结束时间！');
            }
            $setsqlarr['pid'] = $resume['id'];
            $work = M('ResumeWork')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count();//获取教育经历数量
            if (count($work) >= 6) $this->ajaxReturn(0, '工作经历不能超过6条！');
            if ($id = I('request.id', 0, 'intval')) {
                $setsqlarr['id'] = $id;
                $name = 'save_resume_work';
            } else {
                $name = 'add_resume_work';
            }
            $reg = D('ResumeWork')->$name($setsqlarr, $user);
            if ($reg['state']) {
                if (!$id) {
                    $setsqlarr['id'] = $reg['id'];
                    $data = array($setsqlarr);
                } else {
                    $data = array($reg['data']);
                }
                $this->ajaxReturn(1, '工作经历保存成功！', $data);
            } else {
                $this->ajaxReturn(0, $reg['error']);
            }
        }
    }

    /**
     * [resume_list 获取简历列表]
     */
    public function resume_list() {
        $where = array(
            '显示数目'   => I('request.limit', 15, 'intval'),
            '分页显示'   => I('request.is_page', 0, 'intval'),
            '关键字'    => I('request.key'),
            '职位分类'   => I('request.jobcategory'),
            '地区分类'   => I('request.citycategory'),
            '日期范围'   => I('request.settr'),
            '学历'     => I('request.education'),
            '工作经验'   => I('request.experience'),
            '工资'     => I('request.wage'),
            '工作性质'   => I('request.nature'),
            '标签'     => I('request.resumetag'),
            '照片'     => I('request.photo'),
            '所学专业'   => I('request.major'),
            '行业'     => I('request.trade'),
            '年龄'     => I('request.age'),
            '性别'     => I('request.sex'),
            '特长描述长度' => 100,
            '排序'     => I('request.sort'),
            '检测登录'   => 1
        );
        $_GET['page'] = I('request.page', 1, 'intval');
        if ($uid = I('request.uid', 0, 'intval')) {
            $user = M('Members')->find($uid);
            C('visitor', $user);
        }
        $resume_mod = new \Common\qscmstag\resume_listTag($where);
        $resume = $resume_mod->run();
        $this->ajaxReturn(1, '简历获取成功！', $resume);
    }

    /**
     * [resume_apply 简历投递]
     */
    public function resume_apply() {
        $jid = I('request.jid', 0, 'intval');
        !$jid && $this->ajaxReturn(0, '请选择要投递的职位！');
        $rid = I('request.id', 0, 'intval');
        if (false === $resume = $this->_is_resume($rid)) $this->ajaxReturn(0, '请选择简历！');
        $user = M('Members')->find($resume['uid']);
        $reg = D('PersonalJobsApply')->jobs_apply_add($jid, $user, $rid);
        if (!$reg['state'] && $reg['complete']) {// 完整度不够
            $this->ajaxReturn(1, $reg['error']);
        }
        !$reg['state'] && $this->ajaxReturn(0, $reg['error']);
        if ($reg['data']['failure']) {
            $this->ajaxReturn(0, $reg['data']['list'][$jid]['tip']);
        } else {
            $this->ajaxReturn(1, '投递成功！');
        }
    }

    /**
     * [resume_show 获取简历详情]
     */
    public function resume_show() {
        !$id && $id = I('request.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请选择简历！');
        if ($uid = I('request.uid', 0, 'intval')) {
            $user = M('Members')->find($uid);
            C('visitor', $user);
        }
        $resume_mod = new \Common\qscmstag\resume_showTag(array('简历id'=>$id,'接口'=>1));
        $resume = $resume_mod->run();
        $this->visitor->logout();
        $this->ajaxReturn(1, '简历获取成功！', $resume);
    }

    /**
     * [_is_jobs 检测简历是否存在]
     * @return boolean [false || 职位信息(按需要添加字段)]
     */
    protected function _is_jobs($id, $uid) {
        !$id && $id = I('request.id', 0, 'intval');
        !$uid && $uid = I('request.uid', 0, 'intval');
        if (!$id) $this->ajaxReturn(0, '请正确选择职位！');
        $where = array('id' => $id);
        $uid && $where['uid'] = $uid;
        if (!$reg = D('Jobs')->where($where)->find()) $reg = D('JobsTmp')->where($where)->find();
        if (!$reg) return false;
        return $reg;
    }

    /**
     * [_is_company 检测简历是否存在]
     * @return boolean [false || 职位信息(按需要添加字段)]
     */
    protected function _is_company($id, $uid) {
        !$id && $id = I('request.id', 0, 'intval');
        !$uid && $uid = I('request.uid', 0, 'intval');
        if (!$id) $this->ajaxReturn(0, '请正确选择企业！');
        if (!$reg = D('CompanyProfile')->where(array('id' => $id, 'uid' => $uid))->find()) return false;
        return $reg;
    }

    /**
     * [jobs_add 职位新增]
     */
    public function jobs_add() {
        if (false === $company = $this->_is_company(I('request.company_id', 0, 'intval'), I('request.uid', 0, 'intval'))) $this->ajaxReturn(0, '企业不存在！');
        // 判断是否需要完善信息
        $array = array("companyname", "nature", "trade", "district", "scale", "address", "contact", "email", "contents");
        foreach ($company as $key => $value) {
            if (in_array($key, $array) && empty($value)) {
                $this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
            }
        }
        $user = M('Members')->find($company['uid']);
        $setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);

        // 统计有效职位数
        $jobs_num = D('Jobs')->where(array('uid' => $user['uid']))->count();
        $jobs_num_tmp = D('JobsTmp')->where(array('uid' => $user['uid'], 'display' => 1))->count();
        if ($jobs_num + $jobs_num_tmp >= $setmeal['jobs_meanwhile']) {
            $this->ajaxReturn(0, '当前显示的职位已经达到最大限制，请升级服务套餐!');
        }
        // 保存 POST 数据
        // 插入职位信息
        $setsqlarr['setmeal_deadline'] = $setmeal['endtime'];
        $setsqlarr['deadline'] = $setsqlarr['setmeal_deadline'];
        $setsqlarr['setmeal_id'] = $setmeal['setmeal_id'];
        $setsqlarr['setmeal_name'] = $setmeal['setmeal_name'];
        $setsqlarr['uid'] = $user['uid'];
        $setsqlarr['company_id'] = $company['id'];
        $setsqlarr['company_addtime'] = $company['addtime'];
        $setsqlarr['company_audit'] = $company['audit'];
        C('apply.Sincerity') && $setsqlarr['famous'] = $company['famous'];
        $setsqlarr['audit'] = 2;
        $array = array('companyname', 'trade', 'trade_cn', 'scale', 'scale_cn', 'tpl', 'map_x', 'map_y', 'map_zoom');
        if ($setsqlarr['basis_contact'] = I('post.basis_contact', 0, 'intval')) {//与企业联系方式同步
            $array = array_merge($array, array('contact', 'telephone', 'landline_tel', 'address', 'email', 'contact_show', 'email_show', 'telephone_show', 'landline_tel_show'));
        } else {
            $setsqlarr['contact'] = I('post.contact', '', 'trim,badword');
            $setsqlarr['telephone'] = I('post.telephone', '', 'trim,badword');
            $setsqlarr['landline_tel'] = I('post.landline_tel', '', 'trim,badword');
            $setsqlarr['address'] = I('post.address', '', 'trim,badword');
            $setsqlarr['email'] = I('post.email', '', 'trim,badword');
            $setsqlarr['contact_show'] = I('post.contact_show', 1, 'intval');
            $setsqlarr['email_show'] = I('post.email_show', 1, 'intval');
            $setsqlarr['telephone_show'] = I('post.telephone_show', 1, 'intval');
            $setsqlarr['landline_tel_show'] = I('post.landline_tel_show', 1, 'intval');
        }
        foreach ($array as $val) {
            $setsqlarr[$val] = $company[$val];
        }
        $array = array('nature', 'topclass', 'category', 'subclass', 'amount', 'district', 'minwage', 'maxwage', 'negotiable', 'sex', 'education', 'experience', 'graduate', 'minage', 'maxage', 'notify', 'notify_mobile');
        foreach ($array as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        $jobcategory = I('post.jobcategory');
        $jobcategory_arr = explode(".", $jobcategory);
        $setsqlarr['topclass'] = $jobcategory_arr[0];
        $setsqlarr['category'] = $jobcategory_arr[1];
        $setsqlarr['subclass'] = $jobcategory_arr[2];
        $setsqlarr['jobs_name'] = I('post.jobs_name', '', 'trim,badword');
        $setsqlarr['tag'] = I('post.tag', '', 'trim,badword');// 标签
        $setsqlarr['contents'] = I('post.contents', '', 'trim,badword');
        $setsqlarr['department'] = I('post.department', '', 'trim,badword');

        $rst = D('Jobs')->add_jobs($setsqlarr, $user);
        if ($rst['state'] == 0) $this->ajaxReturn(0, $rst['error']);
        switch ($setsqlarr['audit']) {
            case 1:
                $audit_str = '审核通过';
                break;
            case 2:
                $audit_str = '';
                break;
            case 3:
                $audit_str = '审核未通过';
                break;
            default:
                $audit_str = '';
                break;
        }
        if ($audit_str) {
            $auditsqlarr['jobs_id'] = $rst['id'];
            $auditsqlarr['reason'] = '自动设置';
            $auditsqlarr['status'] = $audit_str;
            $auditsqlarr['addtime'] = time();
            $auditsqlarr['audit_man'] = '系统';
            M('AuditReason')->data($auditsqlarr)->add();
        }
        if (C('qscms_jobs_display') == 2) {
            baidu_submiturl(url_rewrite('QS_jobsshow', array('id' => $rst['id'])), 'addjob');
        }
        $this->ajaxReturn(1, '添加成功！', $rst['id']);
    }

    /**
     * [jobs_edit 职位修改]
     */
    public function jobs_edit() {
        if (false === $company = $this->_is_company(I('request.company_id', 0, 'intval'), I('request.uid', 0, 'intval'))) $this->ajaxReturn(0, '企业不存在！');
        // 判断是否需要完善信息
        $array = array("companyname", "nature", "trade", "district", "scale", "address", "contact", "email", "contents");
        foreach ($company as $key => $value) {
            if (in_array($key, $array) && empty($value)) {
                $this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
            }
        }
        $user = M('Members')->find($company['uid']);
        $setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);

        // 保存 POST 数据
        // 插入职位信息
        $jobs_info = '';
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $jobs_info = D('Jobs')->find($id);
            if (!$jobs_info && !$jobs_info = D('JobsTmp')->find($id)) $this->ajaxReturn(0, '职位不存在！');
        }
        $setsqlarr['setmeal_deadline'] = $setmeal['endtime'];
        $setsqlarr['deadline'] = $setsqlarr['setmeal_deadline'];
        $setsqlarr['setmeal_id'] = $setmeal['setmeal_id'];
        $setsqlarr['setmeal_name'] = $setmeal['setmeal_name'];
        $setsqlarr['uid'] = $user['uid'];
        $setsqlarr['company_id'] = $company['id'];
        $setsqlarr['company_addtime'] = $company['addtime'];
        $setsqlarr['company_audit'] = $company['audit'];
        C('apply.Sincerity') && $setsqlarr['famous'] = $company['famous'];
        if ($company['audit'] == 1) {
            if (C('qscms_audit_verifycom_editjob') == '-1') {
                if ($jobs_info['audit'] == 3) {
                    $setsqlarr['audit'] = 2;
                } else {
                    $setsqlarr['audit'] = $jobs_info['audit'];
                }
            } else {
                $setsqlarr['audit'] = C('qscms_audit_verifycom_editjob');
            }
        } else {
            if (C('qscms_audit_unexaminedcom_editjob') == '-1') {
                if ($jobs_info['audit'] == 3) {
                    $setsqlarr['audit'] = 2;
                } else {
                    $setsqlarr['audit'] = $jobs_info['audit'];
                }
            } else {
                $setsqlarr['audit'] = C('qscms_audit_unexaminedcom_editjob');
            }
        }
        $array = array('companyname', 'trade', 'trade_cn', 'scale', 'scale_cn', 'tpl', 'map_x', 'map_y', 'map_zoom');
        if ($setsqlarr['basis_contact'] = I('post.basis_contact', 0, 'intval')) {//与企业联系方式同步
            $array = array_merge($array, array('contact', 'telephone', 'landline_tel', 'address', 'email', 'contact_show', 'email_show', 'telephone_show', 'landline_tel_show'));
        } else {
            $setsqlarr['contact'] = I('post.contact', '', 'trim,badword');
            $setsqlarr['telephone'] = I('post.telephone', '', 'trim,badword');
            $setsqlarr['landline_tel'] = I('post.landline_tel', '', 'trim,badword');
            $setsqlarr['address'] = I('post.address', '', 'trim,badword');
            $setsqlarr['email'] = I('post.email', '', 'trim,badword');
            $setsqlarr['contact_show'] = I('post.contact_show', 1, 'intval');
            $setsqlarr['email_show'] = I('post.email_show', 1, 'intval');
            $setsqlarr['telephone_show'] = I('post.telephone_show', 1, 'intval');
            $setsqlarr['landline_tel_show'] = I('post.landline_tel_show', 1, 'intval');
        }
        foreach ($array as $val) {
            $setsqlarr[$val] = $company[$val];
        }
        $array = array('nature', 'topclass', 'category', 'subclass', 'amount', 'district', 'minwage', 'maxwage', 'negotiable', 'sex', 'education', 'experience', 'graduate', 'minage', 'maxage', 'notify', 'notify_mobile');
        foreach ($array as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        $jobcategory = I('post.jobcategory');
        $jobcategory_arr = explode(".", $jobcategory);
        $setsqlarr['topclass'] = $jobcategory_arr[0];
        $setsqlarr['category'] = $jobcategory_arr[1];
        $setsqlarr['subclass'] = $jobcategory_arr[2];
        $setsqlarr['jobs_name'] = I('post.jobs_name', '', 'trim,badword');
        $setsqlarr['tag'] = I('post.tag', '', 'trim,badword');// 标签
        $setsqlarr['contents'] = I('post.contents', '', 'trim,badword');
        $setsqlarr['department'] = I('post.department', '', 'trim,badword');

        $rst = D('Jobs')->edit_jobs($setsqlarr, $user);
        if ($rst['state'] == 0) $this->ajaxReturn(0, $rst['error']);
        if ($jobs_info) {
            if ($setsqlarr['audit'] != $jobs_info['audit']) {
                switch ($setsqlarr['audit']) {
                    case 1:
                        $audit_str = '审核通过';
                        break;
                    case 2:
                        $audit_str = '审核中';
                        break;
                    case 3:
                        $audit_str = '审核未通过';
                        break;
                    default:
                        $audit_str = '';
                        break;
                }
                if ($audit_str) {
                    $auditsqlarr['jobs_id'] = $jobs_info['id'];
                    $auditsqlarr['reason'] = '自动设置';
                    $auditsqlarr['status'] = $audit_str;
                    $auditsqlarr['addtime'] = time();
                    $auditsqlarr['audit_man'] = '系统';
                    M('AuditReason')->data($auditsqlarr)->add();
                }
            }
        }
        if (C('qscms_jobs_display') == 2) {
            baidu_submiturl(url_rewrite('QS_jobsshow', array('id' => $rst['id'])), 'addjob');
        }
        $this->ajaxReturn(1, '修改成功！');
    }

    /**
     * [jobs_delete 职位删除]
     */
    public function jobs_delete() {
        if (false === $jobs = $this->_is_jobs()) $this->ajaxReturn(0, '请选择职位信息！');
        $user = M('Members')->find($jobs['uid']);
        if (D('Jobs')->jobs_perform(array('yid' => $jobs['id'], 'perform_type' => 'delete', 'user' => $user))) {
            $this->ajaxReturn(1, "删除成功！");
        } else {
            $this->ajaxReturn(0, '删除失败！');
        }
    }

    /**
     * [jobs_admin_edit 职位后台修改]
     */
    public function jobs_admin_edit() {
        if (false === $jobs = $this->_is_jobs()) $this->ajaxReturn(0, '请选择职位信息！');
        $user = M('Members')->find($jobs['uid']);
        $data = I('post.');
        $data['negotiable'] = $data['negotiable'] ? 1 : 0;
        $data['contact_show'] = $data['contact_show'] ? 1 : 0;
        $data['email_show'] = $data['email_show'] ? 1 : 0;
        $data['telephone_show'] = $data['telephone_show'] ? 1 : 0;
        $data['landline_tel_show'] = $data['landline_tel_show'] ? 1 : 0;
        $data['landline_tel'] = $data['landline_tel_first'] . '-' . $data['landline_tel_next'] . '-' . $data['landline_tel_last'];
        $jobcategory = $data['jobcategory'];
        $jobcategory_arr = explode(".", $jobcategory);
        $data['topclass'] = $jobcategory_arr[0];
        $data['category'] = $jobcategory_arr[1];
        $data['subclass'] = $jobcategory_arr[2];
        if (D('Jobs')->admin_edit_jobs($data, $user)) {
            $this->ajaxReturn(1, '职位修改成功！');
        } else {
            $this->ajaxReturn(0, '职位修改失败！');
        }
    }

    /**
     * [jobs_admin_delete 职位删除]
     */
    public function jobs_admin_delete() {
        $id = I('request.id');
        if (!$id) $this->ajaxReturn(0, '请选择职位！');
        if ($n = D('Jobs')->admin_del_jobs(explode(',', $id))) {
            $this->ajaxReturn(1, "删除成功！", $n);
        } else {
            $this->ajaxReturn(0, '删除失败！');
        }
    }

    /**
     * [jobs_audit 职位删除]
     */
    public function jobs_audit() {
        $id = I('request.id');
        $uid = I('request.uid', 0, 'intval');
        if (!$id) $this->error('请选择职位');
        $id = explode(',', $id);
        $user = M('Admin')->find(1);
        $audit = I('post.audit', 0, 'intval');
        $reason = I('post.reason', '', 'trim');
        $result = D('Jobs')->admin_edit_jobs_audit($id, $uid, $audit, $reason, $user);
        if ($result) {
            D('Jobs')->admin_refresh_jobs($id);
            $this->ajaxReturn(1, "设置成功！");
        } else {
            $this->ajaxReturn(0, '设置失败！');
        }
    }

    /**
     * [company_edit 企业编辑]
     */
    public function company_edit() {
        if (false === $company_profile = $this->_is_company()) $this->ajaxReturn(0, '请选择企业！');
        $user = M('Members')->find($company_profile['uid']);
        $setsqlarr['id'] = $company_profile['id'];
        $setsqlarr['uid'] = $company_profile['uid'];
        $setsqlarr['companyname'] = $company_profile['audit'] == 1 ? $company_profile['companyname'] : I('post.companyname', 0, 'trim,badword');
        $setsqlarr['short_name'] = $company_profile['audit'] == 1 ? $company_profile['short_name'] : I('post.short_name', 0, 'trim,badword');
        // 判断企业名称是否重复
        if (C('qscms_company_repeat') == "0") {
            $info = M('CompanyProfile')->where(array('uid' => array('neq', $user['uid']), 'companyname' => $setsqlarr['companyname']))->getField('uid');
            if ($info) $this->ajaxReturn(0, "{$setsqlarr['companyname']}已经存在，同公司信息不能重复注册");
        }

        $data = array('nature', 'trade', 'scale');
        foreach ($data as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        $setsqlarr['district'] = I('post.district', 0, 'intval');
        $city = get_city_info($setsqlarr['district']);
        $setsqlarr['district'] = $city['district'];
        $setsqlarr['district_cn'] = $city['district_cn_all'];

        // 分类缓存
        $category = D('Category')->get_category_cache();
        $setsqlarr['nature_cn'] = $category['QS_company_type'][$setsqlarr['nature']];
        $setsqlarr['trade_cn'] = $category['QS_trade'][$setsqlarr['trade']];
        // $setsqlarr['street_cn']=$category['QS_street'][$setsqlarr['street']];
        $setsqlarr['scale_cn'] = $category['QS_scale'][$setsqlarr['scale']];
        // 字符串字段
        $setsqlarr['registered'] = I('post.registered', '', 'trim,badword');
        $setsqlarr['currency'] = I('post.currency', '', 'trim,badword');
        $setsqlarr['address'] = I('post.address', '', 'trim,badword');
        $setsqlarr['contact'] = I('post.contact', '', 'trim,badword');
        $setsqlarr['telephone'] = $user['mobile'] ? $user['mobile'] : I('post.telephone', '', 'trim,badword');
        $setsqlarr['email'] = I('post.email', '', 'trim,badword');
        $setsqlarr['website'] = I('post.website', '', 'trim,badword');
        $setsqlarr['contents'] = I('post.contents', '', 'trim,badword');
        $setsqlarr['short_desc']=I('post.short_desc','','trim,badword');
        $setsqlarr['contact_show'] = I('post.contact_show', 1, 'intval');
        $setsqlarr['telephone_show'] = I('post.telephone_show', 1, 'intval');
        $setsqlarr['landline_tel_show'] = I('post.landline_tel_show', 1, 'intval');
        $setsqlarr['email_show'] = I('post.email_show', 1, 'intval');
        $setsqlarr['qq'] = I('post.qq', 0, 'intval');
        $setsqlarr['map_x'] = I('post.map_x', 0, 'trim,badword');
        !$setsqlarr['map_x'] && $setsqlarr['map_x'] = 0;
        $setsqlarr['map_y'] = I('post.map_y', 0, 'trim,badword');
        !$setsqlarr['map_y'] && $setsqlarr['map_y'] = 0;
        $setsqlarr['map_zoom'] = I('post.map_zoom', 0, 'intval');

        //座机
        $landline_tel_first = I('post.landline_tel_first', 0, 'trim,badword');
        $landline_tel_next = I('post.landline_tel_next', 0, 'trim,badword');
        $landline_tel_last = I('post.landline_tel_last', 0, 'trim,badword');
        $setsqlarr['landline_tel'] = $landline_tel_first . '-' . $landline_tel_next . ($landline_tel_last ? ('-' . $landline_tel_last) : '');
        $posttag = I('post.tag', '', 'trim,badword');

        if ($posttag) {
            $tagArr = explode(",", $posttag);
            $r_arr = array();
            foreach ($tagArr as $key => $value) {
                $r_arr[] = $value . '|' . $category['QS_jobtag'][$value];
            }
            if (!empty($r_arr)) {
                $setsqlarr['tag'] = implode(",", $r_arr);
            } else {
                $setsqlarr['tag'] = '';
            }
        }

        if ($company_profile['contents']) {
            $setsqlarr['id'] = $company_profile['id'];
            C('qscms_audit_edit_com') <> "-1" ? $setsqlarr['audit'] = C('qscms_audit_edit_com') : $setsqlarr['audit'] = $company_profile['audit'];
        } else {
            $setsqlarr['audit'] = 0;
        }
        $setsqlarr['sync'] = I('post.sync', 0, 'intval');
        // 插入数据
        $rst = D('CompanyProfile')->add_company_profile($setsqlarr, $user);
        $rst['state'] == 0 && $this->ajaxReturn(0, $rst['error']);
        $r = D('TaskLog')->do_task($user, 'done_profile');
        if ($setsqlarr['map_x'] && $setsqlarr['map_y'] && $setsqlarr['map_zoom']) {
            D('TaskLog')->do_task($user, 'set_map');
        }
        if ($setsqlarr['audit'] != $company_profile['audit']) {
            switch ($setsqlarr['audit']) {
                case 0:
                    $audit_str = '未认证';
                    break;
                case 1:
                    $audit_str = '认证通过';
                    break;
                case 2:
                    $audit_str = '认证中';
                    break;
                case 3:
                    $audit_str = '认证未通过';
                    break;
                default:
                    $audit_str = '';
                    break;
            }
            if ($audit_str) {
                $auditsqlarr['company_id'] = $company_profile['id'];
                $auditsqlarr['reason'] = '自动设置';
                $auditsqlarr['status'] = $audit_str;
                $auditsqlarr['addtime'] = time();
                $auditsqlarr['audit_man'] = '系统';
                M('AuditReason')->data($auditsqlarr)->add();
            }
        }
        if ($rst['id']) {
            $success = "添加成功！";
        } else {
            $success = "保存成功！";
        }
        $this->ajaxReturn(1, $success);
    }

    /**
     * [cpmpany_resume_apply 企业收到的简历]
     */
    public function cpmpany_resume_apply() {
        if (false === $company = $this->_is_company()) $this->ajaxReturn(0, '请选择企业！');
        $_GET['page'] = I('request.page', 1, 'intval');
        $where['uid'] = $company['uid'];
        $jids = array();
        $jobs_list = M('Jobs')->where($where)->getField('id', true);
        $jobs_list && $jids = $jobs_list;
        $jobs_list_tmp = M('JobsTmp')->where($where)->getField('id', true);
        $jobs_list_tmp && $jids = $jids + $jobs_list_tmp;
        $apply_list = D('PersonalJobsApply')->get_apply_jobs(array('jobs_id' => array('in', $jids), 'company_uid' => $company['uid']), 1);
        $this->ajaxReturn(1, '企业收到的简历', $apply_list);
    }

    /**
     * [resume_apply_delete 删除收到的简历]
     */
    public function resume_apply_delete() {
        $id = I('request.id', 0, 'intval');
        !$id && $this->error("你没有选择项目！");
        $uid = I('request.uid', 0, 'intval');
        !$uid && $this->ajaxReturn(0, '请选择会员！');
        $user = M('Members')->find($uid);
        $reg = D('PersonalJobsApply')->del_jobs_apply($id, $user);
        if ($reg['state'] == 1) {
            $this->ajaxReturn(1, "删除成功！");
        } else {
            $this->ajaxReturn(0, "删除失败！");
        }
    }

    /**
     * [cpmpany_resume_download_list 企业下载的简历]
     */
    public function cpmpany_resume_download_list() {
        if (false === $company = $this->_is_company()) $this->ajaxReturn(0, '请选择企业！');
        $_GET['page'] = I('request.page', 1, 'intval');
        $where['company_uid'] = $company['uid'];
        $down_list = D('CompanyDownResume')->get_down_resume($where, $state);
        $this->ajaxReturn(1, '企业下载的简历', $down_list);
    }

    /**
     * [cpmpany_resume_download 企业下载简历]
     */
    public function cpmpany_resume_download() {
        $id = I('request.id');
        if (!$id) $this->ajaxReturn(0, '请选择简历！');
        $uid = I('request.uid', 0, 'intval');
        !$uid && $this->ajaxReturn(0, '请选择企业！');
        $user = M('Members')->find($uid);
        $id = is_array($id) ? $id : explode(",", $id);
        //如果是积分兑换下载或者直接免费下载
        $addarr['rid'] = $id;
        $r = D('CompanyDownResume')->add_down_resume($addarr, $user);
        if ($r['state'] == 1) {
            $this->ajaxReturn(1, '下载成功！');
        } else {
            $this->ajaxReturn(0, $r['msg']);
        }
    }

    /**
     * [company_admin_add 企业新增]
     */
    public function company_admin_add() {
        $com_setarr['audit'] = 0;
        $com_setarr['companyname'] = I('post.companyname', '', 'trim,badword');
        // 判断企业名称是否重复
        if (C('qscms_company_repeat') == "0") {
            if ($info = M('CompanyProfile')->where(array('companyname' => $com_setarr['companyname']))->getField('uid')) {
                IS_AJAX && $this->ajaxReturn(0, "{$com_setarr['companyname']}已经存在，同公司信息不能重复注册");
                $this->error("{$com_setarr['companyname']}已经存在，同公司信息不能重复注册");
            }
        }
        $data = array('nature', 'trade', 'scale');
        foreach ($data as $val) {
            $com_setarr[$val] = I('post.' . $val, 0, 'intval');
        }
        $city = get_city_info(I('post.district', 0, 'intval'));
        $com_setarr['district'] = $data['district'] = $city['district'];
        $com_setarr['district_cn'] = $data['district_cn'] = $city['district_cn_all'];
        // 分类缓存
        $category = D('Category')->get_category_cache();
        $com_setarr['nature_cn'] = $category['QS_company_type'][$com_setarr['nature']];
        $com_setarr['trade_cn'] = $category['QS_trade'][$com_setarr['trade']];
        $com_setarr['scale_cn'] = $category['QS_scale'][$com_setarr['scale']];
        // 字符串字段
        $com_setarr['short_name'] = I('post.short_name', '', 'trim,badword');
        $com_setarr['registered'] = I('post.registered', '', 'trim,badword');
        $com_setarr['currency'] = I('post.currency', '', 'trim,badword');
        $com_setarr['address'] = I('post.address', '', 'trim,badword');
        $com_setarr['contact'] = I('post.contact', '', 'trim,badword');
        $com_setarr['telephone'] = I('post.telephone', '', 'trim,badword');
        $com_setarr['email'] = I('post.email', '', 'trim,badword');
        $com_setarr['website'] = I('post.website', '', 'trim,badword');
        $com_setarr['short_desc'] = I('post.short_desc', '', 'trim,badword');
        $com_setarr['contents'] = I('post.contents', '', 'trim,badword');
        $com_setarr['contact_show'] = I('post.contact_show', 0, 'intval');
        $com_setarr['telephone_show'] = I('post.telephone_show', 0, 'intval');
        $com_setarr['landline_tel_show'] = I('post.landline_tel_show', 0, 'intval');
        $com_setarr['email_show'] = I('post.email_show', 0, 'intval');
        $com_setarr['contact_show'] = $com_setarr['contact_show'] ? 1 : 0;
        $com_setarr['email_show'] = $com_setarr['email_show'] ? 1 : 0;
        $com_setarr['telephone_show'] = $com_setarr['telephone_show'] ? 1 : 0;
        $com_setarr['landline_tel_show'] = $com_setarr['landline_tel_show'] ? 1 : 0;
        $com_setarr['qq'] = I('post.qq', 0, 'intval');
        $com_setarr['audit'] = I('post.audit', 0, 'intval');
        $landline_tel_first = I('post.landline_tel_first', 0, 'trim,badword');
        $landline_tel_next = I('post.landline_tel_next', 0, 'trim,badword');
        $landline_tel_last = I('post.landline_tel_last', 0, 'trim,badword');
        $com_setarr['landline_tel'] = $landline_tel_first . '-' . $landline_tel_next . ($landline_tel_last ? ('-' . $landline_tel_last) : '');
        $com_setarr['landline_tel'] = ltrim($com_setarr['landline_tel'], '-');
        if ($com_setarr['telephone'] == '' && $com_setarr['landline_tel'] == '') {
            $this->ajaxReturn(0, '固话或手机号必填一项！');
        }
        $posttag = I('post.tag', '', 'trim,badword');
        if ($posttag) {
            $tagArr = explode(",", $posttag);
            $r_arr = array();
            foreach ($tagArr as $key => $value) {
                $r_arr[] = $value . '|' . $category['QS_jobtag'][$value];
            }
            if (!empty($r_arr)) {
                $com_setarr['tag'] = implode(",", $r_arr);
            } else {
                $com_setarr['tag'] = '';
            }
        }
        $company_mod = D('CompanyProfile');
        if (false === $company_mod->create($com_setarr)) {
            $this->ajaxReturn(0, $company_mod->getError());
        }

        $data = I('post.');
        $data['mobile'] = $com_setarr['telephone'] ?: '';
        if (fieldRegex($data['username'], 'number')) $this->ajaxReturn(0, '用户名不能是纯数字！');
        $user_mod = D('Members');
        if (false === $user = $user_mod->create($data)) $this->ajaxReturn(0, $user_mod->getError());
        $user_mod->password = $user_mod->make_md5_pwd($user['password'], $user['pwd_hash']);
        if (!$user['uid'] = $user_mod->add()) $this->ajaxReturn(0, '企业会员添加失败！');
        $company_mod->uid = $user['uid'];
        if (!$insert_company_id = $company_mod->add()) $this->ajaxReturn(0, '企业添加失败！');
        $user_mod->user_register($user);
        switch ($com_setarr['audit']) {
            case 1:
                $audit_str = '认证通过';
                break;
            case 2:
                $audit_str = '认证中';
                break;
            case 3:
                $audit_str = '认证未通过';
                break;
            default:
                $audit_str = '';
                break;
        }
        if ($audit_str) {
            $auditsqlarr['company_id'] = $insert_company_id;
            $auditsqlarr['reason'] = '自动设置';
            $auditsqlarr['status'] = $audit_str;
            $auditsqlarr['addtime'] = time();
            $auditsqlarr['audit_man'] = '系统';
            M('AuditReason')->data($auditsqlarr)->add();
        }
        $this->ajaxReturn(1, '企业添加成功！');
    }

    /**
     * [company_admin_edit 企业编辑]
     */
    public function company_admin_edit() {
        $setsqlarr['id'] = I('post.id', 0, 'intval');
        $setsqlarr['uid'] = I('post.uid', 0, 'intval');
        $user = M('Members')->find($setsqlarr['uid']);
        // 判断企业名称是否重复
        if (C('qscms_company_repeat') == "0") {
            $info = M('CompanyProfile')->where(array('uid' => array('neq', $setsqlarr['uid']), 'companyname' => $setsqlarr['companyname']))->getField('uid');
            if ($info) $this->ajaxReturn(0, "{$setsqlarr['companyname']}已经存在，同公司信息不能重复注册");
        }

        $data = array('nature', 'trade', 'scale');
        foreach ($data as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        $setsqlarr['district'] = I('post.district', 0, 'intval');
        $city = get_city_info($setsqlarr['district']);
        $setsqlarr['district'] = $city['district'];
        $setsqlarr['district_cn'] = $city['district_cn_all'];
        // 分类缓存
        $category = D('Category')->get_category_cache();
        $setsqlarr['nature_cn'] = $category['QS_company_type'][$setsqlarr['nature']];
        $setsqlarr['trade_cn'] = $category['QS_trade'][$setsqlarr['trade']];
        $setsqlarr['scale_cn'] = $category['QS_scale'][$setsqlarr['scale']];
        // 字符串字段
        $setsqlarr['companyname'] = I('post.companyname', '', 'trim,badword');
        $setsqlarr['short_name'] = I('post.short_name', '', 'trim,badword');
        $setsqlarr['registered'] = I('post.registered', '', 'trim,badword');
        $setsqlarr['currency'] = I('post.currency', '', 'trim,badword');
        $setsqlarr['address'] = I('post.address', '', 'trim,badword');
        $setsqlarr['contact'] = I('post.contact', '', 'trim,badword');
        $setsqlarr['telephone'] = $user['mobile'] ? $user['mobile'] : I('post.telephone', '', 'trim,badword');
        $setsqlarr['email'] = I('post.email', '', 'trim,badword');
        $setsqlarr['website'] = I('post.website', '', 'trim,badword');
        $setsqlarr['short_desc'] = I('post.short_desc', '', 'trim,badword');
        $setsqlarr['contents'] = I('post.contents', '', 'trim,badword');
        $setsqlarr['contact_show'] = I('post.contact_show', 0, 'intval');
        $setsqlarr['telephone_show'] = I('post.telephone_show', 0, 'intval');
        $setsqlarr['landline_tel_show'] = I('post.landline_tel_show', 0, 'intval');
        $setsqlarr['email_show'] = I('post.email_show', 0, 'intval');
        $setsqlarr['contact_show'] = $setsqlarr['contact_show'] ? 1 : 0;
        $setsqlarr['email_show'] = $setsqlarr['email_show'] ? 1 : 0;
        $setsqlarr['telephone_show'] = $setsqlarr['telephone_show'] ? 1 : 0;
        $setsqlarr['landline_tel_show'] = $setsqlarr['landline_tel_show'] ? 1 : 0;
        $setsqlarr['qq'] = I('post.qq', 0, 'intval');
        $setsqlarr['audit'] = I('post.audit', 0, 'intval');

        //座机
        $landline_tel_first = I('post.landline_tel_first', 0, 'trim,badword');
        $landline_tel_next = I('post.landline_tel_next', 0, 'trim,badword');
        $landline_tel_last = I('post.landline_tel_last', 0, 'trim,badword');
        $setsqlarr['landline_tel'] = $landline_tel_first . '-' . $landline_tel_next . ($landline_tel_last ? ('-' . $landline_tel_last) : '');
        $setsqlarr['landline_tel'] = ltrim($setsqlarr['landline_tel'], '-');
        if ($setsqlarr['telephone'] == '' && $setsqlarr['landline_tel'] == '') {
            $this->ajaxReturn(0, '固话或手机号必填一项！');
        }
        $posttag = I('post.tag', '', 'trim,badword');
        if ($posttag) {
            $tagArr = explode(",", $posttag);
            $r_arr = array();
            foreach ($tagArr as $key => $value) {
                $r_arr[] = $value . '|' . $category['QS_jobtag'][$value];
            }
            if (!empty($r_arr)) {
                $setsqlarr['tag'] = implode(",", $r_arr);
            } else {
                $setsqlarr['tag'] = '';
            }
        }
        // 插入数据
        $rst = D('CompanyProfile')->admin_edit_company_profile($setsqlarr, $user, $company_profile);
        $rst['state'] == 0 && $this->ajaxReturn(0, $rst['error']);
        $this->ajaxReturn(1, '保存成功！');
    }

    /**
     * [company_admin_delete 企业删除]
     */
    public function company_admin_delete() {
        $id = I('request.id', '', 'trim');
        if (!$id) $this->ajaxReturn(0, '你没有选择企业！');
        $id = explode(',', $id);
        if (false === D('CompanyProfile')->admin_delete_company($id) || false === D('Members')->delete_member($id)) $this->ajaxReturn(0, '删除企业资料失败！');
        if (false === D('Jobs')->admin_delete_jobs_for_uid($id)) $this->ajaxReturn(0, '删除职位失败！');
        $this->ajaxReturn(1, '删除成功！');
    }

    /**
     * [company_admin_audit 企业认证]
     */
    public function company_admin_audit() {
        $id = I('request.id');
        if (!$id) $this->ajaxReturn(0, '请选择企业');
        $user = M('Admin')->where(array('role_id' => 1))->find();
        $id = explode(',', $id);
        $audit = I('post.audit', 0, 'intval');
        $pms_notice = I('post.pms_notice', 0, 'intval');
        $reason = I('post.reason', '', 'trim');
        $result = D('CompanyProfile')->admin_edit_company_audit($id, $audit, $reason, $user);
        if ($result) {
            $this->ajaxReturn(1, "设置成功！");
        } else {
            $this->ajaxReturn(0, '设置失败！');
        }
    }

    /**
     * [company_admin_refresh 企业认证]
     */
    public function company_admin_refresh() {
        $id = I('request.id');
        if (!$id) $this->ajaxReturn(0, '你没有选择企业！');
        $id = explode(',', $id);
        if (!I('post.refresh_jobs', 0)) {
            $refresh_jobs = false;
        } else {
            $refresh_jobs = true;
        }
        if ($n = D('CompanyProfile')->admin_refresh_company($id, $refresh_jobs)) {
            $this->ajaxReturn(1, "刷新成功！", $n);
        } else {
            $this->ajaxReturn(0, '刷新失败！');
        }
    }

    /**
     * [user_setmeal 读取会员套餐]
     */
    public function user_setmeal() {
        $uid = I('request.uid', 0, 'intval');
        !$uid && $this->ajaxReturn(0, '请选择会员！');
        $this->ajaxReturn(1, '会员套餐获取成功！', D('MembersSetmeal')->get_user_setmeal($uid));
    }
    /**
     * [get_weixin_token 获取微信access_token]
     */
    public function get_weixin_token(){
        $token = \Common\qscmslib\weixin::get_access_token();
        !$token && $this->ajaxReturn(0,'微信access_token获取失败，请检查配置参数！');
        $data['access_token'] = encrypt($token,C('PWDHASH'));
        $this->ajaxReturn(1,'微信access_token获取成功！',$data);
    }
}

?>