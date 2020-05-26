<?php
/**
 * 微信相关
 *
 * @author andery
 */
namespace Common\qscmslib;
class weixin {
    /*
        pwd_hash
        获取access_token
    */
    public function get_access_token($length = 6, $reset = false) {
        $access_token = S('access_token');
        if ($access_token && !$reset) return $access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . C('qscms_weixin_appid') . "&secret=" . C('qscms_weixin_appsecret');
        $result = self::https_request($url);
        $jsoninfo = json_decode($result, true);
        $access_token = $jsoninfo["access_token"];
        //更新数据
        S('access_token', $access_token, 7200);
        return $access_token;
    }

    /**
     * [get_weixin_json_menu 读取微信菜单]
     */
    public function get_weixin_json_menu() {
        $arr = array();
        $weixin_menu = M('WeixinMenu');
        $menu_arr = $weixin_menu->where(array('parentid' => 0, 'status' => 1))->order('menu_order desc,id asc')->select();
        foreach ($menu_arr as $key => $value) {
            $sub_menu = $weixin_menu->where(array('parentid' => $value['id'], 'status' => 1))->order('menu_order desc,id asc')->select();
            if (!empty($sub_menu)) {
                $arr[$key]['name'] = urlencode($value['title']);
                foreach ($sub_menu as $sub_key => $sub_value) {
                    $arr[$key]['sub_button'][$sub_key]['type'] = $sub_value['type'];
                    $arr[$key]['sub_button'][$sub_key]['name'] = urlencode($sub_value['title']);
                    if ($sub_value['type'] == "click") {
                        $arr[$key]['sub_button'][$sub_key]['key'] = $sub_value['key'];
                    } else if($sub_value['type'] == "miniprogram") {
                        $arr[$key]['sub_button'][$sub_key]['appid'] = $sub_value['appid'];
                        $arr[$key]['sub_button'][$sub_key]['pagepath'] = $sub_value['pagepath'];
                        $sub_value['url'] = str_replace('|appid|', C('qscms_weixin_appid'), $sub_value['url']);
                        $sub_value['url'] = htmlspecialchars_decode($sub_value['url'], ENT_QUOTES);
                        $arr[$key]['sub_button'][$sub_key]['url'] = $sub_value['url'];
                        $weixin_menu->where(array('id' => $sub_value['id']))->setfield('url', $sub_value['url']);
                    } else {
                        $sub_value['url'] = str_replace('|appid|', C('qscms_weixin_appid'), $sub_value['url']);
                        $sub_value['url'] = htmlspecialchars_decode($sub_value['url'], ENT_QUOTES);
                        $arr[$key]['sub_button'][$sub_key]['url'] = $sub_value['url'];
                        $weixin_menu->where(array('id' => $sub_value['id']))->setfield('url', $sub_value['url']);
                    }
                }
            } else {
                $arr[$key]['type'] = $value['type'];
                $arr[$key]['name'] = urlencode($value['title']);
                if ($value['type'] == "click") {
                    $arr[$key]['key'] = $value['key'];
                } else if($value['type'] == "miniprogram") {
                    $arr[$key]['appid'] = $value['appid'];
                    $arr[$key]['pagepath'] = $value['pagepath'];
                    $value['url'] = str_replace('|appid|', C('qscms_weixin_appid'), $value['url']);
                    $value['url'] = htmlspecialchars_decode($value['url'], ENT_QUOTES);
                    $arr[$key]['url'] = $value['url'];
                    $weixin_menu->where(array('id' => $value['id']))->setfield('url', $value['url']);
                } else {
                    $value['url'] = str_replace('|appid|', C('qscms_weixin_appid'), $value['url']);
                    $value['url'] = htmlspecialchars_decode($value['url'], ENT_QUOTES);
                    $arr[$key]['url'] = $value['url'];
                    $weixin_menu->where(array('id' => $value['id']))->setfield('url', $value['url']);
                }
            }
        }
        $menu['button'] = $arr;
        return urldecode(json_encode($menu));
    }

