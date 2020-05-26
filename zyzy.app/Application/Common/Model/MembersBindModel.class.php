<?php
namespace Common\Model;
use Think\Model;
class MembersBindModel extends Model{
	public function get_members_bind($where){
		$user = $this->where($where)->find();
		if($user && $user['uid'] && $user['is_bind'] && $user['is_focus'] && $user['openid']){
			return $user;
		}
		return false;
	}
}
?>