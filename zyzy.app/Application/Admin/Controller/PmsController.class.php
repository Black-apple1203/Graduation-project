<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class PmsController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }
    public function index(){
    	$this->_name = 'PmsSys';
    	parent::index();
    }
    public function add(){
    	if(IS_POST){
    		if (!I('post.us')){
    			$this->_name = 'PmsSys';
				parent::add();
			}else{
				$tosuername=explode("\n",I('post.us'));
				if (count($tosuername)==0 || empty($tosuername))
				{
					$this->error('用户名填写错误！');exit;
				}
				else
				{
					$s=0;
					$msg=I('post.msg','','trim');
					$time=time();
					$insertArr = array();
					foreach ($tosuername as $u){ 
						$u=trim($u);
						if(!empty($u))
						{
							$userinfo = D('Members')->where(array('username'=>$u))->find();
							if (intval($userinfo['uid'])>0)
							{
								$data['msgtype'] = 1;
								$data['msgtouid'] = $userinfo['uid'];
								$data['msgtoname'] = $userinfo['username'];
								$data['msgtoutype'] = $userinfo['utype'];
								$data['message'] = $msg;
								$data['dateline']=$time;
								$data['replytime']=$time;
								$data['new']=1;
								$insertArr[] = $data;
								unset($userinfo,$data);
								$s++;
							}
						}
		 			}
					if ($s>0){
						D('Pms')->addAll($insertArr);
						$this->ajaxReturn(1,"发送成功！共发给了 {$s} 个会员");exit;
					}else{
						$this->ajaxReturn(0,"发送失败！请检查会员名称是否正确");exit;
					}			
				}
			}
    	}else{
    		$this->display();
    	}
    }
    public function delete(){
    	$this->_name = 'PmsSys';
    	parent::delete();
    }
    public function edit(){
    	$this->_name = 'PmsSys';
    	parent::edit();
    }
}