<?php
/**
 * 分站
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class subsiteTag {
    public function run(){
		if(false === $subsite = F('subsite_domain_list')){
			return D('Subsite')->get_subsite_domain();
		}
		return $subsite;
    }
}