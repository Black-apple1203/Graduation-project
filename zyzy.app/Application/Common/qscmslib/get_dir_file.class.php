<?php
/**
 * 遍历文件
 *
 * @author andery
 */
namespace Common\qscmslib;
use ZipArchive;
define('DS', DIRECTORY_SEPARATOR);//路径分割符
class get_dir_file{
    protected $files = array();
    public $auth = 0;
    protected $download_url = 'https://www.74cms.com/plus/check_module.php?act=modul_updater';
    protected $download_url_http = 'http://www.74cms.com/plus/check_module.php?act=modul_updater';
    public function getDownloadPath($mod,$username,$password,$domain,$type=0){
        if(!$mod) return '请选择要升级的应用！';
        foreach ($mod as $key => $val) {
            $mods[] = $val['alias'].'|'.$val['version'];
        }
        if($this->is_https()){
            $url = $this->download_url . '&module=' . implode(',',$mods) . '&username='.$username.'&password='.$password.'&domain='.$domain.'&type='.$type;
        }else{
            $url = $this->download_url_http . '&module=' . implode(',',$mods) . '&username='.$username.'&password='.$password.'&domain='.$domain.'&type='.$type;
        }
        $reg = $this->get_curl($url);
        if(!$reg) return false;
        $reg = json_decode($reg,true);
        $reg['msg'] = urldecode($reg['msg']);
        return $reg;
    }
    /*public function getDir($directory){
        $mydir = dir($directory);
        echo "<ul>\n";
        while($file = $mydir->read()){
            if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!="..")){
                echo "<li><font color=\"#ff00cc\"><b>$file</b></font></li>\n";
                $this->tree("$directory/$file");
            }
            else
            echo "<li>$file</li>\n";
        }
        echo "</ul>\n";
        $mydir->close();
    }*/

