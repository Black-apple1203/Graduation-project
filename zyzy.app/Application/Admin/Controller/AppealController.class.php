<?php
namespace Admin\Controller;

use Common\Controller\BackendController;

class AppealController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('MembersAppeal');
        $this->_name = 'MembersAppeal';
    }

    public function _before_index() {
        $this->assign('count', parent::_pending('MembersAppeal', array('status' => 0)));
    }

    public function _before_search($map) {
        $settr = I('get.settr', 0, 'intval');
        if ($settr) {
            $map['addtime'] = array('egt', strtotime('-' . $settr . ' day'));
        }
        $this->order = $this->order = 'status asc, id desc';
        return $map;
    }

    public function set_status() {
        $id = I('request.id');
        $status = I('request.status', 0, 'intval');
        if (empty($id)) {
            $this->error('请选择记录！');
        }
        !is_array($id) && $id = array($id);
        $r = $this->_mod->where(array('id' => array('in', $id)))->setField('status', $status);
        if ($r) {
            $this->success('设置成功！响应行数' . $r);
        } else {
            $this->error('设置失败！');
        }
    }
}

?>