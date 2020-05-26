<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class WeixinController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Config');
    }
	/**
	 * [config 公众号配置]
	 */
	public function index(){
		$this->_edit();//调用父类_edit方法
        $this->display();
	}
	/**
	 * [config 公众号营销]
	 */
	public function wx_public_number(){
		$setmeal_show = D('Setmeal')->order('show_order desc,id')->select();
		$this->assign('setmeal_show',$setmeal_show);
		$this->display();
	}
	public function wx_public(){
		if($jobs_num = I('post.jobs_num',0,'intval')){
			$this->limit = $jobs_num;
		}
		$setmeal_id = I('request.setmeal_id');
		if(1 == $list_type = I('post.list_type',0,'intval')){
			$this->_mod= D('JobsSearch');
			if(1 ==$jobs_order = I('post.jobs_order',0,'intval')){
				$this->order ="stime desc";
			}else{
				$this->order ="refreshtime desc";	
			}
			if($setmeal_id){
				foreach ($setmeal_id as $key => $val) {
				 	$w[] = 'set'.$val;
				}
				if($w){
					$map[] = '+('.implode(' ',$w).')';
				}
				$where['key'] = array('match_mode',$map);
			}
			$jid = $this->_mod->where($where)->order($this->order)->limit($this->limit)->field('id')->select();
			foreach ($jid as $key => $val) {
                $val['id'] && $jobs[] = $val['id'];
            }
            if($jobs){
				$jobs_list = M('Jobs')->where(array('id'=>array('in',$jobs),'user_status'=>1))->field('id,jobs_name,contents,minwage,maxwage,experience_cn,education_cn,age,negotiable')->select();
				foreach ($jobs_list as $key => $value) {
					$row = $value;
					$age = explode('-',$value['age']);
			        if(!$age[0] && !$age[1]){
			            $row['age_cn'] = '不限';
			        }else{
			            $age[0] && $row['age_cn1'] = $age[0].'岁';
			            $age[1] && $row['age_cn2'] = $age[1].'岁';
			        }
					$contact = M('JobsContact')->where(array('pid'=>$value['id']))->field('address')->find();
					$row['address'] = $contact['address'];
					$row['jobs_url'] = C('qscms_site_domain').url_rewrite('QS_jobsshow',array('id'=>$value['id']),0,false);
					$list[] = $row;
				}
			}else{
				$list = array();
			}
		}else{
			$this->_mod=  D('CompanyProfile');
			if($setmeal_id){
				$where['setmeal_id'] = array('in',$setmeal_id);
			}
			$where['user_status'] =1;
			$where['audit'] =1;
			$com = $this->_mod->where($where)->field('id,companyname,tag,address')->order('refreshtime desc')->limit($this->limit)->select();
			foreach ($com as $key => $value) {
				$row = $value;
				$job = M('Jobs')->where(array('audit'=>1,'display'=>1,'company_id'=>$value['id']))->field('id,jobs_name,minwage,maxwage,negotiable')->limit(2)->select();
				if(!$job){
					continue;
				}
				if($value['tag']){
		            $tag_arr = explode(",", $value['tag']);
		            foreach ($tag_arr as $k=> $val) {
		                $arr = explode("|", $val);
		                $row['tag_arr'][] = $arr[1];
		            }
		        }
		        $row['img_url'] = \Common\qscmslib\weixin::qrcode_img(array('type'=>'company','width'=>85,'height'=>85,'params'=>$value['id'],'expire'=>2592000));
				$row['job'] =$job;
				$list[] =$row;
			}
		}
		$this->assign('jobs_order',$jobs_order);
		$this->assign('setmeal_id',implode(',',$setmeal_id));
		$this->assign('list_type',$list_type);
		$this->assign('jobs_num',$jobs_num);
		$this->assign('list',$list);
		$this->display();
	}
	public function ajax_tpl(){
		$list_type = I('post.list_type',0,'intval');
		if($list_type == 1){
			$type='job';
		}else{
			$type='com';
		}
		$num = I('post.num',0,'intval');
		$tpl = 'ajax'.'_'.$type.'_'.$num;
		if($jobs_num = I('post.jobs_num',0,'intval')){
			$this->limit = $jobs_num;
		}
		if(1 == $list_type){
			$this->_mod= D('JobsSearch');
			if(1 ==$jobs_order = I('post.jobs_order',0,'intval')){
				$this->order ="stime desc";
			}else{
				$this->order ="refreshtime desc";	
			}
			if($setmeal_id = I('request.setmeal_id')){
				$setmeal_id = explode(',',$setmeal_id);
				foreach ($setmeal_id as $key => $val) {
				 	$w[] = 'set'.$val;
				}
				if($w){
					$map[] = '+('.implode(' ',$w).')';
				}
				$where['key'] = array('match_mode',$map);
			}
			$jid = $this->_mod->where($where)->order($this->order)->limit($this->limit)->field('id')->select();
			foreach ($jid as $key => $val) {
                $val['id'] && $jobs[] = $val['id'];
            }
            if($jobs){
				$jobs_list = M('Jobs')->where(array('id'=>array('in',$jobs),'user_status'=>1))->field('id,jobs_name,contents,minwage,maxwage,experience_cn,education_cn,age,negotiable')->select();
				foreach ($jobs_list as $key => $value) {
					$row = $value;
					$age = explode('-',$value['age']);
			        if(!$age[0] && !$age[1]){
			            $row['age_cn'] = '年龄不限';
			        }else{
			            $age[0] && $row['age_cn1'] = $age[0].'岁';
			            $age[1] && $row['age_cn2'] = $age[1].'岁';
			        }
					$contact = M('JobsContact')->where(array('pid'=>$value['id']))->field('address')->find();
					$row['address'] = $contact['address'];
					$row['jobs_url'] = C('qscms_site_domain').url_rewrite('QS_jobsshow',array('id'=>$value['id']),0,false);
					$list[] = $row;
				}
			}else{
				$list = array();
			}
		}else{
			$this->_mod=  D('CompanyProfile');
			if($setmeal_id = I('request.setmeal_id')){
				$where['setmeal_id'] = array('in',$setmeal_id);
			}
			$where['user_status'] =1;
			$where['audit'] =1;
			$com = $this->_mod->where($where)->field('id,companyname,tag,address')->limit($this->limit)->select();
			foreach ($com as $key => $value) {
				$row = $value;
				$job = M('Jobs')->where(array('audit'=>1,'display'=>1,'company_id'=>$value['id']))->field('id,jobs_name,minwage,maxwage,negotiable')->limit(2)->select();
				if(!$job){
					continue;
				}
				if($value['tag']){
		            $tag_arr = explode(",", $value['tag']);
		            foreach ($tag_arr as $k=> $val) {
		                $arr = explode("|", $val);
		                $row['tag_arr'][] = $arr[1];
		            }
		        }
		        $row['img_url'] = \Common\qscmslib\weixin::qrcode_img(array('type'=>'company','width'=>85,'height'=>85,'params'=>$value['id'],'expire'=>2592000));
				$row['job'] =$job;
				$list[] =$row;
			}
		}
		$this->assign('jobs_order',$jobs_order);
		$this->assign('setmeal_id',implode(',',$setmeal_id));
		$this->assign('list_type',$list_type);
		$this->assign('jobs_num',$jobs_num);
		$this->assign('list',$list);
		$html = $this->fetch($tpl);
        $this->ajaxReturn(1, '获取数据成功！', $html);
	}
}
?>