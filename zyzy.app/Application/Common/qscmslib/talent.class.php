<?php
/**
 * 才情
 * 用法：
   $talent_api = new \Common\qscmslib\talent;
   $talent_api->act='';
   $talent_api->data = array();
   $talent_api->send();
 */
namespace Common\qscmslib;
class talent {
    public $url;
    public $act;
    public $data;
    public function __construct(){
        $this->url = C('qscms_caiqing_site_domain').'/api/index';
    }
    public function send(){
        if(!$this->url){
            return false;
        }
        if(!$this->act){
            return false;
        }
        if(!$this->data){
            return false;
        }
        $post_data['act'] = $this->act;
        $post_data['data'] = $this->data;
        $result = https_request($this->url,http_build_query($post_data));
        $result_arr = json_decode($result,true);
        if($result_arr['status']==1){
            return $result_arr['data'];
        }else{
            return false;
        }
    }
}