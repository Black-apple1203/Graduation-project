<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CompanyController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CompanyProfile');
    }
    /**
     * 企业管理
     */
    public function index(){
        $this->_name = 'CompanyProfile';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'company_profile';
        $overtime = I('request.overtime',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        $join = array();
        $join[] = 'left join '.$db_pre."members as m on ".$this_t.".uid=m.uid";
        $this->field = $this_t.'.*,m.username,m.mobile,m.email as memail';
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where[$this_t.'.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where[$this_t.'.id']=array('eq',$key);break;
                case 3:
                    $where['m.username']=array('like','%'.$key.'%');break;
                case 4:
                    $where[$this_t.'.uid']=array('eq',$key);break;
                case 5:
                    $where[$this_t.'.address']=array('like','%'.$key.'%');break;
                case 6:
                    $where[$this_t.'.telephone']=array('like','%'.$key.'%');break;
            }
        }else{
            if($settr=I('get.settr',0,'intval')){
                $where['addtime']=array('gt',strtotime("-".$settr." day"));
            }
            if($overtime>0){
                $join[] = 'left join '.$db_pre."members_setmeal as s on ".$this_t.".uid=s.uid";
                $where['s.endtime']=array(array('neq',0),array('elt',strtotime("+".$overtime." day")),'and');
            }else if($overtime==-1){
                $join[] = 'left join '.$db_pre."members_setmeal as s on ".$this_t.".uid=s.uid";
                $where['s.expire']=array('eq',1);
            }
        }
        if('' != $is_bind = I('request.is_bind')){
            $join[] = 'left join '.$db_pre."members_bind as b on ".$this_t.".uid=b.uid and b.type='weixin'";
            $this->field .= ',b.is_bind';
            if($is_bind){
                $where['b.is_bind'] = intval($is_bind);
            }else{
                $where['b.is_bind'] = array(array('eq',0),array('is',null), 'or');
            }
        }
        $this->where = $where;
        $this->order = 'field('. $this_t.'.audit,2) desc ,id '.'desc';
        // $join[] = 'left join '.$db_pre."members_bind as b on ".$this_t.".uid=b.uid and b.type='weixin'";
        $this->join = $join;
        $this->assign('count',parent::_pending('CompanyProfile',array('audit'=>2)));
        $setmeal = D('Setmeal')->get_setmeal_cache();
        $this->assign('setmeal',$setmeal);
        parent::index();
    }
    /**
     *  套餐已到期
     */
    public function setmeal_expire(){

        $this->free_company();
    }
    /**
     *  已有订单免费会员
     */
    public function paid_order(){

        $this->free_company();
    }
    /**
     *  无订单免费会员
     */
    public function no_paid_order(){

        $this->free_company();
    }
    /**
     * 未付费企业
     */
    protected function free_company(){
        $this->_name = 'CompanyProfile';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'company_profile';
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        $is_paid=I('get.ispaid',0,'intval');
        $join = array();
        $join[] = 'left join '.$db_pre."members as m on ".$this_t.".uid=m.uid";
        if ($key && $key_type>0){
            switch($key_type){
                case 1:
                    $where[$this_t.'.companyname']=array('like','%'.$key.'%');break;
                case 2:
                    $where[$this_t.'.id']=array('eq',$key);break;
                case 3:
                    $where['m.username']=array('like','%'.$key.'%');break;
                case 4:
                    $where[$this_t.'.uid']=array('eq',$key);break;
                case 5:
                    $where[$this_t.'.address']=array('like','%'.$key.'%');break;
                case 6:
                    $where[$this_t.'.telephone']=array('like','%'.$key.'%');break;
            }
        }else{
            if($settr=I('get.settr',0,'intval')){
                $where['addtime']=array('gt',strtotime("-".$settr." day"));
            }
        }
        if($is_paid == 1){
            $where[$this_t.'.setmeal_id']=array('eq',1);
            $where[$this_t.'.order_paid']=array('eq',1);
        }elseif($is_paid == 2){
            $where[$this_t.'.setmeal_id']=array('eq',1);
            $where[$this_t.'.order_paid']=array('eq',0);
        }else{
            $join[] = 'left join '.$db_pre."members_setmeal as a on ".$this_t.".uid=a.uid";
            $where['a.expire']=array('eq',1);
        }
        $this->where = $where;
        $this->field .= $this_t.'.*,m.last_login_time';
        $this->order = "m.last_login_time desc ,$this_t.refreshtime desc";
        $this->join = $join;
        $this->_tpl = 'free_company';
        parent::index();
    }
    /**
     * 格式化列表
     */
    public function _custom_fun($list){
        return $this->_mod->admin_format_company_list($list);
    }
    /**
     * 待认证企业
     */
    public function index_noaudit(){
        $_GET['audit']=isset($_GET['audit'])?$_GET['audit']:2;
        $this->index();
    }
    /**
     * 删除企业
     */
    public function delete_company(){
        $u_id = I('request.y_id');
        if(!$u_id){
            $this->error('你没有选择企业！');
        }
        $sitegroup_uids = M('Members')->where(array('uid'=>array('in',$u_id)))->getField('sitegroup_uid',true);
        if(false===$this->_mod->admin_delete_company($u_id) || false===D('Members')->delete_member($u_id)) $this->error('删除企业资料失败！');
        $type['_user'] = 1;
        $type['_company'] = 1;
        D('Jobs')->admin_delete_jobs_for_uid($u_id);
        $type['_jbos'] = 1;
        D('Resume')->admin_del_resume_for_uid($u_id);
        $type['_resume'] = 1;
        if(C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')){
            require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
            $name = 'sitegroup_passport';
            $passport = new $name();
            false === $passport->delete($sitegroup_uids,$type) && $this->error($passport->get_error());
        }
        $this->success('删除成功！');
    }
    /**
     * 认证企业
     */
    public function com_audit(){
        $uid = I('request.y_id');
        if(!$uid){
            $this->error('请选择企业');
        }
        $audit = I('post.audit',0,'intval');
        $pms_notice = I('post.pms_notice',0,'intval');
        $reason = I('post.reason','','trim');
        $result = $this->_mod->admin_edit_company_audit($uid,$audit,$reason,C('visitor'));
        if($result){
            $this->success("设置成功！");
        }else{
            $this->error('设置失败！');
        }
    }
    /**
     * 刷新企业
     */
    public function refresh_company(){
        $u_id = I('request.y_id');
        if(!$u_id){
            $this->error('你没有选择企业！');
        }
        if (!I('post.refresh_jobs',0))
        {
            $refresjobs=false;
        }
        else
        {
            $refresjobs=true;
        }
        if($n=$this->_mod->admin_refresh_company($u_id,$refresjobs))
        {
            $this->success("刷新成功！响应行数 {$n} 行");
        }
        else
        {
            $this->error('刷新失败！');
        }
    }
    /**
     * 加载会员详情
     */
    public function ajax_get_user_info(){
        $id = I('get.id',0,'intval');
        $rst = D('Members')->admin_ajax_get_user_info($id);
        exit($rst['msg']);
    }
    /**
     * 查看会员中心
     */
    public function management(){
        $id = I('get.id',0,'intval');
        $u = D('Members')->get_user_one(array('uid'=>$id));
		//h 后台进入会员
		$u['utype']=1;
        if (!empty($u))
        {
            $user_visitor = new \Common\qscmslib\user_visitor;
            $user_visitor->logout();
            $user_visitor->assign_info($u);
            redirect(U('home/members/index'));
        }
    }
    /**
     * 编辑会员信息
     */
    public function edit_member(){
        $this->_name = 'Members';
        parent::edit();
    }
    public function _after_update($id,$data){
        if($this->_name == 'Members'){
            if(2 == I('request.status',0,'intval')){
                $jobs = M('Jobs')->where(array('uid'=>$data['uid']))->select();
                foreach ($jobs as $val){
                    $val['user_status'] = 2;
                    $job[] = $val;
                }
                if($r = M('JobsTmp')->addAll($job)){
                    M('Jobs')->where(array('uid'=>$data['uid']))->delete();
                    foreach ($job as $val){
                        D('Jobs')->check_jobs($val);
                    }
                }
                M('CompanyProfile')->where(array('uid'=>$data['uid']))->setfield('user_status',2);
            }elseif(1 == I('request.status',0,'intval')){
                $jids =  M('JobsTmp')->where(array('uid'=>$data['uid'],'user_status'=>2))->getfield('id',true);
                $count = M('JobsTmp')->where(array('uid'=>$data['uid'],'user_status'=>2))->setfield('user_status',1);
                $setmeal = D('MembersSetmeal')->get_user_setmeal($data['uid']);
                if($setmeal['jobs_meanwhile'] < $count){
                    $c = M('JobsTmp')->where(array('uid'=>$data['uid'],'display'=>1,'audit'=>array('neq',3)))->count('id');
                    if(0 < $a = $c - $setmeal['jobs_meanwhile']){
                        M('JobsTmp')->where(array('uid'=>$data['uid'],'display'=>1,'audit'=>array('neq',3)))->limit($a)->setfield('display',2);
                    }
                }
                foreach($jids as $val){
                    D('Jobs')->check_jobs('',$val);
                }
                M('CompanyProfile')->where(array('uid'=>$data['uid']))->setfield('user_status',1);
            }
            if(I('post.qq_openid',0,'intval') == 2){
                    M('MembersBind')->where(array('uid'=>$data['uid'],'type'=>'qq'))->delete();
            }
        }
    }
    public function _before_update($data){
        if($this->_name == 'Members'){
            if(C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')){
                require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
                $name = 'sitegroup_passport';
                $passport = new $name();
                $data['password'] && $data['password'] = I('post.password','','trim');
                false === $passport->edit($data['uid'],$data) && $this->error($passport->get_error());
            }
            if(isset($_POST['password'])){
                $model = D('Members');
                $member = $model->find(I('post.uid',0,'intval'));
                $data['password'] = $model->make_md5_pwd(I('post.password','','trim'),$member['pwd_hash']);
            }
        }
        return $data;
    }
    /**
     * 修改企业基本信息
     */
    public function edit_company(){
        $uid = I('request.uid',0,'intval');
        if(!$uid){
            $this->returnMsg(0,'你没有选择企业！');
        }
        $company_profile = D('CompanyProfile')->where(array('uid'=>$uid))->find();
        if(!IS_POST)
        {
            //对座机进行分隔
            $telarray = explode('-',$company_profile['landline_tel']);
            if(intval($telarray[0]) > 0)
            {
                $company_profile['landline_tel_first'] = $telarray[0];
            }
            if(intval($telarray[1]) > 0)
            {
                $company_profile['landline_tel_next'] = $telarray[1];
            }
            if(intval($telarray[2]) > 0)
            {
                $company_profile['landline_tel_last'] = $telarray[2];
            }
            /* 分类*/
            $category = D('Category')->get_category_cache();
            $this->assign('category',$category);

            $comtag = $company_profile['tag']?explode(",",$company_profile['tag']):array();
            $tagArr = array('id'=>array(),'cn'=>array());
            if(!empty($comtag)){
                foreach ($comtag as $key => $value) {
                    $arr = explode("|",$value);
                    $tagArr['id'][] = $arr[0];
                    $tagArr['cn'][] = $arr[1];
                }
            }
            $tagStr = array('id'=>'','cn'=>'');
            if(!empty($tagArr['id']) && !empty($tagArr['cn'])){
                $tagStr['id'] = implode(",",$tagArr['id']);
                $tagStr['cn'] = implode(",",$tagArr['cn']);
            }
            $company_user = D('Members')->get_user_one(array('uid'=>$uid));
            $qq_bind = D('MembersBind')->where(array('uid'=>$uid,'type'=>'qq'))->find();
            if($qq_bind){
                $company_user['qq_openid'] = 1;
            }
            $this->assign('company_user',$company_user);
            $this->assign('userpoints',D('MembersPoints')->get_user_points($uid));
            $this->assign('setmeal',D('MembersSetmeal')->get_user_setmeal($uid));
            $this->assign('givesetmeal',D('Setmeal')->where(array('display'=>1))->order('show_order desc,id')->getField('id,setmeal_name'));
            $this->assign('info',$company_profile);
            $this->assign('tagStr',$tagStr);
            $this->display();
        }
        else //保存企业信息
        {
            $setsqlarr['id']=I('post.id',0,'intval');
            $setsqlarr['uid']=I('post.uid',0,'intval');
            // 判断企业名称是否重复
            if (C('qscms_company_repeat')=="0")
            {
                $info = M('CompanyProfile')->where(array('companyname'=>$setsqlarr['companyname']))->getField('uid');
                if($info) $this->returnMsg(0,"{$setsqlarr['companyname']}已经存在，同公司信息不能重复注册");
            }

            $data = array('nature','trade','scale');
            foreach ($data as $val) {
                $setsqlarr[$val] = I('post.'.$val,0,'intval');
            }
            $setsqlarr['district'] = I('post.district',0,'intval');
            $city = get_city_info($setsqlarr['district']);
            $setsqlarr['district'] = $city['district'];
            $setsqlarr['district_cn'] = $city['district_cn_all'];
            // 分类缓存
            $category = D('Category')->get_category_cache();
            $setsqlarr['nature_cn']=$category['QS_company_type'][$setsqlarr['nature']];
            $setsqlarr['trade_cn']=$category['QS_trade'][$setsqlarr['trade']];
            $setsqlarr['scale_cn']=$category['QS_scale'][$setsqlarr['scale']];
            // 字符串字段
            $setsqlarr['companyname']=I('post.companyname','','trim,badword');
            $setsqlarr['short_name']=I('post.short_name','','trim,badword');
            $setsqlarr['registered']=I('post.registered','','trim,badword');
            $setsqlarr['currency']=I('post.currency','','trim,badword'); 
            $setsqlarr['address']=I('post.address','','trim,badword'); 
            $setsqlarr['contact']=I('post.contact','','trim,badword');
            $setsqlarr['telephone'] = C('visitor.mobile_audit') ? C('visitor.mobile') : I('post.telephone','','trim,badword');
            $setsqlarr['email'] = C('visitor.email_audit') ? C('visitor.email') : I('post.email','','trim,badword');
            $setsqlarr['website']=I('post.website','','trim,badword');
            $setsqlarr['short_desc']=I('post.short_desc','','trim,badword');
            $setsqlarr['contents']=I('post.contents','','trim,badword');
            $setsqlarr['contact_show']=I('post.contact_show',0,'intval');
            $setsqlarr['telephone_show']=I('post.telephone_show',0,'intval');
            $setsqlarr['landline_tel_show']=I('post.landline_tel_show',0,'intval');
            $setsqlarr['email_show']=I('post.email_show',0,'intval');
            $setsqlarr['contact_show'] = $setsqlarr['contact_show']?1:0;
            $setsqlarr['email_show'] = $setsqlarr['email_show']?1:0;
            $setsqlarr['telephone_show'] = $setsqlarr['telephone_show']?1:0;
            $setsqlarr['landline_tel_show'] = $setsqlarr['landline_tel_show']?1:0;
            $setsqlarr['qq']=I('post.qq',0,'intval');
            $setsqlarr['audit']=I('post.audit',0,'intval');
            
            //座机
            $landline_tel_first=I('post.landline_tel_first',0,'trim,badword');
            $landline_tel_next=I('post.landline_tel_next',0,'trim,badword');
            $landline_tel_last=I('post.landline_tel_last',0,'trim,badword');
            $setsqlarr['landline_tel']=$landline_tel_first.'-'.$landline_tel_next.($landline_tel_last?('-'.$landline_tel_last):'');
            $setsqlarr['landline_tel'] = ltrim($setsqlarr['landline_tel'],'-');
            if($setsqlarr['telephone']=='' && $setsqlarr['landline_tel']==''){
                $this->returnMsg(0,'固话或手机号必填一项！');
            }
            $posttag = I('post.tag','','trim,badword');

            if($posttag){
                $tagArr = explode(",",$posttag);
                $r_arr = array();
                foreach ($tagArr as $key => $value) {
                    $r_arr[] = $value.'|'.$category['QS_jobtag'][$value];
                }
                if(!empty($r_arr)){
                    $setsqlarr['tag'] = implode(",",$r_arr);
                }else{
                    $setsqlarr['tag'] = '';
                }
            }
            // 插入数据
            $userinfo = D('Members')->get_user_one(array('uid'=>$company_profile['uid']));
            $rst = D('CompanyProfile')->admin_edit_company_profile($setsqlarr,$userinfo,$company_profile);
            $rst['state']==0 && $this->returnMsg(0,$rst['error']);
            $this->returnMsg(1,'保存成功！');
        }
    }
    public function _statistics($where,$mark=false){
        $model = D('CompanyStatistics');
        $today = strtotime(date('Y-m-d'));
        $where['addtime'] = array('lt',$today);
        $settr = I('get.settr',7,'intval');
        if($settr>0){
            $settr_tmp = $today-$settr*3600*24;
            $where['addtime'] = array(array('egt',$settr_tmp),array('lt',$today));
        }
        $source = I('get.source',0,'intval');
        if($source>0){
            $where['source'] = array('eq',$source);
        }
        $jobid = I('get.jobid',0,'intval');
        if($jobid>0){
            $where['jobid'] = array('eq',$jobid);
        }
        $category = array();
        $set_total = $set_login = array();
        for ($i=$settr_tmp; $i < $today; $i=$i+3600*24) { 
            $category[] = date('Y-m-d',$i);
            $set_total[$i] = 0;
            $set_login[$i] = 0;
        }
        $uidArr = array();
        $count_num = array('total'=>0,'login'=>0);

        $cache_name = ($mark?($mark.'_'):'').$where['comid'].'_'.$where['apply'].'_'.$settr.'_'.$source.'_'.$jobid.'_line_data.cache';

        $cache = check_cache($cache_name,$where['comid'].'/');
        if($cache === false){
            $list = $model->where($where)->select();
            write_cache($cache_name,$where['comid'].'/',json_encode($list));
        }else{
            $list = json_decode($cache,true);
        }
        
        $view_time = array();
        foreach ($list as $key => $value) {
            if($value['uid']>0){
                $view_time['time'][$value['uid']] = $value['viewtime'];
                $view_time['source'][$value['uid']] = $value['source'];
                $set_login[$value['addtime']]++;
                $uidArr[] = $value['uid'];
            }
            $set_total[$value['addtime']]++;
            $count_num['total']++;
        }
        $this->assign('view_time',$view_time);
        $line_xml = '<chart palettecolors="#0075c2,#1aaf5d" bgcolor="#ffffff" showborder="0" showshadow="0" showcanvasborder="0" useplotgradientcolor="0" legendborderalpha="0" legendshadow="0" showaxislines="0" showalternatehgridcolor="0" divlinethickness="1" divlinedashed="1" divlinedashlen="1" showvalues="0">';
        $line_xml.= '<categories>';
        foreach ($category as $key => $value) {
            $line_xml.= '<category label="'.$value.'" />';
        }
        $line_xml.= '</categories>';
        if($where['apply']==1){
            $line_xml.= '<dataset seriesname="用户应聘次数">';
            foreach ($set_login as $key => $value) {
                $line_xml.= '<set value="'.$value.'" />';
            }
            $line_xml.= '</dataset>';
        }else{
            $line_xml.= '<dataset seriesname="总浏览次数">';
            foreach ($set_total as $key => $value) {
                $line_xml.= '<set value="'.$value.'" />';
            }
            $line_xml.= '</dataset>';
            $line_xml.= '<dataset seriesname="登录用户浏览次数">';
            foreach ($set_login as $key => $value) {
                $line_xml.= '<set value="'.$value.'" />';
            }
            $line_xml.= '</dataset>';
        }
        $line_xml.= '</chart>';
        $this->assign('line_xml',$line_xml);

        if(!empty($uidArr)){
            $cache_name = ($mark?($mark.'_'):'').$where['comid'].'_'.$where['apply'].'_'.$settr.'_'.$source.'_'.$jobid.'_resume_data.cache';
            $cache = check_cache($cache_name,$where['comid'].'/');
            if($cache === false){
                $resumelist = D('Resume')->where(array('uid'=>array('in',$uidArr),'def'=>array('eq',1)))->select();
                write_cache($cache_name,$where['comid'].'/',json_encode($resumelist));
            }else{
                $resumelist = json_decode($cache,true);
            }
        }else{
            $resumelist = array();
        }
        $sex_total = array();
        $education_total = array();
        $experience_total = array();
        $age_total = array();
        $table_data = array();
        $data_count = count($resumelist);
        $pagesize = 10;
        $pager = pager($data_count, $pagesize);
        $page = $pager->fshow();
        $this->assign("page", $page);
		if(!I('get.page')){
			$row=$pager->listRows;
		}else{
			$pg=I('get.page');
			$row=$pg*$pager->listRows;
		}
        foreach ($resumelist as $key => $value) {
            if ($value['display_name']=="2")
            {
            $resumelist[$key]['fullname']="N".str_pad($value['id'],7,"0",STR_PAD_LEFT);
            }
            elseif($value['display_name']=="3")
            {
                if($value['sex']==1){
                    $resumelist[$key]['fullname']=cut_str($value['fullname'],1,0,"先生");
                }elseif($value['sex'] == 2){
                    $resumelist[$key]['fullname']=cut_str($value['fullname'],1,0,"女士");
                }else{
                    $resumelist[$key]['fullname']=cut_str($value['fullname'],1,0,"**");
                }
            }
            else
            {
                $resumelist[$key]['fullname']=$value['fullname'];
            }
            $resumelist[$key]['intention_jobs']=cut_str($value['intention_jobs'],10,0,'..');
            $resumelist[$key]['age']=date('Y')-$value['birthdate'];
			if($key>$pager->firstRow && $key<=$row){
				$table_data[] = $resumelist[$key];
				
			}
            if(!IS_AJAX){
                $count_num['login']++;
                if($value['sex']>0){
                    if(isset($sex_total[$value['sex']])){
                        $sex_total[$value['sex']]++;
                    }else{
                        $sex_total[$value['sex']] = 1;
                    }
                }
                if($value['experience']>0){
                    $experience_total['total'] += 1;
                    if(isset($experience_total['data'][$value['experience']])){
                        $experience_total['data'][$value['experience']]['num']++;
                    }else{
                        $experience_total['data'][$value['experience']]['label'] = $value['experience_cn'];
                        $experience_total['data'][$value['experience']]['num'] = 1;
                    }
                }
                if($value['education']>0){
                    $education_total['total'] += 1;
                    if(isset($education_total['data'][$value['education']])){
                        $education_total['data'][$value['education']]['num']++;
                    }else{
                        $education_total['data'][$value['education']]['label'] = $value['education_cn'];
                        $education_total['data'][$value['education']]['num'] = 1;
                    }
                }
                if($value['birthdate']>0){
                    $age_total['total'] += 1;
                    $minus_age = date('Y') - $value['birthdate'];
                    if($minus_age<26){
                        $age_total['data'][0]['label'] = '18-25岁';
                        $age_total['data'][0]['num'] += 1;
                    }else if($minus_age<31){
                        $age_total['data'][1]['label'] = '26-30岁';
                        $age_total['data'][1]['num'] += 1;
                    }else if($minus_age<41){
                        $age_total['data'][2]['label'] = '31-40岁';
                        $age_total['data'][2]['num'] += 1;
                    }else if($minus_age<51){
                        $age_total['data'][3]['label'] = '41-50岁';
                        $age_total['data'][3]['num'] += 1;
                    }else{
                        $age_total['data'][4]['label'] = '50岁';
                        $age_total['data'][4]['num'] += 1;
                    }
                }
            }
        }
        $this->assign("table_data", $table_data);
        if(IS_AJAX){
            $html = $this->fetch('Company/ajax_tpl/statistics_list');
            $this->ajaxReturn(1,'返回成功！',$html);
        }
        $sex_xml = '<chart showborder="0" enablesmartlabels="0" showlabels="0" showpercentvalues="1" showlegend="1" defaultcenterlabel="性别 （'.($sex_total[1]+$sex_total[2]).'人）" centerlabel="$label: $value人" centerlabelbold="0" showtooltip="0" decimals="0" usedataplotcolorforlabels="1" theme="fint">';
        if(!empty($sex_total)){
            $sex_xml.= '<set label="男" value="'.$sex_total[1].'" />';
            $sex_xml.= '<set label="女" value="'.$sex_total[2].'" />';
        }
        $sex_xml.= '</chart>';
        $this->assign('sex_xml',$sex_xml);
        $experience_xml = '<chart showborder="0" enablesmartlabels="0" showlabels="0" showpercentvalues="1" showlegend="1" defaultcenterlabel="工作经验 （'.$experience_total['total'].'人）" centerlabel="$label: $value人" centerlabelbold="0" showtooltip="0" decimals="0" usedataplotcolorforlabels="1" theme="fint">';
        if(!empty($experience_total['data'])){
            foreach ($experience_total['data'] as $key => $value) {
                $experience_xml.= '<set label="'.$value['label'].'" value="'.$value['num'].'" />';
            }
        }
        $experience_xml.= '</chart>';
        $this->assign('experience_xml',$experience_xml);

        $education_xml = '<chart showborder="0" enablesmartlabels="0" showlabels="0" showpercentvalues="1" showlegend="1" defaultcenterlabel="学历 （'.$education_total['total'].'人）" centerlabel="$label: $value人" centerlabelbold="0" showtooltip="0" decimals="0" usedataplotcolorforlabels="1" theme="fint">';
        if(!empty($education_total['data'])){
            foreach ($education_total['data'] as $key => $value) {
                $education_xml.= '<set label="'.$value['label'].'" value="'.$value['num'].'" />';
            }
        }
        $education_xml.= '</chart>';
        $this->assign('education_xml',$education_xml);

        $age_xml = '<chart showborder="0" enablesmartlabels="0" showlabels="0" showpercentvalues="1" showlegend="1" defaultcenterlabel="年龄 （'.$age_total['total'].'人）" centerlabel="$label: $value人" centerlabelbold="0" showtooltip="0" decimals="0" usedataplotcolorforlabels="1" theme="fint">';
        if(!empty($age_total['data'])){
            foreach ($age_total['data'] as $key => $value) {
                $age_xml.= '<set label="'.$value['label'].'" value="'.$value['num'].'" />';
            }
        }
        $age_xml.= '</chart>';
        $this->assign('age_xml',$age_xml);
        $this->assign('source',$source);
        $this->assign('settr',$settr);
        $this->assign('jobid',$jobid);
        $setmeal=D('MembersSetmeal')->get_user_setmeal($uid);
        $upper_limit = 0;
        $jids = M('Jobs')->where(array('uid'=>$uid))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>$uid,'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$setmeal['jobs_meanwhile']) $upper_limit = 1;
        $this->assign('upper_limit',$upper_limit);
        $this->assign('source_arr',array('1'=>'PC端','2'=>'触屏端','3'=>'移动端'));
        $this->assign('count_num',$count_num);
    }
    /**
     * 招聘效果统计 - 访客统计
     */
    public function statistics_visitor(){
        $where['comid'] = I('get.id',0,'intval');
        $where['apply'] = 0;
        $this->_statistics($where);
        $this->assign('statistics_nav','statistics_visitor');
        $this->_name = 'CompanyProfile';
        parent::edit();
    }
    /**
     * 招聘效果统计 - 应聘统计
     */
    public function statistics_apply(){
        $uid = I('get.uid',0,'intval');
        $where['comid'] = I('get.id',0,'intval');
        $where['apply'] = 1;
        $this->_statistics($where);
        $jobs_namearr = array();
        $jobs=D('Jobs')->get_jobs_by_uid($uid);
        foreach ($jobs as $key => $value) {
            $jobs_namearr[$value['id']] = $value['jobs_name'];
        }
        $this->assign('jobs',$jobs);
        $this->assign('jobs_namearr',$jobs_namearr);
        $this->assign('jobid',I('get.jobid',0,'intval'));
        $this->assign('statistics_nav','statistics_visitor');
        $this->_name = 'CompanyProfile';
        parent::edit();
    }
    /**
     * 招聘效果统计 - 职位浏览统计
     */
    public function statistics_viewjobs(){
        $uid = I('get.uid',0,'intval');
        $where['comid'] = I('get.id',0,'intval');
        $where['apply'] = 0;
        $where['jobid'] = array('gt',0);
        $this->_statistics($where,'viewjob');
        $jobs_namearr = array();
        $jobs=D('Jobs')->get_jobs_by_uid($uid);
        foreach ($jobs as $key => $value) {
            $jobs_namearr[$value['id']] = $value['jobs_name'];
        }
        $this->assign('jobs',$jobs);
        $this->assign('jobs_namearr',$jobs_namearr);
        $this->assign('jobid',I('get.jobid',0,'intval'));
        $this->assign('statistics_nav','statistics_visitor');
        $this->_name = 'CompanyProfile';
        parent::edit();
    }
    /**
     * 手机招聘统计
     */
    public function mobile_recruit_statistics(){
        // $uid = I('get.uid',0,'intval');
        $model = D('CompanyPraise');
        $where['company_id'] = I('get.id',0,'intval');
        // $where['uid'] = $uid;
        $cache_name = 'u'.$where['company_id'].'_wzp_tabledata.cache';
        $cache = check_cache($cache_name,'wzp/',1);
        if($cache === false){
            $list = array(array());
            //昨日时间
            $yesterday = intval(strtotime(date("Y-m-d")))-86400;
            //本周时间
            $week = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
            $today_end = strtotime(date("Y-m-d"));
            //上周时间
            $last_week_day_begin = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"));
            $last_week_day_end = mktime(0, 0 , 0,date("m"),date("d")-date("w"),date("Y"));
            //本月时间
            $month_day = strtotime(date("Y-m")."-1");
            //上月时间
            $month_day_begin = strtotime(date("Y-").(date('m')-1)."-1");
            $month_day_end = strtotime(date("Y-m")."-1")-86400;
            //循环数据
            $data = $model->where($where)->select();
            foreach ($data as $key => $value) 
            {
                if($value['addtime']==$yesterday)
                {
                    $list['yesterday'][$value['click_type']] += 1;
                }
                if($value['addtime']>=$week && $value['addtime']<$today_end)
                {
                    $list['week'][$value['click_type']] += 1;
                }
                if($value['addtime']>=$last_week_day_begin && $value['addtime']<=$last_week_day_end)
                {
                    $list['last_week'][$value['click_type']] += 1;
                }
                if($value['addtime']>=$month_day  && $value['addtime']<$today_end )
                {
                    $list['month'][$value['click_type']] += 1;
                }
                if($value['addtime']>=$month_day_begin && $value['addtime']<=$month_day_end)
                {
                    $list['last_month'][$value['click_type']] += 1;
                }
                if($value['addtime']<$today_end)
                {
                    $list['total'][$value['click_type']] += 1;
                }
            }
            //独立ip数据单独统计
            $count_where['company_id'] = $where['company_id'];
            $count_where['addtime'] = array('eq',$yesterday);
            $list['yesterday'][4] = $model->where($count_where)->count('distinct ip');
            unset($count_where['addtime']);

            $count_where['addtime'] = array('eq',$week);
            $list['week'][4] = $model->where($count_where)->count('distinct ip');
            unset($count_where['addtime']);

            $count_where['addtime'] = array(array('egt',$last_week_day_begin),array('elt',$last_week_day_end),'and');
            $list['last_week'][4] = $model->where($count_where)->count('distinct ip');
            unset($count_where['addtime']);

            $count_where['addtime'] = array('eq',$month_day);
            $list['month'][4] = $model->where($count_where)->count('distinct ip');
            unset($count_where['addtime']);

            $count_where['addtime'] = array(array('egt',$month_day_begin),array('elt',$month_day_end),'and');
            $list['last_month'][4] = $model->where($count_where)->count('distinct ip');
            unset($count_where['addtime']);

            $list['total'][4] = $model->where($count_where)->count('distinct ip');

            write_cache($cache_name,'wzp/',json_encode($list));
        }else{
            $list = json_decode($cache,true);
        }

        /**
        * 图表统计start
        **/
        $filter = I('get.settr',7,'intval');
        $cache_name = 'u'.$uid.'_wzp_line_'.$filter.'.cache';
        $cache = check_cache($cache_name,'wzp/',1);
        if($cache){
            $line_data = json_decode($cache,1);
        }else{
            $where1['company_id'] = $this->company_profile['id'];
            $where1['addtime'] = array('gt',strtotime(date('Y-m-d',time()-$filter*86400)));
            $line_data = $model->where($where1)->order('addtime asc')->select();
            write_cache($cache_name,'wzp/',json_encode($line_data));
        }
        for ($i=$filter; $i >0 ; $i--) { 
            $t = strtotime(date('Y-m-d',time()-$i*86400));
            $labelArr[] = $t;
            $line[1][$t] = 0;
            $line[2][$t] = 0;
            $line[3][$t] = 0;
        }
        foreach ($line_data as $key => $value) {
            $line[$value['click_type']][$value['addtime']] += 1;
        }
        $item = 0;
        $line_xml = '<chart palettecolors="#0075c2,#1aaf5d" bgcolor="#ffffff" showborder="0" showshadow="0" showcanvasborder="0" useplotgradientcolor="0" legendborderalpha="0" legendshadow="0" showaxislines="0" showalternatehgridcolor="0" divlinethickness="1" divlinedashed="1" divlinedashlen="1" showvalues="0">';
        $line_xml.= '<categories>';
        foreach ($labelArr as $key => $value) {
            $line_xml.= '<category label="'.date('m-d',$value).'" />';
        }
        $line_xml.= '</categories>';
        $line_xml.= '<dataset seriesname="点击数">';
        foreach ($line[1] as $key => $value) {
            $line_xml.= '<set value="'.$value.'" />';
        }
        $line_xml.= '</dataset>';

        $line_xml.= '<dataset seriesname="点赞数">';
        foreach ($line[2] as $key => $value) {
            $line_xml.= '<set value="'.$value.'" />';
        }
        $line_xml.= '</dataset>';

        $line_xml.= '<dataset seriesname="分享数">';
        foreach ($line[3] as $key => $value) {
            $line_xml.= '<set value="'.$value.'" />';
        }
        $line_xml.= '</dataset>';
        $line_xml.= '</chart>';
        $this->assign('line_xml',$line_xml);
        /**
        * 图表统计end
        **/
        $this->assign('data',$list);
        $this->assign('settr',$filter);
        $this->assign('company_nav','jobs_list');
        $setmeal=D('MembersSetmeal')->get_user_setmeal($uid);
        $upper_limit = 0;
        $jids = M('Jobs')->where(array('uid'=>$uid))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>$uid,'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$setmeal['jobs_meanwhile']) $upper_limit = 1;
        $this->assign('upper_limit',$upper_limit);
        $this->_name = 'CompanyProfile';
        parent::edit();
    }
    
    public function _before_add(){
        if(IS_POST){
            $com_setarr['audit'] = 0;
            $com_setarr['companyname']=I('post.companyname','','trim,badword');
            // 判断企业名称是否重复
            if (C('qscms_company_repeat')=="0")
            {
                $info = M('CompanyProfile')->where(array('companyname'=>$com_setarr['companyname']))->getField('uid');
                if($info){
                    $this->returnMsg(0,"{$com_setarr['companyname']}已经存在，同公司信息不能重复注册");
                }
            }
            $data = array('nature','trade','scale');
            foreach ($data as $val) {
                $com_setarr[$val] = I('post.'.$val,0,'intval');
            }
            $city = get_city_info(I('post.district',0,'intval'));
            $com_setarr['district'] = $data['district'] = $city['district'];
            $com_setarr['district_cn'] = $data['district_cn'] = $city['district_cn_all'];
            // 分类缓存
            $category = D('Category')->get_category_cache();
            $com_setarr['nature_cn']=$category['QS_company_type'][$com_setarr['nature']];
            $com_setarr['trade_cn']=$category['QS_trade'][$com_setarr['trade']];
            $com_setarr['scale_cn']=$category['QS_scale'][$com_setarr['scale']];
            // 字符串字段
            $com_setarr['companyname']=I('post.companyname','','trim,badword');
            $com_setarr['short_name']=I('post.short_name','','trim,badword');
            $com_setarr['registered']=I('post.registered','','trim,badword');
            $com_setarr['currency']=I('post.currency','','trim,badword');
            $com_setarr['address']=I('post.address','','trim,badword'); 
            $com_setarr['contact']=I('post.contact','','trim,badword');
            $com_setarr['telephone']=I('post.telephone','','trim,badword');
            $com_setarr['email'] = C('visitor.email_audit') ? C('visitor.email') : I('post.email','','trim,badword');
            $com_setarr['website']=I('post.website','','trim,badword');
            $com_setarr['short_desc']=I('post.short_desc','','trim,badword');
            $com_setarr['contents']=I('post.contents','','trim,badword');
            $com_setarr['contact_show']=I('post.contact_show',0,'intval');
            $com_setarr['telephone_show']=I('post.telephone_show',0,'intval');
            $com_setarr['landline_tel_show']=I('post.landline_tel_show',0,'intval');
            $com_setarr['email_show']=I('post.email_show',0,'intval');
            $com_setarr['contact_show'] = $com_setarr['contact_show']?1:0;
            $com_setarr['email_show'] = $com_setarr['email_show']?1:0;
            $com_setarr['telephone_show'] = $com_setarr['telephone_show']?1:0;
            $com_setarr['landline_tel_show'] = $com_setarr['landline_tel_show']?1:0;
            $com_setarr['qq']=I('post.qq',0,'intval');
            $com_setarr['audit']=I('post.audit',0,'intval');
            $landline_tel_first=I('post.landline_tel_first',0,'trim,badword');
            $landline_tel_next=I('post.landline_tel_next',0,'trim,badword');
            $landline_tel_last=I('post.landline_tel_last',0,'trim,badword');
            $com_setarr['landline_tel']=$landline_tel_first.'-'.$landline_tel_next.($landline_tel_last?('-'.$landline_tel_last):'');
            $com_setarr['landline_tel'] = ltrim($com_setarr['landline_tel'],'-');
            if($com_setarr['telephone']=='' && $com_setarr['landline_tel']==''){
                $this->returnMsg(0,'固话或手机号必填一项！');
            }
            $posttag = I('post.tag','','trim,badword');
            if($posttag){
                $tagArr = explode(",",$posttag);
                $r_arr = array();
                foreach ($tagArr as $key => $value) {
                    $r_arr[] = $value.'|'.$category['QS_jobtag'][$value];
                }
                if(!empty($r_arr)){
                    $com_setarr['tag'] = implode(",",$r_arr);
                }else{
                    $com_setarr['tag'] = '';
                }
            }
            $company_mod = D('CompanyProfile');
            if(false === $datas = $company_mod->create($com_setarr)){
                $this->returnMsg(0,$company_mod->getError());
            }
        }
    }
    public function add(){
        $this->_name = 'Members';
        $this->assign('givesetmeal',D('Setmeal')->get_setmeal_cache());
        /* 分类*/
        $category = D('Category')->get_category_cache();
        $this->assign('category',$category);
        parent::add();
    }
    public function _before_insert($data){
        if(fieldRegex($data['username'],'number')){
            $this->returnMsg(0,'用户名不能是纯数字！');
        }
        if(C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')){
            require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
            $name = 'sitegroup_passport';
            $passport = new $name();
            $company_mod = D('CompanyProfile');
            $data['companyname'] = $company_mod->companyname;
            $data['contact'] = $company_mod->contact;
            $data['landline_tel'] = $company_mod->landline_tel;
            if(false === $data = $passport->register($data)){
                $this->returnMsg(0,$passport->get_error());
            }
        }
        $data['s_password'] = $data['password'];
        $data['password'] = D('Members')->make_md5_pwd($data['password'],$data['pwd_hash']);
        return $data;
    }
    public function _after_insert($id,$data){
        if(I('post.is_money',0,'intval') && I('post.log_amount')){
            $amount=round(I('post.log_amount'),2);
            $ismoney=2;
        }else{
            $amount='0.00';
            $ismoney=1;
        }
        $data['uid'] = $id;
        D('Members')->user_register($data);
        if(I('post.regpoints')=='y'){
            $regpoints_num = I('post.regpoints_num',0,'intval');
            D('MembersPoints')->report_deal($id,1,$regpoints_num);
        }
        $reg_service=I('post.reg_service',0,'intval');
        if ($reg_service>0)
        {
            //D('MembersSetmeal')->set_members_setmeal($id,$reg_service);//chm修改前
            D('MembersSetmeal')->set_members_setmeal($id,$reg_service,$amount);//chm修改后
        }
        $company_mod=D('CompanyProfile');
        $company_mod->uid=$id;
        $Setmeal=D('MembersSetmeal')->where(array('uid'=>$id))->find();
        $company_mod->setmeal_name=$Setmeal['setmeal_name'];
        $company_mod->setmeal_id=$Setmeal['setmeal_id'];
        $insert_company_id = $company_mod->add();
        if($insert_company_id){
            switch($com_setarr['audit']){
                case 1:
                    $audit_str = '认证通过';break;
                case 2:
                    $audit_str = '认证中';break;
                case 3:
                    $audit_str = '认证未通过';break;
                default:
                    $audit_str = '';break;
            }
            if($audit_str){
                $auditsqlarr['company_id']=$insert_company_id;
                $auditsqlarr['reason']='自动设置';
                $auditsqlarr['status']=$audit_str;
                $auditsqlarr['addtime']=time();
                $auditsqlarr['audit_man']='系统';
                M('AuditReason')->data($auditsqlarr)->add();
            }
        }
        if(!C('qscms_register_password_open')){
            $sendSms['tpl']='set_register_resume';
            $sendSms['data']=array('username'=>$data['username'].'','password'=>$data['s_password']);
            $sendSms['mobile']=$data['mobile'];
            D('Sms')->sendSms('captcha',$sendSms);
        }
        //D('Members')->where(array('uid'=>$id))->save($com_setarrs);
    }
    /**
     * 设置顾问
     */
    public function consultant_install_save(){
        //得到 顾问的id 
        $id = I('request.id',0,'intval');
        !$id && $this->error("选择顾问发生错误！");
        //得到要设置顾问的企业会员uid 
        $tuid = I('request.tuid');
        !$tuid && $this->error("你没有选择会员！");
        $tuid=explode(",", $tuid);
        foreach ($tuid as $uid) {
            D('Members')->where(array('uid'=>$uid))->setField('consultant',$id);
        }
        $this->success('设置成功！',U('index'));
    }
    public function user_log(){
        $this->_name = 'MembersLog';
        $this->assign('type_arr',D('MembersLog')->type_arr);
        $map['log_uid'] = $_GET['uid'];
        $log_type = I('get.log_type','','trim');
        $log_type && $map['log_type'] = $log_type;
        if($settr = I('request.settr',0,'intval')){
            $map['log_addtime'] = array('gt',strtotime("-".$settr." day"));
        }
        parent::_list(D('MembersLog'),$map);
        $this->display();
    }
}
?>