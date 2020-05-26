<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class SubsiteController extends FrontendController{
	// 初始化函数
	public function _initialize(){
		parent::_initialize();
	}
	public function index(){
		$passport = $this->_user_server();
		if($passport->is_sitegroup()){
			$sub = $passport->uc('sitegroup')->get_subsite();
			$count = count($sub['subsite']);
			$mod = (int)($count/2);
			foreach ($sub['subsite'] as $key => $val) {
				$sitegroup_list[$val['ordid']][] = $val;
			}
			$this->assign('mod_val',$count%2 ? $mod+1 : $mod);
            $this->assign('sitegroup',$sub['subsite']);
            $this->assign('sitegroup_org',$sub['subsite_org']);
            $this->assign('sitegroup_list',$sitegroup_list);
        }
		$this->display();
	}
}
?>