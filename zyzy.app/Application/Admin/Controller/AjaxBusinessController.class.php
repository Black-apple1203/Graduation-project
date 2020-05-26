<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class AjaxBusinessController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * 套餐详情
     */
    public function setmeal(){
        $uid = I('get.uid',0,'intval');
        $info = D('MembersSetmeal')->get_user_setmeal($uid);
        $list = M('MembersLog')->where(array('log_uid'=>$uid))->order('log_id desc')->limit(100)->select();
        $this->assign('info',$info);
        $this->assign('list',$list);
        $html = $this->fetch('setmeal');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 积分详情
     */
    public function points(){
        $uid = I('get.uid',0,'intval');
        $list = D('MembersHandsel')->where(array('uid'=>$uid))->order('id desc')->limit(100)->select();
        $this->assign('userpoints',D('MembersPoints')->get_user_points($uid));
        $this->assign('list',$list);
        $html = $this->fetch('points');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 订单
     */
    public function order(){
        $uid = I('get.uid',0,'intval');
        $list = D('Order')->where(array('uid'=>$uid))->order('id desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('order');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 推广
     */
    public function promotion(){
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'promotion';
        $uid = I('get.uid',0,'intval');
        $list = D('Promotion')->join('left join '.$db_pre.'jobs as j on j.id='.$table_name.'.cp_jobid')->where(array('cp_uid'=>$uid))->order('cp_id desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('promotion');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 个人推广
     */
    public function promotion_per(){
        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre.'personal_service_stick_log';
        $uid = I('get.uid',0,'intval');
        $list = D('PersonalServiceStickLog')->join('left join '.$db_pre.'resume as r on r.id='.$table_name.'.resume_id')->where(array('resume_uid'=>$uid))->order($table_name.'.id desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('promotion_per');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 增值服务
     */
    public function increment(){
        $uid = I('get.uid',0,'intval');
        $list = D('Order')->where(array('uid'=>$uid,'order_type'=>array('gt',5)))->order('id desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('increment');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 随机红包
     */
    public function perfected_allowance(){
        $uid = I('get.uid',0,'intval');
        $list = D('MembersPerfectedAllowance')->where(array('uid'=>$uid))->order('id desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('perfected_allowance');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 模板
     */
    public function tpl(){
        $uid = I('get.uid',0,'intval');
        $tpl_dir = APP_PATH.'/Home/View/';
        $tpl_file_dir = 'tpl_company';
        $company_profile = D('CompanyProfile')->where(array('uid'=>$uid))->find();
        $current_tpl = $company_profile['tpl']?$company_profile['tpl']:'default';
        $templates = $this->_get_templates_info($tpl_dir.$tpl_file_dir.'/'.$current_tpl."/info.txt");
        $templates['thumb_dir'] = $tpl_dir.$tpl_file_dir.'/'.$current_tpl;
        $this->assign('templates',$templates);
        $html = $this->fetch('tpl');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 风采
     */
    public function img(){
        $uid = I('get.uid',0,'intval');
        $list = D('CompanyImg')->where(array('uid'=>$uid))->select();
        $this->assign('list',$list);
        $html = $this->fetch('img');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 下载的简历
     */
    public function download_resume(){
        $uid = I('get.uid',0,'intval');
        $list = D('CompanyDownResume')->where(array('company_uid'=>$uid))->order('did desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('download_resume');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 收到的简历
     */
    public function apply(){
        $uid = I('get.uid',0,'intval');
        $list = D('PersonalJobsApply')->where(array('company_uid'=>$uid))->order('did desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('apply');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 面试邀请
     */
    public function interview(){
        $uid = I('get.uid',0,'intval');
        $list = D('CompanyInterview')->where(array('company_uid'=>$uid))->order('did desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('interview');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 申请职位
     */
    public function apply_per(){
        $uid = I('get.uid',0,'intval');
        $list = D('PersonalJobsApply')->where(array('personal_uid'=>$uid))->order('did desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('apply_per');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 面试邀请
     */
    public function interview_per(){
        $uid = I('get.uid',0,'intval');
        $list = D('CompanyInterview')->where(array('resume_uid'=>$uid))->order('did desc')->limit(100)->select();
        $this->assign('list',$list);
        $html = $this->fetch('interview_per');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    /**
     * 营业执照
     */
    public function certificate(){
        $uid = I('get.uid',0,'intval');
        $info = D('CompanyProfile')->where(array('uid'=>$uid))->find();
        $this->assign('info',$info);
        $html = $this->fetch('certificate');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
    private function _get_templates_info($file){
        $file_info = array('name'=>'', 'version'=> '', 'author'=>'', 'authorurl'=>'');
        if (!$fp = @fopen($file,'rb'))
        {
            return false;
        }
        $str = fread($fp, 200);
        @fclose($fp);
        $arr = explode("\n", $str);
        foreach ($arr as $val){
            $pos = strpos($val, ':');
            if ($pos > 0){
                $type = trim(substr($val, 0, $pos), "-\n\r\t ");
                $value = trim(substr($val, $pos+1), "/\n\r\t ");
                if ($type == 'name'){
                    $file_info['name'] = $value;
                }
                elseif ($type == 'version'){
                    $file_info['version'] = $value;
                }
                elseif ($type == 'author'){
                    $file_info['author'] = $value;
                }
                 elseif ($type == 'authorurl'){
                    $file_info['authorurl'] = $value;
                }
            }
        }
        return $file_info;
    }
}
?>