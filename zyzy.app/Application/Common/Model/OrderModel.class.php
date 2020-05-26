<?php
namespace Common\Model;

use Think\Model;

class OrderModel extends Model {
    public $order_type = array(1 => '套餐升级', 2 => '充值积分', 3 => '简历置顶', 4 => '醒目标签', 5 => '简历模板', 6 => '简历包', 7 => '短信包', 8 => '职位置顶', 9 => '职位紧急', 10 => '企业模板', 11 => '诚聘通', 12 => '预约刷新职位', 13 => '职位刷新', 14 => '简历下载');
    protected $_validate = array(
        array('uid,oid,utype,order_type,pay_type,payment,payment_cn,service_name,description', 'identicalNull', '', 1, 'callback'),
        array('uid,utype,order_type,pay_type', 'identicalEnum', '', 0, 'callback'),
    );
    protected $_auto = array(
        array('is_paid', 1),
        array('addtime', 'time', 1, 'function'),
        array('fee', 0, 1)
    );

    /*
        获取单条订单信息
        @data 数组
    */
    public function get_order_one($data) {
        $info = $this->where($data)->find();
        if ($info['params']) {
            $info['params'] = unserialize($info['params']);
        }
        return $info;
    }

    public function edit_order($id, $uid, $is_paid, $pay_amount = 0, $pay_points = 0, $payment = '', $payment_cn = '', $description = '', $payment_time = 0) {
        if ($pay_amount > 0 && $pay_points > 0) {
            $setsqlarr['pay_type'] = 3;
        } elseif ($pay_amount > 0) {
            $setsqlarr['pay_type'] = 2;
        } else {
            $setsqlarr['pay_type'] = 1;
        }
        $setsqlarr['is_paid'] = $is_paid;
        $setsqlarr['pay_amount'] = $pay_amount;
        $setsqlarr['pay_points'] = $pay_points;
        $setsqlarr['payment'] = $payment;
        $setsqlarr['payment_cn'] = $payment_cn;
        $setsqlarr['description'] = $description;
        $setsqlarr['payment_time'] = $payment_time;
        $this->where(array('id' => $id, 'uid' => $uid))->save($setsqlarr);
    }

