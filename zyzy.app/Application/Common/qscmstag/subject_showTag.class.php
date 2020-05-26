<?php
/**
 * 资讯详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class subject_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'专题id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['is_display'] = array('eq',1);
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
    }
    public function run(){
        $val = M('Subject')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $tpl = D('Tpl')->where(array('tpl_id'=>$val['s_tpl_id']))->find();
        $val['tpl'] =$tpl['tpl_dir'];
        $val['content']=htmlspecialchars_decode($val['content'],ENT_QUOTES);
        if ($val['seo_keywords']=="")
        {
        $val['seo_keywords']=$val['title'];
        }
        if ($val['seo_description']=="")
        {
        $val['seo_description']=strip_tags($val['content']);
        $val['seo_description']=trim($val['seo_description']);
        $val['seo_description']=cut_str($val['seo_description'],60,0,"");
        }
        $val['img'] = $val['small_img']?attach($val['small_img'],'images'):attach('no_img_news.png','resource');
        $val['wx_img'] = $val['customer_img']?attach($val['customer_img'],'images'):attach(C('qscms_weixin_service_img'),'resource');
        $fields = 'id,title';
        $list = M('Subject')->where($where)->order('article_order desc, addtime desc')->field($fields)->select();
        $current = -1;
        foreach ($list as $k => $v){
            if ($v['id'] == $val['id']){
                $current = $k;
                break;
            }
        }
        $prev = $current>-1 && !empty($list[$current-1]) ? $list[$current-1] : 0;
        if(!$prev){
            $val['prev'] = 0;
        }else{
            $val['prev'] = 1;
            $val['prev_title'] = $prev['title'];
            $val['prev_url'] = url_rewrite("QS_career_show",array('id'=>$prev['id']));
        }
        $next = $current>-1 && !empty($list[$current+1]) ? $list[$current+1] : 0;
        if(!$next){
            $val['next'] = 0;
        }else{
            $val['next'] = 1;
            $val['next_title'] = $next['title'];
            $val['next_url'] = url_rewrite("QS_career_show",array('id'=>$next['id']));
        }
        //报名企业数量
        $val['com_count'] = M('SubjectCompany')->where(array('subject_id'=>$this->map['id'],'s_audit'=>1))->count();
        $val['per_count'] = M('SubjectPersonal')->where(array('subject_id'=>$this->map['id'],'s_audit'=>1))->count();
        //所属企业职位数量
        $com_uid = M('SubjectCompany')->where(array('subject_id'=>$this->map['id'],'s_audit'=>1))->field('company_uid')->select();
        $uid = array();
        $job_count = 0;
        if($com_uid){
            foreach ($com_uid as $k => $v) {
                $uid[] =$v['company_uid'];
            }
            $job_count = M('Jobs')->where(array('uid'=>array('in',$uid)))->count();
        }
        $val['job_count'] = $job_count;
        if(C('visitor')){
            if(C('visitor.utype') == 1){
                $company = M('CompanyProfile')->where(array('uid'=>C('visitor.uid')))->find();
                $val['name'] = $company['companyname'];
                if(!$company['logo']){
                   $val['photosrc']=attach('no_logo.png','resource');
                }else{
                   $val['photosrc']=attach($company['logo'],'company_logo'); 
                }
            }else{
                $resume = M('Resume')->where(array('uid'=>C('visitor.uid')))->find();
                $val['name'] = $resume['fullname'];
                if(!$resume['photo_img']){
                    $avatar_default = $resume['sex']==1?'no_photo_male.png':'no_photo_female.png';
                    $val['photosrc']=attach($avatar_default,'resource');
                }else{
                    $val['photosrc']=attach($resume['photo_img'],'avatar');
                }
            }
        }
        //统计浏览量
        $sub_arr['subject_id'] = $val['id'];
        $sub_arr['addtime'] =time();
        M('SubjectStatistics')->add($sub_arr);
        return $val;
    }
}