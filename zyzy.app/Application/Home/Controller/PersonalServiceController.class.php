<?php
namespace Home\Controller;

use Home\Controller\PersonalController;

class PersonalServiceController extends PersonalController {
    public $uid;
    public $my_points;
    public $timestamp;

    public function _initialize() {
        parent::_initialize();
        $this->uid = C('visitor.uid');
        $this->my_points = D('MembersPoints')->get_user_points($this->uid);
        $this->timestamp = time();
    }

    /**
     * 我的积分
     */
    public function index() {
        $operate = I('request.operate', 1, 'intval');
        $model = D('MembersHandsel');
        $pagesize = 10;
        $count = $model->where(array('operate' => $operate, 'uid' => array('eq', $this->uid)))->count('id');
        $pager = pager($count, $pagesize);
        $page = $pager->fshow();
        $list = $model->where(array('operate' => $operate, 'uid' => array('eq', $this->uid)))->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        $this->assign('list', $list);
        $this->assign("page", $page);
        $this->assign("operate", $operate);
        $this->assign('mypoints', $this->my_points);
        $this->assign('points_count', D('TaskLog')->count_task_points($this->uid, C('visitor.utype')));
        $this->_config_seo(array('title' => '我的' . C('qscms_points_byname') . ' - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->display('Personal/service/index');
    }

    /**
     * 我的任务
     */
    public function task() {
        $this->assign('task_url', D('Task')->task_url(C('visitor.utype')));
        $this->assign('done_task', D('TaskLog')->get_done_task($this->uid));
        $this->assign('task', D('Task')->get_task_cache(2));
        $this->assign('mypoints', $this->my_points);
        $this->assign('points_count', D('TaskLog')->count_task_points($this->uid, C('visitor.utype')));
        $this->_config_seo(array('title' => '我的任务 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->display('Personal/service/task');
    }

    /**
     * 增值服务
     */
    public function increment() {
        $this->_config_seo(array('title' => '增值服务 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->display('Personal/service/increment');
    }

    /**
     * 添加增值服务
     */
    public function increment_add($cat = '') {
        $payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), 'or')))->order('listorder desc')->select();
        $cat = $cat ? $cat : I('get.cat', 'stick', 'trim');
        switch ($cat) {
            case 'stick':
                $this->assign('choose_arr', M('PersonalServiceStick')->order('sort desc')->select());
                $resume = D('Resume')->get_resume_list(array('where' => array('uid' => $this->uid), 'field' => 'id,title,audit,stick'));
                $this->assign('resume', $resume);
                $tpl_name = 'increment_add_stick';
                break;
            case 'tag':
                $this->assign('tag_arr', M('PersonalServiceTagCategory')->order('sort desc')->select());
                $this->assign('choose_arr', M('PersonalServiceTag')->order('sort desc')->select());
                $resume = D('Resume')->get_resume_list(array('where' => array('uid' => $this->uid), 'field' => 'id,title,audit,strong_tag'));
                $this->assign('resume', $resume);
                $tpl_name = 'increment_add_tag';
                break;
            case 'tpl':
                $choose_arr = M('Tpl')->where(array('tpl_type' => 2, 'tpl_display' => 1))->select();
                foreach ($choose_arr as $key => $value) {
                    $choose_arr[$key]['thumb_dir'] = __RESUME__ . '/' . $value['tpl_dir'];
                }
                $def_resume = D('Resume')->where(array('uid' => C('visitor.uid'), 'def' => 1))->find();
                $this->assign('choose_arr', $choose_arr);
                $this->assign('def_resume', $def_resume);
                $tpl_name = 'increment_add_tpl';
                break;
        }
        $resume_id = I('request.resume_id') ? I('request.resume_id') : '';
        $resume_id = $resume_id ? $resume_id : I('request.rid');
        $this->assign('payment', $payment);
        $this->assign('mypoints', $this->my_points);
        $this->assign('payment_rate', C('qscms_payment_rate'));
        $this->_config_seo(array('title' => '增值服务 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->assign('resume_id', $resume_id);
        if (!IS_AJAX) {
            $this->display('Personal/service/' . $tpl_name);
        }
    }

    /**
     * 添加增值包订单
     */
    public function increment_add_save() {
        //根据不同的支付形式走不同的逻辑代码
        $cat = I('request.type', '', 'trim,badword');
        if ($cat == '') {
            $this->notice('参数错误');
        }
        $func_name = '_increment_add_save_' . $cat;
        $function_arr = array('stick', 'tag', 'tpl');
        if (in_array($cat, $function_arr)) {
            $this->$func_name();
        } else {
            $this->notice('参数错误');
        }
    }

    /**
     * 增值服务支付 - 简历置顶
     */
    public function _increment_add_save_stick() {
        $cat = 'stick';
        $order_pay_type = 3;
        $payment_name = I('post.payment_name', '', 'trim,badword');
        $pay_type = I('post.pay_type', 'points', 'trim,badword');
        $days = I('post.days', 0, 'intval');
        $stick_days = M('PersonalServiceStick')->getfield('days,id');
        if(!$stick_days[$days]){
            $this->notice('参数错误！');
        } 
        $resume_id=I('post.resume_id',0,'intval');
        $is_deductible=I('post.is_deductible',0,'intval');
        $deductible=I('post.deductible','','floatval');
        $amount=I('post.amount','','floatval');
        if($resume_id==0){
            $this->notice('请选择简历！');
        }
        if ($days == 0) {
            $this->notice('请选择置顶天数！');
        }
        if (D('PersonalServiceStickLog')->check_stick_log(array('resume_id' => $resume_id))) {
            $this->notice('您已购买过此服务！');
        }
        if ($amount == 0) {
            $pay_type = 'points';
        }
        $service_need_points = M('PersonalServiceStick')->where(array('days' => array('eq', $days)))->getField('points');
        $service_need_cash = round($service_need_points / C('qscms_payment_rate'), 2);
        $htype_cn = D('Order')->order_type[$order_pay_type] . $days . "天";
        //=================纯积分支付================
        if ($pay_type == 'points') {
            $setsqlarr['points'] = $service_need_points;
            $setsqlarr['resume_id'] = $resume_id;
            $setsqlarr['days'] = $days;
            if ($this->my_points < $service_need_points) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }
            $setsqlarr['resume_uid'] = $this->uid;
            $setsqlarr['endtime'] = strtotime("+{$setsqlarr['days']} day");
            $rst = D('PersonalServiceStickLog')->add_stick_log($setsqlarr);
            if ($rst['state'] == 1) {
                $oid = "P-" . date('ymd', time()) . "-" . date('His', time());//订单号
                $refreshtime = D('Resume')->where(array('id' => $resume_id))->getfield('refreshtime');
                $stime = intval($refreshtime) + 100000000;
                D('Resume')->where(array('id' => $resume_id))->save(array('stick' => 1, 'stime' => $stime));
                D('ResumeSearchPrecise')->where(array('id' => $resume_id))->setField('stime', $stime);
                D('ResumeSearchFull')->where(array('id' => $resume_id))->setField('stime', $stime);
                $description = '购买服务：' . $htype_cn . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
                $order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $htype_cn, 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, 0, $this->timestamp, serialize(array('days' => $days)));
                /* 会员日志 */
                write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));

                $p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
                if ($p_rst) {
                    /* 会员日志 */
                    write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
                    write_members_log(C('visitor'), 'increment', '开通增值服务【' . $htype_cn . '】，支付方式：' . C('qscms_points_byname') . '兑换');
                    $handsel['uid'] = $this->uid;
                    $handsel['htype'] = '';
                    $handsel['htype_cn'] = $htype_cn;
                    $handsel['operate'] = 2;
                    $handsel['points'] = $service_need_points;
                    $handsel['addtime'] = time();
                    D('MembersHandsel')->members_handsel_add($handsel);
                }
                $this->ajaxReturn(1, '支付成功！');
            } else {
                $this->ajaxReturn(0, $rst['error']);
            }
        } //=================现金积分支付================
        else if ($pay_type == 'cash') {
            if ($this->my_points < $deductible && $is_deductible) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }

            $params['resume_id'] = $resume_id;
            $params['days'] = $days;
            $params['points'] = $service_need_points;
            $params = serialize($params);
            $this->_call_cash_pay($htype_cn, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, $params);
        }
    }

    /**
     * 增值服务支付 - 醒目标签
     */
    public function _increment_add_save_tag() {
        $cat = 'tag';
        $order_pay_type = 4;
        $payment_name = I('post.payment_name', '', 'trim,badword');
        $pay_type = I('post.pay_type', 'points', 'trim,badword');
        $tagid = I('post.tagid', 0, 'intval');
        $days = I('post.days', 0, 'intval');
        $tag_days = M('PersonalServiceTag')->getfield('days,id');
        if(!$tag_days[$days]){
            $this->notice('参数错误！');
        }
        $resume_id = I('post.resume_id', 0, 'intval');
        $is_deductible = I('post.is_deductible', 0, 'intval');
        $deductible = I('post.deductible', '', 'floatval');
        $amount = I('post.amount', '', 'floatval');
        if ($resume_id == 0) {
            $this->notice('请选择简历！');
        }
        if ($tagid == 0) {
            $this->notice('请选择标签！');
        }
        if ($days == 0) {
            $this->notice('请选择天数！');
        }
        if (D('PersonalServiceTagLog')->check_tag_log(array('resume_id' => $resume_id))) {
            $this->notice('您已购买过此服务！');
        }
        if ($amount == 0) {
            $pay_type = 'points';
        }
        $service_need_points = M('PersonalServiceTag')->where(array('days' => array('eq', $days)))->getField('points');
        $service_need_cash = round($service_need_points / C('qscms_payment_rate'), 2);
        $htype_cn = D('PersonalServiceTagCategory')->where(array('id' => $tagid))->getField('name');
        //=================纯积分支付================
        if ($pay_type == 'points') {
            $setsqlarr['points'] = $service_need_points;
            $setsqlarr['resume_id'] = $resume_id;
            $setsqlarr['tag_id'] = $tagid;
            $setsqlarr['days'] = $days;
            if ($this->my_points < $service_need_points) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }
            $setsqlarr['resume_uid'] = $this->uid;
            $setsqlarr['endtime'] = strtotime("+{$setsqlarr['days']} day");
            $rst = D('PersonalServiceTagLog')->add_tag_log($setsqlarr);
            if ($rst['state'] == 1) {
                $oid = "P-" . date('ymd', time()) . "-" . date('His', time());//订单号
                D('Resume')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->setField('strong_tag', $tagid);
                $description = '购买服务：' . $htype_cn . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
                $order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $htype_cn, 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, 0, $this->timestamp, serialize(array('days' => $days)));

                /* 会员日志 */
                write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));

                $p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
                if ($p_rst) {
                    /* 会员日志 */
                    write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
                    write_members_log(C('visitor'), 'increment', '开通增值服务【' . $htype_cn . '】，支付方式：' . C('qscms_points_byname') . '兑换');
                    $handsel['uid'] = $this->uid;
                    $handsel['htype'] = '';
                    $handsel['htype_cn'] = $htype_cn;
                    $handsel['operate'] = 2;
                    $handsel['points'] = $service_need_points;
                    $handsel['addtime'] = time();
                    D('MembersHandsel')->members_handsel_add($handsel);
                }
                $this->ajaxReturn(1, '支付成功！');
            } else {
                $this->ajaxReturn(0, $rst['error']);
            }
        } //=================现金积分支付================
        else if ($pay_type == 'cash') {
            if ($this->my_points < $deductible && $is_deductible) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }

            $params['resume_id'] = $resume_id;
            $params['tag_id'] = $tagid;
            $params['days'] = $days;
            $params['points'] = $service_need_points;
            $params = serialize($params);
            $this->_call_cash_pay($htype_cn, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, $params);
        }
    }

    /**
     * 增值服务支付 - 简历模板
     */
    public function _increment_add_save_tpl() {
        $cat = 'tpl';
        $order_pay_type = 5;
        $payment_name = I('post.payment_name', '', 'trim,badword');
        $pay_type = I('post.pay_type', 'points', 'trim,badword');
        $tplid = I('post.tplid', 0, 'intval');
        $is_deductible = I('post.is_deductible', 0, 'intval');
        $deductible = I('post.deductible', '', 'floatval');
        $amount = I('post.amount', '', 'floatval');
        if ($tplid == 0) {
            $this->notice('请选择模板！');
        }
        if ($amount == 0) {
            $pay_type = 'points';
        }
        $service_need_points = M('Tpl')->where(array('tpl_id' => array('eq', $tplid)))->getField('tpl_val');
        $service_need_cash = round($service_need_points / C('qscms_payment_rate'), 2);
        $htype_cn = '简历模板名称：' . D('Tpl')->where(array('tpl_id' => $tplid))->getField('tpl_name');
        //=================纯积分支付================
        if ($pay_type == 'points') {
            if ($this->my_points < $service_need_points) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }
            $setsqlarr['tplid'] = $tplid;
            $setsqlarr['uid'] = $this->uid;
            $rst = D('ResumeTpl')->add_resume_tpl($setsqlarr);
            if ($rst['state'] == 1) {
                $oid = "P-" . date('ymd', time()) . "-" . date('His', time());//订单号
                $description = '购买服务：' . $htype_cn . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
                $order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $htype_cn, 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, 0, $this->timestamp);

                /* 会员日志 */
                write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));

                $p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
                if ($p_rst) {
                    /* 会员日志 */
                    write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
                    write_members_log(C('visitor'), 'increment', '开通增值服务【' . $htype_cn . '】，支付方式：' . C('qscms_points_byname') . '兑换');
                    $handsel['uid'] = $this->uid;
                    $handsel['htype'] = '';
                    $handsel['htype_cn'] = $htype_cn;
                    $handsel['operate'] = 2;
                    $handsel['points'] = $service_need_points;
                    $handsel['addtime'] = time();
                    D('MembersHandsel')->members_handsel_add($handsel);
                }
                $this->ajaxReturn(1, '支付成功！');
            } else {
                $this->ajaxReturn(0, $rst['error']);
            }
        } //=================现金积分支付================
        else if ($pay_type == 'cash') {
            if ($this->my_points < $deductible && $is_deductible) {
                $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
            }

            $params['tplid'] = $tplid;
            $params = serialize($params);
            $this->_call_cash_pay($htype_cn, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, $params);
        }
    }

    protected function notice($message) {
        if (IS_AJAX) {
            $this->ajaxReturn(0, $message);
        } else {
            $this->error($message);
        }
    }

    /**
     * 启动现金支付
     */
    protected function _call_cash_pay($htype_cn, $order_pay_type, $payment_name = '', $amount = '0.0', $is_deductible = 0, $deductible = 0, $params = '') {
        $paymenttpye = D('Payment')->get_payment_info($payment_name);
        if (!$paymenttpye) $this->notice("支付方式错误！");
        if ($is_deductible == 0) {
            $deductible = 0;
        }
        if ($this->my_points < $deductible) {
            $this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
        }
        if ($deductible > 0) {
            $m_amount = $amount - floatval($deductible / C('qscms_payment_rate'));
        } else {
            $m_amount = $amount;
        }


        $paysetarr['ordtotal_fee'] = $m_amount;
        $description = '购买服务：' . $htype_cn;
        $description .= ';' . $paymenttpye['byname'] . $paysetarr['ordtotal_fee'];

        if ($deductible > 0) {
            $description .= ';' . C('qscms_points_byname') . '支付：' . $deductible . C('qscms_points_byname');
        }
        $paysetarr['oid'] = strtoupper(substr($paymenttpye['typename'], 0, 1)) . "-" . date('ymd', time()) . "-" . date('His', time());//订单号
        $insert_id = D('Order')->add_order(C('visitor'), $paysetarr['oid'], $order_pay_type, $amount, $paysetarr['ordtotal_fee'], $deductible, $htype_cn, $payment_name, $paymenttpye['byname'], $description, $this->timestamp, 1, 0, 0, 0, $params);

        if ($deductible > 0 && $is_deductible == 1) {
            $log_payment = $paymenttpye['byname'] . '+' . C('qscms_points_byname') . '抵扣';
        } else {
            $log_payment = $paymenttpye['byname'];
        }
        write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $paysetarr['oid'] . '），支付方式：' . $log_payment, false, array('order_id' => $insert_id));
        $paysetarr['payFrom'] = 'pc';
        $paysetarr['type'] = $payment_name;
        $paysetarr['ordsubject'] = $htype_cn;
        $paysetarr['ordbody'] = $htype_cn;
        $r = D('Payment')->pay($paysetarr);
        if(!$r['state']) $this->ajaxReturn(0,$r['msg']);
        if ($payment_name == 'wxpay') {
            fopen(QSCMS_DATA_PATH . 'wxpay/' . $paysetarr['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
            $_SESSION['wxpay_no'] = $paysetarr['oid'];
            $this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
        }
    }

    public function check_weixinpay_notify() {
        if (file_exists(QSCMS_DATA_PATH . 'wxpay/' . $_SESSION['wxpay_no'] . '.tmp')) {
            $this->ajaxReturn(0, '回调成功');
        } else {
            $order = D('Order')->where(array('oid' => $_SESSION['wxpay_no']))->find();
            unset($_SESSION['wxpay_no']);
            $this->ajaxReturn(1, '回调成功', U('order_detail', array('id' => $order['id'])));
        }
    }

    /**
     * 取消订单
     */
    public function order_cancel() {
        $id = I('request.id', 0, 'intval');
        if (!$id) {
            $this->ajaxReturn(0, '参数错误！');
        }
        if (IS_POST) {
            $order_info = D('Order')->where(array('id' => $id, 'uid' => $this->uid))->find();
            if (!$order_info) {
                $this->ajaxReturn(0, '没有找到对应的订单！');
            } else if ($order_info['is_paid'] != 1) {
                $this->ajaxReturn(0, '该订单不允许取消！');
            }
            $rst = D('Order')->where(array('id' => $id, 'uid' => $this->uid))->setField('is_paid', 3);
            if ($rst) {
                write_members_log(C('visitor'), 'order', '取消订单（订单号：' . $order_info['oid'] . '）', false, array('order_id' => $id));
                $this->ajaxReturn(1, '取消订单成功！');
            } else {
                $this->ajaxReturn(0, '取消订单失败！');
            }
        } else {
            $tip = '您确定要取消该订单吗？';
            $description = '如果您在支付过程中遇到问题，可以联系网站客服，联系电话：' . C("qscms_bootom_tel") . '。';
            $this->ajax_warning($tip, $description);
        }
    }

    /**
     * 删除订单
     */
    public function order_delete() {
        $id = I('request.id', 0, 'intval');
        if (!$id) {
            $this->ajaxReturn(0, '参数错误！');
        }
        if (IS_POST) {
            $source = D('Order')->where(array('id' => $id, 'uid' => $this->uid));
            $info = $source->find();
            if ($info['is_paid'] == 2) {
                $this->ajaxReturn(0, '已完成的订单不允许删除！');
            }
            $rst = $source->delete();
            if (!$rst) {
                $this->ajaxReturn(0, '删除失败！');
            }
            write_members_log(C('visitor'), '', '删除订单（订单号：' . $info['oid'] . '）');
            $this->ajaxReturn(1, '删除订单成功！');
        } else {
            $tip = '订单被删除后无法恢复，您确定要删除该订单吗？';
            $this->ajax_warning($tip);
        }
    }

    /**
     * 订单列表
     */
    public function order_list() {
        $is_paid = I('get.is_paid', 0, 'intval');
        $whereo['uid'] = $this->uid;
		$whereo['order_type']=array(array('eq',3),array('eq',4),array('eq',5),'or');
        //订单状态
        if ($is_paid > 0) {
            $this->assign('is_paid', $is_paid);
            $whereo['is_paid'] = $is_paid;
        }
        $perpage = 10;
        $total_val = M('Order')->where($whereo)->count();
        $pager = pager($total_val, $perpage);
        $rst['list'] = M('Order')->where($whereo)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        $rst['page'] = $pager->fshow();
        $this->assign('is_paid', $is_paid);
        $this->assign('order', $rst);
        $this->assign('payment', D('Payment')->get_cache());
        $this->_config_seo(array('title' => '订单记录 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->display('Personal/service/order_list');
    }

    /**
     * 商品兑换订单列表
     */
    public function order_list_goods() {
        if (!isset($this->apply['Mall'])) $this->_empty();
        $status = I('get.status', 0, 'intval');
        if ($status > 0) {
            $where['status'] = $status;
        }
        $where['uid'] = C('visitor.uid');
        $order = D('Mall/MallOrder')->get_order_list($where);
        $this->assign('order', $order);
        $this->assign('personal_nav', 'service');
        $this->_config_seo(array('title' => '我的订单 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display('Personal/service/order_list_goods');
    }

    /**
     * 订单详情
     */
    public function order_detail() {
        $id = I('get.id', 0, 'intval');
        if ($id == 0) {
            $this->error('参数错误！');
        }
        if (!I('get.org', '', 'trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']) {
            redirect(build_mobile_url(array('c' => 'PersonalService', 'a' => 'order_detail', 'params' => 'order_id=' . intval($_GET['id']))));
        }
        $order = D('Order')->get_order_one(array('id' => $id));
        $this->assign('order', $order);
        $this->assign('order_type_cn', D('Order')->order_type[$order['order_type']]);
        $this->assign('mypoints', $this->my_points);
        $this->assign('payment_rate', C('qscms_payment_rate'));
        $payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), 'or')))->order('listorder desc')->select();
        $this->assign('payment', $payment);
        switch ($order['order_type']) {
            //简历置顶
            case 3:
                $this->assign('cat', 'stick');
                break;
            //醒目标签
            case 4:
                $this->assign('cat', 'tag');
                break;
            //简历模板
            case 5:
                $this->assign('cat', 'tpl');
                break;
        }
        $this->_config_seo(array('title' => '订单记录 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'service');
        $this->display('Personal/service/order_detail');

    }

    public function order_pay_repeat() {
        $id = I('get.id', 0, 'intval');
        if ($id == 0) {
            $this->notice('参数错误！');
        }
        $info = D('Order')->get_order_one(array('id' => $id));
        if ($info['pay_points'] > 0 && $this->my_points < $info['pay_points']) {
            $this->notice(C('qscms_points_byname') . '不足，无法完成支付，请重新下单！');
        }
        $paysetarr['ordtotal_fee'] = $info['pay_amount'];
        $paysetarr['oid'] = $info['oid'];
        $paysetarr['payFrom'] = 'pc';
        $paysetarr['type'] = $info['payment'];
        $paysetarr['ordsubject'] = $info['service_name'];
        $paysetarr['ordbody'] = $info['service_name'];
        $r = D('Payment')->pay($paysetarr);
        if(!$r['state']) $this->ajaxReturn(0,$r['msg']);
        if ($info['payment'] == 'wxpay') {
            fopen(QSCMS_DATA_PATH . 'wxpay/' . $paysetarr['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
            $_SESSION['wxpay_no'] = $paysetarr['oid'];
            $this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
        }
    }

    /**
     * 等待确认支付状态
     */
    public function confirm_pay_status() {
        $tip = '请在新打开的支付页面完成付款';
        $description = '付款完成前请不要关闭此窗口，付款后请根据您的情况点击下面的按钮，如果在支付中遇到问题请到<a target="_blank" href="' . url_rewrite("QS_help") . '">帮助中心</a>。';
        $this->ajax_warning($tip, $description);
    }

    /**
     * 简历置顶
     */
    public function resume_stick() {
        $this->increment_add('stick');
        $html = $this->fetch('Personal/ajax_tpl/ajax_resume_stick');
        $this->ajaxReturn(1, $html);
    }

    /**
     * 简历标签
     */
    public function resume_tag() {
        $this->increment_add('tag');
        $html = $this->fetch('Personal/ajax_tpl/ajax_resume_tag');
        $this->ajaxReturn(1, $html);
    }
}

?>