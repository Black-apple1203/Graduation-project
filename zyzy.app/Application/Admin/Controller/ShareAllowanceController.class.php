<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class ShareAllowanceController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [index 红包任务列表]
     */
    public function index(){
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where['uid'] = intval($key);
                    break;
                case 3:
                    $where['jobs_id'] = intval($key);
                    break;
            }
        }
        $this->_mod = D('ShareAllowancePartake');
        $this->_name = 'ShareAllowancePartake';
        $this->where = $where;
        $this->custom_fun = '_format_partake_list';
        parent::index();
    }
    protected function _format_partake_list($list){
        foreach ($list as $key => $val) {
            $uids[] = $val['uid'];
        }
        if($uids){
            $uids = array_flip(array_flip($uids));
            $user = M('Members')->where(array('uid'=>array('in',$uids)))->getfield('uid,username,avatars');
            $blacklist = M('ShareAllowanceBlacklist')->where(array('uid'=>array('in',$uids)))->getfield('uid,id');
            foreach ($list as $key => $val) {
                $list[$key]['username'] = $user[$val['uid']]['username']?:'该账号已删除';
                $list[$key]['avatars'] = attach($user[$val['uid']]['avatars'],'avatar');
                $list[$key]['black'] = $blacklist[$val['uid']] ? 1 : 0;
            }
        }
        return $list;
    }
    /**
     * [ajax_view_list 加载任务查看日志]
     */
    public function ajax_view_list(){
        $id = I('request.id',0,'intval');
        !$id && $this->ajaxReturn(0,'请选择分享人');
        $list = M('ShareAllowanceView')->where(array('pid'=>$id))->select();
        $this->assign('list',$list);
        $data['html'] = $this->fetch();
        $this->ajaxReturn(1,'日志获取成功',$data);
    }
    /**
     * [jobs 红包列表]
     */
    public function jobs(){
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where['jobs_id'] = intval($key);
                    break;
                case 3:
                    $where['uid'] = intval($key);
                    break;
            }
        }else{
            if ($addtimesettr = I('request.addtimesettr', 0, 'intval')) {
                $where['pay_time'] = array('gt', strtotime("-" . $addtimesettr . " day"));
                $this->sort = 'pay_time';
            }
        }
        $this->_mod = D('ShareAllowance');
        $this->_name = 'ShareAllowance';
        $where['pay_status'] = 1;
        $this->where = $where;
        $this->custom_fun = '_format_jobs_list';
        parent::index();
    }
    protected function _format_jobs_list($list){
        foreach ($list as $key => $val) {
            $cids[] = $val['company_id'];
        }
        if($cids){
            $cids = array_flip(array_flip($cids));
            $company = M('CompanyProfile')->where(array('id'=>array('in',$cids)))->getfield('id,companyname');
            foreach ($list as $key => $val) {
                $list[$key]['companyname'] = $company[$val['company_id']];
                $list[$key]['jobs_url'] = url_rewrite('QS_jobsshow',array('id'=>$val['jobs_id']));
            }
        }
        return $list;
    }
    /**
     * [ajax_partake_list 加载分享日志]
     */
    public function ajax_partake_list(){
        $sid = I('request.sid',0,'intval');
        !$sid && $this->ajaxReturn(0,'请选择职位！');
        $list = M('ShareAllowancePartake')->field('id,uid,oid,pay_time,pay_amount,service_charge')->where(array('sid'=>$sid,'pay_status'=>1))->select();
        $list = $this->_format_partake_list($list);
        $this->assign('list',$list);
        $data['html'] = $this->fetch();
        $this->ajaxReturn(1,'日志获取成功',$data);
    }
    public function ajax_status(){
        if (!$id = I('request.id',0,'intval')) $this->ajaxReturn(0, '请选择任务信息！');
        $reg = M('ShareAllowancePartake')->where(array('status'=>0))->setfield('status',2);
        if($reg === false) $this->ajaxReturn(0,'任务停止失败，信息不存在或已经删除！');
        $this->ajaxReturn(1,'任务停止成功！');
    }
    /**
     * [ajax_partake_audit 红包审核通过，发放红包]
     */
    public function ajax_partake_audit(){
        if (!$id = I('request.id',0,'intval')){
            IS_AJAX && $this->ajaxReturn(0, '请选择审核信息！');
            $this->error('请选择审核信息！');
        }
        if(IS_AJAX){
            $this->assign('id', $id);
            $html = $this->fetch();
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }elseif(IS_POST){
            $partakeData['audit'] = I('post.audit',0,'intval');
            $partakeData['note'] = I('post.note','','trim');
            if(!in_array($partakeData['audit'],array(1,3))) $this->error('请正确选择审核状态！');
            $partake = M('ShareAllowancePartake')->find($id);
            if(!$partake) $this->error('审核失败，信息不存在或已经删除！');
            if($partake['status'] != 1) $this->error('审核失败，任务未完成或已停止！');
            if($partake['pay_time'] == 3) $this->error('审核失败，红包已全部发放!');
            if($partake['audit'] != 2) $this->error('信息已经审核!');
            $allowance = M('ShareAllowance')->find($partake['sid']);
            if(!$allowance) $this->error('职位分享红包不存在！');
            if($allowance['issued'] >= $allowance['count']) $this->error('职位分享红包已经发完！');
            $s = true;
            $userInfo=M('Members')->where(array('uid'=>array('in',array($partake['uid'],$allowance['uid']))))->index('uid')->select();
            $personal_info = $userInfo[$partake['uid']];
            $company_info = $userInfo[$allowance['uid']];
            if($partakeData['audit'] == 1){
                //审核通过，生成订单，发放红包
                $partakeData['oid'] = 'WSHARE'.date('ymd',time()).date('His',time());
                if (C('qscms_share_allowance_service_charge') > 0) {
                    $amount = $partake['amount'] * (1 - C('qscms_share_allowance_service_charge') / 100);
                    $partakeData['pay_amount'] = $amount < 1 ? 1 : $amount;
                    $partakeData['service_charge'] = $partake['amount'] - $partakeData['pay_amount'];
                } else {
                    $partakeData['pay_amount'] = $partake['amount'];
                }
                $userbind = D('MembersBind')->where(array('uid'=>$partake['uid'],'type'=>'weixin'))->find();
                if ($userbind) {
                    include QSCMSLIB_PATH . "pay/wxpay/wxpay.class.php";
                    $pay_type = D('Payment')->get_cache();
                    $setting = $pay_type['wxpay'];
                    $payObj = new \wxpay_pay($setting);
                    $data['re_openid'] = $userbind['openid'];
                    $data['mch_billno'] = $partakeData['oid'];
                    $data['total_amount'] = $partakeData['pay_amount'];
                    $data['scene_id'] = 'PRODUCT_2';
                    $data['wishing'] = '欢迎关注'.C('qscms_site_name').'微信公众平台';
                    $data['send_name'] = C('qscms_site_name');
                    $data['act_name'] = '职位分享红包';
                    $result = $payObj->red_package($data);
                    if ($result) {
                        if(($allowance['issued'] + 1) >= $allowance['count']){
                            M('ShareAllowancePartake')->where(array('id'=>array('neq',$id),'sid'=>$allowance['id'],'audit'=>2,'status'=>1))->setfield('pay_status',3);
                        }
                        M('ShareAllowance')->where(array('id'=>$allowance['id']))->setInc('issued');
                        $partakeData['pay_time'] = time();
                        $partakeData['pay_status'] = 1;
                        $surplus = $allowance['count'] - $allowance['issued'] - 1;
                        if(false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
                        //推广人完成分享任务并领取红包(个人)
                        if ($sms['set_share_allowance_pay_personal'] == 1){
                            $sendSms['mobile']=$personal_info['mobile'];
                            $sendSms['tpl']='set_share_allowance_pay_personal';
                            $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$partake['jobs_name']);
                            D('Sms')->sendSms('notice',$sendSms);
                        }
                        //推广人完成分享任务并领取红包(企业)
                        if ($sms['set_share_allowance_pay_company'] == 1){
                            $sendSms['mobile']=$company_info['mobile'];
                            $sendSms['tpl']='set_share_allowance_pay_company';
                            $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$partake['jobs_name'],'username'=>$personal_info['username'],'surplus'=>$surplus);
                            D('Sms')->sendSms('notice',$sendSms);
                        }
                        //任务红包全部发放完毕，信息汇总
                        if ($sms['set_share_allowance_end'] == 1){
                            $sendSms['mobile']=$company_info['mobile'];
                            $sendSms['tpl']='set_share_allowance_end';
                            $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$allowance['jobs_name'],'complete'=>$allowance['complete'],'share'=>$allowance['share']);
                            D('Sms')->sendSms('notice',$sendSms);
                        }
                        //微信通知
                        if (C('apply.Weixin')) {
                            D('WeixinTplMsg')->set_share_allowance_pay_personal($partake['uid'], $partake['jobs_name'], $partakeData['service_charge'], $partakeData['amount']);
                            D('WeixinTplMsg')->set_share_allowance_pay_company($allowance['uid'], $allowance['jobs_name'], $personal_info['username'], $surplus);
                            D('WeixinTplMsg')->set_share_allowance_end($allowance['uid'], $allowance['jobs_name'], $allowance['complete'], $allowance['share']);
                        }
                        write_members_log($personal_info, 'share_allowance', "会员【{$personal_info['username']}】完成职位分享红包任务，管理员发放红包。红包金额：{$partakeData['pay_amount']}，服务费：{$partakeData['service_charge']}。", false,'',C('visitor.id'),C('visitor.username'));
                    } else {
                        $partakeData['note'] = $payObj->getError();
                        $partakeData['pay_status'] = 2;
                    }
                } else {
                    $partakeData['note'] = '该用户未绑定微信!';
                    $partakeData['pay_status'] = 2;
                }
                $audit_cn = '审核通过';
            }else{
                $audit_cn = '审核不通过';
            }
            $reg = M('ShareAllowancePartake')->where(array('id'=>$id,'audit'=>2))->save($partakeData);
            if($reg === false) $this->error('审核失败，信息不存在或已经删除！');
            if(!$reg) $this->error('信息已经审核');
            write_members_log($personal_info, 'share_allowance', "将会员【{$personal_info['username']}】的职位分享红包设为{$audit_cn}，备注：{$partakeData['note']}。", false,'',C('visitor.id'),C('visitor.username'));
            $this->success("设置成功！");
        }
    }
    /**
     * [ajax_partake_pay 红包继续发放]
     */
    public function ajax_partake_pay(){
        if (!$id = I('request.id',0,'intval')) $this->ajaxReturn(0,'请选择红包发放信息！');
        $partake = M('ShareAllowancePartake')->find($id);
        if(!$partake) $this->ajaxReturn(0,'发放失败，信息不存在或已经删除！');
        if($partake['status'] != 1) $this->ajaxReturn(0,'发放失败，任务未完成或已停止！');
        if($partake['pay_time'] == 3) $this->ajaxReturn(0,'发放失败，红包已全部发放!');
        if($partake['audit'] != 1) $this->ajaxReturn(0,'发放失败，信息审核不通过!');
        $allowance = M('ShareAllowance')->find($partake['sid']);
        if(!$allowance) $this->ajaxReturn(0,'职位分享红包不存在！');
        if($allowance['issued'] >= $allowance['count']) $this->ajaxReturn(0,'职位分享红包已经发完！');
        //审核通过，生成订单，发放红包
        $userbind = D('MembersBind')->where(array('uid'=>$partake['uid'],'type'=>'weixin'))->find();
        if ($userbind) {
            include QSCMSLIB_PATH . "pay/wxpay/wxpay.class.php";
            $pay_type = D('Payment')->get_cache();
            $setting = $pay_type['wxpay'];
            $payObj = new \wxpay_pay($setting);
            $data['re_openid'] = $userbind['openid'];
            $data['mch_billno'] = $partake['oid'];
            $data['total_amount'] = $partake['pay_amount'];
            $data['scene_id'] = 'PRODUCT_2';
            $data['wishing'] = '欢迎关注'.C('qscms_site_name').'微信公众平台';
            $data['send_name'] = C('qscms_site_name');
            $data['act_name'] = '职位分享红包';
            $result = $payObj->red_package($data);
            if ($result) {
                if(($allowance['issued'] + 1) >= $allowance['count']){
                    M('ShareAllowancePartake')->where(array('id'=>array('neq',$id),'sid'=>$allowance['id'],'audit'=>2,'status'=>1))->setfield('pay_status',3);
                }
                M('ShareAllowance')->where(array('id'=>$allowance['id']))->setInc('issued');
                $partakeData['pay_time'] = time();
                $partakeData['pay_status'] = 1;
                $userInfo=M('Members')->where(array('uid'=>array('in',array($partake['uid'],$allowance['uid']))))->index('uid')->select();
                $personal_info = $userInfo[$partake['uid']];
                $company_info = $userInfo[$allowance['uid']];
                $surplus = $allowance['count'] - $allowance['issued'] - 1;
                if(false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
                //推广人完成分享任务并领取红包(个人)
                if ($sms['set_share_allowance_pay_personal'] == 1){
                    $sendSms['mobile']=$personal_info['mobile'];
                    $sendSms['tpl']='set_share_allowance_pay_personal';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$partake['jobs_name']);
                    D('Sms')->sendSms('notice',$sendSms);
                }
                //推广人完成分享任务并领取红包(企业)
                if ($sms['set_share_allowance_pay_company'] == 1){
                    $sendSms['mobile']=$company_info['mobile'];
                    $sendSms['tpl']='set_share_allowance_pay_company';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$partake['jobs_name'],'username'=>$personal_info['username'],'surplus'=>$surplus);
                    D('Sms')->sendSms('notice',$sendSms);
                }
                //任务红包全部发放完毕，信息汇总
                if ($sms['set_share_allowance_end'] == 1){
                    $sendSms['mobile']=$company_info['mobile'];
                    $sendSms['tpl']='set_share_allowance_end';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'),'jobs_name'=>$allowance['jobs_name'],'complete'=>$allowance['complete'],'share'=>$allowance['share']);
                    D('Sms')->sendSms('notice',$sendSms);
                }
                //微信通知
                if (C('apply.Weixin')) {
                    D('WeixinTplMsg')->set_share_allowance_pay_personal($partake['uid'], $partake['jobs_name'], $partakeData['service_charge'], $partakeData['amount']);
                    D('WeixinTplMsg')->set_share_allowance_pay_company($allowance['uid'], $allowance['jobs_name'], $personal_info['username'], $surplus);
                    D('WeixinTplMsg')->set_share_allowance_end($allowance['uid'], $allowance['jobs_name'], $allowance['complete'], $allowance['share']);
                }
                write_members_log($personal_info, 'share_allowance', "会员【{$personal_info['username']}】完成职位分享红包任务，管理员发放红包。红包金额：{$partake['pay_amount']}，服务费：{$partake['service_charge']}。", false,'',C('visitor.id'),C('visitor.username'));
            } else {
                $partakeData['note'] = $payObj->getError();
                $partakeData['pay_status'] = 2;
            }
        } else {
            $partakeData['note'] = '该用户未绑定微信!';
            $partakeData['pay_status'] = 2;
        }
        $reg = M('ShareAllowancePartake')->where(array('id'=>$id,'audit'=>1))->save($partakeData);
        if($reg === false) $this->ajaxReturn(0,'发放失败，信息不存在或已经删除！');
        if(!$reg) $this->ajaxReturn(0,'发放失败！');
        $this->ajaxReturn(1,'发放成功！');
    }
    /**
     * [config 参数配置]
     */
    public function config(){
        if(IS_POST){
            $this->_mod = D('Config');
            $this->_name = 'Config';
            $this->_edit();
        }else{
            $this->_mod = D('ShareAllowanceBlacklist');
            $this->_name = 'ShareAllowanceBlacklist';
            $this->_tpl = 'config';
            $this->custom_fun = '_format_black_list';
            parent::index();
        }
    }
    protected function _format_black_list($list){
        foreach ($list as $key => $val) {
            $uids[] = $val['uid'];
        }
        if($uids){
            $uids = array_flip(array_flip($uids));
            $user = M('Members')->where(array('uid'=>array('in',$uids)))->getfield('uid,username,avatars');
            foreach ($list as $key => $val) {
                $list[$key]['username'] = $user[$val['uid']]['username'];
                $list[$key]['avatars'] = attach($user[$val['uid']]['avatars'],'avatar');
            }
        }
        return $list;
    }
    /**
     * [ajax_blacklist_add 添加黑名单]
     */
    public function ajax_blacklist_add(){
        if (!$uid = I('request.uid',0,'intval')){
            IS_AJAX && $this->ajaxReturn(0, '请选择分享人信息！');
            $this->error('请选择分享人信息！');
        }
        if(IS_AJAX){
            $this->assign('uid', $uid);
            $html = $this->fetch();
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }elseif(IS_POST){
            $note = I('request.note','','trim');
            $reg = D('ShareAllowanceBlacklist')->addBlacklist(array('uid'=>$uid,'note'=>$note));
            if($reg['state']){
                $personal_info = M('Members')->where(array('uid'=>$uid))->find();
                //任务红包全部发放完毕，信息汇总
                if(false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
                if ($sms['set_share_allowance_blacklist'] == 1){
                    $sendSms['mobile']=$personal_info['mobile'];
                    $sendSms['tpl']='set_share_allowance_blacklist';
                    $sendSms['data']=array('sitename'=>C('qscms_site_name'));
                    D('Sms')->sendSms('notice',$sendSms);
                }
                //微信通知
                if (C('apply.Weixin')) D('WeixinTplMsg')->set_share_allowance_blacklist($uid);
                write_members_log($personal_info, 'share_allowance', "会员【{$personal_info['username']}】被管理员移入职位分享红包黑名单。备注：{$note}。", false,'',C('visitor.id'),C('visitor.username'));
                $this->success($reg['msg']);
            }else{
                $this->error($reg['msg']);
            }
        }
    }
    /**
     * [ajax_blacklist_delete 删除黑名单]
     */
    public function ajax_blacklist_delete(){
        if (!$uid = I('request.uid',0,'intval')) $this->ajaxReturn(0, '请选择用户信息！');
        $reg = M('ShareAllowanceBlacklist')->where(array('uid'=>$uid))->delete();
        if($reg){
            $personal_info = M('Members')->where(array('uid'=>$uid))->find();
            write_members_log($personal_info, 'share_allowance', "会员【{$personal_info['username']}】被管理员移出职位分享红包黑名单。", false,'',C('visitor.id'),C('visitor.username'));
            $this->ajaxReturn(1, '移除成功！');
        }else{
            $reg === false && $this->ajaxReturn(0, '用户不存在或已经删除！');
            $this->ajaxReturn(1, '移除成功！');
        }
    }
    /**
     * [rule 通知规则]
     */
    public function rule(){
        if(IS_POST){
            $type = I('get.type','sms','trim');
            if($type == 'sms'){
                foreach (I('post.') as $key => $val) {
                    $reg = D('SmsConfig')->where(array('name' => $key))->save(array('value' => intval($val)));
                    if(false === $reg){
                        IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                        $this->error(L('operation_failure'));
                    }
                }
            }elseif($type == 'weixin'){
                $post = I('post.');
                foreach ($post['alias'] as $key => $val) {
                    $data = array();
                    $data['value'] = $post[$val.'_value'];
                    $data['template_id'] = $post['template_id'][$key];
                    D('WeixinTplMsg')->where(array('alias' => $val))->save($data);
                }
            }
            $this->success(L('operation_success'));
        }else{
            if(false === $smsConfig = F('sms_config')) $smsConfig = D('SmsConfig')->config_cache();
            $this->assign('smsConfig',$smsConfig);
            $weixin_config_list = D('WeixinTplMsg')->where(array('module'=>'ShareAllowance'))->select();
            $this->assign('weixin_config_list',$weixin_config_list);
            $this->display();
        }
    }

    /**
     * [blacklist_delete 使用说明]
     */
    public function explain(){
        $this->display();
    }
}
?>