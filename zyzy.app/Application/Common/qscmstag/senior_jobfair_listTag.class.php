<?php
/**
 * 招聘会列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class senior_jobfair_listTag {
	protected $params = array();
	protected $map = array();
	protected $order;
	protected $limit;
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'显示数目'			=>	'row',
    		'标题长度'			=>	'titlelen',
    		'开始位置'			=>	'start',
    		'填补字符'			=>	'dot',
            '日期范围'          =>  'settr',
            '分页显示'          =>  'paged',
            '参会企业页'        =>  'exhibitorspage',
    		'排序'				=>	'displayorder',
    		'页面'				=>	'showname',
    		'列表页'			=>	'listpage'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
    	$this->map['display'] = array('eq',1);
        if(isset($this->params['settr']) && intval($this->params['settr'])>0){
            $this->map['addtime'] = array('gt',strtotime("-".intval($this->params['settr'])." day"));
        }
    	$displayorder = isset($this->params['displayorder'])?explode(':',$this->params['displayorder']):array('ordid','desc');
    	$this->order = $displayorder[0].' '.$displayorder[1].',id desc';
    	$this->limit = isset($this->params['row'])?intval($this->params['row']):10;
    	$this->limit>20 && $this->limit=20;
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
		$this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
		$this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
		$this->params['showname']=isset($this->params['showname'])?$this->params['showname']:'QS_seniorjobfairshow';
		$this->params['listpage']=isset($this->params['listpage'])?$this->params['listpage']:'QS_seniorjobfairlist';
        $this->params['exhibitorspage']=isset($this->params['exhibitorspage'])?$this->params['exhibitorspage']:'QS_seniorjobfairexhibitors';
		$this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'...';
    }
    public function run(){
        $s_where = $e_where = $this->map;
        $s_result = $e_result = $list = array();
        $s_limit = $e_limit = '';
        $s_order = 'holddate_start asc,' . $this->order;
        $e_order = 'holddate_start desc,' . $this->order;
        $time = time();
        $s_where['holddate_start'] = array('gt',$time);
        $e_where['holddate_start'] = array('elt',$time);
        $s_count = D('Seniorjobfair/Jobfair')->where($s_where)->count();
        $e_count = D('Seniorjobfair/Jobfair')->where($e_where)->count();
        $total = $s_count + $e_count;
        if($this->params['paged']){
            $pager = pager($total, $this->limit);
            $pager->showname = $this->params['listpage'];
            $page = $pager->fshow();
            $p = I('get.'.$pager->varPage,1,'intval');
            $this->firstRow = abs($p - 1) * $this->limit;
        }else{
            $this->firstRow = $this->params['start'];
            $total = 0;
            $page = '';
        }
        if($this->firstRow > $s_count){
            $e_count && $e_limit = intval($this->firstRow) - intval($s_count) . ',' . $this->limit;
        }else{
            $s_count && $s_limit = $this->firstRow . ',' . $this->limit;
            if($e_count &&  0 != $e_limit = abs($this->firstRow + $this->limit - $s_count)){
                $e_limit = '0,' . $e_limit;
            }
        }
        $s_limit && $s_result = D('Seniorjobfair/Jobfair')->where($s_where)->order($s_order)->limit($s_limit)->select();
        $e_limit && $e_result = D('Seniorjobfair/Jobfair')->where($e_where)->order($e_order)->limit($e_limit)->select();
        $result = array_merge($s_result,$e_result);
        foreach ($result as $key => $value) {
        	$row = $value;
        	$row['title_']=$row['title'];
            $row['title']=cut_str($row['title'],$this->params['titlelen'],0,$this->params['dot']);
            $row['url'] = url_rewrite($this->params['showname'],array('id'=>$row['id']));
            $row['bus']=cut_str($row['bus'],20,0,"...");
            $row['exhibitorsurl'] = url_rewrite($this->params['exhibitorspage'],array('id'=>$row['id']));  
            $row['booth_url'] = url_rewrite('QS_seniorjobfair_booth',array('id'=>$row['id']));  
            // 1预定中 0结束预定
            if($row['holddate_start']>$time){
                $row['predetermined_ok'] = 1;
            }else{
                $row['predetermined_ok'] = 0;
            }

            $row['thumb'] = $row['thumb']?att($row['thumb'],'jobfair'):att($row['thumb'],'resource');
            if($row['thumb'] && strstr($row['thumb'],"http://")===false){
                $row['thumb'] = C('qscms_senior_jobfair_site_domain').$row['thumb'];
            }
            $row['com_num'] = D('Seniorjobfair/JobfairExhibitors')->where(array('jobfair_id'=>$row['id'],'audit'=>1))->count();
			$list[] = $row;
        }
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
}