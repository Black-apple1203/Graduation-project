<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class CompanyCancellationController extends BackendController {
    /**
     * 申请列表
     */
    public function index() {
        $this->_name = 'CompanyCancellationApply';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre . 'company_cancellation_apply';

        $settr = I('get.settr', 0, 'intval');
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key && $key_type > 0) {
            switch ($key_type) {
                case 1:
                    $where['m.username'] = array('like', $key . '%');
                    break;
                case 2:
                    $where[$table_name . '.uid'] = array('eq', $key);
                    break;
                case 3:
                    $where[$table_name . '.companyname'] = array('like', $key . '%');
                    break;
            }
        } else {
            if ($settr > 0) {
                $tmpsettr = strtotime("-" . $settr . " day");
                $where['addtime'] = array('gt', $tmpsettr);
            }
        }

        $this->join = 'join ' . $db_pre . "members as m on " . $table_name . ".uid=m.uid";
        $this->field = $table_name . '.*,m.username,m.mobile';
        $this->where = $where;

        parent::index();
    }

    /**
     * 处理申请
     */
    public function handle() {
        $apply_id = I('get.id', 0, 'intval');

        $ret = D('CompanyProfile')->company_cancellation($apply_id);
        if (!$ret) {
            $this->ajaxReturn(0, '申请处理失败！');
        }

        $this->ajaxReturn(1, '申请处理成功');
    }

    /**
     * 删除申请(硬删除)
     */
    public function del() {
        $apply_id = I('get.id', 0, 'intval');

        $ret = M('CompanyCancellationApply')->delete($apply_id);
        if (!$ret) {
            $this->ajaxReturn(0, '删除失败');
        }

        $this->ajaxReturn(1, '删除成功');
    }
}