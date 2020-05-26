<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://74cms.com All rights reserved.
// +----------------------------------------------------------------------
/**
 * 自动执行任务
 * @category   Extend
 * @package  Extend
 * @subpackage  Behavior
 */
namespace Common\Behavior;
class CronRunBehavior{
    protected $options   =  array(
        'CRON_ON'       =>  false,
        'CRON_MAX_TIME' =>  60, // 单个任务最大执行时间
    );
    public function run(&$params) {
        if(!C('CRON_ON')) return;
        // 锁定自动执行
        $lockfile	 =	 RUNTIME_PATH.'cron.lock';
        if(is_writable($lockfile) && filemtime($lockfile) > $_SERVER['REQUEST_TIME'] - C('CRON_MAX_TIME')) {
        	clearstatcache();
        	return ;
        } else {
            touch($lockfile);//设置文件访问和修改时间,文件不存在则会被创建
        }
        set_time_limit(1000);
        ignore_user_abort(true);
        if(is_file(DATA_PATH.'cron_list.php')) {
            $crons = include DATA_PATH.'cron_list.php';
        }else{
            $crons = D('Crons')->cron_cache();
        }
        if(isset($crons) && is_array($crons)) {
            $update	 =	 false;
            $log	=	array();
            foreach ($crons as $key=>$cron){
                if(empty($cron['start']) || $_SERVER['REQUEST_TIME']>=$cron['start']) {
                    // 到达时间 执行cron文件
                    G('cronStart');
                    require_once COMMON_PATH.'Cron/'.$cron['name'].'.class.php';
                    $class = new $cron['name'];
                    $result = $class->run();
                    $_useTime	 =	 G('cronStart','cronEnd', 6);
                    // 更新cron记录
                    $cron['start']	=	$this->setCronStarttime($cron);
                    $crons[$key]	=	$cron;
                    $log[] = "Cron:{$cron['name']} Runat ".date('Y-m-d H:i:s')." Use $_useTime s $result\n";
                    $update	 =	 true;
                }
            }
            if($update) {
                // 记录Cron执行日志
                \Think\Log::write(implode('',$log),'INFO','',CRON_LOG_PATH.date('y_m_d').'.log');
                // 更新cron文件
                $content  = "<?php\nreturn ".var_export($crons,true).";\n?>";
                file_put_contents(DATA_PATH.'cron_list.php',$content);
            }
        }
        // 解除锁定
        unlink($lockfile);
        return ;
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