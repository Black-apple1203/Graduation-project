<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class videoInterviewController extends BackendController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {

        $this->_name = 'videoInterview';

        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre . 'video_interview';
        $this->field = $table_name . '.*,r.fullname,r.id as resume_id,c.companyname,c.id as company_id';
        $joinsql[0] = ' left join ' . $db_pre . "resume as r on " . $table_name . ".personal_uid=r.uid";
        $joinsql[1] = ' left join ' . $db_pre . "company_profile as c on " . $table_name . ".company_uid=c.uid";
        $this->join = $joinsql;
        parent::index();
    }

    protected function _before_search($map)
    {
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:  // 公司名
                    $map['c.companyname'] = array('like', '%' . $key . '%');
                    break;
                case 2:  // 公司ID
                    $map['c.id'] = intval($key);
                    break;
                case 3:  // 职位名
                    $map[C('DB_PREFIX') . 'video_interview.jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 4:  // 职位ID
                    $map[C('DB_PREFIX') . 'video_interview.jobs_id'] = intval($key);
                    break;
                case 5:  // 简历姓名
                    $map['r.fullname'] = array('like', '%' . $key . '%');
                    break;
            }
        }
        return $map;
    }

    protected function _custom_fun($list)
    {
        return array_map(function ($val) {
            if ($val['deadline'] < time()) {
                $val['room_status'] = 'overtime';
            } else {
                $interview_daytime = strtotime(date('Y-m-d', $val['interview_time']));
                if (time() < $interview_daytime) {
                    $val['room_status'] = 'nostart';
                } else {
                    $val['room_status'] = 'opened';
                }
            }
            $val['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume_id']));
            $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
            $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));

            $tip = array();
            $tip[] = '联系人：' . $val['contact'];
            $tip[] = '联系方式：' . $val['contact_tel'];
            $val['interview_tip'] = implode('<br />', $tip);
            return $val;
        }, $list);
    }
    public function config()
    {
        if (IS_POST) {
            foreach (I('post.') as $key => $val) {
                D('Config')->where(array('name' => $key))->save(array('value' => $val));
            }
            $this->ajaxReturn(1, '保存成功');
        } else {
            $info['video_interview_open'] = C('qscms_video_interview_open');
            $info['trtc_appid'] = C('qscms_trtc_appid');
            $info['trtc_appsecret'] = C('qscms_trtc_appsecret');
            $this->assign('info', $info);
            $this->display();
        }
    }
}
