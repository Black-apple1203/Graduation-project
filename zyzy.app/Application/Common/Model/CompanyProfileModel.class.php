<?php
namespace Common\Model;

use Think\Model;

class CompanyProfileModel extends Model {
    protected $_user = array();
    protected $_validate = array(
        array('uid,companyname,nature,nature_cn,trade,trade_cn,scale,scale_cn,district,contact,address,contents,certificate_img', 'identicalNull', '', 0, 'callback'),
        array('uid,nature,trade,scale,', 'identicalEnum', '', 0, 'callback'),
        array('contents', '0,4000', '{%company_profile_contents_length_error}', 2, 'length'),
        array('telephone,landline_tel', 'requireone', '{%company_profile_telephone_requireone}', 1, 'function', 1),
        array('telephone', 'mobile', '{%company_profile_format_error_telephone}', 2),
        //array('landline_tel','tel','{%company_profile_format_error_landline_tel}',2),
        array('website', 'url', '{%members_format_error_website}', 2),
        //array('qq','qq','{%members_format_error_qq}',2),
        array('email', 'email', '{%company_profile_format_error_email}', 2),
	array('companyname','2,50','{%company_profile_companyname_length_error}',0,'length'),
        //array('telephone','_repetition_mobile','{%company_profile_repetition_mobile}',2,'callback'),
        //array('email','_repetition_email','{%company_profile_repetition_email}',2,'callback'),
        array('companyname', '_repetition_companyname', '{%company_profile_repetition_companyname}', 2, 'callback', 1),
    );
    protected $_auto = array(
        array('map_open', 0),
        array('addtime', 'time', 1, 'function'),
        array('refreshtime', 'time', 1, 'function'),
        array('click', 1),
        array('user_status', 1),
        array('contact_show', 1),
        array('telephone_show', 1),
        array('landline_tel_show', 1),
        array('email_show', 1),
        array('resume_processing', 100),
        array('wzp_tpl', 0),
        array('robot', 0),
        array('map_x', 0),
        array('map_y', 0),
        array('order_paid', 0),
    );

    protected function _repetition_companyname($data) {
        if (C('qscms_company_repeat') == "0") {
            $companyname = M('CompanyProfile')->where(array('companyname' => $data))->find();
            if ($companyname) return false;
        }
        return true;

    }

    protected function _repetition_email($data) {
        $uid = M('Members')->where(array('email' => $data))->getfield('uid');
        if ($uid && $uid != $this->_user['uid']) return false;
        return true;
    }

    protected function _repetition_mobile($data) {
        $uid = M('Members')->where(array('mobile' => $data))->getfield('uid');
        if ($uid && $uid != $this->_user['uid']) return false;
        return true;
    }

    public function admin_edit_company_profile($data, $user, $company_profile) {
        $this->_user = $user;
        if (false === $this->create($data)) {
            return array('state' => 0, 'error' => $this->getError());
        } else {
            if (false === $num = $this->save()) {
                return array('state' => 0, 'error' => '数据添加失败！');
            }
            $im = new \Common\qscmslib\im();
            $im->refresh($data['uid']);
            $setsqlarr['telephone'] = $data['telephone'];
            $setsqlarr['email'] = $data['email'];
            $setsqlarr && D('Members')->update_user_info($setsqlarr, $user);
        }
        if (false !== $num && $data['id']) //修改信息
        {
            $jobarr['companyname'] = $data['companyname'];
            $jobarr['trade'] = $data['trade'];
            $jobarr['trade_cn'] = $data['trade_cn'];
            $jobarr['scale'] = $data['scale'];
            $jobarr['scale_cn'] = $data['scale_cn'];
            $r = D('Jobs')->jobs_setfield(array('uid' => $data['uid']), $jobarr);
            if (!$r['state']) array('state' => 0, 'error' => '修改公司名称出错！');
            if (C('apply.Jobfair') && false === M('JobfairExhibitors')->where(array('uid' => $data['uid']))->setField('companyname', $data['companyname'])) return array('state' => 0, 'error' => '修改公司名称出错！');
        }
        return array('state' => 1, 'id' => $insert_id, 'num' => $num);
    }

