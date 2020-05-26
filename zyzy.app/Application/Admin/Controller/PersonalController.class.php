<?php
namespace Admin\Controller;

use Common\Controller\BackendController;

class PersonalController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * [index 简历列表]
     */
    public function index() {
        $this->_name = 'Resume';
        $this->sort = 'refreshtime';
        $key_type = I('request.key_type', 0, 'intval');
        $orderby_str = I('get.orderby', 'addtime', 'trim');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['fullname'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where['id'] = intval($key);
                    break;
                case 3:
                    $where['uid'] = intval($key);
                    break;
                case 4:
                    $where['telephone'] = array('like', '%' . $key . '%');
                    break;
                case 5:
                    $where['qq'] = array('like', '%' . $key . '%');
                    break;
                case 6:
                    $where['residence'] = array('like', '%' . $key . '%');
                    break;
            }
        } else {
            $tabletype = I('request.tabletype', 0, 'intval');
            $audit = $tabletype == 1 ? 0 : I('request.audit', '', 'trim');
            if (I('request.photo', 0, 'intval') || I('request.photo_display', 0, 'intval')) {
                $this->sort = 'addtime';
            }
            if ($addtimesettr = I('request.addtimesettr', 0, 'intval')) {
                $where['addtime'] = array('gt', strtotime("-" . $addtimesettr . " day"));
                $this->sort = 'addtime';
            }
            if ($settr = I('request.settr', 0, 'intval')) {
                $where['refreshtime'] = array('gt', strtotime("-" . $settr . " day"));
            }
            if ($photos = I('photos', '', 'intval')) {
                $photos == 1 && $where['photo_img'] = array('neq', '');
                $photos == 2 && $where['photo_img'] = array('eq', '');
            }
            if ($entrust = I('entrust', 0, 'intval')) {
                if ($entrust == -1) {
                    $where['entrust'] = array('gt', 0);
                } else {
                    $where['entrust'] = $entrust;
                }
            }
            if ($tabletype == 1) {
                $where['display'] = 1;
                //$where['audit'] = 1;
            } elseif ($tabletype == 2) {
                if ($audit != 3) {
                    if ($audit == '') {
                        $where['_string'] = '`display`=0 or `audit`=3';
                    } else {
                        $where['display'] = 0;
                    }
                }
            } elseif ($tabletype == 0) {
            }
        }
        $this->order = 'field(audit,2) desc,' . $orderby_str . ' desc,id desc';
        $this->where = $where;
        $this->custom_fun = '_format_resume_list';
        $this->_after_search_resume($tabletype);
        !$this->_tpl && $this->_tpl = 'index';
        parent::index();
    }

    /**
     * [index 简历列表]
     */
    public function top_resume() {
        $this->_name = 'AdvResume';
        $this->sort = 'addtime';
        $key_type = I('request.key_type', 0, 'intval');
        $orderby_str = I('get.orderby', 'addtime', 'trim');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['fullname'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where['id'] = intval($key);
                    break;
                case 3:
                    $where['uid'] = intval($key);
                    break;
                case 4:
                    $where['telephone'] = array('like', '%' . $key . '%');
                    break;
                case 5:
                    $where['qq'] = array('like', '%' . $key . '%');
                    break;
                case 6:
                    $where['residence'] = array('like', '%' . $key . '%');
                    break;
            }
        } else {
            $tabletype = I('request.tabletype', 0, 'intval');
            $audit = $tabletype == 1 ? 0 : I('request.audit', '', 'trim');
            if (I('request.photo', 0, 'intval') || I('request.photo_display', 0, 'intval')) {
                $this->sort = 'addtime';
            }
            if ($addtimesettr = I('request.addtimesettr', 0, 'intval')) {
                $where['addtime'] = array('gt', strtotime("-" . $addtimesettr . " day"));
                $this->sort = 'addtime';
            }
            if ($settr = I('request.settr', 0, 'intval')) {
                $where['refreshtime'] = array('gt', strtotime("-" . $settr . " day"));
            }
            if ('' != $education = I('get.education', 0, 'intval')) {
                $where['education'] = $education;
            }
            if ('' != $experience = I('get.experience', 0, 'intval')) {
                $where['experience'] = $experience;
            }
            if ($photos = I('photos', '', 'intval')) {
                $photos == 1 && $where['photo_img'] = array('neq', '');
                $photos == 2 && $where['photo_img'] = array('eq', '');
            }
            if ($entrust = I('entrust', 0, 'intval')) {
                if ($entrust == -1) {
                    $where['entrust'] = array('gt', 0);
                } else {
                    $where['entrust'] = $entrust;
                }
            }
            if ($tabletype == 1) {
                $where['display'] = 1;
                //$where['audit'] = 1;
            } elseif ($tabletype == 2) {
                if ($audit != 3) {
                    if ($audit == '') {
                        $where['_string'] = '`display`=0 or `audit`=3';
                    } else {
                        $where['display'] = 0;
                    }
                }
            } elseif ($tabletype == 0) {
            }
        }
        $this->cate_education = D('Category')->get_category_cache('QS_education');
        $this->cate_experience = D('Category')->get_category_cache('QS_experience');
        $this->order = 'field(audit,2) desc,' . $orderby_str . ' desc,id desc';
        $this->where = $where;
        $this->custom_fun = '_format_resume_list';
        $this->_after_search_resume($tabletype);
        !$this->_tpl && $this->_tpl = 'top_resume';
        parent::index();
    }

    protected function _is_resume($pid) {
        !$pid && $pid = I('request.id', 0, 'intval');
        if (!$pid) {
            IS_AJAX && $this->ajaxReturn(0, '请正确选择简历！');
            $this->error('请正确选择简历！');
        }
        //$field = 'id,uid,title,fullname,sex,nature,nature_cn,trade,trade_cn,birthdate,residence,height,marriage_cn,experience_cn,district_cn,wage_cn,householdaddress,education_cn,major_cn,tag,tag_cn,telephone,email,intention_jobs,photo_img,complete_percent,current,current_cn,word_resume';
        if (!$reg = M('AdvResume')->field()->where(array('id' => $pid))->find()) return false;
        $reg['height'] = $reg['height'] == 0 ? '' : $reg['height'];
        $this->assign('resume', $reg);
        return $reg;
    }

    /**
     * [高级简历基本资料]
     */
    public function save_basic_info() {
        $ints = array('display_name', 'sex', 'birthdate', 'education', 'major', 'experience', 'email_notify', 'height', 'marriage');
        $trims = array('telephone', 'fullname', 'residence', 'email', 'householdaddress', 'qq', 'weixin');
        foreach ($ints as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        foreach ($trims as $val) {
            $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
        }
        if ($pid = I('post.pid', 0, 'intval')) {
            $rst = D('AdvResume')->save_resume($setsqlarr, $pid, C('visitor'));
        } else {
            $rst = D('AdvResume')->add_resume($setsqlarr, C('visitor'));
        }
        if ($rst['state'] == 0) {
            $this->error($rst['error']);
        } else {
            $this->success('保存成功！');
        }

    }

    /**
     * [求职意向]
     */
    public function save_basic() {
        $setsqlarr['intention_jobs_id'] = I('post.intention_jobs_id', '', 'trim,badword');
        $setsqlarr['trade'] = I('post.trade', '', 'trim,badword');//期望行业
        $setsqlarr['district'] = I('post.district', '', 'trim,badword');//工作地区
        $setsqlarr['nature'] = I('post.nature', 0, 'intval');//工作性质
        $setsqlarr['current'] = I('post.current', 0, 'intval');
        $setsqlarr['wage'] = I('post.wage', 0, 'intval');//期望薪资
        if ($resume = $this->_is_resume()) {
            $rst = D('AdvResume')->save_resume($setsqlarr, $resume['id']);
        }
        if ($rst['state'] == 0) {
            $this->error($rst['error']);
        } else {
            $this->success('保存成功！');
        }

    }

    /**
     * [自我描述]
     */
    public function save_specialt() {
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');
        $specialty = I('post.specialty', '', 'trim,badword');
        !$specialty && $this->error('请输入自我描述!');
        $rst = D('AdvResume')->save_resume(array('specialty' => $specialty), $resume['id']);
        if ($rst['state'] == 0) {
            $this->error($rst['error']);
        } else {
            $this->success('保存成功！');
        }

    }

    /**
     * [教育经历]
     */
    public function save_education() {
        $setsqlarr['school'] = I('post.school', '', 'trim,badword');
        $setsqlarr['speciality'] = I('post.speciality', '', 'trim,badword');
        $setsqlarr['education'] = I('post.education1', 0, 'intval');
        $setsqlarr['startyear'] = I('post.startyearEdu', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonthEdu', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyearEdu', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonthEdu', 0, 'intval');
        $setsqlarr['todate'] = I('post.tonowEdu', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->error('请选择就读时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('就读开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('就读开始时间需小于毕业时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->error('请选择就读时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('就读开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('就读开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->error('就读结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->error('就读结束时间不允许大于当前时间！');

            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->error('就读开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->error('就读开始时间需小于毕业时间！');
        }
        $education = D('Category')->get_category_cache('QS_education');
        $setsqlarr['education_cn'] = $education[$setsqlarr['education']];
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $education = M('AdvResumeEducation')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取教育经历数量
        if (count($education) >= 6) $this->error('教育经历不能超过6条！');
        if ($id = I('post.edu_id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_education';
        } else {
            $name = 'add_resume_education';
        }
        $reg = D('AdvResumeEducation')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
            $this->success('教育经历保存成功！');
        } else {
            $this->error($reg['error']);
        }

    }

    /**
     * [工作经历]
     */
    public function save_work() {
        $setsqlarr['companyname'] = I('post.companyname', '', 'trim,badword');
        $setsqlarr['achievements'] = I('post.achievements', '', 'trim,badword');
        $setsqlarr['jobs'] = I('post.jobs', '', 'trim,badword');
        $setsqlarr['startyear'] = I('post.startyearExp', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonthExp', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyearExp', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonthExp', 0, 'intval');
        $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->error('请选择工作时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('工作开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('工作开始时间需小于当前时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->error('请选择工作时间！');

            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('工作开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('工作开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->error('工作结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->error('工作结束时间不允许大于当前时间！');

            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->error('工作开始时间不允许大于结束时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->error('工作开始时间需小于结束时间！');
        }
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $work = M('AdvResumeWork')->where(array('pid' => $setsqlarr['pid']))->select();//获取教育经历数量
        if (count($work) >= 6) $this->error('工作经历不能超过6条！');
        if ($id = I('post.work_id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_work';
        } else {
            $name = 'add_resume_work';
        }
        $reg = D('AdvResumeWork')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
            $this->success('工作经历保存成功！', $data);
        } else {
            $this->error($reg['error']);

        }
    }

    /**
     * [培训经历]
     */
    public function save_training() {
        $setsqlarr['agency'] = I('post.agency', '', 'trim,badword');
        $setsqlarr['course'] = I('post.course', '', 'trim,badword');
        $setsqlarr['description'] = I('post.description', '', 'trim,badword');
        $setsqlarr['startyear'] = I('post.startyearTra', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonthTra', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyearTra', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonthTra', 0, 'intval');
        $setsqlarr['todate'] = I('post.todateTra', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->error('请选择培训时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('培训开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('培训开始时间需小于毕业时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->error('请选择培训时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->error('培训开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->error('培训开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->error('培训结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->error('培训结束时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->error('培训开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->error('培训开始时间需小于毕业时间！');
        }
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $training = M('AdvResumeTraining')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取教育经历数量
        if (count($training) >= 6) $this->error('培训经历不能超过6条！');
        if ($id = I('post.tra_id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_training';
        } else {
            $name = 'add_resume_training';
        }
        $reg = D('AdvResumeTraining')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
            $this->success('培训经历保存成功！');
        } else {
            $this->error($reg['error']);
        }

    }

    /**
     * [获得证书]
     *
     */
    public function save_credent() {
        $setsqlarr['name'] = I('post.name', '', 'trim,badword');
        $setsqlarr['year'] = I('post.yearCredent', '', 'trim,badword');
        $setsqlarr['month'] = I('post.monthCredent', '', 'trim,badword');
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');

        if (!$setsqlarr['year'] || !$setsqlarr['month']) $this->error('请选择获得证书时间！');
        if ($setsqlarr['year'] > intval(date('Y'))) $this->error('获得证书时间不能大于当前时间！');
        if ($setsqlarr['year'] == intval(date('Y')) && $setsqlarr['month'] > intval(date('m'))) $this->error('获得证书时间不能大于当前时间！');

        $setsqlarr['pid'] = $resume['id'];
        $credent = M('AdvResumeCredent')->where(array('pid' => $setsqlarr['pid']))->select();//获取证书数量
        if (count($credent) >= 6) $this->error('证书不能超过6条！');
        if ($id = I('post.cre_id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_credent';
        } else {
            $name = 'add_resume_credent';
        }
        $reg = D('AdvResumeCredent')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
            $this->success('证书保存成功！');
        } else {
            $this->error($reg['error']);
        }
    }

    /**
     * [语言]
     *
     */
    public function save_language() {
        $setsqlarr['language'] = I('post.language1', '', 'trim,badword');
        $setsqlarr['level'] = I('post.level1', '', 'trim,badword');
        if (false === $resume = $this->_is_resume()) $this->error('请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $language = M('AdvResumeLanguage')->where(array('pid' => $setsqlarr['pid'], 'language' => $setsqlarr['language']))->select();//获取证书数量
        if ($language) $this->error('语言不能重复添加！');
        if (count($language) >= 6) $this->error('语言不能超过6条！');
        if ($id = I('post.lan_id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_language';
        } else {
            $name = 'add_resume_language';
        }
        $category = D('Category')->get_category_cache();
        $setsqlarr['language_cn'] = $category['QS_language'][$setsqlarr['language']];
        $setsqlarr['level_cn'] = $category['QS_language_level'][$setsqlarr['level']];
        $reg = D('AdvResumeLanguage')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
            $this->success('语言保存成功！');
        } else {
            $this->error($reg['error']);
        }
    }

    /**
     * [特长标签]
     */
    public function save_tag() {
        $pid = I('post.id', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '请正确选择简历！');
        $uid = C('visitor.uid');
        $tag_cn = I('post.tag_cn', '', 'badword');
        $setarr['tag_cn'] = $tag_cn ? implode(",", $tag_cn) : '';
        $tag = I('post.tag', '', 'badword');
        $setarr['tag'] = $tag;
        $tag = $tag ? explode(",", $tag) : '';
        $tags = D('Category')->get_category_cache('QS_resumetag');
        foreach ($tag as $key => $val) {
            $setarr['tag_cn'] .= ",{$tags[$val]}";
        }
        $setarr['tag_cn'] = ltrim($setarr['tag_cn'], ',');
        if (!$setarr['tag_cn']) $s = 2;
        $resume_mod = D('AdvResume');
        if (false !== $resume_mod->where(array('id' => $pid))->save($setarr)) {
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '修改简历特长标签（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            D('AdvResume')->save_resume('', $pid, C('visitor'));
            $this->success('简历特长标签保存成功！');
        } else {
            $this->error('保存失败！');
        }
    }

    protected function _edit_data($type) {
        $id = I('get.id', 0, 'intval');
        $pid = I('get.pid', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请求缺少参数！');
        !$pid && $this->ajaxReturn(0, '请先填写简历基本信息！');
        $data = M($type)->where(array('id' => $id, 'pid' => $pid))->find();
        !$data && $this->ajaxReturn(0, '数据不存在或已经删除！');
        $this->ajaxReturn(1, '数据获取成功！', $data);
    }

    //修改教育经历
    public function edit_education() {
        $this->_edit_data('AdvResumeEducation');
    }

    //修改工作经历
    public function edit_work() {
        $this->_edit_data('AdvResumeWork');
    }

    //修改培训经历
    public function edit_training() {
        $this->_edit_data('AdvResumeTraining');
    }

    //修改证书
    public function edit_credent() {
        $this->_edit_data('adv_resume_credent');
    }

    //修改语言
    public function edit_language() {
        $this->_edit_data('AdvResumeLanguage');
    }

    /**
     * [_del_data 删除简历信息]
     */
    protected function _del_data($type) {
        $id = I('request.id', 0, 'intval');
        $pid = I('request.pid', 0, 'intval');
        if (!$pid || !$id) $this->ajaxReturn(0, '请求缺少参数！');
        if (M($type)->where(array('id' => $id, 'pid' => $pid))->delete()) {
            switch ($type) {
                case 'AdvResumeEducation':
                    write_members_log($user, 'resume', '删除高级简历教育经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                    break;
                case 'AdvResumeWork':
                    write_members_log($user, 'resume', '删除高级简历工作经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                    break;
                case 'AdvResumeTraining':
                    write_members_log($user, 'resume', '删除高级简历培训经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                    break;
                case 'AdvResumeLanguage':
                    write_members_log($user, 'resume', '删除高级简历语言能力（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                    break;
                case 'AdvResumeCredent':
                    write_members_log($user, 'resume', '删除高级简历证书（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                    break;
            }
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    //删除教育经历
    public function del_education() {
        $this->_del_data('AdvResumeEducation');
    }

    //删除工作经历
    public function del_work() {
        $this->_del_data('AdvResumeWork');
    }

    //删除培训经历
    public function del_training() {
        $this->_del_data('AdvResumeTraining');
    }

    //删除语言能力
    public function del_language() {
        $this->_del_data('AdvResumeLanguage');
    }

    //删除证书
    public function del_credent() {
        $this->_del_data('AdvResumeCredent');
    }

    /**
     * [删除作品]
     */
    public function img_del() {
        $img_id = I('request.id', 0, 'intval');
        $img_mod = M('AdvResumeImg');
        $row = $img_mod->where(array('id' => $img_id))->field('img,resume_id')->find();
        $size = explode(',', C('qscms_resume_img_size'));
        if (strpos($row['img'], '..') !== false) die('Error Img.');
        @unlink(C('qscms_attach_path') . "top_resume_img/" . $row['img']);
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new \Common\ORG\qiniu;
            $qiniu->delete($row['img']);
        }
        foreach ($size as $val) {
            @unlink(C('qscms_attach_path') . "top_resume_img/{$row['img']}_{$val}x{$val}.jpg");
            if (C('qscms_qiniu_open') == 1) {
                $thumb_name = $qiniu->getThumbName($row['img'], $val, $val);
                $qiniu->delete($thumb_name);
            }
        }
        if (false === $img_mod->where(array('id' => $img_id))->delete()) $this->error('删除失败！');
        //写入会员日志
        write_members_log(C('visitor'), 'resume', '删除简历图片（简历id：' . intval($row['resume_id']) . '）', false, array('resume_id' => intval($row['resume_id'])));
        $this->success('删除成功！');
    }

    /**
     * [photo 照片简历]
     */
    public function photo() {
        $_REQUEST['photos'] = $_GET['photos'] = 1;
        $this->_tpl = 'photo';
        $this->distinct = 'uid';
        $this->index();
    }

    /**
     * [委托投递的简历]
     */
    public function entrust() {
        $this->_clear_expired_entrust();
        $this->_name = 'Resume';
        $key_type = I('request.key_type', 0, 'intval');
        $orderby_str = I('get.orderby', 'addtime', 'trim');
        $where['entrust'] = array('gt', 0);
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['fullname'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where['id'] = intval($key);
                    break;
                case 3:
                    $where['uid'] = intval($key);
                    break;
                case 4:
                    $where['telephone'] = array('like', '%' . $key . '%');
                    break;
                case 5:
                    $where['residence'] = array('like', '%' . $key . '%');
                    break;
            }
        } else {
            if ($addtimesettr = I('request.addtimesettr', 0, 'intval')) {
                $where['addtime'] = array('egt', strtotime("-" . $addtimesettr . " day"));
            }
            if ($settr = I('request.settr', 0, 'intval')) {
                $where['refreshtime'] = array('egt', strtotime("-" . $settr . " day"));
            }
            if ($photos = I('photos', 0, 'intval')) {
                $photos == 1 && $where['photo_img'] = array('neq', '');
                $photos == 2 && $where['photo_img'] = array('eq', '');
            }
            if ($entrust = I('entrust', 0, 'intval')) {
                $where['entrust'] = $entrust;
            }
        }
        $this->order = $orderby_str . ' desc,id desc';
        $this->where = $where;
        $this->custom_fun = '_format_entrust_list';
        $this->_tpl = 'entrust';
        parent::index();
    }

    /**
     * 待审核简历
     */
    public function index_noaudit() {
        $_REQUEST['audit'] = $_GET['audit'] = I('request.audit', 2, 'intval');
        $this->index();
    }
    /**
     * [del_refresh_resume 取消自动投递]
     */
    public function del_apply_resume(){
        $uid=I('get.uid',0,'intval');
        $id=I('get.id',0,'intval');
        $del=M('ResumeEntrust')->where(array('resume_id'=>$id,'uid'=>$uid))->delete();
        M('Resume')->where(array('id'=>$id,'uid'=>$uid))->setfield('entrust',0);
        if($del){
            $this->success("取消委托成功！"); 
        }else{
            $this->success("取消委托失败！");
        }
    }
    /**
     * [resume_delete 删除简历]
     */
    public function resume_delete() {
        $id = I('request.id');
        if (!$id) $this->error('请选择简历');
        if ($n = D('Resume')->admin_del_resume($id)) {
            $this->success("删除成功！共删除{$n}行");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * [advresume_delete 高级简历删除]
     */
    public function advresume_delete() {
        $id = I('request.id');
        if (!$id) $this->error('请选择简历');
        if ($n = D('AdvResume')->admin_del_advresume($id)) {
            $this->success("删除成功！共删除{$n}行");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     * 审核简历
     */
    public function set_audit() {
        $id = I('request.id');
        if (!$id) $this->error('请选择简历');
        $audit = I('post.audit', 0, 'intval');
        $pms_notice = I('post.pms_notice', 0, 'intval');
        $reason = I('post.reason', '', 'trim');
        !D('Resume')->admin_edit_resume_audit($id, $audit, $reason, $pms_notice, C('visitor')) ? $this->error('设置失败！') : $this->success("设置成功！");
    }

    /**
     * 审核照片简历
     */
    public function set_photo_audit() {
        $id = I('request.id');
        if (!$id) $this->error('请选择简历');
        $audit = I('post.photo_audit', 0, 'intval');
        $pms_notice = I('post.pms_notice', 0, 'intval');
        $reason = I('post.reason', '', 'trim');
        !D('Resume')->admin_edit_resume_photo_audit($id, $audit, $reason, $pms_notice, C('visitor')) ? $this->error('设置失败！') : $this->success("设置成功！");
    }

    /**
     * 刷新简历
     */
    public function refresh() {
        $id = I('request.id');
        if (!$id) $this->error('请选择简历');
        if ($n = D('Resume')->admin_refresh_resume($id)) {
            $this->success("刷新成功！响应行数 {$n}");
        } else {
            $this->error("刷新失败！");
        }
    }

    /**
     * 个人会员
     */
    public function member_list() {
        $this->_name = 'Members';
        $where['utype'] = 2;
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        $source=I('get.source',0,'intval');
        $db_pre = C('DB_PREFIX');
        $this_t = C('DB_PREFIX') . 'members';
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['username'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.uid'] = intval($key);
                    break;
                case 3:
                    $where['email'] = array('like', '%' . $key . '%');
                    break;
                case 4:
                    $where['mobile'] = array('like', '%' . $key . '%');
                    break;
            }
        } else {
            if ($settr = I('request.settr', 0, 'intval')) {
                $where['reg_time'] = array('gt', strtotime("-" . $settr . " day"));
            }
            if ($source>0){
                $where['reg_source']=array('eq',$source);
            }
            if ($photo_audit = I('get.photo_audit', 0, 'intval')) {
                $join[] = 'left join ' . $db_pre . 'members_info i on i.uid=' . $this_t . '.uid';
                $where['i.photo_audit'] = $photo_audit;
                $this->field = $db_pre . 'members.*,i.photo_audit';
            }
        }
        $join[] = 'left join ' . $db_pre . 'members_bind b on b.uid=' . $this_t . ".uid and b.type='weixin'";
        $this->field = $this->field ? $this->field . ',b.is_bind' : $this_t . '.*,b.is_bind';
        if ('' != $is_bind = I('request.is_bind')) {
            if ($is_bind) {
                $where['b.is_bind'] = intval($is_bind);
                $where['b.openid'] = array('neq', '');
            } else {
                $where['b.is_bind'] = array(array('eq', 0), array('is', null), 'or');
            }
        }
        $this->join = $join;
        $this->where = $where;
        $this->order = $this_t . '.uid desc';
        $this->_tpl = 'member_list';
        parent::index();
    }

    /**
     * 删除会员
     */
    public function member_delete() {
        $tuid = I('post.tuid', '', 'trim');
        !$tuid && $this->error('你没有选择会员！');
        $sitegroup_uids = M('Members')->where(array('uid' => array('in', $tuid)))->getField('sitegroup_uid', true);
        if (false === D('Members')->delete_member($tuid)) $this->error('删除会员失败！');
        $type['_user'] = 1;
        D('Resume')->admin_del_resume_for_uid($tuid);
        $type['_resume'] = 1;
        D('CompanyProfile')->admin_delete_company($tuid);
        $type['_company'] = 1;
        D('Jobs')->admin_delete_jobs_for_uid($tuid);
        $type['_jbos'] = 1;
        if (C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')) {
            require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
            $name = 'sitegroup_passport';
            $passport = new $name();
            false === $passport->delete($sitegroup_uids, $type) && $this->error($passport->get_error());
        }
        $this->success('删除成功！');
    }

    /**
     * 添加会员
     */
    public function member_add() {
        $this->_name = 'Members';
        parent::add();
    }

    /**
     * 高级简历创建
     */
    public function adv_resume_add() {
        $this->_name = 'AdvResume';
        $pid = I('get.id', 0, 'intval');
        $this->info = D('AdvResume')->get_resume_one($pid);
        $this->edu_info = D('AdvResumeEducation')->get_resume_education($pid);
        $this->work_info = D('AdvResumeWork')->get_resume_work($pid);
        $this->tra_info = D('AdvResumeTraining')->get_resume_training($pid);
        $this->img_info = D('AdvResumeImg')->get_resume_img($pid);
        $this->cre_info = D('AdvResumeCredent')->get_resume_credent($pid);
        $this->lan_info = D('AdvResumeLanguage')->get_resume_language($pid);
        $this->category = D('Category')->get_category_cache();
        parent::add();
    }

    public function _before_insert($data) {
        if ($this->_name == 'Members') {
            if (fieldRegex($data['username'], 'number')) {
                $this->returnMsg(0,'用户名不能是纯数字！');
            }
            if (C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')) {
                require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
                $name = 'sitegroup_passport';
                $passport = new $name();
                if (false === $data = $passport->register($data)) {
                    $this->returnMsg(0,$passport->get_error());
                }
            }
            $data['s_password'] = $data['password'];
            $data['password'] = D('Members')->make_md5_pwd($data['password'], $data['pwd_hash']);
        }
        return $data;
    }

    public function _after_insert($id, $data) {
        if ($this->_name == 'Members') {
            D('Members')->user_register($data);
            if(!C('qscms_register_password_open')){
                $sendSms['tpl']='set_register_resume';
                $sendSms['data']=array('username'=>$data['username'].'','password'=>$data['s_password']);
                $sendSms['mobile']=$data['mobile'];
                D('Sms')->sendSms('captcha',$sendSms);
            }
        }
    }

    /**
     * 编辑会员信息
     */
    public function member_edit() {
        $this->_name = 'Members';
        if (!IS_POST) {
            $uid = I('get.uid', 0, 'intval');
            $resume = D('Resume')->where(array('uid' => $uid))->select();
            if ($resume) {
                $user = M('Members')->where(array('uid' => $uid))->find();
                $Ip = new \Common\ORG\IpLocation('UTFWry.dat');
                $rst = $Ip->getlocation($user['reg_ip']);
                foreach ($resume as $key => $value) {
                    $resume[$key]['ipAddress'] = $rst['country'];
                }
            }
            $this->assign('resume', $resume);
        }
        parent::edit();
    }

    public function _after_update($id, $data) {
        if ($this->_name == 'Members') {
            if (2 == I('request.status', 0, 'intval')) {
                M('ResumeSearchFull')->where(array('uid' => $data['uid']))->delete();
                M('ResumeSearchPrecise')->where(array('uid' => $data['uid']))->delete();
            } elseif (1 == I('request.status', 0, 'intval')) {
                $resume = M('Resume')->field('id,uid,key_full,key_precise,stime,refreshtime')->where(array('uid' => $data['uid']))->select();
                foreach ($resume as $key => $val) {
                    $full[] = array('id' => $val['id'], 'uid' => $val['uid'], 'key' => $val['key_full'], 'stime' => $val['stime'], 'refreshtime' => $val['refreshtime']);
                    $precise[] = array('id' => $val['id'], 'uid' => $val['uid'], 'key' => $val['key_precise'], 'stime' => $val['stime'], 'refreshtime' => $val['refreshtime']);
                }
                M('ResumeSearchFull')->addAll($full);
                M('ResumeSearchPrecise')->addAll($precise);
            }
            $members_info = D('Members')->find($data['uid']);
            D('Members')->update_user_info($data,$members_info);
            if(I('post.qq_openid',0,'intval') == 2){
                    M('MembersBind')->where(array('uid'=>$data['uid'],'type'=>'qq'))->delete();
            }
        }
    }
    public function _before_update($data){
        if($this->_name == 'Members'){
            if(C('qscms_sitegroup_open') && C('qscms_sitegroup_domain') && C('qscms_sitegroup_secret_key') && C('qscms_sitegroup_id')){
                require_once QSCMSLIB_PATH . 'passport/sitegroup.php';
                $name = 'sitegroup_passport';
                $passport = new $name();
                $data['password'] && $data['password'] = I('post.password', '', 'trim');
                false === $passport->edit($data['uid'], $data) && $this->error($passport->get_error());
            }
            if (isset($_POST['password'])) {
                $model = D('Members');
                $member = $model->find(I('post.uid', 0, 'intval'));
                $data['password'] = $model->make_md5_pwd(I('post.password', '', 'trim'), $member['pwd_hash']);
            }
        }
        return $data;
    }
    public function _after_select($info){
        $qq_bind = D('MembersBind')->where(array('uid'=>$info['uid'],'type'=>'qq'))->find();
        if($qq_bind){
            $info['qq_openid'] = 1;
        }
        return $info;
    }
    /**
     * 加载会员详情
     */
    public function ajax_get_user_info() {
        $id = I('get.id', 0, 'intval');
        $rst = D('Members')->admin_ajax_get_user_info($id);
        exit($rst['msg']);
    }

    /**
     * 加载委托详情(旧)
     */
    public function ajax_get_entrust_info() {
        //会员id
        $uid = I('get.uid', 0, 'intval');
        //简历id
        $rid = I('get.rid', 0, 'intval');
        $info = D('Members')->get_user_one(array('uid' => $uid));
        if (empty($info)) {
            exit("会员信息不存在！可能已经被删除！");
        }
        $resume_info = D('Resume')->where(array('id' => $rid))->find();
        if (empty($resume_info)) {
            exit("简历信息不存在！可能已经被删除！");
        }
        $entrust_info = D('ResumeEntrust')->where(array('resume_id' => $rid, 'uid' => $uid))->find();
        $entrust_info['entrust_start'] = $entrust_info['entrust_start'] ? date("Y/m/d", $entrust_info['entrust_start']) : '----';
        $html = "委托开始时间：{$entrust_info['entrust_start']}<br/>";
        $entrust_info['entrust_end'] = $entrust_info['entrust_end'] ? date("Y/m/d", $entrust_info['entrust_end']) : '----';
        $html .= "委托结束时间：{$entrust_info['entrust_end']}<br/>";
        exit($html);
    }

    /**
     * 查看会员中心
     */
    public function management() {
        $id = I('get.id', 0, 'intval');
        $action = I('get.action', 'home/members/index', 'trim');
        $u = D('Members')->get_user_one(array('uid' => $id));
		//h 后台进入会员
		$u['utype']=2;
        if (!empty($u)) {
            $user_visitor = new \Common\qscmslib\user_visitor;
            $user_visitor->logout();
            $user_visitor->assign_info($u);
            redirect(U($action));
        }
    }

    /**
     * 查看简历
     */
    public function resume_show() {
        $id = I('get.id', 0, 'intval');
        if (!$id) {
            $this->error('参数错误！');
        }
        $uid = I('get.uid', 0, 'intval');
        $resume = D('Resume')->get_resume_one($id);
        if (empty($resume)) {
            $this->error('简历不存在或已经被删除！');
        }
        $this->assign('resume', $resume);
        $this->assign('resume_education', D('ResumeEducation')->get_resume_education($id));
        $this->assign('resume_work', D('ResumeWork')->get_resume_work($id));
        $this->assign('resume_training', D('ResumeTraining')->get_resume_training($id));
        $this->assign('resumeaudit', M('AuditReason')->where(array('resume_id' => $id)));
        $this->display();
    }

    /**
     * 删除审核日志
     */
    public function del_auditreason() {
        $id = I('request.a_id');
        if (!$id) {
            $this->error('你没有选择日志！');
        }
        $n = D('AuditReason')->delete($id);
        if ($n > 0) {
            adminmsg("删除成功！共删除 {$n} 行", 2);
        } else {
            adminmsg("删除失败！", 0);
        }
    }

    /**
     * 匹配
     */
    public function match() {
        $id = I('get.id', '0', 'intval');
        $uid = I('get.uid', '0', 'intval');
        $resume = M('Resume')->where(array('id' => $id, 'uid' => $uid))->find();
        $jids = M('PersonalJobsApply')->where(array('resume_id' => $id))->getfield('jobs_id', true);
        $where = array(
            '职位分类' => $resume['intention_jobs_id'],
            '显示数目' => '10',
            '分页显示' => 1
        );
        $jobs_mod = new \Common\qscmstag\jobs_listTag($where);
        $jobs_list = $jobs_mod->run();
        //dump($jobs_list);die;
        $page_params = $jobs_list['page_params'];
        $pager = pager($page_params['totalRows'], 10);//实例化thinkphp内置的分页显示类
        $page = $pager->fshow();
        $jobs_list['page'] = $page;
        $this->assign("jobslist", $jobs_list);
        $this->assign('resume', $resume);
        $this->assign('applied_jids', $jids);
        $this->display();
    }

    /**
     * [apply 匹配结果投递简历]
     */
    public function apply(){
        $jid = I('request.jid','','trim');
        $rid = I('request.id',0,'intval');
        $uid = I('request.uid',0,'intval');
        !$jid && $this->error('请选择要投递的职位！');
        !$rid && $this->error('请选择要投递的简历！');
        !$uid && $this->error('请选择要投递的个人用户！');
        $user = D('Members')->get_user_one(array('uid'=>$uid));
        $apply = D('PersonalJobsApply')->jobs_apply_add($jid,$user,$rid,1);
        if (!$apply['state'] && $apply['complete']) {// 完整度不够
            $this->error($apply['error']);
        }
        !$apply['state'] &&  $this->error($apply['error']);
        $apply['data']['failure'] && $this->error($apply['data']['list'][$jid]['tip']);
        $this->success('投递成功！');
    }

    /**
     * [promotion 推广]
     */
    public function promotion() {
        $type = I('request.type', 'stick', 'trim');
        $name = 'promotion_' . $type;
        $this->$name();
    }

    /**
     * [promotion_stick 个人置顶推广]
     */
    protected function promotion_stick() {
        $this->_name = 'PersonalServiceStickLog';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'personal_service_stick_log';
        $this->join = 'left join ' . $db_pre . 'resume r on r.id=' . $this_t . '.resume_id';
        $this->field = $this_t . '.*,r.title,r.fullname';
        $settr = I('request.settr');
        if ($settr) {
            $where['endtime'] = array('lt', strtotime(intval($settr) . " day"));
        } else if ($settr == '0') {
            $where['endtime'] = array('lt', time());
        }
        $uid = I('request.uid', 0, 'intval');
        $uid && $where['resume_uid'] = $uid;
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['r.title'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.resume_id'] = intval($key);
                    break;
                case 3:
                    $where[$this_t . '.resume_uid'] = intval($key);
                    break;
            }
        }
        $this->where = $where;
        $this->custom_fun = '_format_resume_list';
        $this->_tpl = 'promotion_stick';
        parent::index();
    }

    /**
     * [promotion_stick_edit 修改个人简历置顶推广]
     */
    public function promotion_stick_edit() {
        $id = I('request.id', '', 'trim');
        !$id && $this->error('你没有选择简历！');
        if (IS_POST) {
            $days = I('request.days', 0, 'intval');
            !$days && $this->error('请填写要延长推广的天数！');
            $time = $days * (3600 * 24);
            $reg = D('PersonalServiceStickLog')->where(array('id' => $id))->save(array('days' => array('exp', 'days+' . $days), 'endtime' => array('exp', 'endtime+' . $time)));
            !$reg && $this->error('设置失败，请重新操作！');
            $this->success('保存成功！');
        } else {
            $info = M('PersonalServiceStickLog')->find($id);
            !$info && $this->error('简历置顶推广已删除！');
            $resume = M('Resume')->field('title,fullname')->find($info['resume_id']);
            $resume && $info = array_merge($info, $resume);
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * [promotion_stick_deltet 删除个人简历置顶推广]
     */
    public function promotion_stick_deltet() {
        $id = I('post.id', '', 'trim');
        if (!$id) $this->error('你没有选择简历！');
        if (false === D('PersonalServiceStickLog')->del_promotion_stick($id)) {
            $this->error('取消简历推广失败！');
        }
        $this->success('取消简历推广成功');
    }

    /**
     * [promotion_tag 个人标签推广]
     */
    protected function promotion_tag() {
        $this->_name = 'PersonalServiceTagLog';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'personal_service_tag_log';
        $this->join = 'left join ' . $db_pre . 'resume r on r.id=' . $this_t . '.resume_id';
        $this->field = $this_t . '.*,r.title,r.fullname';
        $settr = I('request.settr');
        if ($settr) {
            $where['endtime'] = array('lt', strtotime(intval($settr) . " day"));
        } else if ($settr == '0') {
            $where['endtime'] = array('lt', time());
        }
        $uid = I('request.uid', 0, 'intval');
        $uid && $where['resume_uid'] = $uid;
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where['r.title'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.resume_id'] = intval($key);
                    break;
                case 3:
                    $where[$this_t . '.resume_uid'] = intval($key);
                    break;
            }
        }
        $this->where = $where;
        $this->custom_fun = '_format_resume_list';
        if (false === $tag_list = F('service_tag_category')) $tag_list = D('PersonalServiceTagCategory')->tag_category_cache();
        $this->assign('tag_list', $tag_list);
        $this->_tpl = 'promotion_tag';
        parent::index();
    }

    /**
     * [promotion_stick_add 添加简历置顶推广]
     */
    public function promotion_add() {
        if (false === $tag_list = F('service_tag_category')) $tag_list = D('PersonalServiceTagCategory')->tag_category_cache();
        if (IS_POST) {
            $type = I('request.type', 'stick', 'trim');
            $days = I('request.days', 0, 'intval');
            !$days && $this->error("请填写推广时间！");
            if ($type == 'stick') {
                $this->_name = 'PersonalServiceStickLog';
                if (D($this->_name)->check_stick_log(array('resume_id' => I('request.resume_id')))) {
                    $this->error("此简历正在执行此推广！请选择其他简历或者其他推广方案");
                }
                $setsqlarr['points'] = 0;
                $setsqlarr['resume_id'] = I('post.resume_id', 0, 'intval');
                $setsqlarr['days'] = $days;
                $setsqlarr['resume_uid'] = I('post.resume_uid', 0, 'intval');
                $setsqlarr['endtime'] = strtotime("+{$setsqlarr['days']} day");
                $resume_info = D('Resume')->find($setsqlarr['resume_id']);
                $rst = D('PersonalServiceStickLog')->add_stick_log($setsqlarr);
                if ($rst['state'] == 1 && $resume_info) {
                    $refreshtime = $resume_info['refreshtime'];
                    $stime = intval($refreshtime) + 100000000;
                    D('Resume')->where(array('id' => $setsqlarr['resume_id']))->save(array('stick' => 1, 'stime' => $stime));
                    D('ResumeSearchPrecise')->where(array('id' => $setsqlarr['resume_id']))->setField('stime', $stime);
                    D('ResumeSearchFull')->where(array('id' => $setsqlarr['resume_id']))->setField('stime', $stime);
                    $this->success('设置成功！');
                    exit;
                }
                $this->error('设置失败！' . $rst['error']);
            } else {
                $tag_id = I('request.tag_id', 0, 'intval');
                !$tag_list[$tag_id] && $this->error("请正确选择醒目标签！");
                $this->_name = 'PersonalServiceTagLog';
                if (D($this->_name)->check_tag_log(array('resume_id' => I('request.resume_id')))) {
                    $this->error("此简历正在执行此推广！请选择其他简历或者其他推广方案");
                }
                $setsqlarr['points'] = 0;
                $setsqlarr['resume_id'] = I('post.resume_id', 0, 'intval');
                $resume_info = D('Resume')->find($setsqlarr['resume_id']);
                $setsqlarr['tag_id'] = $tag_id;
                $setsqlarr['days'] = $days;
                $setsqlarr['resume_uid'] = I('post.resume_uid', 0, 'intval');
                $setsqlarr['endtime'] = strtotime("+{$setsqlarr['days']} day");
                $rst = D('PersonalServiceTagLog')->add_tag_log($setsqlarr);
                if ($rst['state'] == 1 && $resume_info) {
                    D('Resume')->where(array('id' => array('eq', $setsqlarr['resume_id'])))->setField('strong_tag', $tag_id);
                    $this->success('设置成功！');
                    exit;
                }
                $this->error('设置失败！' . $rst['error']);
            }
        } else {
            $this->_name = 'PersonalServiceStickLog';
            $this->assign('tag_list', $tag_list);
            parent::add();
        }
    }

    /**
     * [promotion_stick_edit 修改个人简历置顶推广]
     */
    public function promotion_tag_edit() {
        $id = I('request.id', '', 'trim');
        !$id && $this->error('你没有选择简历！');
        if (false === $tag_list = F('service_tag_category')) $tag_list = D('PersonalServiceTagCategory')->tag_category_cache();
        if (IS_POST) {
            $tag_id = I('request.tag_id', 0, 'intval');
            $resume_id = I('request.resume_id', 0, 'intval');
            !$tag_list[$tag_id] && $this->error('请正确选择标签！');
            if ($days = I('request.days', 0, 'intval')) {
                $time = $days * (3600 * 24);
                $data['days'] = array('exp', 'days+' . $days);
                $data['endtime'] = array('exp', 'endtime+' . $time);
            }
            $data['tag_id'] = $tag_id;
            $reg = D('PersonalServiceTagLog')->where(array('id' => $id))->save($data);
            false === $reg && $this->error('设置失败，请重新操作！');
            D('Resume')->where(array('id' => $resume_id))->setField('strong_tag', $tag_id);
            $this->success('保存成功！');
        } else {
            $info = M('PersonalServiceTagLog')->find($id);
            !$info && $this->error('简历标签推广已删除！');
            $resume = M('Resume')->field('title,fullname')->find($info['resume_id']);
            $resume && $info = array_merge($info, $resume);
            $this->assign('tag_list', $tag_list);
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * [promotion_stick_deltet 删除个人简历置顶推广]
     */
    public function promotion_tag_deltet() {
        $id = I('post.id', '', 'trim');
        if (!$id) $this->error('你没有选择简历！');
        if (false === D('PersonalServiceTagLog')->del_promotion_tag($id)) {
            $this->error('取消简历标签失败！');
        }
        $this->success('取消简历推广成功');
    }

    /**
     * ajax获取简历
     */
    public function ajax_get_resume() {
        $type = I('get.type', '', 'trim');
        $key = I('get.key', '', 'trim');
        switch ($type) {
            case 'get_fullname':
                $where = array('fullname' => array('like', '%' . $key . '%'));
                break;
            case 'get_resumeid':
                $where = array('id' => intval($key));
                $limit = 1;
                break;
            case 'get_uid':
                $where = array('uid' => intval($key));
                $limit = 30;
                break;
        }
        $result = D('Resume')->where($where)->limit($limit)->select();
        $info = array();
        foreach ($result as $key => $value) {
            $value['addtime'] = date("Y-m-d", $value['addtime']);
            $value['refreshtime'] = date("Y-m-d", $value['refreshtime']);
            $value['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $value['id']));
            $info[] = $value['id'] . "%%%" . $value['fullname'] . "%%%" . $value['resume_url'] . "%%%" . $value['addtime'] . "%%%" . $value['refreshtime'] . "%%%" . $value['uid'];
        }
        if (!empty($info)) {
            exit(implode('@@@', $info));
        } else {
            exit();
        }
    }

    /**
     * [user_points_edit 用户积分操作]
     */
    public function user_points_edit() {
        if (IS_POST) {
            $points_type = I('post.points_type', 1, 'intval');
            $t = $points_type == 1 ? "+" : "-";
            $points = I('post.points', 1, 'intval');
            $uid = I('post.uid', 1, 'intval');
            D('MembersPoints')->report_deal($uid, $points_type, $points);
            $userinfo = D('Members')->get_user_one(array('uid' => $uid));
            //会员积分变更记录。管理员后台修改会员的积分。3表示：管理员后台修改
            if (I('post.is_money', 0, 'intval') && I('post.log_amount')) {
                $amount = round(I('post.log_amount'), 2);
                $ismoney = 2;
            } else {
                $amount = '0.00';
                $ismoney = 1;
            }
            $notes = "操作人：" . C('visitor.username') . ",说明：修改会员 {$userinfo['username']} {C('qscms_points_byname')} ({$t}{$points})。收取{C('qscms_points_byname')}金额：{$amount} 元，备注：{I('post.points_notes','','trim')}";
			write_members_log(array('uid'=>$uid,'utype'=>1,'username'=>$userinfo['username']),'setmeal',$notes,false,array(),C('visitor.id'),C('visitor.username'));
            $this->returnMsg(1,'保存成功');
        } else {
            $this->_name = 'Members';
            $where['uid'] = I('get.uid', 0, 'intval');
            $list = D('MembersHandsel')->get_handsel_list($where);
            $this->assign('userpoints', D('MembersPoints')->get_user_points($where['uid']));
            $this->assign('list', $list);
            $this->edit();
        }
    }

    /**
     * [user_log 用户日志]
     */
    public function user_log() {
        $this->_name = 'MembersLog';
        $this->assign('type_arr', D('MembersLog')->type_arr);
        $where['log_uid'] = I('request.uid', 0, 'intval');
        if ($settr = I('request.settr', 0, 'intval')) {
            $where['log_addtime'] = array('gt', strtotime("-" . $settr . " day"));
        }
        $this->where = $where;
        parent::index();
    }

    /**
     * [user_apply_jobs 申请职位]
     */
    public function user_apply_jobs() {
        $this->_name = 'PersonalJobsApply';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'personal_jobs_apply';
        $this->join = 'left join ' . $db_pre . 'jobs j on j.id=' . $this_t . '.jobs_id';
        $this->field = 'did,resume_id,resume_name,jobs_id,apply_addtime,personal_look,is_reply,is_apply,j.id,j.jobs_name,j.company_id,j.companyname,j.district';
        $where['personal_uid'] = I('request.uid', 0, 'intval');
        if ($settr = I('request.settr', 0, 'intval')) {
            $where['apply_addtime'] = array('gt', strtotime("-" . $settr . " day"));
        }
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where[$this_t . '.jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.jobs_id'] = intval($key);
                    break;
                case 3:
                    $where[$this_t . '.company_name'] = array('like', '%' . $key . '%');
                    break;
                case 4:
                    $where[$this_t . '.company_id'] = intval($key);
                    break;
                case 5:
                    $where[$this_t . '.resume_name'] = array('like', '%' . $key . '%');
                    break;
                case 6:
                    $where[$this_t . '.resume_id'] = intval($key);
                    break;
            }
        }
        $this->where = $where;
        $this->custom_fun = '_format_resume_apply_list';
        parent::index();
    }

    /**
     * [user_apply_delete 删除申请职位信息]
     */
    public function user_apply_delete() {
        $this->_name = 'PersonalJobsApply';
        parent::delete();
    }

    /**
     * [user_nterview 面试邀请]
     */
    public function user_nterview() {
        $this->_name = 'CompanyInterview';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'company_interview';
        $this->join = 'left join ' . $db_pre . 'jobs j on j.id=' . $this_t . '.jobs_id';
        $this->field = 'did,resume_id,resume_name,jobs_id,interview_addtime,personal_look,j.id,j.jobs_name,j.company_id,j.companyname,j.district';
        $where['resume_uid'] = I('request.uid', 0, 'intval');
        if ($settr = I('request.settr', 0, 'intval')) {
            $where['interview_addtime'] = array('gt', strtotime("-" . $settr . " day"));
        }
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        if ($key_type && $key) {
            switch ($key_type) {
                case 1:
                    $where[$this_t . '.jobs_name'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.jobs_id'] = intval($key);
                    break;
                case 3:
                    $where[$this_t . '.company_name'] = array('like', '%' . $key . '%');
                    break;
                case 4:
                    $where[$this_t . '.company_id'] = intval($key);
                    break;
                case 5:
                    $where[$this_t . '.resume_name'] = array('like', '%' . $key . '%');
                    break;
                case 6:
                    $where[$this_t . '.resume_id'] = intval($key);
                    break;
            }
        }
        $this->where = $where;
        $this->custom_fun = '_format_resume_apply_list';
        parent::index();
    }

    /**
     * [increment 个人增值服务]
     */
    public function increment() {
        $type = I('request.type', 'stick', 'trim');
        $name = 'increment_' . $type;
        $this->$name();
    }

    protected function increment_stick() {
        $this->_name = 'PersonalServiceStick';
        $this->_tpl = 'increment_stick';
        $this->order = 'sort desc';
        parent::index();
    }

    protected function increment_tag() {
        $this->_name = 'PersonalServiceTag';
        $tag_list = D('PersonalServiceTagCategory')->order('sort desc')->select();
        // if(false === $tag_list = F('service_tag_category')) $tag_list = D('PersonalServiceTagCategory')->tag_category_cache();
        $this->assign('tag_list', $tag_list);
        $this->_tpl = 'increment_tag';
        $this->order = 'sort desc';
        parent::index();
    }

    public function increment_stick_add() {
        $this->_name = 'PersonalServiceStick';
        parent::add();
    }

    public function increment_stick_edit() {
        $this->_name = 'PersonalServiceStick';
        parent::edit();
    }

    public function increment_stick_del() {
        $this->_name = 'PersonalServiceStick';
        parent::delete();
    }

    public function increment_stick_save_sort() {
        $id = I('post.id');
        $sort = I('post.sort');
        foreach ($id as $key => $value) {
            D('PersonalServiceStick')->where(array('id' => array('eq', intval($value))))->setField('sort', $sort[$key]);
        }
        $this->success('保存成功！');
    }

    public function increment_tag_add() {
        $this->_name = 'PersonalServiceTag';
        parent::add();
    }

    public function increment_tag_edit() {
        $this->_name = 'PersonalServiceTag';
        parent::edit();
    }

    public function increment_tag_del() {
        $this->_name = 'PersonalServiceTag';
        parent::delete();
    }

    public function increment_tag_save_sort() {
        $id = I('post.id');
        $sort = I('post.sort');
        foreach ($id as $key => $value) {
            D('PersonalServiceTag')->where(array('id' => array('eq', intval($value))))->setField('sort', $sort[$key]);
        }
        $this->success('保存成功！');
    }

    public function increment_tag_cat_save_sort() {
        $id = I('post.id');
        $sort = I('post.sort');
        foreach ($id as $key => $value) {
            D('PersonalServiceTagCategory')->where(array('id' => array('eq', intval($value))))->setField('sort', $sort[$key]);
        }
        $this->success('保存成功！');
    }

    public function tag_category_add() {
        $this->_name = 'PersonalServiceTagCategory';
        parent::add();
    }

    public function tag_category_edit() {
        $this->_name = 'PersonalServiceTagCategory';
        parent::edit();
    }

    public function tag_category_del() {
        $this->_name = 'PersonalServiceTagCategory';
        parent::delete();
    }

    /**
     * [user_interview_delete 删除面试邀请信息]
     */
    public function user_interview_delete() {
        $this->_name = 'CompanyInterview';
        parent::delete();
    }

    /**
     * [_after_search_resume 统计简历列表，各状态下简历列表数量]
     */
    protected function _after_search_resume($tabletype) {
        $total_all_resume = parent::_pending('Resume');
        $count[0] = $total_all_resume;
        $count[1] = parent::_pending('Resume', array('display' => 1, 'audit' => array('neq', 3)));
        $count[2] = $total_all_resume - $count[1];
        if ($tabletype == 0) {
        } elseif ($tabletype == 1) {
            $where['display'] = 1;
        } elseif ($tabletype == 2) {
            $where['display'] = 0;
        }
        $where['audit'] = 1;
        $count[3] = parent::_pending('Resume', $where);
        $where['audit'] = 2;
        $count[4] = parent::_pending('Resume', $where);
        $where['audit'] = 3;
        if ($tabletype == 2) unset($where['display']);
        $count[5] = parent::_pending('Resume', $where);
        $where = array('photo_img' => array('neq', ''));
        $count[6] = parent::_pending('Resume', $where, 'uid');
        $where['photo_audit'] = 2;
        $count[7] = parent::_pending('Resume', $where, 'uid');
        $where['photo_audit'] = 1;
        $count[8] = parent::_pending('Resume', $where, 'uid');
        $where['photo_audit'] = 3;
        $count[9] = parent::_pending('Resume', $where, 'uid');
        unset($where['photo_audit']);
        $where['audit'] = 1;
        $count[13] = parent::_pending('Resume', $where, 'uid');
        $where['audit'] = 2;
        $count[14] = parent::_pending('Resume', $where, 'uid');
        $where['audit'] = 3;
        if ($tabletype == 2) unset($where['display']);
        $count[15] = parent::_pending('Resume', $where, 'uid');
        $this->assign('count', $count);
    }

    /**
     * [_format_member_list 解析用户注册地址]
     */
    protected function _format_member_list($list) {
        foreach ($list as $key => $val) {
            $uids[] = $val['uid'];
        }
        if ($uids) {
            $weixin_bind = M('MembersBind')->where(array('uid' => array('in', $uids)))->getfield('uid,is_bind');
            foreach ($list as $key => $val) {
                $weixin_bind[$val['uid']] && $list[$key]['is_bind'] = $weixin_bind[$val['uid']];
            }
        }
        return $list;
    }

    /**
     * [_format_resume_list 解析简历跳转链接(简历列表页用)]
     */
    protected function _format_resume_list($list) {
        foreach ($list as $key => $val) {
            $id = $val['resume_id'] ?: $val['id'];
            $list[$key]['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $id)) . '&validation=1';
        }
        return $list;
    }

    /**
     * [_format_entrust_list 解析委托投递数据(委托投递列表页用)]
     */
    protected function _format_entrust_list($list) {
        foreach ($list as $key => $val) {
            $id = $val['resume_id'] ?: $val['id'];
            $list[$key]['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $id));
            $entrust = M('ResumeEntrust')->field('entrust_start,entrust_end')->where(array('resume_id' => $id))->find();
            $list[$key]['entrust_start'] = $entrust['entrust_start'];
            $list[$key]['entrust_end'] = $entrust['entrust_end'];
            $list[$key]['apply_count'] = M('PersonalJobsApply')->where(array('is_apply' => 1, 'resume_id' => $id))->count();
        }
        return $list;
    }

    /**
     * [_clear_expired_entrust 清理到期的委托投递]
     */
    protected function _clear_expired_entrust() {
        $resume_ids = M('ResumeEntrust')->where(array('entrust_end' => array('lt', time())))->getField('resume_id', true);
        M('ResumeEntrust')->where(array('entrust_end' => array('lt', time())))->delete();
        if ($resume_ids) {
            M('Resume')->where(array('id' => array('in', $resume_ids)))->setField('entrust', 0);
        }
    }

    /**
     * [_format_resume_apply_list 解析简历跳转链接(申请职位/面试邀请列表页用)]
     */
    protected function _format_resume_apply_list($list) {
        foreach ($list as $key => $val) {
            if (empty($val['company_id'])) {
                $jobs = M('JobsTmp')->field('id,jobs_name,company_id,companyname,district')->where(array('id' => $val['jobs_id']))->find();
                $list[$key] = array_merge($val, $jobs);
            }
            $list[$key]['resume_url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume_id']));
            $list[$key]['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['id']));
            $list[$key]['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
        }
        return $list;
    }

    /**
     * 照片/作品
     */
    public function resume_img() {
        $this->display();
    }

    /**
     * 随机红包
     */
    public function perfected_allowance() {
        $this->_name = 'MembersPerfectedAllowance';
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre . 'members_perfected_allowance';
        $key_type = I('request.key_type', 0, 'intval');
        $key = I('request.key', '', 'trim');
        $join = array();
        $join[] = 'left join ' . $db_pre . "members as m on " . $this_t . ".uid=m.uid";
        if ($key && $key_type > 0) {
            switch ($key_type) {
                case 1:
                    $where['m.username'] = array('like', '%' . $key . '%');
                    break;
                case 2:
                    $where[$this_t . '.uid'] = array('eq', $key);
                    break;
                case 3:
                    $where['m.email'] = array('like', '%' . $key . '%');
                    break;
                case 4:
                    $where['m.mobile'] = array('like', '%' . $key . '%');
                    break;
            }
        } else {
            if ($settr = I('get.settr', 0, 'intval')) {
                $where['addtime'] = array('gt', strtotime("-" . $settr . " day"));
            }
            if ('' != $status = I('get.status')) {
                $where[$this_t . '.status'] = array('eq', intval($status));
            }
        }
        $this->where = $where;
        $this->field = $this_t . '.*,m.username';
        $this->order = 'field(' . $this_t . '.status,0,1),id desc';
        $this->join = $join;
        parent::index();
    }

    public function perfected_allowance_repay($id) {
        $perfected_info = M('MembersPerfectedAllowance')->find($id);
        if ($perfected_info['stauts'] == 0) {
            if ($userbind = D('MembersBind')->get_members_bind(array('uid' => $perfected_info['uid'], 'type' => 'weixin'))) {
                include QSCMSLIB_PATH . "pay/wxpay/wxpay.class.php";
                $pay_type = D('Common/Payment')->get_cache();
                $setting = $pay_type['wxpay'];
                $payObj = new \wxpay_pay($setting);
                $data['openid'] = $userbind['openid'];
                $data['partner_trade_no'] = 'PraUid' . $perfected_info['uid'] . 'T' . time();
                $data['amount'] = $perfected_info['value'];
                $result = $payObj->payment($data);
                if ($result) {
                    M('MembersPerfectedAllowance')->where(array('uid' => $perfected_info['uid']))->save(array('status' => 1, 'reason' => '', 'notice' => 0));
                    $this->success('发放成功');
                } else {
                    M('MembersPerfectedAllowance')->where(array('uid' => $perfected_info['uid']))->save(array('status' => 0, 'reason' => $payObj->getError()));
                    $this->error('发放失败，' . $payObj->getError());
                }
            } else {
                M('MembersPerfectedAllowance')->where(array('uid' => $perfected_info['uid']))->save(array('status' => 0, 'reason' => '未绑定微信'));
                $this->error('发放失败，未绑定微信');
            }
        } else {
            $this->error('发放失败，该红包已发放过');
        }
    }
}

?>