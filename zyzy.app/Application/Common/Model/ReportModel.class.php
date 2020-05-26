<?php
namespace Common\Model;

use Think\Model;

class ReportModel extends Model {
    public $type_arr = array(1 => '电话虚假(如空号、无人接听)', 2 => '职介收费', 3 => '虚假(如职位、待遇等虚假)', 4 => '涉黄违法', 5 => '网赚虚假(刷钻、刷信誉欺诈)', 6 => '职介冒充', 7 => '其他');
    protected $_validate = array(
        array('uid,jobs_id,jobs_name,jobs_addtime,report_type,content,telephone,content', 'identicalNull', '', 0, 'callback'),
        array('uid,jobs_id,jobs_addtime,report_type', 'identicalEnum', '', 0, 'callback'),
        array('telephone', '0,60', '{%report_length_error_telephone}', 0, 'length'),
        array('telephone', '_tel', '{%report_tel}', 0, 'callback'),
        array('content', '0,200', '{%report_length_error_content}', 0, 'length'),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('audit', 1),
    );

    protected function _tel($data) {
        if (!fieldRegex($data, 'tel') && !fieldRegex($data, 'mobile')) return false;
        return true;
    }

    public function add_report($data, $user) {
        if ($this->check_jobs_report($user['uid'], $data['jobs_id'])) {
            return array('state' => 0, 'msg' => '您已经举报过此职位！');
        }
        $jobs_info = D('Jobs')->find($data['jobs_id']);
        if ($jobs_info) {
            $data['uid'] = $user['uid'];
            $data['username'] = $user['username'];
            $data['jobs_name'] = $jobs_info['jobs_name'];
            $data['jobs_addtime'] = $jobs_info['addtime'];
            $data['audit'] = 1;
            $data['addtime'] = time();
            if (false === $this->create($data)) return array('state' => 0, 'msg' => $this->getError());
            if (false === $insert = $this->add()) return array('state' => 0, 'msg' => '举报职位失败！');
            if ($insert) {
                /* 会员日志 */
                write_members_log($user, '', '投诉职位（职位id：' . $data['jobs_id'] . '）');
                //检测加入黑名单
                if (C('apply.Allowance')) {
                    if (false === $config = F('allowance_config')) {
                        $config = D('Allowance/AllowanceConfig')->config_cache();
                    }
                    if ($config['report_jobs_times_setblack'] != '0') {
                        $count = $this->where(array('jobs_id' => $data['jobs_id']))->count();
                        if ($count >= $config['report_jobs_times_setblack']) {
                            $deadline = $config['blacklist_time_limit'] == 0 ? 0 : strtotime('+' . $config['blacklist_time_limit'] . ' days');
                            D('Allowance/AllowanceBlacklist')->add(array('uid' => $jobs_info['uid'], 'robot' => 2, 'deadline' => $deadline, 'utype' => 1));
                        }
                    }
                }
                return array('state' => 1, 'msg' => '投诉成功！请等待管理员核实！');
            } else {
                return array('state' => 0, 'msg' => '投诉失败！');
            }
        } else {
            return array('state' => 0, 'msg' => '职位不存在！');
        }
    }

    public function check_jobs_report($uid, $jobs_id) {
        $log = $this->where(array('uid' => $uid, 'jobs_id' => $jobs_id))->find();
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
                    // 职位信息 企业会员信息
                    $jobsinfo = D('Jobs')->get_jobs_one(array('id' => intval($list['jobs_id'])));
                    $jobsurl = url_rewrite('QS_jobsshow', array('id' => $list['jobs_id']));
                    if (!$jobsinfo) {
                        continue;
                    }
                    $user_info_com = D('Members')->get_user_one(array('uid' => $jobsinfo['uid']));
                    // 若属实
                    if ($audit == 2) {
                        $r = D('TaskLog')->do_task($user_info, 'report_jobs');
                        $msg_p = "，奖励" . $r['data'] . C('qscms_points_byname') . "，感谢您对" . C('qscms_site_name') . "的支持！";
                        $message = "您于" . $timestring . "举报企业【" . $jobsinfo['companyname'] . "】发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】,经平台核实情况属实" . $msg_p;
                        D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $message,2);

                        $message_c = "您发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】于" . $timestring . "被举报，经平台核实情况属实，请尽快处理，如再有此类情况发生将作封号处理！";
                        D('Pms')->write_pmsnotice($user_info_com['uid'], $user_info_com['username'], $message_c,1);
                    } else {
                        $message = "您于" . $timestring . "举报企业【" . $jobsinfo['companyname'] . "】发布的职位：【<a href=\"{$jobsurl}\" target=\"_blank\">{$list['jobs_name']}</a>】,经平台核实情况不属实";
                        D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $message,2);
                    }
                }
            }
        }
        return $return;
    }
}

?>