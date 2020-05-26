<?php

namespace Admin\Controller;

use Common\Controller\BackendController;

class ResumeDownController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->_name = 'CompanyDownResume';

        $db_pre = C('DB_PREFIX');
        $table_name = $db_pre . 'company_down_resume';

        $joinsql[] = 'join ' . $db_pre . "company_profile as c on " . $table_name . ".company_uid=c.uid";
        $joinsql[] = 'join ' . $db_pre . "resume as r on " . $table_name . ".resume_id=r.id";
        $this->join = $joinsql;

        $this->field = $table_name . '.*,c.id as company_id,r.fullname,r.birthdate,r.sex_cn,r.education_cn,r.experience_cn,r.photo_img';

        parent::index();
    }

    protected function _before_search($map) {
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $map['company_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $map['fullname'] = array('like', '%' . $key . '%');
                    break;
            }
        }
        return $map;
    }

    protected function _custom_fun($list) {
        return array_map(function ($val) {
            $val['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume_id']));
            $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
            return $val;
        }, $list);
    }
}