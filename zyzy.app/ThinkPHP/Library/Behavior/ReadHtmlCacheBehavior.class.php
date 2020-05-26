<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behavior;
use Think\Storage;
/**
 * 系统行为扩展：静态缓存读取
 */
class ReadHtmlCacheBehavior {
    private $moduleName;
    // 行为扩展的执行入口必须是run
    public function run(&$params){
        $this->check_is_mobile();
        if(C('PLATFORM')=='mobile' && strtolower(MODULE_NAME)=='home'){
            $this->moduleName = 'mobile';
        }else{
            $this->moduleName = strtolower(MODULE_NAME);
        }
        // 开启静态缓存
        if(IS_GET && C('HTML_CACHE_ON'))  {
            $cacheTime = $this->requireHtmlCache($this->moduleName);
            if( false !== $cacheTime && $this->checkHTMLCache(HTML_FILE_NAME,$cacheTime)) { //静态页面有效
                // 读取静态页面输出
                echo Storage::read(HTML_FILE_NAME,'html');
                exit();
            }
        }
    }
    private function is_mobile_request()   
    {    
        $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';    
        $mobile_browser = '0';    
        if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) $mobile_browser++;    
        if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))     $mobile_browser++;    
        if(isset($_SERVER['HTTP_X_WAP_PROFILE']))     $mobile_browser++;    
        if(isset($_SERVER['HTTP_PROFILE']))     $mobile_browser++;    
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));    
        $mobile_agents = array(       'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',       'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',       'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',       'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',       'newt','noki','oper','palm','pana','pant','phil','play','port','prox',       'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',       'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',       'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',       'wapr','webc','winw','winw','xda','xda-'     );    if(in_array($mobile_ua, $mobile_agents))     $mobile_browser++;    
        if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)     $mobile_browser++;    
        // Pre-final check to reset everything if the user is on Windows    
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)     $mobile_browser=0;    
        // But WP7 is also Windows, with a slightly different characteristic    
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)     $mobile_browser++;    
        if($mobile_browser>0)     
            return true;   
        else   
            return false; 
    }
    private function check_is_mobile(){
        if(SETUP_STATUS) return;
        if(false === $apply = F('apply_list')) $apply = D('Apply')->apply_cache();
        if(!$apply['Mobile']) return;
        $is_mobile = $this->is_mobile_request();
        if($is_mobile){
            C('PLATFORM','mobile');
            return;
        }
    }

    // 判断是否需要静态缓存
    static private function requireHtmlCache($moduleName) {
        // 分析当前的静态规则
         $htmls = C('HTML_CACHE_RULES'); // 读取静态规则
         if(!empty($htmls)) {
            $htmls = array_change_key_case($htmls);
            // 静态规则文件定义格式 actionName=>array('静态规则','缓存时间','附加规则')
            // 'read'=>array('{id},{name}',60,'md5') 必须保证静态规则的唯一性 和 可判断性
            // 检测静态规则
            // $moduleName = strtolower(MODULE_NAME);
            $controllerName = strtolower(CONTROLLER_NAME);
            $actionName     = strtolower(ACTION_NAME);
            if(isset($htmls[$moduleName.':'.$controllerName.':'.$actionName])) {
                $html   =   $htmls[$moduleName.':'.$controllerName.':'.$actionName];   // 某个控制器的操作的静态规则
            }elseif(isset($htmls[$controllerName.':'])){// 某个控制器的静态规则
                $html   =   $htmls[$controllerName.':'];
            }elseif(isset($htmls[$actionName])){
                $html   =   $htmls[$actionName]; // 所有操作的静态规则
            }elseif(isset($htmls['*'])){
                $html   =   $htmls['*']; // 全局静态规则
            }
            if(!empty($html)) {
                // 解读静态规则
                $rule   = is_array($html)?$html[0]:$html;
                // 以$_开头的系统变量
                $callback = function($match){ 
                    switch($match[1]){
                        case '_GET':        $var = $_GET[$match[2]]; break;
                        case '_POST':       $var = $_POST[$match[2]]; break;
                        case '_REQUEST':    $var = $_REQUEST[$match[2]]; break;
                        case '_SERVER':     $var = $_SERVER[$match[2]]; break;
                        case '_SESSION':    $var = $_SESSION[$match[2]]; break;
                        case '_COOKIE':     $var = $_COOKIE[$match[2]]; break;
                    }
                    return (count($match) == 4) ? $match[3]($var) : $var;
                };
                $rule     = preg_replace_callback('/{\$(_\w+)\.(\w+)(?:\|(\w+))?}/', $callback, $rule);
                // {ID|FUN} GET变量的简写
                $rule     = preg_replace_callback('/{(\w+)\|(\w+)}/', function($match){return $match[2]($_GET[$match[1]]);}, $rule);
                $rule     = preg_replace_callback('/{(\w+)}/', function($match){return $_GET[$match[1]];}, $rule);
                // 特殊系统变量
                $rule   = str_ireplace(
                    array('{:controller}','{:action}','{:module}'),
                    array($controllerName,$actionName,$moduleName),
                    $rule);
                // {|FUN} 单独使用函数
                $rule  = preg_replace_callback('/{|(\w+)}/', function($match){return $match[1]();},$rule);
                $cacheTime  =   C('HTML_CACHE_TIME',null,60);
                if(is_array($html)){
                    if(!empty($html[2])) $rule    =   $html[2]($rule); // 应用附加函数
                    $cacheTime  =   isset($html[1])?$html[1]:$cacheTime; // 缓存有效期
                }else{
                    $cacheTime  =   $cacheTime;
                }
                
                // 当前缓存文件
                define('HTML_FILE_NAME',HTML_PATH . $rule.C('HTML_FILE_SUFFIX',null,'.html'));
                return $cacheTime;
            }
        }
        // 无需缓存
        return false;
    }

    /**
     * 检查静态HTML文件是否有效
     * 如果无效需要重新更新
     * @access public
     * @param string $cacheFile  静态文件名
     * @param integer $cacheTime  缓存有效期
     * @return boolean
     */
    static public function checkHTMLCache($cacheFile='',$cacheTime='') {
        if(!is_file($cacheFile) && 'sae' != APP_MODE ){
            return false;
        }elseif (filemtime(\Think\Think::instance('Think\View')->parseTemplate()) > Storage::get($cacheFile,'mtime','html')) {
            // 模板文件如果更新静态文件需要更新
            return false;
        }elseif(!is_numeric($cacheTime) && function_exists($cacheTime)){
            return $cacheTime($cacheFile);
        }elseif ($cacheTime != 0 && NOW_TIME > Storage::get($cacheFile,'mtime','html')+$cacheTime) {
            // 文件是否在有效期
            return false;
        }
        //静态文件有效
        return true;
    }

}