<?php
namespace Admin\Controller;

use Common\Controller\BackendController;

class AjaxController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }

    public function userinfo() {
        $uid = I('get.uid', 0, 'intval');
        if (!$uid) {
            $this->ajaxReturn(0, '参数错误！');
        }
        $userinfo = D('Members')->get_user_one(array('uid' => $uid));
        $manage_url = $userinfo['utype'] == 1 ? U('Company/management', array('id' => $userinfo['uid'])) : U('Personal/management', array('id' => $userinfo['uid']));
        if ($userinfo['utype'] == 1) {
            $consultant = D('Consultant')->find($userinfo['consultant']);
            $this->assign('consultant', $consultant);
            $company_profile = D('CompanyProfile')->where(array('uid' => $userinfo['uid']))->find();
            !$company_profile && $company_profile['companyname'] = $userinfo['username'];
            $this->assign('company_profile', $company_profile);
        } else {
            $userinfo['realname'] = M('Resume')->where(array('uid' => $uid, 'def' => 1))->limit(1)->getfield('fullname');
            $this->assign('resume_manage', U('personal/management', array('id' => $userinfo['uid'])));
        }
        $this->assign('userinfo', $userinfo);
        $this->assign('manage_url', $manage_url);
        $html = $this->fetch('userinfo');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    public function business() {
        $uid = I('get.uid', 0, 'intval');
        if (!$uid) {
            $this->ajaxReturn(0, '参数错误！');
        }
        $utype = I('get.utype', 2, 'intval');
        $this->assign('utype',$utype);
        $userinfo = D('Members')->get_user_one(array('uid' => $uid));
        $manage_url = $utype == 1 ? U('Company/management', array('id' => $userinfo['uid'])) : U('Personal/management', array('id' => $userinfo['uid']));
        if ($utype == 1) {
            $consultant = D('Consultant')->find($userinfo['consultant']);
            $this->assign('consultant', $consultant);
            $company_profile = D('CompanyProfile')->where(array('uid' => $userinfo['uid']))->find();
            !$company_profile && $company_profile['companyname'] = $userinfo['username'];
            $this->assign('company_profile', $company_profile);
        }
        $this->assign('userinfo', $userinfo);
        $this->assign('manage_url', $manage_url);
        $html = $this->fetch('business');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }
	/*
	chm*******start
	*/
    public function issue_list() {   
		$static_id = I('get.static_id');     
        if (!$static_id) {
            $this->ajaxReturn(0, '参数错误！');
        }
		$where['static_id'] = $static_id;
		
		$pagesize = 5;
		$count = M("GiftIssue")->where($where)->count();
		$pager = pager($count, $pagesize);
		$list = M("GiftIssue")->where($where)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
		//$list = M("GiftIssue")->where($where)->select();
		$gifts = M("Gift")->getField('id,gift_name');	
		$admins = M("Admin")->getField('id,username');
		$uid_str='';
		foreach($list as $k=>$val){
			$uid_str.=$val['uid'].',';
		}
		$uidstr = substr($uid_str,0,-1);
		$company_where['uid'] = array('IN',$uidstr);
		$company = M("CompanyProfile")->where($company_where)->getField('uid,companyname');
        foreach($list as $k=>$val){
			$gift_id = $val['gift_id'];
			$val['gift_name'] = $gifts[$gift_id];
            $admin_id = $val['admin_id'];
			$val['admin_name'] = $admins[$admin_id];
            $uid = $val['uid'];
			$val['companyname'] = $company[$uid];
			$val['company_url'] = url_rewrite('QS_companyshow', array('id' => $company['id']));
			$list[$k] = $val;
		}
		$this->assign('page', $pager->fshow());
        $this->assign('list', $list);
        $html = $this->fetch('issue_list');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }
	/*
	chm*******end
	*/
    public function ajax_message() {
        $uid = I('request.uid', 0, 'intval');
        $userinfo = D('Members')->get_user_one(array('uid' => $uid));
        $this->assign('userinfo', $userinfo);
        $html = $this->fetch('message');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    public function ajax_send_sms() {
        $uid = I('request.uid', 0, 'intval');
        $mobile = I('request.mobile', '', 'trim');
        $service_info = D('Sms')->where(array('alias' => C('qscms_sms_other_service')))->find();
        if (!$service_info) {
            $this->ajaxReturn(0, '短信配置错误，请修改后再发送！');
        }
        if (IS_POST) {
            if (!$uid) {
                $this->error('用户UID错误！');
            }
            $setsqlarr['s_mobile'] = $mobile ? $mobile : $this->returnMsg(0,'手机不能为空！');
            $setsqlarr['s_body'] = I('request.txt', '', 'trim') ?: $this->returnMsg(0,'短信内容不能为空！');
            $setsqlarr['s_addtime'] = time();
            $setsqlarr['s_uid'] = $uid;
            $setsqlarr['s_tplid'] = I('request.tplid', '', 'trim');
            if ($setsqlarr['s_tplid']) {
                $data = array('mobile' => $setsqlarr['s_mobile'], 'tpl' => $setsqlarr['s_body'], 'tplId' => $setsqlarr['s_tplid'], 'data' => array());
                $service = C('qscms_sms_other_service');
                $sms = new \Common\qscmslib\sms($service);
                $r = $sms->sendTemplateSMS('other', $data);
            } else {
                $r = D('Sms')->sendSms('other', array('mobile' => $setsqlarr['s_mobile'], 'tplStr' => $setsqlarr['s_body']));
            }
            if (true === $r) {
                $setsqlarr['s_sendtime'] = time();
                $setsqlarr['s_type'] = 2;//发送成功
                D('Smsqueue')->add($setsqlarr);
                unset($setsqlarr);
                $this->returnMsg(1,'发送成功！');
            } else {
                $setsqlarr['s_sendtime'] = time();
                $setsqlarr['s_type'] = 3;//发送失败
                D('Smsqueue')->add($setsqlarr);
                unset($setsqlarr);
                $this->returnMsg(0,'发送失败，错误未知！');
            }
        } else {
            $this->assign('uid', $uid);
            $this->assign('mobile', $mobile);
            $this->assign('service_info', $service_info);
            $this->assign('need_tpl', $service_info['replace'] ? 1 : 0);
            $html = $this->fetch('send_sms');
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }
    }
    
    public function ajax_send_pms() {
        $tousername = I('request.tousername', '', 'trim');
        if (IS_POST) {
            if (!$tousername) {
                $this->returnMsg(0,'用户名填写错误！');
                exit;
            } else {
                $s = 0;
                $msg = I('post.msg', '', 'trim');
                $time = time();
                $data = array();
                $userinfo = D('Members')->where(array('username' => $tousername))->find();
                if (intval($userinfo['uid']) > 0) {
                    $data['msgtype'] = 1;
                    $data['msgtouid'] = $userinfo['uid'];
                    $data['msgtoname'] = $userinfo['username'];
                    $data['message'] = $msg;
                    $data['dateline'] = $time;
                    $data['replytime'] = $time;
                    $data['new'] = 1;
                }
                D('Pms')->add($data);
                $this->returnMsg(1,'发送成功！');
                exit;
            }
        } else {
            $this->assign('tousername', $tousername);
            $html = $this->fetch('send_pms');
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }
    }

    /**
     * 批量审核职位
     */
    public function jobs_audit() {
        $ids = I('request.id');
        $uids = I('request.uid');
        if (!$ids) $this->ajaxReturn(0, '请选择职位！');
        $this->assign('ids', $ids);
        $this->assign('uids', $uids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量审核简历
     */
    public function resumes_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择简历！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量审核简历照片
     */
    public function resumes_photo_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择简历！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量审核公司
     */
    public function company_audit() {
        $ids = I('request.y_id');
        if (!$ids) $this->ajaxReturn(0, '请选择企业！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量审核投诉
     */
    public function complain_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择投诉！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量处理举报
     */
    public function report_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择举报记录！');
        $type = I('request.type', 1, 'intval');
        $this->assign('ids', $ids);
        $this->assign('type', $type);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量处理意见建议
     */
    public function feedback_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择意见建议！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量处理帐号申诉
     */
    public function appeal_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择申诉记录！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量处理发票
     */
    public function set_invoice() {
        $ids = I('request.order_id');
        if (!$ids) $this->ajaxReturn(0, '请选择发票记录！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量设置企业顾问
     */
    public function set_consultant() {
        $ids = I('request.y_id');
        if (!$ids) $this->ajaxReturn(0, '请选择企业会员！');
        $consultants = M('Consultant')->select();
        if (!$consultants) $this->ajaxReturn(0, '没有企业顾问可设置！');
        if (is_array($ids)) {
            $ids = implode(",", $ids);
        }
        $this->assign('ids', $ids);
        $this->assign('consultants', $consultants);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量刷新企业
     */
    public function refresh_company() {
        $ids = I('request.y_id');
        if (!$ids) $this->ajaxReturn(0, '请选择企业！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量删除企业
     */
    public function delete_company() {
        $ids = I('request.y_id');
        if (!$ids) $this->ajaxReturn(0, '请选择企业！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 审核日志
     */
    public function audit_log() {
        $type = I('get.type', 'jobs_id', 'trim');
        $id = I('get.id', 0, 'intval');
        switch ($type) {
            case 'jobs_id':
            case 'resume_id':
            case 'company_id':
                $list = D('AuditReason')->where(array($type => $id, 'famous' => 0))->order('id desc')->select();
                break;
            default:
                $list = null;
                break;
        }
        $this->assign('list', $list);
        $html = $this->fetch('audit_log');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 个人会员日志
     */
    public function personal_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 简历审核日志
     */
    public function resume_audit_log() {
        $id = I('get.id', 0, 'intval');
        $list = D('MembersLog')->where(array('resume_id' => $id, 'log_type' => 'resume_audit'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 简历日志
     */
    public function resume_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 高级简历日志
     */
    public function adv_resume_log() {
        $uid = I('get.resumeid', 0, 'intval');
        $list = D('MembersLog')->where(array('resume_id' => $uid))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 增值服务日志
     */
    public function increment_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'increment'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 推广日志
     */
    public function promotion_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'promotion'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 订单日志
     */
    public function order_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'order'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 套餐日志
     */
    public function setmeal_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'setmeal'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 积分日志
     */
    public function points_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'points'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 企业日志
     */
    public function company_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 企业审核日志
     */
    public function company_audit_log() {
        $uid = I('get.uid', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $uid, 'log_type' => 'company_audit'))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 职位日志
     */
    public function jobs_log() {
        $id = I('get.id', 0, 'intval');
        $list = D('MembersLog')->where(array('jobs_id' => $id))->order('log_id desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('_log_tpl');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 登录日志
     */
    public function login_log() {
        $id = I('get.id', 0, 'intval');
        $list = D('MembersLog')->where(array('log_uid' => $id, 'log_type' => 'login'))->order('log_addtime desc')->limit('5')->select();
        $this->assign('list', $list);
        $html = $this->fetch('login_log');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 套餐详情
     */
    public function setmeal_detail() {
        $uid = I('get.uid', 0, 'intval');
        $info = D('MembersSetmeal')->get_user_setmeal($uid);
        $list = M('MembersLog')->where(array('log_uid' => $uid))->select();
        $this->assign('info', $info);
        $this->assign('list', $list);
        $html = $this->fetch('setmeal_detail');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量删除企业会员
     */
    public function delete_company_members() {
        $ids = I('request.tuid');
        if (!$ids) $this->ajaxReturn(0, '请选择会员！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 批量删除个人会员
     */
    public function delete_person_members() {
        $ids = I('request.tuid');
        if (!$ids) $this->ajaxReturn(0, '请选择会员！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 审核图片
     */
    public function img_audit() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择图片！');
        $type = I('request.utype', 0, 'intval');
        if ($type == 1) {
            $controller = 'CompanyImg';
        } elseif ($type == 2) {
            $controller = 'ResumeImg';
        } else {
            $this->ajaxReturn(0, '用户类型错误！');
        }
        $this->assign('controller', $controller);
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 委托投递记录
     */
    public function entrust_apply_log() {
        $id = I('get.id', 0, 'intval');
        $list = M('PersonalJobsApply')->where(array('is_apply' => 1, 'resume_id' => $id))->order('did desc')->select();
        $this->assign('list', $list);
        $html = $this->fetch('entrust_apply_log');
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * [affair_stat 待处理事务统计]
     */
    public function affair_stat() {
        $affair = I('request.affair', '', 'trim');
        foreach ($affair as $key => $val) {
            $name = '_affair_' . $val;
            $d = $this->$name();
            $d && $list[$val] = $d;
        }
        $this->ajaxReturn(1, '获取成功！', $list);
    }

    /**
     * 发送邮件营销
     */
    public function send_mail_queue() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择项目！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 删除邮件营销
     */
    public function delete_mail_queue() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择项目！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 发送短信营销
     */
    public function send_sms_queue() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择项目！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 删除短信营销
     */
    public function delete_sms_queue() {
        $ids = I('request.id');
        if (!$ids) $this->ajaxReturn(0, '请选择项目！');
        $this->assign('ids', $ids);
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 页面管理批量设置链接
     */
    public function page_set_url() {
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 页面管理批量设置链接
     */
    public function page_set_caching() {
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 短信营销导入号码
     */
    public function import_mobile_num() {
        $html = $this->fetch();
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 简历导入
     */
    public function resume_import() {
        $html = $this->fetch("resume_import");
        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * [_affair_resume 待处理简历]
     */
    protected function _affair_resume() {
        $count = parent::_pending('Resume', array('audit' => 2));
        return $count ?: false;
    }

    /**
     * [_affair_resume_img 待处理简历作品]
     */
    protected function _affair_resume_img() {
        $join = 'right join '.C('DB_PREFIX').'resume as r on r.id='.C('DB_PREFIX').'resume_img.resume_id';
        $count = parent::_pending('ResumeImg', array(C('DB_PREFIX').'resume_img.audit' => 2),C('DB_PREFIX').'resume_img.id',$join);
        return $count ?: false;
    }

    /**
     * [_affair_photo 待处理简历照片]
     */
    protected function _affair_photo() {
        $count = parent::_pending('Resume', array('photo_img' => array('neq', ''), 'photo_audit' => 2), 'uid');
        return $count ?: false;
    }

    /**
     * [_affair_jobs 待处理职位]
     */
    protected function _affair_jobs() {
        $count = parent::_pending('Jobs', array('audit' => 2));
        $count1 = parent::_pending('JobsTmp', array('audit' => 2));
        $count = $count + $count1;
        return $count ?: false;
    }

    /**
     * [_affair_company 待认证企业]
     */
    protected function _affair_company_audit() {
        $count = parent::_pending('CompanyProfile', array('audit' => 2));
        return $count ?: false;
    }

    /**
     * [_affair_company_img 待处理企业风采]
     */
    protected function _affair_company_img() {
        $count = parent::_pending('CompanyImg', array('audit' => 2));
        return $count ?: false;
    }

    /**
     * [_affair_company_order 待处理企业线下支付订单]
     */
    protected function _affair_company_order() {
        $count = parent::_pending('Order', array('payment' => 'remittance', 'is_paid' => 1));
        return $count ?: false;
    }

    /**
     * [_affair_report 待处理举报信息]
     */
    protected function _affair_report(){
        $count2 = parent::_pending('Report',array('audit'=>0));
        $count1 = parent::_pending('ReportResume',array('audit'=>1));
        $count3 = parent::_pending('Feedback',array('audit'=>1));
        $count4 = parent::_pending('ConsultantComplaint',array('audit'=>1));
        $count = $count1 + $count2 + $count3 + $count4;
        return $count ?:false;
    }

    /**
     * [_affair_feedback 待处理意见与建议]
     */
    protected function _affair_feedback() {
        $count = parent::_pending('Feedback', array('audit' => 1));
        return $count ?: false;
    }

    /**
     * [_affair_appeal 待处理帐号申诉]
     */
    protected function _affair_appeal() {
        $count = parent::_pending('MembersAppeal', array('status' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_exhibitors 待处理参会企业]
     */
    protected function _affair_exhibitors() {
        $count = parent::_pending('JobfairExhibitors', array('audit' => 2));
        return $count ?: false;
    }

    /**
     * [_affair_mall_order 待处理商城订单]
     */
    protected function _affair_mall_order() {
        $count = parent::_pending('MallOrder', array('status' => 1));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待处理发票]
     */
    protected function _affair_order_invoice() {
        $count = parent::_pending('OrderInvoice', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待处理发票]
     */
    protected function _affair_resume_entrust() {
        $count = parent::_pending('Resume', array('entrust' => array('gt', 0)));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核兼职]
     */
    protected function _affair_parttime() {
        $count = parent::_pending('ParttimeJobs', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核门店职位]
     */
    protected function _affair_store_jobs() {
        $count = parent::_pending('StorerecruitJobs', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核门店转租]
     */
    protected function _affair_store_rent() {
        $count = parent::_pending('Storetransfer', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核门店求租]
     */
    protected function _affair_store_seek() {
        $count = parent::_pending('Storetenement', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核店铺图片]
     */
    protected function _affair_store_rent_img() {
        $count = parent::_pending('StoretransferImg', array('display' => 1, 'audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核房屋出租]
     */
    protected function _affair_house_rent() {
        $count = parent::_pending('HouseRent', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核房屋求租]
     */
    protected function _affair_house_seek() {
        $count = parent::_pending('HouseSeek', array('audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_order_invoice 待审核房屋照片]
     */
    protected function _affair_house_rent_img() {
        $count = parent::_pending('HouseRentImg', array('display' => 1, 'audit' => 0));
        return $count ?: false;
    }

    /**
     * [_affair_gworker 待处理申请]
     */
    protected function _affair_gworker() {
        $count = parent::_pending('GworkerPublishApply', array('status' => 0));
        return $count ?: false;
    }

    /**
     * [affair 主菜单待处理事务统计]
     */
    public function affair() {
        $affair = I('request.affair', '', 'trim');
        foreach ($affair as $key => $val) {
            $name = '_total_affair_' . $val;
            $d = $this->$name();
            $d && $list[$val] = $d;
        }
        $this->ajaxReturn(1, '获取成功！', $list);
    }

    protected function _subsite($mod, $where) {
        return M($mod)->where($where)->find();
    }

    /**
     * [_total_affair_personal 个人事务]
     */
    protected function _total_affair_personal() {
        $s = $this->_subsite('Resume', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('ResumeImg', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('Resume', array('photo_img' => array('neq', ''), 'photo_audit' => 2));
        return false;
    }

    /**
     * [_total_affair_company 企业事务]
     */
    protected function _total_affair_company() {
        $s = $this->_subsite('Jobs', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('JobsTmp', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('CompanyProfile', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('CompanyImg', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('OrderInvoice', array('audit' => 2));
        if ($s) return 1;
        $s = $this->_subsite('Order', array('payment' => 'remittance', 'is_paid' => 1));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_content 待处理内容]
     */
    protected function _total_affair_content() {
        $s = $this->_subsite('Report', array('audit' => 1));
        if ($s) return 1;
        $s = $this->_subsite('ReportResume', array('audit' => 1));
        if ($s) return 1;
        $s = $this->_subsite('Feedback', array('audit' => 1));
        if ($s) return 1;
        $s = $this->_subsite('MembersAppeal', array('status' => 0));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_jobfair 待处理招聘会]
     */
    protected function _total_affair_jobfair() {
        $s = $this->_subsite('JobfairExhibitors', array('audit' => 2));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_mall 待处理商城]
     */
    protected function _total_affair_mall() {
        $s = $this->_subsite('MallOrder', array('status' => 1));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_parttime 兼职事务]
     */
    protected function _total_affair_parttime() {
        $s = $this->_subsite('ParttimeJobs', array('audit' => 0));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_store 门店事务]
     */
    protected function _total_affair_store() {
        $s = $this->_subsite('StorerecruitJobs', array('audit' => 0));
        if ($s) return 1;
        $s = $this->_subsite('Storetransfer', array('audit' => 0));
        if ($s) return 1;
        $s = $this->_subsite('Storetenement', array('audit' => 0));
        if ($s) return 1;
        $s = $this->_subsite('StoretransferImg', array('display' => 1, 'audit' => 0));
        if ($s) return 1;
        return false;
    }

    /**
     * [_total_affair_store 租房事务]
     */
    protected function _total_affair_house() {
        $s = $this->_subsite('HouseRent', array('audit' => 0));
        if ($s) return 1;
        $s = $this->_subsite('HouseSeek', array('audit' => 0));
        if ($s) return 1;
        $s = $this->_subsite('HouseRentImg', array('display' => 1, 'audit' => 0));
        if ($s) return 1;
        return false;
    }
     /**
     * [_total_affair_gworker 普工事务]
     */
    protected function _total_affair_gworker(){
        $s = $this->_subsite('GworkerPublishApply',array('status'=>0));
        if($s) return 1;
        return false;
    }
    /**
     * 点评简历
     */
    public function comment_resume() {
        $id = I('request.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '参数错误！');
        if (IS_POST) {
            $data['comment_content'] = I('post.comment_content', '', 'trim');
            $data['talent'] = I('post.talent', 0, 'intval');
            D('Resume')->where(array('id' => $id))->save($data);
            $this->returnMsg(1,'保存成功！');
        } else {
            $resume = D('Resume')->find($id);
            $this->assign('resume', $resume);
            $html = $this->fetch('comment_resume');
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }
    }
     /**
     * 加入黑名单
     */
    public function set_blacklist(){
        if(IS_POST){
            $uid = I('request.uid');
            !$uid && $this->error('请选择用户！');
            $info = D('InviteBlacklist')->where(array('uid'=>$uid))->find();
            if($info){
                $this->error('此用户已被加入黑名单！');
            }
            $note = I('request.note','','trim');
            $arr['uid'] =$uid;
            $arr['note'] =$note;
            $resume = M('Resume')->where(array('uid'=>$uid))->field('fullname,telephone')->find();
            $username = M('Members')->where(array('uid'=>$uid))->field('username')->find();
            $arr['fullname'] = $resume['fullname'];
            $arr['addtime'] = time();
            $arr['admin_name'] = C('visitor.username');
            $arr['username'] = $username['username'];
            $insert_id =D('InviteBlacklist')->add($arr);
            if($insert_id){
                $info = D('InviteAllowance')->where(array('inviter_uid'=>$uid))->select();
                foreach ($info as $key => $value) {
                    D('InviteAllowance')->where(array('id'=>$value['id']))->setfield('is_black',2);
                }
                if(false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
                if ($sms['set_invite_blacklist']=="1")
                {
                    $sendSms['mobile']=$resume['telephone'];
                    $sendSms['tpl']='set_invite_blacklist';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'));
                    D('Common/Sms')->sendSms('notice',$sendSms);
                }
                //微信提醒
                D('Common/WeixinTplMsg')->set_invite_blacklist($uid);
                $this->success('操作成功！'); 
            }else{
                $this->error('操作失败！'); 
            }
        }else{
            $uid = I('request.uid');
            !$uid && $this->error('请选择！');
            $this->assign('uid',$uid);
            $html = $this->fetch('set_blacklist');
            $this->ajaxReturn(1,'调用成功！',$html);
        }
    }
    /**
     * 加入黑名单
     */
    public function set_blacklist_save(){
        $uid = I('request.uid');
        !$uid && $this->error('请选择用户！');
        $note = I('request.note','','trim');
        $timelimit = I('request.timelimit',0,'intval');
        $deadline = $timelimit==0?0:strtotime('+'.$timelimit.' days');
        $uid_arr = is_array($uid)?$uid:array($uid);
        $members = D('Common/Members')->where(array('uid'=>array('in',$uid_arr)))->select();
        foreach ($members as $key => $value) {
            $insert_id = D('AllowanceBlacklist')->add(array('deadline'=>$deadline,'uid'=>$value['uid'],'utype'=>$value['utype'],'robot'=>0,'note'=>$note,'admin_name'=>C('visitor.username')));
            //短信提醒
            if($insert_id){
                if(false === $sms = F('sms_config')) $sms = D('Common/SmsConfig')->config_cache();
                if ($sms['set_allowance_add_blacklist']=="1")
                {
                    $sendSms['mobile']=$value['mobile'];
                    $sendSms['tpl']='set_allowance_add_blacklist';
                    $timelimit = $timelimit==0?'永久':$timelimit.'天';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'),'time_limit'=>$timelimit);
                    D('Common/Sms')->sendSms('notice',$sendSms);
                }
            }
            //微信提醒
            if(C('apply.Weixin')){
                D('Allowance/AllowanceTplMsg')->set_allowance_add_blacklist($value['uid']);
            }
        }
        $this->success('操作成功！');
    }
}

?>