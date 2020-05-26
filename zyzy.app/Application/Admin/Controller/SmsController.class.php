<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SmsController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Sms');
    }
    public function _before_index(){
        $this->order = 'ordid desc,id';
    }
    /**
     * [config_edit 短信配置,修改前回调]
     */
    public function config_edit(){
    	$this->_edit();
    	if(false === $sms_list = F('sms_list')){
			$sms_list = $this->_mod->sms_cache();
		}
		$this->assign('sms_list',$sms_list);
    	$this->display();
    }
    /**
     * [_after_select 查询前回调]
     */
    public function _after_select($data){
    	$data['config'] = unserialize($data['config']);
    	return $data;
    }
    /**
     * [testing 测试]
     */
    public function testing(){
    	if(IS_POST){
    		$mobile = I('post.mobile','','trim');
    		$type = I('post.type','captcha','trim');
    		if(!fieldRegex($mobile,'mobile')) $err = '手机号不合法！';
    		if(!$err){
    			if(true !== $reg = $this->_mod->sendSms($type,array('mobile'=>$mobile,'tpl'=>'set_testing'))){
                    $err = $reg;
                }
    		}
            $this->assign('err',$err);
    	}
    	$this->display();
    }
    /**
     * [rule 发送规则]
     */
    public function rule(){
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
	    	$this->display();
    	}
    }
    /**
     * [setup 安装]
     */
    public function setup(){
        $id = I('get.id',0,'intval');
        !$id && $this->error('请选择短信服务商！');
        if($this->_mod->where(array('id'=>$id))->setfield('status',1)){
            $this->success('安装成功！');
        }else{
            $this->error('安装失败！');
        }
    }
    /**
     * [unload 卸载]
     */
    public function unload(){
        $id = I('get.id',0,'intval');
        !$id && $this->error('请选择短信服务商！');
        if($this->_mod->where(array('id'=>$id))->setfield('status',0)){
            $this->success('卸载成功！');
        }else{
            $this->error('卸载失败！');
        }
    }
    public function send(){
        $uid = I('request.uid',0,'intval');
        $mobile = I('request.mobile','','trim');
        $map['s_uid'] = array('eq',$uid);
        $order = 's_id desc';
        $total = M('Smsqueue')->where($map)->order($order)->count();
        $pager = pager($total, 10);
        $page = $pager->fshow();
        $smslog = M('Smsqueue')->where($map)->order($order)->select();
        if (empty($url))
        {
            $url=U('send',array('mobile'=>$mobile,'uid'=>$uid));
        }
        $this->assign('url',$url);
        $this->assign('smslog',$smslog);
        $this->assign('page',$page);
        $this->display('send_sms');
    }
    public function send_sms(){
        $uid = I('request.uid',0,'intval');
        $url = I('request.url','','trim');
        $mobile = I('request.mobile','','trim');
        $txt = I('request.txt','','trim');
        if (!$uid)
        {
        $this->error('用户UID错误！');
        }
        $setsqlarr['s_mobile']=$mobile?$mobile:$this->error('手机不能为空！'); 
        $setsqlarr['s_body']=$txt?$txt:$this->error('短信内容不能为空！');
        $setsqlarr['s_addtime']=time();
        $setsqlarr['s_uid']=$uid;
        if(D('Sms')->sendSms('other',array('mobile'=>$setsqlarr['s_mobile'],'tplStr'=>$setsqlarr['s_body']))){
            $setsqlarr['s_sendtime']=time();
            $setsqlarr['s_type']=2;//发送成功
            D('Smsqueue')->add($setsqlarr);
            unset($setsqlarr);
            $this->success('发送成功！',$url);
        }
        else
        {
            $setsqlarr['s_sendtime']=time();
            $setsqlarr['s_type']=3;//发送失败
            D('Smsqueue')->add($setsqlarr);
            unset($setsqlarr);
            $this->error('发送失败，错误未知！',$url);
        }
    }
    public function again_send(){
        $id = I('request.id',0,'intval');
        if (empty($id))
        {
        $this->error("请选择要发送的项目！");
        }
        $result = D('Smsqueue')->find($id);
        $map['m_id'] = $id;
        if(D('Sms')->sendSms('other',array('mobile'=>$result['s_mobile'],'tplStr'=>$result['s_body']))){
            $setsqlarr['m_sendtime']=time();
            $setsqlarr['m_type']=2;//发送成功
            D('Smsqueue')->where($map)->save($setsqlarr);
            $this->success('发送成功');
        }else{
            $setsqlarr['m_sendtime']=time();
            $setsqlarr['m_type']=3;
            D('Smsqueue')->where($map)->save($setsqlarr);
            $this->error('发送失败');
        }
    }
    public function del(){
        $id = I('request.id',0,'intval');
        if (empty($id))
        {
        $this->error("请选择要发送的项目！");
        }
        if(!is_array($id)) $id=array($id);
        $sqlin=implode(",",$id);
        if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
        {
            D('Smsqueue')->where(array('s_id'=>array('in',$sqlin)))->delete();
            $this->success("删除成功");
        }
    }
    public function tpl(){
        $type = I('request.type','','trim');
        !$type && $this->error('请选择正确的服务商！');
        if(IS_POST){
            $alias = I('post.alias');
            $value = I('post.value');
            $tpl_id = I('post.tpl_id');
            foreach ($alias as $key => $val) {
                unset($data);
                $data['value'] = $value[$key];
                $data['tpl_id'] = $tpl_id[$key];
                D('SmsTemplates')->where(array('type'=>$type,'alias' => $val))->save($data);
            }
            $this->success(L('operation_success'));
        }else{
            $list = D('SmsTemplates')->where(array('type'=>$type))->select();
            $this->assign('list',$list);
            $this->assign('type',$type);
            $this->display();
        }
    }
}
?>