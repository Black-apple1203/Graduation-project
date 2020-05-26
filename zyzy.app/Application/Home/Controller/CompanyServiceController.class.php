<?php

namespace Home\Controller;

use Home\Controller\CompanyController;

class CompanyServiceController extends CompanyController
{
	public $uid;
	public $my_setmeal;
	public $my_points;
	public $increment_arr;
	public $timestamp;
	public function _initialize()
	{
		parent::_initialize();
		//访问者控制
		if (!$this->visitor->is_login && IS_AJAX) $this->ajaxReturn(0, L('login_please'), '', 1);
		if (C('visitor.utype') != 1 && IS_AJAX) $this->ajaxReturn(0, '请登录企业帐号！');
		$this->uid = C('visitor.uid');
		$this->my_setmeal = D('MembersSetmeal')->get_user_setmeal($this->uid);
		$this->my_points = D('MembersPoints')->get_user_points($this->uid);
		$this->timestamp = time();
		$this->assign('company_nav', '');
		$setmeal_increment_pay_points_rule = C('qscms_setmeal_increment_pay_points_rule');
		$this->assign('setmeal_increment_pay_points_rule', $setmeal_increment_pay_points_rule);
	}
	/**
	 * 会员服务首页
	 */
	public function index()
	{
		//剩余天数
		if ($this->my_setmeal['endtime'] == 0) {
			$leave_days = '永久';
		} else {
			$minus = ($this->my_setmeal['endtime'] - time()) / 3600 / 24;
			$leave_days = intval($minus);
		}

		$setmeal_list = D('Setmeal')->where(array('apply' => 1, 'display' => 1))->order('show_order desc')->select();
		foreach ($setmeal_list as $key => $value) {
			$setmeal_list[$key]['discount'] = D('Setmeal')->get_discount_for_setmeal_one($value);
			$setmeal_list[$key]['long'] = $value['days'] == 0 ? '永久' : $this->format_days($value['days']);
		}
		$total[0] = M('Jobs')->where(array('uid' => C('visitor.uid')))->count();
		$total[1] = M('JobsTmp')->where(array('uid' => C('visitor.uid'), 'display' => array('neq', 2)))->count();
		$this->my_setmeal['surplus_jobs'] = $this->my_setmeal['jobs_meanwhile'] - $total[0] - $total[1];

		$this->assign('company_profile', $this->company_profile);
		$this->assign('my_points', $this->my_points);
		$this->assign('my_setmeal', $this->my_setmeal);
		$this->assign('my_userinfo', D('Members')->get_user_one(array('uid' => $this->uid)));
		$this->assign('leave_days', $leave_days);
		$this->assign('setmeal_list', $setmeal_list);
		$this->assign('left_nav', 'setmeal');
		$this->_config_seo(array('title' => '会员服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/index');
	}
	protected function format_days($days)
	{
		if ($days < 30) {
			return $days . '天';
		} else {
			return intval($days / 30) . '个月';
		}
	}
	/**
	 * 购买基础套餐
	 */
	public function setmeal_add()
	{
		if (!$this->cominfo_flge) {
			if (IS_AJAX) {
				$this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
			} else {
				$this->error('为了达到更好的招聘效果，请先完善您的企业资料！', U('company/com_info'));
			}
		}
		$id = I('get.id', 1, 'intval');
		$setmeal_info = D('Setmeal')->where(array('apply' => 1, 'display' => 1, 'id' => $id))->find();
		if (!$setmeal_info) {
			$this->error('参数错误！');
		}
		if (C('qscms_mobile_setmeal_discount_value') > 0) {
			$setmeal_info['mobile_expense'] = C('qscms_mobile_setmeal_discount_type') == 1 ? $setmeal_info['expense'] / 100 * C('qscms_mobile_setmeal_discount_value') : $setmeal_info['expense'] - C('qscms_mobile_setmeal_discount_value');
			$setmeal_info['mobile_expense'] = $setmeal_info['mobile_expense'] < 0 ? 0 : $setmeal_info['mobile_expense'];
		} else {
			$setmeal_info['mobile_expense'] = $setmeal_info['expense'];
		}
		$setmeal_info['long'] = $this->format_days($setmeal_info['days']);
		$payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), array('eq', 'remittance'), 'or')))->order('listorder desc')->select();
		/*
		chm***********start
		*/
		$issue_where['gift_setmeal_id'] = $id;
		$issue_where['uid'] = C('visitor.uid');
		$issue_where['deadtime'] = array('gt', time());
		$issue_where['is_used'] = 2;
		$gift_issue = M("GiftIssue")->where($issue_where)->select();
		foreach ($gift_issue as $key => $val) {
			$gift_issue[$key]['gift_info'] = M("Gift")->where(array('id' => $val['gift_id']))->find();
		}
		$gift_id = I('get.gift_id');
		if ($gift_id) {
			$gift_issue_info = M("GiftIssue")->where(array('id' => $gift_id))->find();
			$gift_issue_info['gift_info'] = M("Gift")->where(array('id' => $gift_issue_info['gift_id']))->find();
			$this->assign('gift_issue_info', $gift_issue_info);
		}

		$this->assign('gift_issue_num', count($gift_issue));
		$this->assign('gift_issue', $gift_issue);
		/*
		chm***********end
		*/
		$this->assign('payment', $payment);
		$this->assign('setmeal_info', $setmeal_info);
		$this->assign('mypoints', $this->my_points);
		$this->assign('payment_rate', C('qscms_payment_rate'));
		$this->assign('need_points', $setmeal_info['expense'] * C('qscms_payment_rate'));
		$this->assign('my_setmeal', $this->my_setmeal);
		$this->assign('left_nav', 'setmeal');
		$this->assign('discount', D('Setmeal')->get_discount_for_setmeal_one($setmeal_info));
		$this->_config_seo(array('title' => '购买套餐 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/setmeal_add');
	}
	/**
	 * 基础套餐支付
	 */
	public function setmeal_add_save()
	{
		//检查未处理订单数
		$order_pay_type = 1;
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$pay_type = I('post.pay_type', 'gift', 'trim,badword');
		$setmeal_id = I('post.project_id', 0, 'intval');
		$gift_id = I('post.gift_id', 0, 'intval');
		if ($gift_id) {
			if (C('qscms_open_give_gift') != 1) {
				$this->notice('优惠券功能关闭，请使用其他方式支付！');
			}
			$gift_issue_info = M("GiftIssue")->where(array("id" => $gift_id))->find();
			$gift_issue_info['gift_info'] = M("Gift")->where(array("id" => $gift_issue_info['gift_id']))->find();
			if ($gift_issue_info['gift_setmeal_id'] != $setmeal_id) {
				$this->notice('此优惠券不适用于该套餐！');
			}
			if ($gift_issue_info['gift_type'] == 1) {
				$gift_pay_name = "专享优惠券";
			} elseif ($gift_issue_info['gift_type'] == 2) {
				$gift_pay_name = "新用户专享券";
			} elseif ($gift_issue_info['gift_type'] == 3) {
				$gift_pay_name = "活动专享券";
			}
		}
		$is_deductible = I('post.is_deductible', 0, 'intval');
		if ($is_deductible == 0) {
			$deductible_gift = 0;
		} else {
			$deductible_gift = $gift_issue_info['gift_info']['price'];
		}
		$amount = I('post.amount', '', 'floatval');
		if ($amount == 0) {
			$pay_type = 'gift';
		}
		if ($setmeal_id == 0) {
			$this->notice('请选择套餐');
		}
		$setmeal_info = D('Setmeal')->get_setmeal_one($setmeal_id);
		$amount = $setmeal_info['expense'];
		// 如果后台设置关闭优惠券功能
		if (!C('qscms_open_give_gift')) {
			$is_deductible = 0;
			$deductible_gift = 0;
			if ($setmeal_info['expense']) {
				$pay_type = 'cash';
			} else {
				$pay_type = 'gift';
			}
		}
		if ($pay_type == 'gift') {
			if ($gift_issue_info['gift_info']['price'] < $amount) {
				$this->notice('券面金额不足，请使用其他方式支付！');
			}
			//D('MembersSetmeal')->set_members_setmeal($this->uid,$setmeal_id);//chm修改前
			D('MembersSetmeal')->set_members_setmeal($this->uid, $setmeal_id, 0); //chm修改后
			$description = '购买服务：' . $setmeal_info['setmeal_name'] . ';' . $gift_pay_name . '支付：' . $deductible_gift . '元';
			$oid = "P-" . date('ymd', time()) . "-" . date('His', time()); //订单号
			$order_insert_id = D('Order')->add_order(C('visitor'), $oid, 1, $setmeal_info['expense'], 0, $service_need_points, $setmeal_info['setmeal_name'], 'gift', $gift_pay_name . '支付', $description, $this->timestamp, 2, 0, $setmeal_id, $this->timestamp, '', $gift_pay_name . $deductible_gift . '元', '', $deductible_gift, $gift_id);
			/* 会员日志 */
			write_members_log(C('visitor'), 'order', '创建套餐订单（订单号：' . $oid . '），支付方式：' . $gift_pay_name . '兑换', false, array('order_id' => $order_insert_id));
			$issuedata['is_used'] = 1;
			$issuedata['usetime'] = time();
			$p_rst = M('GiftIssue')->where(array('id' => $gift_id))->save($issuedata);
			if ($p_rst) {
				/* 会员日志 */
				write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . $gift_pay_name . '兑换', false, array('order_id' => $order_insert_id));
				write_members_log(C('visitor'), 'setmeal', '开通套餐【' . $setmeal_info['setmeal_name'] . '】，支付方式：' . $gift_pay_name . '兑换');
			}
			$this->ajaxReturn(1, '支付成功！', $order_insert_id);
		}
		//=================现金积分支付================
		else if ($pay_type == 'cash') {
			/************chm----start**********/
			if ($is_deductible == 1 && $deductible_gift > 0) {
				$description = $setmeal_info['setmeal_name'] . ';' . $gift_pay_name . '：' . $deductible_gift . '元';
				$discount = $gift_pay_name . $deductible_gift . '元';
			}
			/************chm---end**********/
			$paymenttpye = D('Payment')->get_payment_info($payment_name);
			if (!$paymenttpye) $this->notice("支付方式错误！");

			/************chm----start**********/
			if ($deductible_gift > 0 && $is_deductible) {
				if ($amount > $deductible_gift) {
					$amount = $amount - $deductible_gift;
				} else {
					$amount = 0;
				}
			}
			/************chm----end**********/
			$paysetarr['ordtotal_fee'] = $amount;
			$description = '购买服务：' . $setmeal_info['setmeal_name'] . ';' . $paymenttpye['byname'] . $paysetarr['ordtotal_fee'];
			if ($deductible_gift > 0 && $is_deductible == 1) {
				if ($amount > $deductible_gift) {
					$deductible_gifts = $deductible_gift;
				} else {
					$deductible_gifts = $amount;
				}
				$description .= ';' . $gift_pay_name . '抵扣支付：' . $deductible_gifts . '元';
			}
			$paysetarr['oid'] = strtoupper(substr($paymenttpye['typename'], 0, 1)) . "-" . date('ymd', time()) . "-" . date('His', time()); //订单号
			$insert_id = D('Order')->add_order(C('visitor'), $paysetarr['oid'], 1, $setmeal_info['expense'], $paysetarr['ordtotal_fee'], $deductible, $setmeal_info['setmeal_name'], $payment_name, $paymenttpye['byname'], $description, $this->timestamp, 1, 0, $setmeal_id, '', '', $discount, '', $deductible_gift, $gift_id);
			/* 会员日志 */
			/************chm----start**********/
			$issuedata['is_used'] = 1;
			$issuedata['usetime'] = time();
			$p_rst = M('GiftIssue')->where(array('id' => $gift_id))->save($issuedata);
			if ($deductible_gift > 0 && $is_deductible == 1) {
				$log_payment = $paymenttpye['byname'] . '+' . $gift_pay_name . '';
			} else {
				$log_payment = $paymenttpye['byname'];
			}
			/************chm----end**********/
			write_members_log(C('visitor'), 'order', '创建套餐订单（订单号：' . $paysetarr['oid'] . '），支付方式：' . $log_payment, false, array('order_id' => $insert_id));
			if ($payment_name == 'remittance') {
				$this->redirect('order_detail', array('id' => $insert_id));
				exit;
			}
			$paysetarr['payFrom'] = 'pc';
			$paysetarr['type'] = $payment_name;
			$paysetarr['ordsubject'] = $setmeal_info['setmeal_name'];
			$paysetarr['ordbody'] = $setmeal_info['setmeal_name'];
			$r = D('Payment')->pay($paysetarr);
			if (!$r['state']) $this->ajaxReturn(0, $r['msg']);
			if ($payment_name == 'wxpay') {
				fopen(QSCMS_DATA_PATH . 'wxpay/' . $paysetarr['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
				$_SESSION['wxpay_no'] = $paysetarr['oid'];
				$this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
			}
		}
	}
	public function send_sms()
	{
		if (C('qscms_captcha_open') && C('qscms_captcha_config.varify_mobile') && true !== $reg = \Common\qscmslib\captcha::verify()) $this->ajaxReturn(0, $reg);
		$mobile = I('post.mobile', '', 'trim');
		!$mobile && $this->ajaxReturn(0, '请填手机号码！');
		if (!fieldRegex($mobile, 'mobile')) $this->ajaxReturn(0, '手机号错误！');
		$rand = getmobilecode();
		$sendSms['tpl'] = 'set_login';
		$sendSms['data'] = array('rand' => $rand . '', 'sitename' => C('qscms_site_name'));
		$smsVerify = session('login_smsVerify');
		if ($smsVerify && $smsVerify['mobile'] == $mobile && time() < $smsVerify['time'] + 60) $this->ajaxReturn(0, '60秒内仅能获取一次短信验证码,请稍后重试');
		$sendSms['mobile'] = $mobile;
		if (true === $reg = D('Sms')->sendSms('captcha', $sendSms)) {
			session('login_smsVerify', array('rand' => substr(md5($rand), 8, 16), 'time' => time(), 'mobile' => $mobile));
			$this->ajaxReturn(1, '手机验证码发送成功！');
		} else {
			$this->ajaxReturn(0, $reg);
		}
	}
	/**
	 * 优惠券首页
	 */
	public function gifts()
	{
		$datass['is_used'] = 3;
		$wheress['is_used'] = 2;
		$wheress['deadtime'] = array("lt", time());
		M('GiftIssue')->where($wheress)->save($datass); //********将过期未使用数据的使用状态设置为已过期		
		$where['uid'] = C('visitor.uid');
		$where['is_used'] = 2;
		$gift_issues = M('GiftIssue')->where($where)->order("deadtime asc")->select();
		$gifts = M("Gift")->getField('id,gift_name,price,setmeal_name,setmeal_id,effectivetime');
		foreach ($gifts as $k => $v) {
			$id = $v['id'];
			$gift_arr[$id] = $v;
		}
		foreach ($gift_issues as $key => $val) {
			/*$setmeal = D('Setmeal')->get_setmeal_one($val['gift_setmeal_id']);
			if($setmeal['apply'] && $setmeal['display']){*/
			$gift_issue[$key] = $val;
			$gift_id = $val['gift_id'];
			$gift_issue[$key]['gift_info'] = $gift_arr[$gift_id];

			if (($val['deadtime'] <= time() + C('qscms_gift_min_remind') * 24 * 60 * 60) && ($val['deadtime'] >= time())) {
				$gift_issue[$key]['is_expire'] = 1;
			} else {
				$gift_issue[$key]['is_expire'] = 0;
			}
			/*}*/
		}
		$this->assign('gift_issue', $gift_issue);
		$this->assign('left_nav', 'gifts');
		$this->_config_seo(array('title' => '优惠券 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/gifts');
	}
	/**
	 * 增值服务首页
	 */
	public function increment()
	{
		$model = D('Setmeal');
		//计算各种增值服务的最大折扣
		$return_discount[0] = $model->get_max_discount('download_resume');
		$return_discount[1] = $model->get_max_discount('sms');
		$return_discount[2] = $model->get_max_discount('stick');
		$return_discount[3] = $model->get_max_discount('emergency');
		$return_discount[4] = $model->get_max_discount('tpl');
		$return_discount[5] = $model->get_max_discount('auto_refresh_jobs');
		$this->assign('return_discount', $return_discount);
		$this->assign('left_nav', 'increment');
		$this->_config_seo(array('title' => '增值服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/increment');
	}
	/**
	 * 购买增值包
	 */
	public function increment_add($cat = '')
	{
		if (!$this->cominfo_flge) {
			if (IS_AJAX) {
				$this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
			} else {
				$this->error('为了达到更好的招聘效果，请先完善您的企业资料！', U('company/com_info'));
			}
		}
		$cat = $cat ? $cat : I('get.cat', 'download_resume', 'trim,badword');
		$payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), array('eq', 'remittance'), 'or')))->order('listorder desc')->select();
		switch ($cat) {
			case 'download_resume':
			case 'sms':
				$display_tpl = 'resume_sms';
				$this->_increment_add_normal($cat);
				break;
			case 'stick':
			case 'emergency':
				$display_tpl = 'stick_emergency';
				$this->_increment_add_normal($cat);
				break;
			case 'tpl':
				$display_tpl = 'tpl';
				$request_tpl = I('get.request_tpl', 0, 'intval');
				$this->_increment_add_tpl($request_tpl);
				break;
			case 'auto_refresh_jobs':
				$display_tpl = 'auto_refresh_jobs';
				$this->_increment_add_normal($cat);
				break;
			default:
				$this->error('参数错误');
		}
		$jobs_id = I('request.jobs_id') ? I('request.jobs_id') : '';
		$jobs_id = $jobs_id ? $jobs_id : I('request.yid');
		$this->assign('uid', $this->uid);
		$this->assign('cat', $cat);
		$this->assign('payment', $payment);
		$this->assign('is_free', $this->my_setmeal['is_free']);
		$this->assign('my_setmeal', $this->my_setmeal);
		$this->assign('increment_arr', $this->increment_arr);
		$this->assign('payment_rate', C('qscms_payment_rate'));
		$this->assign('mypoints', $this->my_points);
		$this->assign('cate_arr', D('SetmealIncrement')->cate_arr);
		$this->assign('unit_arr', D('SetmealIncrement')->service_unit);
		$this->assign('jobs_id', $jobs_id);
		$this->assign('companyinfo', $this->company_profile);
		if (!IS_AJAX) {
			$this->assign('left_nav', 'increment');
			$this->_config_seo(array('title' => '增值服务 - 企业会员中心 - ' . C('qscms_site_name')));
			$this->display('Company/service/increment_add_' . $display_tpl);
		}
	}
	/**
	 * 购买增值包 - 普通增值服务
	 */
	protected function _increment_add_normal($cat)
	{
		$increment_arr = D('SetmealIncrement')->get_cache($cat);
		foreach ($increment_arr as $key => $value) {
			//如果是非免费套餐
			if ($this->my_setmeal['is_free'] == 0) {
				$discount = D('Setmeal')->get_increment_discount_by_array($cat, $this->my_setmeal);
				//当前基础套餐套餐需要付的价格,如果折扣为0,则价格与原始价格一致
				$increment_arr[$key]['my_price'] = $discount > 0 ? round($value['price'] * $discount / 10, 2) : $value['price'];
				//当前基础套餐的对应的折扣
				$increment_arr[$key]['my_discount'] = $discount;
				//单条价格
				$increment_arr[$key]['my_unit_price'] = round($increment_arr[$key]['my_price'] / $value['value'], 2);
				//节省的数
				$increment_arr[$key]['my_saved_price'] = $value['price'] - $increment_arr[$key]['my_price'];
			} else {
				$free_discount = D('Setmeal')->get_increment_discount_by_array($cat, $this->my_setmeal);
				//免费会员需要付的价格
				$increment_arr[$key]['my_price'] = $free_discount > 0 ? round($value['price'] * $free_discount / 10, 2) : $value['price'];
				//免费会员单条价格
				$increment_arr[$key]['my_unit_price'] = round($increment_arr[$key]['my_price'] / $value['value'], 2);
				//VIP会员价格,取出折扣最大的套餐折扣
				$vip_discount = D('Setmeal')->get_max_discount($cat);
				$increment_arr[$key]['vip_price'] = intval($vip_discount) > 0 ? round($value['price'] * $vip_discount / 10, 2) : $value['price'];
				//VIP会员单条价格
				$increment_arr[$key]['vip_unit_price'] = round($increment_arr[$key]['vip_price'] / $value['value'], 2);
			}
			//换算积分
			$increment_arr[$key]['need_points'] = round($increment_arr[$key]['my_price'] * C('qscms_payment_rate'));
		}
		$this->increment_arr = $increment_arr;
		if ($cat == 'stick' || $cat == 'emergency' || $cat == 'auto_refresh_jobs') {
			$jobs_where['uid'] = $this->uid;
			C('qscms_jobs_display') == 1 && $jobs_where['audit'] = 1;
			$jobs_list = D('Jobs')->where($jobs_where)->select();
			if ($cat == 'auto_refresh_jobs') {
				foreach ($jobs_list as $key => $value) {
					$has_auto = M('QueueAutoRefresh')->where(array('pid' => $value['id'], 'type' => 1))->find();
					$jobs_list[$key]['auto_refresh'] = $has_auto ? 1 : 0;
				}
			}
			$this->assign('jobs_arr', $jobs_list);
		}
		$this->assign('buy', $cat);
		$buy_cn = '';
		switch ($cat) {
			case 'stick':
				$buy_cn = '置顶';
				break;
			case 'emergency':
				$buy_cn = '紧急';
				break;
		}
		$this->assign('buy_cn', $buy_cn);
	}
	/**
	 * 购买增值包 - 模板
	 */
	public function _increment_add_tpl($request_tpl = 0)
	{
		if ($request_tpl > 0) {
			$increment_arr = D('Tpl')->where(array('tpl_id' => array('eq', $request_tpl)))->select();
		} else {
			$increment_arr = D('Tpl')->where(array('tpl_type' => 1))->select();
		}
		foreach ($increment_arr as $key => $value) {
			$discount = D('Setmeal')->get_increment_discount_by_array('tpl', $this->my_setmeal);
			//当前基础套餐套餐需要付的价格,如果折扣为0,则价格与原始价格一致
			$increment_arr[$key]['my_price'] = $discount > 0 ? round($value['tpl_val'] / C('qscms_payment_rate') * $discount / 10, 2) : ($value['tpl_val'] / C('qscms_payment_rate'));
			//当前基础套餐的对应的折扣
			$increment_arr[$key]['my_discount'] = $discount;
			//换算积分
			$increment_arr[$key]['need_points'] = $discount > 0 ? round($value['tpl_val'] * $discount / 10, 2) : $value['tpl_val'];
			$increment_arr[$key]['id'] = $value['tpl_id'];
			$increment_arr[$key]['thumb_dir'] = __COMPANY__ . '/' . $value['tpl_dir'];
		}
		$this->increment_arr = $increment_arr;
	}
	/**
	 * 添加增值包订单
	 */
	public function increment_add_save()
	{
		//检查未处理订单数
		//根据不同的支付形式走不同的逻辑代码
		$cat = I('request.service_type', '', 'trim,badword');
		if ($cat == '') {
			$this->notice('参数错误');
		}
		$func_name = '_increment_add_save_' . $cat;
		$function_arr = array('download_resume', 'sms', 'stick', 'emergency', 'tpl', 'auto_refresh_jobs');
		if (in_array($cat, $function_arr)) {
			$this->$func_name();
		} else {
			$this->notice('参数错误');
		}
	}
	/**
	 * 增值服务支付 - 下载简历
	 */
	public function _increment_add_save_download_resume()
	{
		$cat = 'download_resume';
		$order_pay_type = 6;
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$pay_type = I('post.pay_type', 'points', 'trim,badword');
		$project_id = I('post.project_id', 0, 'intval');
		$is_deductible = I('post.is_deductible', 0, 'intval');
		if ($is_deductible == 0) {
			$deductible = 0;
		} else {
			$deductible = I('post.deductible', '', 'floatval');
		}
		$amount = I('post.amount', '', 'floatval');
		if ($amount == 0) {
			$pay_type = 'points';
		}
		if ($project_id == 0) {
			$this->notice('请选择套餐');
		}
		$increment_info = D('SetmealIncrement')->get_cache('', $project_id);
		$my_discount = D('Setmeal')->get_increment_discount_by_array($increment_info['cat'], $this->my_setmeal);
		$service_need_cash = $my_discount > 0 ? round($increment_info['price'] * $my_discount / 10, 2) : $increment_info['price'];
		$service_need_points = round($service_need_cash * C('qscms_payment_rate'));
		if ($pay_type == 'points') {
			if ($this->my_points < $service_need_points) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			D('MembersSetmeal')->where(array('uid' => $this->uid))->setInc('download_resume', $increment_info['value']);
			$oid = "P-" . date('ymd', time()) . "-" . date('His', time()); //订单号

			$description = '购买服务：' . $increment_info['name'] . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
			$order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $increment_info['name'], 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, $project_id, $this->timestamp, '', '专享' . $my_discount . '折优惠');
			/* 会员日志 */
			write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));

			$p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
			if ($p_rst) {
				/* 会员日志 */
				write_members_log(C('visitor'), 'points', '兑换增值服务【' . $increment_info['name'] . '】，消耗积分：' . $service_need_points);
				write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
				write_members_log(C('visitor'), 'increment', '开通增值服务【' . $increment_info['name'] . '】，支付方式：' . C('qscms_points_byname') . '兑换');
				$handsel['uid'] = $this->uid;
				$handsel['htype'] = '';
				$handsel['htype_cn'] = '购买增值包:' . $increment_info['name'];
				$handsel['operate'] = 2;
				$handsel['points'] = $service_need_points;
				$handsel['addtime'] = time();
				D('MembersHandsel')->members_handsel_add($handsel);
			}
			$this->ajaxReturn(1, '支付成功！', $order_insert_id);
		}
		//=================现金积分支付================
		else if ($pay_type == 'cash') {
			if ($is_deductible == 1 && $this->my_points < $deductible) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			$this->_call_cash_pay($increment_info, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, '', '', 0, $project_id, '专享' . $my_discount . '折优惠');
		}
	}
	/**
	 * 增值服务支付 - 短信
	 */
	public function _increment_add_save_sms()
	{
		$cat = 'sms';
		$order_pay_type = 7;
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$pay_type = I('post.pay_type', 'points', 'trim,badword');
		$project_id = I('post.project_id', 0, 'intval');
		$is_deductible = I('post.is_deductible', 0, 'intval');
		if ($is_deductible == 0) {
			$deductible = 0;
		} else {
			$deductible = I('post.deductible', '', 'floatval');
		}
		$amount = I('post.amount', '', 'floatval');
		if ($amount == 0) {
			$pay_type = 'points';
		}
		if ($project_id == 0) {
			$this->notice('请选择套餐');
		}
		$increment_info = D('SetmealIncrement')->get_cache('', $project_id);
		$my_discount = D('Setmeal')->get_increment_discount_by_array($increment_info['cat'], $this->my_setmeal);
		$service_need_cash = $my_discount > 0 ? round($increment_info['price'] * $my_discount / 10, 2) : $increment_info['price'];
		$service_need_points = round($service_need_cash * C('qscms_payment_rate'));
		if ($pay_type == 'points') {
			if ($this->my_points < $service_need_points) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			D('Members')->where(array('uid' => $this->uid))->setInc('sms_num', $increment_info['value']);
			$oid = "P-" . date('ymd', time()) . "-" . date('His', time()); //订单号

			$description = '购买服务：' . $increment_info['name'] . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
			$order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $increment_info['name'], 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, $project_id, $this->timestamp, '', '专享' . $my_discount . '折优惠');
			/* 会员日志 */
			write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
			$p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
			if ($p_rst) {
				/* 会员日志 */
				write_members_log(C('visitor'), 'points', '兑换增值服务【' . $increment_info['name'] . '】，消耗积分：' . $service_need_points);
				write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
				write_members_log(C('visitor'), 'increment', '开通增值服务【' . $increment_info['name'] . '】，支付方式：' . C('qscms_points_byname') . '兑换');
				$handsel['uid'] = $this->uid;
				$handsel['htype'] = '';
				$handsel['htype_cn'] = '购买增值包:' . $increment_info['name'];
				$handsel['operate'] = 2;
				$handsel['points'] = $service_need_points;
				$handsel['addtime'] = time();
				D('MembersHandsel')->members_handsel_add($handsel);
			}
			$this->ajaxReturn(1, '支付成功！', $order_insert_id);
		}
		//=================现金积分支付================
		else if ($pay_type == 'cash') {
			if ($is_deductible == 1 && $this->my_points < $deductible) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			$this->_call_cash_pay($increment_info, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, '', '', 0, $project_id, '专享' . $my_discount . '折优惠');
		}
	}
	/**
	 * 增值服务支付 - 置顶
	 */
	public function _increment_add_save_stick()
	{
		$this->_stick_emergency_refresh('stick', '置顶', 8);
	}
	/**
	 * 增值服务支付 - 紧急
	 */
	public function _increment_add_save_emergency()
	{
		$this->_stick_emergency_refresh('emergency', '紧急', 9);
	}
	/**
	 * 增值服务支付 - 预约职位刷新
	 */
	public function _increment_add_save_auto_refresh_jobs()
	{
		$this->_stick_emergency_refresh('auto_refresh', '预约刷新职位', 12);
	}
	/**
	 * 增值服务支付 - 模板
	 */
	public function _increment_add_save_tpl()
	{
		$cat = 'tpl';
		$order_pay_type = 10;
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$pay_type = I('post.pay_type', 'points', 'trim,badword');
		$project_id = I('post.project_id', 0, 'intval');
		$is_deductible = I('post.is_deductible', 0, 'intval');
		if ($is_deductible == 0) {
			$deductible = 0;
		} else {
			$deductible = I('post.deductible', '', 'floatval');
		}
		$amount = I('post.amount', '', 'floatval');
		if ($amount == 0) {
			$pay_type = 'points';
		}
		if ($project_id == 0) {
			$this->notice('请选择模板');
		}
		$increment_info = D('Tpl')->where(array('tpl_id' => $project_id))->find();
		$check_tpl = D('CompanyTpl')->check_tpl(array('uid' => $this->uid, 'tplid' => $project_id));
		if ($check_tpl) {
			$this->notice('您已购买过该模板');
		}
		$my_discount = D('Setmeal')->get_increment_discount_by_array('tpl', $this->my_setmeal);
		$service_need_cash = $my_discount > 0 ? round($increment_info['tpl_val'] / C('qscms_payment_rate') * $my_discount / 10, 2) : $increment_info['tpl_val'] / C('qscms_payment_rate');
		$service_need_points = $increment_info['tpl_val'];
		$increment_info['cat'] = 'tpl';
		$increment_info['name'] = '模板包[' . $increment_info['tpl_name'] . ']';
		if ($pay_type == 'points') {
			if ($this->my_points < $service_need_points) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			$tplsqlarr['uid'] = $this->uid;
			$tplsqlarr['tplid'] = $project_id;
			$r = D('CompanyTpl')->add_company_tpl($tplsqlarr);
			if ($r['state'] == 0) {
				$this->ajaxReturn(0, $r['error']);
			}
			$oid = "P-" . date('ymd', time()) . "-" . date('His', time()); //订单号

			$description = '购买服务：' . $increment_info['name'] . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('points_quantifier');
			$order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $increment_info['name'], 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, $project_id, $this->timestamp, '', '专享' . $my_discount . '折优惠');

			/* 会员日志 */
			write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
			$p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
			if ($p_rst) {
				/* 会员日志 */
				write_members_log(C('visitor'), 'increment', '兑换增值服务【' . $increment_info['name'] . '】，消耗积分：' . $service_need_points);
				write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
				write_members_log(C('visitor'), 'increment', '开通增值服务【' . $increment_info['name'] . '】，支付方式：' . C('qscms_points_byname') . '兑换');
				$handsel['uid'] = $this->uid;
				$handsel['htype'] = '';
				$handsel['htype_cn'] = '购买增值包:' . $increment_info['name'];
				$handsel['operate'] = 2;
				$handsel['points'] = $service_need_points;
				$handsel['addtime'] = time();
				D('MembersHandsel')->members_handsel_add($handsel);
			}
			$this->ajaxReturn(1, '支付成功！', $order_insert_id);
		}
		//=================现金积分支付================
		else if ($pay_type == 'cash') {
			if ($is_deductible == 1 && $this->my_points < $deductible) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			$this->_call_cash_pay($increment_info, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, '', '', 0, $project_id, '专享' . $my_discount . '折优惠');
		}
	}
	/**
	 * 增值服务推广：置顶-紧急-预约职位刷新需要调用的增加订单方法
	 */
	protected function _stick_emergency_refresh($cat, $cat_cn, $order_pay_type)
	{
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$pay_type = I('post.pay_type', 'points', 'trim,badword');
		$project_id = I('post.project_id', 0, 'intval');
		$jobs_id = I('post.jobs_id');
		$is_deductible = I('post.is_deductible', 0, 'intval');
		if ($is_deductible == 0) {
			$deductible = 0;
		} else {
			$deductible = I('post.deductible', '', 'floatval');
		}
		$amount = I('post.amount', '', 'floatval');
		if ($amount == 0) {
			$pay_type = 'points';
		}
		if ($project_id == 0) {
			$this->notice('请选择套餐');
		}
		if (!$jobs_id) {
			$this->notice('请选择职位');
		}
		if ($cat == 'stick' || $cat == 'emergency') {
			$promotion_field = D('Jobs')->where(array('id' => $jobs_id))->find();
			if (!$promotion_field) {
				$promotion_field = D('JobsTmp')->where(array('id' => $jobs_id))->find();
			}
			if (!$promotion_field) {
				$this->notice('职位不存在！');
			}
			if ($promotion_field[$cat] == 1) {
				$this->notice('该职位已' . $cat_cn . '！');
			}
		} else {
			$promotion_field = M('QueueAutoRefresh')->where(array('pid' => $jobs_id, 'type' => 1))->find();
			if ($promotion_field) {
				$this->notice('该职位已预约刷新！');
			}
		}


		$increment_info = D('SetmealIncrement')->get_cache('', $project_id);
		$my_discount = D('Setmeal')->get_increment_discount_by_array($increment_info['cat'], $this->my_setmeal);
		$service_need_cash = $my_discount > 0 ? round($increment_info['price'] * $my_discount / 10, 2) : $increment_info['price'];
		$service_need_points = round($service_need_cash * C('qscms_payment_rate'));
		if ($pay_type == 'points') {
			if ($this->my_points < $service_need_points) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			if ($cat == 'stick' || $cat == 'emergency') {
				// 推广操作
				$promotionsqlarr['cp_uid'] = $this->uid;
				$promotionsqlarr['cp_jobid'] = $jobs_id;
				$promotionsqlarr['cp_ptype'] = $increment_info['cat'];
				$promotionsqlarr['cp_days'] = $increment_info['value'];
				$promotionsqlarr['cp_starttime'] = $this->timestamp;
				$promotionsqlarr['cp_endtime'] = strtotime("{$increment_info['value']} day");
				$promotion_insert_id = D('Promotion')->add_promotion($promotionsqlarr);
				write_members_log(array('uid' => $promotionsqlarr['cp_uid'], 'utype' => 1, 'username' => ''), 'promotion', '开通增值服务【' . ($promotionsqlarr['cp_ptype'] == 'stick' ? '置顶' : ($promotionsqlarr['cp_ptype'] == 'emergency' ? '紧急' : '智能刷新')) . '】', false, array('promotion_id' => $promotion_insert_id));
				D('Promotion')->set_job_promotion($jobs_id, $increment_info['cat']);
				$params_array = array('days' => $increment_info['value']);
			} else {
				$days = $increment_info['value'];
				$params_array = array('days' => $increment_info['value']);
				$nowtime = time();
				$params_array['starttime'] = $nowtime;
				for ($i = 0; $i < $days * 4; $i++) {
					$timespace = 3600 * 6 * $i;
					M('QueueAutoRefresh')->add(array('uid' => C('visitor.uid'), 'pid' => $jobs_id, 'type' => 1, 'refreshtime' => $nowtime + $timespace));
					if ($i + 1 == $days * 4) {
						$params_array['endtime'] = $nowtime + $timespace;
					}
				}
			}
			$params_array['jobs_id'] = $jobs_id;
			$oid = "P-" . date('ymd', time()) . "-" . date('His', time()); //订单号

			$description = '购买服务：' . $increment_info['name'] . ';' . C('qscms_points_byname') . '支付' . $service_need_points . C('qscms_points_byname');
			$order_insert_id = D('Order')->add_order(C('visitor'), $oid, $order_pay_type, $service_need_cash, 0, $service_need_points, $increment_info['name'], 'points', C('qscms_points_byname') . '支付', $description, $this->timestamp, 2, 0, $project_id, $this->timestamp, serialize($params_array), '专享' . $my_discount . '折优惠');
			/* 会员日志 */
			write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
			$p_rst = D('MembersPoints')->report_deal($this->uid, 2, $service_need_points);
			if ($p_rst) {
				/* 会员日志 */
				write_members_log(C('visitor'), 'increment', '兑换增值服务【' . $increment_info['name'] . '】，消耗积分：' . $service_need_points);
				write_members_log(C('visitor'), 'order', '支付订单（订单号：' . $oid . '），支付方式：' . C('qscms_points_byname') . '兑换', false, array('order_id' => $order_insert_id));
				write_members_log(C('visitor'), 'increment', '开通增值服务【' . $increment_info['name'] . '】，支付方式：' . C('qscms_points_byname') . '兑换');
				$handsel['uid'] = $this->uid;
				$handsel['htype'] = '';
				$handsel['htype_cn'] = '购买增值包:' . $increment_info['name'];
				$handsel['operate'] = 2;
				$handsel['points'] = $service_need_points;
				$handsel['addtime'] = time();
				D('MembersHandsel')->members_handsel_add($handsel);
			}
			$this->ajaxReturn(1, '支付成功！', $order_insert_id);
		}
		//=================现金积分支付================
		else if ($pay_type == 'cash') {
			if ($is_deductible == 1 && $this->my_points < $deductible) {
				$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
			}
			$params['jobs_id'] = $jobs_id;
			$params['days'] = $increment_info['value'];
			$nowtime = time();
			$params['starttime'] = $nowtime;
			for ($i = 0; $i < $params['days'] * 4; $i++) {
				$timespace = 3600 * 6 * $i;
				if ($i + 1 == $params['days'] * 4) {
					$params['endtime'] = $nowtime + $timespace;
				}
			}
			$params = serialize($params);
			$this->_call_cash_pay($increment_info, $order_pay_type, $payment_name, $service_need_cash, $is_deductible, $deductible, '', $params, 0, $project_id, '专享' . $my_discount . '折优惠');
		}
	}
	/**
	 * 启动现金支付
	 */
	protected function _call_cash_pay($increment_info, $order_pay_type, $payment_name = '', $amount = '0.0', $is_deductible, $deductible = 0, $description = '', $params = '', $points = 0, $stemeal = 0, $discount = '')
	{
		$paymenttpye = D('Payment')->get_payment_info($payment_name);
		if (!$paymenttpye) $this->notice("支付方式错误！");
		if ($this->my_points < $deductible) {
			$this->notice(C('qscms_points_byname') . '不足，请使用其他方式支付！');
		}
		if ($is_deductible == 0) {
			$deductible = 0;
		}
		if ($deductible > 0) {
			$m_amount = $amount - floatval($deductible / C('qscms_payment_rate'));
		} else {
			$m_amount = $amount;
		}

		$paysetarr['ordtotal_fee'] = $m_amount;
		if ($description == '') {
			$description = '购买服务：' . $increment_info['name'];
		}
		$description .= ';' . $paymenttpye['byname'] . $paysetarr['ordtotal_fee'] . '元';

		if ($deductible > 0) {
			$description .= ';' . C('qscms_points_byname') . '支付：' . $deductible . C('qscms_points_byname');
		}
		$paysetarr['oid'] = strtoupper(substr($paymenttpye['typename'], 0, 1)) . "-" . date('ymd', time()) . "-" . date('His', time()); //订单号
		$insert_id = D('Order')->add_order(C('visitor'), $paysetarr['oid'], $order_pay_type, $amount, $paysetarr['ordtotal_fee'], $deductible, $increment_info['name'], $payment_name, $paymenttpye['byname'], $description, $this->timestamp, 1, $points, $stemeal, 0, $params, $discount);
		if ($deductible > 0 && $is_deductible == 1) {
			$log_payment = $paymenttpye['byname'] . '+' . C('qscms_points_byname') . '抵扣';
		} else {
			$log_payment = $paymenttpye['byname'];
		}
		write_members_log(C('visitor'), 'order', '创建增值服务订单（订单号：' . $paysetarr['oid'] . '），支付方式：' . $log_payment, false, array('order_id' => $insert_id));
		if ($payment_name == 'remittance') {
			$this->redirect('order_detail', array('id' => $insert_id));
			exit;
		}
		$paysetarr['payFrom'] = 'pc';
		$paysetarr['type'] = $payment_name;
		$paysetarr['ordsubject'] = $increment_info['name'];
		$paysetarr['ordbody'] = $increment_info['name'];
		$r = D('Payment')->pay($paysetarr);
		if (!$r['state']) $this->ajaxReturn(0, $r['msg']);
		if ($payment_name == 'wxpay') {
			fopen(QSCMS_DATA_PATH . 'wxpay/' . $paysetarr['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
			$_SESSION['wxpay_no'] = $paysetarr['oid'];
			$this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
		}
	}
	protected function notice($message)
	{
		if (IS_AJAX) {
			$this->ajaxReturn(0, $message);
		} else {
			$this->error($message);
		}
	}
	/**
	 * 支付完成
	 */
	public function order_pay_finish()
	{
		$order_id = I('request.order_id', 0, 'intval');
		$order = D('Order')->where(array('id' => $order_id))->find();
		$my_setmeal = D('MembersSetmeal')->where(array('uid' => $this->uid))->find();
		switch ($order['order_type']) {
			case 8:
			case 9:
			case 12:
				$params = unserialize($order['params']);
				$endtime = $order['payment_time'] + intval($params['days']) * 24 * 3600;
				$endtime = date('Y-m-d', $endtime);
				break;
			default:
				$endtime = $my_setmeal['endtime'] == 0 ? '永久有效' : date('Y-m-d', $my_setmeal['endtime']);
				break;
		}
		$this->assign('order', $order);
		$this->assign('endtime', $endtime);
		$this->assign('my_setmeal', $this->my_setmeal);
		$this->assign('left_nav', 'increment');
		$this->_config_seo(array('title' => '会员服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/order_pay_finish');
	}
	/**
	 * 检查微信支付回调
	 */
	public function check_weixinpay_notify()
	{
		if (file_exists(QSCMS_DATA_PATH . 'wxpay/' . $_SESSION['wxpay_no'] . '.tmp')) {
			$this->ajaxReturn(0, '回调成功');
		} else {
			$order = D('Order')->where(array('oid' => $_SESSION['wxpay_no']))->find();
			unset($_SESSION['wxpay_no']);
			$this->ajaxReturn(1, '回调成功', U('order_detail', array('id' => $order['id'])));
		}
	}
	/**
	 * 服务介绍
	 */
	public function explain()
	{
		$this->_config_seo(array('title' => '会员服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/explain');
	}
	/**
	 * 套餐使用明细
	 */
	public function setmeal_detail()
	{
		//剩余天数
		if ($this->my_setmeal['endtime'] == 0) {
			$leave_days = '永久';
		} else {
			$minus = ($this->my_setmeal['endtime'] - time()) / 3600 / 24;
			$leave_days = intval($minus);
		}
		$endtime = I('request.endtime', date('Y-m-d'), 'trim');
		$endtime = $endtime ? strtotime($endtime) + 3600 * 24 : strtotime('today') + 3600 * 24;
		$starttime = I('request.starttime', date('Y-m-d', $endtime - 30 * 3600 * 24), 'trim');
		$starttime = $starttime ? strtotime($starttime) : strtotime(date('Y-m-d', $endtime - 30 * 3600 * 24));
		$where['log_addtime'] = array(array('gt', $starttime), array('lt', $endtime), 'and');
		$where['log_type'] = 'setmeal';
		$where['log_uid'] = $this->uid;
		$count = D('MembersLog')->where($where)->count();
		$pager = pager($count, 10);
		$log_page = $pager->fshow();
		$log = D('MembersLog')->where($where)->limit($pager->firstRow . ',' . $pager->listRows)->select();
		$total[0] = M('Jobs')->where(array('uid' => C('visitor.uid')))->count();
		$total[1] = M('JobsTmp')->where(array('uid' => C('visitor.uid'), 'display' => array('neq', 2)))->count();
		$this->my_setmeal['surplus_jobs'] = $this->my_setmeal['jobs_meanwhile'] - $total[0] - $total[1];
		$this->assign('log', $log);
		$this->assign('log_page', $log_page);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime - 3600 * 24);
		$this->assign('company_profile', $this->company_profile);
		$this->assign('my_points', $this->my_points);
		$this->assign('my_setmeal', $this->my_setmeal);
		$this->assign('my_userinfo', D('Members')->get_user_one(array('uid' => $this->uid)));
		$this->assign('leave_days', $leave_days);
		$this->assign('left_nav', 'setmeal');
		$this->_config_seo(array('title' => '套餐使用明细 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/setmeal_detail');
	}
	/**
	 * 订单列表
	 */
	public function order_list()
	{
		$type = I('get.type', 'setmeal', 'trim,badword');
		$function_arr = array('setmeal', 'increment', 'points');
		if (!in_array($type, $function_arr)) {
			$this->error('参数错误！');
		}
		switch ($type) {
			case 'setmeal':
				$where['order_type'] = array('eq', 1);
				break;
			case 'increment':
				$where['order_type'] = array(array('eq', 6), array('eq', 7), array('eq', 8), array('eq', 9), array('eq', 10), array('eq', 11), array('eq', 12), array('eq', 13), array('eq', 14), 'or');
				break;
			case 'points':
				$where['order_type'] = array('eq', 2);
				break;
		}
		$is_paid = I('get.is_paid', 0, 'intval');
		if ($is_paid > 0) {
			$where['is_paid'] = $is_paid;
		}
		$where['uid'] = C('visitor.uid');
		$order = D('Order')->get_order_list($where);
		$this->assign('order', $order);
		$this->assign('type', $type);
		$this->assign('setmeal', D('Setmeal')->get_setmeal_cache());
		$this->assign('left_nav', 'order_list');
		$this->_config_seo(array('title' => '我的订单 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/order_list');
	}
	/**
	 * 商品兑换订单列表
	 */
	public function order_list_goods()
	{
		if (!isset($this->apply['Mall'])) $this->_empty();
		$status = I('get.status', 0, 'intval');
		if ($status > 0) {
			$where['status'] = $status;
		}
		$where['uid'] = C('visitor.uid');
		$order = D('Mall/MallOrder')->get_order_list($where);
		$this->assign('order', $order);
		$this->assign('left_nav', 'order_list');
		$this->_config_seo(array('title' => '我的订单 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/order_list_goods');
	}
	/**
	 * 订单详情
	 */
	public function order_detail()
	{
		if (!I('get.org', '', 'trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']) {
			redirect(build_mobile_url(array('c' => 'CompanyService', 'a' => 'order_detail', 'params' => 'order_id=' . intval($_GET['id']))));
		}
		$id = I('get.id', 0, 'intval');
		if ($id == 0) {
			$this->error('参数错误！');
		}
		$order = D('Order')->get_order_one(array('id' => $id));
		//dump($order);die;
		$order['gift_info'] = M("GiftIssue")->where(array('id' => $order['gift_id']))->find();
		if ($order['gift_info']['gift_type'] == 1) {
			$order['gift_pay_name'] = "专享优惠券";
		} elseif ($order['gift_info']['gift_type'] == 2) {
			$order['gift_pay_name'] = "新用户专享券";
		} elseif ($order['gift_info']['gift_type'] == 3) {
			$order['gift_pay_name'] = "活动专享券";
		}
		$this->assign('order', $order);
		$this->assign('open_invoice', C('qscms_open_invoice'));
		$this->assign('invoice', D('OrderInvoice')->getone($id, $this->uid));
		$this->assign('order_type_cn', D('Order')->order_type[$order['order_type']]);
		$this->assign('setmeal', D('MembersSetmeal')->get_user_setmeal($this->uid));
		$this->assign('payment_info', D('Payment')->where(array('typename' => $order['payment']))->find());
		$this->assign('left_nav', 'order_list');
		$this->_config_seo(array('title' => '订单详情 - 企业会员中心 - ' . C('qscms_site_name')));
		if ($order['is_paid'] == 2 || $order['is_paid'] == 3) {
			$contact = M('CompanyProfile')->field('companyname,contact,telephone,landline_tel,address')->where(array('uid' => $order['uid']))->find();
			$this->assign('category', D('OrderInvoiceCategory')->invoice_category_cache());
			$this->assign('contact', $contact);
			$this->display('Company/service/order_detail');
		} else {
			$this->assign('mypoints', $this->my_points);
			$this->assign('payment_rate', C('qscms_payment_rate'));
			$this->display('Company/service/order_detail_nopay');
		}
	}
	/**
	 * 索取发票保存
	 */
	public function invoice_save()
	{
		$data = I('post.');
		$data['uid'] = $this->uid;
		$result = D('OrderInvoice')->addone($data, C('visitor'));
		$this->ajaxReturn($result['state'], $result['error'], $result['data']);
	}
	/**
	 * 取消订单
	 */
	public function order_cancel()
	{
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
			$issuedata['is_used'] = 2;
			$issuedata['usetime'] = time();
			M('GiftIssue')->where(array('id' => $order_info['gift_id']))->save($issuedata);
			$rst = D('Order')->where(array('id' => $id, 'uid' => $this->uid))->setField('is_paid', 3);
			if ($rst) {
				write_members_log(C('visitor'), 'order', '取消订单（订单号：' . $order_info['oid'] . '）', false, array('order_id' => $order_info['id']));
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
	public function order_delete()
	{
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
	 * 我的积分
	 */
	public function points()
	{
		$this->assign('task_url', D('Task')->task_url(C('visitor.utype')));
		$this->assign('company_profile', $this->company_profile);
		$this->assign('my_points', $this->my_points);
		$this->assign('points_count', D('TaskLog')->count_task_points(C('visitor.uid'), C('visitor.utype')));
		$this->assign('done_task', D('TaskLog')->get_done_task(C('visitor.uid'), C('visitor.utype')));
		$this->assign('task', D('Task')->get_task_cache(1));
		$this->assign('left_nav', 'points');
		$this->_config_seo(array('title' => '我的' . C('qscms_points_byname') . ' - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/points');
	}
	/**
	 * 购买积分
	 */
	public function points_add()
	{
		if (!$this->cominfo_flge) {
			if (IS_AJAX) {
				$this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
			} else {
				$this->error('为了达到更好的招聘效果，请先完善您的企业资料！', U('company/com_info'));
			}
		}
		if (C('qscms_enable_com_buy_points') == 0) {
			$this->error('参数错误！');
		}
		$payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), array('eq', 'remittance'), 'or')))->order('listorder desc')->select();
		$this->assign('payment', $payment);
		$this->assign('payment_rate', C('qscms_payment_rate'));
		$this->assign('points_count', D('TaskLog')->count_task_points(C('visitor.uid'), C('visitor.utype')));
		$this->assign('left_nav', 'points');
		$this->_config_seo(array('title' => '购买' . C('qscms_points_byname') . ' - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/points_add');
	}
	/**
	 * 购买积分支付
	 */
	public function points_add_save()
	{
		if (C('qscms_enable_com_buy_points') == 0) {
			$this->error('参数错误！');
		}
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$points = I('post.points', 0, 'intval');
		if ($points == 0) {
			$this->notice('请输入要购买的' . C('qscms_points_byname') . '数量！');
		} else if ($points < C('qscms_com_buy_points_min')) {
			$points = C('qscms_com_buy_points_min');
		}

		$service_need_cash = round($points / C('qscms_payment_rate'), 2);
		$this->_call_cash_pay(array('name' => $points . C('qscms_points_byname')), 2, $payment_name, $service_need_cash, 0, 0, '充值' . C('qscms_points_byname'), '', $points);
	}
	/**
	 * 积分使用明细
	 */
	public function points_detail()
	{
		$where['uid'] = C('visitor.uid');
		$list = D('MembersHandsel')->get_handsel_list($where);
		$this->assign('list', $list);
		$this->assign('company_profile', $this->company_profile);
		$this->assign('my_points', $this->my_points);
		$this->assign('points_count', D('TaskLog')->count_task_points(C('visitor.uid'), C('visitor.utype')));
		$this->_config_seo(array('title' => C('qscms_points_byname') . '使用明细 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->assign('left_nav', 'points');
		$this->display('Company/service/points_detail');
	}
	/**
	 * 购买诚聘通
	 */
	public function famouns_add()
	{
		if (!$this->cominfo_flge) {
			if (IS_AJAX) {
				$this->ajaxReturn(0, '为了达到更好的招聘效果，请先完善您的企业资料！');
			} else {
				$this->error('为了达到更好的招聘效果，请先完善您的企业资料！', U('company/com_info'));
			}
		}
		$payment = M('Payment')->where(array('p_install' => 2, 'typename' => array(array('eq', 'alipay'), array('eq', 'wxpay'), array('eq', 'remittance'), 'or')))->order('listorder desc')->select();
		$this->assign('payment', $payment);
		$this->assign('amount', C('qscms_famous_company_price'));
		$this->assign('left_nav', 'increment');
		$this->_config_seo(array('title' => '增值服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/famous_add');
	}
	/**
	 * 购买诚聘通支付
	 */
	public function famouns_add_save()
	{
		$payment_name = I('post.payment_name', '', 'trim,badword');
		$service_need_cash = C('qscms_famous_company_price');
		$this->_call_cash_pay(array('name' => '购买诚聘通'), 11, $payment_name, C('qscms_famous_company_price'), 0, 0, '购买诚聘通');
	}
	public function order_pay_repeat()
	{
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
		if (!$r['state']) $this->ajaxReturn(0, $r['msg']);
		if ($info['payment'] == 'wxpay') {
			fopen(QSCMS_DATA_PATH . 'wxpay/' . $paysetarr['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
			$_SESSION['wxpay_no'] = $paysetarr['oid'];
			$this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
		}
	}
	/**
	 * 等待确认支付状态
	 */
	public function confirm_pay_status()
	{
		$tip = '请在新打开的支付页面完成付款';
		$description = '付款完成前请不要关闭此窗口，付款后请根据您的情况点击下面的按钮，如果在支付中遇到问题请到<a target="_blank" href="' . url_rewrite("QS_help") . '">帮助中心</a>。';
		$this->ajax_warning($tip, $description);
	}
	/**
	 * 职位刷新
	 */
	public function jobs_refresh()
	{
		$yid = I('request.yid');
		if (!$yid) {
			$this->ajaxReturn(0, '请选择职位！');
		}
		$yid = is_array($yid) ? $yid : explode(",", $yid);
		$jobs_num = count($yid);
		if (IS_POST) {
			$payment_name = I('request.payment_name', '', 'trim');
			//如果是微信或者支付宝支付，调起支付接口
			if ($payment_name) {
				$is_deductible = I('request.is_deductible', 0, 'intval');
				if ($is_deductible == 0) {
					$deductible = 0;
				} else {
					$deductible = I('request.deductible', '', 'floatval');
				}
				$increment_info['name'] = '职位刷新';
				$params['jobs_id'] = $yid;
				$params['type'] = 'jobs_refresh';
				$params = serialize($params);
				$this->_call_cash_pay($increment_info, 13, $payment_name, C('qscms_refresh_jobs_price') * $jobs_num, $is_deductible, $deductible, '', $params);
			} else {
				//如果是积分兑换刷新或者直接免费刷新
				$r = D('Jobs')->jobs_refresh(array('yid' => $yid, 'user' => C('visitor')));
				if ($r['state'] == 1) {
					$this->ajaxReturn(1, '刷新成功！');
				} else {
					$this->ajaxReturn(0, $r['error']);
				}
			}
		} else {
			$refresh_log_mod = D('RefreshLog');
			if (!$this->my_setmeal) $this->ajaxReturn(0, '您还没有开通服务，请<a target="_blank" href="' . U('CompanyService/index') . '">开通</a>！');
			if ($this->my_setmeal['expire'] == 1 && $this->my_setmeal['setmeal_id'] > 1) $this->ajaxReturn(0, '您的服务已经到期，请<a target="_blank" href="' . U('CompanyService/index') . '">重新开通</a>！');
			$refrestime = $refresh_log_mod->get_last_refresh_date(array('uid' => C('visitor.uid'), 'type' => 1001, 'mode' => 2));
			$duringtime = time() - $refrestime;
			$space = C('qscms_refresh_jobs_space') * 60;
			if ($space > 0 && $duringtime <= $space) {
				$this->ajaxReturn(0, C('qscms_refresh_jobs_space') . "分钟内不能重复刷新职位！");
			}
			//获取今天免费刷新的次数
			$refresh_time = $refresh_log_mod->get_today_refresh_times(array('uid' => C('visitor.uid'), 'type' => 1001, 'mode' => 2));
			if ($jobs_num > 1 && $refresh_time >= $this->my_setmeal['refresh_jobs_free']) {
				$this->ajaxReturn(2, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您共选中了 <span class="font_yellow">' . $jobs_num . '</span> 条职位，今天免费刷新次数已用完。<br>请单条刷新。');
			} elseif ($jobs_num > 1 && $jobs_num + $refresh_time > $this->my_setmeal['refresh_jobs_free']) {
				$surplus = $this->my_setmeal['refresh_jobs_free'] - $refresh_time;
				$this->ajaxReturn(2, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您共选中了 <span class="font_yellow">' . $jobs_num . '</span> 条职位，今天免费刷新次数剩余 <span class="font_yellow">' . $surplus . '</span> 次。<br>请单条刷新。');
			}
			if ($refresh_time >= $this->my_setmeal['refresh_jobs_free']) //免费刷新次数已到
			{
				$mode = 'points';
				if ($this->my_points < C('qscms_refresh_jobs_price') * C('qscms_payment_rate') * $jobs_num) {
					$mode = 'mix';
				}
			} else {
				$mode = 'setmeal';
			}
			if ($mode == 'points' && C('qscms_refresh_jobs_by_points') == 0) {
				$mode = 'mix';
			}
			if ($mode == 'setmeal') {
				$show_footer = 1;
				$this->assign('free_time', $this->my_setmeal['refresh_jobs_free'] - $refresh_time);
			} else if ($mode == 'points') {
				$show_footer = 0;
			} else {
				$show_footer = 0;
				$this->assign('max_discount', D('Setmeal')->get_max_discount('auto_refresh_jobs'));
				$this->assign('need_cash', C('qscms_refresh_jobs_price') * $jobs_num);
			}
			$this->assign('open_points_convert', C('qscms_refresh_jobs_by_points'));
			if ($jobs_num == 1) {
				$jobid = implode(",", $yid);
				$auto_refresh_log = M('QueueAutoRefresh')->where(array('pid' => $jobid, 'type' => 1))->find();
				$auto_refresh_log = $auto_refresh_log ? 1 : 0;
			} else {
				$auto_refresh_log = 0;
			}
			$this->assign('increment', I('request.increment', 0));
			$this->assign('mode', $mode);
			$this->assign('auto_refresh_log', $auto_refresh_log);
			$this->assign('jobs_num', $jobs_num);
			$this->assign('refresh_points', C('qscms_refresh_jobs_price') * C('qscms_payment_rate') * $jobs_num);
			$this->increment_add('auto_refresh_jobs');
			$this->assign('more_times', $this->my_setmeal['refresh_jobs_free'] + C('qscms_refresh_jobs_more') - $refresh_time);
			$html = $this->fetch('Company/ajax_tpl/ajax_job_refresh');
			$this->ajaxReturn(1, $html, array('show_footer' => $show_footer));
		}
	}
	/**
	 * 简历下载
	 */
	public function resume_download()
	{
		$rid = I('request.rid');
		if (!$rid) {
			$this->ajaxReturn(0, '请选择简历！');
		}
		$rid = is_array($rid) ? $rid : explode(",", $rid);
		$resume_num = count($rid);
		if (IS_POST) {
			$payment_name = I('request.payment_name', '', 'trim');
			//如果是微信或者支付宝支付，调起支付接口
			if ($payment_name) {
				$is_deductible = I('request.is_deductible', 0, 'intval');
				$deductible = I('request.deductible', 0, 'intval');
				$increment_info['name'] = '简历下载';
				$params['resume_id'] = $rid;
				$params['type'] = 'resume_download';
				$params = serialize($params);
				$this->_call_cash_pay($increment_info, 14, $payment_name, C('qscms_download_resume_price') * $resume_num, $is_deductible, $deductible, '', $params);
			} else {
				//如果是积分兑换下载或者直接免费下载
				$addarr['rid'] = $rid;
				$r = D('CompanyDownResume')->add_down_resume($addarr, C('visitor'));
				if ($r['state'] == 1) {
					$this->ajaxReturn(1, '下载成功！');
				} else {
					$this->ajaxReturn(0, $r['msg']);
				}
			}
		} else {
			$refresh_log_mod = D('RefreshLog');
			if (!$this->my_setmeal) $this->ajaxReturn(0, '您还没有开通服务，请<a target="_blank" href="' . U('CompanyService/index') . '">开通</a>！');
			if ($this->my_setmeal['expire'] == 1 && $this->my_setmeal['setmeal_id'] > 1) $this->ajaxReturn(0, '您的服务已经到期，请<a target="_blank" href="' . U('CompanyService/index') . '">重新开通</a>！');
			if (C('qscms_down_resume_limit') == 1) {
				$user_jobs = D('Jobs')->count_auditjobs_num(C('visitor.uid'));
				if ($user_jobs == 0) {
					$this->ajaxReturn(0, '你没有发布职位或审核未通过导致无法下载简历');
				}
			} else if (C('qscms_down_resume_limit') == 3) {
				$companyinfo = M('CompanyProfile')->where(array('uid' => C('visitor.uid')))->find();
				if ($companyinfo['audit'] != 1) {
					$this->ajaxReturn(0, '你的营业执照未通过认证导致无法下载简历');
				}
			}
			if ($this->my_setmeal['download_resume_max'] > 0) {
				$downwhere['down_addtime'] = array('between', strtotime('today') . ',' . strtotime('tomorrow'));
				$downwhere['company_uid'] = C('visitor.uid');
				$downnum = D('CompanyDownResume')->where($downwhere)->count();
				if ($resume_num > 1 && $resume_num + $downnum > $this->my_setmeal['download_resume_max'] && $downnum < $this->my_setmeal['download_resume_max']) {
					$this->ajaxReturn(0, '您今天剩余的下载简历数量不足，请选择单个简历下载！');
				} elseif ($downnum >= $this->my_setmeal['download_resume_max']) {
					$this->ajaxReturn(0, '您今天已下载 <span class="txt_highlight">' . $downnum . '</span> 份简历，已达到每天下载上限，请先收藏该简历，明天继续下载。');
				}
			}
			$resume_arr = D('Resume')->where(array('id' => array('in', $rid)))->select();
			foreach ($resume_arr as $key => $val) {
				$counts[$val['id']] = resume_refreshtime_day($val['refreshtime']);
				$resume_count += $counts[$val['id']];
			}
			if (C('qscms_resume_download_quick') == 1) {
				if ($this->my_setmeal['download_resume'] < $resume_count) //套餐中简历下载点数不足
				{
					$mode = 'points';
					if ($this->my_points < C('qscms_download_resume_price') * C('qscms_payment_rate') * $resume_num) {
						$mode = 'mix';
					}
				} else {
					$mode = 'setmeal';
				}
			} else {
				$mode = 'setmeal';
				if ($this->my_setmeal['download_resume'] < $resume_count) //套餐中简历下载点数不足
				{
					$this->ajaxReturn(0, '您套餐中剩余的下载简历点数不足，请升级套餐后继续下载');
				}
			}

			if ($mode == 'points' && C('qscms_down_resume_by_points') == 0) {
				$mode = 'mix';
			}
			if ($mode == 'setmeal') {
				$show_footer = 1;
				$this->assign('free_time', $this->my_setmeal['download_resume']);
			} else if ($mode == 'points') {
				$show_footer = 0;
			} else {
				$show_footer = 0;

				$this->assign('need_cash', C('qscms_download_resume_price') * $resume_num);
			}
			$this->assign('max_discount', D('Setmeal')->get_max_discount('download_resume'));
			$this->assign('open_points_convert', C('qscms_down_resume_by_points'));
			$this->assign('mode', $mode);
			$this->assign('resume_num', $resume_num);
			$this->assign('resume_count', $resume_count);
			$this->assign('deadline', $this->my_setmeal['endtime']);
			$this->assign('resume_id', implode(",", $rid));
			$this->assign('refresh_points', C('qscms_download_resume_price') * C('qscms_payment_rate') * $resume_num);
			$this->increment_add('download_resume');
			$html = $this->fetch('Company/ajax_tpl/ajax_resume_download');
			$this->ajaxReturn(1, $html, array('show_footer' => $show_footer));
		}
	}
	/**
	 * 职位置顶
	 */
	public function jobs_stick()
	{
		$this->increment_add('stick');
		$html = $this->fetch('Company/ajax_tpl/ajax_jobs_stick_emergency');
		$this->ajaxReturn(1, $html);
	}
	/**
	 * 职位紧急
	 */
	public function jobs_emergency()
	{
		$this->increment_add('emergency');
		$html = $this->fetch('Company/ajax_tpl/ajax_jobs_stick_emergency');
		$this->ajaxReturn(1, $html);
	}
	/**
	 * 企业会员中心首页一键刷新职位
	 */
	public function jobs_refresh_all()
	{
		$user_jobs = D('Jobs')->count_auditjobs_num(C('visitor.uid'));
		if ($user_jobs == 0) {
			$this->ajaxReturn(0, '没有可刷新的职位！');
		}
		$refresh_time = D('RefreshLog')->get_today_refresh_times(array('uid' => C('visitor.uid'), 'type' => 1001, 'mode' => 2));
		if ($refresh_time >= $this->my_setmeal['refresh_jobs_free']) {
			$mobile_surplus = $this->my_setmeal['refresh_jobs_free'] + C('qscms_refresh_jobs_more') - $refresh_time;
			if ($mobile_surplus >= 0) {
				$this->ajaxReturn(2, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您当前共有 <span class="font_yellow">' . $user_jobs . '</span> 条在招职位，今天免费刷新次数已用完。</br>请前往 <a target="_blank" href="' . U('company/jobs_list', array('type' => 1)) . '" class="font_blue">职位列表</a> 单条刷新。使用触屏版还可免费刷新 <span class="font_yellow">' . $mobile_surplus . '</span> 次！', U('company/jobs_list', array('type' => 1)));
			} else {
				$this->ajaxReturn(2, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您当前共有 <span class="font_yellow">' . $user_jobs . '</span> 条在招职位，今天免费刷新次数已用完。</br>请前往 <a target="_blank" href="' . U('company/jobs_list', array('type' => 1)) . '" class="font_blue">职位列表</a> 单条刷新。', U('company/jobs_list', array('type' => 1)));
			}
		} elseif ($user_jobs + $refresh_time > $this->my_setmeal['refresh_jobs_free']) {
			$surplus = $this->my_setmeal['refresh_jobs_free'] - $refresh_time;
			$this->ajaxReturn(2, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您当前共有 <span class="font_yellow">' . $user_jobs . '</span> 条在招职位，今天免费刷新次数剩余 <span class="font_yellow">' . $surplus . '</span> 次。</br>请前往 <a target="_blank" href="' . U('company/jobs_list', array('type' => 1)) . '" class="font_blue">职位列表</a> 单条刷新。', U('company/jobs_list', array('type' => 1)));
		} else {
			$condition['uid'] = C('visitor.uid');
			C('qscms_jobs_display') == 1 && $condition['audit'] = 1;
			$jobsid_arr = D('Jobs')->where($condition)->field('id')->select();
			$yid = array();
			foreach ($jobsid_arr as $key => $value) {
				$yid[] = $value['id'];
			}
			$r = D('Jobs')->jobs_refresh(array('yid' => $yid, 'user' => C('visitor')));
			$this->ajaxReturn($r['state'], $r['error']);
		}
	}
	/**
	 * 退出诚聘通
	 */
	public function cancel_famous()
	{
		if (IS_POST) {
			$r = D('CompanyProfile')->where(array('uid' => C('visitor.uid')))->setField('famous', 2);
			D('Jobs')->jobs_setfield(array('uid' => C('visitor.uid')), array('famous' => 2));
			if ($r) {
				write_members_log(C('visitor'), 'sincerity', '退出诚聘通');
				$this->ajaxReturn(1, '已退出诚聘通会员，请联系您的专属客服申请退款');
			} else {
				$this->ajaxReturn(0, '操作失败');
			}
		}
	}
	/**
	 * 购买套餐提示
	 */
	public function confirm_pay_setmeal()
	{
		if (C('qscms_is_superposition') == 0 && C('qscms_is_superposition_time') == 0) //项目和时间都不叠加
		{
			$tip = '您当前是【' . $this->my_setmeal['setmeal_name'] . '】重新开通套餐<br /><span class="font_yellow">1. 原有套餐资源以新开套餐资源为准；</span><br /><span class="font_yellow">2. 原有会员服务时长以新开套餐时长为准；</span><br />确定要重新开通套餐吗？';
		} else if (C('qscms_is_superposition') == 0 && C('qscms_is_superposition_time') == 1) //项目不叠加时间叠加
		{
			$tip = '您当前是【' . $this->my_setmeal['setmeal_name'] . '】重新开通套餐<br /><span class="font_yellow">您的原套餐资源会被新开通的套餐资源覆盖</span><br />确定要重新开通套餐吗？';
		} else if (C('qscms_is_superposition') == 1 && C('qscms_is_superposition_time') == 0) //项目叠加时间不叠加
		{
			$tip = '您当前是【' . $this->my_setmeal['setmeal_name'] . '】重新开通套餐<br /><span class="font_yellow">您的会员服务时长将以新开套餐服务时长为准</span><br />确定要重新开通套餐吗？';
		} else //项目叠加时间也叠加
		{
			$tip = '您当前是【' . $this->my_setmeal['setmeal_name'] . '】重新开通套餐<br /><span class="font_yellow">您的套餐资源和会员时长将叠加</span><br />确定要重新开通套餐吗？';
		}
		$this->ajax_warning($tip);
	}
	/**
	 * 购买增值服务提示
	 */
	public function confirm_pay_increment()
	{
		$setmeal_end_days = '永久';
		if ($this->my_setmeal['endtime'] == 0) {
			$tip = '您当前【' . $this->my_setmeal['setmeal_name'] . '】有效期 ' . date('Y-m-d', $this->my_setmeal['starttime']) . '至永久。增值包有效期与会员有效期一致（' . $setmeal_end_days . '），是否继续购买增值包？';
		} else {
			if ($this->my_setmeal['endtime'] > time()) {
				$sub_day = sub_day($this->my_setmeal['endtime'], time());
				$sub_day = preg_replace('/(\d+)/', '<span class="font_yellow">\1</span>', $sub_day);
				$setmeal_end_days = $sub_day . '后到期';
			} else {
				$setmeal_end_days = '已经到期';
			}
			$tip = '您当前【' . $this->my_setmeal['setmeal_name'] . '】有效期 ' . date('Y-m-d', $this->my_setmeal['starttime']) . '至' . date('Y-m-d', $this->my_setmeal['endtime']) . '。增值包有效期与会员有效期一致（' . $setmeal_end_days . '），是否继续购买增值包？';
		}

		$this->ajax_warning($tip);
	}
	/**
	 * 获取招聘外包相关分类
	 */
	protected function get_rpo_cate()
	{
		$where = array('type' => 'job', 'display' => 1);
		$cateJobList = M('RpoCategory')->where($where)->order('sort DESC,cid')->limit(3)->select();
		$where['type'] = 'stage';
		$cateStageList = M('RpoCategory')->where($where)->order('sort DESC,cid')->limit(3)->select();
		$this->assign('cateJobList', $cateJobList);
		$this->assign('cateStageList', $cateStageList);
	}
	/**
	 * 招聘外包RPO
	 */
	public function rpo()
	{
		if (!isset($this->apply['Rpo'])) $this->_empty();
		$this->assign('left_nav', 'rpo');
		$this->get_rpo_cate();
		$this->_config_seo(array('title' => '招聘外包服务 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/rpo');
	}
	/**
	 * 招聘外包申请记录
	 */
	public function rpo_list()
	{
		if (!isset($this->apply['Rpo'])) $this->_empty();
		$this->assign('left_nav', 'rpo');
		$where['uid'] = $this->uid;
		$apply = D('Rpo/Rpo')->get_apply_list($where);
		$this->assign('list', $apply);
		$this->_config_seo(array('title' => '招聘外包申请记录 - 企业会员中心 - ' . C('qscms_site_name')));
		$this->display('Company/service/rpo_list');
	}
	/**
	 * 申请rpo服务
	 */
	public function apply_rpo()
	{
		if (!isset($this->apply['Rpo'])) $this->_empty();
		if (IS_POST) {
			$data = I('post.');
			$data['uid'] = $this->uid;
			$data['com_id'] = M('CompanyProfile')->getFieldByUid($this->uid, 'id');
			if (false !== D('Rpo/Rpo')->apply_rpo($data)) {
				$this->ajaxReturn(1, '申请服务成功！');
			} else {
				$err = D('Rpo/Rpo')->getError();
				$this->ajaxReturn(0, $err ? $err : '申请服务失败！');
			}
		} else {
			$this->get_rpo_cate();
			$contact = M('CompanyProfile')->field('id,contact,telephone,landline_tel')->where(array('uid' => $this->uid))->find();
			$contact['phone'] = $contact['telephone'];
			($contact['landline_tel'] && trim($contact['landline_tel']) != '-') && $contact['phone'] .= '、' . $contact['landline_tel'];
			$this->assign('contact', $contact); // 联系方式
			$html = $this->fetch('Company/ajax_tpl/ajax_rpo');
			$this->ajaxReturn(1, $html);
		}
	}
	/**
	 * [share_allowance 分享红包]
	 */
	public function share_allowance()
	{
		if (!C('qscms_share_allowance_open')) {
			$this->ajaxReturn(0, '职位分享红包已关闭');
		}
		$jobs_id = I('request.id', 0, 'intval');
		if (!$jobs_id) {
			$this->ajaxReturn(0, '请选择职位');
		}
		$jobs = M('Jobs')->where(array('id' => $jobs_id, 'uid' => C('visitor.uid')))->find();
		if (!$jobs) {
			$this->ajaxReturn(0, '职位不存在或已删除');
		}
		if ($jobs['allowance_id']) {
			$this->ajaxReturn(0, '仅能同时开启一种红包方式哦');
		}
		$shareAllowanceMod = D('ShareAllowance');
		$share = $shareAllowanceMod->where(array('jobs_id' => $jobs_id, 'uid' => C('visitor.uid'), 'pay_status' => 1))->order('id desc')->find();
		if ($share && $share['issued'] < $share['count']) {
			$this->ajaxReturn(0, '当前职位已经发布分享红包');
		}
		$shareAllowanceMod->where(array('uid' => C('visitor.uid'), 'pay_status' => 0, 'addtime' => array('lt', time() - 86400)))->delete();
		$this->assign('jobs', $jobs);
		$data['html'] = $this->fetch('Company/service/share_allowance');
		$this->ajaxReturn(1, '发布分享红包', $data);
	}
	/**
	 * [share_allowance_pay 分享红包支付]
	 */
	public function share_allowance_pay()
	{
		if (!C('qscms_share_allowance_open')) {
			IS_AJAX && $this->ajaxReturn(0, '职位分享红包已关闭');
			$this->error('职位分享红包已关闭');
		}
		$jobs_id = I('request.id', 0, 'intval');
		if (!$jobs_id) {
			IS_AJAX && $this->ajaxReturn(0, '请选择职位');
			$this->error('请选择职位');
		}
		$jobs = M('Jobs')->where(array('id' => $jobs_id, 'uid' => C('visitor.uid')))->find();
		if (!$jobs) {
			IS_AJAX && $this->ajaxReturn(0, '职位不存在或已删除');
			$this->error('职位不存在或已删除');
		}
		$shareAllowanceMod = D('ShareAllowance');
		$share = $shareAllowanceMod->where(array('jobs_id' => $jobs_id, 'uid' => C('visitor.uid'), 'pay_status' => 1))->order('id desc')->find();
		if ($share && $share['issued'] < $share['count']) {
			IS_AJAX && $this->ajaxReturn(0, '当前职位已经发布分享红包');
			$this->error('当前职位已经发布分享红包');
		}
		$data['amount'] = I('post.amount', '', 'trim');
		$data['amount'] = intval($data['amount'] * 100) / 100;
		$data['count'] = I('post.count', 0, 'intval');
		$data['task_views'] = I('post.task_views', 0, 'intval');
		$data['jobs_id'] = $jobs['id'];
		$data['uid'] = $jobs['uid'];
		$data['company_id'] = $jobs['company_id'];
		$data['jobs_name'] = $jobs['jobs_name'];
		$data['payment'] = I('post.payment', 'wxpay', 'trim');
		$reg = $shareAllowanceMod->share_allowance($data);
		if ($reg['state']) {
			$allowance = $shareAllowanceMod->find($reg['data']);
			$paysetarr['payFrom'] = 'pc';
			$paysetarr['type'] = $allowance['payment'];
			$paysetarr['ordsubject'] = '职位分享红包';
			$paysetarr['ordbody'] = '职位分享红包';
			$paysetarr['ordtotal_fee'] = $allowance['pay_amount'];
			$paysetarr['oid'] = $allowance['oid'];
			$paysetarr['pay_resource'] = 'share_allowance';
			$r = D('Payment')->pay($paysetarr);
			if (!$r['state']) {
				IS_AJAX && $this->ajaxReturn(0, $r['msg']);
				$this->error($reg['msg']);
			}
			if (IS_AJAX && $paysetarr['type'] == 'wxpay') {
				$path = QSCMS_DATA_PATH . 'wxpay/';
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				fopen($path . $allowance['oid'] . '.tmp', "w") or die("无法打开缓存文件!");
				session('wxpay_share_allowance_no', $allowance['oid']);
				$this->ajaxReturn(1, '回调成功', C('qscms_site_dir') . 'index.php?m=Home&c=Qrcode&a=index&url=' . $r['data']);
			}
		} else {
			IS_AJAX && $this->ajaxReturn(0, $reg['msg']);
			$this->error($reg['msg']);
		}
	}
	/**
	 * [share_allowance_check 验证微信支付是否成功]
	 */
	public function share_allowance_check()
	{
		if (file_exists(QSCMS_DATA_PATH . 'wxpay/' . session('wxpay_share_allowance_no') . '.tmp')) {
			$this->ajaxReturn(0, '回调成功');
		} else {
			session('wxpay_share_allowance_no', NULL);
			$this->ajaxReturn(1, '回调成功', U('Home/Company/jobs_list'));
		}
	}
	public function share_allowance_explain()
	{
		$html = $this->fetch('Company/service/share_allowance_explain');
		$this->ajaxReturn(1, '获取数据成功！', $html);
	}
}
