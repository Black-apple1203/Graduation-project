<?php 
namespace Common\Model;
use Think\Model;
class CategoryMajorModel extends Model
{
	protected $_validate = array(
		array('categoryname','1,25','{%category_major_length_error_categoryname}',0,'length'),
	);
	protected $_auto = array (
		array('category_order',0),
	);
	/**
	 * [major_cache 获取专业写入缓存]
	 */
	public function major_cache(){
		$major = array();
		$majorData = $this->field('id,parentid,categoryname')->order('parentid asc,category_order desc,id')->select();
		foreach ($majorData as $key => $val) {
			$major[$val['parentid']][$val['id']] = $val['categoryname'];
		}
		F('major_cate',$major);
		return $major;
	}
	/**
	 * [get_major_cache 读取专业数据]
	 */
	public function get_major_cache($pid=0){
		if(false === $major = F('major_cate')){
			$major = $this->major_cache();
		}
		if($pid === 'all') return $major;
		return $major[intval($pid)];
	}
	/**
	 * [major_data_cache 专业类别缓存]
	 */
	public function major_data_cache(){
		$majorList = $this->order('category_order desc,id')->getfield('id,parentid,categoryname');
		F('major_data_list',$majorList);
		return $majorList;
	}
	public function get_major_list(){
		if(false === $major = F('major_data_list')){
			$major = $this->major_data_cache();
		}
		return $major;
	}
		/**
	 * [major_data_cache_ios 专业类别缓存]
	 */
	public function major_data_cache_ios(){
		$majorList = $this->order('category_order desc,id')->getfield('id,parentid,categoryname');
		foreach($majorList as $key=>$val){
			if($val['parentid']!=0){
			$datas[] = $val;
			}
		}
		//dump($datas);die;
		F('major_data_list_ios',$datas);
		return $datas;
	}
	public function get_major_list_ios(){
		if(false === $major = F('major_data_list_ios')){
			$major = $this->major_data_cache_ios();
		}
		return $major;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('major_cate', NULL);
        F('major_data_list',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('major_cate', NULL);
        F('major_data_list',NULL);
    }
	public function category_delete($id,$num=0){
		if (!is_array($id)) $id=array($id);
		foreach ($id as $key => $value) {
			$child = $this->where(array('parentid'=>$value))->getfield('id',true);
			if($child){
				$num = $this->category_delete($child,$num);
			}
			$this->where(array('id'=>$value))->delete();
			$num++;
		}
		return $num;
	}
}
?>