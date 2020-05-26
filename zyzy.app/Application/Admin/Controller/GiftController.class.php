<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class GiftController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_name = 'Gift';
		$this->setmeal = M('Setmeal')->select();
        $this->assign('setmeal',$this->setmeal);		
    }
	public function index() {		
		parent::index();
    }
	public function gift_add() {
		if(C('qscms_open_give_gift')==0){
			 $this->error("优惠券功能已关闭！请开启后重试");
		}
        parent::add();
    }
	public function _before_insert($data){
		$data['addtime'] = time();
		$setmeal_id = $_POST['setmeal_id'];
		$sema = D('Setmeal')->get_setmeal_one($setmeal_id);
		$data['setmeal_name'] = $sema['setmeal_name'];
        return $data;
    }
	public function _before_update($data){
        $data['addtime'] = time();
		$setmeal_id = $_POST['setmeal_id'];
		if(!$setmeal_id){
			$this->error("请选择绑定套餐！");
		}
		$sema = D('Setmeal')->get_setmeal_one($setmeal_id);
		$data['setmeal_name'] = $sema['setmeal_name'];
        return $data;
    }
	public function gift_edit(){	
		if(C('qscms_open_give_gift')==0){
			 $this->error("优惠券功能已关闭！请开启后重试");
		}			
        parent::edit();
    }
	public function gift_set(){	
		if(IS_POST){
            foreach($_POST as $key=>$val){
				if($key == 'gift_id'){
					$where['name']='is_give_gift_value';
					$data['value']=implode(',',$val);
				}else{
					$where['name']=$key;
					$data['value']=$val;
				}
				if(false === M('Config')->where($where)->save($data)) $this->ajaxReturn(0, "保存失败!");
			}			
			F('config', NULL);
			$this->ajaxReturn(1,L('operation_success'));
        }else{
            $all_gifts = D("Gift")->get_gift_list('',0);
			$this->assign("gifts",$all_gifts);			
			$configs = M('Config')->getField('name,value');			
			$this->assign("is_give_gift",$configs['is_give_gift']);	
			$this->assign("is_give_gift_value",$configs['is_give_gift_value']);	
			$this->assign("open_give_gift",$configs['open_give_gift']);	
			$this->assign("gift_min_remind",$configs['gift_min_remind']);				
        }
        $this->display();
    }
	public function gift_delete(){
		if(C('qscms_open_give_gift')==0){
			 $this->error("优惠券功能已关闭！请开启后重试");
		}
		//h 优惠券删除问题
		$id=I('request.id');
		$giftstatic = M("GiftStatic")->field("gift_id,id")->select();
		foreach($giftstatic as $val){
			$gift_id = explode(",",$val['gift_id']);
			foreach($id as $v){
				if(in_array($v,$gift_id)){
					$keys = array_search($v,$gift_id);
					unset($gift_id[$keys]);
					if($gift_id){
						$static_giftid = implode(",",$gift_id);
						$data['gift_id'] = $static_giftid; 
						M("GiftStatic")->where(array("id"=>$val['id']))->save($data);
					}else{
						M("GiftStatic")->where(array("id"=>$val['id']))->delete();
					}
					
				}
			}
		}
		D('GiftIssue')->where(array('gift_id'=>array('in',$id)))->delete();
        parent::delete();
    }	
	public function issue() {
        $all_gifts = D("Gift")->get_gift_list('',0);
		$this->assign("gifts",$all_gifts);
		$all_companys = M("CompanyProfile")->field("id,uid,companyname")->limit(0,10)->select();
		$this->assign("companys",$all_companys);
		$this->display();
    }	
	public function ajax_get_company() {
        $key = I('post.key','','trim');
        $page = I('post.page',1,'intval');
		$perpage = 10;
		$uppage = $page-2;
		$nowpage = $page-1;
		$nextpage = $page+1;
		if($uppage>=0){
			$uppage_companys = M("CompanyProfile")->field("id,uid,companyname")->where(array('companyname'=>array('like','%'.$key.'%')))->limit($uppage*10,$perpage)->select();
		}
		$all_companys = M("CompanyProfile")->field("id,uid,companyname")->where(array('companyname'=>array('like','%'.$key.'%')))->limit($nowpage*10,$perpage)->select();
		$nextpage_companys = M("CompanyProfile")->field("id,uid,companyname")->where(array('companyname'=>array('like','%'.$key.'%')))->limit($nextpage*10,$perpage)->select();
		$html='';
		foreach($all_companys as $key=>$val){
			$html .= '<div class="item item-'.$val['uid'];
			if($val['disable'] == 1){
				$html .= ' disable ';	
			}
			$html .= '" data-id="'.$val['uid'].'" data-title="'.$val['companyname'].'">
						<div class="title">'.$val['companyname'].'</div>
					</div>';
		}
		$html .= '<div>';
		if($uppage>0){
			$html.='<input type="button" class="admin_submit gray" value="上一页" id="upbtn" uppage="'.$uppage.'" style="margin-left:20px;"/>';
		}
		if($nextpage_companys){
			$html.='<input type="button" class="admin_submit gray" value="下一页" id="nextbtn" nextpage="'.$nextpage.'" style="float:right;"/>';
		}
		$html.='</div>';
		$this->ajaxReturn(1,'获取数据成功',$html);
		
    }
	public function gift_issue() {
		if(C('qscms_open_give_gift')==0){
			$this->ajaxReturn(0, "优惠券功能已关闭！请开启后重试");
		}
		$page = I('request.page',1,'intval');	
		$gift_id = !empty($_REQUEST['gift_id'])?$_REQUEST['gift_id']:$this->ajaxReturn(0, "请选择优惠券！");
		$gift_id_str = implode(",",$gift_id);
		$start = ($page-1)*500;
		$end = $page*500;
		if($_REQUEST['setmeal_id']=='selfdefine'){			
			$company_num = -count(explode(',',I('request.company', '', 'trim')));	
			$end_num = -$company_num;
			$rest_num = 0;				
		}elseif($_REQUEST['setmeal_id']=='all'){
			$company_num = M('CompanyProfile')->count();
			$end_num = ($company_num>$end)?$end:$company_num;
			$rest_num = ($company_num-$end>0)?$company_num-$end:0;
		}else{
			$company_where['setmeal_id'] = $_REQUEST['setmeal_id'];
			$company_num = M('CompanyProfile')->where($company_where)->count();
			$end_num = ($company_num>$end)?$end:$company_num;
			$rest_num = ($company_num-$end>0)?$company_num-$end:0;
		}
		if(($start<=$company_num && $company_num>0) || $company_num<0){
			if($_REQUEST['setmeal_id']=='selfdefine'){			
				$company = trim(I('request.company'))?explode(',',I('request.company', '', 'trim')):$this->ajaxReturn(0, "请选择企业！");					
			}elseif($_REQUEST['setmeal_id']=='all'){
				$company = M('CompanyProfile')->limit($start,500)->getField('uid',true);
			}else{
				$company_where['setmeal_id'] = $_REQUEST['setmeal_id'];
				$company = M('CompanyProfile')->where($company_where)->limit($start,500)->getField('uid',true);
			}		
			if(count($company)>1){
				$data['gift_type']=3;//1单发企业=专享优惠券 ； 2新用户开了送=新用户专享； 3活动批量发=活动专享
			}else{
				$data['gift_type']=1;
			}
			if($page>1){
				$company_str = $_SESSION['company_str'];
				$company_str.= ','.implode(",",$company);
			}else{
				$company_str = implode(",",$company);	
			}
			$_SESSION['company_str']=$company_str;	
			$succ=0;$fals=0;$str="";
			$gift_issue = M('GiftIssue')->getField('id,issue_num');
			$max_issue_num_id = M('GiftIssue')->max('id');
			$max_issue_num = $gift_issue[$max_issue_num_id];
			$gifts = M("Gift")->getField('id,gift_name,price,setmeal_name,setmeal_id,effectivetime');
			foreach($gifts as $k=>$v){
				$id=$v['id'];
				$gift_arr[$id]=$v;
			}
			$now_issue_num_id=$max_issue_num_id+1;	
			foreach($company as $key=>$val){
				$num=0;
				foreach($gift_id as $keys=>$vals){
					$data['gift_id']=$vals;
					$data['gift_setmeal_id']=$gift_arr[$vals]['setmeal_id'];
					$data['admin_id']=C('visitor.id');
					$data['is_used']=2;
					$data['addtime']=time();
					$data['static_id']=$static_id;
					$data['deadtime']=$data['addtime']+$gift_arr[$vals]['effectivetime']*60*60*24;
					if(strlen($now_issue_num_id)<10){
						$issue_num = $now_issue_num_id;
						$len = strlen($val['uid']);
						for($i=8;$i>=$len;$i--){
							$issue_num = '0'.$issue_num;
						}
					}else{
						$issue_num = $now_issue_num_id;
					}
					$now_issue_num_id++;
					$data['issue_num'] = $issue_num;
					$data['uid']=$val;
					$insertid = M('GiftIssue')->add($data);
					if($insertid){
						$num++;
						$succ++;
						$str.=$insertid.",";
					}else{
						$fals++;
					}				
					unset($data['uid']);
				}
				$user_info = D('Members')->find($val);
				if($num>0){
					//站内信
					$setsqlarr_pms['message'] = "恭喜您获得".$num."张套餐优惠券！";					
					D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $setsqlarr_pms['message'],1);
					//sms
					$sms = D('SmsConfig')->get_cache();
					if ($sms['set_gift'] == 1) {
						$send_sms = true;
						if (C('qscms_company_sms') == 1) {
							if ($user_info['sms_num'] == 0) {
								$send_sms = false;
							}
						}
						if ($send_sms == true) {
							$r = D('Sms')->sendSms('notice', array('mobile' => $user_info['mobile'], 'tpl' => 'set_gift', 'data' => array('username' => $user_info['username'],'succ' => $num)));
							if ($r === true) {
								D('Members')->where(array('uid' => $val))->setDec('sms_num');
							}
						}
					}
					//微信
					if (false === $module_list = F('apply_list')) $module_list = D('Apply')->apply_cache();
					if ($module_list['Weixin']) {
						$map['uid'] = $val;		
						$remind = M('GiftIssue')->where($map)->order('addtime desc')->find();
						D('Weixin/TplMsg')->set_gift($val, '套餐优惠券', date('Y-m-d H:i',$remind['addtime']).'至'.date('Y-m-d H:i',$remind['deadtime']));
					}
				}
			}
			if($_REQUEST['setmeal_id']=='selfdefine'){			
				$static_data['admin_id']=C('visitor.id');
				$static_data['gift_id']=$gift_id_str;		
				$static_data['uid']=$_SESSION['company_str'];
				$static_data['addtime']=time();
				$static_id = M('GiftStatic')->add($static_data);
				if(!$gift_id_str){
					$this->ajaxReturn(0, "发放失败，请选择礼品卡!");
				}
				if(!$_SESSION['company_str']){
					$this->ajaxReturn(0, "发放失败，没有此类型的企业会员!");
				}
				if($gift_id_str && $_SESSION['company_str']){
					$issue_where['gift_id']=array('IN',$gift_id_str);
					$issue_where['uid']=array('IN',$_SESSION['company_str']);
					$issue_date['static_id']=$static_id;
					M('GiftIssue')->where($issue_where)->save($issue_date);
					unset($_SESSION['company_str']);
					$this->ajaxReturn(1, "成功发放".$end_num."份优惠券！");
				}else{
					$this->ajaxReturn(0, "发放失败!");
				}				
			}else{
				$this->ajaxReturn(2, "已发送".$end_num."份，还剩".$rest_num."份未发放，请勿进行其他操作！！！");	
			}
				
		}else{
			$static_data['admin_id']=C('visitor.id');
			$static_data['gift_id']=$gift_id_str;		
			$static_data['uid']=$_SESSION['company_str'];
			$static_data['addtime']=time();
			$static_id = M('GiftStatic')->add($static_data);
			if(!$gift_id_str){
				$this->ajaxReturn(0, "发放失败，请选择礼品卡!");
			}
			if(!$_SESSION['company_str']){
				$this->ajaxReturn(0, "发放失败，没有此类型的企业会员!");
			}
			if($gift_id_str && $_SESSION['company_str']){
				$issue_where['gift_id']=array('IN',$gift_id_str);
				$issue_where['uid']=array('IN',$_SESSION['company_str']);
				$issue_date['static_id']=$static_id;
				M('GiftIssue')->where($issue_where)->save($issue_date);
				unset($_SESSION['company_str']);
				$this->ajaxReturn(1, "发放成功!");
			}else{
				$this->ajaxReturn(0, "发放失败!");
			}
			
			
		}
    }
	public function static_list() {
        $this->_name = 'GiftStatic';
		$this->_tpl = "static_list";
		$this->custom_fun = "static_list_custom_fun";
		parent::index();
    }
	public function static_delete(){	
		if(C('qscms_open_give_gift')==0){
			 $this->error("优惠券功能已关闭！请开启后重试");
		}
		$this->_name = 'GiftStatic';	
        parent::delete();
    }
	protected function static_list_custom_fun($list) {
        $gifts = M("Gift")->getField('id,gift_name');	
		$admins = M("Admin")->getField('id,username');		
		foreach($list as $k=>$val){
			$gift_id = $val['gift_id'];
			$val['gift_name'] = $gifts[$gift_id];
			$admin_id = $val['admin_id'];
			$val['admin_name'] = $admins[$admin_id];
			$val['uidcount'] = M('GiftIssue')->where(array('static_id'=>$val['id']))->count();
			$list[$k] = $val;
		}
		return $list;	
    }
}