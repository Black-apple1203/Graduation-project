<?php
/**
 * 控制器基类
 *
 * @author andery
 */
namespace Common\Controller;
use Think\Controller;
class BaseController extends Controller
{
    protected function _initialize() {
        //消除所有的magic_quotes_gpc转义
        \Common\ORG\Input::noGPC();
		
        if(false === $this->apply = F('apply_list')) $this->apply = D('Apply')->apply_cache();
        if(!in_array(MODULE_NAME,array('Home','Admin')) && !$this->apply[MODULE_NAME]) $this->_empty();
        !IS_AJAX && $this->assign('apply',$this->apply);
        //初始化网站配置
        if(false === $config = F('config')){
            $config = D('Config')->config_cache();
        }
        $config['apply'] = $this->apply;
		C($config);
		//区分分站
		$subsite_open=$config['qscms_subsite_open'];
		if($subsite_open==1 && !in_array(MODULE_NAME,array('Weixinapp')) && !in_array(ACTION_NAME,array('resume_apply'))){
			$this->_initRenterApp();
		}
		//end
        
    }
    public function _empty() {
        $this->_404();
    }
    protected function _404($tip,$url = '') {
        if ($url) {
            redirect($url);
        } else {
            send_http_status(404);
            if(MODULE_NAME == 'Admin' || CONTROLLER_NAME == 'Admin'){
                $tpl = APP_PATH.'Admin/View/'.C('DEFAULT_THEME').'/public/404.html';
            }elseif(is_file(MODULE_PATH.'View/'.C('DEFAULT_THEME').'/public/404.html')){
                $tpl = MODULE_PATH.'View/'.C('DEFAULT_THEME').'/public/404.html';
            }else{
                $tpl = APP_PATH.'Home/View/'.C('DEFAULT_THEME').'/public/404.html';
            }
            $this->assign('tip',$tip);
            $this->display($tpl);
            exit;
        }
    }
	 /**
     * 初始化当前站点
     */
    protected function _initRenterApp($subsite_open){
        if(MODULE_NAME =='Appapi' && ACTION_NAME != 'subsite_tpl'){
            $host = $_SERVER['HTTP_74CMSSUBSITE'];
        }else{
            $host=$_SERVER['HTTP_HOST'];   
        }
		if(false === $subsite_arr = F('subsite_list')){
			$subsite_arr = D('Subsite')->subsite_cache();
		}
		if($subsite_arr[$host]){
			C('subsite_info',$subsite_arr[$host]);
		}
    }
    /**
     * 上传文件
     */
    protected function _upload($file, $dir = '', $thumb = array(), $save_rule='uniqid') {
        $upload = new \Common\ORG\UploadFile();
        if ($dir) {
            $upload_path = C('qscms_attach_path') . $dir . '/';
            $upload->savePath = $upload_path;//上传文件保存路径
        }
        if ($thumb) {
        	$maxSize = isset($thumb['maxSize']) ? $thumb['maxSize'] : C('qscms_attr_allow_size');
        	$upload->maxSize = intval($maxSize) * 1024;   //文件大小限制
        	$upload->uploadReplace=isset($thumb['uploadReplace']) ? true : false;//存在同名文件是否是覆盖 
            $upload->thumb =isset($thumb['thumb']) ? true : false;//是否对图像进行缩略图处理
            $upload->thumbMaxWidth = $thumb['width'];//生成缩略图的尺寸，多个时用(,)进行分割
            $upload->thumbMaxHeight = $thumb['height'];//生成缩略图的尺寸，多个时用(,)进行分割
            $upload->thumbPrefix = '';//缩略图的文件前缀，默认为thumb_
            $upload->thumbSuffix = isset($thumb['suffix']) ? $thumb['suffix'] : '_thumb';//缩略图的文件后缀，默认为空 
            $upload->thumbExt = isset($thumb['ext']) ? $thumb['ext'] : '';//指定缩略图的扩展名
            $upload->thumbRemoveOrigin = isset($thumb['remove_origin']) ? true : false;//生成缩略图后是否删除原图 
            if(isset($thumb['attach_exts'])){//永许上传的文件类型
            	$upload->allowExts = explode(',', $thumb['attach_exts']);  //文件类型限制
            }else{
            	$allow_exts = explode(',', C('qscms_attr_allow_exts')); //读取配置
            	$allow_exts && $upload->allowExts = $allow_exts;  //文件类型限制
            }
        }
        if( $save_rule!='uniqid' ){
            $upload->saveRule = $save_rule;
        }
        if ($result = $upload->uploadOne($file)) {
            foreach (array('png','gif','bmp','jpg','jpeg') as $val) {
                if(strpos(strtolower($thumb['attach_exts']),$val)){
                    $s = true;
                    break;
                }
            }
            if(!$upload->thumb && $s){
                $image = new \Common\ORG\ThinkImage();
                $path = $result[0]['savepath'].$result[0]['savename'];
                $imageModel = $image->open($path);
                $thumb_width = $imageModel->width();
                $thumb_height = $imageModel->height();
                $imageModel->thumb($thumb_width,$thumb_height)->save($path);
            }
            return array('error'=>1, 'info'=>$result);
        } else {
            return array('error'=>0, 'info'=>$upload->getErrorMsg());
        }
    }
    /**
     * AJAX返回数据标准
     *
     * @param int $status
     * @param string $msg
     * @param mixed $data
     * @param string $dialog
     */
    protected function ajaxReturn($status=1, $msg='', $data='', $dialog='',$type='') {
        parent::ajaxReturn(array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
            'dialog' => $dialog,
        ),$type);
    }
    
    /**
     * 获取当前Action名称
     * @access protected
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // 获取Action名称
            $offset=strrpos(get_class($this), '\\', -10);  // 从尾部第 10 个位置开始查找
            $this->name     =   substr(get_class($this),$offset+1,-10);
        }
        return $this->name;
    }
    /**
     * 检测是否开启验证
     */
    protected function check_captcha_open($type_num,$session_name='error_login_count'){
        $l = C('PLATFORM')=='mobile' ? 'qscms_mobile_captcha_open' : 'qscms_captcha_open';
        if(C($l)==1){
            if($type_num==0){
                return 1;
            }else{
                if(session('?'.$session_name) && session($session_name)>=$type_num){
                    return 1;
                }else{
                    return 0;
                }
            }
        }else{
            return 0;
        }
    }
    /**
     * 渲染简历模板
     */
    public function assign_resume_tpl($variable,$tpl){
        foreach ($variable as $key => $value) {
            $this->assign($key,$value);
        }
        return $this->fetch($tpl);
    }
}