<?php
/**
 * 普工招聘列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class gworker_jobs_listTag {
	protected $params = array();
	protected $map = array();
	protected $order;
	protected $limit;
    protected $model;
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'显示数目'			=>	'row',
    		'标题长度'			=>	'titlelen',
    		'开始位置'			=>	'start',
    		'填补字符'			=>	'dot',
            '薪资待遇'          =>  'wage',
            '地区分类'          =>  'citycategory',
            '关键字'            =>  'key',
            '分页显示'          =>  'paged',
    		'排序'				=>	'displayorder',
            '描述长度'          =>  'brieflylen',
            '去除id'            =>  'except_id',
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $model = D('Gworker/GworkerJobs');
        $this->map['display'] = $model::DISPLAY_SHOW;
        $this->model = $model;
        if(isset($this->params['wage']) && intval($this->params['wage'])>0){
            $this->map['wage'] = array('eq',intval($this->params['wage']));
        }
        $this->_where_district($this->params['citycategory']);
        if(isset($this->params['key']) && trim($this->params['key'])<>''){
            $this->map['jobs_name'] = array('like','%'.trim(urldecode($this->params['key'])).'%');
        }
		//分站信息调取
		if(C('subsite_info') && C('subsite_info.s_id')!=0){
			  $this->map['subsite_id'] = C('subsite_info.s_id');
		}
		//end
        $this->params['except_id'] && $this->map['id'] = array('neq',intval($this->params['except_id']));
        $this->order = 'refreshtime desc,id desc';
    	$this->limit = isset($this->params['row'])?intval($this->params['row']):10;
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
		$this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
		$this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
		$this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
    	if($this->params['paged']){
    		$count = $this->model->where($this->map);
	    	$total = $count->count();
	        $pager = pager($total, $this->limit);
            $pager->showname = 'QS_gworker';
	        $page = $pager->fshow();
	        $this->params['start']>0 && $pager->firstRow = $this->params['start'];
	        $this->limit = $pager->firstRow.','.$pager->listRows;
            $return['page_params'] = $pager->get_page_params();
    	}else{
            $this->limit = $this->params['start'].','.$this->limit;
    		$total = 0;
    		$page = '';
    	}
        if($list = $this->model->where($this->map)->limit($this->limit)->order('refreshtime desc,id desc')->select()){
            foreach($list as $key => $val){
                $ids[] = $val['id'];
            }
            C('visitor.uid') && $apply = D('Gworker/GworkerJobsApply')->where(array('uid'=>C('visitor.uid'),'pid'=>array('in', $ids)))->getfield('pid,id');
            foreach ($list as $key => $val) {
                $val['url']=url_rewrite('QS_gworker_show',array('id'=>$val['id']));
                if ($this->params['brieflylen']>0){
                    $val['briefly']=cut_str(strip_tags($val['intro']),$this->params['brieflylen'],0,$this->params['dot']);
                }else{
                    $val['briefly']=strip_tags($val['intro']);
                }
                $val['refreshtime_cn'] = daterange(time(),$val['refreshtime'],'Y-m-d',"#FF3300");
                $val['amount']=$val['amount']=="0"?'若干':$val['amount'];
                $val['like_num_cn'] = $val['like_num']>99 ? '99+' : $val['like_num'];
                $val['is_apply'] = $apply[$val['id']]?1:0;
                $list[$key] = $val;
            }
        }
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
    private function _where_district($data){
        if($data){
            if (!strpos($data,".")){
                if(APP_SPELL && !fieldRegex($data,'in')){
                    $result = D('CategoryDistrict')->city_cate_cache();
                    $arr=explode(",",$data);
                    foreach ($arr as $key => $val) {
                        $arr[$key] = $result['spell'][$val]['id'];
                    }
                }else{
                    if(fieldRegex($data,'in')){
                        $arr=explode(",",$data);
                    }
                }
                $arr=array_unique($arr);
                if($arr){
                    if(false === $city_cate = F('city_search_cate')) $city_cate = D('CategoryDistrict')->city_search_cache();
                    foreach ($arr as $key => $val) {
                        $s[] = str_replace('_','.',$city_cate[$val]);
                    }
                }
                $s = $s[0];
                $s_arr = explode(".", $s);
                $s_arr = array_filter($s_arr);
                $district_id = implode(".", $s_arr);
            }else{
                $district_id = $data;
            }
            if ($district_id) {
                // $district_info = get_city_info($district);
                // $district_id = $district_info['district'];
                $district_id_arr = explode(".", $district_id);
                $this->map['district' . count($district_id_arr)] = array('eq', $district_id);
            }
        }
    }
}