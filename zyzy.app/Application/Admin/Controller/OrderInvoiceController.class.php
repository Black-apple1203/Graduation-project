<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class OrderInvoiceController extends BackendController{
	public function _initialize(){
		parent::_initialize();
		$this->_mod = D('OrderInvoice');
	}
	public function index(){
		$key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $map['uid'] = intval($key);
                    break;
                case 2:
                    $map['addressee'] = array('like','%'.$key.'%');
                    break;
            }
        }else{
            if($settr = I('request.settr',0,'intval')){
                $map['addtime'] = array('gt',strtotime("-".$settr." day"));
            }
        }
		$this->where = $map;
		$this->order = 'audit asc';
		$this->assign('count',parent::_pending('OrderInvoice',array('audit'=>0)));
		parent::index();
	}

    protected function _after_search(){
        $data = $this->get('list');
        if(false == $category = F('order_invoice_category')){
            $category = D('OrderInvoiceCategory')->invoice_category_cache();
        }
        foreach ($data as $key => $val) {
            $val['cate_cn'] = $category[$val['cid']];
            $list[] = $val;
        }
        $this->assign('category',$category);
        $this->assign('list',$list);
    }
	/**
	 * [set_audit 处理发票申请]
	 */
	public function set_audit(){
		$id = I('request.order_id');
        $audit = I('request.audit',0,'intval');
        if(empty($id)){
            $this->error('请选择记录！');
        }
        !is_array($id) && $id = array($id);
        $r = $this->_mod->where(array('order_id'=>array('in',$id)))->setField('audit',$audit);
        if(false !== $r){
            $this->success('设置成功！响应行数'.$r);
        }else{
            $this->error('设置失败！');
        }
	}
	/**
	 * [edit 详情]
	 */
	public function invoice_show(){
		$id = I('get.order_id',0,'intval');
		!$id && $this->error('请选择发票内容！');
		$order = M('Order')->where(array('id'=>$id))->find();
		$order['username'] = M('Members')->where(array('uid'=>$order['uid']))->getfield('username');
		$this->assign('order',$order);
		$this->_tpl = 'invoice_show';
		parent::edit();
	}

	protected function _after_select($info){
        if(false == $category = F('order_invoice_category')){
            $category = D('OrderInvoiceCategory')->invoice_category_cache();
        }
        $info['cate_cn'] = $category[$info['cid']];
        return $info;
    }
	/**
	 * [invoice_category 发票类型列表]
	 */
	public function invoice_category(){
		$this->_name = 'OrderInvoiceCategory';
		$this->order = 'category_order desc,id desc';
		parent::index();
	}
	/**
	 * [add_category 添加发票类型]
	 */
	public function add_category(){
		$this->_name = 'OrderInvoiceCategory';
		parent::add();
	}
	/**
	 * [edit_category 修改发票类型]
	 */
	public function edit_category(){
		$this->_name = 'OrderInvoiceCategory';
		parent::edit();
	}
	/**
	 * [delete_category 删除发票类型]
	 */
	public function delete_category(){
		$this->_name = 'OrderInvoiceCategory';
		parent::delete();
	}
}
?>