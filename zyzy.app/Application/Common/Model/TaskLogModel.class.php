<?php
namespace Common\Model;

use Think\Model;

class TaskLogModel extends Model {
    protected $_validate = array(
        array('uid,taskid', 'identicalNull', '', 0, 'callback'),
        array('uid,taskid,points,once', 'identicalEnum', '', 0, 'callback'),
    );
    protected $_auto = array(
        array('addtime', 'today_time', 1, 'callback'),
    );

    protected function today_time() {
        return strtotime('today');
    }


    /**
     * 添加任务记录
     */
    public function do_task($userinfo, $alias) {
        $task_info = D('Task')->get_task_cache($userinfo['utype'], $alias);
        if ($task_info) {
            return $this->_do_task($userinfo['uid'], $task_info);
        } else {
            return array('state' => 0, 'msg' => '没有找到对应的任务');
        }
    }

    protected function _do_task($uid, $task_info) {
        if ($task_info['once'] == 1) {//单次任务
            //查询是否完成过该任务
            $map['uid'] = array('eq', $uid);
            $map['taskid'] = array('eq', $task_info['id']);
            $log = $this->where($map)->find();
            if ($log === NULL) {
                $result = $this->add_task($uid, $task_info);
                return $result;
            } else {
                return array('state' => 0, 'msg' => '已经完成过这个任务了');
            }
        } else {//日常任务
            //查询今天完成过该任务的次数
            $map['uid'] = array('eq', $uid);
            $map['taskid'] = array('eq', $task_info['id']);
            $map['addtime'] = array('eq', strtotime('today'));
            $log_time = $this->where($map)->count();
            if ($log_time < $task_info['times'] || $task_info['times'] == -1) {
                $result = $this->add_task($uid, $task_info);
                return $result;
            } else {
                return array('state' => 0, 'msg' => '完成次数达到限额');
            }
        }
    }

    protected function add_task($uid, $task_info) {
        $data['uid'] = $uid;
        $data['taskid'] = $task_info['id'];
        $data['points'] = $task_info['points'];
        $data['once'] = $task_info['once'];
        $mod = $this->create($data);
        if (false === $mod) {
            return array('state' => 0, 'msg' => $mod->getError());
        } else {
            $insertid = $this->add();
            if ($insertid) {
                D('MembersPoints')->report_deal($uid, 1, $task_info['points']);
                $handsel['uid'] = $uid;
                $handsel['htype'] = 'task_' . $task_info['t_alias'];
                $handsel['htype_cn'] = $task_info['title'];
                $handsel['operate'] = 1;
                $handsel['points'] = $task_info['points'];
                $handsel['addtime'] = time();
                D('MembersHandsel')->members_handsel_add($handsel);
                return array('state' => 1, 'msg' => '操作成功', 'data' => $task_info['points']);
            } else {
                return array('state' => 0, 'msg' => '操作失败');
            }
        }
    }

    /**
     * 统计任务 - 今天获得的积分和剩余可获得积分
     */
    public function count_task_points($uid, $utype) {
        $count = array();
        $map['uid'] = array('eq', $uid);
        $map['addtime'] = array('eq', strtotime('today'));
        //今天已获得的积分
        $count[0] = $this->where($map)->sum('points');
        //全部任务
        $all_task = D('Task')->get_task_cache($utype);
        //找出所有的单次任务
        $single_task_id = $loop_task_id = array();
        //找出所有的日常任务
        $loop_task_id = array();
        foreach ($all_task as $key => $value) {
            if ($value['becount'] == 1) {
                if ($value['once'] == 1) {
                    $single_task_id[] = $key;
                } else {
                    $loop_task_id[] = $key;
                }
            } else {
                $becount[] = $key;
            }
        }
        //已完成的单次任务
        if (!empty($single_task_id)) {
            $once = $this->where(array('uid' => array('eq', $uid), 'taskid' => array('in', $single_task_id)))->getField('taskid', true);
        } else {
            $once = false;
        }

        //未完成的单次任务
        if ($once) {
            $result = array_diff($single_task_id, $once);
        } else {
            $result = $single_task_id;
        }

        $count[1] = 0;
        foreach ($result as $key => $value) {
            $count[1] += $all_task[$value]['points'];
        }

        //已完成的日常任务
        if (!empty($loop_task_id)) {
            $loop = $this->where(array('uid' => array('eq', $uid), 'taskid' => array('in', $loop_task_id), 'addtime' => array('eq', strtotime('today'))))->getField('taskid', true);
        } else {
            $loop = array();
        }
        //计算已完成的日常任务分别已完成多少次
        $loop_count = array();
        foreach ($loop as $key => $value) {
            if (isset($loop_count[$value])) {
                $loop_count[$value]++;
            } else {
                $loop_count[$value] = 1;
            }
        }
        $count[2] = 0;
        foreach ($loop_task_id as $key => $value) {
            if (isset($loop_count[$value])) {
                $count[2] += ($all_task[$value]['times'] - $loop_count[$value]) * $all_task[$value]['points'];
            } else {
                $count[2] += $all_task[$value]['points'] * $all_task[$value]['times'];
            }
        }
        foreach ($becount as $key => $value) {
            if ($all_task[$value]['times'] == '-1') {
                $count[2] += $all_task[$value]['points'];
            }
        }
        return array('obtain' => intval($count[0]), 'able' => intval($count[1] + $count[2]));
    }

    /**
     * 获取已完成任务
     */
    public function get_done_task($uid, $utype = 2) {
        //已完成的单次任务
        $return = array();
        $once = $this->where(array('uid' => array('eq', $uid), 'once' => array('eq', 1)))->getField('taskid', true);
        foreach ($once as $key => $value) {
            $return[$value] = 0;
        }
        //已完成的日常任务
        $loop = $this->where(array('uid' => array('eq', $uid), 'once' => array('eq', 0), 'addtime' => array('eq', strtotime('today'))))->getField('taskid', true);
        //计算已完成的日常任务分别已完成多少次
        $loop_count = array();
        foreach ($loop as $key => $value) {
            if (isset($loop_count[$value])) {
                $loop_count[$value]++;
            } else {
                $loop_count[$value] = 1;
            }
        }
        $all_task = D('Task')->get_task_cache($utype);
        $loop_task_id = array();
        foreach ($all_task as $key => $value) {
            if ($value['once'] == 0) {
                $loop_task_id[] = $key;
            }
        }
        /**
         * 注意：此处算出的已完成的日常任务并非是全部完成的，而只是今天执行过的
         */
        foreach ($loop_task_id as $key => $value) {
            //如果times=-1，永远是未完成
            // if($all_task[$value]['times']==-1){
            // 	continue;
            // }
            if (isset($loop_count[$value])) {
                if ($value == 3 || $value == 18) {
                    $return[$value] = 0;
                } else {
                    $return[$value] = $loop_count[$value];
                }
            }
        }
        return $return;
    }
}

?>