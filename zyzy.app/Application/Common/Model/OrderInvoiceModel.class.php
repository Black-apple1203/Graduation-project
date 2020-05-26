<?php
namespace Common\Model;
use Think\Model;
class OrderInvoiceModel extends Model{
	public $title_arr = array(1=>'单位',2=>'个人');
	public $cate = array(1=>'咨询费',2=>'咨询服务费',3=>'服务费');
	protected $_validate = array(
		array('order_id,uid,title,cid,addressee,mobile,address,postcode','identicalNull','',0,'callback'),
		array('order_id,uid,title,cid','identicalEnum','',0,'callback'),
		array('organization','0,30','{%order_invoice_organization_length}',2,'length'),
		array('addressee','0,30','{%order_invoice_addressee_length}',2,'length'),
		array('mobile','_mobile','{%order_invoice_mobile_error}',2,'callback'),
		array('address','0,100','{%order_invoice_address_length}',2,'length'),
		array('postcode','zip','{%order_invoice_postcode_error}',2)
	);
	protected function _mobile($data){
		if(fieldRegex($data,'mobile') || fieldRegex($data,'tel')) return true;
		return false;
	}
	public function getone($id,$uid=0){
		$where['order_id'] = $id;
		$uid>0 && $where['uid'] = $uid;
		$info = $this->where($where)->find();
		if($info){
			$category = D('OrderInvoiceCategory')->invoice_category_cache();
			$info['title'] = $this->title_arr[$info['title']];
			$info['cate'] = $category[$info['cid']];
			return $info;
		}else{
			return false;
		}
	}
	public function addone($data,$user){
		$has = $this->getone($data['order_id'],$data['uid']);
		if($has){
			return array('state'=>0,'error'=>'您已经索取过发票了！');
		}
		if($data['title'] == 2){
			unset($data['organization']);
		}
		$data['addtime'] = time();
		if(false === $data = $this->create($data)){
            return array('state'=>0,'error'=>$this->getError());
        }
        if($this->add()){
        	write_members_log(C('visitor'),'','索取发票（订单id：'.$data['order_id'].'）');
        	return array('state'=>1,'error'=>'索取发票成功！','data'=>$this->getone($data['order_id'],$data['uid']));
        }else{
        	return array('state'=>0,'error'=>'操作失败！');
        }
	}
}
?>