    /*
        添加，保存企业信息
        @data post 数据
        其中 data['id'] 存在 为修改保存 否则就是添加
    */
    public function add_company_profile($data, $user) {
        $this->_user = $user;
        // 替换原名称
        $company = $this->field('id,companyname')->where(array('uid' => $user['uid']))->find();
        if ($company) {
            $data['companyname'] = $company['companyname'];
        }
        if (false === $this->create($data)) {
            return array('state' => 0, 'error' => $this->getError());
        } else {
            if ($company['id']) {
                if (false === $num = $this->save()) {
                    return array('state' => 0, 'error' => '数据添加失败！');
                }
                $setsqlarr['telephone'] = $data['telephone'];
                $setsqlarr['email'] = $data['email'];
                $setsqlarr && D('Members')->update_user_info($setsqlarr, $user);
            } else {
                //注册帐号后已经初始化了企业套餐，当时没有企业资料，需要同步套餐信息
                if ($setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid'])) {
                    $this->setmeal_id = $setmeal['setmeal_id'];
                    $this->setmeal_name = $setmeal['setmeal_name'];
                }
                if (false === $insert_id = $this->add()) {
                    return array('state' => 0, 'error' => '数据添加失败！');
                }
                $im = new \Common\qscmslib\im();
                $im->refresh($user['uid']);
            }
        }
        // 插入数据后相应操作
        /*	if ($insert_id) //添加信息
            {
                baidu_submiturl(url_rewrite('QS_companyshow',array('id'=>$insertid)),'addcompany');
            }*/
        if (false !== $num && $data['id']) //修改信息
        {
            $jobarr['companyname'] = $data['companyname'];
            $jobarr['trade'] = $data['trade'];
            $jobarr['trade_cn'] = $data['trade_cn'];
            $jobarr['scale'] = $data['scale'];
            $jobarr['scale_cn'] = $data['scale_cn'];
            $jobarr['map_x'] = $data['map_x'];
            $jobarr['map_y'] = $data['map_y'];
            $jobarr['map_zoom'] = $data['map_zoom'];
            $r = D('Jobs')->jobs_setfield(array('uid' => $data['uid']), $jobarr);
            if (!$r['state']) array('state' => 0, 'error' => '修改公司名称出错！');
            if (C('apply.Jobfair') && false === M('JobfairExhibitors')->where(array('uid' => $data['uid']))->setField('companyname', $data['companyname'])) return array('state' => 0, 'error' => '修改公司名称出错！');

            //同步到职位联系方式
            if (intval($data['sync']) == 1) {
                if ($jobsid_arr = M('Jobs')->where(array('uid' => $data['uid']))->getField('id', true)) {
                    $contact['telephone'] = $data['telephone'];
                    $contact['email'] = $data['email'];
                    $contact['contact'] = $data['contact'];
                    $contact['qq'] = $data['qq'];
                    $contact['landline_tel'] = $data['landline_tel'];
                    $contact['address'] = $data['address'];
                    $contact['contact_show'] = $data['contact_show'];
                    $contact['telephone_show'] = $data['telephone_show'];
                    $contact['email_show'] = $data['email_show'];
                    $contact['landline_tel_show'] = $data['landline_tel_show'];
                    M('JobsContact')->where(array('pid' => array('in', $jobsid_arr)))->save($contact);
                }
            }
            $log_value = '修改企业资料';
        }
        //写入会员日志
        $user = D('Members')->get_user_one(array('uid' => $data['uid']));
        write_members_log($user, '', '修改企业资料');
        return array('state' => 1, 'id' => $insert_id, 'num' => $num);
    }

