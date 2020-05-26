<?php 
namespace Common\Model;
use Think\Model;
class CategoryJobsModel extends Model
{
	protected $_validate = array(
		array('categoryname','1,40','{%category_jobs_length_error_categoryname}',0,'length'),
		array('spell','','{%category_jobs_exist_error_spell}',2,'unique',3),
	);
	protected $_auto = array (
		array('category_order',0),
	);
	/**
	 * [custom description]
	 */
	public function custom_jobs_cache(){
		$jobs = array();
		$jobsData = $this->field('id,parentid,categoryname,spell')->order('category_order desc')->select();
		foreach ($jobsData as $key => $val) {
			$jobs[$val['parentid']][$val['id']] = $val;
		}
		F('jobs_custom_cate',$jobs);
		return $jobs;
	}
	/**
	 * [jobs_cache 获取职位数据写入缓存]
	 */
	public function jobs_cache(){
		$jobs = array();
		$jobsData = $this->field('id,parentid,categoryname')->order('category_order desc')->select();
		foreach ($jobsData as $key => $val) {
			$jobs[$val['parentid']][$val['id']] = $val['categoryname'];
		}
		F('jobs_cate',$jobs);
		return $jobs;
	}
	/**
	 * [get_jobs_cache 读取职位数据]
	 */
	public function get_jobs_cache($pid=0){
		if(false === $jobs = F('jobs_cate')){
			$jobs = $this->jobs_cache();
		}
		if($pid === 'all') return $jobs;
		return $jobs[intval($pid)];
	}
	/**
	 * [jobs_search_cache 职位搜索缓存]
	 */
	public function jobs_search_cache(){
		$jobs = $jobs_list = array();
		$t = range(0,2);
		$jobsData = $this->field('id,parentid')->order('parentid asc')->select();
		foreach ($jobsData as $key => $val) {
			if(!$val['parentid']){
				$jobs_list[$val['id']] = $val['id'].'_0_0';
				$jobs[$val['id']] = array('tier'=>1,'spid'=>$val['id']);
			}else{
				if($jobs[$val['parentid']]['tier'] == 1){
					$jobs_list[$val['id']] = $jobs[$val['parentid']]['spid'].'_'.$val['id'].'_0';
					$jobs[$val['id']] = array('tier'=>2,'spid'=>$jobs[$val['parentid']]['spid'].'_'.$val['id']);
				}elseif($jobs[$val['parentid']]['tier'] == 2){
					$jobs_list[$val['id']] = $jobs[$val['parentid']]['spid'].'_'.$val['id'];
				}
			}
		}
		F('jobs_search_cate',$jobs_list);
		return $jobs_list;
	}
	/**
	 * [jobs_cate_cache 职位列表缓存]
	 */
	public function jobs_cate_cache(){
		$jobsSpell = $this->order('parentid desc')->getfield('spell,id,parentid,categoryname');
		foreach ($jobsSpell as $key => $val) {
			$jobsId[$val['id']] = $val;
		}
		$jobs = array('spell'=>$jobsSpell,'id'=>$jobsId);
		F('jobs_cate_list',$jobs);
		return $jobs;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('jobs_cate', NULL);
        F('jobs_search_cate',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('jobs_cate', NULL);
        F('jobs_search_cate',NULL);
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