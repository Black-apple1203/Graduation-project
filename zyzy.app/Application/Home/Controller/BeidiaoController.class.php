<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class BeidiaoController extends FrontendController{
    public function index() {
    	$status = C('qscms_beidiao_status');
    	if($status != 1){
    		$this->error('背景调查功能暂未开启');
    	}
    	$link = C('qscms_beidiao_link').'/index.html#/loginLoad';
    	$key = C('qscms_beidiao_key');
    	$map_company['uid'] = C('visitor.uid');
    	$companyinfo = M('CompanyProfile')->where($map_company)->find();
    	$company_id = $companyinfo['id'];
    	$company_name = $companyinfo['companyname'];
    	$url = $link.'?str='.$key.'&company_name='.$company_name.'&cpy_unique_code='.$company_id;
        redirect($url);
        //Header("Location:$url"); 
    }
    
}