    /*
        上传营业执照
    */
    public function add_certificate_img($data, $user) {
        D('Jobs')->jobs_setfield(array('uid' => $user['uid']), array('company_audit' => 2));
        $rst = $this->where(array('uid' => $user['uid']))->save($data);
        if ($rst === false) return array('state' => 0, 'error' => '更新营业执照数据错误！');
        //写入会员日志
        write_members_log($user, '', '上传营业执照');
        return array('state' => 1);
    }

    /**
     * 格式化企业列表
     */
    public function admin_format_company_list($list) {
        $uid_arr = $jobs_count_list = $report = array();
        foreach ($list as $key => $value) {
            $uid_arr[] = $value['uid'];
            $arr = $value;
            $arr['company_url'] = url_rewrite('QS_companyshow', array('id' => $value['id']));
            $list[$key] = $arr;
        }
        if (!empty($uid_arr)) {
            $jobs_count_list = D('Jobs')->field('count(*) as num,uid')->where(array('uid' => array('in', $uid_arr)))->group('uid')->index('uid')->select();
            if (C('apply.Report')) {
                $report = D('CompanyReport')->where(array('uid' => array('in', $uid_arr)))->index('uid')->select();
            } else {
                $report = array();
            }
        }
        foreach ($list as $key => $value) {
            $list[$key]['jobs_count'] = (isset($jobs_count_list[$value['uid']]) && intval($jobs_count_list[$value['uid']]) > 0) ? $jobs_count_list[$value['uid']]['num'] : 0;
            $list[$key]['report_open'] = isset($report[$value['uid']]) ? 1 : 0;
            $list[$key]['report'] = isset($report[$value['uid']]) ? $report[$value['uid']] : array();
        }
        return $list;
    }

    /**
     * 后台删除企业
     */
    public function admin_delete_company($uid) {
        !is_array($uid) && $uid = array($uid);
        $sqlin = implode(",", $uid);
        if (fieldRegex($sqlin, 'in')) {
            $result = $this->where(array('uid' => array('in', $sqlin)))->select();
            foreach ($result as $key => $value) {
                @unlink(C('qscms_attach_path') . "certificate_img/" . $value['certificate_img']);
            }
            $this->where(array('uid' => array('in', $sqlin)))->delete();
            M('CompanyImg')->where(array('uid' => array('in', $sqlin)))->delete();
            M('ResumeImg')->where(array('uid' => array('in', $sqlin)))->delete();
            $this->where(array('uid' => array('in', $sqlin)))->delete();
            return true;
        }
        return false;
    }

