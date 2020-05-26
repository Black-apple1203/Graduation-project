<?php
/**
 * 七牛云上传
 */
namespace Common\ORG;
require 'qiniuSDK/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use Qiniu\Processing\ImageUrlBuilder;
class qiniu
{
    private $_config = array(
        'secretKey'      => '', //七牛密码
        'accessKey'      => '', //七牛用户
        'domain'         => '', //七牛服务器
        'bucket'         => '', //空间名称
        'timeout'        => 300, //超时时间
        'maxSize' => 5*1024,//文件大小(KB)
        'exts'=>'bmp,png,gif,jpeg,jpg',
        'rootPath' => './',
        'saveNameRule'=>''
    );
    private $_apiOpen;
    private $_error = 0;

    public function __construct($params = array()) {
        $this->_apiOpen = C('qscms_qiniu_open');
        if(!$this->_apiOpen){
            $this->_error = 'api not found';
            return false;
        }
        $this->_config = array(
            'secretKey'      => C('qscms_qiniu_secretKey'), //七牛密码
            'accessKey'      => C('qscms_qiniu_accessKey'), //七牛用户
            'domain'         => C('qscms_qiniu_domain'), //七牛服务器
            'bucket'         => C('qscms_qiniu_bucket'), //空间名称
            'saveNameRule'   => array('uniqid', '')
        );
        $this->_auth = new Auth($this->_config['accessKey'], $this->_config['secretKey']);
        $this->_config = array_merge($this->_config,$params);
        $this->_config['maxSize'] = $this->_config['maxSize'] * 1024;
    }
    /**
     * 缩略图
     */
    public function makeThumb($url,$width,$height){
        $imageUrlBuilder = new ImageUrlBuilder();
        $thumbLink = $imageUrlBuilder->thumbnail($url, 1, $width, $height);
        return $thumbLink;
    }
    /**
     * 上传文件 - 二进制
     */
    public function uploadStream($file,$filename="",$width="",$height="",$thumb=false){
        $uploadMgr = new UploadManager();
        $token = $this->_auth->uploadToken($this->_config['bucket']);
        list($ret, $err) = $uploadMgr->put($token, $filename, $file);
        if ($err !== null) {
            $this->_error = $err;
            return false;
        } else {
            if($thumb){
                return $this->makeThumb($this->downLink($ret['key']),$width,$height); 
            }else{
                return $this->downLink($ret['key']);
            }
        }
    }
    /**
     * 文件复制
     */
    public function copy($file,$file_copy){
        //初始化BucketManager
        $bucketMgr = new BucketManager($this->_auth);
        $err = $bucketMgr->copy($this->_config['bucket'], $file, $this->_config['bucket'], $file_copy);
        if ($err !== null) {
            var_dump($err);
        } else {
            echo "Success!";
        }
    }
    /**
     * 上传文件
     */
    public function upload($file,$name,$filename="",$width="",$height="",$thumb=false){
        $file = $file[$name];
        $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);
        //检查合法性
        if(!$this->check($file)){
            return false;
        }
        $token = $this->_auth->uploadToken($this->_config['bucket']);
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        $filename = $filename?$filename:$this->getSaveName($file);
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $filename, $file['tmp_name']);
        if ($err !== null) {
            $this->_error = $err;
            return false;
        } else {
            if($thumb){
                return $this->makeThumb($this->downLink($ret['key']),$width,$height); 
            }else{
                return $this->downLink($ret['key']);
            }
        }
    }
    public function getKey($url){
        $url = str_replace("http://".$this->_config['domain']."/", "", $url);
        return $url;
    }
    /**
     * 生成缩略图url
     */
    public function getThumbName($url,$width,$height){
        $url = str_replace("http://".$this->_config['domain']."/", "", $url);
        $arr = explode(".", $url);
        return $arr[0]."_".$width."x".$height.".".$arr[1];
    }
    /**
     * 删除文件
     */
    public function delete($file){
        $file = str_replace('http://'.$this->_config['domain'].'/','',$file);
        //初始化BucketManager
        $bucketMgr = new BucketManager($this->_auth);
        $err = $bucketMgr->delete($this->_config['bucket'], $file);
        if ($err !== null) {
            $this->_error = $err;
            return false;
        } else {
            return true;
        }
    }
    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule, $filename){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            foreach ($param as &$value) {
               $value = str_replace('__FILE__', $filename, $value);
            }
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }
    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
        $rule = $this->_config['saveNameRule'];
        if (empty($rule)) { //保持文件名不变
            /* 解决pathinfo中文文件名BUG */
            $filename = substr(pathinfo("_{$file['name']}", PATHINFO_FILENAME), 1);
            $savename = $filename;
        } else {
            $savename = $this->getName($rule, $file['name']);
            if(empty($savename)){
                $this->error = '文件命名规则错误！';
                return false;
            }
        }
        /* 获取上传文件后缀，允许上传无后缀文件 */
        $file['ext']    =   pathinfo($file['name'], PATHINFO_EXTENSION);
        return date('Y-m-d-').$savename . '.' . $file['ext'];
    }
    //获取文件下载资源链接
    public function downLink($key){
        $key = urlencode($key);
        $key = self::_escapeQuotes($key);
        $url = "http://{$this->_config['domain']}/{$key}";
        return $url;
    }
    static function _escapeQuotes($str){
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }
    private function _encode($str) // URLSafeBase64Encode
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($str));
    }
    public function getError(){
        return $this->_error;
    }
    /**
     * 检查上传的文件
     * @param array $file 文件信息
     */
    private function check($file) {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']) {
            $this->_error = '文件上传失败';
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            $this->_error = '未知上传错误！';
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->_error = '非法上传文件！';
            return false;
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->_error = '上传文件大小不符！';
            return false;
        }

        /* 检查文件Mime类型 */
        //TODO:FLASH上传的文件获取到的mime类型都为application/octet-stream
        if (!$this->checkMime($file['type'])) {
            $this->_error = '上传文件MIME类型不允许！';
            return false;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file['ext'])) {
            $this->_error = '上传文件后缀不允许';
            return false;
        }

        /* 通过检测 */
        return true;
    }
    /**
     * 检查文件大小是否合法
     * @param integer $size 数据
     */
    private function checkSize($size) {
        return !($size > $this->_config['maxSize']) || (0 == $this->_config['maxSize']);
    }

    /**
     * 检查上传的文件MIME类型是否合法
     * @param string $mime 数据
     */
    private function checkMime($mime) {
        return empty($this->_config['mimes']) ? true : in_array(strtolower($mime), explode(",", $this->config['mimes']));
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param string $ext 后缀
     */
    private function checkExt($ext) {
        return empty($this->_config['exts']) ? true : in_array(strtolower($ext), explode(",", $this->_config['exts']));
    }
}