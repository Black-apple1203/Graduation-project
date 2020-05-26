<?php
namespace Admin\Controller;

use Common\Controller\ConfigbaseController;

class SetPerController extends ConfigbaseController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
        $this->_name = 'Config';
    }

    public function index() {
        if (IS_POST) {
            parent::_edit();
        } else {
            parent::edit();
        }
    }

    public function config_task() {
        $model = D('Task');
        if (IS_POST) {
            $idArr = I('post.id');
            $pointsArr = I('post.points');
            $timesArr = I('post.times');
            foreach ($idArr as $key => $val) {
                $data['points'] = $pointsArr[$key];
                $data['times'] = $timesArr[$key];
                $data['status'] = intval($_POST['status_' . $val]);
                $model->where(array('id' => $val))->save($data);
                unset($data);
            }
            $this->returnMsg(1,L('operation_success'));
            exit;
        }
        $list = $model->where(array('utype' => array('eq', 2)))->select();
		$commonlist = $model->where(array('utype' => array('eq', 0)))->select();//公共任务
        $this->assign('list', $list);
		$this->assign('commonlist', $commonlist);//公共任务
        $this->display();
    }

    public function login_remind() {
        parent::_edit();
        $this->display();
    }

    public function search() {
        parent::_edit();
        $this->display();
    }

    public function set_audit() {
        parent::_edit();
        $this->display();
    }
}