<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://74cms.com All rights reserved.
// +----------------------------------------------------------------------
/**
 * 自动识别ssl
 * @category   Extend
 * @package  Extend
 * @subpackage  Behavior
 */
namespace Common\Behavior;
class IsSslBehavior{
    public function run(&$params) {
        if(is_ssl()){
            C('HTTP_TYPE','https://');
        }else{
            C('HTTP_TYPE','http://');
        }
        return;
    }
}