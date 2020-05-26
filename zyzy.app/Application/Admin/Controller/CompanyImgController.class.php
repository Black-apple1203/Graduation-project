<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CompanyImgController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CompanyImg');
    }
    public function index(){
        $this->_name = 'CompanyImg';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'company_img';
        $key = I('get.key','','trim');
        $key_type = I('get.key_type',0,'intval');
        if ($key && $key_type>0)
        {
            switch($key_type){
                case 1:
                    $where['c.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where['c.id']=array('eq',$key);break;
                case 3:
                    $where[$table_name.'.id']=array('eq',$key);break;
                case 4:
                    $where[$table_name.'.title']=array('like',$key.'%');break;
            }
        }
        if ($settr = I('get.settr',0,'intval')){
            $tmpsettr=strtotime("-".$settr." day");
            $where[$table_name.'.addtime'] = array('gt',$tmpsettr);
        }
        $this->where = $where;
        $this->field =  $table_name.".*,".$table_name.".id as i_id,c.companyname,c.email,c.telephone";
        $this->join = $db_pre.'company_profile as c on '.$table_name.'.company_id=c.id';
        $this->order = 'field('. $table_name.'.audit,2) desc ,id '.'desc';
        $this->pagesize = 16;
        parent::index();
    }

    /**
     * 认证图片
     */
    public function set_audit(){
        $id = I('request.id');
        if(!$id){
            $this->error('请选择图片');
        }
        $audit = I('post.audit',0,'intval');
        $pms_notice = I('post.pms_notice',0,'intval');
        $reason = I('post.reason','','trim');
        $result = $this->_mod->set_audit($id,$audit,$reason,$pms_notice);
        if($result){
            $this->success("设置成功！");
        }else{
            $this->error('设置失败！');
        }
    }
    public function _before_del($list){
        if(C('qscms_qiniu_open')==1){
            $qiniu = new \Common\ORG\qiniu;
        }
        foreach ($list as $key => $value) {
            @unlink(C('qscms_attach_path').'company_img/'.$value['img']);
            C('qscms_qiniu_open')==1 && $qiniu->delete($value['img']);
        }
    }
}
?>