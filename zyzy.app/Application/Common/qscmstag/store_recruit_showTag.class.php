<?php
/**
 * 职位详情
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class store_recruit_showTag {
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
        $model = D('Store/StorerecruitJobs');
        $val = $model->where($this->map)->find();
        if(!$val){
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $val['amount']=$val['amount']=="0"?'若干':$val['amount'];
        $val['url']=url_rewrite('QS_storerecruit_show',array('id'=>$val['id']));
        $val['contents'] = htmlspecialchars_decode($val['contents'],ENT_QUOTES);
        $val['refreshtime_cn']=daterange(time(),$val['refreshtime'],'Y-m-d');
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
        
        if (C('qscms_contact_img_store') == 2) {
            $val['show_mobile'] = '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($val['mobile'], C('PWDHASH'))),'','',true) . '"/>';
        } else {
            $val['show_mobile'] = $val['mobile'];
        }
        $model->where(array('id' => $val['id']))->setInc('click', 1);
        return $val;
    }
}