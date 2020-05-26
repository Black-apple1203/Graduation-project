<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class jobs_listTag {
    protected $range_select     = 0;   
    protected $mod;                                                             //索引表
    protected $params           = array();                                      //全部传值
    protected $where            = array();                                      //查询条件
    protected $join;
    protected $order;                                                           //排序
    protected $firstRow         = 0;                                            //分页开始位置
    protected $spage_size       = 10;                                           	//单页条数
    protected $default_order    = 'stime desc';      //默认排序字段
    protected $order_array      = array(//排序枚举列表
        'addtime'               =>    'addtime %s',
        'rtime'                 =>    'refreshtime %s',
        'stickrtime'            =>    'stime %s,id %s',
        'hot'                   =>    'click %s',
        'scale'                 =>    'scale %s,refreshtime %s',
        'wage'                  =>    'minwage %s,refreshtime %s',
        'score'                 =>    '`score` %s',
        'null'                  =>    ''
    );
    protected $enum             = array(
        '搜索类型'              =>  'search_type',
        '搜索内容'              =>  'search_cont',
        '保证金'                =>  'famous',
        '名企招聘'              =>  'setmeal',
        '显示数目'              =>  'spage_size',
        '开始位置'              =>  'firstRow',
        '职位名长度'            =>  'jobslen',
        '企业名长度'            =>  'companynamelen',
        '描述长度'              =>  'brieflylen',
        '填补字符'              =>  'dot',
        '职位分类'              =>  'jobcategory',
        '职位大类'              =>  'category',
        '职位小类'              =>  'subclass',
        '地区分类'              =>  'citycategory',
        '地区大类'              =>  'district',
        '地区中类'              =>  'sdistrict',
        '地区小类'              =>  'tdistrict',
        '标签'                  =>  'tag',
        '行业'                  =>  'trade',
        '学历'                  =>  'education',
        '工作经验'              =>  'experience',
        '工资'                  =>  'wage',
        '最低工资'              =>  'minwage',
        '最高工资'              =>  'maxwage',
        '性别'                  =>  'sex',
        '职位性质'              =>  'nature',
        '公司规模'              =>  'scale',
        '紧急招聘'              =>  'emergency',
        '推荐'                  =>  'recommend',
        '营业执照'              =>  'license',
        '过滤已投递'            =>  'deliver',
        '关键字'                =>  'key',
        '关键字类型'            =>  'keytype',
        '日期范围'              =>  'settr',
        '排序'                  =>  'displayorder',
        '分页显示'              =>  'page',
        '会员uid'               =>  'uid',
        '公司页面'              =>  'companyshow',
        '职位页面'              =>  'jobsshow',
        '列表页'                =>  'listpage',
        '合并'                  =>  'mode',
        '公司列表名'            =>  'comlistname',
        '公司职位页面'          =>  'companyjobs',
        '单个公司显示职位数'    =>  'companyjobs_row',
        '浏览过的职位'          =>  'view_jobs',
        '风格模板'              =>  'tpl_compnay',
        '去除id'                =>  'except_id',
        '经度'                  =>  'lng',
        '纬度'                  =>  'lat',
        '半径'                  =>  'wa',
        '搜索范围'              =>  'range',
        '左下经度'              =>  'ldLng',
        '左下纬度'              =>  'ldLat',
        '右上经度'              =>  'ruLng',
        '右上纬度'              =>  'ruLat',
        '联系方式'              =>  'show_contact',
        '关健字类型'            =>  'key_type',
        '检测登录'              =>  'check_login',
        '检测收藏'              =>  'check_collect',
        '检测点赞'              =>  'check_like',
		'分站'                  =>  'is_sub',
		'职位id'				=>  'jid'
    );
    public function __construct($options) {
        $arr = array("lng","lat","ldLng","ldLat","ruLng","ruLat");
        foreach ($options as $key => $val) {
            if(in_array($this->enum[$key],$arr)){
                $this->params[$this->enum[$key]] = floatval($val);
            }else{
                $this->params[$this->enum[$key]] = $val;
            }
        }
        $this->field = 'id';
        $map = array();
        foreach(array(1=>'citycategory',2=>'jobcategory',3=>'trade',4=>'nature',5=>'tag') as $k => $v) {//省市,职位,行业
            $name = '_where_'.$v;
            if(false !== $w = $this->$name(trim($this->params[$v])))  $map[] = $w;
        }
        if($education = intval($this->params['education'])){
            $category = D('Category')->get_category_cache();
            $w = '';
            foreach ($category['QS_education'] as $key => $val) {
                if($key <= $education) $w[] = 'edu'.$key;
            }
            if($w){
                $map[] = '+('.implode(' ',$w).')';
            }
        }
        if($experience = intval($this->params['experience'])){
            $map[] = '+(exp'.$experience.' exp0)';
        }
		if(C('subsite_info') && C('subsite_info.s_id')!=0){
			if($this->params['is_sub']!=1){
				$map[] = '+sub'.C('subsite_info.s_id');
			}	
		}
        if($sex = intval($this->params['sex'])){
            $map[] = '+(sex'.$sex.' sex0)';
        }
        $range = isset($this->params['range']) ? $this->params['range'] : '20';
        if($range && $this->params['lat'] && $this->params['lng']){
            $this->params['wa'] = intval($range)*1000;
            $this->range_select = 1;
            $this->field = "id,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$this->params['lat']."*PI()/180-map_y*PI()/180)/2),2)+COS(".$this->params['lat']."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$this->params['lng']."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS map_range";
            if($this->params['ldLng'] && $this->params['ldLng'] && $this->params['ldLng'] && $this->params['ldLng']){
                $this->where['map_x'] = array('between',array($this->params['ldLng'],$this->params['ruLng']));
                $this->where['map_y'] = array('between',array($this->params['ldLat'],$this->params['ruLat']));
            }else{
                !$this->params['wa'] && $this->params['wa'] = 5000;
                $squares = square_point($this->params['lng'],$this->params['lat'],$this->params['wa']/1000);
                $this->where['map_x'] = array('between',array($squares['lt']['lng'],$squares['rb']['lng']));
                $this->where['map_y'] = array('between',array($squares['rb']['lat'],$squares['lt']['lat']));
            }
        }
        if($this->params['wage']){
            if(false === $category = F('category_wage_list')) $category = D('Category')->category_wage();
            $wage = $category[$this->params['wage']];
            if(preg_match_all('(\d+)',$wage,$reg)){
                $reg = $reg[0];
                $reg[0] && $this->where['minwage'] = array('egt',intval($reg[0]));
                $reg[1] && $this->where['maxwage'] = array('elt',intval($reg[1]));
            }
        }else{
            $this->params['minwage'] && $this->where['minwage'] = array('egt',intval($this->params['minwage']));
            $this->params['maxwage'] && $this->where['maxwage'] = array('elt',intval($this->params['maxwage']));
        }
		
		//职位id
		if($this->params['jid']){
			$this->where['id'] = array("in",$this->params['jid']);
		}
		
        if(C('qscms_jobs_display')==1){
            $map[] = '+audit1';
        }
        if(intval($this->params['famous'])==1){
            $map[] = '+fam1';
        }
        switch($this->params['search_cont']){
            case 'setmeal':
                $setmeal = D('Setmeal')->get_setmeal_cache();
                $w = '';
                foreach ($setmeal as $key => $val) {
                    if($key > 1) $w[] = 'set'.$key;
                }
                if($w){
                    $map[] = '+('.implode(' ',$w).')';
                }
                break;
            case 'emergency':
                $map[] = '+eme1';
                break;
            case 'allowance':
                $map[] = '+all1';
                break;
        }
        $this->params['uid'] && $this->where['uid'] = array('eq',intval($this->params['uid']));
        $this->params['except_id'] && $this->where['id'] = array('neq',intval($this->params['except_id']));
        if(C('qscms_term_of_validity') > 0){
            $validity =C('qscms_term_of_validity')*24*3600;
            $time = time()-$validity;
            $this->where['refreshtime'] = array('gt',$time);
        }
        $this->has_apply = array();
        $this->has_favor = array();
        if(C('visitor.utype') == 2){
            $jids = M('PersonalJobsApply')->where(array('personal_uid'=>C('visitor.uid')))->getfield('jobs_id',true);
            if($this->params['deliver'] && $jids){
                $this->where['id'] = array('not in',$jids);
            }else{
                $this->has_apply = $jids;
            }
            $this->has_favor = M('PersonalFavorites')->where(array('personal_uid'=>C('visitor.uid')))->getfield('jobs_id',true);
        }
        if($settr = intval($this->params['settr'])) $this->where['addtime'] = array('gt',strtotime("-".$settr."day"));
        $array = array('lic'=>'license','eme'=>'emergency','scale'=>'scale');
        foreach ($array as $key=>$val) {
            if($d =  intval($this->params[$val])) $map[] = '+'.$key.$d;
        }
        if($this->range_select==1){
            $this->order = 'map_range asc,refreshtime desc';
        }
        elseif($sort = trim($this->params['displayorder']))
        {
            $sort = explode('.',$sort);
            if(!$sort[0]) $sort[0] = C('qscms_fulltext_orderby') && $this->params['key'] ? 'score' : 'stickrtime';
            if($sort[0] == 'score' && (!C('qscms_fulltext_orderby') || !$this->params['key'])){
                $sort[0] = 'stickrtime';
                $_GET['sort'] = '';
            }
            if(!$order = $this->order_array[$sort[0]]) $order = $this->default_order;
            if($sort[1]=='desc'){
                $sort[1]="desc";
            }elseif($sort[1]=="asc"){
                $sort[1]="asc";
            }else{
                $sort[1]="desc";
            }
			if($this->params['displayorder']=='hot'){
				$time=time();
				$before_time=time()-3600*24*7;
				$this->where['refreshtime']=array('between',array($before_time,$time));
			}
            $this->order = str_replace('%s',$sort[1],$order);
        }
        else
        {
            $this->order = $this->default_order;
        }
        $this->mod = 'jobs_search';
        if(!empty($this->params['key'])){
            $this->params['key'] = urldecode(urldecode($this->params['key']));
            $key = trim($this->params['key']);
            if(!$this->params['search_type']){
                if(C('qscms_jobsearch_key_first_choice') == 1){
                    $this->params['search_type'] = 'jobs';
                }elseif(C('qscms_jobsearch_type') != 0){
                    $this->params['search_type'] = 'full';
                }
            }
            switch($this->params['search_type']){
                case 'jobs':
                    $this->where['jobs_name'] = array('like','%'.$key.'%');
                    $this->mod = 'jobs_search';
                    break;
                case 'company':
                    $this->where['companyname'] = array('like','%'.$key.'%');
                    $this->mod = 'jobs_search';
                    break;
                case 'full':
                    $key = get_tags($key);
                    if('or' == $this->params['key_type']){
                        $map[] = '+('.implode(' ',$key).')';
                    }else{
                        foreach ($key as $k => $v) {
                            $key[$k] = '+'.$v;
                        }
                        $map = array_merge($map,$key);
                    }
                    if(C('qscms_match_type') && $sort[0] == 'score'){
                        $match_with = true;
                    }
                    if($sort[0] == 'score'){
                        $this->field = 'id,MATCH (`key`) AGAINST ("'.implode(' ',$key).'") as score';
                    }
                    $this->mod = 'jobs_search_key';
                    break;
                case 'jobs_commpany':
                    $this->where['jobs_name|companyname'] = array('like','%'.$key.'%');
                    $this->mod = 'jobs_search';
                    break;
                default:
                    $this->where['jobs_name'] = array('like','%'.$key.'%');
                    $this->mod = 'jobs_search';
                    break;
            }
            D('Hotword')->set_inc_batch($key);
            if(C('apply.Recommend')){
                $class = new \Recommend\Controller\IndexController();
                $class->set_jobs($this->params['key']);
            }
        }
        if($map) $this->where['key'] = $match_with ? array('match_with',$map) : array('match_mode',$map);
		
        isset($this->params['firstRow']) && $this->firstRow = intval($this->params['firstRow']);
        if(isset($this->params['spage_size'])){
            $this->spage_size = $this->params['spage_size'] == '-1' ? 0 : intval($this->params['spage_size']);
        }
        $this->params['dot'] = isset($this->params['dot']) ? trim($this->params['dot']) : '...';
        $this->params['listpage'] = isset($this->params['listpage']) ? $this->params['listpage'] : "QS_jobslist";
        $this->params['jobsshow'] = isset($this->params['jobsshow']) ? $this->params['jobsshow'] : 'QS_jobsshow';
        $this->params['companyshow'] = isset($this->params['companyshow']) ? $this->params['companyshow'] : 'QS_companyshow';
        $this->params['companyjobs'] = isset($this->params['companyjobs']) ? $this->params['companyjobs'] : 'QS_companyjobs';
    }
    public function run(){
        $db_pre = C('DB_PREFIX');
        $model = new \Think\Model;
        $list_limit = 0;
        $show_login_notice = 0;
        $hidden_all_result = 0;
        $pageSize = $this->spage_size;
        if($this->params['check_login']==1 && C('qscms_jobs_search_login')==1 && !C('visitor')){
            $p = I('request.page',1,'intval');
            $jobs_search_num = intval(C('qscms_jobs_search_num_login'));
            if($jobs_search_num > 0){
                if(0 < $count = $p * $this->spage_size - $jobs_search_num){
                    $pageSize = $this->spage_size > $count ? $this->spage_size - $count : 0;
                }
            }else{
                $pageSize = 0;
            }
            if($pageSize != $this->spage_size){
                $need_login_params = array('tag','wage','trade','scale','nature','education','experience','settr','jobcategory','citycategory','lng','lat','ldLng','ldLat','ruLng','ruLat','range');
                foreach ($this->params as $key => $value) {
                    if(in_array($key,$need_login_params) && $value){
                        unset($this->where);
                        $this->where['id'] = 0;
                        $hidden_all_result = 1;
                        break;
                    }
                }
                $show_login_notice = 1;
            }
        }
        if($this->params['page']){
            if($this->params['view_jobs']){
                $result['total'] = count(cookie('view_jobs_log'));
            }else{
                if($result['total'] = $model->Table($db_pre.$this->mod.' j')->where($this->where)->join($this->join)->count('id')){
                    if (C('qscms_jobs_list_max') > 0){
                        $result['total'] > intval(C('qscms_jobs_list_max')) && $result['total']=intval(C('qscms_jobs_list_max'));
                    }
                    $pager = pager($result['total'],$this->spage_size);
                    //$start = abs($pager->firstRow - 1) * $this->spage_size;
                    $range = $this->params['range'];
                    if($range !='' && $this->params['lat'] && $this->params['lng']){
                        $sql = $model->Table($db_pre.$this->mod)->where($this->where)->join($this->join)->select(false);
                        $field = "id,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((".$this->params['lat']."*PI()/180-map_y*PI()/180)/2),2)+COS(".$this->params['lat']."*PI()/180)*COS(map_y*PI()/180)*POW(SIN((".$this->params['lng']."*PI()/180-map_x*PI()/180)/2),2)))*1000) AS map_range";
                        $list_limit = $list_limit?$list_limit:($pager->firstRow . ',' .$pageSize);
                        $jobs_list = $model->query('SELECT '.$field.' FROM('.$sql.') as j ORDER BY map_range asc,refreshtime desc LIMIT '.$list_limit);
                    }else{
                        $list_limit = $list_limit?$list_limit:($pager->firstRow . ',' .$pageSize);
                        $jobs_list = $model->Table($db_pre.$this->mod.' j')->field($this->field)->where($this->where)->join($this->join)->order($this->order)->limit($list_limit)->select();
                        //echo $model->getlastsql();
                    }
                    $pager->path = $this->params['listpage'];
                    $pager->showname = $this->params['listpage'];
                    $result['page'] = $pager->fshow();
                    $result['page_params'] = $pager->get_page_params();
					
                }else{
                    $result['page'] = '';
                    $result['page_params']['isfull'] = true;
                }
            }
        }else{
            if(!$list_limit){
                $limit = $this->spage_size ? $this->firstRow . ',' .$pageSize : '';
            }else{
                $limit = $list_limit;
            }
            $jobs_list = $model->Table($db_pre.$this->mod.' j')->field($this->field)->where($this->where)->join($this->join)->order($this->order)->limit($limit)->select();
         
			$result['page'] = '';
            $result['total'] = 0;
        }
        if($this->params['view_jobs']){
            $jobs = cookie('view_jobs_log');
        }else{
            foreach ($jobs_list as $key => $val) {
                $val['id'] && $jobs[] = $val['id'];
            }
        }
        if($jobs){
            if($this->params['check_collect']==1 && C('visitor')){
                $collect_map['personal_uid'] = array('eq',C('visitor.uid'));
                $collect_map['jobs_id'] = array('in',$jobs);
                $collect_id_arr = D('PersonalFavorites')->where($collect_map)->getField('jobs_id',true);
            }else{
                $collect_id_arr = array();
            }
            if($this->params['check_like']==1 && C('visitor')){
                $like_map['uid'] = array('eq',C('visitor.uid'));
                $like_map['pid'] = array('in',$jobs);
                $like_map['ptype'] = array('eq',1);
                $like_id_arr = D('LikeRecord')->where($like_map)->getField('pid',true);
            }else{
                $like_id_arr = array();
            }
            if($this->params['show_contact']==1){
                $contact_info = D('JobsContact')->where(array('pid'=>array('in',$jobs)))->select();
                foreach ($contact_info as $k => $v) {
                    $contact_arr[$v['pid']] = $v['telephone']?$v['telephone']:trim($v['landline_tel'],'-');
                    if($v['telephone']){
                        $tel[$v['pid']] = $v['telephone'];
                    }else{
                        $tel[$v['pid']] = explode('-',$v['landline_tel']);
                        unset($tel[$v['pid']][2]);
                        $tel[$v['pid']] = implode('-', $tel[$v['pid']]);
                    }
                }
            }else{
                $contact_arr = array();
            }
            $jids = implode(',',$jobs);
            $jobs = M('Jobs')->field()->where(array('id'=>array('in',$jids)))->order('field(id,'.$jids.')')->limit($this->spage_size)->select();
           
			foreach ($jobs as $key => $val) {
                $cids[] = $val['company_id'];
            }
            $cids && $company_list = M('CompanyProfile')->where(array('id'=>array('in',$cids)))->limit(count($cids))->getfield('id,logo,address,short_name');
            foreach ($jobs as $key => $val) {
                $val['is_collect'] = in_array($val['id'], $collect_id_arr)?1:0;
                $val['is_like'] = in_array($val['id'], $like_id_arr)?1:0;
                $val['like_num_cn'] = $val['like_num']>99 ? '99+' : $val['like_num'];
                $val['jobs_name_'] = $val['jobs_name'];
                $val['refreshtime_cn'] = $this->daterange($val['addtime'],$val['refreshtime']);
                $this->params['jobslen'] && $val['jobs_name']=cut_str($val['jobs_name'],$this->params['jobslen'],0,$this->params['dot']);
                if ($this->params['brieflylen']>0){
                    $val['briefly']=cut_str(strip_tags($val['contents']),$this->params['brieflylen'],0,$this->params['dot']);
                }else{
                    $val['briefly']=strip_tags($val['contents']);
                }
                $city = explode('/',$val['district_cn']);
                $val['city'] = end($city);
                $val['amount']=$val['amount']=="0"?'若干':$val['amount'];
                $val['briefly_']=strip_tags($val['contents']);
                $val['companyname_']=$val['companyname'];
				
				if($company_list[$val['company_id']]['short_name']){
					$val['short_name'] = $company_list[$val['company_id']]['short_name'];
				}else{
					$val['short_name'] = $val['companyname'];
				}
				
				
                if($this->params['companynamelen']){
                    $val['short_name']=cut_str($val['short_name'],$this->params['companynamelen'],0,$this->params['dot']);
                    $val['companyname']=cut_str($val['companyname'],$this->params['companynamelen'],0,$this->params['dot']);
                }
                $val['jobs_url']=url_rewrite($this->params['jobsshow'],array('id'=>$val['id'],'style'=>$this->params['tpl_compnay']));
                $val['company_url']=url_rewrite($this->params['companyshow'],array('id'=>$val['company_id']));
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
                $age = explode('-',$val['age']);
                if(!$age[0] && !$age[1]){
                    $val['age_cn'] = '不限';
                }else{
                    $age[0] && $val['age_cn'] = $age[0].'岁以上';
                    $age[1] && $val['age_cn'] = $age[1].'岁以下';
                }
                if ($val['tag_cn']){
                    $val['tag_cn']=explode(',',$val['tag_cn']);
                }else{
                    $val['tag_cn']=array();
                }
                $val['logo'] = $company_list[$val['company_id']]['logo'];
				$val['address'] = $company_list[$val['company_id']]['address'];
                if ($val['logo'])
                {
                    $val['logo']=attach($val['logo'],'company_logo');
                }
                else
                {
                    $val['logo']=attach('no_logo.png','resource');
                }
                if($val['experience_cn']=='不限'){
                    $val['experience_cn'] = '经验不限';
                }
                if($val['education_cn']=='不限'){
                    $val['education_cn'] = '学历不限';
                }
                if(C('apply.Allowance') && $val['allowance_id']>0){
                    $val['allowance_info'] = D('Allowance/AllowanceInfo')->find($val['allowance_id']);
                    $val['allowance_info']['type_cn'] = D('Allowance/AllowanceInfo')->get_alias_cn($val['allowance_info']['type_alias']);
                }else{
                    $val['allowance_info'] = array();
                    $val['allowance_id'] = 0;
                }
                if($this->params['lat'] && $this->params['lng']){
                    $val['map_range'] = $this->_get_distance($this->params['lat'],$this->params['lng'],$val['map_y'],$val['map_x']);
                }
                // 企业实地报告
                if(C('apply.Report')){
                    $where['com_id'] = $val['company_id'];
                    $where['status'] = 1;
                    $report = M('CompanyReport')->where($where)->find();
                    $report && $val['com_report'] = 1;
                }
                //分享红包
                if(C('qscms_share_allowance_open') == 1 && $val['share_allowance'] == 1){
                    $share_allowance = M('ShareAllowance')->field('id,amount,task_views')->where(array('jobs_id'=>$val['id'],'pay_status'=>1))->order('id desc')->find();
                    if($share_allowance){
                        $val['share_allowance_info'] = $share_allowance;
                    }else{
                        $val['share_allowance'] = 0;
                        D('Jobs')->jobs_setfield(array('id'=>$val['id']),array('share_allowance'=>0));
                    }
                }else{
                    $val['share_allowance'] = 0;
                }
                //合并公司 显示模式
                if($this->params['mode']==1){
                    //统计单个公司符合条件职位数
                    $match_map['company_id'] = $val['company_id'];
                    if(C('qscms_jobs_display')==1){
                        $match_map['audit'] = 1;
                    }
                    $val['count'] = M('Jobs')->where($match_map)->count('id');
                    $val['count_url']= $val['company_url'];
                    $list[$val['company_id']][] = $val;
                }else{//职位列表 显示模式
                    $list[] = $val;
                }
            }
            $result['list'] = $list;
            $result['contact_arr'] = $contact_arr;
            $result['has_apply'] = $this->has_apply;
            $result['has_favor'] = $this->has_favor;
            if(C('apply.Allowance')){
				//分站信息调取
				if(C('subsite_info') && C('subsite_info.s_id')!=0){
					  $subsite_id = C('subsite_info.s_id');
				}
				//end
                $result['allowance_count'] = D('Jobs')->where(array('allowance_id'=>array('gt',0),'subsite_id'=>$subsite_id))->count();
            }
            $result['tel'] = $tel;
        }else{
            $result['list'] = '';
        }
        $result['show_login_notice'] = $show_login_notice;
        $result['hidden_all_result'] = $hidden_all_result;
        return $result;
    }
    /**
     * 计算两坐标点之间的距离
     * 返回友好的距离长度
     *
     * @param   $lat1     decimal   纬度
     * @param   $lng1     decimal   经度
     * @param   $lat2     decimal   纬度
     * @param   $lng2     decimal   经度
     *
     * @return  decimal   距离
     */
    protected function _get_distance($lat1, $lng1, $lat2, $lng2, $type = false){
        $PI = '3.1415926535898';
        $radLat1 = $lat1 * ($PI / 180);
        $radLat2 = $lat2 * ($PI / 180);
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * ($PI / 180)) - ($lng2 * ($PI / 180));
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s * 6378;
        if(!$type){
            $s = $s > 1 ? round($s,1).'km' : round($s*1000).'m';
        }else{
            $s = round($s,1);
        }
        return $s;
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
                if(APP_SPELL && !fieldRegex($data,'in')){
                    $result = D('CategoryJobs')->jobs_cate_cache();
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
                    if(false === $jobs_cate = F('jobs_search_cate')) $jobs_cate = D('CategoryJobs')->jobs_search_cache();
                    foreach ($arr as $key => $val) {
                        $s[] = str_replace('_','.',$jobs_cate[$val]);
                    }
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
    protected function _where_trade($data){
        if($data){
            if (strpos($data,',')){
                $arr = explode(',',$data);
                $arr=array_unique($arr);
                $arr = array_slice($arr,0,10);
                $arr = array_map('intval', $arr);
                $sqlin = implode(' trade',$arr);
                return '+(trade'.$sqlin.')';
            }else{
                return '+trade'.intval($data);
            }
        }
        return false;
    }
    protected function _where_nature($data){
        if($data){
            if (strpos($data,',')){
                $arr = explode(',',$data);
                $arr=array_unique($arr);
                $arr = array_slice($arr,0,10);
                $arr = array_map('intval', $arr);
                $sqlin = implode(' trade',$arr);
                return '+(nat'.$sqlin.')';
            }else{
                return '+nat'.intval($data);
            }
        }
        return false;
    }
    protected function _where_tag($data){
        if($data){
            if (strpos($data,',')){
                $arr = explode(',',$data);
                $arr=array_unique($arr);
                $arr = array_slice($arr,0,10);
                $sqlin = implode(' tag',$arr);
                return '+(tag'.$sqlin.')';
            }else{
                return '+tag'.intval($data);
            }
        }
        return false;
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
            return daterange(time(),$refreshtime,'Y-m-d');
        }
        return "<span id=\"r_time\" style=\"color:#FF3300\">".$return."</span>";
    }
}