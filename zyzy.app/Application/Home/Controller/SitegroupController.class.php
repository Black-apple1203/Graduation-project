<?php
namespace Home\Controller;

use Common\Controller\FrontendController;

class SitegroupController extends FrontendController {
    protected $post;

    public function _initialize() {
        parent::_initialize();
        //验证是否配置集群系统参数,并打开
        if (!C('qscms_sitegroup_open') && !in_array(ACTION_NAME, array('test', 'getsynusercount', 'getsynuser', 'setsyncuser'))) {
            $this->ajaxReturn(0, '分站未开启！');
        }
        if (!C('qscms_sitegroup_domain') || !C('qscms_sitegroup_secret_key') || !C('qscms_sitegroup_id')) {
            $this->ajaxReturn(0, '分站参数未配置！');
        }
        $code = I('request.code', '', 'trim,rawurldecode');
        $time = I('request.time', 0, 'intval');
        if (!$code || !$time) $this->ajaxReturn(0, '缺少参数！');
        parse_str(decrypt($code, C('qscms_sitegroup_secret_key')), $data);
        if (!$data || $data['_type'] != ACTION_NAME || $time != $data['_time']) $this->ajaxReturn(0, '非法参数！');
        if (time() - intval($data['_time']) > 3600) $this->ajaxReturn(0, '链接过期！');
        $this->post = $data;
    }

    /**
     * [test 测试通信状态]
     */
    public function test() {
        if (C('qscms_sitegroup_id') == $this->post['id']) {
            $this->ajaxReturn(1, '通信成功！', encrypt(C('PWDHASH'), C('qscms_sitegroup_secret_key')));
        }
        $this->ajaxReturn(0);
    }

    /**
     * [syndelete 删除用户信息]
     */
    public function syndelete() {
        !$this->post['uids'] && $this->ajaxReturn(0, '请选择要删除的用户UID！');
        if (!fieldRegex($this->post['uids'], 'in')) $this->ajaxReturn(0, '请选择用户UID！');
        $uids = M('Members')->where(array('sitegroup_uid' => array('in', $this->post['uids'])))->getfield('uid', true);
        if ($this->post['_user']) {
            D('Members')->delete_member($uids);
        }
        if ($this->post['_jobs']) {
            D('Jobs')->admin_delete_jobs_for_uid($uids);
        }
        if ($this->post['_resume']) {
            D('Resume')->admin_del_resume_for_uid($uids);
        }
        if ($this->post['_company']) {
            D('CompanyProfile')->admin_delete_company($uids);
        }
        $this->ajaxReturn(1, '成功删除用户信息！');
    }

    /**
     * [synregister 同步注册]
     */
    public function synregister() {
        if ($this->post['unbind_mobile']) {
            !$this->post['mobile'] && $this->ajaxReturn(0, '请填写手机号！');
            $repeat = M('Members')->where(array('mobile' => $this->post['mobile']))->select();
            foreach ($repeat as $val) {
                if (false != D('UnbindMobile')->create($val)) {
                    D('UnbindMobile')->add();
                }
                $update['mobile'] = '';
                M('Members')->where(array('uid' => $val['uid']))->save($update);
            }
        }
        $members_mod = D('Members');
        if (false === $members_mod->create($this->post)) $this->ajaxReturn(0, $members_mod->getError());
        $this->_register_company_validate($this->post);
        $members_mod->password = $members_mod->make_md5_pwd($this->post['password'], $members_mod->pwd_hash);
        $members_mod->invitation_code = $members_mod->randstr(8, true);
        if (!$this->post['uid'] = $members_mod->add()) $this->ajaxReturn(0, '用户注册失败，请重新操作！');
        $this->_register_company($this->post);
        D('Members')->user_register($this->post);
        $this->visitor->login($this->post['uid']);
        $this->ajaxReturn(1, '同步注册成功！');
    }

    /**
     * [synedit 修改用户信息]
     */
    public function synedit() {
        $members_mod = D('Members');
        $uid = $this->post['uid'];
        unset($this->post['uid']);
        $info = $members_mod->where(array('sitegroup_uid' => $uid))->find();
        if ($this->post['old_password']) {//先验证用户合法性
            if ($info['password'] != $members_mod->make_md5_pwd($this->post['old_password'], $info['pwd_hash'])) {
                $this->ajaxReturn(0, '原密码错误！');
            }
        }
        $this->post['uid'] = $info['uid'];
        if (false === $members_mod->create($this->post)) $this->ajaxReturn(0, $members_mod->getError());
        if ($this->post['password']) {
            $members_mod->password = $members_mod->make_md5_pwd($this->post['password'], $info['pwd_hash']);
        }
        if (!$members_mod->where(array('sitegroup_uid' => $uid))->save()) $this->ajaxReturn(0, '用户修改失败，请重新操作！');
        $this->visitor->update();
        $info['utype'] == 2 && $members_mod->update_user_info($this->post, $info);
        $this->ajaxReturn(1, '同步用户信息成功！');
    }

