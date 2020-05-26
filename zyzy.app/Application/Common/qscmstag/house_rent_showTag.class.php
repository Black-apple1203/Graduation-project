<?php
/**
 * 出租详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class house_rent_showTag {
	protected $params = array();
	protected $map = array();
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'信息id'			=>	'id'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        $this->map['id'] = array('eq',intval($this->params['id']));
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"info";
    }
    public function run(){
        $model = D('House/HouseRent');
        $val = $model->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['url']=url_rewrite('QS_house_rent_show',array('id'=>$val['id']));
        $val['contents'] = htmlspecialchars_decode($val['contents'],ENT_QUOTES);
        $val['refreshtime_cn']=daterange(time(),$val['refreshtime'],'Y-m-d');
        $storetag = $val['tag'] ? explode(",", $val['tag']) : array();
        $tagArr = array();
        if (!empty($storetag)) {
            foreach ($storetag as $key => $value) {
                $arr = explode("|", $value);
                $tagArr[] = $arr[1];
            }
        }
        $val['tag'] = $tagArr;
        if (C('qscms_contact_img_house') == 2) {
            $val['show_mobile'] = '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($val['mobile'], C('PWDHASH'))),'','',true) . '"/>';
        } else {
            $val['show_mobile'] = $val['mobile'];
        }
        $val['img'] = M('HouseRentImg')->where(array('pid'=>$val['id'],'display'=>1,'audit'=>1))->select();
        foreach ($val['img'] as $key => $value) {
            $val['img'][$key]['img'] = attach($value['img'],'house_rent');
        }
        $model->where(array('id' => $val['id']))->setInc('click', 1);
        return $val;
    }
}