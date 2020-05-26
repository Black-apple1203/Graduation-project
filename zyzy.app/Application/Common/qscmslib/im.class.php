<?php
/**
 * 第三方即时通讯
 *
 * @author andery
 */
namespace Common\qscmslib;
class im {
    private $_error = '';
    public function __construct() {
        $this->_appKey = C('qscms_im_appkey');
        $this->_appSecret = C('qscms_im_appsecret');
    }
    public function get_user_info($sendUser){
        if(!C('visitor.uid')){
            $this->_error = '请登录帐号！';
            return false;
        }
        $userInfo = C('visitor');
        $userInfo['im_token'] = $this->token($userInfo);
        $userInfo['im_access_token'] = $this->accesstoken();
        $sendUser && $this->token($sendUser);
        $userInfo['uid'] .= '_'.$userInfo['utype'];
        return $userInfo;
    }
    /**
     * [accesstoken 获取accesstoken]
     */
    public function accesstoken(){
        $im_access_token=S('im_access_token');
        if($im_access_token) return $im_access_token;
        $result = https_request('http://im.74cms.com/accesstoken',array('app_key'=>$this->_appKey,'app_secret'=>$this->_appSecret));
        if(!$jsoninfo = json_decode($result, true)) return false;
        if(!$jsoninfo['status'] || !$jsoninfo['data']['access_token']) return false;
        //更新数据
        S('im_access_token',$jsoninfo['data']['access_token'],$jsoninfo['data']['token_createtime']?:7200);
        return $jsoninfo['data']['access_token'];
    }
    public function token($user){
        if(!$user['utype']){
            $this->_error = '用户类型错误！';
            return false;
        }
        $uid = $user['uid'];
        $user['uid'] .= '_' . $user['utype'];
        $userT = M('ImToken')->where(array('uid'=>$user['uid']))->find();
        if(!$userT || !$userT['token']){
            $data['uid'] = $user['uid'];
            $data['nickname'] = $user['username'];
            if(!$user['refresh']){
                if($user['utype'] == 1){
                    $avatars = M('CompanyProfile')->where(array('uid'=>$uid))->getfield('logo');
                    $user['avatars'] = $avatars ? attach($avatars,'company_logo') : attach('no_logo.png','resource');
                }else{
                    if ($user['avatars']) {
                        $user['avatars'] = attach($user['avatars'], 'avatar');
                    } else {
                        $sex = D('Resume')->where(array('uid' => $uid, 'def' => 1))->getfield('sex');
                        $avatar_default = $sex == 1 ? 'no_photo_male.png' : 'no_photo_female.png';
                        $user['avatars'] = attach($avatar_default, 'resource');
                    }
                }
            }
            $data['avatar'] = $user['avatars'];
            $data['access_token'] = $this->accesstoken();
            if(!$data['access_token']) return false;
            $reg = https_request('http://im.74cms.com/usertoken',$data);
            $reg = json_decode($reg,true);
            if($reg['status'] == 1 && $token = $reg['data']){
                if($userT){
                    M('ImToken')->where(array('uid'=>$user['uid']))->setfield('token',$token);
                }else{
                    M('ImToken')->add(array('uid'=>$user['uid'],'token'=>$token));
                }
            }else{
                return false;
            }
        }else{
            $token = $userT['token'];
        }
        return $token;
    }
    public function refresh($uid){
        if(!$uid){
            $this->_error = '请选择用户！';
            return false;
        }
        if(!$user = M('Members')->find($uid)){
            $this->_error = '用户不存在！';
            return false;
        }
        if(!$user['utype']){
            $this->_error = '用户类型错误！';
            return false;
        }
        if($user['utype'] == 1){
            $company = M('CompanyProfile')->field('companyname,logo')->where(array('uid'=>$uid))->find();
            $user['avatar'] = $company['logo'] ? attach($company['logo'],'company_logo') : attach('no_logo.png','resource');
            $company['companyname'] && $user['username'] = $company['companyname'];
        }else{
            $resume = D('Resume')->field('sex,fullname,photo_img')->where(array('uid' => $uid, 'def' => 1))->find();
            if($resume['photo_img']){
                $user['avatar'] = attach($resume['photo_img'], 'avatar');
            }else{
                $avatar_default = $resume['sex'] == 1 ? 'no_photo_male.png' : 'no_photo_female.png';
                $user['avatar'] = attach($avatar_default, 'resource');
            }
            $resume['fullname'] && $user['username'] = $resume['fullname'];
        }
        $user['refresh'] = 1;
        $data['token'] = $this->token($user);
        $data['access_token'] = $this->accesstoken();
        $data['nickname'] = $user['username'];
        $data['avatar'] = $user['avatar'];
        $reg = https_request('http://im.74cms.com/Userinfoupdate/index',$data);
        $reg = json_decode($reg,true);
        if($reg['status'] == 1){
            return true;
        }else{
            $this->_error = $reg['msg'];
            return false;
        }
    }
    /**
     * 错误
     */
    public function getError(){
        return $this->_error;
    }
}