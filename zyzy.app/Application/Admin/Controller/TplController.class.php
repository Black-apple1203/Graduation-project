<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class TplController extends ConfigbaseController {
    public $tpl_dir;
    public function _initialize() {
        parent::_initialize();
        $this->tpl_dir = APP_PATH.'/Home/View/';
    }
    protected function _get_dir(){
        $dirs = getsubdirs($this->tpl_dir);
        unset($dirs[array_search("tpl_company",$dirs)]);
        unset($dirs[array_search("tpl_resume",$dirs)]);
        return $dirs;
    }
    public function index(){
        $dirs = $this->_get_dir();
        $list=array();
        foreach ($dirs as $k=> $val)
        {
            $list[$k]['thumb_dir']=$this->tpl_dir.$val;
            $list[$k]['dir']=$val;
            $list[$k]['info']=$this->_get_templates_info($this->tpl_dir.$val."/Config/info.txt");
        }
        $this->assign('list',$list);
        $templates['thumb_dir']=$this->tpl_dir.C('qscms_template_dir');
        $templates['dir']=C('qscms_template_dir');
        $templates['info']=$this->_get_templates_info($this->tpl_dir.$templates['dir']."/Config/info.txt");
        $this->assign('templates',$templates);
        $this->display();
    }
    public function theme_tpl(){
        $this->_mod = D('Config');
        $this->_name = 'Config';
        if (IS_POST) {
            parent::_edit();
        } else {
            parent::edit();
        }
    }
    public function _get_templates_info($file){
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
    /**
     * 备份模板
     */
    public function backup(){
        $tpl = I('request.tpl_name','','trim');
        if (dirname($tpl)<>'.')
        {
        $this->error("操作失败！");
        }
        $filename = TPL_BACKUP_PATH . $tpl . '_' . date('Ymd') . '.zip';
        $zip = new \Common\qscmslib\phpzip;
        $done = $zip->zip($this->tpl_dir . $tpl . '/', $filename);
        if ($done)
        {        
            header("Location:".$filename."");
        }
        else
        {
            $this->error("操作失败！");
        }
    }
    /**
     * 更换模板
     */
    public function set(){
        $tpl_dir = I('request.tpl_dir','','trim');
        $dirs = $this->_get_dir();
        if(!in_array($tpl_dir,$dirs)) $this->error('模板不存在或已经删除！');
        $templates_info=$this->_get_templates_info($this->tpl_dir.$tpl_dir."/info.txt");
        D('Config')->where(array('name'=>'template_dir'))->setField('value',$tpl_dir);
        if(C('qscms_template_dir') != $tpl_dir) D('AdCategory')->ads_init($tpl_dir);
        $this->update_config(array('DEFAULT_THEME'=>$tpl_dir));
        $this->success('设置成功！');
    }
    /**
     * 企业模板
     */
    public function com_tpl(){
        $this->_tpl_list(1);
    }
    /**
     * 保存企业模板
     */
    public function com_tpl_save(){
        $this->_tpl_save(1);
    }
    /**
     * 更新模板
     */
    public function refresh_tpl(){
        $type=I('get.type',0,'intval');
        $tpl_dir=I('get.tpl_dir','','trim');
        $tab_dir=$this->_get_user_tpl_dir($type);
        $dirs = getsubdirs($this->tpl_dir.$tpl_dir);
        $map['tpl_dir'] = array();
        foreach ($dirs as $str)
        {
            if (!in_array($str,$tab_dir))
            {
                $info=$this->_get_templates_info($this->tpl_dir.$tpl_dir."/".$str."/info.txt");
                D('Tpl')->add(array('tpl_name'=>$info['name'],'tpl_dir'=>$str,'tpl_type'=>$type));
            }
            $map['tpl_dir'][]=array('neq',$str);
        }
        if (!empty($map['tpl_dir']))
        {
            $map['tpl_dir'][] = 'and';
            $map['tpl_type'] = array('eq',$type);
            D('Tpl')->where($map)->delete();
        }
        $this->success('刷新成功');
    }
    /**
     * 简历模板
     */
    public function resume_tpl(){
        $this->_tpl_list(2);
    }
    /**
     * 保存简历模板
     */
    public function resume_tpl_save(){
        $this->_tpl_save(2);
    }
    /**
     * 模板列表共用方法
     */
    protected function _tpl_list($tpl_type){
        $tpl_file_dir = $tpl_type==1?'tpl_company':'tpl_resume';
        $result = D('Tpl')->where(array('tpl_type'=>$tpl_type))->select();
        $list = array();
        foreach ($result as $key => $value) {
            $value['info']=$this->_get_templates_info($this->tpl_dir.$tpl_file_dir.'/'.$value['tpl_dir']."/info.txt");
            $value['thumb_dir'] = $this->tpl_dir.$tpl_file_dir.'/'.$value['tpl_dir'];
            $list[] =$value;
        }
        $this->assign('list',$list);
        $this->display();
    }
    /**
     * 获取数据库中的模板方法
     */
    protected function _get_user_tpl_dir($type)
    {
        $type=intval($type);
        $result = D('Tpl')->where(array('tpl_type'=>$type))->select();
        $row_arr = array();
        foreach ($result as $key => $value) {
            $row_arr[] =$value['tpl_dir'];
        }
        return $row_arr;
    }
    /**
     * 保存模板共用方法
     */
    protected function _tpl_save($tpl_type){
        if($tpl_type==1){
            $tpl_type_word=I('post.tpl_company','','trim');
            $name = 'tpl_company';
        }else{
            $tpl_type_word=I('post.tpl_personal','','trim');
            $name = 'tpl_personal';
        }
        
        $r = D('Config')->where(array('name'=>$name))->setField('value',$tpl_type_word);
        if($r===false){
            $this->error('更新站点设置失败！');
        }
        $tpl_id=I('post.tpl_id');
        $tpl_name=I('post.tpl_name');
        $tpl_display=I('post.tpl_display');
        $tpl_val=I('post.tpl_val');
        if (is_array($tpl_id) && count($tpl_id)>0)
        {
            for ($i =0; $i <count($tpl_id);$i++){
                $setsqlarr['tpl_name']=trim($tpl_name[$i]);
                $setsqlarr['tpl_display']=intval($tpl_display[$i]);
                $setsqlarr['tpl_val']=intval($tpl_val[$i]);
                $r = D('Tpl')->where(array('tpl_id'=>intval($tpl_id[$i])))->save($setsqlarr);
                if($r===false){
                    $this->error('保存失败！');
                }
            }
        }
        $this->success('保存成功！');
    }
}