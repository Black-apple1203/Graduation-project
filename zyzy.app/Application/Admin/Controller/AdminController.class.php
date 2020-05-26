<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class AdminController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Admin');
    }
    public function clear_bind() {
        $id=I('request.id','','intval');
        if($id){
            $sqlarr['openid']='';
            $res = M('Admin')->where(array('id'=>$id))->save($sqlarr);
            if($res!==false){
                $this->ajaxReturn(1,'微信解绑成功！');
            }else{
                $this->ajaxReturn(0,'微信解绑失败！');
            }
        }
        $this->error('管理员不存在！');
    }
    public function _before_index() {
        $this->list_relation = true;
    }
    public function _before_add() {
        if(false === $roles = F('admin_role_list')){
            $roles = D('AdminRole')->role_cache();
        }
        $this->assign('roles', $roles);
    }
    public function _before_insert($data='') {
        if(trim($data['password'])==''){
            unset($data['password']);
            unset($data['pwd_hash']);
        }else{
            $data['password'] = md5(md5($data['password']).$data['pwd_hash'].C("PWDHASH"));
        }
        return $data;
    }
    public function _before_edit() {
        $this->_before_add();
    }
    public function _before_update($data=''){
        return $this->_before_insert($data);
    }
    public function _before_delete(){
        $id = I('request.id',0,'intval');
        if($id==1){
            $this->error('超级管理员不允许被删除！');
        }
    }
    /**
     * [details 管理员详情]
     */
    public function details(){
    	$this->_before_add();
    	$this->edit();
    }
}
?>