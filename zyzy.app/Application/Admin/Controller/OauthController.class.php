<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class OauthController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Oauth');
    }
    public function _after_select($info){
        $info['config'] = unserialize($info['config']);
		$info['app_config'] = unserialize($info['app_config']);
        return $info;
    }
    public function _before_update($data){
		if($data['config']){
        $data['config'] = serialize($data['config']);
		}else if($data['app_config']) {
		$data['app_config'] = serialize($data['app_config']);
		}
        return $data;
    }
    /**
     * qq账号登录
     */
    public function index(){
        if(!IS_POST){
            $info = D('Oauth')->where(array('alias'=>'qq'))->find();
            $_GET['id'] = $info['id']; 
        }
        $this->edit();
    }
    /**
     * 新浪微博账号登录
     */
    public function sina(){
        if(!IS_POST){
            $info = D('Oauth')->where(array('alias'=>'sina'))->find();
            $_GET['id'] = $info['id'];
        }
        $this->edit();
    }
    /**
     * 微信账号登录
     */
    public function weixin(){
        if(!IS_POST){
            $info = D('Oauth')->where(array('alias'=>'weixin'))->find();
            $_GET['id'] = $info['id'];
        }
        $user = M('MembersBind')->where(array('type'=>'weixin','unionid'=>''))->find();
        if(IS_POST && $user && 1 == I('request.status',0,'intval')){
            $this->error('请先同步微信数据！');
        }
        $this->assign('is_sync',$user?1:0);
        $this->edit();
    }

}