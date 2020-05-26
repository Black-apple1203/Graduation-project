<?php 
namespace Common\Model;
use Think\Model;
class ShareAllowancePartakeModel extends Model{
	protected $_validate = array(
		array('sid,uid,jobs_name,amount,task_views','identicalNull','',0,'callback'),
        array('sid,uid,task_views', 'identicalEnum', '', 2, 'callback')
	);
    protected $_auto = array (
        array('addtime', 'time', 1, 'function'),
        array('pay_amount',0),
        array('service_charge',0),
        array('status', 0)
    );
    /**
     * [set_partake 个人分享职位]
     */
    public function set_partake($data,$user){
        $data['uid'] = $data['uid']?:$user['uid'];
        $data['deadline'] = time() + C('qscms_share_allowance_deadline') * 86400;
        if(false === $this->create($data)){
            return array('state'=>0,'msg'=>$this->getError());
        }elseif(!$id = $this->add()){
            return array('state'=>0,'msg'=>'分享失败');
        }else{
            return array('state'=>1,'msg'=>'分享成功','data'=>array('id'=>$id));
        }
    }
    public function get_partake($completion_status,$pay_status,$user){
        if($completion_status){
            $completion_status = intval($completion_status);
            switch($completion_status){
                case 0:
                    $where['status'] = 0;
                    $where['deadline'] = array('gt',time());
                    break;
                case 1:
                    $where['status'] = 1;
                    break;
                case 2:
                    $where['status'] = 2;
                    break;
                case 3:
                    $where['status'] = 0;
                    $where['deadline'] = array('lt',time());
                    break;
            }
        }
        if($pay_status){
            $pay_status = intval($pay_status);
            switch($pay_status){
                case 1:
                    $where['pay_status'] = 1;
                    break;
                case 3:
                    $where['pay_status'] = 3;
                    break;
                default:
                    $where['pay_status'] = array('in',array(0,2));
            }
        }
        $where['uid'] = $user['uid'];
        $count = $this->where($where)->count('id');
        $pager =  pager($count, $pagesize);
        $rst['list'] = array();
        if($count){
            if($rst['list'] = $this->where($where)->limit($pager->firstRow . ',' . $pager->listRows)->select()){
                foreach($rst['list'] as $val){
                    $ids[] = $val['id'];
                }
                $view = M('ShareAllowanceView')->field('pid,openid,username,avatars,addtime')->where(array('pid'=>array('in',$ids)))->select();
                foreach($view as $val){
                    $pid = $val['pid'];
                    unset($val['pid']);
                    $views[$pid][] = $val;
                }
                foreach($rst['list'] as $key=>$val){
                    $rst['list'][$key]['jobs_url'] = url_rewrite('QS_jobsshow',array('id'=>$val['jobs_id']));
                    $rst['list'][$key]['views'] = count($views[$val['id']]);
                    $rst['list'][$key]['viewList'] = $views[$val['id']];
                    switch($val['status']){
                        case '1':
                            $rst['list'][$key]['completion_status_cn'] = '已完成';
                            $rst['list'][$key]['completion_status'] = 1;
                            break;
                        case '2':
                            $rst['list'][$key]['completion_status_cn'] = '已停止';
                            $rst['list'][$key]['completion_status'] = 2;
                            break;
                        default:
                            if($val['deadline'] < time()){
                                $rst['list'][$key]['completion_status_cn'] = '已过期';
                                $rst['list'][$key]['completion_status'] = 3;
                            }else{
                                $day = ($val['deadline'] - time()) / 86400;
                                $day = $day < 1 ? '今天' : intval($day).'天后';
                                $rst['list'][$key]['completion_status_cn'] = '分享中('.$day.'到期)';
                                $rst['list'][$key]['completion_status'] = 0;
                            }
                            break;
                    }
                    switch($val['pay_status']){
                        case '0':
                            $rst['list'][$key]['pay_status_cn'] = '未发放';
                            break;
                        case '1':
                            $rst['list'][$key]['pay_status_cn'] = '已发放';
                            break;
                        case '2':
                            $rst['list'][$key]['pay_status_cn'] = '未发放';
                            break;
                        case '3':
                            $rst['list'][$key]['pay_status_cn'] = '来晚了红包已发完';
                            break;
                    }
                }
            }
        }
        $rst['count'] = $count;
        $rst['page'] = $pager->fshow();
        $rst['page_params'] = $pager->get_page_params();
        return $rst;
    }
    public function share_allowance_partake_del($ids){
        if (!is_array($ids)) $ids=array($ids);
        $sqlin=implode(",",$ids);
        if (!preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin)) return array('state'=>0,'error'=>'删除id错误！');
        $where['id']=array('in',$sqlin);
        $where['uid']=C('visitor.uid');
        $num = $this->where($where)->delete();
        if (false===$num) return array('state'=>0,'error'=>'删除失败！');
        //写入会员日志
        foreach (explode(",", $sqlin) as $k => $v) {
            write_members_log(C('visitor'), '', '删除职位分享红包任务（任务id：' . $v . '）');
        }
        return array('state'=>1,'num'=>$num);
    }
}
?>