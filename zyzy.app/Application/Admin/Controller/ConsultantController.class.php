<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ConsultantController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Consultant');
    }
    public function _before_del($list){
        foreach ($list as $key => $value) {
            @unlink(C('qscms_attach_path').'consultant/'.$value['pic']);
        }
    }
    public function _after_del($ids){
        if($this->_name == 'Consultant'){
            $result = D('Members')->where(array('consultant'=>array('in',$ids)))->select();
            foreach ($result as $key => $val) {
                $this->_mod->set_consultant($val);
            }
        }
    }
    /**
     * 管理
     */
    /*public function manage(){
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'members';
        $consultantid = I('request.id',0,'intval');
        $consultant = $this->_mod->where(array('id'=>$consultantid))->find();
        $this->assign('consultant',$consultant);
        $map['consultant'] = $consultantid;
        $this->order = $this_t.'.uid desc';
        $this->join = $db_pre."company_profile as c on ".$this_t.".uid=c.uid";
        parent::_list(D('Members'),$map);
        $this->display();
    }*/
    public function manage(){
        $consultantid = I('request.id',0,'intval');
        $consultant = $this->_mod->where(array('id'=>$consultantid))->find();
        $this->assign('consultant',$consultant);
        $this->_name = 'Members';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'members';
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where['c.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where['c.id']=array('eq',$key);break;
                case 3:
                    $where[$this_t.'.username']=array('like','%'.$key.'%');break;
                case 4:
                    $where[$this_t.'.uid']=array('eq',$key);break;
            }
        }
        $where['consultant'] = $consultantid;
        $this->join = 'left join ' . $db_pre."company_profile as c on ".$this_t.".uid=c.uid";
        $this->where = $where;
        $this->field = $this_t.'.*,c.id,c.companyname,c.trade';
        $this->order = $this_t.'.uid desc';
        $this->_tpl = 'manage';
        parent::index();
    }
    /**
     * 重置
     */
    public function resetting(){
        $uid = I('request.uid','','trim');
        if(!$uid){
            $this->error('请选择会员');
        }
        is_array($uid) && $uid = implode(",",$uid);
        $result = D('Members')->where(array('uid'=>array('in',$uid)))->select();
        foreach ($result as $key => $val) {
            $r = D('Consultant')->set_consultant($val,$val['consultant']);
            if($r) $i++;
        }
        if($i){
            $this->success($i.' 个企业重置顾问成功！');
        }else{
            $this->error('重置失败！');
        }
    }
    /**
     * [complain 投诉]
     */
    public function complain(){
        $this->_name="ConsultantComplaint";
        $audit = I('request.audit','','trim');
        if($audit == 2){
            unset($_REQUEST['audit']);
            $where['audit'] = array('gt',1);
            $this->where = $where;
        }
        parent::index();
    }
    public function complain_audit(){
        $id = I('request.id');
        $audit= I('request.audit',0,'intval');
        $audit=intval($_POST['audit']);
        if (empty($id)) $this->error("您没有选择项目！");
        $this->_mod = D('ConsultantComplaint');
        $num = $this->_mod->where(array('id'=>array('in',$id)))->setField('audit',intval($audit));
        if($num){
          $this->success("设置成功！共影响 {$num}行 ");
        }else{
          $this->error("设置失败！");
        }
    }
    public function complain_delete(){
        $this->_name = 'ConsultantComplaint';
        parent::delete();
    }
}
?>