    /**
     * [menu_sync 微信菜单同步]
     */
    public function menu_sync() {
        if (!C('qscms_weixin_appid')) return '请配置微信公众号参数！';
        $access_token = self::get_access_token();
        $jsonmenu = self::get_weixin_json_menu();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $result = self::https_request($url, $jsonmenu);
        $result_arr = json_decode($result, true);
        if ($result_arr['errcode'] == 0 && $result_arr['errmsg'] == 'ok') {
            return true;
        } else {
            return $result_arr['errmsg'] . '(错误代码：' . $result_arr['errcode'] . ')';
        }
    }

    /**
     * [send_msg 微信发送信息]
     */
    public function send_msg($openid, $content) {
        if (!$openid) return '微信openid不能为空！';
        if (!$content) return '内容不能为空！';
        $access_token = self::get_access_token();
        $data_arr = array(
            "touser"  => $openid,
            "msgtype" => "text",
            "text"    => array(
                "content" => urlencode($content)
            )
        );
        $data = urldecode(json_encode($data_arr));
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
        $result = self::https_request($url, $data);
        $result_arr = json_decode($result, true);
        if ($result_arr['errcode'] == 0 && $result_arr['errmsg'] == 'ok') {
            return true;
        } else {
            return $result_arr['errmsg'] . '(错误代码：' . $result_arr['errcode'] . ')';
        }
    }
    /**
     * [send_queue_msg 批量发送微信消息]
     */
    public function send_queue_msg($utype, $content) {
        if (!$content) return '内容不能为空！';
        $db_pre = C('DB_PREFIX');
        $m_t = $db_pre . 'members';
        if ($utype == 1 || $utype == 2) {
            $where['utype'] = $utype;
        }
        $where['b.type'] = 'weixin';
        if ($user_list = M('Members')->field($m_t . '.uid,username,utype,b.keyid as weixin_openid')->where($where)->join($db_pre . 'members_bind as b ON b.uid=' . $m_t . '.uid')->select()) {
            $access_token = self::get_access_token();
            $msg_mod = D('WeixinMsgList');
            foreach ($user_list as $val) {
                $result = self::send_msg($val['weixin_openid'], $content);
                if ($result) {
                    $val['content'] = $content;
                    if (false === $msg_mod->create($val)) return $msg_mod->getError();
                    if (false === $msg_mod->add()) return '微信消息记录失败！';
                }
            }
            return true;
        } else {
            return '暂时还没有满足条件的用户！';
        }
    }
    /**
     * [qrcode_img 生成微信二维码]
     */
    public function qrcode_img($option = array()) {
        $config = array('type'=>'','width'=>120,'height'=>120,'params'=>0);
        $config = array_merge($config,$option);
        if(!in_array($config['type'],array('login','register','bind','job','company','sync','subscribe_job','subscribe_company'))) return '';
        $access_token = self::get_access_token();
        $data['type'] = $config['type'];
		//h
		if($config['id']){
			$data['id'] = $config['id'];
		}
		if($config['token_admin']==1){
			$data['token_admin']=1;
		}
		//
        if($config['type'] != 'sync'){
            srand((double)microtime() * 1000000);
            $scene_id = rand(1, 10000000);
            session($config['type'] . '_scene_id', $scene_id);
            F('/weixin/' . ($scene_id % 10) . '/' . $scene_id, $config['params']);
            $data['value'] = $scene_id;
        }elseif(!$data['value'] = $config['params']){
            return '';
        }
        $expire_time = $config['expire']?$config['expire']:1800;
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
        $post_data = '{"expire_seconds": '.$expire_time.', "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "' . http_build_query($data) . '"}}}';
        $result = self::https_request($url, $post_data);
        $result_arr = json_decode($result, true);
        if ($result_arr['errcode'] == 40001) {
            self::get_access_token(6, true);
            self::qrcode_img($config);
        } else {
            $ticket = urlencode($result_arr["ticket"]);
            $html = '<img width=' . $config['width'] . ' height=' . $config['height'] . ' src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket . '">';
            return $html;
        }
    }
    /**
     * 创建永久二维码
     * $scene_value  场景值
     * $str_type     默认为字符串形式
     */
    public function create_forever_qrcode($scene_value, $str_type = true) {
        $access_token = self::get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
        if ($str_type) {
            $post_data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . $scene_value . '"}}}';
        } else {
            $post_data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' . $scene_value . '}}}';
        }
        $result = self::https_request($url, $post_data);
        $result_arr = json_decode($result, true);
        if ($result_arr['errcode'] == 40001) {
            self::get_access_token(6, true);
            self::create_forever_qrcode($scene_value, $str_type);
        } else {
            $ticket = urlencode($result_arr["ticket"]);
            return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
        }
    }

    /**
     * 获取二维码图片信息
     */
    protected function downloadImageFromWeixin($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        return array_merge(array('body' => $package), array('header' => $httpinfo));
    }

    /**
     * 二维码下载到本地
     */
    public function down_qrcode($img_src, $filename) {
        $imageinfo = self::downloadImageFromWeixin($img_src);
        $local_file = fopen($filename, 'w');
        if (false !== $local_file) {
            if (false !== fwrite($local_file, $imageinfo['body'])) {
                fclose($local_file);
            }
        }
    }

    /**
     * [bind 绑定微信]
     */
    public function bind($openid, $user) {
        if (!$openid) return array('state' => 0, 'tip' => 'openid不能为空！');
        $bind_user = D('MembersBind')->get_members_bind(array('type' => 'weixin', 'openid' => $openid));
        if ($bind_user['uid'] && $bind_user['uid'] != $user['uid']) return array('state' => 0, 'tip' => '此帐号已经绑定过本站！');
        $bind_uid = D('MembersBind')->where(array('uid' => $user['uid'], 'type' => 'weixin'))->select();
        if ($bind_uid) return array('state' => 0, 'tip' => '此帐号已经绑定过微信！');
        $access_token = self::get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $result = self::https_request($url);
        $result = preg_replace("/(\\\ue[0-9a-f]{3})/ie", "addslashes('\\1')", $result);
        $userinfo = json_decode($result, true);
        if($userinfo['errcode']) return array('static'=>0,'tip'=>$userinfo['errmsg']);
        $userarr['uid'] = $user['uid'];
        $userinfo['subscribe'] && $userarr['is_focus'] = $userinfo['subscribe'];
        $userarr['openid'] = $userinfo['openid'];
        $userarr['username'] = emoji_unicode($userinfo['nickname']);
        $userarr['unionid'] = $userinfo['unionid'];
        $userarr['focustime'] = $userinfo['subscribe_time'];
        $userarr['type'] = 'weixin';
        $userarr['keyavatar_big'] = $userinfo['headimgurl'];
        $userarr['info'] = array('openid' => $userinfo['openid'], 'keyname' => $userarr['username'], 'sex' => $userinfo['sex'], 'city' => $userinfo['city'], 'province' => $userinfo['province'], 'country' => $userinfo['country'], 'headimgurl' => $userinfo['headimgurl']);
        $userarr['is_bind'] = 1;
        $userarr['bindingtime'] = time();
        $map['openid'] = $userinfo['openid'];
        $map['unionid'] = $userinfo['unionid'];
        $map['_logic'] = 'OR';
        $where['type'] = 'weixin';
        $where['_complex'] = $map;
        $bind_user = M('MembersBind')->where($where)->find();
        if (!$bind_user) {
            $userarr['info'] = serialize($userarr['info']);
            if ($id = M('MembersBind')->add($userarr)) {
                // 绑定微信 获得积分
                D('TaskLog')->do_task($user, 'binding_weixin');
                $rule = D('Task')->get_task_cache($user['utype'], 'binding_weixin');
                $user_points = D('MembersPoints')->get_user_points($user['uid']);
                $operator = "+";
                $scene_id = session('bind_scene_id');
                session('bind_scene_id', null);
                F('/weixin/' . ($scene_id % 10) . '/' . $scene_id, null);
            } else {
                return array('state' => 0, 'tip' => '微信绑定失败！');
            }
        } else {
            $userarr['info'] = serialize(array_merge(unserialize($bind_user['info']), $userarr['info']));
            M('MembersBind')->where(array('type' => 'weixin', 'unionid' => $userinfo['unionid']))->save($userarr);
        }
        return array('state' => 1, 'tip' => '微信绑定成功！','data'=>$userarr);
    }

    /**
     * [get_user_info 获取微信用户信息]
     */
    public function get_user_info($openid) {
        if (!$openid) return array('state' => 0, 'tip' => 'openid不能为空！');
        $access_token = self::get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $result = self::https_request($url);
        $result = preg_replace("/(\\\ue[0-9a-f]{3})/ie", "addslashes('\\1')", $result);
        $userinfo = json_decode($result, true);
        $userinfo['subscribe'] && $user['is_focus'] = $userinfo['subscribe'];
        $user['openid'] = $userinfo['openid'];
        $user['username'] = emoji_unicode($userinfo['nickname']);
        $user['unionid'] = $userinfo['unionid'];
        $user['focustime'] = $userinfo['subscribe_time'];
        $user['type'] = 'weixin';
        $user['keyavatar_big'] = $userinfo['headimgurl'];
        $user['bind_info'] = serialize(array('openid' => $userinfo['openid'], 'keyname' => $user['username'], 'sex' => $userinfo['sex'], 'city' => $userinfo['city'], 'province' => $userinfo['province'], 'country' => $userinfo['country'], 'headimgurl' => $userinfo['headimgurl']));
        session('members_bind_info', $user);
        cookie('members_bind_info', $user);
        return array('state' => 1, 'tip' => '用户信息获取成功！', 'data' => $user);
    }
    /**
     * [bind_auth 绑定微信]
     */
    public function bind_auth($openid,$access_token,$user) {
        if (!$openid) return array('state' => 0, 'tip' => 'openid不能为空！');
        if (!$access_token) return array('state' => 0, 'tip' => 'access_token不能为空！');
        $bind_user = D('MembersBind')->get_members_bind(array('type' => 'weixin', 'openid' => $openid));
        if ($bind_user['uid'] && $bind_user['uid'] != $user['uid']) return array('state' => 0, 'tip' => '此帐号已经绑定过本站！');
        $bind_uid = D('MembersBind')->where(array('uid' => $user['uid'], 'type' => 'weixin'))->select();
        if ($bind_uid) return array('state' => 0, 'tip' => '此帐号已经绑定过微信！');
        $reg = self::get_userinfo($openid,$access_token);
        if(!$reg['state']) return array('state' => 0, 'tip' => $reg['tip']);
        $userarr = $reg['data'];
        $userarr['uid'] = $user['uid'];
        $userarr['is_bind'] = 1;
        $userarr['bindingtime'] = time();
        $userarr['info'] = $userarr['bind_info'];
        $map['openid'] = $userarr['openid'];
        $map['unionid'] = $userarr['unionid'];
        $map['_logic'] = 'OR';
        $where['type'] = 'weixin';
        $where['_complex'] = $map;
        $bind_user = M('MembersBind')->where($where)->find();
        if (!$bind_user) {
            if ($id = M('MembersBind')->add($userarr)) {
                // 绑定微信 获得积分
                D('TaskLog')->do_task($user, 'binding_weixin');
                $rule = D('Task')->get_task_cache($user['utype'], 'binding_weixin');
                $user_points = D('MembersPoints')->get_user_points($user['uid']);
                $operator = "+";
                $scene_id = session('bind_scene_id');
                session('bind_scene_id', null);
                F('/weixin/' . ($scene_id % 10) . '/' . $scene_id, null);
            } else {
                return array('state' => 0, 'tip' => '微信绑定失败！');
            }
        } else {
            $userarr['info'] = serialize(array_merge(unserialize($bind_user['info']), unserialize($userarr['info'])));
            $aaa = M('MembersBind')->where(array('type' => 'weixin', 'unionid' => $userarr['unionid']))->save($userarr);
        }
        return array('state' => 1, 'tip' => '微信绑定成功！','data'=>$userarr);
    }
    public function get_userinfo($openid,$access_token) {
        if (!$openid) return array('state' => 0, 'tip' => 'openid不能为空！');
        if (!$access_token) return array('state' => 0, 'tip' => 'access_token不能为空！');
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $result = self::https_request($url);
        $result = preg_replace("/(\\\ue[0-9a-f]{3})/ie", "addslashes('\\1')", $result);
        $userinfo = json_decode($result, true);
        $userinfo['subscribe']&& $user['is_focus'] = $userinfo['subscribe'];
        $user['openid'] = $userinfo['openid'];
        $user['username'] = emoji_unicode($userinfo['nickname']);
        $user['unionid'] = $userinfo['unionid'];
        $user['focustime'] = $userinfo['subscribe_time']?:0;
        $user['type'] = 'weixin';
        $user['keyavatar_big'] = $userinfo['headimgurl'];
        $user['bind_info'] = serialize(array('openid' => $userinfo['openid'], 'keyname' => $user['username'], 'sex' => $userinfo['sex'], 'city' => $userinfo['city'], 'province' => $userinfo['province'], 'country' => $userinfo['country'], 'headimgurl' => $userinfo['headimgurl']));
        $user['access_token'] = $access_token;
        session('members_bind_info', $user);
        cookie('members_bind_info', $user);
        return array('state' => 1, 'tip' => '用户信息获取成功！', 'data' => $user);
    }
    public function https_request($url, $data = null) {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        } else {
            return false;
        }
    }

    /**
     * 构建模板消息
     */
    public function build_tpl_msg($data) {
        $access_token = self::get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
        $result = self::https_request($url, $data);
        return json_decode($result, true);
    }

    /**
     * 错误
     */
    public function getError() {
        return self::_error;
    }
    /**
     * 构建小程序订阅模板消息
     */
    public function build_tpl_submsg($data) {
        $sub_access_token = self::get_access_subtoken();
        $url ="https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$sub_access_token;
        $result = self::https_request($url, $data);
        return json_decode($result, true);
    }
    /*
    * 获取小程序access_token
    */
    public function get_access_subtoken($length = 6, $reset = false) {
        $sub_access_token = S('sub_access_token');
        if ($sub_access_token && !$reset) return $sub_access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . C('qscms_weixinapp_appid') . "&secret=" . C('qscms_weixinapp_appsecret');
        $result = self::https_request($url);
        $jsoninfo = json_decode($result, true);
        $sub_access_token = $jsoninfo["access_token"];
        //更新数据
        S('sub_access_token', $sub_access_token, 7200);
        return $sub_access_token;
    }
    /**
     * 构建百度小程序订阅模板消息
     */
    public function build_tpl_baidusubmsg($data) {
        $baidusub_access_token = self::get_access_baidusubtoken();
        $url ="https://openapi.baidu.com/rest/2.0/smartapp/template/send?access_token=".$baidusub_access_token;
        $result = self::https_request($url, $data);
        return json_decode($result, true);
    }
    /*
    * 获取百度小程序access_token
    */
    public function get_access_baidusubtoken($length = 6, $reset = false) {
        $baidusub_access_token = S('baidusub_access_token');
        if ($baidusub_access_token && !$reset) return $baidusub_access_token;
        $url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=uwx4iduAvYcwIiVeHHz5BnZqeD8yn4KA&client_secret=LfoKIuaf0pxQqYqcLVzD6iKaaLUDGUu5&scope=smartapp_snsapi_base";
        $result = self::https_request($url);
        $jsoninfo = json_decode($result, true);
        $baidusub_access_token = $jsoninfo["access_token"];
        //更新数据
        S('baidusub_access_token', $baidusub_access_token, 7200);
        return $baidusub_access_token;
    }
}

