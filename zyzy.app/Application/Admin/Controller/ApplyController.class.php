<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ApplyController extends BackendController{
    /**
     * [$_rollback 不能回滚的应用版本号]
     */
	protected $_rollback = array(
        'Home'         => array('4.2.56','4.2.59'),
        'Mobile'       => array('4.2.49'),
        'Weixinapp'    => array('4.2.9'),
        'Remind'       => array('4.1.4'),
        'Allowance'    => array('4.2.8'),
        'Crm'          => array('4.2.2'),
        'Sincerity'    => array('4.2.2'),
    );
    /**
     * [$_setup 当前版本基础版对应用安装的版本号判断]
     */
    protected $_setup = array(
        '4.2.59'        => array(
            'Analyze'   => '4.1.2',
            'Sincerity' => '4.2.2',
            'Report'    => '4.2.0',
        ),
    );
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * [index 应用列表]
     */
    public function index(){
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        $module = $this->getDir(APP_PATH);
        foreach ($module as $key => $val) {
            $module[$key] = array(
                'module' => $key,
                'version' => F($key.'/Install/version','',APP_PATH),
                'ico' => APP_PATH.$key.'/Install/module_ico.jpg',
                'is_setup' => isset($apply[$key]),
                'setup_time' => $apply[$key]['setup_time'],
                'enable_rollback'=>$this->dir_is_empty(ONLINE_ROLLBACK_PATH.'/versions/'.$key.'/')?'0':'1'
            );
        }
        $base = array(
            'module' => 'Home',
            'version' => F('Home/Install/version','',APP_PATH),
            'ico' => APP_PATH.'Home/Install/module_ico.jpg',
            'is_setup' => true,
            'setup_time' => $apply['Home']['setup_time'],
            'enable_rollback'=>$this->dir_is_empty(ONLINE_ROLLBACK_PATH.'/versions/Home/')?'0':'1'
        );
        $model = new \Think\Model;
        $info = $model->query("SHOW COLUMNS FROM `".C('DB_PREFIX')."company_profile` like 'districts'");
        $module_name = array_keys($module);
        $module_name[] = 'Home';
        $this->assign('module_name',implode(',',$module_name));
        $this->assign('base',$base);
        $this->assign('list',$module);
        $this->assign('is_shift',count($info)>0?1:0);
        $this->display();
    }
    /**
     * [setup 安装应用]
     */
    public function setup(){
        $mod = I('request.mod','','trim');
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        if(!IS_POST){
            !$mod && $this->error('请选择要安装的应用！');
            $mod == 'Home' && $this->error('骑士人才系统核心模块已安装！');
            if(false === $module = F($mod.'/Install/version','',APP_PATH)) $this->error('请正确拷贝安装文件！');
            $module = array(
                'module' => $mod,
                'version' => F($mod.'/Install/version','',APP_PATH),
                'ico' => APP_PATH.$mod.'/Install/module_ico.jpg',
                'is_setup' => isset($apply[$mod]),
                'setup_time' => $apply['Home']['setup_time']
            );
            $this->assign('apply',$apply[$mod]);
            $this->assign('module',$module);
            $this->display();
        }else{
            //开始安装
            if(!$mod){
                $this->_show_process('安装应用失败！');
                $this->_show_process('请正确选择应用！','parent.install_failure();');
                return false;
            }
            if($mod == 'Home'){
                $this->_show_process('安装应用失败！');
                $this->_show_process('骑士人才系统核心模块已安装！','parent.install_failure();');
                return false;
            }
            $module = F($mod.'/Install/install_version','',APP_PATH);
            if(!$module && false === $module = F($mod.'/Install/version','',APP_PATH)){
                $this->_show_process('安装失败！');
                $this->_show_process('请正确拷贝安装文件至 '.APP_NAME.' 目录！','parent.install_failure();');
                return false;
            }
            //检测版本号是否可以安装
            if($modV = $this->_setup[$apply['Home']['version']][$mod]){
                $modV =  explode('.', $modV);
                $v = $modV[0] * 1000000 + $modV[1] * 10000 + $modV[2];
                $modV1 =  explode('.', $module['version']);
                $v1 = $modV1[0] * 1000000 + $modV1[1] * 10000 + $modV1[2];
                if($v >= $v1){
                    $this->_show_process('安装失败！');
                    $this->_show_process('请获取最新版【'.$module['module_name'].'】安装包，当前版本【'.$module['version'].'】无法正常使用！','parent.install_failure();');
                    return false; 
                }
            }
            $this->_show_process('安装应用程序开始...');
            if($module['is_exe'] && $is_exe = is_file(APP_PATH .$mod.'/Install/'.$mod.'Setup.class.php')){
                //检测可执行文件是否存在
                include APP_PATH .$mod.'/Install/'.$mod.'Setup.class.php';
                $class = $mod . 'Setup';
                $exe = new $class();
                if(false !== $exe->setup_init()){
                    $this->_show_process('应用程序安装成功！');
                }else{
                    $this->_show_process('应用程序安装失败！');
                    $this->_show_process($exe->getError(),'parent.install_failure();');
                    return false;
                }
            }
            $charset = C('DEFAULT_CHARSET');
            header('Content-type:text/html;charset=' . $charset);
            $charset = C('DB_CHARSET');
            $conn = mysql_connect(C('DB_HOST') . ':' . C('DB_PORT'), C('DB_USER'), C('DB_PWD'));
            $version = mysql_get_server_info();
            if ($version > '4.1') {
                if ($charset != 'latin1') {
                    mysql_query("SET character_set_connection={$charset}, character_set_results={$charset}, character_set_client=binary", $conn);
                }
                if ($version > '5.0.1') {
                    mysql_query("SET sql_mode=''", $conn);
                }
            }
            $selected_db = mysql_select_db(C('DB_NAME'), $conn);
            if($apply[$mod] || is_file(QSCMS_DATA_PATH.'apply/'.$mod.'_install.lock')){
                $this->_show_process('删除系统当前 “'.$module['module_name'].'” 文件开始...');
                @unlink(QSCMS_DATA_PATH.'apply/'.$mod.'_install.lock');
                $this->_show_process('删除 “'.$module['module_name'].'” 标记成功！');
                if($module['is_delete_data'] && is_file(APP_PATH . $mod . '/Install/sqldata/deletedata.sql')){
                    $this->_show_process('删除数据表开始...');
                    $sqls = $this->_get_sql(APP_PATH . $mod . '/Install/sqldata/deletedata.sql');
                    foreach ($sqls as $sql) {
                        //替换前缀
                        $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                        //获得表名
                        $run = mysql_query($sql, $conn);
                        if (substr($sql, 0, 10) == 'DROP TABLE') {
                            $table_name = C('DB_PREFIX') . preg_replace("/DROP TABLE IF EXISTS `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                            $reg = $run ? '成功！' : '失败！';
                            $this->_show_process(sprintf('删除数据表 %s '.$reg, $table_name));
                        }elseif(substr($sql, 0, 11) == 'ALTER TABLE'){
                            $table_name = C('DB_PREFIX') . preg_replace("/ALTER TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                            $reg = $run ? '成功！' : '失败！';
                            $this->_show_process(sprintf('删除数据表 %s 字段'.$reg, $table_name));
                        }
                    }
                }
                $this->_show_process('删除数据开始...');
                D('Config')->where(array('type'=>$mod))->delete();
                $this->_show_process('删除数据成功！');
                if($module['is_exe'] && $is_exe){
                    //检测可执行文件是否存在
                    $this->_show_process('卸载应用程序开始...');
                    $class = $mod . 'Setup';
                    $exe->unload();
                    $this->_show_process('应用程序卸载成功！');
                }
            }
            if($module['is_create_table'] && is_file(APP_PATH . $mod . '/Install/sqldata/create_table.sql')){
                $this->_show_process('创建数据表开始...');
                $sqls = $this->_get_sql(APP_PATH . $mod . '/Install/sqldata/create_table.sql');
                foreach ($sqls as $sql) {
                    //替换前缀
                    $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                    //获得表名
                    $run = mysql_query($sql, $conn);
                    if (substr($sql, 0, 12) == 'CREATE TABLE') {
                        $table_name = C('DB_PREFIX') . preg_replace("/CREATE TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                        $reg = $run ? '成功！' : '失败！';
                        $this->_show_process(sprintf('创建数据表 %s '.$reg, $table_name));
                    }elseif(strpos($sql,'ADD')){
                        $table_name = C('DB_PREFIX') . preg_replace("/ALTER TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                        $reg = $run ? '成功！' : '失败！';
                        $this->_show_process(sprintf('数据表 %s 添加字段'.$reg, $table_name));
                    }
                }
            }
            //开始导入数据
            $this->_show_process('添加初始数据开始...');
            if($module['is_insert_data'] && APP_PATH . $mod . '/Install/sqldata/initdata.sql'){
                $sqls = $this->_get_sql(APP_PATH . $mod . '/Install/sqldata/initdata.sql');
            }
            $module['versioning'] = is_file(APP_PATH.$mod.'/Install/upgrade_log.log') ? file_get_contents(APP_PATH.$mod.'/Install/upgrade_log.log') : '';
            $sqls[] = "INSERT INTO `" . C('DB_PREFIX') . "apply` (`alias`, `module_name`, `version`, `is_create_table`, `is_insert_data`, `is_exe`, `is_delete_data`, `update_time`, `setup_time`,`explain`,`versioning`, `status`) VALUES " . "('" . $module['module'] . "', '" . $module['module_name'] . "', '" . $module['version'] . "', '" . $module['is_create_table'] . "', '" . $module['is_insert_data'] . "', '" . $module['is_exe'] . "', '" . $module['is_delete_data'] . "', '" . $module['update_time'] . "', " . time() . ",'" . $module['explain']. "', '".$module['versioning']."', 1);";
            foreach ($sqls as $key=>$sql) {
                //替换前缀
                $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                //获得表名
                if (substr($sql, 0, 11) == 'INSERT INTO') {
                    $table_name = preg_replace("/INSERT INTO `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                    if($table_name == 'menu'){
                        if(false === $this->_menu($sql)){
                            $this->_show_process(sprintf('数据表 %s 初始化数据添加失败！',$table_name),'parent.install_failure();');
                            return false;
                        }
                    }else{
                        $run = mysql_query($sql, $conn);
                    }
                    $this->_show_process(sprintf('数据表 %s 初始化数据添加成功！',$table_name));
                }
            }
            $this->_show_process('初始数据添加成功！');
            if($module['is_exe'] && $is_exe){
                //检测可执行文件是否存在
                $this->_show_process('安装应用程序开始...');
                $class = $mod . 'Setup';
                $exe->setup();
                $this->_show_process('应用程序安装成功！');
            }
            copy(APP_PATH.$mod.'/Install/install_version.php',APP_PATH.$mod.'/Install/version.php');
            //安装完毕
            touch(QSCMS_DATA_PATH.'apply/'.$mod.'_install.lock');
            $this->_show_process('恭喜您，您的 '.$module['module_name'].' 已经安装完成！', 'parent.install_successed();');
            D('Apply')->update_cache();
            D('Menu')->update_cache();
            D('AdminAuthGroup')->menu_group_init();
            return false;
        }
    }
    /**
     * [unload 卸载应用]
     */
    public function unload(){
        $mod = I('request.mod','','trim');
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        if(!IS_POST){
            !$mod && $this->error('请选择要卸载的应用！');
            $mod == 'Home' && $this->error('骑士人才系统核心模块无法卸载！');
            $apply[$mod]['ico'] = APP_PATH.$mod.'/Install/module_ico.jpg';
            $this->assign('apply',$apply[$mod]);
            $this->display();
        }else{
            //卸载应用开始
            if(!$mod){
                $this->_show_process('卸载应用失败！');
                $this->_show_process('请正确选择应用！','parent.install_failure();');
                return false;
            }
            if($mod == 'Home'){
                $this->_show_process('卸载应用失败！');
                $this->_show_process('骑士人才系统核心模块无法卸载！','parent.install_failure();');
                return false;
            }
            $apply = $apply[$mod];
            if($apply || is_file(QSCMS_DATA_PATH.'apply/'.$mod.'_install.lock')){
                $this->_show_process('卸载应用程序开始...');
                if($apply['is_exe'] && $is_exe = is_file(APP_PATH .$mod.'/Install/'.$mod.'Setup.class.php')){
                    //检测可执行文件是否存在
                    include APP_PATH .$mod.'/Install/'.$mod.'Setup.class.php';
                    $class = $mod . 'Setup';
                    $exe = new $class();
                    if(false === $exe->unload_init()){
                        $this->_show_process('卸载应用失败！');
                        $this->_show_process($exe->getError(),'parent.install_failure();');
                        return false;
                    }
                }
                $charset = C('DEFAULT_CHARSET');
                header('Content-type:text/html;charset=' . $charset);
                $charset = C('DB_CHARSET');
                $conn = mysql_connect(C('DB_HOST') . ':' . C('DB_PORT'), C('DB_USER'), C('DB_PWD'));
                $version = mysql_get_server_info();
                if ($version > '4.1') {
                    if ($charset != 'latin1') {
                        mysql_query("SET character_set_connection={$charset}, character_set_results={$charset}, character_set_client=binary", $conn);
                    }
                    if ($version > '5.0.1') {
                        mysql_query("SET sql_mode=''", $conn);
                    }
                }
                $selected_db = mysql_select_db(C('DB_NAME'), $conn);
                $this->_show_process('删除系统当前 “'.$apply['module_name'].'” 文件开始...');
                @unlink(QSCMS_DATA_PATH.'apply/'.$mod.'_install.lock');
                $this->_show_process('删除 “'.$apply['module_name'].'” 标记成功！');
                if($apply['is_delete_data'] && is_file(APP_PATH . $mod . '/Install/sqldata/deletedata.sql')){
                    $this->_show_process('删除数据表开始...');
                    $sqls = $this->_get_sql(APP_PATH . $mod . '/Install/sqldata/deletedata.sql');
                    foreach ($sqls as $sql) {
                        //替换前缀
                        $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                        //获得表名
                        $run = mysql_query($sql, $conn);
                        if (substr($sql, 0, 10) == 'DROP TABLE') {
                            $table_name = C('DB_PREFIX') . preg_replace("/DROP TABLE IF EXISTS `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                            $reg = $run ? '成功！' : '失败！';
                            $this->_show_process(sprintf('删除数据表 %s '.$reg, $table_name));
                        }elseif(substr($sql, 0, 11) == 'ALTER TABLE'){
                            $table_name = C('DB_PREFIX') . preg_replace("/ALTER TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                            $reg = $run ? '成功！' : '失败！';
                            $this->_show_process(sprintf('删除数据表 %s 字段'.$reg, $table_name));
                        }
                    }
                }
                $this->_show_process('删除数据开始...');
                D('Config')->where(array('type'=>$mod))->delete();
                $this->_show_process('删除数据成功！');
                if($apply['is_exe'] && $is_exe){
                    //检测可执行文件是否存在
                    $this->_show_process('卸载应用程序开始...');
                    $class = $mod . 'Setup';
                    $exe->unload();
                    $this->_show_process('应用程序卸载成功！');
                }
                if(is_file(APP_PATH.$mod.'/Install/install_version.php')){
                    copy(APP_PATH.$mod.'/Install/install_version.php',APP_PATH.$mod.'/Install/version.php');
                }
                $this->_show_process('您的 '.$apply['module_name'].' 已经卸载完成！', 'parent.install_successed();');
                D('Apply')->where(array('alias'=>$mod))->delete();
            }else{
                $this->_show_process('卸载应用失败！');
                $this->_show_process('应用没有正确定装！','parent.install_failure();');
            }
        }
    }
    /**
     * [details 应用详情]
     */
    public function details(){
        $mod = I('request.mod','','trim');
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        $module = array(
            'module' => $mod,
            'version' => F($mod.'/Install/version','',APP_PATH),
            'ico' => APP_PATH.$mod.'/Install/module_ico.jpg',
            'is_setup' => isset($apply[$mod]),
            'setup_time' => $apply['Home']['setup_time']
        );
        $path = APP_PATH.$mod.'/Install/updater_log/';
        $logs = $this->getLog($path);
        if($logs){
            foreach ($logs as $key => $val) {
                if(preg_match_all('(\d+)',$val,$reg)){
                    $v = $reg[0][0] * 1000000 + $reg[0][1] * 10000 + $reg[0][2];
                }
                $data[$v] = $val;
            }
            krsort($data);
            foreach ($data as $key => $val) {
                $cont = file_get_contents($path.$val);
                $cont =  str_replace(array("\r\n", "\r", "\n"), "</br>", $cont); 
                $module['versioning'] .= "【{$val}】</br>".$cont;
                $module['versioning'] .= '</br></br>';
            }
        }else{
            $module['versioning'] = file_get_contents(APP_PATH.$mod.'/Install/upgrade_log.log');
        }
        $this->assign('module',$module);
        $this->display();
    }
    /**
     * 显示安装进程
     */
    protected function _show_process($msg, $script = '') {
        echo '<script type="text/javascript">parent.show_process(\'<p><span>' . $msg . '</span></p>\');' . $script . '</script>';
        flush();
        ob_flush();
    }
    protected function _get_sql($sql_file) {
        $contents = file_get_contents($sql_file);
        $contents = str_replace("\r\n", "\n", $contents);
        $contents = trim(str_replace("\r", "\n", $contents));
        $return_items = $items = array();
        $items = explode(";\n", $contents);

        foreach ($items as $item) {
            $return_item = '';
            $item = trim($item);
            $lines = explode("\n", $item);
            foreach ($lines as $line) {
                if (isset($line[1]) && $line[0] . $line[1] == '--') {
                    continue;
                }
                $return_item .= $line;
            }
            if ($return_item) {
                $return_items[] = $return_item; //.";";
            }
        }
        return $return_items;
    }
    //获取文件目录列表,该方法返回数组
    protected function getDir($dir) {
        if (false != ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".." && false === strpos($file,".") && !in_array($file,array('Common','Home','Admin'))) {
                    $dirArray[$file]=1;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
    //获取文件列表,该方法返回数组
    protected function getLog($dir) {
        if (false != ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".." && false !== strpos($file,".log")) {
                    $dirArray[]=$file;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
    /**
     * 安装应用后台菜单添加
     */
    protected function _menu($sql){
        $arr = explode('VALUES',$sql);
        $reg = "/`(?!".C('DB_PREFIX')."menu)(\w+)`/is";
        if(!preg_match_all($reg,$arr[0],$reg)) return false;
        $fields = $reg[1];
        $values = explode('),',$arr[1]);
        foreach ($values as $key => $val) {
            $val = str_replace(array(PHP_EOL,"'"," ",");",")"), '', $val);
            $val = ltrim($val,'(');
            $val = explode(',',$val);
            $data = array();
            foreach ($fields as $key => $v) {
                $data[$v] = $val[$key];
            }
            $menus[$data['id']] = $data;
        }
        $tree = $this->_tree($menus);
        return $this->_menu_add($tree);
    }
    /**
     * [_tree 树型化菜单数据]
     */
    protected function _tree($items){
        $tree = array();
        foreach($items as $item){
            if(isset($items[$item['pid']])){
                $items[$item['pid']]['son'][] = &$items[$item['id']];
            }else{
                $tree[] = &$items[$item['id']];
            }
        }
        return $tree;
    }
    /**
     * [_menu_add 应用菜单添加]
     */
    protected function _menu_add($menus,$fid = 0,&$spids){
        $m_c_a = array('name','module_name','controller_name','action_name');
        foreach ($menus as $key => $val) {
            unset($val['id']);
            if(false === $mid = M('Menu')->add($val)) return false;
            if(!$fid){
                if(fieldRegex($val['pid'],'number')){
                    $pid = $val['pid'];
                }else{
                    $arr = explode('.',$val['pid']);
                    foreach($arr as $k=>$v){
                        $where[$m_c_a[$k]] = $v;
                    }
                    $pid = M('Menu')->where($where)->getfield('id');
                }
                $spids[$pid] = '';
            }else{
                $pid = $fid;
            }
            if($pid && !$spids[$pid]){
                $spids[$pid] = M('Menu')->where(array('id'=>$pid))->getfield('spid');
            }
            $spid = $spids[$pid].$mid.'|';
            if(false === M('Menu')->where(array('id'=>$mid))->save(array('pid'=>$pid,'spid'=>$spid))) return false;
            M('AdminAuth')->add(array('role_id' => 1,'menu_id' => $mid));
            if($val['son']){
                if(false === $this->_menu_add($val['son'],$mid,$spids)) return false;
            }
        }
        return true;
    }
    /**
     * 在线更新(获取应用更新列表、下载补丁包、解压、比对)
     */
    public function updater(){
        if(!APP_UPDATER) return false;
        $mod = I('request.mod','','trim');
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        if(!$apply[$mod]) $this->error('请先安装应用！');
        $except_mod = array('Home','Mobile','Subject','Store');
        if(!in_array($mod,$except_mod)){
            $need_check_auth = 1;
        }else{
            $need_check_auth = 0;
        }
        if(!IS_POST){
            !$mod && $this->error('请选择要升级的应用！');
            if(false === $module = F($mod.'/Install/version','',APP_PATH)) $this->error('请正确拷贝安装文件！');
            $module = array(
                'module' => $mod,
                'version' => F($mod.'/Install/version','',APP_PATH),
                'ico' => APP_PATH.$mod.'/Install/module_ico.jpg',
                'is_setup' => isset($apply[$mod]),
                'setup_time' => $apply['Home']['setup_time']
            );
            $this->assign('apply',$apply[$mod]);
            $this->assign('module',$module);
            $this->assign('apply_user',cookie('apply_user'));
            $this->assign('need_check_auth',$need_check_auth);
            $this->display();
        }else{
            $username = I('post.username','','trim');
            $password = I('post.password','','trim');
            $type = I('post.type',0,'intval');
            if($need_check_auth==1){
                $check_auth_res_json = https_request('http://www.74cms.com/index.php?m=home&c=plus&a=plus_check_auth&username='.$username.'&password='.$password.'&module='.$mod.'&domain='.urlencode($_SERVER['HTTP_HOST']));
                $check_auth_res = json_decode($check_auth_res_json,true);
                if($check_auth_res['status']==0){
                    $this->_show_process('应用升级失败！');
                    $this->_show_process($check_auth_res['msg'],'parent.install_failure();');
                    return false;
                }
            }
            $user['username'] =$username;
            $user['password'] =$password;
            cookie('apply_user',$user);
            //开始安装
            if(!$mod){
                $this->_show_process('应用升级失败！');
                $this->_show_process('请正确选择应用！','parent.install_failure();');
                return false;
            }
            if(!extension_loaded('zip')){
                $this->_show_process('应用升级失败！');
                $this->_show_process('升级失败，请开启ZipArchive服务！','parent.install_failure();');
                return false;
            }
            $this->_show_process('升级应用开始');
            $update_cache_path = ONLINE_UPDATER_PATH.'/_cache';
            if(is_dir($update_cache_path)){
                $this->_show_process('清空上次升级缓存文件开始');
                if(!is_writable($update_cache_path)){
                    $this->_show_process('应用升级失败！');
                    $this->_show_process('请手动设置目录【'.$update_cache_path.'】的读写权限！','parent.install_failure();');
                    return false;
                }
                rmdirs($update_cache_path);
                $this->_show_process('缓存文件清空成功');
            }
            $this->_show_process('获取应用下载地址');
            //获取要升级的应用列表
            $dir = new \Common\qscmslib\get_dir_file();
            $mod = is_array($mod) ? $mod : array($mod);
            foreach ($mod as $key => $val) {
                $mods[$val] = $apply[$val];
            }
            //获取应用升级包下载地址
            $paths = $dir->getDownloadPath($mods,$username,$password,urlencode($_SERVER['HTTP_HOST']),$type);
            if(false === $paths){
                $this->_show_process('应用升级失败！');
                $this->_show_process('获取应用下载地址失败！','parent.install_failure();');
                return false;
            }
            if($paths['status'] == 0 || !$paths['data']){
                $this->_show_process('应用升级失败！');
                $this->_show_process($paths['msg'],'parent.install_failure();');
                return false;
            }
            $this->_show_process('应用下载地址获取成功');
			
            //跟据反回下载地址，下载升级包程序
            foreach ($paths['data'] as $val) {
                foreach ($val as $key => $v) {
                    $file = ONLINE_UPDATER_PATH . '/versions/' . $v['module_name'] . '/' . pathinfo($v['download'],PATHINFO_BASENAME);
                    $reg = \Common\ORG\Http::curlDownload($v['download'],$file);
                    if(false === $reg){
                        $this->_show_process($apply[$v['module_name']]['module_name'].'升级包【'.$v['version'].'】下载失败');
                    }else{
                        $v['file'] = $file;
                        $files[$v['module_name']][] = $v;
                        $this->_show_process($apply[$v['module_name']]['module_name'].'升级包【'.$v['version'].'】下载成功');
                    }   
                }
            }
            if(!$files){
                $this->_show_process('应用升级失败！');
                $this->_show_process('没有可以升级的程序！','parent.install_failure();');
                return false;
            }
            //解压升级包
            $this->_show_process('解压升级包开始');
            foreach ($files as $val) {
                foreach ($val as $key => $v) {
                    $path = $dir->unzip($v['file'],ONLINE_UPDATER_PATH.'/_cache/');
                    if(false !== $path){
                        unset($v['download']);
                        unset($v['file']);
                        $v['setup'] = $path;
                        $_cache[$v['module_name']][] = $v;
                        $this->_show_process($apply[$v['module_name']]['module_name'].'升级包【'.$v['version'].'】解压成功');
                    }else{
                        $this->_show_process($apply[$v['module_name']]['module_name'].'升级包【'.$v['version'].'】解压失败');
                    }
                }
            }
            if(!$_cache){
                $this->_show_process('应用升级失败！');
                $this->_show_process('没有可以升级的程序！','parent.install_failure();');
                return false;
            }
            $data = array(
                'mod' => $mod,
                'cache' => $_cache
            );
            S('_apply_updater_cache',$data,3600);
            $time = time();
            session('_apply_updater_cache',$time);
            $url = U('apply/updater_auth',array('time'=>$time));
            $this->_show_process('升级包比对','parent.install_updater_auth("'.$url.'");');
            return false;
        }
    }
    public function updater_auth(){
        if(!APP_UPDATER) return false;
        $time = I('request.time',0,'intval');
        $time_ori = session('_apply_updater_cache');
        if($time != $time_ori || !$_cache = S('_apply_updater_cache')){
            S('_apply_updater_cache',NULL);
            $this->redirect('apply/index');
        }else{
            $dir = new \Common\qscmslib\get_dir_file();
            $update_cache_path = ONLINE_UPDATER_PATH.'/_cache';
            if(!IS_POST){
                if(false !== $checked_dirs = $dir->comparison($_cache['cache'])){
                    $this->assign('time',$time_ori);
                    $this->assign('auth',$dir->auth);
                    $this->assign('checked_dirs',$checked_dirs);
                }else{
                    $this->assign('error','升级失败，没有可以升级的程序！');
                }
                $this->display();
            }else{
                if(false === $checked_dirs = $dir->comparison($_cache['cache'],true)){
                    $this->_show_process('应用升级失败！');
                    $this->_show_process('没有可以升级的程序！','parent.install_failure(1);');
                    return false;
                }
                if($dir->auth == 1){
                    $this->_show_process('应用升级失败！');
                    $this->_show_process('您有文件或路径未设置读写权限，不能进行升级！','parent.install_failure(2);');
                    return false;
                }
                if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
                $this->_show_process('升级程序开始...');
                foreach ($checked_dirs as $key => $val) {
                    $this->_show_process('--------------------------------------------------------------------------------------------------------------------');
                    $this->_show_process('【'.$apply[$key]['module_name'].'】升级开始...');
                    foreach ($val as $k => $v) {
                        $dir->backup_rollback_file($v['setup'],ONLINE_ROLLBACK_PATH.'/versions/'.$v['module_name'].'/74cms_'.$v['module_name'].'_v'.str_replace('.','_',$v['version']).'.zip');
                        $dir->backup_rollback_version($v['module_name'],$v['version']);
                        $dir->backup_rollback_file_php($v['setup'],$v['module_name'],$v['version']);
                        if($count = count($v['dirs'])){
                            $success = $dir->cover($v['dirs']);
                            $failure = $count - $success;
                            $this->_show_process('【v'.$v['version'].'】共覆盖'.$count.'个文件,成功'.$success.'个，失败'.$failure.'个');
                        }
                        $charset = C('DEFAULT_CHARSET');
                        header('Content-type:text/html;charset=' . $charset);
                        $charset = C('DB_CHARSET');
                        $conn = mysql_connect(C('DB_HOST') . ':' . C('DB_PORT'), C('DB_USER'), C('DB_PWD'));
                        $version = mysql_get_server_info();
                        if ($version > '4.1') {
                            if ($charset != 'latin1') {
                                mysql_query("SET character_set_connection={$charset}, character_set_results={$charset}, character_set_client=binary", $conn);
                            }
                            if ($version > '5.0.1') {
                                mysql_query("SET sql_mode=''", $conn);
                            }
                        }
                        $selected_db = mysql_select_db(C('DB_NAME'), $conn);
                        if(is_file($v['setup'].'/sql/create_table.sql')){
                            $this->_show_process('【v'.$v['version'].'】创建数据表开始...');
                            $sqls = $this->_get_sql($v['setup'].'/sql/create_table.sql');
                            foreach ($sqls as $sql) {
                                //替换前缀
                                $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                                //获得表名
                                $run = mysql_query($sql, $conn);
                                if (substr($sql, 0, 12) == 'CREATE TABLE') {
                                    $table_name = C('DB_PREFIX') . preg_replace("/CREATE TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                                    $reg = $run ? '成功！' : '失败！';
                                    $this->_show_process(sprintf('【v'.$v['version'].'】创建数据表 %s '.$reg, $table_name));
                                }elseif(strpos($sql,'ADD')){
                                    $table_name = C('DB_PREFIX') . preg_replace("/ALTER TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                                    $reg = $run ? '成功！' : '失败！';
                                    $this->_show_process(sprintf('【v'.$v['version'].'】数据表 %s 添加字段'.$reg, $table_name));
                                }
                            }
                        }
                        if(is_file($v['setup'].'/sql/initdata.sql')){
                            $this->_show_process('【v'.$v['version'].'】初始化数据开始...');
                            $sqls = $this->_get_sql($v['setup'].'/sql/initdata.sql');
                            foreach ($sqls as $sql) {
                                //替换前缀
                                $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                                //获得表名
                                if (substr($sql, 0, 11) == 'INSERT INTO') {
                                    $table_name = preg_replace("/INSERT INTO `" . C('DB_PREFIX') . "([a-z0-9_]+)` .*/is", "\\1", $sql);
                                    if($table_name == 'menu'){
                                        if(false === $this->_menu($sql)){
                                            $this->_show_process(sprintf('【v'.$v['version'].'】数据表 %s 初始化数据添加失败！',$table_name),'parent.install_failure();');
                                            return false;
                                        }
                                    }else{
                                        $run = mysql_query($sql, $conn);
                                    }
                                    $this->_show_process(sprintf('【v'.$v['version'].'】数据表 %s 初始化数据添加成功！',$table_name));
                                }
                            }
                            $this->_show_process('【v'.$v['version'].'】初始数据添加成功！');
                        }
                        if(is_file($v['setup'].'/sql/deletedata.sql')){
                            $this->_show_process('【v'.$v['version'].'】删除数据表开始...');
                            $sqls = $this->_get_sql($v['setup'].'/sql/deletedata.sql');
                            foreach ($sqls as $sql) {
                                //替换前缀
                                $sql = str_replace('`qs_', '`' . C('DB_PREFIX'), $sql);
                                //获得表名
                                $run = mysql_query($sql, $conn);
                                if (substr($sql, 0, 10) == 'DROP TABLE') {
                                    $table_name = C('DB_PREFIX') . preg_replace("/DROP TABLE IF EXISTS `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                                    $reg = $run ? '成功！' : '失败！';
                                    $this->_show_process(sprintf('【v'.$v['version'].'】删除数据表 %s '.$reg, $table_name));
                                }elseif(substr($sql, 0, 11) == 'ALTER TABLE'){
                                    $table_name = C('DB_PREFIX') . preg_replace("/ALTER TABLE `" . C('DB_PREFIX') . "([a-z0-9_]+)`.*/is", "\\1", $sql);
                                    $reg = $run ? '成功！' : '失败！';
                                    $this->_show_process(sprintf('【v'.$v['version'].'】删除数据表 %s 字段'.$reg, $table_name));
                                }
                            }
                            $this->_show_process('【v'.$v['version'].'】删除数据表成功');
                        }
                        if(is_file($v['setup'].'/php/setup.class.php')){
                            //检测可执行文件是否存在
                            $this->_show_process('【v'.$v['version'].'】自定义程序执行开始...');
                            include $v['setup'].'/php/setup.class.php';
                            $name = $key.'_v'.str_replace('.','_',$v['version']);
                            $exe = new $name();
                            $exe->run();
                            $this->_show_process('【v'.$v['version'].'】自定义程序执行成功');
                        }
                        $v['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
                        D('Apply')->where(array('alias'=>$key))->save(array('version'=>$v['version'],'update_time'=>$v['update_time']));
                        $config = F($key.'/Install/version','',APP_PATH);
                        $config['version'] = $qscms['QSCMS_VERSION'] = $v['version'];
                        $config['update_time'] = $qscms['QSCMS_RELEASE'] = $v['update_time'];
                        F($key.'/Install/version',$config,APP_PATH);
                        if($v['module_name'] == 'Home'){
                            $this->update_config($qscms,CONF_PATH.'url.php');
                        }
                        if(is_file($v['setup'].'/readme.log')){
                            $log_path = APP_PATH.$key.'/Install/updater_log/';
                            if(!is_dir($log_path)) mkdir($log_path,0755,true);
                            copy($v['setup'].'/readme.log',$log_path.$key.'_v'.$v['version'].'.log');
                        }
                    }
                    $this->_show_process('【'.$apply[$key]['module_name'].'】升级成功');
                }
                $this->_show_process('--------------------------------------------------------------------------------------------------------------------');
                $this->_show_process('删除临时文件...');
                rmdirs($update_cache_path);
                $this->_show_process('删除临时文件成功');
                $this->_show_process('恭喜您，您的系统已经升级完成！', 'parent.install_successed();');
            }
        }
    }
    /**
     * 回滚
     */
    public function rollback(){
        if(!APP_UPDATER) return false;
        $mod = I('request.mod','','trim');
        if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
        if(!$apply[$mod]) $this->error('请先安装应用！');
        if(!IS_POST){
            !$mod && $this->error('请选择要升级的应用！');
            if(false === $module = F($mod.'/Install/version','',APP_PATH)) $this->error('请正确拷贝安装文件！');
            $module = array(
                'module' => $mod,
                'version' => F($mod.'/Install/version','',APP_PATH),
                'ico' => APP_PATH.$mod.'/Install/module_ico.jpg',
                'is_setup' => isset($apply[$mod]),
                'setup_time' => $apply['Home']['setup_time']
            );
            $this->assign('enable_rollback',$this->dir_is_empty(ONLINE_ROLLBACK_PATH.'/versions/'.$mod.'/')?'0':'1');
            $this->assign('apply',$apply[$mod]);
            $this->assign('module',$module);
            $this->display();
        }else{
            if(in_array($apply[$mod]['version'],$this->_rollback[$mod])){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('当前版本【'.$apply[$mod]['version'].'】补丁不支持回滚！','parent.install_failure();');
                return false;
            }
            //开始回滚
            if(!$mod){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('请正确选择应用！','parent.install_failure();');
                return false;
            }
            if(!extension_loaded('zip')){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('回滚失败，请开启ZipArchive服务！','parent.install_failure();');
                return false;
            }
            $this->_show_process('回滚应用开始');
            $rollback_cache_path = ONLINE_ROLLBACK_PATH.'/_cache';
            if(is_dir($rollback_cache_path)){
                $this->_show_process('清空上次回滚缓存文件开始');
                if(!is_writable($rollback_cache_path)){
                    $this->_show_process('应用回滚失败！');
                    $this->_show_process('请手动设置目录【'.$rollback_cache_path.'】的读写权限！','parent.install_failure();');
                    return false;
                }
                rmdirs($rollback_cache_path);
                $this->_show_process('缓存文件清空成功');
            }
            $this->_show_process('获取回滚文件');
            //获取回滚文件
            $dir = new \Common\qscmslib\get_dir_file();
            $current_version_info = F($mod.'/Install/version','',APP_PATH);
            $rollback_zip_path = ONLINE_ROLLBACK_PATH.'/versions/'.$mod.'/74cms_'.$mod.'_v'.str_replace(".", "_", $current_version_info['version']).'.zip';
            if(!file_exists($rollback_zip_path)){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('获取回滚文件失败！','parent.install_failure();');
                return false;
            }
            $v['module_name'] = $mod;
            $v['file'] = $rollback_zip_path;
            $v['version'] = $current_version_info['version'];
            $files[$v['module_name']][] = $v;
            
            if(!$files){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('没有可以回滚的程序！','parent.install_failure();');
                return false;
            }
            //解压升级包
            $this->_show_process('解压回滚压缩包开始');
            foreach ($files as $val) {
                foreach ($val as $key => $v) {
                    $path = $dir->unzip($v['file'],ONLINE_ROLLBACK_PATH.'/_cache/');
                    if(false !== $path){
                        unset($v['download']);
                        unset($v['file']);
                        $v['setup'] = $path;
                        $version_info_cache = include ONLINE_ROLLBACK_PATH.'/_cache/74cms_'.$v['module_name'].'_v'.str_replace(".", "_", $v['version']).'/version.php';
                        $v['version'] = $version_info_cache['version'];
                        $v['update_time'] = $version_info_cache['update_time'];
                        $_cache[$v['module_name']][] = $v;
                        $this->_show_process($apply[$v['module_name']]['module_name'].'回滚包【'.$v['version'].'】解压成功');
                    }else{
                        $this->_show_process($apply[$v['module_name']]['module_name'].'回滚包【'.$v['version'].'】解压失败');
                    }
                }
            }
            if(!$_cache){
                $this->_show_process('应用回滚失败！');
                $this->_show_process('没有可以回滚的程序！','parent.install_failure();');
                return false;
            }
            $data = array(
                'mod' => $mod,
                'cache' => $_cache
            );
            S('_apply_rollback_cache',$data,3600);
            $time = time();
            session('_apply_rollback_cache',$time);
            $url = U('apply/rollback_auth',array('time'=>$time));
            $this->_show_process('回滚包比对','parent.install_updater_auth("'.$url.'");');
            return false;
        }
    }
    public function rollback_auth(){
        if(!APP_UPDATER) return false;
        $time = I('request.time',0,'intval');
        $time_ori = session('_apply_rollback_cache');
        if($time != $time_ori || !$_cache = S('_apply_rollback_cache')){
            S('_apply_rollback_cache',NULL);
            $this->redirect('apply/index');
        }else{
            $dir = new \Common\qscmslib\get_dir_file();
            $rollback_cache_path = ONLINE_ROLLBACK_PATH.'/_cache';
            if(!IS_POST){
                if(false !== $checked_dirs = $dir->comparison($_cache['cache'])){
                    $this->assign('time',$time_ori);
                    $this->assign('auth',$dir->auth);
                    $this->assign('checked_dirs',$checked_dirs);
                }else{
                    $this->assign('error','回滚失败，没有可以回滚的程序！');
                }
                $this->display();
            }else{
                if(false === $checked_dirs = $dir->comparison($_cache['cache'],true)){
                    $this->_show_process('应用回滚失败！');
                    $this->_show_process('没有可以回滚的程序！','parent.install_failure(1);');
                    return false;
                }
                if($dir->auth == 1){
                    $this->_show_process('应用回滚失败！');
                    $this->_show_process('您有文件或路径未设置读写权限，不能进行回滚！','parent.install_failure(2);');
                    return false;
                }
                if(false === $apply = F('apply_info_list')) $apply = D('Apply')->apply_info_cache();
                $this->_show_process('回滚程序开始...');
                foreach ($checked_dirs as $key => $val) {
                    $this->_show_process('--------------------------------------------------------------------------------------------------------------------');
                    $this->_show_process('【'.$apply[$key]['module_name'].'】回滚开始...');
                    foreach ($val as $k => $v) {
                        if($count = count($v['dirs'])){
                            $success = $dir->cover($v['dirs']);
                            $failure = $count - $success;
                            $this->_show_process('【v'.$v['version'].'】共覆盖'.$count.'个文件,成功'.$success.'个，失败'.$failure.'个');
                        }
                        
                        if(is_file($v['setup'].'/php/rollback.class.php')){
                            //检测可执行文件是否存在
                            $this->_show_process('【v'.$v['version'].'】自定义程序执行开始...');
                            include $v['setup'].'/php/rollback.class.php';
                            $exe = new \Setup();
                            $exe->run();
                            $this->_show_process('【v'.$v['version'].'】自定义程序执行成功');
                        }
                        $v['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
                        D('Apply')->where(array('alias'=>$key))->save(array('version'=>$v['version'],'update_time'=>$v['update_time']));
                        $config = F($key.'/Install/version','',APP_PATH);
                        $config['version'] = $qscms['QSCMS_VERSION'] = $v['version'];
                        $config['update_time'] = $qscms['QSCMS_RELEASE'] = $v['update_time'];
                        F($key.'/Install/version',$config,APP_PATH);
                        if($v['module_name'] == 'Home'){
                            $this->update_config($qscms,CONF_PATH.'url.php');
                        }
                        if(is_file($v['setup'].'/version.php')){
                            $ver_path = APP_PATH.$key.'/Install/';
                            if(!is_dir($ver_path)) mkdir($ver_path,0755,true);
                            copy($v['setup'].'/version.php',$ver_path.'version.php');
                        }
                        @unlink(str_replace('_cache','versions/'.$key,$v['setup']).'.zip');
                        @unlink($ver_path.'updater_log/'.$key.'_v'.str_replace('_','.',str_replace('_v','',strstr($v['setup'],'_v'))).'.log');
                    }
                    $this->_show_process('【'.$apply[$key]['module_name'].'】回滚成功');
                }
                $this->_show_process('--------------------------------------------------------------------------------------------------------------------');
                $this->_show_process('删除临时文件...');
                rmdirs($rollback_cache_path);
                $this->_show_process('删除临时文件成功');
                $this->_show_process('恭喜您，您的系统已经回滚完成！', 'parent.install_successed();');
            }
        }
    }
    public function dir_is_empty($dir){ 
        if($handle = opendir($dir)){  
            while($item = readdir($handle)){   
                if ($item != '.' && $item != '..')
                    return false;  
            } 
        } 
        return true;
    }
    public function update_content(){
        $content = I('post.content');
        $this->assign('content',$content);
        $html = $this->fetch('update_content');
        $this->ajaxReturn(1,'success',$html);
    }
}
?>