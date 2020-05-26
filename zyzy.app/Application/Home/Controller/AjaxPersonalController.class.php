<?php
namespace Home\Controller;

use Common\Controller\FrontendController;

class AjaxPersonalController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login && !in_array(ACTION_NAME, array('resume_add_dig', 'resume_add'))) $this->ajaxReturn(0, L('login_please'), '', 1);
        if ($this->visitor->is_login && C('visitor.utype') != 2) $this->ajaxReturn(0, '请登录个人帐号！');
    }

    /**
     * [resume_add_dig 快速创简历弹框]
     */
    public function resume_add_dig() {
        $no_apply = I('request.no_apply', 0, 'intval');
        if ($no_apply == 0) {
            $jid = I('request.jid', '', 'trim');
            !$jid && $this->ajaxReturn(0, '请选择要投递的职位！');
            is_array($jid) && $jid = implode(',', $jid);
            $jobs = M('Jobs')->find($jid);
            $jobs['age'] = explode('-', $jobs['age']);
            $jobs['age'][0] && $jobs['birthdate'] = date('Y') - intval($jobs['age'][0]);
            $this->assign('jid', $jid);
            $this->assign('jobs', $jobs);
        }
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('no_apply', $no_apply);
        $this->assign('oauth_list', $oauth_list);
        $this->assign('verify_userlogin', $this->check_captcha_open(C('qscms_captcha_config.user_login'), 'error_login_count'));
        $data['html'] = $this->fetch('AjaxCommon/resume_add_dig');
        $this->ajaxReturn(1, '快速创简历弹窗获取成功！', $data);
    }

    /**
     * [resume_add 快速创简历，并登录帐号]
     */
    public function resume_add() {
        $no_apply = I('request.no_apply', 0, 'intval');
        if ($no_apply == 0) {
            $jid = I('request.jid', '', 'trim');
            !$jid && $this->ajaxReturn(0, '请选择要投递的职位！');
        }
        if (C('qscms_closereg')) $this->ajaxReturn(0, '网站暂停会员注册，请稍后再次尝试！');
        $mobile = I('post.telephone', 0, 'trim');
        $smsVerify = session('gsou_reg_smsVerify');
        if(true !== $tip = verify_mobile($mobile,$smsVerify,I('post.mobilevcode', 0, 'intval'))) $this->ajaxReturn(0, $tip);
        if(!$data = M('Members')->field('uid,mobile,utype')->where(array('mobile'=>$smsVerify['mobile']))->find()){
            $data['utype'] = 2;
            $data['mobile'] = $smsVerify['mobile'];
            $passport = $this->_user_server();
            if (false === $data = $passport->register($data)) $this->ajaxReturn(0, $passport->get_error());

            $sendSms['tpl'] = 'set_register_resume';
            $sendSms['data'] = array('username' => $data['username'], 'password' => $data['password']);
            $sendSms['mobile'] = $data['mobile'];
            if (true !== $reg = D('Sms')->sendSms('captcha', $sendSms)) $this->ajaxReturn(0, $reg);

            session('gsou_reg_smsVerify', null);
            D('Members')->user_register($data);
        }
        if (false === $this->visitor->login($data['uid'])) $this->ajaxReturn(0, $this->visitor->getError());
        if($data['utype'] != 2){
            if (null === D('MembersSetmeal')->get_user_setmeal($this->visitor->info['uid'])) {
                D('Members')->user_register($this->visitor->info);//积分、套餐、分配客服等初始化操作
            }
            if (false !== D('Members')->where(array('uid' => $this->visitor->info['uid']))->setField('utype', 2)) {
                $this->visitor->update();
            }
        }
        $ints = array('sex', 'birthdate', 'education', 'experience', 'wage');
        $trims = array('telephone', 'fullname', 'intention_jobs_id', 'district');
        foreach ($ints as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        foreach ($trims as $val) {
            $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
        }
        if($data['password']){
            $setsqlarr['nature'] = 62;
            $setsqlarr['current'] = 241;
            $setsqlarr['def'] = 1;
            $setsqlarr['display_name'] = C('qscms_default_display_name');
            $setsqlarr['is_quick'] = 1;
            $rst = D('Resume')->add_resume($setsqlarr,$this->visitor->info);
        }else{
            $resume = D('Resume')->where(array('uid'=>$data['uid']))->order('def desc')->find();
            if($resume){
                $rst = D('Resume')->save_resume($setsqlarr,$resume['id'],$this->visitor->info);
            }else{
                $setsqlarr['nature'] = 62;
                $setsqlarr['current'] = 241;
                $setsqlarr['def'] = 1;
                $setsqlarr['display_name'] = C('qscms_default_display_name');
                $setsqlarr['is_quick'] = 1;
                $rst = D('Resume')->add_resume($setsqlarr,$this->visitor->info);
            }
        }
        if(!$rst['state']) $this->ajaxReturn(0,$rst['error']);
        $resume_insertid = $rst['id'];
        if ($no_apply == 0) {
            $this->_resume_apply_exe($jid, $resume_insertid);
        }
        $resume_info = D('Resume')->find($resume_insertid);
        $this->assign('resume_info', $resume_info);
        $return_data['create_success_html'] = $this->fetch('AjaxCommon/create_resume_success');
        $return_data['url'] = U('Personal/index');
        $this->ajaxReturn(1, '简历创建成功！', $return_data);
    }

    protected function _resume_apply_exe($jid, $rid) {
        $reg = D('PersonalJobsApply')->jobs_apply_add($jid, $this->visitor->info, $rid);
        if (!$reg['state'] && $reg['complete']) {// 完整度不够
            $this->assign('info', $reg['complete']);
            $data['rid'] = $reg['complete']['id'];
            $data['percent'] = $reg['complete']['percent'];
            $data['html'] = $this->fetch('AjaxCommon/apply_job_percent_tip');
            $this->ajaxReturn(1, $reg['error'], $data);
        }
        !$reg['state'] && $this->ajaxReturn(0, $reg['error'], $reg['create']);
        if ($reg['data']['failure']) {
            $tpl = 'AjaxCommon/batch_delivery_fail';
        } else {
            $tpl = 'AjaxCommon/batch_delivery_success';
        }
        $task_info = D('Task')->get_task_cache(C('visitor.utype'), 'submit_resume');
        $user_points = D('MembersPoints')->get_user_points($this->visitor->info['uid']);
        $this->assign('user_points', $user_points);
        $this->assign('apply', $reg['data']);
        $this->assign('show_points', $task_info ? 1 : 0);
        $this->assign('jid', is_array($jid) ? $jid[0] : $jid);
        $data['html'] = $this->fetch($tpl);
        $this->ajaxReturn(1, '投递成功！', $data);
    }

    /**
     * [ resume_apply 简历投递]
     */
    public function resume_apply() {
        $jid = I('request.jid');
        !$jid && $this->ajaxReturn(0, '请选择要投递的职位！');
        $this->_resume_apply_exe($jid);
    }

    /**
     * [jobs_favorites 收藏职位]
     */
    public function jobs_favorites() {
        $jid = I('request.jid', '', 'trim');
        !$jid && $this->ajaxReturn(0, '请选择要收藏的职位！');
        $reg = D('PersonalFavorites')->add_favorites($jid, C('visitor'));
        !$reg['state'] && $this->ajaxReturn(0, $reg['error']);
        $this->ajaxReturn(1, '收藏成功！');
    }

    /**
     * 关注企业/取消关注
     */
    public function company_focus() {
        if (!$company_id = I('request.company_id',0,'intval')) {
            $this->ajaxReturn(0, '请选择企业！');
        }
        $r = D('PersonalFocusCompany')->add_focus($company_id, C('visitor.uid'));
        $this->ajaxReturn($r['state'], $r['msg'], $r['data']);
    }

    /**
     * 举报职位
     */
    public function report_jobs() {
        $jobs_id = I('request.jobs_id', 0, 'intval');
        if (!$jobs_id) {
            $this->ajaxReturn(0, '参数错误！');
        }
        if (IS_POST) {
            $report_type = I('request.report_type', 1, 'intval');
            $content = I('request.content', '', 'trim');
            !$content && $this->ajaxReturn(0, '请填写备注说明！');
            $telephone = I('request.telephone', '', 'trim');
            !$telephone && $this->ajaxReturn(0, '请填写联系电话！');
            $data['jobs_id'] = $jobs_id;
            $data['report_type'] = $report_type;
            $data['telephone'] = $telephone;
            $data['content'] = $content;
            $r = D('Report')->add_report($data, C('visitor'));
            $this->ajaxReturn($r['state'], $r['msg']);
        } else {
            $taskinfo = D('Task')->get_task_cache(2, 'report_jobs');
            $this->assign('taskinfo', $taskinfo);
            $this->assign('jobs_id', $jobs_id);
            $this->assign('type_arr', D('Report')->type_arr);
            $html = $this->fetch('AjaxCommon/report_job');
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }
    }
}

?>