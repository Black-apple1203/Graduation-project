<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class company_jobs_listTag {
    protected $mod;                                                             //索引表
    protected $params           = array();                                      //全部传值
    protected $where            = array();                                      //查询条件
    protected $order;                                                           //排序
    protected $join;
    protected $firstRow         = 0;                                            //分页开始位置
    protected $spage_size       = 10;                                           //单页条数
    protected $default_order    = 'refreshtime %s,id %s';                             //默认排序字段
    protected $order_array      = array(//排序枚举列表
        'rtime'                 =>    'refreshtime %s,id %s',
        'stickrtime'            =>    'stime %s,id %s',
        'hot'                   =>    'click %s',
        'scale'                 =>    'scale %s,refreshtime %s',
        'wage'                  =>    'minwage %s,refreshtime %s',
        'null'                  =>    ''
    );
    protected $enum             =   array(
        '显示数目'              =>  'spage_size',
        '开始位置'              =>  'firstRow',
        '企业名长度'            =>  'companynamelen',
        '填补字符'              =>  'dot',
        '地区分类'              =>  'citycategory',
        '地区大类'              =>  'district',
        '地区中类'              =>  'sdistrict',
        '地区小类'              =>  'tdistrict',
        '紧急招聘'              =>  'emergency',
        '日期范围'              =>  'settr',
        '推荐'                  =>  'recommend',
        '名企'                  =>  'setmeal',
		'套餐'                  =>  'meal',
        '职位名长度'            =>  'jobslen',
        '显示职位'              =>  'jobsrow',
        '职位页面'              =>  'jobsshow',
        '职位分类'              =>  'jobcategory',
        '行业'                  =>  'trade',
        '排序'                  =>  'displayorder',
        '分页显示'              =>  'page',
        '公司页面'              =>  'companyshow',
        '列表页'                =>  'listpage',
        '统计职位'              =>  'countjobs',
        '去除id'                =>  'except_uid',
        '职位数量'              =>  'jobs_num',
        '企业规模'              =>  'scale'
    );
    public function __construct($options) {
        foreach ($options as $key => $val) {
            $this->params[$this->enum[$key]] = $val;
        }
        $map = array();
        foreach(array(1=>'citycategory',2=>'jobcategory') as $v) {
            $name = '_where_'.$v;
            if(false !== $w = $this->$name(trim($this->params[$v])))  $map[] = $w;
        }
        if($settr = intval($this->params['settr'])) $this->where['refreshtime'] = array('gt',strtotime("-".$settr."day"));
        foreach (array('eme'=>'emergency','trade'=>'trade','scale'=>'scale') as $key => $val) {
            if($d =  intval($this->params[$val])) $map[] = '+'.$key.$d;
        }
        if($this->params['setmeal'] && !$this->params['meal']){
			$setmeal = D('Setmeal')->get_setmeal_cache();
			foreach ($setmeal as $key => $val) {
				if($key > 1) $w[] = 'set'.$key;
			}
			if($w){
				$map[] = '+('.implode(' ',$w).')';
			}
        } elseif(!$this->params['setmeal'] && $this->params['meal']) {
			$w = 'set'.$this->params['meal'];
			$map[] = '+'.$w;
		}
        if(C('qscms_jobs_display')==1){
            $map[] = '+audit1';
        }
		if(C('subsite_info') && C('subsite_info.s_id')!=0){
			 $map[] = '+sub'.C('subsite_info.s_id');
		}
        $sort = trim($this->params['displayorder']);
        $sort = explode('.',$sort);
        if(!$sort[0]) $sort[0] = 'rtime';
        if(!$order = $this->order_array[$sort[0]]) $order = $this->default_order;
        if($sort[1]=='desc'){
            $sort[1]="desc";
        }elseif($sort[1]=="asc"){
            $sort[1]="asc";
        }else{
            $sort[1]="desc";
        }
        $this->order = str_replace('%s',$sort[1],$order);
        if($map) $this->where['key'] = array('match_mode',$map);
        $this->params['except_uid'] && $this->where['uid'] = array('neq',intval($this->params['except_uid']));
        isset($this->params['firstRow']) && $this->firstRow = intval($this->params['firstRow']);
        isset($this->params['spage_size']) && $this->spage_size = intval($this->params['spage_size']);
        $this->params['dot'] = isset($this->params['dot']) ? trim($this->params['dot']) : '...';
        $this->params['companyshow'] = isset($this->params['companyshow']) ? $this->params['companyshow'] : "QS_companyshow";
        $this->params['jobsshow'] = isset($this->params['jobsshow']) ? $this->params['jobsshow'] : "QS_jobsshow";
        $this->params['listpage'] = isset($this->params['listpage']) ? $this->params['listpage'] : "QS_companyjobs";
    }
    public function run(){
        $model = M('JobsSearch');
        if($this->params['page']){
            if($result['total'] = $model->where($this->where)->order($this->order)->count('distinct uid')){
                $pager = pager($result['total'],$this->spage_size);
                $company_info = $model->where($this->where)->order($this->order)->limit($pager->firstRow . ',' .$this->spage_size)->group('uid')->getfield('distinct uid,count(id) as jobs_num');
                $pager->path = $this->params['listpage'];
                $pager->showname = $this->params['listpage'];
                $result['page'] = $pager->fshow();
                $result['page_params'] = $pager->get_page_params();
            }else{
                $result['page'] = '';
                $result['page_params']['isfull'] = true;
            }
        }else{
            $company_info = $model->where($this->where)->order($this->order)->limit($this->firstRow . ',' .$this->spage_size)->group('uid')->getfield('distinct uid,count(id) as jobs_num');
			
            $result['page'] = '';
            $result['total'] = 0;
        }
        if($company_info){
            $cids = array_keys($company_info);
            $field_famous = C('apply.Sincerity')?',famous':'';
            $company = M('CompanyProfile')->where(array('uid'=>array('in',$cids)))->order('refreshtime desc')->getfield('id,uid,audit,companyname,nature_cn,district_cn,scale_cn,trade_cn,addtime,refreshtime,logo,short_name'.$field_famous);
            $cids = array();
            foreach ($company as $key => $val) {
                $company[$key]['jobs_num'] = $company_info[$val['uid']];
                if(!$val['short_name']){
                    $company[$key]['short_name'] = $val['companyname'];
                }
                if($this->params['companynamelen']){
                    $company[$key]['companyname'] = cut_str($val['companyname'],$this->params['companynamelen'],0,$this->params['dot']);
                    $company[$key]['short_name'] = cut_str($company[$key]['short_name'],$this->params['companynamelen'],0,$this->params['dot']);
                }
                $company[$key]['company_url']=url_rewrite($this->params['companyshow'],array('id'=>$val['id']));
                $company[$key]['company_jobs_url']=url_rewrite('QS_companyjobs',array('id'=>$val['id']));
                $company[$key]['refreshtime_cn']=$this->daterange($val['addtime'],$val['refreshtime']);
                if ($val['logo'])
                {
                    $company[$key]['logo']=attach($val['logo'],'company_logo');
                }
                else
                {
                    $company[$key]['logo']=attach('no_logo.png','resource');
                }
                $cids[] = $val['id'];
				$company[$key]['jobs_count']=M('Jobs')->where(array('company_id'=>$val['id']))->count();
            }
            if($cids){
                $jobs_map['company_id'] = array('in',$cids);
                if(C('qscms_jobs_display')==1){
                    $jobs_map['audit'] = 1;
                }
				if(C('subsite_info') && C('subsite_info.s_id')!=0){
					 $jobs_map['subsite_id'] = C('subsite_info.s_id');
				}
                $jobs = M('Jobs')->where($jobs_map)->order($this->order)->select();
                foreach ($jobs as $key => $val) {
                    if($this->params['jobs_num'] && count($company[$val['company_id']]['jobs']) >= $this->params['jobs_num']) continue;
                    $val['jobs_name'] = $this->params['jobslen']?cut_str($val['jobs_name'],$this->params['jobslen'],0,$this->params['dot']):$val['jobs_name'];
                    $val['refreshtime_cn']=$this->daterange($val['addtime'],$val['refreshtime']);
                    $val['jobs_url'] = url_rewrite($this->params['jobsshow'],array('id'=>$val['id']));
                    if($val['experience_cn']=='不限'){
                    $val['experience_cn'] = '经验不限';
                    }
                    if($val['education_cn']=='不限'){
                        $val['education_cn'] = '学历不限';
                    }
                    $city = explode('/',$val['district_cn']);
                    $val['city'] = end($city);
                    if (!empty($val['highlight'])){
                        $val['jobs_name']="<span style=\"color:{$val['highlight']}\">{$val['jobs_name']}</span>";
                    }
                    if($val['negotiable']==0){
                        if(C('qscms_wage_unit') == 1){
                            $val['minwage'] = $val['minwage']%1000==0?(($val['minwage']/1000).'K'):(round($val['minwage']/1000,1).'K');
                            $val['maxwage'] = $val['maxwage']?($val['maxwage']%1000==0?(($val['maxwage']/1000).'K'):(round($val['maxwage']/1000,1).'K')):0;
                        }elseif(C('qscms_wage_unit') == 2){
                            if($val['minwage']>=10000){
                                if($val['minwage']%10000==0){
                                   $val['minwage'] = ($val['minwage']/10000).'万';
                                }else{
                                    $val['minwage'] = round($val['minwage']/10000,1);
                                    $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','万',$val['minwage']) : $val['minwage'].'万';
                                }
                            }else{
                                if($val['minwage']%1000==0){
                                    $val['minwage'] = ($val['minwage']/1000).'千';
                                }else{
                                    $val['minwage'] = round($val['minwage']/1000,1);
                                    $val['minwage'] = strpos($val['minwage'],'.') ? str_replace('.','千',$val['minwage']) : $val['minwage'].'千';
                                }
                            }
                            if($val['maxwage']>=10000){
                                if($val['maxwage']%10000==0){
                                   $val['maxwage'] = ($val['maxwage']/10000).'万';
                                }else{
                                    $val['maxwage'] = round($val['maxwage']/10000,1);
                                    $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','万',$val['maxwage']) : $val['maxwage'].'万';
                                }
                            }elseif($val['maxwage']){
                                if($val['maxwage']%1000==0){
                                   $val['maxwage'] = ($val['maxwage']/1000).'千';
                                }else{
                                    $val['maxwage'] = round($val['maxwage']/1000,1);
                                    $val['maxwage'] = strpos($val['maxwage'],'.') ? str_replace('.','千',$val['maxwage']) : $val['maxwage'].'千';
                                }
                            }else{
                                $val['maxwage'] = 0;
                            }
                        }
                        if($val['maxwage']==0){
                            $val['wage_cn'] = '面议';
                        }else{
                            if($val['minwage']==$val['maxwage']){
                                $val['wage_cn'] = $val['minwage'].'/月';
                            }else{
                                $val['wage_cn'] = $val['minwage'].'-'.$val['maxwage'].'/月';
                            }
                        }
                    }else{
                        $val['wage_cn'] = '面议';
                    }
                    $company[$val['company_id']]['setmeal_id'] = $val['setmeal_id'];
                    $company[$val['company_id']]['jobs'][] = $val;
                }
            }
        }
        $result['list'] = $company;
        return $result;
    }
    protected function _parse($k,$str){
        if($str){
            if(is_array($str)){
                $arr = array_slice($str,0,20);
            }else{
                $arr = explode(',',$str);
                $arr = array_slice($arr,0,10);
            }
            foreach($arr as $v) {
                $a = explode('.',$v);
                $t = range(0,5);
                foreach ($t as $key => $val) {
                    $t[$key] = isset($a[$key]) ? intval($a[$key]) : 0;
                }
                for($i = 5;$i>=0;$i--){
                    $d[] = $k.implode('_',$t);
                    unset($t[$i]);
                }
            }
            return $d ? '+('.implode(' ',array_unique($d)).')' : false;
        }
        return false;
    }
    protected function _where_citycategory($data){
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
                return $this->_parse('city',$s);
            }else{
                return $this->_parse('city',$data);
            }
        }else{
            $district = $sdistrict = $tdistrict = array();
            if (trim($this->params['district'])){
                if (fieldRegex($this->params['district'],'in')){
                    $arr=explode(",",$this->params['district']);
                    $district = array_slice($arr,0,20);
                }
            }
            if (trim($this->params['sdistrict'])){
                if (fieldRegex($this->params['sdistrict'],'in')){
                    $arr=explode(",",$this->params['sdistrict']);
                    $sdistrict = array_slice($arr,0,20);
                }
            }
            if (trim($this->params['tdistrict'])){
                if (fieldRegex($this->params['tdistrict'],'in')){
                    $arr=explode(",",$this->params['tdistrict']);
                    $tdistrict = array_slice($arr,0,20);
                }
            }
            if($d = array_merge($district,$sdistrict,$tdistrict)){
                $d=array_unique($d);
                if(false === $city_cate = F('city_search_cate')) $city_cate = D('CategoryDistrict')->city_search_cache();
                foreach ($d as $key => $val) {
                    $s[] = 'city'.$city_cate[$val];
                }
            }
            return $s ? '+('.implode(' ',array_unique($s)).')' : false;
        }
    }
    protected function _where_jobcategory($data){
        if($data){
            if (!strpos($data,".")){
                if(false === $result = F('jobs_cate_list')) $result = D('CategoryJobs')->jobs_cate_cache();
                if(!fieldRegex($data,'in')){
                    $arr=explode(",",$data);
                    foreach ($arr as $key => $val) {
                        $arr[$key] = $result['spell'][$val]['id'];
                    }
                }
                $arr=array_unique($arr);
                if(false === $jobs_cate = F('jobs_search_cate')) $jobs_cate = D('CategoryJobs')->jobs_search_cache();
                foreach ($arr as $key => $val) {
                    $s[] = str_replace('_','.',$jobs_cate[$val]);
                }
                return $this->_parse('jobs',$s);
            }else{
                return $this->_parse('jobs',$data);
            }
        }else{
            $topclass = $category = $subclass = array();
            if (trim($this->params['topclass'])){
                if (fieldRegex($this->params['topclass'],'in')){
                    $arr=explode(",",$this->params['topclass']);
                    $topclass = array_slice($arr,0,20);
                }
            }
            if (trim($this->params['category'])){
                if (fieldRegex($this->params['category'],'in')){
                    $arr=explode(",",$this->params['category']);
                    $category = array_slice($arr,0,20);
                }
            }
            if (trim($this->params['subclass'])){
                if (fieldRegex($this->params['subclass'],'in')){
                    $arr=explode(",",$this->params['subclass']);
                    $subclass = array_slice($arr,0,20);
                }
            }
            if($d = array_merge($topclass,$category,$subclass)){
                $d=array_unique($d);
                if(false === $jobs_cate = F('jobs_search_cate')) $jobs_cate = D('CategoryJobs')->jobs_search_cache();
                foreach ($d as $key => $val) {
                    $s[] = 'jobs'.$jobs_cate[$val];
                }
            }
            return $s ? '+('.implode(' ',array_unique($s)).')' : false;
        }
    }
    protected function daterange($addtime,$refreshtime){
        $time = $refreshtime - $addtime;
        $time1 = time() - $refreshtime;
        if($time < 0 || $time1 < 0){
            return '';
        }elseif($time1 < 120){
            if($time < 60){
                $return = '新发布';
            }else{
                $return = '刚刚';
            }
        }else{
            return daterange(time(),$refreshtime,'m-d');
        }
        return "<span id=\"r_time\" style=\"color:#FF3300\">".$return."</span>";
    }
}