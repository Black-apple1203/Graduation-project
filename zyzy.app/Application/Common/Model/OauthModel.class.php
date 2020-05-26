<?php
namespace Common\Model;
use Think\Model;
class OauthModel extends Model {
    /**
     * [oauth_cache 读取第三方登录数据写入缓存]
     */
    public function oauth_cache() {
        $oauthList = $this->where(array('status' => 1))->order('ordid')->getField('alias,name,config,app_config,status');
        F('oauth_list', $oauthList);
        return $oauthList;
    }

    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('oauth_list', NULL);
    }

    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data, $options) {
        F('oauth_list', NULL);
    }
}

?>