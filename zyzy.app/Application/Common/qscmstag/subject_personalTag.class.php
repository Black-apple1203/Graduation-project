<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class subject_personalTag {
    protected $params = array();
    protected $map = array();
    public function __construct($options) {
        $array = array(
            '列表名'           =>  'listname',
            '显示数目'          =>  'row',
            '专题公司id'          =>  'id',
            '关键字'          =>  'key',
            '关键字类型'          =>  'keytype',
            '分页显示'              =>  'page'

        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->limit = isset($this->params['row'])?intval($this->params['row']):5;
        $this->limit>20 && $this->limit=20;
        $this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
    }
    public function run(){
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'subject_personal';
        $join = 'left join '.$db_pre."resume as m on ".$this_t.".resume_uid=m.uid";
        $this->map[$this_t.'.subject_id'] = array('eq',intval($this->params['id']));
        $this->map[$this_t.'.s_audit'] = 1;
        if(!empty($this->params['key'])){
            $this->params['key'] = urldecode(urldecode($this->params['key']));
            $key = trim($this->params['key']);
            $this->map['m.intention_jobs'] = array('like','%'.$key.'%');
        }
        if($this->params['page']){
            $total = M('SubjectPersonal')->join($join)->where($this->map)->order('m.refreshtime desc')->count();
            $pager = pager($total, $this->limit);
            $page = $pager->fshow();
            $this->limit = $pager->firstRow.','.$pager->listRows;
            $page_params = $pager->get_page_params();
        }else{
            $this->limit = $this->params['start'].','.$this->limit;
            $total = 0;
            $page = '';
            $page_params = array();
        }
        $field = $this_t.".*,m.uid,m.id as r_id,m.display,m.display_name,m.fullname,m.sex,m.intention_jobs,m.photo,m.photo_img,m.photo_display,m.refreshtime,m.birthdate,m.education_cn,m.sex_cn,m.experience_cn,m.wage_cn";        
        $resume = M('SubjectPersonal')->join($join)->where($this->map)->limit($this->limit)->field($field)->order('m.refreshtime desc')->select();
        foreach ($resume as $key => $val) {
            if ($val['display_name']== 2){
                $val['fullname']="N".str_pad($val['id'],7,"0",STR_PAD_LEFT);
                $val['fullname_']=$val['fullname'];
            }elseif($val['display_name']==3){ 
                if($val['sex']==1){
                    $val['fullname']=cut_str($val['fullname'],1,0,"先生");
                }elseif($val['sex'] == 2){
                    $val['fullname']=cut_str($val['fullname'],1,0,"女士");
                }else{
                    $val['fullname']=cut_str($val['fullname'],1,0,"**");
                }   
            }else{
                $val['fullname_']=$val['fullname'];
            }
            $jobs = explode(',', $val['intention_jobs']);
            $val['intention_jobs'] =$jobs;
            $val['resume_url']=url_rewrite('QS_resumeshow',array('id'=>$val['r_id']));
            $val['age']=date("Y")-$val['birthdate'];
            $default_avatar = $val['sex']==1?'no_photo_male.png':'no_photo_female.png';
            // 照片显示方式
            if ($val['photo']==1){
                if($val['photo_display']==1 && $val['photo_img']){
                    $val['photosrc']=attach($val['photo_img'],'avatar');
                }else{
                    $val['photosrc']=attach($default_avatar,'resource');
                }
            }else{
                $val['photosrc']=attach($default_avatar,'resource');
            }
            $list[] = $val;
            }
        $return['page'] = $page;  
        $return['list'] = $list;
        return $return;
    }
}