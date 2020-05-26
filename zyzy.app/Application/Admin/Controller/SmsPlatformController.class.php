<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SmsPlatformController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
    }
    public function index(){
        $this->_edit();
        $this->display();
    }
    public function stat(){
        $page = I('get.page',1,'intval');
        $username = C('qscms_sms_platform_username');
        $password = C('qscms_sms_platform_password');
        if(!$username && !$password){
            $this->assign('error_msg','权限验证失败，请检查配置参数是否正确');
            $this->assign('sms_count',0);
            $this->assign('list',array());
            $this->assign('page_html','');
        }else{
           $result = https_request('https://www.74cms.com/sms.php/Api/stat',json_encode(array('page'=>$page,'username'=>$username,'password'=>$password)));
            $result = json_decode($result,1);
            if($result['status']==1){
                $count = $result['data']['list_count'];
                $page = pager($count,10);
                $page_html   = $page->fshow(true);
                $list = $result['data']['list_data'];
                $this->assign('error_msg','');
                $this->assign('sms_count',$result['data']['sms_count']);
                $this->assign('list',$list);
                $this->assign('page_html',$page_html);
            }else{
                $this->assign('error_msg',$result['msg']);
                $this->assign('sms_count',0);
                $this->assign('list',array());
                $this->assign('page_html','');
            } 
        }
        $this->display();
    }
}
?>