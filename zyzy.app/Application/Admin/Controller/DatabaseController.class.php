<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class DatabaseController extends BackendController {
    public $offset = '500'; //每次取数据条数
    public $dump_sql = '';
    public function _initialize() {
        parent::_initialize();
        $this->_database_mod = new \Think\Model;
    }
    public function index(){
        if (IS_POST || isset($_GET['dosubmit'])){
            if (isset($_GET['type']) && $_GET['type'] == 'url'){
                $sizelimit = isset($_GET['sizelimit']) && abs(intval($_GET['sizelimit'])) ? abs(intval
                    ($_GET['sizelimit'])) : $this->error('请输入每个分卷文件大小');
                $this->backup_name = isset($_GET['backup_name']) && trim($_GET['backup_name']) ?
                    trim($_GET['backup_name']) : $this->_make_backup_name();
                $vol = $this->_get_vol();
                $vol++;
            } else {
                $sizelimit = isset($_POST['sizelimit']) && abs(intval($_POST['sizelimit'])) ?
                    abs(intval($_POST['sizelimit'])) : $this->error('请输入每个分卷文件大小');

                $this->backup_name = $this->_make_backup_name();
                $backup_tables = isset($_POST['tables']) && $_POST['tables'] ? $_POST['tables'] :
                    $this->error('请选择要备份的数据表');

                if (is_dir(DATABASE_BACKUP_PATH . $this->backup_name))
                {
                    $this->error(L('backup_name') . L('exists'));
                }
                mkdir(DATABASE_BACKUP_PATH . $this->backup_name,0777,true);
                if (!is_file(DATABASE_BACKUP_PATH . $this->backup_name . '/tbl_queue.log')){
                    //写入队列
                    $this->_put_tbl_queue($backup_tables);
                }
                $vol = 1;
            }
            $tables = $this->_dump_queue($vol, $sizelimit * 1024);
            if ($tables === false) $this->error('加载队列文件错误');
            $this->_deal_result($tables, $vol, $sizelimit);
            exit();
        }
        $this->assign('sizelimit', 10*1024*1024 / 1024);
        $this->assign('list', $this->_database_mod->db()->getTables()); //显示所有数据表
        $this->display();
    }
    /**
     * 备份数据列表
     */
    public function restore(){
        $this->assign('list', $this->_get_backups());
        $this->display();
    }
    /**
     * 删除备份
     */
    public function del(){
        $name = I('request.name','','trim');
        !$name && $this->error('请选择要删除的备份文件');
        !is_array($name) && $name = array($name);
        foreach ($name as $key => $val) {
            rmdirs(DATABASE_BACKUP_PATH.$val,true);
        }
        $this->success('删除备份文件成功！');
    }
    /**
     * 导入备份
     */
    public function import(){
        $backup_name = I('request.name','','trim');
        !$backup_name && $this->error('请选择要恢复的备份文件！');
        $vol = I('request.vol',1,'intval');
        $this->backup_name = $backup_name;
        //获得所有分卷
        $backups = $this->_get_vols($this->backup_name);
        $backup = isset($backups[$vol]) && $backups[$vol] ? $backups[$vol] : $this->
            error('没有找到备份文件');
        if($backup['74cms_ver'] != C('QSCMS_VERSION')){
            $this->error('当前程序与备份程序版本不一致');
        }
        //开始导入卷
        if ($this->_import_vol($backup['file'])){
            if ($vol < count($backups)){
                $vol++;
                $link = U('admin/Database/import',array('vol'=>$vol,'name'=>urlencode($this->backup_name)));
                $this->success(sprintf("还原分卷 (%d) 成功，系统将自动还原下一个分卷...", $vol - 1), $link,1,'系统将自动继续...');
            } else {
                $this->success('数据恢复成功', U('admin/Database/restore'));
            }
        }
    }
    /**
     * 优化数据表列表
     */
    public function optimize(){
        $list = $this->_get_optimize_list();
        $this->assign('list',$list);
        $this->display();
    }
    public function doOptimize(){
        $tableArr = I('post.tables');
        if(empty($tableArr)){
            $this->error('请选择项目！');
        }
        $r = $this->optimize_table($tableArr);
        if($r){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    } 
    protected function _import_vol($sql_file_name){
        $sql_file = DATABASE_BACKUP_PATH . $this->backup_name . '/' . $sql_file_name;
        $sql_str = file($sql_file);
        $sql_str = str_replace("\r", '', implode('', array_splice($sql_str,6)));
        $ret = explode(";;\n", $sql_str);
        $ret_count = count($ret);
        for ($i = 0; $i < $ret_count; $i++){
            $ret[$i] = trim($ret[$i], " \r\n;;"); //剔除多余信息
            if (!empty($ret[$i])){
                $this->_database_mod->execute($ret[$i]);
            }
        }
        return true;
    }
    /**
     * 生成备份文件夹名称
     */
    protected function _make_backup_name(){
        $backup_path = DATABASE_BACKUP_PATH;
        $today = date('Ymd_', time());
        $today_backup = array(); //保存今天已经备份过的
        if (is_dir($backup_path))
        {
            if ($handle = opendir($backup_path))
            {
                while (($file = readdir($handle)) !== false)
                {
                    if ($file{0} != '.' && filetype($backup_path . $file) == 'dir')
                    {
                        if (strpos($file, $today) === 0)
                        {
                            $no = intval(str_replace($today, '', $file)); //当天的编号
                            if ($no)
                            {
                                $today_backup[] = $no;
                            }
                        }
                    }
                }
            }
        }
        if ($today_backup)
        {
            $today .= max($today_backup) + 1;
        } else
        {
            $today .= '1';
        }
        return $today;
    }
    /**
     * 需要备份的数据表写入队列
     */
    protected function _put_tbl_queue($tables){
        return file_put_contents(DATABASE_BACKUP_PATH . $this->backup_name .
            '/tbl_queue.log', "<?php return " . var_export($tables, true) . ";\n?>");
    }
    /**
     * 获取需要处理的数据表队列
     */
    protected function _get_tbl_queue(){
        $tbl_queue_file = DATABASE_BACKUP_PATH . $this->backup_name . '/tbl_queue.log';
        if (!is_file($tbl_queue_file)){
            return false;
        } else{
            return include ($tbl_queue_file);
        }
    }
    /**
     * 删除队列文件
     */
    protected function _drop_tbl_queue(){
        $tbl_queue_file = DATABASE_BACKUP_PATH . $this->backup_name . '/tbl_queue.log';
        return @unlink($tbl_queue_file);
    }
    /**
     * 写入分卷记录
     */
    protected function _set_vol($vol){
        $log_file = DATABASE_BACKUP_PATH . $this->backup_name . '/vol.log';
        return file_put_contents($log_file, $vol);
    }
    /**
     * 获取上一次操作分卷记录
     */
    protected function _get_vol(){
        $log_file = DATABASE_BACKUP_PATH . $this->backup_name . '/vol.log';
        if (!is_file($log_file)) return 0;
        $content = file_get_contents($log_file);
        return is_numeric($content) ? intval($content) : false;
    }
    /**
     * 删除分卷记录文件
     */
    protected function _drop_vol(){
        $log_file = DATABASE_BACKUP_PATH . $this->backup_name . '/vol.log';
        return @unlink($log_file);
    }
    /**
     * 保存导出的sql
     */
    protected function _sava_sql($vol){
        return file_put_contents(DATABASE_BACKUP_PATH . $this->backup_name .
            '/' . $this->backup_name . '_' . $vol . '.sql', $this->dump_sql);
    }
    //获取文件目录列表,该方法返回数组
    protected function getDir($dir,$type = false) {
        if (false != ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != "..") {
                    if(!$type && false === strpos($file,".") && filetype(DATABASE_BACKUP_PATH . $file) == 'dir') $dirArray[$file]=1;
                    if($type && false !== strpos($file,".sql")) $dirArray[$file]=1;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
    /**
     * 获得备份文件夹下的sql文件
     */
    protected function _get_vols($backup_name){
        $vol_path = DATABASE_BACKUP_PATH . $backup_name . '/';
        $vols = array(); //所有的卷
        $bytes = 0;
        $file = $this->getDir($vol_path,true);
        foreach ($file as $key => $value) {
            $file_info = pathinfo($vol_path . $key);
            if ($file_info['extension'] == 'sql'){
                $vol = $this->_get_head($vol_path . $key);
                $vol['file'] = $key;
                $bytes += filesize($vol_path . $key);
                $vol['size'] = ceil(10 * filesize($vol_path . $key) / 1024) / 10;
                $vol['total_size'] = ceil(10 * $bytes / 1024) / 10;
                $vols[$vol['vol']] = $vol;
            }
        }
        ksort($vols);
        return $vols;
    }
    /**
     * 获得备份列表
     */
    protected function _get_backups(){
        $file = $this->getDir(DATABASE_BACKUP_PATH);
        ksort($file);
        foreach ($file as $key => $val) {
            $backup['name'] = $key;
            $backup['date'] = filemtime(DATABASE_BACKUP_PATH . $key);
            $backup['date_str'] = date('Y-m-d H:i:s', $backup['date']);
            $temp = $this->_get_dir_size(DATABASE_BACKUP_PATH . $key);
            $backup = array_merge($backup,$temp);
            $backups[] = $backup;
        }
        return $backups;
    }
    protected function _deal_result($tables, $vol, $sizelimit){
        $this->_sava_sql($vol);
        if (empty($tables)){
            //备份完毕
            $this->_drop_tbl_queue();
            $vol != 1 && $this->_drop_vol(); //只有一卷时不需删除
            $this->success('数据库备份成功', U('restore'));
        } else {
            //开始下一卷
            $this->_set_vol($vol); //设置分卷记录
            $link = U('admin/Database/index',array('dosubmit'=>1,'type'=>'url','backup_name'=>$this->backup_name,'sizelimit'=>$sizelimit));
            $this->success(sprintf("文件%d_%d.sql 成功备份。程序将自动继续...", $this->backup_name,$vol), $link,1,'系统将自动继续...');
        }
    }
    protected function _dump_queue($vol, $sizelimit){
        $queue_tables = $this->_get_tbl_queue();
        if (!$queue_tables) return false;
        $this->dump_sql = $this->_make_head($vol);
        foreach ($queue_tables as $table => $pos){
            //获取表结构
            if ($pos == '-1'){
                $table_df = $this->_get_table_df($table);
                if (strlen($this->dump_sql) + strlen($table_df) > $sizelimit){
                    break;
                } else {
                    $this->dump_sql .= $table_df;
                    $pos = 0;
                }
            }
            //获取表数据
            $post_pos = $this->_get_table_data($table, $pos, $sizelimit);
            if ($post_pos == -1){
                unset($queue_tables[$table]); //此表已经完全导出
            } else {
                //此表未完成，放到下一个分卷
                $queue_tables[$table] = $post_pos;
                break;
            }
        }
        $this->_put_tbl_queue($queue_tables);
        return $queue_tables;
    }
    /**
     * 获取数据表结构语句
     *
     * @param string $table 表名
     */
    protected function _get_table_df($table){
        $table_df = "DROP TABLE IF EXISTS `$table`;;\n";
        $tmp_sql = $this->_database_mod->query("SHOW CREATE TABLE `$table` ");
        $tmp_sql = $tmp_sql['0']['create table'];
        $tmp_sql = substr($tmp_sql, 0, strrpos($tmp_sql, ")") + 1); //去除行尾定义。
        $tmp_sql = str_replace("\n", "\r\n", $tmp_sql);
        $table_df .= $tmp_sql . " COLLATE='utf8_general_ci' ENGINE=MyISAM;;\r\n";
        return $table_df;
    }
    /**
     * 获取数据表数据
     */
    protected function _get_table_data($table, $pos, $sizelimit){
        $post_pos = $pos;
        $total = $this->_database_mod->table($table)->count(); //数据总数
        if ($total == 0 || $pos >= $total) return - 1;
        $cycle_time = ceil(($total - $pos) / $this->offset); //每次取offset条数。获得需要取的次数
        for ($i = 0; $i < $cycle_time; $i++){
            $data = $this->_database_mod->query("SELECT * FROM $table LIMIT " . ($this->
                offset * $i + $pos) . ', ' . $this->offset);
            $data_count = count($data);
            $fields = array_keys($data[0]);
            $start_sql = "INSERT INTO $table ( `" . implode("`, `", $fields) . "` ) VALUES ";
            //循环将数据写入
            for ($j = 0; $j < $data_count; $j++){
                $record = array_map(array($this, 'dump_escape_string'), $data[$j]); //过滤非法字符
                $tmp_dump_sql = $start_sql . " (" . $this->_implode_insert_values($record) . ");;\r\n";
                if (strlen($this->dump_sql) + strlen($tmp_dump_sql) > $sizelimit - 32){
                    return $post_pos;
                } else {
                    $this->dump_sql .= $tmp_dump_sql;
                    $post_pos++;
                }
            }
        }
        return - 1;
    }
    protected function dump_escape_string($str){
        return addslashes($str);
        //return $this->_database_mod->escape_string($str);
    }
    /**
     * 备份文件头部声明信息
     */
    protected function _make_head($vol){
        $date = date('Y-m-d H:i:s', time());
        $db_version = $this->_database_mod->query("select version() as ver");
        $db_version = $db_version[0]['ver'];
        $version = C('QSCMS_VERSION');
        $head = "-- 74CMS SQL Dump Program\r\n" . "-- \r\n" . "-- DATE : " . $date . "\r\n"."-- 74CMS VERSION : ".$version."\r\n"."-- Mysql VERSION : ".$db_version."\r\n" ."-- Vol : " . $vol . "\r\n";
        return $head;
    }
    /**
     * 对 MYSQL INSERT INTO 语句的values部分内容进行字符串连接
     *
     * @param array $values
     * @return string
     */
    protected function _implode_insert_values($values){
        $str = '';
        $values = array_values($values);
        foreach ($values as $k => $v)
        {
            $v = ($v === null) ? 'null' : "'" . $v . "'";
            $str = ($k == 0) ? $str . $v : $str . ',' . $v;
        }
        return $str;
    }
    /**
     * 将G M K转换为字节
     *
     * @param string $val
     * @return int
     */
    protected function _return_bytes($val){
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last)
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
    protected function _get_dir_size($dir){
        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle))){
            if ($FolderOrFile != "." && $FolderOrFile != ".."){
                if (is_dir("$dir/$FolderOrFile")){
                    $sizeResult += $this->_get_dir_size("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                    !$file && $file = "$dir/$FolderOrFile";
                }
            }
        }
        closedir($handle);
        $file_info = $this->_get_head($file);
        $file_info['total_size'] = round($sizeResult/1024/1024,2);
        return $file_info;
    }
    /**
     * 获得头文件信息
     */
    protected function _get_head($path){
        $fp = fopen($path, 'rb');
        $str = fread($fp, 150);
        fclose($fp);
        $arr = explode("\n", $str);
        foreach ($arr as $val){
            $pos = strpos($val, ':');
            if ($pos > 0){
                $type = trim(substr($val, 0, $pos), "-\n\r\t ");
                $value = trim(substr($val, $pos + 1), "/\n\r\t ");
                if ($type == '74CMS VERSION'){
                    $file_info['74cms_ver'] = $value;
                }elseif ($type == 'Mysql VERSION'){
                    $file_info['mysql_ver'] = substr($value,0,3);
                }elseif ($type == 'Create time'){
                    $file_info['add_time'] = $value;
                }elseif ($type == 'DATE'){
                    $file_info['date'] = $value;
                } elseif ($type == 'Vol'){
                    $file_info['vol'] = $value;
                }
            }
        }
        return $file_info;
    }
    /**
     * 获取需要优化的表
     */
    protected function _get_optimize_list(){
        $row_arr = array();
        $result = $this->_database_mod->query("SHOW TABLE STATUS FROM `".C('DB_NAME')."` WHERE Data_free>0");
        foreach ($result as $key => $value) {
            if ($value['data_free']=="0") $value['data_free']="-";
            if ($value['data_free']>1 && $value['data_free']<1024) {
                $value['data_free']=$value['data_free']." byte";
            } elseif($value['data_free']>1024 && $value['data_free']<1048576) {
                $value['data_free']=number_format(($value['data_free']/1024),1)." KB";
            } elseif($value['data_free']>1048576) {
                $value['data_free']=number_format(($value['data_free']/1024/1024),1)." MB";
            }
            $value['data_length']=$value['data_length']+$value['index_length'];
            //--
            if ($value['data_length']=="0") {
                $value['data_length']="-";
            } elseif($value['data_length']<1048576) {
                $value['data_length']=number_format(($value['data_length']/1024),1)." KB";
            } elseif($value['data_length']>1048576) {
                $value['data_length']=number_format(($value['data_length']/1024/1024),1)." MB";
            }
            $row_arr[] = $value;
        }
        return $row_arr;
    }
    protected function optimize_table($table){
        if(!$table)return false;
        !is_array($table) && $table = array($table);
        $sqlstr=implode(",",$table);
        if ($this->_database_mod->execute("OPTIMIZE TABLE $sqlstr")) {   
            return true;
        } else {
            return fase;
        }
    }
}
?>
