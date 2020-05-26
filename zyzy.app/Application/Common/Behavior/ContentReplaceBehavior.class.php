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
namespace Common\Behavior;
/**
 * 系统行为扩展：模板内容输出替换
 */
class ContentReplaceBehavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        $content = $this->_replace($content);
    }

    /**
     * 模板内容替换
     * @access protected
     * @param string $content 模板内容
     * @return string
     */
    protected function _replace($content) {
        // 系统默认的特殊变量替换
        $replace = array();
        //静态资源地址
        $statics_url = C('qscms_statics_url');
        if ($statics_url != '') {
            $replace['__STATIC__'] = $statics_url;
        } else {
            $replace['__STATIC__'] = __ROOT__.'/static';
        }
        //附件地址
        $replace['__UPLOAD__'] = __ROOT__.'/data/upload';
        $replace['../public'] = __ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/'.C('DEFAULT_THEME').'/public';
        $replace['__ADMINPUBLIC__'] = __ROOT__.'/'.APP_NAME.'/Admin/View/default/public';
        $replace['__HOMEPUBLIC__'] = __ROOT__.'/'.APP_NAME.'/Home/View/'.C('DEFAULT_THEME').'/public';
        $replace['__COMPANY__'] = __ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/tpl_company';
        $replace['__RESUME__'] = __ROOT__.'/'.APP_NAME.'/'.MODULE_NAME.'/View/tpl_resume';
        $replace['__DEFAULTTHEME__'] = 'Home@'.C('DEFAULT_THEME').'/';
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        return $content;
    }
}