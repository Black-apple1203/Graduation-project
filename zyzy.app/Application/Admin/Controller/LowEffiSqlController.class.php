<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class LowEffiSqlController extends BackendController{
	public function _initialize() {
        parent::_initialize();
    }
    /**
     * [index 应用列表]
     */
    public function index(){
        $logs = $this->getDir(SQL_LOG_PATH);
        $length = 10;
        $pager = pager(count($logs), $length);
        $page = $pager->fshow();
        $start = $pager->firstRow;
        $list = array();
        $count = 0;
        foreach ($logs as $key => $value) {
            if($key>=$start){
                $count++;
                $list[] = $value;
            }
            if($count>=$length){
                break;
            }
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }
    public function detail(){
        $filename = I('get.filename','','trim');
        if(!file_exists(SQL_LOG_PATH.$filename)){
            $this->error('日志文件不存在！');
        }
        $content = file_get_contents(SQL_LOG_PATH.$filename);
        

        $content_arr = explode("[qscms]", $content);
        $length = 20;
        $pager = pager(count($content_arr), $length);
        $page = $pager->fshow();
        $start = $pager->firstRow;
        $list = array();
        $count = 0;

        $content_arr = array_slice($content_arr,$start,$length);
        foreach ($content_arr as $key => $val) {
            $str = str_replace(PHP_EOL, '',$val);
            if(preg_match("/SQL: (.*?) \[/",$str,$s)) $data['sql'] = $s[1];
            if(preg_match("/\/(.*)SQL:/",$str,$s)) $data['url'] = $s[1];
            if(preg_match("/\[RunTime:(.*?)\]/",$str,$s)) $data['runtime'] = $s[1];
            if(preg_match("/\[IP:(.*?)\]/",$str,$s)) $data['ip'] = $s[1];
            if(preg_match("/\[AddTime:(.*?)\]/",$str,$s)) $data['addtime'] = $s[1];
            $list[] = $data;
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }
    //获取文件目录列表,该方法返回数组
    protected function getDir($dir) {
        if (false != ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if (false !== strpos($file,".log")) {
                    $dirArray[]=$file;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
}
?>