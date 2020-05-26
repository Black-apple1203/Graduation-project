<?php

namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class InviteController extends ConfigbaseController {
    public function _initialize() {
        parent::_initialize();
        $this->_name = 'InviteAllowance';		
    }
	public function index() {		
		if(IS_POST){
            parent::_edit();
        }else{
            $data_count = D('InviteBlacklist')->count();
            $pager = pager($data_count, 5);
            $page = $pager->fshow();
            $list = D('InviteBlacklist')->limit($pager->firstRow . ',' . $pager->listRows)->select();
            $this->assign('page',$page);
            $this->assign('list',$list);
            parent::edit();
        }
    }
    public function invite_list(){
        $where['is_black'] = 1;
        $this->where = $where;
        parent::index();
    }
    public function _after_search(){
        $data = $this->get('list');
        foreach ($data as $key => $val) {
            $row = $val;
            $invitee = M('Members')->where(array('uid'=>$val['invitee_uid']))->field('username')->find();
            $invite = M('Members')->where(array('uid'=>$val['inviter_uid']))->field('username')->find();
            $row['invitee_name'] =$invitee['username'];
            $row['invite_name'] =$invite['username'];
            $list[] =$row;

        }
        $this->assign('list',$list);
    }
    public function delete(){
        $id = I('get.id','','intval');
        !$id && $this->error('请选择！');
        $uid = D('InviteBlacklist')->where(array('id'=>$id))->field('uid')->find();
        $result = M('InviteBlacklist')->where(array('id'=>$id))->delete();
        if($result){
           D('InviteAllowance')->where(array('inviter_uid'=>$uid['uid']))->setfield('is_black',1);
           $this->success('删除成功！'); 
        }else{
           $this->error('删除失败！'); 
        }
    }
    public function invite_del(){
        $this->_name = 'InviteAllowance';
        parent::delete();
    }
    /**
     * 审核
     */
    public function set_audit(){
        $id = I('request.id');
        !$id && $this->error('请选择项目！');
        $this->assign('id',$id);
        $html = $this->fetch('set_audit');
        $this->ajaxReturn(1,'调用成功！',$html);
    }
    public function set_audit_save(){
        $id = I('request.id');
        !$id && $this->error('请选择项目！');
        $id_arr = is_array($id)?$id:array($id);
        $pass = I('request.pass',0,'intval');
        $note = I('request.note','','trim');
        if($pass==1){
            foreach ($id_arr as $key => $value) {
                $recordinfo = M('InviteAllowance')->find($value);
                if($recordinfo['resume_percent'] < C('qscms_inviter_perfected_resume_allowance_percent')){
                    $this->error('该简历完整度还没达到条件！');
                }
                $userbind = D('MembersBind')->get_members_bind(array('uid'=>$recordinfo['inviter_uid'],'type'=>'weixin'));
                if ($userbind) {
                    include QSCMSLIB_PATH . "pay/wxpay/wxpay.class.php";
                    $pay_type = D('Payment')->get_cache();
                    $setting = $pay_type['wxpay'];
                    $payObj = new \wxpay_pay($setting);
                    $data['re_openid'] = $userbind['openid'];
                    $data['mch_billno'] = $recordinfo['oid'];

                    if (C('qscms_inviter_service_charge') > 0 && $recordinfo['money'] >=1) {
                        $amount = $recordinfo['money'] * (1 - C('qscms_inviter_service_charge') / 100);
                    } else {
                        $amount = $recordinfo['money'];
                    }
                    $data['total_amount'] = $amount;
                    $data['scene_id'] = 'PRODUCT_2';
                    $data['wishing'] = '欢迎关注'.C('qscms_site_name').'微信公众平台';
                    $data['send_name'] = C('qscms_site_name');
                    $data['act_name'] = '邀请注册奖励';
                    $result = $payObj->red_package($data);
                    if ($result) {
                        //修改任务表状态
                        $arr['state']  = 1;
                        $arr['grant']  = 1;
                        $arr['audit_reason']  =$note;
                        M('InviteAllowance')->where(array('id'=>$id))->save($arr);
                        //写入交易记录
                        $deallog['uid'] = $recordinfo['inviter_uid'];
                        $deallog['amount'] = $data['total_amount'];
                        $deallog['service_charge'] = $recordinfo['money'] - $data['total_amount'];
                        $deallog['record_id'] = $id;
                        $deallog['note'] = '发放邀请红包';
                        $deallog['addtime'] =time();
                        D('InviteAllowanceDealLog')->add($deallog);
                        if(false === $sms = F('sms_config')) $sms = D('SmsConfig')->config_cache();
                        $personal_info=M('Members')->where(array('uid'=>$recordinfo['inviter_uid']))->find();
                        if ($sms['set_invite_allowance']=="1")
                        {
                            $sendSms['mobile']=$personal_info['mobile'];
                            $sendSms['tpl']='set_invite_allowance';
                            $sendSms['data']=array('sitename'=>C('qscms_site_name'),'username'=>$personal_info['username']);
                            D('Sms')->sendSms('notice',$sendSms);
                        }
                        //微信通知
                        D('Common/WeixinTplMsg')->set_invite_allowance($recordinfo['inviter_uid'], '邀请注册红包', $deallog['service_charge'], $deallog['amount']);
                        //操作日志
                        M('InviteAllowanceLog')->add(array('note'=>'发放邀请红包成功','record_id'=>$id,'addtime'=>time(),'username'=>C('visitor.username')));
                        $this->success('红包发放成功！');
                    } else {
                        $pay_fail_reason = $payObj->getError();
                        M('InviteAllowanceLog')->add(array('note'=>$pay_fail_reason,'record_id'=>$id,'addtime'=>time(),'username'=>C('visitor.username')));
                        $this->error($pay_fail_reason);
                    }
                } else {
                    //支付失败，写明原因，方便后台管理员操作
                    $pay_fail_reason = '该用户未绑定或者绑定信息错误!';
                    $this->error($pay_fail_reason);
                }
            }

        }else{
           //修改任务表状态
            $arr['state']  = 3;
            $arr['audit_reason']  =$note;
            M('InviteAllowance')->where(array('id'=>$id))->save($arr);
            $this->success('设置成功！');  
        }
    }
    /**
     * [rule 短信发送规则]
     */
    public function invite_rule(){
        $config_mod = D('SmsConfig');
        if(IS_POST){
            foreach (I('post.') as $key => $val) {
                $reg = D('SmsConfig')->where(array('name' => $key))->save(array('value' => intval($val)));
                if(false === $reg){
                    IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                    $this->error(L('operation_failure'));
                }
            }
            $this->success(L('operation_success'));
        }else{
            if(false === $smsConfig = F('sms_config')) $smsConfig = D('SmsConfig')->config_cache();
            $this->assign('smsConfig',$smsConfig);
            $weixin_config_list = D('WeixinTplMsg')->where(array('module'=>'invite'))->select();
            $this->assign('weixin_config_list',$weixin_config_list);
            $this->display();
        }
    }
    /**
     * [rule 微信发送规则]
     */
    public function weixin_rule(){
        if(IS_POST){
            $post = I('post.');
            foreach ($post['alias'] as $key => $val) {
                unset($data);
                $data['value'] = $post[$val.'_value'];
                $data['template_id'] = $post['template_id'][$key];
                D('WeixinTplMsg')->where(array('alias' => $val))->save($data);
            }
           $this->success(L('operation_success'));
        }  
    }
    /**
     * 使用说明
     */
    public function invite_instructions(){
        $this->display();
    }
}