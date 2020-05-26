<?php
namespace Home\Controller;

use Common\Controller\FrontendController;

class AdvPersonalController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'), '', 1);
            //非ajax的跳转页面
            $this->redirect('members/login');
        }
        if (C('visitor.utype') != 2) {
            IS_AJAX && $this->ajaxReturn(0, '请登录个人帐号！');
            $this->redirect('members/index');
        }
        !IS_AJAX && $this->_global_variable();
    }

    protected function _global_variable() {
        // 帐号状态 为暂停
        if (C('visitor.status') == 2 && !in_array(ACTION_NAME, array('index'))) {
            $this->error('您的账号处于暂停状态，请联系管理员设为正常后进行操作！', U('Personal/index'));
        }
        $resume_count = D('Resume')->where(array('uid' => C('visitor.uid')))->find();
        if (!$resume_count && !in_array(ACTION_NAME, array('resume_add'))) $this->redirect('personal/resume_add');
        if (!C('qscms_login_refresh_resume') && !S('personal_login_first_' . C('visitor.uid'))) {
            S('personal_login_first_' . C('visitor.uid'), 1, 86400 - (time() - strtotime("today")));
            if ($resume_count > 0) {
                $resume = M('Resume')->where(array('uid' => C('visitor.uid')))->order('def desc')->limit(1)->find();//当前用户默认简历内容
                $this->assign('resume', $resume);//当前用户简历内容
            }
        }
        $this->assign('personal_nav', ACTION_NAME);
    }

    /**
     * [_is_resume 检测简历是否存在]
     * @return boolean [false || 简历信息(按需要添加字段)]
     */
    protected function _is_resume($pid) {
        !$pid && $pid = I('request.pid', 0, 'intval');
        if (!$pid) {
            IS_AJAX && $this->ajaxReturn(0, '请正确选择简历！');
            $this->error('请正确选择简历！');
        }
        //$field = 'id,uid,title,fullname,sex,nature,nature_cn,trade,trade_cn,birthdate,residence,height,marriage_cn,experience_cn,district_cn,wage_cn,householdaddress,education_cn,major_cn,tag,tag_cn,telephone,email,intention_jobs,photo_img,complete_percent,current,current_cn,word_resume';
        if (!$reg = M('AdvResume')->field()->where(array('id' => $pid, 'uid' => C('visitor.uid')))->find()) return false;
        $reg['height'] = $reg['height'] == 0 ? '' : $reg['height'];
        if ($reg['audit'] == 2) {
            $reg['_audit'] = C('qscms_resume_display') == 2 ? 1 : $reg['audit'];// 先显示再审核
        } else {
            $reg['_audit'] = $reg['audit'];
        }
        $this->assign('resume', $reg);
        return $reg;
    }
    public function adv_index() {
        session('error_login_count', 0);
        $uid = C('visitor.uid');
        $resume_list = D('AdvResume')->get_resume_list(array('where' => array('uid' => $uid), 'order' => 'def desc', 'countinterview' => true, 'countdown' => true, 'countapply' => true, 'views' => true, 'stick' => true));
        $this->assign('points', D('MembersPoints')->get_user_points($uid));//当前用户积分数
        $resume_info = $resume_list[0];
        $resume_info['tag_cn'] = $resume_info['tag_cn'] ? explode(',', $resume_info['tag_cn']) : array();
        $category = D('Category')->get_category_cache();
        $get_resume_img = M('AdvResumeImg')->where(array('resume_id' => $resume_info['id']))->select();//获取简历附件图片
        if ($resume_info['intention_jobs_id']) {
            $jobcategory = explode(',', $resume_info['intention_jobs_id']);
            $jobcategory = explode('.', $jobcategory[0]);
            $jobcategory = $jobcategory[2] ? $jobcategory[2] : $jobcategory[1];
            if (false === $result = F('jobs_cate_list')) $result = D('CategoryJobs')->jobs_cate_cache();
            $resume_info['jobcategory'] = $result['id'][$jobcategory]['spell'];
        }
        $this->assign('resume_info', $resume_info);
        $this->assign('audit_reason', D('AuditReason')->where(array('resume_id' => $resume_info['id']))->order('id desc')->find());
        if ($resume_info['level'] == 1) {
            $points_rule = D('Task')->get_task_cache(2, 'complete_60');
        } else if ($resume_info['level'] == 2) {
            $points_rule = D('Task')->get_task_cache(2, 'complete_90');
        } else {
            $points_rule = D('Task')->get_task_cache(2, 'submit_resume');
        }
        $this->assign('points_rule', $points_rule);
        //微信扫描绑定
        $user_bind = M('MembersBind')->where(array('uid' => $uid))->getfield('type,keyid,is_focus');
        $this->assign('hidden_perfect_notice', cookie($uid . '_hidden_perfect_notice'));
        $this->assign('current', D('Category')->get_category_cache('QS_current'));
        $this->assign('category', $category);
        $this->assign('resume_img', $get_resume_img);//获取简历附件图片
        $this->assign('resume_close', $resume_info['display']);
        $this->assign('view_num', D('ViewResume')->where(array('resumeid' => intval($resume_info['id'])))->count());
        $this->_config_seo(array('title' => '首页 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    public function adv_resume() {
        $this->display();
    }
    /*
    **创建简历成功
    */
    public function resume_check() {
        if (false === $resume = $this->_is_resume()) $this->error('简历不存在或已经删除!');
        $this->_config_seo(array('title' => '创建简历 - 个人会员中心 - ' . C('qscms_site_name')));
        $add_tag = session('add_tag');
        session('add_tag', 0);
        $this->assign('add_tag', $add_tag);
        $this->display();
    }
    /**
     * [ajax_save_basic_info ajax修改简历基本信息]
     */
    public function ajax_save_basic_info() {
        //if(false === $resume = $this->_is_resume()) $this->ajaxReturn(0,'简历不存在或已经删除!');
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
        if ($rst['state']) $this->ajaxReturn(1, '数据保存成功！');
        $this->ajaxReturn(0, $rst['error']);
    }

    /*
    **修改求职意向
    */
    public function ajax_save_basic() {
        //$pid = I('post.pid',0,'intval');
        //!$pid && $this->ajaxReturn(0,'请选择简历！');
        $setsqlarr['intention_jobs_id'] = I('post.intention_jobs_id', '', 'trim,badword');
        $setsqlarr['trade'] = I('post.trade', '', 'trim,badword');//期望行业
        $setsqlarr['district'] = I('post.district', '', 'trim,badword');//工作地区
        $setsqlarr['nature'] = I('post.nature', 0, 'intval');//工作性质
        $setsqlarr['current'] = I('post.current', 0, 'intval');
        $setsqlarr['wage'] = I('post.wage', 0, 'intval');//期望薪资
        if ($resume = $this->_is_resume()) {
            $rst = D('AdvResume')->save_resume($setsqlarr, $resume['id'], C('visitor'));
        } else {
            $rst = D('AdvResume')->add_resume($setsqlarr, C('visitor'));
        }
        if ($rst['state']) $this->ajaxReturn(1, '求职意向修改成功！', $rst['attach']);
        $this->ajaxReturn(0, $rst['error']);
    }

    /**
     * [_edit_data AJAX获取被修改数据]
     */
    protected function _edit_data($type) {
        $id = I('get.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请求缺少参数！');
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $data = M($type)->where(array('id' => $id, 'uid' => C('visitor.uid'), 'pid' => $resume['id']))->find();
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

    //修改语言
    public function edit_language() {
        if (galse === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $uid = C('visitor.uid');
        $language_list = M('AdvResumeLanguage')->where(array('pid' => $resume['id'], 'uid' => $uid))->select();
        !$language_list && $language_list = array(array('id' => 0));
        $category = D('Category')->get_category_cache();
        $this->assign('language', $category['QS_language']);
        $this->assign('language_level', $category['QS_language_level']);
        $this->assign('list', $language_list);
        $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_language_edit_list');
        $this->ajaxReturn(1, '语言能力获取成功！', $data);
    }

    //修改证书
    public function edit_credent() {
        $this->_edit_data('adv_resume_credent');
    }

    /**
     * [_del_data 删除简历信息]
     */
    protected function _del_data($type) {
        $id = I('request.id', 0, 'intval');
        $pid = I('request.pid', 0, 'intval');
        if (!$pid || !$id) $this->ajaxReturn(0, '请求缺少参数！');
        if (IS_POST) {
            $uid = C('visitor.uid');
            $user = D('Members')->find($uid);
            if (M($type)->where(array('id' => $id, 'uid' => $uid, 'pid' => $pid))->delete()) {
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
                $this->ajaxReturn(1, '删除成功！');
            } else {
                $this->ajaxReturn(0, '删除失败！');
            }
        } else {
            switch ($type) {
                case 'AdvResumeEducation':
                    $s = '教育经历';
                    break;
                case 'AdvResumeWork':
                    $s = '工作经历';
                    break;
                case 'AdvResumeTraining':
                    $s = '培训经历';
                    break;
                case 'AdvResumeLanguage':
                    $s = '语言能力';
                    break;
                case 'AdvResumeCredent':
                    $s = '证书';
                    break;
            }
            $tip = '删除后将无法恢复，您确定要删除该' . $s . '吗？';
            $this->ajax_warning($tip);
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
        $this->_del_data('ResumeCredentTop');
    }

    /**
     * [_ajax_list ajax获取简历信息列表]
     * @param  [type] $type  [要查的数据表名]
     * @param  [type] $field [要附加的字段名称]
     */
    protected function _ajax_list($type, $fields) {
        $pid = I('get.pid', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '请选择简历！');
        $uid = C('visitor.uid');
        $field = $fields ? 'id,pid,' . $fields : 'id,pid';
        if ($dataInfo = M($type)->field($field)->where(array('pid' => $pid, 'uid' => $uid))->select()) {
            $this->assign('list', $dataInfo);
            $data['list'] = 1;
        }
        $data['html'] = $this->fetch('Personal/ajax_tpl/' . strtolower(ACTION_NAME));
        $this->ajaxReturn(1, '数据读取成功！', $data);
    }

    //获取教育经历列表
    public function ajax_get_education_list() {
        $this->_ajax_list('adv_resume_education', 'startyear,startmonth,endyear,endmonth,school,speciality,education_cn,todate');
    }

    //工作经历
    public function ajax_get_work_list() {
        $this->_ajax_list('adv_resume_work', 'companyname,jobs,achievements,startyear,startmonth,endyear,endmonth,todate');
    }

    //培训经历
    public function ajax_get_training_list() {
        $this->_ajax_list('adv_resume_training', 'startyear,startmonth,endyear,endmonth,agency,course,description,todate');
    }

    //语言能力
    public function ajax_get_language_list() {
        $this->_ajax_list('adv_resume_language', 'language_cn,level_cn');
    }

    //获得证书
    public function ajax_get_credent_list() {
        $this->_ajax_list('adv_resume_credent', 'name,year,month');
    }

    //添加||修改教育经历
    public function save_education() {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['school'] = I('post.school', '', 'trim,badword');
        $setsqlarr['speciality'] = I('post.speciality', '', 'trim,badword');
        $setsqlarr['education'] = I('post.education', 0, 'intval');
        $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
        $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择就读时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择就读时间！');

            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '就读结束时间不允许大于当前时间！');

            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
        }
        $education = D('Category')->get_category_cache('QS_education');
        $setsqlarr['education_cn'] = $education[$setsqlarr['education']];
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $education = M('AdvResumeEducation')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取教育经历数量
        if (count($education) >= 6) $this->ajaxReturn(0, '教育经历不能超过6条！');
        if ($id = I('post.id', 0, 'intval')) {
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
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_education_list');
            $this->ajaxReturn(1, '教育经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加||修改工作经历
    public function save_work() {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['companyname'] = I('post.companyname', '', 'trim,badword');
        $setsqlarr['achievements'] = I('post.achievements', '', 'trim,badword');
        $setsqlarr['jobs'] = I('post.jobs', '', 'trim,badword');
        $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
        $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择工作时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择工作时间！');

            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');

            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '工作开始时间不允许大于结束时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '工作开始时间需小于结束时间！');
        }
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $work = M('AdvResumeWork')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取教育经历数量
        if (count($work) >= 6) $this->ajaxReturn(0, '工作经历不能超过6条！');
        if ($id = I('post.id', 0, 'intval')) {
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
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_work_list');
            $this->ajaxReturn(1, '工作经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加||修改培训经历
    public function save_training() {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['agency'] = I('post.agency', '', 'trim,badword');
        $setsqlarr['course'] = I('post.course', '', 'trim,badword');
        $setsqlarr['description'] = I('post.description', '', 'trim,badword');
        $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
        $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择培训时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '培训开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '培训开始时间需小于毕业时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择培训时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '培训开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '培训开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '培训结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '培训结束时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '培训开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '培训开始时间需小于毕业时间！');
        }
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        $training = M('AdvResumeTraining')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取教育经历数量
        if (count($training) >= 6) $this->ajaxReturn(0, '培训经历不能超过6条！');
        if ($id = I('post.id', 0, 'intval')) {
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
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_training_list');
            $this->ajaxReturn(1, '培训经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加修改语言能力
    public function save_language() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $uid = C('visitor.uid');
        $language = I('post.language');
        if (count($language) > 6) $this->ajaxReturn(0, '语言能力不能超过6条！');
        M('AdvResumeLanguage')->where(array('pid' => $resume['id'], 'uid' => $uid))->delete();
        $category = D('Category')->get_category_cache();
        foreach ($language as $key => $val) {
            $language['language'] = intval($val);
            if ($language_list[$language['language']]) continue;
            $language['uid'] = $uid;
            $language['pid'] = $resume['id'];
            $language['level'] = intval($_POST['level'][$key]);
            $language['language_cn'] = $category['QS_language'][$language['language']];
            $language['level_cn'] = $category['QS_language_level'][$language['level']];
            if (!$language['id'] = D('AdvResumeLanguage')->add_resume_language($language, C('visitor'))) {
                $this->ajaxReturn(0, '语言能力保存失败！');
            }
            $language_list[$language['language']] = $language;
        }
        $this->assign('list', $language_list);
        D('AdvResume')->save_resume('', $resume['id'], C('visitor'));
        $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_language_list');
        $this->ajaxReturn(1, '语言能力保存成功！', $data);
    }

    //添加||修改获得证书
    public function save_credent() {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['name'] = I('post.name', '', 'trim,badword');
        $setsqlarr['year'] = I('post.year', '', 'trim,badword');
        $setsqlarr['month'] = I('post.month', '', 'trim,badword');
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');

        if (!$setsqlarr['year'] || !$setsqlarr['month']) $this->ajaxReturn(0, '请选择获得证书时间！');
        if ($setsqlarr['year'] > intval(date('Y'))) $this->ajaxReturn(0, '获得证书时间不能大于当前时间！');
        if ($setsqlarr['year'] == intval(date('Y')) && $setsqlarr['month'] > intval(date('m'))) $this->ajaxReturn(0, '获得证书时间不能大于当前时间！');

        $setsqlarr['pid'] = $resume['id'];
        $credent = M('AdvResumeCredent')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->select();//获取证书数量
        if (count($credent) >= 6) $this->ajaxReturn(0, '证书不能超过6条！');
        if ($id = I('post.id', 0, 'intval')) {
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
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_credent_list');
            $this->ajaxReturn(1, '证书保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    /*
    **自我描述
    */
    public function ajax_save_specialty() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $specialty = I('post.specialty', '', 'trim,badword');
        !$specialty && $this->ajaxReturn(0, '请输入自我描述!');
        $rst = D('AdvResume')->save_resume(array('specialty' => $specialty), $resume['id'], C('visitor'));
        $pid = $resume['id'];
        if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
        write_members_log(C('visitor'), 'resume', '保存简历自我描述（简历id：' . $pid . '）', false, array('resume_id' => $pid));
        $this->ajaxReturn(1, '简历自我描述修改成功');
    }

    /*
    **特长标签start
    */
    public function ajax_save_tag() {
        $pid = I('post.pid', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '请正确选择简历！');
        $uid = C('visitor.uid');
        $tag_cn = I('post.tag_cn', '', 'badword');
        $setarr['tag_cn'] = $tag_cn ? implode(",", $tag_cn) : '';
        $tag = I('post.tag', '', 'badword');
        $setarr['tag'] = $tag ? implode(",", $tag) : '';
        $tags = D('Category')->get_category_cache('QS_resumetag');
        foreach ($tag as $key => $val) {
            $setarr['tag_cn'] .= ",{$tags[$val]}";
        }
        $setarr['tag_cn'] = ltrim($setarr['tag_cn'], ',');
        if (!$setarr['tag_cn']) $s = 2;
        $resume_mod = D('AdvResume');
        if (false !== $resume_mod->where(array('id' => $pid, 'uid' => $uid))->save($setarr)) {
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '修改简历特长标签（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            D('AdvResume')->save_resume('', $pid, C('visitor'));
            $this->ajaxReturn(1, '简历特长标签修改成功！');
        }
        $this->ajaxReturn(0, '保存失败！');
    }

    /*
    **删除简历附件
    */
    public function ajax_resume_img_del() {
        if (IS_POST) {
            $img_id = I('request.id', 0, 'intval');
            !$img_id && $this->ajaxReturn(0, '请选择要删除的图片！');
            $uid = C('visitor.uid');
            $img_mod = M('AdvResumeImg');
            $row = $img_mod->where(array('id' => $img_id, 'uid' => $uid))->field('img,resume_id')->find();
            $size = explode(',', C('qscms_resume_img_size'));
            if (strpos($row['img'], '..') !== false) die('Error Img.');
            @unlink(C('qscms_attach_path') . "photo/" . $row['img']);
            if (C('qscms_qiniu_open') == 1) {
                $qiniu = new \Common\ORG\qiniu;
                $qiniu->delete($row['img']);
            }
            foreach ($size as $val) {
                @unlink(C('qscms_attach_path') . "photo/{$row['img']}_{$val}x{$val}.jpg");
                if (C('qscms_qiniu_open') == 1) {
                    $thumb_name = $qiniu->getThumbName($row['img'], $val, $val);
                    $qiniu->delete($thumb_name);
                }
            }
            if (false === $img_mod->where(array('id' => $img_id, 'uid' => $uid))->delete()) $this->ajaxReturn(0, '删除失败！');
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '删除简历图片（简历id：' . intval($row['resume_id']) . '）', false, array('resume_id' => intval($row['resume_id'])));
            $this->ajaxReturn(1, '删除成功！');
        } else {
            $tip = '删除后将无法恢复，您确定要删除该条数据吗？';
            $this->ajax_warning($tip);
        }
    }

    /**
     * [ajax_resume_attach 保存照片/作品]
     */
    public function ajax_resume_attach() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $img_mod = M('AdvResumeImg');
        $data['resume_id'] = $resume['id'];
        $data['id'] = I('post.id', 0, 'intval');
        $data['uid'] = C('visitor.uid');
        $img = $img_mod->where(array('uid' => $data['uid'], 'id' => $data['id'], 'resume_id' => $data['resume_id']))->find();
        if (!$img) {
            $this->ajaxReturn(0, '作品不存在！');
        }
        //$data['title'] = I('post.title','','trim,badword');
        $data['img'] = $img['img'];
        $reg = D('AdvResumeImg')->save_resume_img($data);
        if ($reg['state']) {
            $this->ajaxReturn(1, '附件添加成功！', $reg['id']);
        }
        $this->ajaxReturn(0, $reg['error']);
    }

    /**
     * 初始化照片/作品的扫码监听
     */
    public function ajax_resume_img_scan() {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        S('resume_img_count' . $resume['id'], null);
        $img_count = M('AdvResumeImg')->where(array('resume_id' => $resume['id']))->count();//获取照片作品数量
        S('resume_img_count' . $resume['id'], $img_count);
        $this->ajaxReturn(1, '开始监听！');
    }
    /*
    **删除word简历
    */
    public function ajax_word_del() {
        $warning = I('request.warning', 0, 'intval');
        if ($warning) {
            $tip = '删除后将无法恢复，您确定要删除该word简历吗？';
            $this->ajax_warning($tip);
        } else {
            if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
            if ($resume['word_resume']) {
                @unlink(C('qscms_attach_path') . "top_word_resume/" . $resume['word_resume']);
                if (C('qscms_qiniu_open') == 1) {
                    $qiniu = new \Common\ORG\qiniu;
                    $qiniu->delete($resume['word_resume']);
                }
                $resume_mod = D('AdvResume');
                if (false === $resume_mod->where(array('id' => $resume['id']))->setfield('word_resume', '')) $this->ajaxReturn(1, '删除失败！');
                //写入会员日志
                write_members_log(C('visitor'), 'resume', '删除word简历（简历id：' . intval($resume['id']) . '）', false, array('resume_id' => intval($resume['id'])));
                $this->ajaxReturn(1, '删除成功！');
            }
            $this->ajaxReturn(0, 'word简历已删除或不存在！');
        }
    }

    public function save_photo_display() {
        $setsqlarr = I('post.');
        if ($setsqlarr['photo_display'] == 1) {
            $setsqlarr['photo'] = 1;
        } else {
            $setsqlarr['photo'] = 0;
        }
        if (true !== $reg = D('Members')->update_user_info($setsqlarr, C('visitor'))) $this->ajaxReturn(0, $reg);
        $this->ajaxReturn(1, '保存成功！');
    }

}

?>