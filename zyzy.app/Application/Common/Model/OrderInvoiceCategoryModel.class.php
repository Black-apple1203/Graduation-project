<?php
namespace Common\Model;
use Think\Model;
class OrderInvoiceCategoryModel extends Model{
	protected $_validate = array(
		array('categoryname','require','{%order_invoice_category_null_error_categoryname}',1),
		array('categoryname','1,60','{%order_invoice_category_length_error_categoryname}',1,'length'),
		array('categoryname','','{%order_invoice_category_unique_category}',0,'unique'),
	);
	protected $_auto = array (
		array('admin_set',0),
	);
	public function invoice_category_cache(){
		$order_invoice_category = $this->order('category_order desc,id desc')->getfield('id,categoryname');
		F('order_invoice_category',$order_invoice_category);
		return $order_invoice_category;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('order_invoice_category', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('order_invoice_category', NULL);
    }
}
?>