    /**
    * PHP判断当前协议是否为HTTPS
    */
    public function is_https() {
        if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
            return true;
        } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }

    public function getReaddirDir($dir){
        $files=array();
        if(is_dir($dir))
        {
            if($handle=opendir($dir))
            {
                while(($file=readdir($handle))!==false)
                {
                    if($file!="." && $file!="..")
                    {
                        if(is_dir($dir.'/'.$file))
                        {
                            $files[$file]=$this->getReaddirDir($dir.'/'.$file);
                        }
                        else
                        {
                            $this->files[]=$dir.'/'.$file;
                        }
                    }
                }
                closedir($handle);
                return $files;
            }
        }
    }
    public function unzip($data,$path){
        $zip = new ZipArchive; 
        $res = $zip->open($data);
        if ($res === TRUE) {
            $name = basename($data, '.zip');
            $data = dirname($data);
            if(!is_dir($data)) mkdir($data,0777,true);
            //解压缩文件夹 
            $reg = $zip->extractTo($path.$name);
            if($reg){
                $file = $path.$name.'/php/setup.class.php';
                if(is_file($file)){
                    $countent = file_get_contents($file);
                    $str = str_replace('74cms_','',$name);
                    $str = str_replace('.','_',$str);
                    $countent = str_replace('Setup',$str,$countent);
                    file_put_contents($file,$countent);
                }
            }
            $zip->close();
            return $reg ? $path.$name : false;
        } else {
            return false;
        }
    }
    public function comparison($cache,$s = false){
        foreach ($cache as $key=>$val) {
            foreach ($val as $k => $v) {
                $temp = $v['setup'].'/upload';
                $this->files = array();
                $this->getReaddirDir($temp);
                if($this->files){
                    !$s && $this->files[] = $temp . '/'.APP_NAME.'/'.$key.'/Install/version.php';
                    foreach ($this->files as $_k => $_v) {
                        if(!is_array($_v)){
                            $checked_dir = $this->comparison_file($_v,$temp,$s);
                            if(false === $checked_dir) continue;
                            if($s){
                                $v['dirs'][$checked_dir['dir']] = $checked_dir;
                            }else{
                                $checked_dirs[$checked_dir['dir']] = $checked_dir;
                            }
                        }
                    }
                    $s && $checked_dirs[$key][$k] = $v;
                }
            }
        }
        return $checked_dirs?:false;
    }
    protected function comparison_file($dir,$_cache_path,$switch){
        $ori_dir = str_replace($_cache_path,'.',$dir);
        if (!file_exists($ori_dir)){
            while(false !== $n = strrpos($ori_dir,"/")){
                $ori_dir = substr($ori_dir,0,$n);
                if(file_exists($ori_dir)){
                    $checked_dir = $this->comparison_auth($ori_dir,$_cache_path);
                    break;
                }
            }
            if($switch){
                $s = $this->auth;
                $checked_dir = $this->comparison_auth($dir,$_cache_path,$switch);
                $this->auth = $s;
            }else{
                $checked_dir = $checked_dir?:false;
            }
        }else{
            $checked_dir = $this->comparison_auth($dir,$_cache_path,$switch);
        }
        return $checked_dir;
    }
    protected function comparison_auth($dir,$_cache_path,$switch){
        $checked_dir['dir'] = str_replace($_cache_path,'.',$dir);
        if($switch){
            $checked_dir['cache_dir'] = $dir;
        }else{
            $checked_dir['auth'] = substr(decoct(fileperms($checked_dir['dir'])),2);
        }
        if (is_readable($checked_dir['dir'])){
            $checked_dir['read'] = '<span style="color:green;">√可读</span>';
        }else{
            $checked_dir['read'] = '<span sylt="color:red;">×不可读</span>';
            !$s && $s = 1;
        }
        if(is_writable($checked_dir['dir'])){
            $checked_dir['write'] = '<span style="color:green;">√可写</span>';
        }else{
            $checked_dir['write'] = '<span style="color:red;">×不可写</span>';
            !$s && $s = 1;
        }
        $s && $this->auth = $s;
        return $checked_dir;
    }
    public function cover($data){
        $n = 0;
        foreach ($data as $key => $val) {
            $path = dirname($val['dir']);
            if (!is_dir($path)) mkdir($path,0755,true);
            if(copy($val['cache_dir'],$val['dir'])){
                $n++;
            }
        }
        return $n;
    }
    protected function get_curl($url){
        if(function_exists('file_get_contents')){
            $file_contents = file_get_contents($url);
        }else{
            $ch = curl_init();
            $timeout = 5;
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }
    public function getError(){
        return $this->_error();
    }
    /**
     * 根据下载的更新包，查找当前文件，备份为zip包以备回滚使用
     */
    public function backup_rollback_file($path,$save_path){
        $this->getReaddirDir($path.'/upload/');
        $zip=new ZipArchive();
        if (!is_dir(dirname($save_path))) mkdir(dirname($save_path),0755,true);
        foreach ($this->files as $key => $value) {
            $c_file = strstr($value,'Application');
            if($zip->open($save_path, ZipArchive::CREATE)=== TRUE){
                $this->addFileToZip($c_file, $zip);
                $zip->close();
            }
        }
        $this->files = null;
    }
    /**
     * 备份php文件
     */
    public function backup_rollback_file_php($path,$module_name,$version){
        $zip=new ZipArchive();
        $save_path = ONLINE_ROLLBACK_PATH.'/versions/'.$module_name.'/74cms_'.$module_name.'_v'.str_replace(".", "_", $version).'.zip';
        if($zip->open($save_path, ZipArchive::CREATE)=== TRUE && file_exists($path.'/php/rollback.class.php')){
            $zip->addFile($path.'/php/rollback.class.php','php/rollback.class.php');
            $zip->close();
        }
    }
    /**
     * 备份版本文件
     */
    public function backup_rollback_version($module_name,$version){
        $zip=new ZipArchive();
        $save_path = ONLINE_ROLLBACK_PATH.'/versions/'.$module_name.'/74cms_'.$module_name.'_v'.str_replace(".", "_", $version).'.zip';
        if($zip->open($save_path, ZipArchive::CREATE)=== TRUE){
            $zip->addFile(APP_PATH.$module_name.'/Install/version.php','version.php');
            $zip->close();
        }
    }
    /**
     * 递归压缩
     */
    public function addFileToZip($file,$zip){
        if(is_dir($file)){// 如果读取的某个对象是文件夹，则递归
            $this->addFileToZip($file, $zip);
        }else{ //将文件加入zip对象
            $zip->addFile($file,'upload/'.$file);
        }
    }
}
?>