    /**
     * 添加订单数据
     * @param $user           用户信息
     * @param $oid            订单号
     * @param $order_type     订单类型，详见D('Order')->order_type
     * @param $amount         总金额
     * @param $pay_amount     现金支付金额
     * @param $pay_points     积分支付数
     * @param $service_name   所购买服务名称
     * @param $payment        支付方式英文
     * @param $payment_cn     支付方式中文
     * @param $description    订单详情描述
     * @param $addtime      下单时间
     * @param $is_paid 1待支付 2已支付
     * @param $points         购买积分数
     * @param $setmeal        购买套餐/增值服务id
     * @param $payment_time   支付时间
     * @param $params         需要特殊处理的参数序列化
     * @param $notes          备注
     */
    public function add_order($user, $oid, $order_type, $amount, $pay_amount, $pay_points, $service_name, $payment, $payment_cn, $description, $addtime, $is_paid = 1, $points = 0.0, $setmeal = 0, $payment_time = 0, $params = '', $discount = '', $notes = '',$pay_gift=0.0,$gift_id=0) {///************chm----start******       ,$pay_gift        ****/
		$uid = $user['uid'];
        $setsqlarr['oid'] = $oid;
        $setsqlarr['uid'] = $uid;
        $setsqlarr['utype'] = $user['utype'];
        $setsqlarr['order_type'] = $order_type;
        if ($pay_amount > 0 && $pay_points > 0 && $pay_gift > 0) {
			$setsqlarr['pay_type'] = 7;//现金+积分+优惠券
		} elseif($pay_gift > 0 && $pay_points > 0) {
			$setsqlarr['pay_type'] = 6;//积分+优惠券
		} elseif($pay_amount > 0 && $pay_gift > 0) {
			$setsqlarr['pay_type'] = 5;//现金+优惠券
		} elseif($pay_amount > 0 && $pay_points > 0) {
			$setsqlarr['pay_type'] = 3;//现金+积分
		} elseif ($pay_amount > 0) {
			$setsqlarr['pay_type'] = 2;//现金
		} elseif ($pay_gift > 0) {
			$setsqlarr['pay_type'] = 4;//优惠券
		} elseif ($pay_points > 0) {
			$setsqlarr['pay_type'] = 1;//积分
		}
        $setsqlarr['is_paid'] = $is_paid;
        $setsqlarr['amount'] = $amount;
        $setsqlarr['pay_amount'] = $pay_amount;
        $setsqlarr['pay_points'] = $pay_points;
        $setsqlarr['pay_gift'] = $pay_gift?:0;
        $setsqlarr['gift_id'] = $gift_id?:0;
		$gift_issue_info=M("GiftIssue")->where(array("id"=>$gift_id))->find();
        $gift_issue_info['gift_info']=M("Gift")->where(array("id"=>$gift_issue_info['gift_id']))->find();
		if($gift_issue_info['gift_type']==1){
			$gift_pay_name="专享优惠券";
		}elseif($gift_issue_info['gift_type']==2){
			$gift_pay_name="新用户专享券";
		}elseif($gift_issue_info['gift_type']==3){
			$gift_pay_name="活动专享券";
		}
		
        $setsqlarr['payment'] = $payment;
        $setsqlarr['payment_cn'] = $payment_cn;
        $setsqlarr['description'] = $description;
        $setsqlarr['service_name'] = $service_name;
        $setsqlarr['points'] = $points;
        $setsqlarr['setmeal'] = $setmeal;
        $setsqlarr['params'] = $params;
        $setsqlarr['notes'] = $notes;
        $setsqlarr['addtime'] = $addtime;
        $setsqlarr['payment_time'] = $payment_time;
        $setsqlarr['discount'] = $discount;
        $payment_model = D('Payment')->where(array('typename' => $payment))->find();
        if ($payment_model && $pay_amount > 0) {
            $setsqlarr['fee'] = $pay_amount * $payment_model['fee'] / 100;
        } else {
            $setsqlarr['fee'] = 0;
        }
        if (false === $this->create($setsqlarr)) return false;
        $insert_id = $this->add($setsqlarr);
        $userinfo = M('Members')->where(array('uid' => $uid))->find();
        if (false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
        if ($is_paid == 1) {
            if($user['utype']==1){
                if(C('qscms_site_dir')=='/'){
                $replac_mail['order_url']=rtrim(C('qscms_site_domain').C('qscms_site_dir'),'/').U('Home/CompanyService/order_detail',array('id'=>$insert_id));
                }else{
                $replac_mail['order_url']=rtrim(C('qscms_site_domain'),'/').U('Home/CompanyService/order_detail',array('id'=>$insert_id));
                }
            }else{
                if(C('qscms_site_dir')=='/'){
                $replac_mail['order_url']=rtrim(C('qscms_site_domain').C('qscms_site_dir'),'/').U('Home/PersonalService/order_detail',array('id'=>$insert_id));
                }else{
                $replac_mail['order_url']=rtrim(C('qscms_site_domain'),'/').U('Home/PersonalService/order_detail',array('id'=>$insert_id));
                }
            }
            $pms_message = '订单' . $oid . '已经添加成功，付款方式为：' . $payment_cn . '，应付金额' . $pay_amount . ($pay_points > 0 ? ('(抵扣' . $pay_points . C('qscms_points_byname') . ')') : '')  . ($pay_gift > 0 ? ('('.$gift_pay_name.'抵扣' . $pay_gift. '元)') : '') .  '。<a href="' . $replac_mail['order_url'] . '" target="_blank">点击查看详情>></a>';
            //发送站内信
            D('Pms')->write_pmsnotice($user['uid'], $user['username'], $pms_message,$user['utype']);
            //sms
            if ($sms['set_order'] == "1" && $amount > 0) {
                $sendSms['mobile'] = $user['mobile'];
                $sendSms['tpl'] = 'set_order';
                $sendSms['data'] = array('sitename' => C('qscms_site_name'), 'sitedomain' => C('qscms_site_name'), 'paymenttpye' => $payment_cn, 'oid' => $oid, 'amount' => $amount . '');
                D('Sms')->sendSms('notice', $sendSms);
            }
            //微信
            if (C('apply.Weixin')) {
                D('Weixin/TplMsg')->add_order($setsqlarr['uid'], $setsqlarr['oid'], $setsqlarr['description'], $setsqlarr['amount']);
            }
        } else if ($is_paid == 2) {
            if($user['utype']==1){
                if(C('qscms_site_dir')=='/'){
                $replac_mail['order_url']=rtrim(C('qscms_site_domain').C('qscms_site_dir'),'/').U('Home/CompanyService/order_detail',array('id'=>$insert_id));
                }else{
                $replac_mail['order_url']=rtrim(C('qscms_site_domain'),'/').U('Home/CompanyService/order_detail',array('id'=>$insert_id));
                }
            }else{
                if(C('qscms_site_dir')=='/'){
                $replac_mail['order_url']=rtrim(C('qscms_site_domain').C('qscms_site_dir'),'/').U('Home/PersonalService/order_detail',array('id'=>$insert_id));
                }else{
                $replac_mail['order_url']=rtrim(C('qscms_site_domain'),'/').U('Home/PersonalService/order_detail',array('id'=>$insert_id));
                }
            }
            $pms_message = '订单' . $oid . '已经支付成功，付款方式为：' . $payment_cn . '，支付金额' . $pay_amount . ($pay_points > 0 ? ('(抵扣' . $pay_points . C('qscms_points_byname') . ')') : '') . ($pay_gift > 0 ? ('('.$gift_pay_name.'抵扣' . $pay_gift. '元)') : '') .  '。<a href="' . $replac_mail['order_url'] . '" target="_blank">点击查看详情>></a>';
            //发送站内信
            D('Pms')->write_pmsnotice($user['uid'], $user['username'], $pms_message,$user['utype']);
            //sms
            if ($sms['set_payment'] == "1" && $amount > 0) {
                $sendSms['mobile'] = $user['mobile'];
                $sendSms['tpl'] = 'set_payment';
                $sendSms['data'] = array('sitename' => C('qscms_site_name'), 'sitedomain' => C('qscms_site_domain'));
                D('Sms')->sendSms('notice', $sendSms);
            }
            //微信
            if (C('apply.Weixin')) {
                D('Weixin/TplMsg')->set_payment($setsqlarr['uid'], $setsqlarr['oid'], $setsqlarr['description'], $setsqlarr['amount']);
            }
        }
        return $insert_id;
    }

    /**
     * 简历置顶回调
     */
    protected function order_paid_resume_stick($order, $user) {
        $params = unserialize($order['params']);
        $setsqlarr['resume_id'] = $params['resume_id'];
        $setsqlarr['resume_uid'] = $order['uid'];
        $setsqlarr['days'] = $params['days'];
        $setsqlarr['points'] = $params['points'];
        $setsqlarr['addtime'] = time();
        $setsqlarr['endtime'] = strtotime("+{$params['days']} day");
        $uid = $order['uid'];
        $deductible = $order['pay_points'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $rst = D('PersonalServiceStickLog')->add_stick_log($setsqlarr);
        if ($rst['state'] == 1) {
            $refreshtime = D('Resume')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->getfield('refreshtime');
            $stime = intval($refreshtime) + 100000000;
            D('Resume')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->save(array('stick' => 1, 'stime' => $stime));
            D('ResumeSearchPrecise')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->setField('stime', $stime);
            D('ResumeSearchFull')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->setField('stime', $stime);
            /* 会员日志 */

            if ($deductible > 0) {
                $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
                write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
            } else {
                $log_payment = $order['payment_cn'];
                write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
            }
            return true;
        }
        return false;
    }

    /**
     * 醒目标签回调
     */
    protected function order_paid_resume_tag($order, $user) {
        $params = unserialize($order['params']);
        $setsqlarr['resume_id'] = $params['resume_id'];
        $setsqlarr['resume_uid'] = $order['uid'];
        $setsqlarr['days'] = $params['days'];
        $setsqlarr['points'] = $params['points'];
        $setsqlarr['tag_id'] = $params['tag_id'];
        $setsqlarr['addtime'] = time();
        $setsqlarr['endtime'] = strtotime("+{$params['days']} day");
        $uid = $order['uid'];
        $deductible = $order['pay_points'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $rst = D('PersonalServiceTagLog')->add_tag_log($setsqlarr);
        if ($rst['state'] == 1) {
            D('Resume')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->setField('strong_tag', $setsqlarr['tag_id']);
            /* 会员日志 */
            if ($deductible > 0) {
                $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
                write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
            } else {
                $log_payment = $order['payment_cn'];
                write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
            }
            return true;
        }
        return false;
    }

    /**
     * 简历模板回调
     */
    protected function order_paid_resume_tpl($order, $user) {
        $params = unserialize($order['params']);
        $setsqlarr['tplid'] = $params['tplid'];
        $setsqlarr['uid'] = $order['uid'];
        $deductible = $order['pay_points'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($setsqlarr['uid'], 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $setsqlarr['uid'];
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $rst = D('ResumeTpl')->add_resume_tpl($setsqlarr);
        if ($rst['state'] == 1) {
            /* 会员日志 */
            if ($deductible > 0) {
                $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
                write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
            } else {
                $log_payment = $order['payment_cn'];
                write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
            }
            return true;
        }
        return false;
    }

    /**
     * 增值包回调 - 简历包和短信包
     */
    protected function order_paid_setmeal_increment($order, $user) {
        $uid = $order['uid'];
        $project_id = $order['setmeal'];
        $deductible = $order['pay_points'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $setmeal_increment = D('SetmealIncrement')->where(array('id' => $project_id))->find();
        if ($setmeal_increment['cat'] == 'download_resume') {
            D('MembersSetmeal')->where(array('uid' => $uid))->setInc('download_resume', $setmeal_increment['value']);
        } else if ($setmeal_increment['cat'] == 'sms') {
            D('Members')->where(array('uid' => $uid))->setInc('sms_num', $setmeal_increment['value']);
        }
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /**
     * 增值包回调 - 职位推广
     */
    protected function order_paid_job_promotion($order, $user) {
        $params = unserialize($order['params']);
        $uid = $order['uid'];
        $project_id = $order['setmeal'];
        $deductible = $order['pay_points'];
        $jobs_id = $params['jobs_id'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $setmeal_increment = D('SetmealIncrement')->where(array('id' => $project_id))->find();

        // 推广操作
        $promotionsqlarr['cp_uid'] = $uid;
        $promotionsqlarr['cp_jobid'] = $jobs_id;
        $promotionsqlarr['cp_ptype'] = $setmeal_increment['cat'];
        $promotionsqlarr['cp_days'] = $setmeal_increment['value'];
        $promotionsqlarr['cp_starttime'] = time();
        $promotionsqlarr['cp_endtime'] = strtotime("{$setmeal_increment['value']} day");
        D('Promotion')->add_promotion($promotionsqlarr);
        D('Promotion')->set_job_promotion($jobs_id, $setmeal_increment['cat']);
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
             write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /**
     * 增值包回调 - 企业模板
     */
    protected function order_paid_company_tpl($order, $user) {
        $uid = $order['uid'];
        $project_id = $order['setmeal'];
        $deductible = $order['pay_points'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        D('CompanyTpl')->add_company_tpl(array('uid' => $uid, 'tplid' => $project_id));
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /**
     * 增值包回调 - 诚聘通
     */
    protected function order_paid_famous_company($order, $user) {
        $uid = $order['uid'];
        D('CompanyProfile')->where(array('uid' => $uid))->setField('famous', 1);
        D('Jobs')->jobs_setfield(array('uid' => $uid), array('famous' => 1));
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
             write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        $companyinfo = D('CompanyProfile')->where(array('uid' => $uid))->find();
        $auditsqlarr['company_id'] = $companyinfo['id'];
        $auditsqlarr['reason'] = '成为诚聘通企业';
        $auditsqlarr['status'] = '是';
        $auditsqlarr['addtime'] = time();
        $auditsqlarr['audit_man'] = '系统';
        $auditsqlarr['famous'] = 1;
        M('AuditReason')->data($auditsqlarr)->add();
        return true;
    }

    /**
     * 增值包回调 - 预约刷新职位
     */
    protected function order_paid_auto_refresh_jobs($order, $user) {
        $params = unserialize($order['params']);
        $uid = $order['uid'];
        $project_id = $order['setmeal'];
        $deductible = $order['pay_points'];
        $jobs_id = $params['jobs_id'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $setmeal_increment = D('SetmealIncrement')->where(array('id' => $project_id))->find();
        $days = $setmeal_increment['value'];
        $nowtime = time();
        for ($i = 0; $i < $days * 4; $i++) {
            $timespace = 3600 * 6 * $i;
            M('QueueAutoRefresh')->add(array('uid' => $uid, 'pid' => $jobs_id, 'type' => 1, 'refreshtime' => $nowtime + $timespace));
        }
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /**
     * 单独购买职位刷新
     */
    protected function order_paid_single_refresh_jobs($order, $user) {
        $params = unserialize($order['params']);
        $uid = $order['uid'];
        $deductible = $order['pay_points'];
        $jobs_id = $params['jobs_id'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        // 刷新操作
        $time = time();
        $where = array('uid' => $uid, 'id' => array('in', $jobs_id));
        $jobs = M('Jobs')->field('id,stick')->where($where)->select();
        $jobsTmp = M('JobsTmp')->field('id,stick')->where($where)->select();
        $jobs = $jobs ?: array();
        $jobsTmp = $jobsTmp ?: array();
        $jobs = array_merge($jobs, $jobsTmp);
        foreach ($jobs as $val) {
            if ($val['stick']) {
                $ids[] = $val['id'];
            } else {
                $idsTmp[] = $val['id'];
            }
        }
        if ($ids) {
            $where = array('id' => array('in', $ids));
            $stick_data = array('refreshtime' => $time, 'stime' => $time + 100000000);
            if (false === M('Jobs')->where($where)->save($stick_data)) return false;
            if (false === M('JobsTmp')->where($where)->save($stick_data)) return false;
            if (false === M('JobsSearch')->where($where)->save($stick_data)) return false;
            if (false === M('JobsSearchKey')->where($where)->save($stick_data)) return false;
        }
        if ($idsTmp) {
            $where = array('id' => array('in', $idsTmp));
            $stick_data = array('refreshtime' => $time, 'stime' => $time);
            if (false === M('Jobs')->where($where)->save($stick_data)) return false;
            if (false === M('JobsTmp')->where($where)->save($stick_data)) return false;
            if (false === M('JobsSearch')->where($where)->save($stick_data)) return false;
            if (false === M('JobsSearchKey')->where($where)->save($stick_data)) return false;
        }
        M('CompanyProfile')->where(array('uid' => $uid))->setfield('refreshtime', $time);

        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /**
     * 单独购买简历下载
     */
    protected function order_paid_single_resume_download($order, $user) {
        $params = unserialize($order['params']);
        $uid = $order['uid'];
        $deductible = $order['pay_points'];
        $resume_id = $params['resume_id'];
        if ($deductible > 0) {
            $p_rst = D('MembersPoints')->report_deal($uid, 2, $deductible);
            if ($p_rst) {
                $handsel['uid'] = $uid;
                $handsel['htype'] = '';
                $handsel['htype_cn'] = $order['service_name'];
                $handsel['operate'] = 2;
                $handsel['points'] = $deductible;
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
            }
        }
        $resume_arr = D('Resume')->where(array('id' => array('in', $resume_id)))->select();
        foreach ($resume_arr as $key => $value) {
            $addarr['resume_id'] = $value['id'];
            if ($value['display_name'] == "2") {
                $addarr['resume_name'] = "N" . str_pad($value['id'], 7, "0", STR_PAD_LEFT);
            } elseif ($value['display_name'] == "3") {
                if ($value['sex'] == 1) {
                    $addarr['resume_name'] = cut_str($value['fullname'], 1, 0, "先生");
                } elseif ($value['sex'] == 2) {
                    $addarr['resume_name'] = cut_str($value['fullname'], 1, 0, "女士");
                }
            } else {
                $addarr['resume_name'] = $value['fullname'];
            }
            $company = M('CompanyProfile')->where(array('uid' => $uid))->find();
            $addarr["company_uid"] = $uid;
            $addarr["resume_uid"] = $value['uid'];
            $addarr['company_name'] = $company['companyname'];
            $addarr['down_addtime'] = time();
            D('CompanyDownResume')->save_down_resume($addarr);
        }
        /* 会员日志 */
        if ($deductible > 0) {
            $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
            write_members_log($user,'points',C('qscms_points_byname').'抵扣,开通服务：'.$order['service_name'],false,array('order_id'=>$order['id']));
        } else {
            $log_payment = $order['payment_cn'];
            write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
        }
        return true;
    }

    /*
        付款后开通
    */
    public function order_paid($out_trade_no, $time) {
        $order = $this->where(array('oid' => $out_trade_no, 'is_paid' => '1'))->find();		
		if($order['gift_id']){
			$gift_issue_info=M("GiftIssue")->where(array("id"=>$order['gift_id']))->find();
			$gift_issue_info['gift_info']=M("Gift")->where(array("id"=>$gift_issue_info['gift_id']))->find();
			if($gift_issue_info['gift_type']==1){
				$gift_pay_name="专享优惠券";
			}elseif($gift_issue_info['gift_type']==2){
				$gift_pay_name="新用户专享券";
			}elseif($gift_issue_info['gift_type']==3){
				$gift_pay_name="活动专享券";
			}
		}
        //判断是否支付完成（防止支付完立即关闭页面  从而导致未开通服务）
        if (intval($order['is_paid']) == 1) {
            $data['is_paid'] = 2;
            $data['payment_time'] = $time;
            $this->where(array('oid' => $out_trade_no))->save($data);
            D('CompanyProfile')->where(array('uid'=>$order['uid']))->setField('order_paid',1);
            $user = D('Members')->get_user_one(array('uid' => $order['uid']));
            //套餐支付
            if ($order['order_type'] == '1') {
                $deductible = $order['pay_points'];
                if ($deductible > 0) {
                    $p_rst = D('MembersPoints')->report_deal($order['uid'], 2, $deductible);
                    if ($p_rst) {
                        $handsel['uid'] = $order['uid'];
                        $handsel['htype'] = '';
                        $handsel['htype_cn'] = $order['service_name'];
                        $handsel['operate'] = 2;
                        $handsel['points'] = $deductible;
                        $handsel['addtime'] = time();
                        D('MembersHandsel')->members_handsel_add($handsel);
                    }
                }
                $order_name = "套餐订单";
                //D('MembersSetmeal')->set_members_setmeal($order['uid'], $order['setmeal']);/*chm修改前*/
                D('MembersSetmeal')->set_members_setmeal($order['uid'], $order['setmeal'], $order['pay_amount']);/*chm修改后*/
                $setmeal = M('Setmeal')->where(array('id' => $order['setmeal']))->find();
                /* 会员日志 */
                if ($deductible > 0) {
                    $log_payment = $order['payment_cn'] . '+' . C('qscms_points_byname') . '抵扣';
                } else {
                    $log_payment = $order['payment_cn'];
                }
                write_members_log($user, 'order', '开通服务：' . $order['service_name'], false, array('order_id' => $order['id']));
                //会员套餐变更记录。会员购买成功。log_type 2表示：会员自己购买
                $notes = date('Y-m-d H:i', time()) . "通过：" . D('Payment')->get_payment_info($order['payment_name'], true) . " 成功充值 " . $order['amount'] . "元并开通{$setmeal['setmeal_name']}";
                write_members_log($user,'setmeal',$notes);
            } //积分支付
            else if ($order['order_type'] == '2') {
                $order_name = C('qscms_points_byname') . "订单";
                $p_rst = D('MembersPoints')->report_deal($user['uid'], 1, $order['points']);
                if ($p_rst) {
                    $handsel['uid'] = $user['uid'];
                    $handsel['htype'] = 'buy_points';
                    $handsel['htype_cn'] = '充值' . C('qscms_points_byname');
                    $handsel['operate'] = 1;
                    $handsel['points'] = $order['points'];
                    $handsel['addtime'] = time();
                    D('MembersHandsel')->members_handsel_add($handsel);
                }
                $user_points = D('MembersPoints')->get_user_points($user['uid']);
                /* 会员日志 */
                $notes = date('Y-m-d H:i', time()) . "通过：" . D('Payment')->get_payment_info($order['payment_name'], true) . " 成功充值 " . $order['points'].C('qscms_points_byname');
                write_members_log($user, 'order', $notes, false, array('order_id' => $order['id']));
            }
            //简历置顶
            if ($order['order_type'] == '3') {
                $this->order_paid_resume_stick($order, $user);
            } //醒目标签
            else if ($order['order_type'] == '4') {
                $this->order_paid_resume_tag($order, $user);
            } //简历模板
            else if ($order['order_type'] == '5') {
                $this->order_paid_resume_tpl($order, $user);
            } //增值包-简历包,短信包
            else if ($order['order_type'] == '6' || $order['order_type'] == '7') {
                $this->order_paid_setmeal_increment($order, $user);
            } //增值包-职位推广
            else if ($order['order_type'] == '8' || $order['order_type'] == '9') {
                $this->order_paid_job_promotion($order, $user);
            } //增值包-企业模板
            else if ($order['order_type'] == '10') {
                $this->order_paid_company_tpl($order, $user);
            } //增值包-诚聘通
            else if ($order['order_type'] == '11') {
                $this->order_paid_famous_company($order, $user);
            } //增值包-预约职位刷新
            else if ($order['order_type'] == '12') {
                $this->order_paid_auto_refresh_jobs($order, $user);
            } //单独购买职位刷新
            else if ($order['order_type'] == '13') {
                $this->order_paid_single_refresh_jobs($order, $user);
            } //单独购买简历下载
            else if ($order['order_type'] == '14') {
                $this->order_paid_single_resume_download($order, $user);
            }
            //站内信
            if ($user['utype'] == 1) {
                $replac_mail['order_url'] = rtrim(C('qscms_site_domain') . C('qscms_site_dir'), '/') . U('Home/CompanyService/order_detail', array('id' => $order['id']));
            } else {
                $replac_mail['order_url'] = rtrim(C('qscms_site_domain') . C('qscms_site_dir'), '/') . U('Home/PersonalService/order_detail', array('id' => $order['id']));
            }
            $pms_message = '订单' . $order['oid'] . '已经支付成功，付款方式为：' . $order['payment_cn'] . '，支付金额' . $order['pay_amount'] . ($order['pay_points'] > 0 ? ('(抵扣' . $order['pay_points'] . C('qscms_points_byname') . ')') : '')  . ($order['pay_gift'] > 0 ? ('('.$gift_pay_name.'抵扣' .$order['pay_gift']. '元)') : ''). '。<a href="' . $replac_mail['order_url'] . '" target="_blank">点击查看详情>></a>';
            D('Pms')->write_pmsnotice($user['uid'], $user['username'], $pms_message,$order['utype']);
            // 发送短信
            if (false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
            if ($sms['set_payment'] == 1 && $order['amount'] > 0) {
                $sendSms['mobile'] = $user['mobile'];
                $sendSms['tpl'] = 'set_payment';
                $sendSms['data'] = array('sitename' => C('qscms_site_name'), 'sitedomain' => C('qscms_site_domain'));
                D('Sms')->sendSms('notice', $sendSms);
            }
            //微信
            if (C('apply.Weixin')) {
                D('Weixin/TplMsg')->set_payment($order['uid'], $order['oid'], $order['description'], $order['amount']);
            }
            return true;
        } else {
            return true;
        }
    }

    /*
        订单列表
        @$data 订单查询条件
    */
    public function get_order_list($data, $pagesize = 10) {
        $count = $this->where($data)->count();
        $pager = pager($count, $pagesize);
        $rst['list'] = $this->where($data)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        foreach ($rst['list'] as $key => $value) {
            $rst['list'][$key]['invoice'] = D('OrderInvoice')->getone($value['id']);
        }
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }

    /**
     * 付款后开通
     */
    function admin_order_paid($id) {
        $time = time();
        $order = $this->where(array('id' => $id))->find();
        return $this->order_paid($order['oid'], $time);
    }
}

?>