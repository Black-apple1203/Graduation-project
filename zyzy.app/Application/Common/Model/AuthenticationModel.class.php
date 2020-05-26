<?php 
namespace Common\Model;
use Think\Model;
class AuthenticationModel extends Model
{
    public function add_auth_info($mobile){
        $info = $this->where(array('mobile'=>$mobile))->find();
        if(!$info){
            $data['mobile'] = $mobile;
            $data['secretkey'] = self::_randstr(16);
            $info = $data;
            $info['id'] = $this->add($data);
        }
        return $info;
    }
    private function _randstr($length=6)
    {
        $hash='';
        $chars= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'; 
        $max=strlen($chars)-1;   
        mt_srand((double)microtime()*1000000);   
        for($i=0;$i<$length;$i++)   {   
            $hash.=$chars[mt_rand(0,$max)];   
        }   
        return $hash;
    }
}
?>