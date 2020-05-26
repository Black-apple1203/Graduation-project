<?php 
namespace Common\Model;
use Think\Model;
class ImUserModel extends Model{
	protected $_auto = array (
		array('addtime','time',1,'function'),
		array('updatetime','time',3,'function')
	);
	public function get_user_info($sendUser){
		if(!C('visitor.uid')) return array('state'=>0,'error'=>'请登录帐号！');
		$userInfo = C('visitor');
        $im = new \Common\qscmslib\im();
        $userInfo['im_token'] = $im->token($userInfo);
		$userInfo['im_access_token'] = $im->accesstoken();
		$sendUser && $im->token($sendUser);
		return $userInfo ? array('state'=>1,'user'=>$userInfo) : array('state'=>0,'error'=>'用户信息获取失败！');
	}
}
?>