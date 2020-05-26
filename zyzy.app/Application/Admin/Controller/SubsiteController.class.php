<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SubsiteController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
       $this->_name = 'Subsite';
    }
	public function _before_index(){
        $this->order = 's_order desc,s_id';
    }
	/**
     * [config 分站配置项]
     */
    public function config(){
    	 $this->_edit();
    }
	/**
     * [_before_insert 分站添加前项]
     * [_after_insert 分站添加后项]
     */
	public function _before_insert($data){
        $data['s_domain'] = str_replace('http://','',$data['s_domain']);
		$data['s_domain'] = str_replace('https://','',$data['s_domain']);
        $data['s_m_domain'] = str_replace('http://','',$data['s_m_domain']);
		$data['s_m_domain'] = str_replace('https://','',$data['s_m_domain']);
        $site_domain = str_replace('http://','',C('qscms_site_domain'));
		$site_domain = str_replace('http://','',$site_domain);
        $wap_domain = str_replace('http://','',C('qscms_wap_domain'));
		$wap_domain = str_replace('https://','',$wap_domain);
        if($data['s_domain'] == $site_domain || $data['s_m_domain'] == $site_domain){
            $this->error('分站域名不能与主域名重复！');
        }
		if(!empty($data['s_m_domain'])){
			if($data['s_domain'] == $wap_domain || $data['s_m_domain'] == $wap_domain){
				$this->error('分站域名不能与触屏版域名重复！');
			}
			if($data['s_domain'] == $data['s_m_domain']){
				$this->error('分站pc域名不能与分站触屏版域名重复！');
			}
		}
		
        $subsites = D('Subsite')->get_subsite_cache();
        if($data['s_id']){
            if(($subsites[$data['s_domain']] && $subsites[$data['s_domain']]['s_id'] != $data['s_id']) || ($subsites[$data['s_m_domain']] && $subsites[$data['s_m_domain']]['s_id'] != $data['s_id'])){
                $d = $subsites[$data['s_domain']]['s_sitename'] ?:$subsites[$data['s_m_domain']]['s_sitename'];
                $this->error('分站域名不能与('.$d.')域名重复！');
            }
			if($subsites[$data['s_sitename']]['s_sitename']!=$data['s_sitename'] && $subsites[$data['s_sitename']]['s_sitename']){
                $d = $subsites[$data['s_sitename']]['s_sitename'] ?:$subsites[$data['s_sitename']]['s_sitename'];
                $this->error('分站名称不能与('.$d.')名称重复！');
            }
        }else{
            if($subsites[$data['s_domain']] || $subsites[$data['s_m_domain']]){
                $d = $subsites[$data['s_domain']]['s_sitename'] ?:$subsites[$data['s_m_domain']]['s_sitename'];
                $this->error('分站域名不能与('.$d.')域名重复！');
            }
			if($subsites[$data['s_sitename']]){
                $d = $subsites[$data['s_sitename']]['s_sitename'] ?:$subsites[$data['s_sitename']]['s_sitename'];
                $this->error('分站名称不能与('.$d.')名称重复！');
            }
        }
        $py = new \Common\qscmslib\pinyin;
        $data['s_spell'] = $data['s_spell']?:$py->getFirstPY($data['s_sitename']);
        $data['s_first'] = $data['s_first']?:strtoupper(substr($data['s_spell'],0,1));
		$data['pc_type']=$data['pc_type']==0?'http://':'https://';
		$data['mobile_type']=$data['mobile_type']==0?'http://':'https://';
        return $data;
    }
	public function _after_insert($id,$data){
		$this->_after_update();
    }
	/**
     * [_before_update 分站修改前项]
     * [_after_update 分站修改后项]
     */
	public function _before_update($data){
        return $this->_before_insert($data);
    }
	public function _after_update(){
        $domain = D('Config')->sub_domain();
		D('Subsite')->get_subsite_domain();
		D('Subsite')->get_subsite_tpl();
        $this->update_config(array('APP_SUB_DOMAIN_RULES'=>$domain),CONF_PATH.'sub_domain.php');
    }
}
?>