    /**
     * 审核企业
     */
    public function admin_edit_company_audit($uid, $audit, $reason, $audit_man = array()) {
        if (!is_array($uid)) $uid = array($uid);
        $sqlin = implode(",", $uid);
        if (fieldRegex($sqlin, 'in')) {
            $comlist = $this->where(array('uid' => array('in', $sqlin)))->select();
            if (false === $this->where(array('uid' => array('in', $sqlin)))->setField('audit', $audit)) return false;
            if ($audit == 1) {
                foreach ($comlist as $key => $val) {
                    baidu_submiturl(url_rewrite('QS_companyshow', array('id' => $val['id'])), 'addcompany');
                }
            }
            $r = D('Jobs')->jobs_setfield(array('uid' => array('in', $sqlin)), array('company_audit' => $audit));
            if (!$r['state']) return false;
            $reasona = $reason == '' ? '无' : $reason;
            foreach ($comlist as $list) {
                write_members_log(array('uid' => $list['uid'], 'utype' => 1, 'username' => ''), 'company_audit', "将企业uid为" . $list['uid'] . "的企业的认证状态修改为" . ($audit == 1 ? '认证通过' : ($audit == 2 ? '等待认证' : '认证未通过')) . '；备注：' . $reasona, false, array(), $audit_man['id'], $audit_man['username']);
                $auditsqlarr['company_id'] = $list['id'];
                $auditsqlarr['reason'] = $reasona;
                $auditsqlarr['status'] = $audit == 1 ? '认证通过' : ($audit == 2 ? '等待认证' : '认证未通过');
                $auditsqlarr['addtime'] = time();
                $auditsqlarr['audit_man'] = $audit_man['username'] ? $audit_man['username'] : '未知';
                M('AuditReason')->data($auditsqlarr)->add();
            }
            //站内信
            if ($audit == '1') {
                $note = '成功通过网站管理员审核!';
            } elseif ($audit == '2') {
                $note = '正在审核中!';
            } else {
                $note = '未通过网站管理员审核！';
            }
            foreach ($comlist as $list) {
                $user_info = D('Members')->get_user_one(array('uid' => $list['uid']));
                $pms_message = "您的公司营业执照" . $note . '其他说明：' . $reasona;
                D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $pms_message,1);
            }
            //sms
            $sms = D('SmsConfig')->get_cache();
            if ($audit == "1" && $sms['set_licenseallow'] == "1") {
                $mobilearray = array();
                foreach ($comlist as $key => $value) {
                    $usermobile = D('Members')->get_user_one(array('uid' => $value['uid']));
                    if (!in_array($value['mobile'], $mobilearray)) {
                        $mobilearray[] = $usermobile['mobile'];
                    }
                }
                if (!empty($mobilearray)) {
                    $mobilestr = implode(",", $mobilearray);
                    D('Sms')->sendSms('notice', array('mobile' => $mobilestr, 'tpl' => 'set_licenseallow'));
                }
            }
            //sms
            if ($audit == "3" && $sms['set_licensenotallow'] == "1")//认证未通过
            {
                $mobilearray = array();
                foreach ($comlist as $key => $value) {
                    $usermobile = D('Members')->get_user_one(array('uid' => $value['uid']));
                    if (!in_array($value['mobile'], $mobilearray)) {
                        $mobilearray[] = $usermobile['mobile'];
                    }
                }
                if (!empty($mobilearray)) {
                    $mobilestr = implode(",", $mobilearray);
                    D('Sms')->sendSms('notice', array('mobile' => $mobilestr, 'tpl' => 'set_licensenotallow'));
                }
            }
            //微信
            if (C('apply.Weixin')) {
                foreach ($comlist as $key => $value) {
                    D('Weixin/TplMsg')->set_licenseallow($value['uid'], $audit == 1 ? '审核通过' : '审核未通过', $reasona);
                }
            }
            if ($audit == '1') {
                $userinfo_arr = D('Members')->where(array('uid' => array('in', $uid)))->select();
                foreach ($userinfo_arr as $key => $value) {
                    D('TaskLog')->do_task($value, 'license_audit');
                }
            }
            return true;
        }
        return false;
    }
	/**
     * 删除营业执照
     */
    public function del_oauth($id,$user) {
		$update['certificate_img']='';
		$update['audit']=0;
		$return = $this->where(array('id' => $id))->setField($update);
		if($return !== false){
			write_members_log(array('uid' => C('visitor.uid'), 'utype' => 1, 'username' => C('visitor.username')), '', '删除营业执照', false, array(), C('visitor.id'), C('visitor.username'));
			return array('state' => 1);
		}
    }
    /**
     * 刷新企业
     */
    public function admin_refresh_company($uid, $refresjobs = false) {
        $return = 0;
        if (!is_array($uid)) $uid = array($uid);
        $sqlin = implode(",", $uid);
        $time = time();
        if (fieldRegex($sqlin, 'in')) {
            $return = $this->where(array('uid' => array('in', $sqlin)))->setField('refreshtime', $time);
            if (false === $return) return false;
            if ($refresjobs) {
                $return = $return + D('Jobs')->admin_refresh_jobs_by_uid($uid);
            }
            foreach ($uid as $vo) {
                write_members_log(array('uid' => $vo, 'utype' => 1, 'username' => ''), '', '刷新企业', false, array(), C('visitor.id'), C('visitor.username'));
            }
        }
        return $return;
    }
    /**
     * 转移企业
     */
    public function company_migration($from_uid,$to_uid){
        //企业信息 company_profile
        D('CompanyProfile')->where(array('uid'=>$to_uid))->delete();
        $to_userinfo = D('Members')->get_user_one(array('uid' => $to_uid));
        D('CompanyProfile')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid,'telephone'=>$to_userinfo['mobile']));

        //下载简历记录  company_down_resume
        D('CompanyDownResume')->where(array('company_uid'=>$from_uid))->save(array('company_uid'=>$to_uid));

        //收藏简历  company_favorites
        D('CompanyFavorites')->where(array('company_uid'=>$from_uid))->save(array('company_uid'=>$to_uid));

        //企业图片  company_img
        D('CompanyImg')->where(array('company_uid'=>$from_uid))->save(array('company_uid'=>$to_uid));

        //邀请面试  company_interview
        D('CompanyInterview')->where(array('company_uid'=>$from_uid))->save(array('company_uid'=>$to_uid));

        //实地认证 company_report
        if(C('apply.Report')){
            D('CompanyReport')->where(array('uid'=>$to_uid))->delete();
            D('CompanyReport')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        }
        //统计  company_statistics
        D('CompanyStatistics')->where(array('uid'=>$to_uid))->delete();
        D('CompanyStatistics')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));

        //模板  company_tpl
        D('CompanyTpl')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));

        //招聘会参会企业  jobfair_exhibitors   （需要检测是否安装插件）
        if(C('apply.Jobfair')){
            D('JobfairExhibitors')->where(array('uid'=>$to_uid))->delete();
            D('JobfairExhibitors')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        }

        //职位  jobs  jobs_contact  jobs_search  jobs_search_key  jobs_tag  jobs_tmp
        $jobslist = D('Jobs')->where(array('uid'=>$from_uid))->select();
        $jids = array();
        foreach ($jobslist as $key => $value) {
            $jids[] = $value['id'];
        }
        if(!empty($jids)){
            D('JobsContact')->where(array('pid'=>array('in',$jids)))->save(array('uid'=>$to_uid));
        }
        D('Jobs')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        D('JobsSearch')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        D('JobsSearchKey')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        D('JobsTag')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
        D('JobsTmp')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));

        //套餐  members_setmeal
        D('MembersSetmeal')->where(array('uid'=>$to_uid))->delete();
        D('MembersSetmeal')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));

        //申请职位记录 personal_jobs_apply
        D('PersonalJobsApply')->where(array('company_uid'=>$from_uid))->save(array('company_uid'=>$to_uid));

        //Rpo  rpo  rpo_follow
        if(C('apply.Rpo')){
            D('Rpo')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));
            D('RpoFollow')->where(array('com_uid'=>$from_uid))->save(array('com_uid'=>$to_uid));
        }
        //view_jobs
        D('ViewJobs')->where(array('jobs_uid'=>$from_uid))->save(array('jobs_uid'=>$to_uid));

        //view_resume
        D('ViewResume')->where(array('uid'=>$from_uid))->save(array('uid'=>$to_uid));

        //删除未处理的注销申请
        M('CompanyCancellationApply')->where(array('uid'=>$from_uid,'status'=>0))->delete();

        //积分、套餐、分配客服等初始化操作
        $userinfo = D('Members')->get_user_one(array('uid' => $from_uid));
        D('MembersSetmeal')->add_members_setmeal($userinfo['uid'], 0);
        D('MembersSetmeal')->set_members_setmeal($userinfo['uid'], C('qscms_reg_service'));//赠送企业套餐
        //设置企业模版
        $setsqlarr['tplid'] = 5;
        $setsqlarr['uid'] = $userinfo['uid'];
        D('CompanyTpl')->add_company_tpl($setsqlarr);
    }
    /**
     * 转移企业日志记录
     *
     * @param int $from_uid 来源会员
     * @param int $to_uid 目标会员
     * @param bool $isnewuser 目标会员是否新创建会员
     */
    public function company_migration_log($from_uid, $to_uid, $isnewuser) {
        $from_user = M('Members')->field('uid,username,mobile')->where(array('uid'=>$from_uid))->find();
        $to_user = M('Members')->field('uid,username,mobile')->where(array('uid'=>$to_uid))->find();
        $company = M('CompanyProfile')->field('id,companyname')->where(array('uid'=>$to_uid))->find();

        $log['from_uid'] = $from_user['uid'];
        $log['from_username'] = $from_user['username'];
        $log['from_mobile'] = $from_user['mobile'];
        $log['to_uid'] = $to_user['uid'];
        $log['to_username'] = $to_user['username'];
        $log['to_mobile'] = $to_user['mobile'];
        $log['company_id'] = $company['id'];
        $log['companyname'] = $company['companyname'];
        $log['isnewuser'] = (int) $isnewuser;
        $log['type'] = 1;  // 默认自主处理
        if (strtolower(MODULE_NAME) == 'admin') {
            $log['type'] = 2;  // 管理员处理
        }
        $log['addtime'] = time();

        M('CompanyMigrationLog')->add($log);
    }
    /**
     * 企业账号注销
     */
    public function company_cancellation($apply_id){
        $info = M('CompanyCancellationApply')->find($apply_id);
        if(!$info){
            return false;
        }
        $uid = $info['uid'];
        D('CompanyProfile')->where(array('uid'=>$uid))->delete();
        D('CompanyDownResume')->where(array('company_uid'=>$uid))->delete();
        D('CompanyFavorites')->where(array('company_uid'=>$uid))->delete();
        D('CompanyImg')->where(array('company_uid'=>$uid))->delete();
        D('CompanyInterview')->where(array('company_uid'=>$uid))->delete();
        if(C('apply.Report')){
            D('CompanyReport')->where(array('uid'=>$uid))->delete();
        }
        D('CompanyStatistics')->where(array('uid'=>$uid))->delete();
        D('CompanyTpl')->where(array('uid'=>$uid))->delete();
        if(C('apply.Jobfair')){
            D('JobfairExhibitors')->where(array('uid'=>$uid))->delete();
        }
        $jobslist = D('Jobs')->where(array('uid'=>$uid))->select();
        $jids = array();
        foreach ($jobslist as $key => $value) {
            $jids[] = $value['id'];
        }
        if(!empty($jids)){
            D('JobsContact')->where(array('pid'=>array('in',$jids)))->delete();
        }
        D('Jobs')->where(array('uid'=>$uid))->delete();
        D('JobsSearch')->where(array('uid'=>$uid))->delete();
        D('JobsSearchKey')->where(array('uid'=>$uid))->delete();
        D('JobsTag')->where(array('uid'=>$uid))->delete();
        D('JobsTmp')->where(array('uid'=>$uid))->delete();
        D('MembersSetmeal')->where(array('uid'=>$uid))->delete();
        D('PersonalJobsApply')->where(array('company_uid'=>$uid))->delete();
        if(C('apply.Rpo')){
            D('Rpo')->where(array('uid'=>$uid))->delete();
            D('RpoFollow')->where(array('com_uid'=>$uid))->delete();
        }
        D('ViewJobs')->where(array('jobs_uid'=>$uid))->delete();
        D('ViewResume')->where(array('uid'=>$uid))->delete();
        M('CompanyCancellationApply')->where(array('id'=>$apply_id))->save(array('status'=>1,'finishtime'=>time()));

        //积分、套餐、分配客服等初始化操作
        D('MembersSetmeal')->add_members_setmeal($uid, 0);
        D('MembersSetmeal')->set_members_setmeal($uid, C('qscms_reg_service'));//赠送企业套餐
        //设置企业模版
        $setsqlarr['tplid'] = 5;
        $setsqlarr['uid'] = $uid;
        D('CompanyTpl')->add_company_tpl($setsqlarr);
        return true;
    }
}

?>