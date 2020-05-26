<?php
/**
 * 招聘会详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class jobfair_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'招聘会id'			=>	'id',
            '标题长度'          =>  'titlelen',
            '填补字符'          =>  'dot',
            '参会企业页'        =>  'exhibitorspage'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
        $this->params['titlelen']=isset($this->params['titlelen'])?$this->params['titlelen']:20;
        $this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'...';
        $this->params['exhibitorspage']=isset($this->params['exhibitorspage'])?$this->params['exhibitorspage']:"QS_jobfairexhibitors";
    }
    public function run(){
        $val = M('Jobfair')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['title']=cut_str($val['title'],$this->params['titlelen'],0,$this->params['dot']);
        $val['url'] = url_rewrite("QS_jobfairshow",array('id'=>$val['id']));
        $val['url'] = C('qscms_site_domain') . $val['url'];
        $val['exhibitorsurl'] = url_rewrite($this->params['exhibitorspage'],array('id'=>$val['id']));  
        $val['introduction'] = htmlspecialchars_decode($val['introduction'],ENT_QUOTES);
        $time=time();
        // 1预定中 0结束预定
        if($val['holddate_start']>$time){
            $val['predetermined_ok'] = 1;
        }else{
            $val['predetermined_ok'] = 0;
        }
        $val['thumb'] = $val['thumb']?attach($val['thumb'],'jobfair'):attach($val['thumb'],'resource');
        $val['keywords']=$val['title'];
        $val['introduction']=htmlspecialchars_decode($val['introduction'],ENT_QUOTES);
        $val['description']=str_replace('&nbsp;','',$val['introduction']);
        $val['description']=cut_str(strip_tags($val['description']),60,0,"");
        //展位图
        if($val['predetermined_ok']==1){
            $val['position_img'] = M('JobfairPositionImg')->where(array('jobfair_id'=>$this->params['id']))->select();
            $val['area'] = M('JobfairArea')->where(array('jobfair_id'=>$this->params['id']))->order('area asc')->select();
            $position_arr = M('JobfairPosition')->where(array('jobfair_id'=>$this->params['id']))->order('area_id asc,orderid asc')->select();
            $position = array();
            foreach ($position_arr as $key => $value) {
                $position[$value['area_id']][] = $value;
            }
            $val['position'] = $position;
        }
        //精彩回顾
        if($val['holddate_end']<time()){
            $val['retrospect'] = M('JobfairRetrospect')->where(array('jobfair_id'=>$this->params['id']))->select();
        }
        $val['booth_count'] = M('JobfairExhibitors')->where(array('jobfair_id'=>$this->params['id'],'audit'=>1))->count();
        $val['position_count'] = M('JobfairPosition')->where(array('jobfair_id'=>$this->params['id']))->count();
        $phone = $val['phone']?explode(",", $val['phone']):array($val['phone']);
        $val['phone'] = $phone[0];
        return $val;
    }
}