<?php 
namespace Common\Model;
use Think\Model;
class ShareAllowanceModel extends Model{
	protected $_validate = array(
		array('uid,company_id,jobs_id,jobs_name,oid,amount,count,task_views,payment,payable,pay_amount','identicalNull','',0,'callback'),
        array('uid,company_id,jobs_id,count,task_views','identicalEnum','',0,'callback'),
        array('amount','_amount','{%share_allowance_amount_min_error}',0,'callback')
	);
    protected $_auto = array(
        array('addtime','time',1,'function'),
        array('status', 1),
    );
    protected function _amount($data){
        if(intval($data * 100) / 100 < 1) return false;
        return true;
    }
    /**
     * [share_allowance 发布职位分享红包]
     */
    public function share_allowance($data){
        if(!in_array($data['payment'],array('wxpay','alipay'))) return array('state'=>0,'msg'=>'请正确选择支付类型');
        $data['pay_amount'] = $data['amount'] * $data['count'];
        $data['payable'] = $data['pay_amount'];
        $data['oid'] = strtoupper(substr($data['payment'],0,1)).'-SHARE'.'-'.date('ymd',time())."-".date('His',time());
        if(false === $this->create($data)){
            return array('state'=>0,'msg'=>$this->getError());
        }elseif(!$id = $this->add()){
            return array('state'=>0,'msg'=>'职位分享红包开启失败');
        }else{
            return array('state'=>1,'msg'=>'职位分享红包开启成功','data'=>$id);
        }
    }
    /**
     * [set_share_allowance 确认职位分享红包收款]
     */
    public function set_share_allowance($oid){
        if($allowance = $this->where(array('oid'=>$oid,'pay_status'=>0))->find()){
            if($s = $this->where(array('id'=>$allowance['id']))->setfield(array('pay_status'=>1,'pay_time'=>time()))){
                $reg = D('Jobs')->jobs_setfield(array('id'=>$allowance['jobs_id']),array('share_allowance'=>1));
                if($reg['state']){
                    D('WeixinTplMsg')->set_share_allowance_publish($allowance['uid'], $allowance['jobs_name'], $allowance['amount'], $allowance['count']);
                    return array('state'=>1,'msg'=>'职位分享红包开启成功');
                }
            }
            return array('state'=>0,'msg'=>'职位分享红包开启失败');
        }else{
            return array('state'=>0,'msg'=>'职位分享红包不存在');
        }
    }
    public function get_share_allowance($user){
        $where = array('uid'=>$user['uid'],'pay_status'=>1);
        $count = $this->where($where)->count('id');
        $pager =  pager($count, $pagesize);
        $rst['list'] = array();
        if($count){
            if($rst['list'] = $this->where($where)->limit($pager->firstRow . ',' . $pager->listRows)->select()){
                foreach($rst['list'] as $val){
                    $ids[] = $val['id'];
                }
                $partake = M('ShareAllowancePartake')->where(array('sid'=>array('in',$ids)))->getfield('id,sid,uid');
                foreach($partake as $val){
                    $partakes[$val['sid']][] = array('uid'=>$val['uid']);
                    $uids[] = $val['uid'];
                }
                if($uids = array_flip(array_flip($uids))){
                    $userInfo = M('Members')->where(array('uid'=>array('in',$uids)))->getfield('uid,username,avatars');
                }
                foreach($rst['list'] as $key=>$val){
                    $rst['list'][$key]['share'] = count($partakes[$val['id']]);
                    foreach($partakes[$val['id']] as $k=>$v){
                        $partakes[$val['id']][$k]['username'] = $userInfo[$v['uid']]['username'];
                        $partakes[$val['id']][$k]['avatars'] = $userInfo[$v['uid']]['avatars']?attach($userInfo[$v['uid']]['avatars'],'avatar'):attach('no_photo_male.png','resource');
                    }
                    $rst['list'][$key]['partakeList'] = $partakes[$val['id']];
                }
            }
        }
        $rst['count'] = $count;
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }
}
?>