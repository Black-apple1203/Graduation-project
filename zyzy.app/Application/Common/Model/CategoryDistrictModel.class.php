<?php 
namespace Common\Model;
use Think\Model;
class CategoryDistrictModel extends Model
{
	protected $_validate = array(
		array('categoryname','1,60','{%category_district_length_error_categoryname}',1,'length'),
		array('spell','','{%category_district_exist_error_spell}',2,'unique',3),
	);
	protected $_auto = array (
		array('parentid',0),
		array('category_order',0),
	);
	/**
	 * [district_level 获取地区层级]
	 */
	public function set_district_level($pid = 0){
		$district = $this->get_district_cache('all');
		$this->_level = 0;
		$this->get_level($pid,$district,0);
		return $this->_level;
	}
	protected function get_level($id,$district,$s){
		$s++;
		if($s>$this->_level) $this->_level++;
		foreach($district[$id] as $key=>$val){
			$district[$key] && $this->get_level($key,$district,$s);
		}
	}
	public function level($id,$array=array(),$i=0) {
        foreach($array as $n=>$value){
            if ($value['id'] == $id) {
                if($value['parentid']== '0') return $i;
                $i++;
                return $this->level($value['parentid'],$array,$i);
            }
        }
    }
	/**
	 * [custom description]
	 */
	public function custom_district_cache(){
		$district = array();
		$districtData = $this->field('id,parentid,categoryname,spell')->order('category_order desc,id asc')->select();
		foreach ($districtData as $key => $val) {
			$district[$val['parentid']][$val['id']] = $val;
			$level = M('CategoryDistrict')->where(array('parentid'=>$val['id']))->find();
			if($level){
				$district[$val['parentid']][$val['id']]['level'] = 1;
			}else{
				$district[$val['parentid']][$val['id']]['level'] = 0;
			}	
		}
		F('district_custom_cate',$district);
		return $district;
	}
	/**
	 * [district_cache 获取省市数据写入缓存]
	 */
	public function district_cache(){
		$district = array();
		$districtData = $this->field('id,parentid,categoryname')->order('category_order desc,id asc')->select();
		foreach ($districtData as $key => $val) {
			$district[$val['parentid']][$val['id']] = $val['categoryname'];
		}
		F('district',$district);
		$this->district_level();
		return $district;
	}
	/**
	 * [get_district_cache 读取省市数据]
	 */
	public function get_district_cache($pid=0){
		if(false === $district = F('district')){
			$district = $this->district_cache();
		}
		if($pid === 'all') return $district;
		return $district[intval($pid)];
	}
	/**
	 * [city_search_cache 地区搜索缓存]
	 */
	public function city_search_cache(){
		$city = $city_list = array();
		$cityData = $this->field('id,parentid')->order('parentid asc')->select();
		foreach ($cityData as $key => $val) {
			if(!$val['parentid']){
				$city_list[$val['id']] = $val['id'].'_0_0_0_0_0';
				$city[$val['id']] = array('tier'=>1,'spid'=>$val['id']);
			}else{
				switch ($city[$val['parentid']]['tier']) {
					case 1:
						$city_list[$val['id']] = $city[$val['parentid']]['spid'].'_'.$val['id'].'_0_0_0_0';
						$city[$val['id']] = array('tier'=>2,'spid'=>$city[$val['parentid']]['spid'].'_'.$val['id']);
						break;
					case 2:
						$city_list[$val['id']] = $city[$val['parentid']]['spid'].'_'.$val['id'].'_0_0_0';
						$city[$val['id']] = array('tier'=>3,'spid'=>$city[$val['parentid']]['spid'].'_'.$val['id']);
						break;
					case 3:
						$city_list[$val['id']] = $city[$val['parentid']]['spid'].'_'.$val['id'].'_0_0';
						$city[$val['id']] = array('tier'=>4,'spid'=>$city[$val['parentid']]['spid'].'_'.$val['id']);
						break;
					case 4:
						$city_list[$val['id']] = $city[$val['parentid']]['spid'].'_'.$val['id'].'_0';
						$city[$val['id']] = array('tier'=>5,'spid'=>$city[$val['parentid']]['spid'].'_'.$val['id']);
						break;
					case 5:
						$city_list[$val['id']] = $city[$val['parentid']]['spid'].'_'.$val['id'];
						break;
				}
			}
		}
		F('city_search_cate',$city_list);
		return $city_list;
	}
	/**
	 * [city_cate_cache 地区列表缓存]
	 */
	public function city_cate_cache(){
		$city['spell'] = F('city_cate_list_spell');
		$city['id'] = F('city_cate_list_id');
		if(false === $city['spell'] || false === $city['id']){
			$citySpell = $this->order('parentid desc,id asc')->getfield('spell,id,parentid,categoryname');
			foreach ($citySpell as $key => $val) {
				$cityId[$val['id']] = $val;
			}
			$city = array('spell'=>$citySpell,'id'=>$cityId);
			F('city_cate_list_spell',$citySpell);
			F('city_cate_list_id',$cityId);
		}
		return $city;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('district', NULL);
        F('city_search_cate',NULL);
        F('city_cate_list_spell',NULL);
		F('city_cate_list_id',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('district', NULL);
        F('city_search_cate',NULL);
        F('city_cate_list_spell',NULL);
		F('city_cate_list_id',NULL);
    }
    public function district_level(){
    	$level = $this->set_district_level(0);
    	D('Config')->where(array('name'=>'category_district_level'))->setfield('value',$level);
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