<?php

namespace Home\Controller;

class SchoolController extends CompanyController {
    public function _initialize() {
        parent::_initialize();
        $this->assign('company_nav', 'school_talk_list');
    }

    public function talk_list() {
        if (!isset($this->apply['School'])) $this->_empty();

        if ($this->company_profile['id']) {
            $where = array(
                '显示数目' => 10,
                '分页显示' => 1,
                '标题长度' => 40,
                '列表页' => 'Home/School/talk_list',
                '企业id' => $this->company_profile['id'],
                '审核' => 3
            );
            $talk_list_tag = new \Common\qscmstag\school_talk_listTag($where);
            $talk_list = $talk_list_tag->run();
            $this->assign('talk_list', $talk_list);
        }

        $this->_config_seo(array('title' => '近期宣讲会 - 企业会员中心 - {site_name}'));
        $this->assign('week', array('日', '一', '二', '三', '四', '五', '六'));
        $this->display();
    }

    public function talk_delete() {
        $id = I('request.id');
        if (IS_POST) {
            $info = D('SchoolTalk')->find($id);
            if (!$info || ($this->company_profile['id'] != $info['company_id'])) {
                $this->ajaxReturn(0, '数据不存在！');
            }
            if ($info['audit'] == '1') {
                $this->ajaxReturn(0, '已审核通过的宣讲会不能删除！');
            }
            $ret = D('School/SchoolTalk')->talk_delete($id);
            if (!$ret) {
                $this->ajaxReturn(0, '删除失败！');
            } else {
                $this->ajaxReturn(1, '删除成功');
            }
        } else {
            $tip = '被删除后将无法恢复，您确定要删除吗？';
            $this->ajax_warning($tip);
        }
    }

    /**
     * 申请宣讲会
     */
    public function talk_add() {
        $this->redirect_cominfo();

        if (IS_POST) {
            $data['school_id'] = I('post.school_id', '', 'intval');
            $data['subject'] = I('post.subject', '', 'trim');
            $data['address'] = I('post.address', '', 'trim');
            $starttime = I('post.starttime', '', 'trim');
            $data['starttime'] = strtotime($starttime);
            $data['introduction'] = I('post.introduction', '', 'trim');

            $ret = D('School/SchoolTalk')->talk_add($data, $this->company_profile);
            if ($ret['state'] === 0) {
                $this->ajaxReturn(0, $ret['error']);
            } else {
                $this->ajaxReturn(1, '申请成功');
            }

        } else {
            $school_list = M('School')->field('id,name')->where(array('display' => 1))->select();
            $this->assign('school_list', $school_list);

            $this->_config_seo(array('title' => '申请宣讲会 - 企业会员中心 - {site_name}'));
            $this->display();
        }
    }

    /**
     * 申请宣讲会
     */
    public function talk_edit() {
        $this->redirect_cominfo();

        if (IS_POST) {
            $data['school_id'] = I('post.school_id', '', 'intval');
            $data['subject'] = I('post.subject', '', 'trim');
            $data['address'] = I('post.address', '', 'trim');
            $starttime = I('post.starttime', '', 'trim');
            $data['starttime'] = strtotime($starttime);
            $data['introduction'] = I('post.introduction', '', 'trim');

            $id = I('post.id', 0, 'intval');

            $ret = D('School/SchoolTalk')->talk_edit($data, $id);
            if ($ret['state'] === 0) {
                $this->ajaxReturn(0, $ret['error']);
            } else {
                $this->ajaxReturn(1, '修改成功');
            }
        } else {
            $id = I('id', 0, 'intval');
            $info = D('SchoolTalk')->find($id);
            if (!$info) {
                $controller = new \Common\Controller\BaseController;
                $controller->_empty();
            }

            $this->assign('info', $info);
            $school = D('School')->find($info['school_id']);
            $this->assign('school', $school);

            $school_list = M('School')->field('id,name')->where(array('display' => 1))->select();
            $this->assign('school_list', $school_list);

            $this->_config_seo(array('title' => '申请宣讲会 - 企业会员中心 - {site_name}'));
            $this->display();
        }
    }

    /**
     * 未完善企业资料，需要先完善企业资料
     */
    private function redirect_cominfo() {
        if (!$this->cominfo_flge) {
            if (IS_AJAX) {
                $this->ajaxReturn(0, '为了达到更好的效果，请先完善您的企业资料！');
            } else {
                $this->error('为了达到更好的效果，请先完善您的企业资料！', U('company/com_info'));
            }
        }
    }
}