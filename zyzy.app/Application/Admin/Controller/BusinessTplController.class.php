<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BusinessTplController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CompanyProfile');
    }
    /**
     * 业务管理
     */
    public function index(){
        $this->_name = 'CompanyProfile';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'company_profile';
        $has_overtime = I('request.has_overtime','','trim');
        $sort = I('request.sortby','starttime','trim');
        $overtime = I('request.overtime',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
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
        $this->where = $where;
        $this->field = $this_t.'.*,m.username,m.mobile,m.email as memail';
        $this->order = 'id asc';
        $this->join = $join;
        $this->assign('count',parent::_pending('CompanyProfile',array('audit'=>2)));
        parent::index();
    }
    /**
     * 风格模板
     */
    public function edit(){
        $uid = I('get.uid',0,'intval');
        $tpl_dir = APP_PATH.'/Home/View/';
        $tpl_file_dir = 'tpl_company';
        $result = D('Tpl')->where(array('tpl_type'=>1))->select();
        $list = array();
        foreach ($result as $key => $value) {
            $value['info']=$this->_get_templates_info($tpl_dir.$tpl_file_dir.'/'.$value['tpl_dir']."/info.txt");
            $value['thumb_dir'] = $tpl_dir.$tpl_file_dir.'/'.$value['tpl_dir'];
            $list[] =$value;
        }
        //当前模板
        $company_profile = D('CompanyProfile')->where(array('uid'=>$uid))->find();
        $current_tpl = $company_profile['tpl']?$company_profile['tpl']:'default';
        $templates = $this->_get_templates_info($tpl_dir.$tpl_file_dir.'/'.$current_tpl."/info.txt");
        $templates['thumb_dir'] = $tpl_dir.$tpl_file_dir.'/'.$current_tpl;
        $this->assign('list',$list);
        $this->assign('templates',$templates);
        $this->assign('uid',$company_profile['uid']);
        $this->_name = 'CompanyProfile';
        parent::edit();
    }
    /**
     * 更换企业模板
     */
    public function set_tpl(){
        $tpl_dir = I('get.tpl_dir','','trim');
        $uid = I('get.uid',0,'intval');
        D('CompanyProfile')->where(array('uid'=>$uid))->setField('tpl',$tpl_dir);
        D('Jobs')->where(array('uid'=>$uid))->setField('tpl',$tpl_dir);
        D('JobsTmp')->where(array('uid'=>$uid))->setField('tpl',$tpl_dir);
        $this->success('保存成功！');
    }
    /**
     * 格式化列表
     */
    public function _custom_fun($list){
        $list = $this->_mod->admin_format_company_list($list);
        foreach ($list as $key => $value) {
            $list[$key]['templates'] = $this->_get_tpl($value);
        }
        return $list;
    }
    private function _get_tpl($company_profile){
        $tpl_dir = APP_PATH.'/Home/View/';
        $tpl_file_dir = 'tpl_company';
        $current_tpl = $company_profile['tpl']?$company_profile['tpl']:'default';
        $templates = $this->_get_templates_info($tpl_dir.$tpl_file_dir.'/'.$current_tpl."/info.txt");
        $templates['thumb_dir'] = $tpl_dir.$tpl_file_dir.'/'.$current_tpl;
        return $templates;
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