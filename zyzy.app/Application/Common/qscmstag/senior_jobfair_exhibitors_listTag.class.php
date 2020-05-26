<?php
/**
 * 参会企业
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class senior_jobfair_exhibitors_listTag {
	protected $params = array();
	protected $map = array();
	protected $order;
	protected $limit;
    protected $tablename;
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
            '招聘会id'          =>  'jobfair_id',
    		'显示数目'			=>	'row',
    		'公司名称长度'		=>	'titlelen',
    		'开始位置'			=>	'start',
    		'填补字符'			=>	'dot',
            '日期范围'          =>  'settr',
            '分页显示'          =>  'paged',
    		'排序'				=>	'displayorder',
    		'页面'				=>	'showname',
    		'列表页'			=>	'listpage'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->tablename = 'jobfair_jobfair_exhibitors';
    	$this->map[$this->tablename.'.audit'] = array('eq',1);
        if(isset($this->params['jobfair_id']) && intval($this->params['jobfair_id'])>0){
            $this->map[$this->tablename.'.jobfair_id'] = array('eq',intval($this->params['jobfair_id']));
        }
        if(isset($this->params['settr']) && intval($this->params['settr'])>0){
            $this->map[$this->tablename.'.eaddtime'] = array('gt',strtotime("-".intval($this->params['settr'])." day"));
        }
    	$displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('id','desc');
    	$this->order = $this->tablename.'.'.$displayorder[0].' '.$displayorder[1];
    	$this->limit = isset($this->params['row'])?intval($this->params['row']):10;
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
		$this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
		$this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
		$this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_companyshow';
		$this->params['listpage']=isset($this->params['listpage'])?$this->params['listpage']:'QS_seniorjobfairexhibitors';
		$this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
    	if($this->params['paged']){
    		$count = D('Seniorjobfair/JobfairExhibitors')->where($this->map);
	    	$total = $count->count();
	        $pager = pager($total, $this->limit);
            $pager->showname = $this->params['listpage'];
	        $page = $pager->fshow();
	        $this->params['start']>0 && $pager->firstRow = $this->params['start'];
	        $this->limit = $pager->firstRow.','.$pager->listRows;
    	}else{
            $this->limit = $this->params['start'].','.$this->limit;
    		$total = 0;
    		$page = '';
    	}
		$join = C('DB_PREFIX').'company_profile as c on '.$this->tablename.'.company_id=c.id';
        $result = D('Seniorjobfair/JobfairExhibitors')->join($join)->where($this->map)->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
        	$row = $value;
        	$row['companyname_']=$row['companyname'];
            $row['companyname']=cut_str($row['companyname'],$this->params['titlelen'],0,$this->params['dot']);
            if ($row['uid']>0)
            {
            $row['url'] =url_rewrite($this->params['showname'],array('id'=>$row['company_id']));
            }
            else
            {
            $row['url']="";
            }
            if ($row['tag_cn']){
                $row['tag_cn']=explode(',',$row['tag_cn']);
            }else{
                $row['tag_cn']=array();
            }
            $jobs_list_map['company_id']=array('eq',$row['company_id']);
            if(C('qscms_jobs_display')==1){
                $jobs_list_map['audit']=1;
            }
            $jobslist = D('Jobs')->where($jobs_list_map)->field('id,jobs_name,amount')->select();
            foreach ($jobslist as $k => $v) {
                $row['jobslist'][$k]['jobs_name']= $v['jobs_name'];
                $row['jobslist'][$k]['jobs_url']= url_rewrite('QS_jobsshow',array('id'=>$v['id']));
            }
			$list[] = $row;
        }
        /**
         * 推荐企业start
         */
        $return['recommend'] = array();
        $this->map[$this->tablename.'.recommend'] = 1;
        $this->map[$this->tablename.'.audit'] = 1;
        $result = D('Seniorjobfair/JobfairExhibitors')->join($join)->where($this->map)->order($this->order)->limit(7)->select();
        foreach ($result as $key => $value) {
            $row = $value;
            $row['company_logo'] = $value['logo']?attach($value['logo'],'company_logo'):attach('no_logo.png','resource');
            $row['company_name'] = cut_str($row['companyname'],6,0,'...');
            $row['company_url'] = url_rewrite($this->params['showname'],array('id'=>$row['company_id']));
            $return['recommend'][] = $row;
        }
        /**
         * 推荐企业end
         */
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
}