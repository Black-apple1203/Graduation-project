<?php
/**
 * 招聘会详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class senior_jobfair_showTag {
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
        $this->params['exhibitorspage']=isset($this->params['exhibitorspage'])?$this->params['exhibitorspage']:"QS_seniorjobfairexhibitors";
    }
    public function run(){
        $val = D('Seniorjobfair/Jobfair')->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['title']=cut_str($val['title'],$this->params['titlelen'],0,$this->params['dot']);
        $val['url'] = url_rewrite("QS_seniorjobfairshow",array('id'=>$val['id']));
        $val['url'] = C('qscms_site_domain') . $val['url'];
        $val['exhibitorsurl'] = url_rewrite($this->params['exhibitorspage'],array('id'=>$val['id'])); 
        $val['booth_url'] = url_rewrite('QS_seniorjobfair_booth',array('id'=>$val['id']));  
        $val['introduction'] = htmlspecialchars_decode($val['introduction'],ENT_QUOTES);
        $time=time();
        // 1预定中 0结束预定
        if($val['holddate_start']>$time){
            $val['predetermined_ok'] = 1;
        }else{
            $val['predetermined_ok'] = 0;
        }
        $val['thumb'] = $val['thumb']?att($val['thumb'],'jobfair'):att($val['thumb'],'resource');
        if(strstr($val['thumb'],"http://")===false){
            $val['thumb'] = C('qscms_senior_jobfair_site_domain').$val['thumb'];
        }
        if($val['intro_img']){
            $val['intro_img'] = att($val['intro_img'],'jobfair');
           if(strstr($val['intro_img'],"http://")===false){
                $val['intro_img'] = C('qscms_senior_jobfair_site_domain').$val['intro_img'];
            }
        }
        $val['keywords']=$val['title'];
        $val['introduction']=htmlspecialchars_decode($val['introduction'],ENT_QUOTES);
        $val['description']=str_replace('&nbsp;','',$val['introduction']);
        $val['description']=cut_str(strip_tags($val['description']),60,0,"");
        $val['map_x'] = $val['map_x']?:C('qscms_map_center_x');
        $val['map_y'] = $val['map_y']?:C('qscms_map_center_y');
        $val['map_zoom'] = $val['map_zoom']?:C('qscms_map_zoom');
        //展位图
        if($val['predetermined_ok']==1){
            $val['position_img'] = D('Seniorjobfair/JobfairPositionImg')->where(array('jobfair_id'=>$this->params['id']))->select();
            if($val['position_img']){
                foreach ($val['position_img'] as $_key => $_val) {
                    if($_val['img']){
                       $_val['img'] = att($_val['img'],'jobfair');
                       if(strstr($_val['img'],"http://")===false){
                            $val['position_img'][$_key]['img'] = C('qscms_senior_jobfair_site_domain').$_val['img'];
                        }
                    }
                }
            }
            /*$val['area'] = D('Seniorjobfair/JobfairArea')->where(array('jobfair_id'=>$this->params['id']))->order('area asc')->select();
            $position_arr = D('Seniorjobfair/JobfairPosition')->where(array('jobfair_id'=>$this->params['id']))->order('area_id asc,orderid asc')->select();
            $position = array();
            foreach ($position_arr as $key => $value) {
                $position[$value['area_id']][] = $value;
            }
            $val['position'] = $position;*/

            $where = array('jobfair_id'=>$this->params['id']);
            $val['floor'] = D('Seniorjobfair/JobfairFloor')->where($where)->getfield('id,floor');
            if($area = D('Seniorjobfair/JobfairArea')->where($where)->order('area asc')->select()){
                foreach($area as $_val){
                    $val['area'][$_val['floor_id']][$_val['id']] = $_val['area'];
                }
            }
            if($position = D('Seniorjobfair/JobfairPosition')->where($where)->order('floor_id asc,area_id asc,orderid asc')->select()){
                foreach($position as $_val){
                    $val['position'][$_val['floor_id']][$_val['area_id']][$_val['id']] = $_val;
                }
            }
        }
        //精彩回顾
        if($val['holddate_end']<time()){
            $val['retrospect'] = D('Seniorjobfair/JobfairRetrospect')->where(array('jobfair_id'=>$this->params['id']))->select();
            if($val['retrospect']){
                foreach ($val['retrospect'] as $_key => $_val) {
                    if($_val['img']){
                       $_val['img'] = att($_val['img'],'jobfair');
                       if(strstr($_val['img'],"http://")===false){
                            $val['retrospect'][$_key]['img'] = C('qscms_senior_jobfair_site_domain').$_val['img'];
                        }
                    }
                }
            }
        }
        $val['booth_count'] = D('Seniorjobfair/JobfairExhibitors')->where(array('jobfair_id'=>$this->params['id'],'audit'=>1))->count();
        $val['position_count'] = D('Seniorjobfair/JobfairPosition')->where(array('jobfair_id'=>$this->params['id']))->count();
        $phone = $val['phone']?explode(",", $val['phone']):array($val['phone']);
        $val['phone'] = $phone[0];
        return $val;
    }
}