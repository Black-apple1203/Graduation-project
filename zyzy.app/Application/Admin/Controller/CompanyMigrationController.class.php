<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class CompanyMigrationController extends BackendController {
    /**
     * 创建企业迁移
     */
    public function create() {
        $uid = I('get.uid', '', 'intval');

        $this->assign('uid', $uid);

        $html = $this->fetch();

        $this->ajaxReturn(1, '获取数据成功！', $html);
    }

    /**
     * 执行企业迁移
     */
    public function store() {
        $from_uid = I('post.from_uid', '', 'intval');
        $receive_mobile = I('post.receive_mobile', '', 'strval');

        if (!$receive_mobile || !fieldRegex($receive_mobile, 'mobile')) {
            $this->ajaxReturn(0, '接收手机号错误');
        }

        $userinfo = M('Members')->field('uid,mobile')->where(array('mobile' => $receive_mobile))->find();
        $to_uid = 0;
        if ($userinfo) {
            $companyinfo = D('CompanyProfile')->where(array('uid' => $userinfo['uid']))->find();
            if ($companyinfo) {
                $this->ajaxReturn(0, '该账号已存在企业信息!');
            }
            $to_uid = $userinfo['uid'];
        } else {
            $data['utype'] = 1;
            $data['mobile'] = $receive_mobile;
            $passport = $this->_user_server('');
            if (false === $data = $passport->register($data)) {
                $this->ajaxReturn(0, $passport->get_error());
            }
            if (!C('qscms_register_password_open')) {
                $sendSms['tpl'] = 'set_register_resume';
                $sendSms['data'] = array('username' => $data['username'] . '', 'password' => $data['password']);
                $sendSms['mobile'] = $data['mobile'];
                if (true !== $reg = D('Sms')->sendSms('captcha', $sendSms)) $this->ajaxReturn(0, $reg);
            }
            D('Members')->user_register($data);//积分、套餐、分配客服等初始化操作
            $to_uid = $data['uid'];
        }
        D('CompanyProfile')->company_migration($from_uid, $to_uid);

        D('CompanyProfile')->company_migration_log($from_uid, $to_uid, !$userinfo);

        $this->ajaxReturn(1, '账号迁移成功');
    }

    /**
     * 迁移日志列表
     */
    public function logs() {
        $this->_tpl = 'logs';
        $this->_name = 'CompanyMigrationLog';

        $key = I('get.key', '', 'trim');
        $key_type = I('get.key_type', 0, 'intval');
        if ($key && $key_type > 0) {
            switch ($key_type) {
                case 1:
                    $where['companyname'] = array('like', '%' . $key . '%');
                    break;
            }
        }
        if (!empty($where)) {
            $this->where = $where;
        }

        parent::index();
    }

    /**
     * 连接用户中心
     */
    private function _user_server($type = '') {
        $passport = new \Common\qscmslib\passport($type);
        return $passport;
    }
}