    /**
     * [synlogin 同步登陆]
     */
    public function synlogin() {
        $uid = M('Members')->where(array('sitegroup_uid' => $this->post['sitegroup_uid']))->getfield('uid');
        if (!$uid) {
            $members_mod = D('Members');
            $where['username'] = $this->post['username'];
            $where['email'] = $this->post['email'];
            $where['mobile'] = $this->post['mobile'];
            $password = $this->post['password'];
            $user = $members_mod->where($where)->find();
            if ($user['password'] == $members_mod->make_md5_pwd($password, $user['pwd_hash'])) {
                $members_mod->where(array('uid' => $user['uid']))->setField('sitegroup_uid', $this->post['sitegroup_uid']);
                if (false === $this->visitor->login($user['uid'], $this->post['expire'])) $this->ajaxReturn(0, $this->visitor->getError());
                $this->ajaxReturn(1, '同步登录成功！');
            }
            if (false === $members_mod->create($this->post)) $this->ajaxReturn(0, '用户不存在或已经删除！');
            $this->_register_company_validate($this->post);
            $members_mod->password = $members_mod->make_md5_pwd($this->post['password'], $members_mod->pwd_hash);
            $members_mod->invitation_code = $members_mod->randstr(8, true);
            if (!$uid = $members_mod->add()) $this->ajaxReturn(0, '用户不存在或已经删除！');
            $this->_register_company($this->post);
            D('Members')->user_register($this->post);
        }
        if (false === $this->visitor->login($uid, $this->post['expire'])) $this->ajaxReturn(0, $this->visitor->getError());
        $this->ajaxReturn(1, '同步登录成功！');
    }
    /**
     * [synlogout 同步退出]
     */
    public function synlogout() {
        $this->visitor->logout();
        $this->ajaxReturn(1, '同步退出成功！');
    }

    /**
     * [getsynusercount 获取满足同步的用户数量]
     */
    public function getsynusercount() {
        $count = M('Members')->where(array('sitegroup_uid' => 0))->count('uid');
        false === $count && $count = 0;
        exit("updater({$count})");
    }

    /**
     * [getsynuser 获取用户]
     */
    public function getsynuser() {
        $this->post['limit'] = $this->post['limit'] ?: '1000';
        $user = M('Members')->where(array('sitegroup_uid' => 0))->limit($this->post['limit'])->order('uid asc')->index('uid')->select();
        foreach ($user as $key => $val) {
            if ($val['utype'] == 1) $uids[] = $val['uid'];
        }
        if ($uids) {
            $company = M('CompanyProfile')->where(array('uid' => array('in', $uids)))->field('companyname,condition')->getfield('uid,companyname,contact,landline_tel');
            foreach ($company as $key => $val) {
                $user[$key] = array_merge($user[$key], $val);
            }
        }
        $this->ajaxReturn(1, '用户数据获取成功！', $user);
    }

    /**
     * [setsyncuser 设置同步用户sitegroup_uid]
     */
    public function setsyncuser() {
        $uids_str = I('request.uids', '', 'trim');
        if (!$this->post['uids_del'] && !$uids_str) $this->ajaxReturn(0, '没有可同步的用户sitegroup_uid');
        $user_mod = M('Members');
        if ($this->post['uids_del'] && fieldRegex($this->post['uids_del'], 'in')) {
            $reg = $user_mod->where(array('uid' => array('in', $this->post['uids_del'])))->delete();
        }
        if ($uids_str) {
            $uids_str = htmlspecialchars_decode($uids_str, ENT_QUOTES);
            parse_str($uids_str, $uids);
            foreach ($uids as $key => $val) {
                $reg = $user_mod->where(array('uid' => $key))->setfield('sitegroup_uid', $val);
                false !== $reg && $c++;
            }
        }
        if (!$reg && !$c) {
            $this->ajaxReturn(0, '同步失败！');
        } else {
            $this->ajaxReturn(1, '同步成功！', $c);
        }
    }

    /**
     * [_register_company_validate 注册企帐号验证]
     */
    protected function _register_company_validate($data) {
        if ($data['utype'] == 1) {
            $com_setarr['audit'] = 0;
            $com_setarr['email'] = $data['email'];
            $com_setarr['companyname'] = $data['companyname'];
            $com_setarr['contact'] = $data['contact'];
            $com_setarr['telephone'] = $data['telephone'];
            $com_setarr['landline_tel'] = $data['landline_tel'];
            $company_mod = D('CompanyProfile');
            if (false === $company_mod->create($com_setarr)) $this->ajaxReturn(0, $company_mod->getError());
        }
    }

    /**
     * [_register_company 注册企帐号]
     */
    protected function _register_company($data) {
        if ($data['utype'] == 1) {
            $company_mod = D('CompanyProfile');
            $company_mod->uid = $data['uid'];
            $insert_company_id = $company_mod->add();
            if ($insert_company_id) {
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
            }
        }
    }
}

?>