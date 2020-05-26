<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CompanyMembersController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Members');
    }
    /**
     * 企业会员列表
     */
    public function index(){
        $this->_name = 'Members';
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'members';
        $settr=I('get.settr',0,'intval');
        $source=I('get.source',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where[$table_name.'.username']=array('like',$key.'%');break;
                case 2:
                    $where[$table_name.'.uid']=array('eq',$key);break;
                case 3:
                    $where[$table_name.'.email']=array('like',$key.'%');break;
                case 4:
                    $where[$table_name.'.mobile']=array('like',$key.'%');break;
				case 5:
                    $where['c.companyname']=array('like','%'.$key.'%');break;  //新增会员中心企业名称搜素
            }
        }else{
            if ($settr>0){
                $tmpsettr=strtotime("-".$settr." day");
                $where['reg_time']=array('gt',$tmpsettr);
            }
            if ($source>0){
                $where['reg_source']=array('eq',$source);
            }
        }
        $where['utype'] = 1;
        if('' != $is_bind = I('request.is_bind')){
            if($is_bind){
                $where['b.is_bind'] = intval($is_bind);
                $where['b.openid'] = array('neq','');
            }else{
                $where['b.is_bind'] = array(array('eq',0),array('is',null), 'or');
            }
        }
        $this->field = $table_name.".*,c.companyname,c.contents as c_contents,c.id as company_id,b.is_bind";
        $this->order = $table_name.'.uid '.'desc';
        $joinsql[0] = 'left join '.$db_pre."company_profile as c on ".$table_name.".uid=c.uid";
        $joinsql[1] = 'left join '.$db_pre."members_bind as b on ".$table_name.".uid=b.uid and b.type='weixin'";
        $this->join = $joinsql;
        $this->where = $where;
        parent::index();
    }
    /**
     * 删除会员
     */
    public function delete(){
        $tuid = I('post.tuid','','trim')!=''?I('post.tuid'):$this->error('你没有选择会员！');
        $sitegroup_uids = M('Members')->where(array('uid'=>array('in',$tuid)))->getField('sitegroup_uid',true);
        if(false===D('Members')->delete_member($tuid)) $this->error('删除会员失败！');
        $type['_user'] = 1;
        D('CompanyProfile')->admin_delete_company($tuid);
        $type['_company'] = 1;
        D('Jobs')->admin_delete_jobs_for_uid($tuid);
        $type['_jbos'] = 1;
        D('Resume')->admin_del_resume_for_uid($tuid);
        $type['_resume'] = 1;
        if(C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')){
            require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
            $name = 'sitegroup_passport';
            $passport = new $name();
            false === $passport->delete($sitegroup_uids,$type) && $this->error($passport->get_error());
        }
        $this->success('删除成功！');
    }
}
?>