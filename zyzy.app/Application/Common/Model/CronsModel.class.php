<?php 
namespace Common\Model;
use Think\Model;
class CronsModel extends Model
{
	protected $_validate = array(
		array('name,filename','identicalNull','',1,'callback'),
		array('name,filename','identicalLength_60','',0,'callback'),
	);

	protected $_auto = array (
		array('available',1),
		array('admin_set',0),
	);
	/**
	 * 
	 * 验证指定字段长度
	 * @param array $data 被验证字段
	 * 
	 * @return Boolean/string 验证结果(true:合法,false:不合法,字符串：不合法提示语)
	 */
	protected function identicalLength_60($data){
		foreach($data as $key=>$val){
			if(strlen($val)>=60) return 'crons_length_error_'.$key;
		}
		return true;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('cron_list', NULL);
    }
	/**
	 * 生成计划任务列表缓存
	 */
	public function cron_cache(){
		$cron = array();
        $cronData = $this->where('available=1')->field('cronid,filename,weekday,day,hour,minute')->select();
        foreach ($cronData as $key=>$val){
        	$cron[$val['cronid']] = $val;
        	$cron[$val['cronid']]['name'] = $val['filename'];
        	$cron[$val['cronid']]['start'] = $this->setCronStarttime($val);
        }
        F('cron_list', $cron);
        return $cron;
	}
	public function get_cron_cache()
    {
        if(false === $cron = F('cron_list')){
            $cron = $this->cron_cache();
        }
        return $cron;
    }
	/**
	 * 更新任务时间表
	 */
	protected function setCronStarttime($crons){
		if ($crons['weekday']>=0)
		{
		$weekday=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
		$nextrun=strtotime("Next ".$weekday[$crons['weekday']]);
		}
		elseif ($crons['day']>0)
		{
		$nextrun=strtotime('+1 months'); 
		$nextrun=mktime(0,0,0,date("m",$nextrun),$crons['day'],date("Y",$nextrun));
		}
		else
		{
		$nextrun=time();
		}
		if ($crons['hour']>=0)
		{
		$nextrun=strtotime('+1 days',$nextrun); 
		$nextrun=mktime($crons['hour'],0,0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
		}
		if (stripos($crons['minute'],'/')!==false)
		{
			$minute_arr = explode("/", $crons['minute']);
			$nextrun=$nextrun + 60 * intval($minute_arr[1]);
			$nextrun=mktime(date("H",$nextrun),date("i",$nextrun),0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
		}
		else if(intval($crons['minute'])>0)
		{
			$nextrun=strtotime('+1 hours',$nextrun); 
			$nextrun=mktime(date("H",$nextrun),$crons['minute'],0,date("m",$nextrun),date("d",$nextrun),date("Y",$nextrun));
		}
		return $nextrun;
	}
}
?>