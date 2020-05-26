<?php 
namespace Common\Model;
use Think\Model;
class SubsiteModel extends Model
{
	protected $_validate = array(
		array('s_sitename,s_domain','identicalNull','',0,'callback'),
		array('s_order','number','',2,'regex'),
		array('s_sitename','0,10','{%subsite_s_sitename_length}',0,'length'),
		array('s_domain','0,50','{%subsite_s_domain_length}',0,'length'),
		array('s_m_domain','0,50','{%subsite_s_m_domain_length}',0,'length'),
		array('s_title','0,200','{%subsite_s_title_length}',0,'length'),
		array('s_keywords','0,200','{%subsite_s_keywords_length}',0,'length'),
		array('s_description','0,600','{%subsite_s_description_length}',0,'length')
	);
	protected $_auto = array (
		array('s_tpl','default')
	);
	public function get_subsites(){
		if(false === $subsite = F('subsite_ids')){
			$subsite = $this->where(array('s_display'=>1))->getfield('s_id',true);
			$subsite[] = 0;
		}
		F('subsite_ids',$subsite);
		return $subsite;
	}
	public function get_subsite_cache(){
		if(false === $subsite = F('subsite_list')) $subsite = $this->subsite_cache();
		return $subsite;
	}
	public function subsite_cache(){
		$data = $this->where(array('s_display'=>1))->order('s_order desc,s_id')->getfield('s_id,s_domain,s_m_domain,s_sitename,s_spell,s_first,s_pc_logo,s_mobile_logo,s_title,s_keywords,s_description,pc_type,mobile_type');
		if(false === $config = F('config')) $config = D('Config')->config_cache();
		$home_domain = str_replace('http://','',$config['qscms_site_domain']);
		$home_domain = str_replace('https://','',$home_domain);
		$home = array('s_domain'=>$home_domain,'s_id'=>0,'s_sitename'=>'总站');

        if(preg_match('/com.cn|net.cn|gov.cn|org.cn$/',$home_domain) === 1){
            $domain = array_slice(explode('.', $home_domain), -3, 3);
        }else{
            $domain = array_slice(explode('.', $home_domain), -2, 2);
        }
        $domain = implode('.',$domain);
        if($domain != $home_domain){
        	$home1 = array('s_domain'=>$domain,'s_id'=>0,'s_sitename'=>'总站');
        }

		if($config['qscms_wap_domain']){
			$wap_domain = str_replace('http://','',$config['qscms_wap_domain']);
			$wap_domain = str_replace('https://','',$wap_domain);
			$subsite_list[$wap_domain] = array('s_domain'=>$home_domain,'s_m_domain'=>$wap_domain,'s_id'=>0,'s_sitename'=>'总站');
			$home['s_m_domain'] = $wap_domain;
			$home1 && $home1['s_m_domain'] = $wap_domain;
		}
		$subsite_list[$home_domain] = $home;
		$domain && $subsite_list[$domain] = $home1;
		foreach ($data as $key => $val) {
			$subsite_list[$val['s_domain']] = $val;
			$val['s_m_domain'] && $subsite_list[$val['s_m_domain']] = $val;
			$subsite_list[$val['s_sitename']] = $val;
		}
		F('subsite_list',$subsite_list);
		return $subsite_list;
	}
	
	public function get_subsite_domain(){
		$subsite_list = $this->where(array('s_display'=>1))->order('s_order desc,s_id')->getfield('s_id,s_sitename,s_domain,s_m_domain,pc_type,mobile_type,s_first');
		$home_domain = str_replace('http://','',C('qscms_site_domain'));
		$home_domain = str_replace('https://','',$home_domain);
		$home = array('s_id'=>'0','s_domain'=>$home_domain,'s_sitename'=>'总站');
		$subsite[0] = $home;
		foreach ($subsite_list as $key => $val) {
			$subsite[$key] = $val;
		}
		F('subsite_domain_list',$subsite);
		return $subsite;
	}
	public function get_subsite_tpl(){
		$subsite_list = $this->where(array('s_display'=>1))->order('s_order desc,s_id')->getfield('s_id,s_first,s_sitename,s_domain,s_m_domain,pc_type,mobile_type');
		$home_domain = str_replace('http://','',C('qscms_site_domain'));
		$home_domain = str_replace('https://','',$home_domain);
		foreach ($subsite_list as $key => $val) {
			$subsite[$key] = $val;
		}
		F('subsite_tpl_list',$subsite);
		return $subsite;
	}
	/**
     * [sub_domain 子域名生成]
     */
    public function sub_domain(){
		$sub_arr = M('Subsite')->where(array('s_display'=>1))->getField('s_m_domain',true);
		if($sub_arr){
			foreach ($sub_arr as $val) {
				$domain[$val] = 'Mobile';
			}
		}
        return $domain ?:array();
    }
	/**
     * [_after_update 更新缓存文件]
     */
	 protected function _after_update($data,$options){
          D('Subsite')->subsite_cache(); 
    }
	
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('subsite_list', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('subsite_list', NULL);
        F('subsite_domain_list',NULL);
		F('subsite_tpl_list',NULL);
       
    }
}
?>