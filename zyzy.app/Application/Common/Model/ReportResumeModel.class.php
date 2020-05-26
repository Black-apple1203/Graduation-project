<?php
namespace Common\Model;
use Think\Model;
class ReportResumeModel extends Model {
    public $type_arr = array(1 => '广告简历（以宣传、买卖为目的的简历）', 2 => '无意义简历（乱写、乱填）');
    protected $_validate = array(
        array('uid,resume_id,resume_realname,resume_addtime,report_type,content', 'identicalNull', '', 0, 'callback'),
        array('uid,resume_id,resume_addtime,report_type', 'identicalEnum', '', 0, 'callback'),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('audit', 1),
    );

    public function add_report($data, $user) {
        if ($this->check_resume_report($user['uid'], $data['resume_id'])) {
            return array('state' => 0, 'msg' => '您已经举报过此简历！');
        }
        $resume_info = D('Resume')->find($data['resume_id']);
        if ($resume_info) {
            if ($resume_info['display_name'] == "2") {
                $data['resume_realname'] = "N" . str_pad($resume_info['id'], 7, "0", STR_PAD_LEFT);
            } elseif ($resume_info['display_name'] == "3") {
                if ($resume_info['sex'] == 1) {
                    $data['resume_realname'] = cut_str($resume_info['fullname'], 1, 0, "先生");
                } elseif ($resume_info['sex'] == 2) {
                    $data['resume_realname'] = cut_str($resume_info['fullname'], 1, 0, "女士");
                }
            } else {
                $data['resume_realname'] = $resume_info['fullname'];
            }
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];
            $data['resume_addtime'] = $resume_info['addtime'];
            $data['audit'] = 1;
            $data['addtime'] = time();
            $data['content'] = '';
            $insert = $this->add($data);
            if ($insert) {
                /* 会员日志 */
                write_members_log($user, '', '投诉简历（简历id：' . $data['resume_id'] . '）');
                //检测加入黑名单
                if (C('apply.Allowance')) {
                    if (false === $config = F('allowance_config')) {
                        $config = D('Allowance/AllowanceConfig')->config_cache();
                    }
                    if ($config['report_resume_times_setblack'] != '0') {
                        $count = $this->where(array('resume_id' => $data['resume_id']))->count();
                        if ($count >= $config['report_resume_times_setblack']) {
                            $deadline = $config['blacklist_time_limit'] == 0 ? 0 : strtotime('+' . $config['blacklist_time_limit'] . ' days');
                            D('Allowance/AllowanceBlacklist')->add(array('uid' => $resume_info['uid'], 'robot' => 2, 'deadline' => $deadline, 'utype' => 2));
                        }
                    }
                }
                return array('state' => 1, 'msg' => '投诉成功！请等待管理员核实！');
            } else {
                return array('state' => 0, 'msg' => '投诉失败！');
            }
        } else {
            return array('state' => 0, 'msg' => '简历不存在！');
        }
    }

    public function check_resume_report($uid, $resume_id) {
        $log = $this->where(array('uid' => $uid, 'resume_id' => $resume_id))->find();
        if ($log) {
            return true;
        } else {
            return false;
        }
    }

    public function report_audit($id, $audit, $rid) {
        if (!is_array($id)) $id = array($id);
        $return = 0;
        $sqlin = implode(",", $id);
        $sqlrin = implode(",", $rid);
        if (preg_match("/^(\d{1,10},)*(\d{1,10})$/", $sqlin)) {
            $return = $this->where(array('id' => array('in', $sqlin)))->setField('audit', intval($audit));
            if ($return > 0) {
                //发送站内信
                $result = $this->where(array('id' => array('in', $sqlin)))->select();
                foreach ($result as $key => $list) {
                    $user_info = D('Members')->get_user_one(array('uid' => $list['uid']));
                    $timestring = date("Y年m月d日", time());

                    // 简历信息  会员信息
                    $resumeurl = url_rewrite('QS_resumeshow', array('id' => $list['resume_id']));
                    $resumeinfo = D('Resume')->find(intval($list['resume_id']));
                    if (!$resumeinfo) {
                        continue;
                    }
                    $user_info_per = D('Members')->get_user_one(array('uid' => $resumeinfo['uid']));
                    // 若属实
                    if ($audit == 2) {
                        $r = D('TaskLog')->do_task($user_info, 'report_resume');

                        $msg_p = "，奖励" . $r['data'] . C('qscms_points_byname') . "，感谢您对" . C('qscms_site_name') . "的支持！";
                        $message = "您于" . $timestring . "举报的简历：【<a href=\"{$resumeurl}\" target=\"_blank\">{$list['resume_realname']}</a>】，经平台核实情况属实" . $msg;
                        D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $message,$user_info['utype']);

                        $message_c = "您发布的简历【" . $resumeinfo['title'] . "】于" . $timestring . "被举报，经平台核实情况属实，请尽快处理，如再有此类情况发生将作封号处理！";
                        D('Pms')->write_pmsnotice($user_info_per['uid'], $user_info_per['username'], $message_c,2);
                    } else {
                        $message = "您于" . $timestring . "举报的简历：【<a href=\"{$resumeurl}\" target=\"_blank\">{$list['resume_realname']}</a>】，经平台核实情况不属实";
                        D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $message,$user_info['utype']);
                    }
                }
            }
        }
        return $return;
    }
}

?>