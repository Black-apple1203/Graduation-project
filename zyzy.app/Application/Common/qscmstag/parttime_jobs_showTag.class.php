<?php
/**
 * 职位详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class parttime_jobs_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'职位id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $model = D('Parttime/ParttimeJobs');
        $val = $model->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['amount']=$val['amount']=="0"?'若干':$val['amount'];
        $val['url']=url_rewrite('QS_parttime_show',array('id'=>$val['id']));
        $val['contents'] = htmlspecialchars_decode($val['contents'],ENT_QUOTES);
        $val['refreshtime_cn']=daterange(time(),$val['refreshtime'],'Y-m-d');
        
        $val['worktime'] = $val['worktime']?unserialize($val['worktime']):array();
        
        if (C('qscms_contact_img_parttime') == 2) {
            $val['show_mobile'] = '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($val['mobile'], C('PWDHASH'))),'','',true) . '"/>';
        } else {
            $val['show_mobile'] = $val['mobile'];
        }
        $model->where(array('id' => $val['id']))->setInc('click', 1);
        return $val;
    }
}