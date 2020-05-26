<?php 
namespace Common\Model;
use Think\Model;
class CategoryModel extends Model
{
	protected $_validate = array(
		array('c_name,c_alias,','identicalNull','',0,'callback'),
		array('c_name','1,30','{%category_length_error_c_name}',0,'length'),
		array('c_alias','1,30','{%category_length_error_c_alias}',0,'length'),
	);

	/*public function get_classify($alias){
		if (intval($alias)!=$alias) return false;
		$where['c_alias']=$alias;
		$get_classify = $this->where($where)->select(); 
		F('get_classify', $get_classify);
        return $get_classify;
	}*/
    public function category_wage(){
        $categoryData = $this->where(array('c_alias'=>'QS_wage'))->order('c_order desc,c_id')->getfield('c_id,c_name');
        F('category_wage_list',$categoryData);
        return $categoryData;
    }
	/**
     * 读取其它分类(全部)参数生成缓存文件
     */
    public function category_cache() {
        $category = array();
        $categoryData = $this->field('c_id,c_alias,c_name')->order('c_order desc,c_id')->select();
        foreach ($categoryData as $key=>$val){
        	$category[$val['c_alias']][$val['c_id']] = $val['c_name'];
        }
        foreach ($category['QS_wage'] as $key => $val) {
            if(preg_match_all('(\d+)',$val,$reg)){
                $reg = $reg[0];
                $min = $reg[0]%1000==0?(($reg[0]/1000).'K'):(round($reg[0]/1000,1).'K');
                $max = $reg[1]?($reg[1]%1000==0?(($reg[1]/1000).'K'):(round($reg[1]/1000,1).'K')):'';
                $max_k = $max ? '~'.$max :'以上';
                $category['QS_wage_k'][$key] = $min.$max_k.'/月';
                if(C('qscms_wage_unit') == 1){
                    $minwage = $min;
                    $maxwage = $max;
                }elseif(C('qscms_wage_unit') == 2){
                    if($reg[0]>=10000){
                        if($reg[0]%10000==0){
                            $minwage = ($reg[0]/10000).'万';
                        }else{
                            $minwage = round($reg[0]/10000,1);
                            $minwage = strpos($minwage,'.') ? str_replace('.','万',$minwage) : $minwage . '万';
                        }
                    }else{
                        if($reg[0]%1000==0){
                            $minwage = ($reg[0]/1000).'千';
                        }else{
                            $minwage = round($reg[0]/1000,1);
                            $minwage = strpos($minwage,'.') ? str_replace('.','千',$minwage) : $minwage . '千';
                        }
                    }
                    if($reg[1]>=10000){
                        if($reg[1]%10000==0){
                            $maxwage = ($reg[1]/10000).'万';
                        }else{
                            $maxwage = round($reg[1]/10000,1);
                            $maxwage = strpos($maxwage,'.') ? str_replace('.','万',$maxwage) : $maxwage . '万';
                        }
                    }elseif($reg[1]){
                        if($reg[1]%1000==0){
                            $maxwage = ($reg[1]/1000).'千';
                        }else{
                            $maxwage = round($reg[1]/1000,1);
                            $maxwage = strpos($maxwage,'.') ? str_replace('.','千',$maxwage) : $maxwage . '千';
                        }
                    }else{
                        $maxwage = '';
                    }
                }
                $maxwage = $maxwage ? '~'.$maxwage :'以上';
                $category['QS_wage'][$key] = $minwage.$maxwage.'/月';
            }else{
                $category['QS_wage'][$key] = $category['QS_wage_k'][$key] = $val;
            }
        }
        F('category', $category);
        return $category;
    }
    /**
     * [get_category_cache 读取缓存]
     * @param  string $type [单一分类名称]
     * @return array       [分类集]
     */
    public function get_category_cache($type='')
    {
        if(false === $category = F('category')){
            $category = $this->category_cache();
        }
        if($type) return $category[$type];
        return $category;
    }
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('get_classify', NULL);
        F('category', NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        F('get_classify', NULL);
        F('category', NULL);
    }
}
?>