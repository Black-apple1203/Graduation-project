<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class CompanyInterviewController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->_name = 'CompanyInterview';

        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre . 'company_interview';
        $this->join = 'join ' . $db_pre . "resume as r on " . $table_name . ".resume_id=r.id";
        $this->field = $table_name . '.*,r.fullname,r.birthdate,r.sex_cn,r.education_cn,r.experience_cn,r.photo_img';

        parent::index();
    }

    protected function _before_search($map) {
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:  // 公司名
                    $map['company_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:  // 公司ID
                    $map['company_id'] = intval($key);
                    break;
                case 3:  // 职位名
                    $map['jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 4:  // 职位ID
                    $map['jobs_id'] = intval($key);
                    break;
                case 5:  // 简历姓名
                    $map['fullname'] = array('like', '%' . $key . '%');
                    break;
            }
        }
        return $map;
    }

    protected function _custom_fun($list) {
        return array_map(function ($val) {
            $tip = array();
            $tip[] = '联系人：' . $val['contact'];
            $tip[] = '联系方式：' . $val['telephone'];
            $tip[] = '面试地点：' . $val['address'];

            $val['interview_tip'] = implode('<br />', $tip);
            $val['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume_id']));
            $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
            $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['jobs_id']));

            return $val;
        }, $list);
    }
}