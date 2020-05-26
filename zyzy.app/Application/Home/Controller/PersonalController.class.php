<?php

namespace Home\Controller;

use Common\Controller\FrontendController;

class PersonalController extends FrontendController
{
    public function _initialize()
    {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'), '', 1);
            //非ajax的跳转页面
            $this->redirect('members/login');
        }
        //添加meta的链接
        $canonical = C('qscms_site_domain') . $_SERVER['REQUEST_URI'];
        $this->assign('canonical', $canonical);
        //end
        //分站判断
        /* 	if(C('qscms_subsite_open')==1 && C('subsite_info.s_id') >0 && !in_array(ACTION_NAME,array('resume_add','ajax_save_basic_info','ajax_save_basic'))){
			C('subsite_info.s_id',0);
		} */
        if (C('visitor.utype') != 2) {
            IS_AJAX && $this->ajaxReturn(0, '请登录个人帐号！');
            $this->redirect('members/index');
        }
        !IS_AJAX && $this->_global_variable();
    }

    protected function _global_variable()
    {
        // 帐号状态 为暂停
        if (C('visitor.status') == 2 && !in_array(ACTION_NAME, array('index'))) {
            $this->error('您的账号处于暂停状态，请联系管理员设为正常后进行操作！', U('Personal/index'));
        }
        $resume_count = D('Resume')->count_resume(array('uid' => C('visitor.uid'))); //当前用户简历份数
        if (!$resume_count && !in_array(ACTION_NAME, array('resume_add', 'ajax_enroll'))) {
            $this->redirect('personal/resume_add');
        } elseif ($resume_count && in_array(ACTION_NAME, array('resume_add'))) {
            $this->redirect('personal/index');
        }
        if (!C('qscms_login_refresh_resume') && !S('personal_login_first_' . C('visitor.uid'))) {
            S('personal_login_first_' . C('visitor.uid'), 1, 86400 - (time() - strtotime("today")));
            if ($resume_count > 0) {
                $resume = M('Resume')->where(array('uid' => C('visitor.uid')))->order('def desc')->limit(1)->find(); //当前用户默认简历内容
                $this->assign('resume', $resume); //当前用户简历内容
            }
        }
        if (C('qscms_share_allowance_open') && C('qscms_inviter_perfected_resume_allowance_open')) {
            $url = U('personal/share_allowance_partake');
        } elseif (C('qscms_share_allowance_open') && !C('qscms_inviter_perfected_resume_allowance_open')) {
            $url = U('personal/share_allowance_partake');
        } elseif (!C('qscms_share_allowance_open') && C('qscms_inviter_perfected_resume_allowance_open')) {
            $url = U('personal/invite_friend');
        }
        $this->assign('url', $url);
        $this->assign('personal_nav', ACTION_NAME);
    }

    /**
     * [_is_resume 检测简历是否存在]
     * @return boolean [false || 简历信息(按需要添加字段)]
     */
    protected function _is_resume($pid)
    {
        !$pid && $pid = I('request.pid', 0, 'intval');
        if (!$pid) {
            IS_AJAX && $this->ajaxReturn(0, '请正确选择简历！');
            $this->error('请正确选择简历！');
        }
        //$field = 'id,uid,title,fullname,sex,nature,nature_cn,trade,trade_cn,birthdate,residence,height,marriage_cn,experience_cn,district_cn,wage_cn,householdaddress,education_cn,major_cn,tag,tag_cn,telephone,email,intention_jobs,photo_img,complete_percent,current,current_cn,word_resume';
        if (!$reg = M('Resume')->field()->where(array('id' => $pid, 'uid' => C('visitor.uid')))->find()) return false;
        $reg['height'] = $reg['height'] == 0 ? '' : $reg['height'];
        if ($reg['audit'] == 2) {
            $reg['_audit'] = C('qscms_resume_display') == 2 ? 1 : $reg['audit']; // 先显示再审核
        } else {
            $reg['_audit'] = $reg['audit'];
        }
        $this->assign('resume', $reg);
        return $reg;
    }
    /*
    **保存到桌面
    */
    /*public function shortcut(){
        $Shortcut = "[InternetShortcut]
        URL=".C('qscms_site_domain').C('qscms_site_dir')."?lnk
        IDList= 
        IconFile=".C('qscms_site_domain').C('qscms_site_dir')."favicon.ico
        IconIndex=100
        [{000214A0-0000-0000-C000-000000000046}]
        Prop3=19,2";
        header("Content-type: application/octet-stream"); 
        header("Content-Disposition: attachment; filename=".C('qscms_site_name').".url;"); 
        exit($Shortcut);
    }*/
    /*
    **个人会员中心首页
    */
    public function index()
    {
        session('error_login_count', 0);
        $uid = C('visitor.uid');
        $resume_list = D('Resume')->get_resume_list(array('where' => array('uid' => $uid), 'order' => 'def desc', 'countinterview' => true, 'countdown' => true, 'countapply' => true, 'views' => true, 'stick' => true));
        $this->assign('points', D('MembersPoints')->get_user_points($uid)); //当前用户积分数
        $resume_info = $resume_list[0];
        $resume_info['tag_cn'] = $resume_info['tag_cn'] ? explode(',', $resume_info['tag_cn']) : array();
        $category = D('Category')->get_category_cache();
        $get_resume_img = M('ResumeImg')->where(array('resume_id' => $resume_info['id']))->select(); //获取简历附件图片
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
        $this->ajax_get_interest_jobs('recommend_jobs');
        //微信扫描绑定
        $user_bind = M('MembersBind')->where(array('uid' => $uid))->getfield('type,keyid,is_focus');
        if (C('qscms_weixin_apiopen') && !$user_bind['weixin']['is_focus']) {
            if (!S('weixin_focus_per_first_' . C('visitor.uid')) && !C('visitor.wx_frame_status')) {
                S('weixin_focus_per_first_' . C('visitor.uid'), 1, 86400 - (time() - strtotime("today")));
                $this->assign('weixin_focus', 1);
            }
            $this->assign('weixin_img', \Common\qscmslib\weixin::qrcode_img(array('type' => 'sync', 'width' => 78, 'height' => 78, 'params' => C('visitor.uid'))));
        }
        $this->assign('wx_frame_status', cookie('wx_frame_status'));
        $this->assign('hidden_perfect_notice', cookie($uid . '_hidden_perfect_notice'));
        $this->assign('current', D('Category')->get_category_cache('QS_current'));
        $this->assign('category', $category);
        $this->assign('resume_img', $get_resume_img); //获取简历附件图片
        $this->assign('resume_close', $resume_info['display']);
        $this->assign('view_num', D('ViewResume')->where(array('resumeid' => intval($resume_info['id'])))->count());
        $this->_config_seo(array('title' => '首页 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * [ajax_get_interest_jobs ajax推荐职位]
     * type recommend_jobs,nearby_jobs,new_jobs
     */
    public function ajax_get_interest_jobs($type = '')
    {
        $type = $type ? $type : I('get.type', '', 'trim,badword');
        !$type && IS_AJAX && $this->ajaxReturn(0, '数据类型错误！');
        if (!in_array($type, array('recommend_jobs', 'nearby_jobs', 'new_jobs'))) $this->ajaxReturn(0, '数据类型错误！');
        $where = array(
            '显示数目' => '12',
            '分页显示' => 1
        );
        if ($type == 'recommend_jobs') {
            $jobcategory = M('Resume')->where(array('uid' => C('visitor.uid')))->getField('intention_jobs_id', true);
            $where['职位分类'] = $jobcategory;
            $where['排序'] = 'stickrtime';
            $msg = "没有合适的推荐职位！";
        } elseif ($type == 'nearby_jobs') {
            $where['经度'] = I('get.lng', '0', 'trim,badword'); //112.732929
            $where['纬度'] = I('get.lat', '0', 'trim,badword'); //37.714684
            $msg = "没有找到附近的职位！";
        } else {
            $where['排序'] = 'rtime';
            $msg = "没有找到最新的职位！";
        }
        $jobs_mod = new \Common\qscmstag\jobs_listTag($where);
        $jobs_list = $jobs_mod->run();
        $this->assign('msg', $msg);
        $this->assign('jobs_list', $jobs_list['list']);
        if (IS_AJAX) {
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_jobs_list');
            $data['isfull'] = $jobs_list['page_params']['nowPage'] >= $jobs_list['page_params']['totalPages'];
            $this->ajaxReturn(1, '职位信息获取成功！', $data);
        }
    }

    /*
    **个人会员中心刷新简历弹窗
    */
    public function ajax_refresh_resume()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $current = D('Category')->get_category_cache('QS_current');
        $this->assign('current', $current);
        $tpl = $this->fetch('Personal/ajax_tpl/ajax_refresh_resume');
        $this->ajaxReturn(1, '', $tpl);
    }

    /**
     * [refresh_resume 刷新简历]
     */
    public function refresh_resume()
    {
        if (IS_AJAX) {
            $pid = I('post.pid', 0, 'intval');
            !$pid && $pid = M('Resume')->where(array('uid' => C('visitor.uid')))->order('def desc')->limit(1)->getField('id');
            $uid = C('visitor.uid');
            $r = D('Resume')->get_resume_list(array('where' => array('uid' => $uid, 'id' => $pid), 'field' => 'id,title,audit,display'));
            !$r && $this->ajaxReturn(0, "选择的简历不存在！");
            $r[0]['_audit'] != 1 && $this->ajaxReturn(0, "审核中或未通过的简历无法刷新！");
            $r[0]['display'] != 1 && $this->ajaxReturn(0, "简历已关闭，无法刷新！");
            $refresh_log = M('RefreshLog');
            $refrestime = $refresh_log->where(array('uid' => $uid, 'type' => 2001))->order('addtime desc')->getfield('addtime');
            $duringtime = time() - $refrestime;
            $space = C('qscms_per_refresh_resume_space') * 60;
            $today = strtotime(date('Y-m-d'));
            $tomorrow = $today + 3600 * 24;
            $count = $refresh_log->where(array('uid' => $uid, 'type' => 2001, 'addtime' => array('BETWEEN', array($today, $tomorrow))))->count();
            if (C('qscms_per_refresh_resume_time') != 0 && ($count >= C('qscms_per_refresh_resume_time'))) {
                $this->ajaxReturn(0, "每天最多可刷新 " . C('qscms_per_refresh_resume_time') . " 次，您今天已达到最大刷新次数！");
            } elseif ($duringtime <= $space && $space != 0) {
                $this->ajaxReturn(0, C('qscms_per_refresh_resume_space') . " 分钟内不允许重复刷新简历！");
            } else {
                $r = D('Resume')->refresh_resume($pid, C('visitor'));
                $this->ajaxReturn(1, '刷新简历成功！', $r['data']);
            }
        }
    }

    /**
     * [jobs_matching_list 匹配职位]
     */
    public function jobs_matching_list()
    {
        $this->_config_seo(array('title' => '匹配职位 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * [jobs_matching_list 我的红包]
     */
    public function job_money()
    {
        $this->display();
    }

    /**
     * [save_shield_company 添加屏蔽企业关健字]
     */
    public function save_shield_company()
    {
        $keyword = I('post.comkeyword', '', 'trim,badword');
        !$keyword && $this->ajaxReturn(0, '企业关健字不能为空！');
        $data['uid'] = C('visitor.uid');
        if (10 <= $count = M('PersonalShieldCompany')->where($data)->count()) $this->ajaxReturn(0, '您最多屏蔽 10 个企业关键词！');
        $data['comkeyword'] = $keyword;
        $shield_mod = D('PersonalShieldCompany');
        if (false === $shield_mod->create($data)) $this->ajaxReturn(0, $shield_mod->getError());
        if (false === $data['id'] = $shield_mod->add()) $this->ajaxReturn(0, '企业关健字添加失败，请重新添加！');
        //写入会员日志
        write_members_log(C('visitor'), '', '添加屏蔽企业（关键字：' . $keyword . '）');
        $this->ajaxReturn(1, '企业关健字添加成功！', $data);
    }

    /**
     * [del_shield_company 删除屏蔽企业关健字]
     */
    public function del_shield_company()
    {
        $keyword_id = I('request.keyword_id', 0, 'intval');
        !$keyword_id && $this->ajaxReturn(0, '请选择关健字！');
        $uid = C('visitor.uid');
        if (IS_POST) {
            if ($reg = M('PersonalShieldCompany')->where(array('id' => $keyword_id, 'uid' => C('visitor.uid')))->delete()) {
                //写入会员日志
                write_members_log(C('visitor'), '', '删除屏蔽企业（id：' . $keyword_id . '）');
                $this->ajaxReturn(1, '关健字删除成功！');
            }
            $reg === false && $this->ajaxReturn(0, '关健字删除失败！');
            $this->ajaxReturn(0, '关健字不存在或已经删除！');
        } else {
            $tip = '删除后无法恢复，您确定要删除该关键字吗？';
            $this->ajax_warning($tip);
        }
    }

    /*
    **隐私设置
    */
    public function resume_privacy()
    {
        $keywords = M('PersonalShieldCompany')->where(array('uid' => C('visitor.uid')))->select();
        $this->assign('keywords', $keywords);
        $this->_config_seo(array('title' => '隐私设置 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'user_info');
        $this->display();
    }

    /*
    **隐私设置更新数据库
    */
    public function save_resume_privacy()
    {
        $pid = I('post.pid', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '请选择简历!');
        $setsqlarr['display'] = I('post.display', 0, 'intval');
        // $setsqlarr['display_name']=I('post.display_name',0,'intval');
        // $setsqlarr['photo_display']=I('post.photo_display',0,'intval');
        $uid = C('visitor.uid');
        $where = array('id' => $pid, 'uid' => $uid);
        if (false !== M('Resume')->where($where)->save($setsqlarr)) {
            $reg = D('Resume')->resume_index($pid);
            if (!$reg['state']) $this->ajaxReturn(0, $reg['error']);
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '保存显示/隐藏设置（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            $this->ajaxReturn(1, '显示/隐藏设置成功!');
        } else {
            $this->ajaxReturn(0, '显示/隐藏设置失败，请重新操作!');
        }
    }

    /*
    **委托简历选择弹窗
    */
    public function entrust()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $data['entrust'] = $resume['entrust'];
        if ($data['entrust'] > 0) {
            $entrust_info = D('ResumeEntrust')->where(array('resume_id' => $resume['id']))->find();
            $this->assign('entrust_info', $entrust_info);
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_entrust_resume_cancel');
        } else {
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_entrust_resume');
        }
        $this->ajaxReturn(1, '', $data);
    }

    /*
    **委托简历更新数据库
    */
    public function set_entrust()
    {
        $uid = C('visitor.uid');
        $pid = I('post.pid', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '您没有选择简历!');
        $setsqlarr['entrust'] = I('post.entrust', 0, 'intval');
        $setsqlarr['entrust_start'] = time();
        switch ($setsqlarr['entrust']) {
            case '3':
                $setsqlarr['entrust_end'] = strtotime("+3 day");
                break;
            case '7':
                $setsqlarr['entrust_end'] = strtotime("+7 day");
                break;
            case '14':
                $setsqlarr['entrust_end'] = strtotime("+14 day");
                break;
            case '30':
                $setsqlarr['entrust_end'] = strtotime("+30 day");
                break;
            default:
                $this->ajaxReturn(0, '请正确选择委托时间!');
        }
        //设置简历委托
        if (D('ResumeEntrust')->set_resume_entrust($pid, $uid, $setsqlarr)) {
            M('Resume')->where(array('id' => $pid, 'uid' => $uid))->setfield('entrust', $setsqlarr['entrust']);
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '设置简历委托（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            $this->ajaxReturn(1, '委托成功!');
        } else {
            $this->ajaxReturn(0, '委托失败!');
        }
    }

    /*
    **取消委托简历更新数据库
    */
    public function set_entrust_del()
    {
        $pid = I('post.pid', 0, 'intval');
        !$pid && $this->ajaxReturn(0, '您没有选择简历！!');
        $uid = C('visitor.uid');
        if (false !== M('ResumeEntrust')->where(array('resume_id' => $pid, 'uid' => $uid))->delete()) {
            M('Resume')->where(array('id' => $pid, 'uid' => $uid))->setfield('entrust', 0);
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '取消简历委托（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            $this->ajaxReturn(1, '取消委托成功！');
        } else {
            $this->ajaxReturn(0, '取消委托失败！');
        }
    }

    /**
     * [resume_tpl 获取简历模板]
     */
    public function resume_tpl()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $tplid_list = D('ResumeTpl')->where(array('uid' => C('visitor.uid')))->field('tplid')->select();
        $tplid_arr = array();
        foreach ($tplid_list as $key => $value) {
            $tplid_arr[] = $value['tplid'];
        }
        if ($tplid_arr) {
            $tpl_list = D('Tpl')->where(array('tpl_id' => array('in', $tplid_arr)))->select();
        } else {
            $tpl_list = array();
        }
        foreach ($tpl_list as $key => $value) {
            $tpl_list[$key]['thumb_dir'] = __RESUME__ . '/' . $value['tpl_dir'];
        }
        $this->assign('tpl_list', $tpl_list);
        $resume['tpl'] = $resume['tpl'] == '' ? 'default' : $resume['tpl'];
        $this->assign('resume', $resume);
        $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_change_resume_tpl');
        $this->ajaxReturn(1, '', $data);
    }

    public function set_tpl()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $tpl = I('post.tpl', '', 'trim');
        $reg = D('Tpl')->resume_tpl($tpl, C('visitor'));
        if ($reg['state']) {
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '更换简历模板（简历id：' . $resume['id'] . '，模板：' . $tpl . '）', false, array('resume_id' => $resume['id']));
            $this->ajaxReturn(1, '简历模板更换成功！');
        }
        $this->ajaxReturn(0, $reg['error']);
    }

    /*
    **删除简历更新数据库
    */
    public function set_del_resume()
    {
        $id = I('request.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '您没有选择简历！');
        $resume_num = D('Resume')->count_resume(array('uid' => C('visitor.uid')));
        if (IS_POST) {
            if ($resume_num == 1) {
                $this->ajaxReturn(0, '删除失败，您至少要保留一份简历！');
            }
            $current = D('Resume')->get_resume_one($id);
            if (true === $reg = D('Resume')->del_resume(C('visitor'), $id)) {
                if ($current['def'] == 1) {
                    D('Resume')->where(array('uid' => C('visitor.uid')))->order('complete_percent desc')->limit(1)->setField('def', 1);
                }
                $this->ajaxReturn(1, '简历删除成功！');
            } else {
                $this->ajaxReturn(0, '删除失败！');
            }
        } else {
            if ($resume_num == 1) {
                $tip = '该简历无法删除，请至少保留一份简历！';
                $could = 0;
            } else {
                $tip = '您确定要删除该份简历吗？';
                $could = 1;
            }
            $description = '如果您目前暂无求职意向，将简历状态设置为【保密】即可免受企业骚扰。';
            $this->ajax_warning($tip, $description, $could);
        }
    }

    /**
     * [set_default 默认简历设置]
     */
    public function set_default()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        if (!$resume['def']) {
            $reg = M('Resume')->where(array('uid' => C('visitor.uid'), 'def' => 1))->setfield('def', 0);
            false === $reg && $this->ajaxReturn(0, '默认简历设置失败，请重新操作！');
            M('Resume')->where(array('id' => $resume['id']))->setfield('def', 1);
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '设置默认简历（简历id：' . $resume['id'] . '）', false, array('resume_id' => $resume['id']));
        }
        $this->ajaxReturn(1, '默认简历设置成功！');
    }

    /*
    **创建简历-基本信息
    */
    public function resume_add()
    {
        $uid = C('visitor.uid');
        if (IS_POST && IS_AJAX) {
            if (!C('visitor.mobile')) {
                $this->ajaxReturn(0, '您的手机未认证，认证后才能进行其他操作！', 1);
            }
            $ints = array('display_name', 'sex', 'birthdate', 'education', 'major', 'experience', 'email_notify', 'height', 'marriage', 'nature', 'current', 'wage');
            $trims = array('telephone', 'title', 'fullname', 'residence', 'email', 'householdaddress', 'intention_jobs', 'intention_jobs_id', 'trade', 'district', 'qq', 'weixin', 'idcard');
            foreach ($ints as $val) {
                $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
            }
            foreach ($trims as $val) {
                $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
            }
            $resume_count == 0 && $setsqlarr['def'] = 1;
            //分站信息调取
            if (C('subsite_info') && C('subsite_info.s_id') != 0) {
                $setsqlarr['subsite_id'] = C('subsite_info.s_id');
            }
            //end
            $rst = D('Resume')->add_resume($setsqlarr, C('visitor'));
            if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
            $add_tag = I('post.add_tag', 0, 'intval');
            session('add_tag', $add_tag);
            $this->ajaxReturn(1, '简历创建成功！', array('url' => U('personal/resume_guidance', array('pid' => $rst['id']))));
        } else {
            $category = D('Category')->get_category_cache();
            $this->assign('education', $category['QS_education']); //最高学历
            $this->assign('experience', $category['QS_experience']); //工作经验
            $this->assign('current', $category['QS_current']); //目前状态
            $this->assign('jobs_nature', $category['QS_jobs_nature']); //工作性质
            $this->assign('wage', $category['QS_wage']); //期望薪资
            $this->assign('memberinfo', D('Members')->find($uid));
            $this->_config_seo(array('title' => '创建简历 - 个人会员中心 - ' . C('qscms_site_name')));
            $this->display();
        }
    }
    public function resume_guidance()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        if (IS_POST) {
            if ($education = I('post.education')) {
                foreach ($education as $val) {
                    if ($val) {
                        $e = true;
                        break;
                    }
                }
                if ($e) {
                    // 选择至今就不判断结束时间了
                    if ($education['todate'] == 1) {
                        if (!$education['startyear'] || !$education['startmonth']) $this->ajaxReturn(0, '请选择就读时间！');
                        if ($education['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
                        if ($education['startyear'] == intval(date('Y')) && $education['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
                    } else {
                        if (!$education['startyear'] || !$education['startmonth'] || !$education['endyear'] || !$education['endmonth']) $this->ajaxReturn(0, '请选择就读时间！');

                        if ($education['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '就读开始时间不允许大于当前时间！');
                        if ($education['startyear'] == intval(date('Y')) && $education['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '就读开始时间需小于当前时间！');
                        //if($education['endyear'] > intval(date('Y'))) $this->ajaxReturn(0,'就读结束时间不允许大于当前时间！');
                        //if($education['endyear'] == intval(date('Y')) && $education['endmonth'] > intval(date('m'))) $this->ajaxReturn(0,'就读结束时间不允许大于当前时间！');

                        if ($education['startyear'] > $education['endyear']) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
                        if ($education['startyear'] == $education['endyear'] && $education['startmonth'] >= $education['endmonth']) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
                    }
                    $education['pid'] = $resume['id'];
                    $education['uid'] = C('visitor.uid');
                    $educations = D('Category')->get_category_cache('QS_education');
                    $education['education_cn'] = $educations[$education['education']];
                    if (!D('ResumeEducation')->create($education)) {
                        $this->ajaxReturn(0, D('ResumeEducation')->getError());
                    }
                }
            }
            if ($experience = I('post.experience')) {
                foreach ($experience as $val) {
                    if ($val) {
                        $s = true;
                        break;
                    }
                }
                if ($s) {
                    // 选择至今就不判断结束时间了
                    if ($experience['todate'] == 1) {
                        if (!$experience['startyear'] || !$experience['startmonth']) $this->ajaxReturn(0, '请选择工作时间！');
                        if ($experience['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
                        if ($experience['startyear'] == intval(date('Y')) && $experience['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
                    } else {
                        if (!$experience['startyear'] || !$experience['startmonth'] || !$experience['endyear'] || !$experience['endmonth']) $this->ajaxReturn(0, '请选择工作时间！');

                        if ($experience['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作开始时间不允许大于当前时间！');
                        if ($experience['startyear'] == intval(date('Y')) && $experience['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '工作开始时间需小于当前时间！');
                        if ($experience['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');
                        if ($experience['endyear'] == intval(date('Y')) && $experience['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '工作结束时间不允许大于当前时间！');

                        if ($experience['startyear'] > $experience['endyear']) $this->ajaxReturn(0, '工作开始时间不允许大于结束时间！');
                        if ($experience['startyear'] == $experience['endyear'] && $experience['startmonth'] >= $experience['endmonth']) $this->ajaxReturn(0, '工作开始时间需小于结束时间！');
                    }
                    $experience['pid'] = $resume['id'];
                    $experience['uid'] = C('visitor.uid');
                    if (!D('ResumeWork')->create($experience)) {
                        $this->ajaxReturn(0, D('ResumeWork')->getError());
                    }
                }
            }
            if ($e) {
                $reg = D('ResumeEducation')->add_resume_education($education, C('visitor'));
                if (!$reg['state']) $this->ajaxReturn(0, $reg['msg']);
            }
            if ($s) {
                $reg = D('ResumeWork')->add_resume_work($experience, C('visitor'));
                if (!$reg['state']) $this->ajaxReturn(0, $reg['msg']);
            }
            $this->ajaxReturn(1, '简历创建成功！', array('url' => U('personal/resume_check', array('pid' => $resume['id']))));
        } else {
            $category = D('Category')->get_category_cache();
            $this->assign('education', $category['QS_education']); //最高学历
            $this->assign('resume', $resume);
            $this->display();
        }
    }
    /*
    **创建简历成功
    */
    public function resume_check()
    {
        if (false === $resume = $this->_is_resume()) $this->error('简历不存在或已经删除!');
        $this->_config_seo(array('title' => '创建简历 - 个人会员中心 - ' . C('qscms_site_name')));
        $add_tag = session('add_tag');
        session('add_tag', 0);
        $this->assign('add_tag', $add_tag);
        $this->display();
    }

    /**
     * [del_refresh_resume 取消自动刷新]
     */
    public function del_refresh_resume()
    {
        if (IS_AJAX) {
            $uid = C('visitor.uid');
            $pid = I('post.pid', 0, 'intval');
            $del = M('QueueAutoRefresh')->where(array('pid' => $pid, 'uid' => $uid))->delete();
            if ($del) {
                $this->ajaxReturn(1, '取消自动刷新成功');
            }
        }
    }

    /**
     * [del_refresh_resume 取消自动投递]
     */
    public function del_apply_resume()
    {
        if (IS_AJAX) {
            $uid = C('visitor.uid');
            $pid = I('post.resume_id', 0, 'intval');
            $del = M('ResumeEntrust')->where(array('resume_id' => $pid, 'uid' => $uid))->delete();
            M('Resume')->where(array('id' => $pid, 'uid' => $uid))->setfield('entrust', 0);
            if ($del) {
                $this->ajaxReturn(1, '取消自动投递成功');
            }
        }
    }

    /*
    **简历修改
    */
    public function resume_auto_apply()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在或已经删除');
        $uid = C('visitor.uid');
        if (IS_POST) {
            if (1 == $auto_refresh = I('post.auto_refresh', 0, 'intval')) { // 3天内自动刷新
                $auto_mod = M('QueueAutoRefresh');
                $time = time();
                if ($auto_mod->where(array('pid' => $resume['id'], 'type' => 2, 'refreshtime' => array('gt', $time)))->getfield('id')) {
                    $auto_mod->where(array('pid' => $resume['id'], 'type' => 2, 'refreshtime' => array('gt', $time)))->delete();
                }
                $auto_mod->add(array('uid' => $uid, 'pid' => $resume['id'], 'type' => 2, 'refreshtime' => $time + 3600 * 24));
                $auto_mod->add(array('uid' => $uid, 'pid' => $resume['id'], 'type' => 2, 'refreshtime' => $time + 3600 * 24 * 2));
                $auto_mod->add(array('uid' => $uid, 'pid' => $resume['id'], 'type' => 2, 'refreshtime' => $time + 3600 * 24 * 3));
            }
            if (1 == $auto_apply = I('post.auto_apply', 0, 'intval')) { // 3天内委托投递
                if (true !== $reg = D('ResumeEntrust')->set_resume_entrust($resume['id'], $uid)) $this->ajaxReturn(0, $reg);
                M('Resume')->where(array('id' => $resume['id']))->setField('entrust', 3);
            }
            $this->ajaxReturn(1, '简历设置成功！');
        }
    }

    /**
     * [ajax_save_title 修改简历标题]
     */
    public function ajax_save_title()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在或已经删除!');
        $title = I('post.title', '', 'trim,badword');
        $rst = D('Resume')->save_resume(array('title' => $title), $resume['id'], C('visitor'));
        if ($rst['state']) $this->ajaxReturn(1, '数据保存成功！');
        $this->ajaxReturn(0, $rst['error']);
    }

    /**
     * [ajax_save_basic_info ajax修改简历基本信息]
     */
    public function ajax_save_basic_info()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在或已经删除!');
        $ints = array('display_name', 'sex', 'birthdate', 'education', 'major', 'experience', 'email_notify', 'height', 'marriage');
        $trims = array('telephone', 'fullname', 'residence', 'email', 'householdaddress', 'qq', 'weixin', 'idcard');
        foreach ($ints as $val) {
            $setsqlarr[$val] = I('post.' . $val, 0, 'intval');
        }
        foreach ($trims as $val) {
            $setsqlarr[$val] = I('post.' . $val, '', 'trim,badword');
        }
        $rst = D('Resume')->save_resume($setsqlarr, $resume['id'], C('visitor'));
        if ($rst['state']) $this->ajaxReturn(1, '数据保存成功！');
        $this->ajaxReturn(0, $rst['error']);
    }

    /*
    **修改求职意向
    */
    public function ajax_save_basic()
    {
        $resume = $this->_is_resume();
        $setsqlarr['intention_jobs_id'] = I('post.intention_jobs_id', '', 'trim,badword');
        $setsqlarr['trade'] = I('post.trade', '', 'trim,badword'); //期望行业
        $setsqlarr['district'] = I('post.district', '', 'trim,badword'); //工作地区
        $setsqlarr['nature'] = I('post.nature', 0, 'intval'); //工作性质
        $setsqlarr['current'] = I('post.current', 0, 'intval');
        $setsqlarr['wage'] = I('post.wage', 0, 'intval'); //期望薪资
        $rst = D('Resume')->save_resume($setsqlarr, $resume['id'], C('visitor'));
        if ($rst['state']) $this->ajaxReturn(1, '求职意向修改成功！', $rst['attach']);
        $this->ajaxReturn(0, $rst['error']);
    }

    /**
     * [_edit_data AJAX获取被修改数据]
     */
    protected function _edit_data($type)
    {
        $id = I('get.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请求缺少参数！');
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $data = M($type)->where(array('id' => $id, 'uid' => C('visitor.uid'), 'pid' => $resume['id']))->find();
        !$data && $this->ajaxReturn(0, '数据不存在或已经删除！');
        $this->ajaxReturn(1, '数据获取成功！', $data);
    }

    //修改教育经历
    public function edit_education()
    {
        $this->_edit_data('ResumeEducation');
    }

    //修改工作经历
    public function edit_work()
    {
        $this->_edit_data('ResumeWork');
    }

    //修改培训经历
    public function edit_training()
    {
        $this->_edit_data('ResumeTraining');
    }

    //修改项目经历
    public function edit_project()
    {
        $this->_edit_data('ResumeProject');
    }

    //修改语言
    public function edit_language()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $uid = C('visitor.uid');
        $language_list = M('ResumeLanguage')->where(array('pid' => $resume['id'], 'uid' => $uid))->select();
        !$language_list && $language_list = array(array('id' => 0));
        $category = D('Category')->get_category_cache();
        $this->assign('language', $category['QS_language']);
        $this->assign('language_level', $category['QS_language_level']);
        $this->assign('list', $language_list);
        $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_language_edit_list');
        $this->ajaxReturn(1, '语言能力获取成功！', $data);
    }

    //修改证书
    public function edit_credent()
    {
        $this->_edit_data('resume_credent');
    }

    /**
     * [_del_data 删除简历信息]
     */
    protected function _del_data($type)
    {
        $id = I('request.id', 0, 'intval');
        $pid = I('request.pid', 0, 'intval');
        if (!$pid || !$id) $this->ajaxReturn(0, '请求缺少参数！');
        if (IS_POST) {
            $uid = C('visitor.uid');
            $user = D('Members')->find($uid);
            if (M($type)->where(array('id' => $id, 'uid' => $uid, 'pid' => $pid))->delete()) {
                switch ($type) {
                    case 'ResumeEducation':
                        write_members_log($user, 'resume', '删除简历教育经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeWork':
                        write_members_log($user, 'resume', '删除简历工作经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeTraining':
                        write_members_log($user, 'resume', '删除简历培训经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeProject':
                        write_members_log($user, 'resume', '删除简历项目经历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeLanguage':
                        write_members_log($user, 'resume', '删除简历语言能力（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                    case 'ResumeCredent':
                        write_members_log($user, 'resume', '删除简历证书（简历id：' . $pid . '）', false, array('resume_id' => $pid));
                        break;
                }
                $resume_mod = D('Resume');
                $resume_mod->check_resume($uid, $pid); //更新简历完成状态
                $this->ajaxReturn(1, '删除成功！');
            } else {
                $this->ajaxReturn(0, '删除失败！');
            }
        } else {
            switch ($type) {
                case 'ResumeEducation':
                    $s = '教育经历';
                    break;
                case 'ResumeWork':
                    $s = '工作经历';
                    break;
                case 'ResumeTraining':
                    $s = '培训经历';
                    break;
                case 'ResumeProject':
                    $s = '项目经历';
                    break;
                case 'ResumeLanguage':
                    $s = '语言能力';
                    break;
                case 'ResumeCredent':
                    $s = '证书';
                    break;
            }
            $tip = '删除后将无法恢复，您确定要删除该' . $s . '吗？';
            $this->ajax_warning($tip);
        }
    }

    //删除教育经历
    public function del_education()
    {
        $this->_del_data('ResumeEducation');
    }

    //删除工作经历
    public function del_work()
    {
        $this->_del_data('ResumeWork');
    }

    //删除培训经历
    public function del_training()
    {
        $this->_del_data('ResumeTraining');
    }

    //删除项目经历
    public function del_project()
    {
        $this->_del_data('ResumeProject');
    }

    //删除语言能力
    public function del_language()
    {
        $this->_del_data('ResumeLanguage');
    }

    //删除证书
    public function del_credent()
    {
        $this->_del_data('ResumeCredent');
    }

    /**
     * [_ajax_list ajax获取简历信息列表]
     * @param  [type] $type  [要查的数据表名]
     * @param  [type] $field [要附加的字段名称]
     */
    protected function _ajax_list($type, $fields)
    {
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
    public function ajax_get_education_list()
    {
        $this->_ajax_list('resume_education', 'startyear,startmonth,endyear,endmonth,school,speciality,education_cn,todate');
    }

    //工作经历
    public function ajax_get_work_list()
    {
        $this->_ajax_list('resume_work', 'companyname,jobs,achievements,startyear,startmonth,endyear,endmonth,todate');
    }

    //培训经历
    public function ajax_get_training_list()
    {
        $this->_ajax_list('resume_training', 'startyear,startmonth,endyear,endmonth,agency,course,description,todate');
    }

    //项目经历
    public function ajax_get_project_list()
    {
        $this->_ajax_list('resume_project', 'startyear,startmonth,endyear,endmonth,projectname,role,description,todate');
    }

    //语言能力
    public function ajax_get_language_list()
    {
        $this->_ajax_list('resume_language', 'language_cn,level_cn');
    }

    //获得证书
    public function ajax_get_credent_list()
    {
        $this->_ajax_list('resume_credent', 'name,year,month');
    }

    //添加||修改教育经历
    public function save_education()
    {
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
            //if($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0,'就读结束时间不允许大于当前时间！');
            //if($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0,'就读结束时间不允许大于当前时间！');

            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '就读开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '就读开始时间需小于毕业时间！');
        }
        $education = D('Category')->get_category_cache('QS_education');
        $setsqlarr['education_cn'] = $education[$setsqlarr['education']];
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_education';
        } else {
            //2019-10-16  修改数量限制$educationcount
            $educationcount = M('ResumeEducation')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count(); //获取教育经历数量
            if ($educationcount >= 6) $this->ajaxReturn(0, '教育经历不能超过6条！');
            $name = 'add_resume_education';
        }
        $reg = D('ResumeEducation')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('Resume')->save_resume('', $resume['id'], C('visitor'));
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_education_list');
            $this->ajaxReturn(1, '教育经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加||修改工作经历
    public function save_work()
    {
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
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_work';
        } else {
            //2019-10-16  修改数量限制$workcount
            $workcount = M('ResumeWork')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count(); //获取教育经历数量
            if ($workcount >= 6) $this->ajaxReturn(0, '工作经历不能超过6条！');
            $name = 'add_resume_work';
        }
        $reg = D('ResumeWork')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('Resume')->save_resume('', $resume['id'], C('visitor'));
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_work_list');
            $this->ajaxReturn(1, '工作经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加||修改培训经历
    public function save_training()
    {
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
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_training';
        } else {
            //2019-10-16  修改数量限制$trainingcount
            $trainingcount = M('ResumeTraining')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count(); //获取教育经历数量
            if ($trainingcount >= 6) $this->ajaxReturn(0, '培训经历不能超过6条！');
            $name = 'add_resume_training';
        }
        $reg = D('ResumeTraining')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('Resume')->save_resume('', $resume['id'], C('visitor'));
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_training_list');
            $this->ajaxReturn(1, '培训经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加||修改培训经历
    public function save_project()
    {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['projectname'] = I('post.projectname', '', 'trim,badword');
        $setsqlarr['role'] = I('post.role', '', 'trim,badword');
        $setsqlarr['description'] = I('post.description', '', 'trim,badword');
        $setsqlarr['startyear'] = I('post.startyear', 0, 'intval');
        $setsqlarr['startmonth'] = I('post.startmonth', 0, 'intval');
        $setsqlarr['endyear'] = I('post.endyear', 0, 'intval');
        $setsqlarr['endmonth'] = I('post.endmonth', 0, 'intval');
        $setsqlarr['todate'] = I('post.todate', 0, 'intval'); // 至今
        // 选择至今就不判断结束时间了
        if ($setsqlarr['todate'] == 1) {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth']) $this->ajaxReturn(0, '请选择项目时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '项目开始时间不允许大于结束时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '项目开始时间需小于结束时间！');
        } else {
            if (!$setsqlarr['startyear'] || !$setsqlarr['startmonth'] || !$setsqlarr['endyear'] || !$setsqlarr['endmonth']) $this->ajaxReturn(0, '请选择项目时间！');
            if ($setsqlarr['startyear'] > intval(date('Y'))) $this->ajaxReturn(0, '项目开始时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] == intval(date('Y')) && $setsqlarr['startmonth'] >= intval(date('m'))) $this->ajaxReturn(0, '项目开始时间需小于当前时间！');
            if ($setsqlarr['endyear'] > intval(date('Y'))) $this->ajaxReturn(0, '项目结束时间不允许大于当前时间！');
            if ($setsqlarr['endyear'] == intval(date('Y')) && $setsqlarr['endmonth'] > intval(date('m'))) $this->ajaxReturn(0, '项目结束时间不允许大于当前时间！');
            if ($setsqlarr['startyear'] > $setsqlarr['endyear']) $this->ajaxReturn(0, '项目开始时间不允许大于毕业时间！');
            if ($setsqlarr['startyear'] == $setsqlarr['endyear'] && $setsqlarr['startmonth'] >= $setsqlarr['endmonth']) $this->ajaxReturn(0, '项目开始时间需小于毕业时间！');
        }
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $setsqlarr['pid'] = $resume['id'];
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_project';
        } else {
            //2019-10-16  修改数量限制$projectcount
            $projectcount = M('ResumeProject')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count(); //获取教育经历数量
            if ($projectcount >= 6) $this->ajaxReturn(0, '项目经历不能超过6条！');
            $name = 'add_resume_project';
        }
        $reg = D('ResumeProject')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('Resume')->save_resume('', $resume['id'], C('visitor'));
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_project_list');
            $this->ajaxReturn(1, '项目经历保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    //添加修改语言能力
    public function save_language()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $uid = C('visitor.uid');
        $language = I('post.language');
        if (count($language) > 6) $this->ajaxReturn(0, '语言能力不能超过6条！');
        M('ResumeLanguage')->where(array('pid' => $resume['id'], 'uid' => $uid))->delete();
        $category = D('Category')->get_category_cache();
        foreach ($language as $key => $val) {
            $language['language'] = intval($val);
            if ($language_list[$language['language']]) continue;
            $language['uid'] = $uid;
            $language['pid'] = $resume['id'];
            $language['level'] = intval($_POST['level'][$key]);
            $language['language_cn'] = $category['QS_language'][$language['language']];
            $language['level_cn'] = $category['QS_language_level'][$language['level']];
            if (!$language['id'] = D('ResumeLanguage')->add_resume_language($language, C('visitor'))) {
                $this->ajaxReturn(0, '语言能力保存失败！');
            }
            $language_list[$language['language']] = $language;
        }
        $this->assign('list', $language_list);
        D('Resume')->save_resume('', $resume['id'], C('visitor'));
        $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_language_list');
        $this->ajaxReturn(1, '语言能力保存成功！', $data);
    }

    //添加||修改获得证书
    public function save_credent()
    {
        $setsqlarr['uid'] = C('visitor.uid');
        $setsqlarr['name'] = I('post.name', '', 'trim,badword');
        $setsqlarr['year'] = I('post.year', '', 'trim,badword');
        $setsqlarr['month'] = I('post.month', '', 'trim,badword');
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');

        if (!$setsqlarr['year'] || !$setsqlarr['month']) $this->ajaxReturn(0, '请选择获得证书时间！');
        if ($setsqlarr['year'] > intval(date('Y'))) $this->ajaxReturn(0, '获得证书时间不能大于当前时间！');
        if ($setsqlarr['year'] == intval(date('Y')) && $setsqlarr['month'] > intval(date('m'))) $this->ajaxReturn(0, '获得证书时间不能大于当前时间！');

        $setsqlarr['pid'] = $resume['id'];
        if ($id = I('post.id', 0, 'intval')) {
            $setsqlarr['id'] = $id;
            $name = 'save_resume_credent';
        } else {
            //2019-10-16  修改数量限制$credentcount
            $credentcount = M('ResumeCredent')->where(array('pid' => $setsqlarr['pid'], 'uid' => $setsqlarr['uid']))->count(); //获取证书数量
            if ($credentcount >= 6) $this->ajaxReturn(0, '证书不能超过6条！');
            $name = 'add_resume_credent';
        }
        $reg = D('ResumeCredent')->$name($setsqlarr, C('visitor'));
        if ($reg['state']) {
            $setsqlarr['id'] = $reg['id'];
            $this->assign('list', array($setsqlarr));
            D('Resume')->save_resume('', $resume['id'], C('visitor'));
            $data['html'] = $this->fetch('Personal/ajax_tpl/ajax_get_credent_list');
            $this->ajaxReturn(1, '证书保存成功！', $data);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    /*
    **自我描述
    */
    public function ajax_save_specialty()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '请先填写简历基本信息！');
        $specialty = I('post.specialty', '', 'trim,badword');
        !$specialty && $this->ajaxReturn(0, '请输入自我描述!');
        $rst = D('Resume')->save_resume(array('specialty' => $specialty), $resume['id'], C('visitor'));
        if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
        write_members_log(C('visitor'), 'resume', '保存简历自我描述（简历id：' . $pid . '）', false, array('resume_id' => $pid));
        $this->ajaxReturn(1, '简历自我描述修改成功');
    }

    /*
    **特长标签start
    */
    public function ajax_save_tag()
    {
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
        $resume_mod = D('Resume');
        if (false !== $resume_mod->where(array('id' => $pid, 'uid' => $uid))->save($setarr)) {
            //才情start
            if ($tag) {
                foreach ($tag as $k1 => $v1) {
                    $talent_api = new \Common\qscmslib\talent;
                    $talent_api->act = 'resume_tag_add';
                    $talent_api->data = array(
                        'pid' => $pid,
                        'tag' => $v1
                    );
                    $talent_api->send();
                    unset($talent_api);
                }
            }
            //才情end

            $resume_mod->check_resume($uid, $pid); //更新简历完成状态
            //写入会员日志
            write_members_log(C('visitor'), 'resume', '修改简历特长标签（简历id：' . $pid . '）', false, array('resume_id' => $pid));
            D('Resume')->save_resume('', $pid, C('visitor'));
            $this->ajaxReturn(1, '简历特长标签修改成功！');
        }
        $this->ajaxReturn(0, '保存失败！');
    }

    /*
    **删除简历附件
    */
    public function ajax_resume_img_del()
    {
        if (IS_POST) {
            $img_id = I('request.id', 0, 'intval');
            !$img_id && $this->ajaxReturn(0, '请选择要删除的图片！');
            $uid = C('visitor.uid');
            $img_mod = M('ResumeImg');
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
            D('Resume')->check_resume(C('visitor.uid'), intval($row['resume_id'])); //更新简历完成状态
            $this->ajaxReturn(1, '删除成功！');
        } else {
            $tip = '删除后将无法恢复，您确定要删除该条数据吗？';
            $this->ajax_warning($tip);
        }
    }

    /**
     * [ajax_resume_attach 保存照片/作品]
     */
    public function ajax_resume_attach()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $img_mod = M('ResumeImg');
        $data['resume_id'] = $resume['id'];
        $data['id'] = I('post.id', 0, 'intval');
        $data['uid'] = C('visitor.uid');
        $img = $img_mod->where(array('uid' => $data['uid'], 'id' => $data['id'], 'resume_id' => $data['resume_id']))->find();
        if (!$img) {
            $this->ajaxReturn(0, '作品不存在！');
        }
        //$data['title'] = I('post.title','','trim,badword');
        $data['img'] = $img['img'];
        $reg = D('ResumeImg')->save_resume_img($data, C('visitor'));
        if ($reg['state']) {
            D('Resume')->check_resume(C('visitor.uid'), intval($data['resume_id'])); //更新简历完成状态
            $this->ajaxReturn(1, '附件添加成功！', $reg['id']);
        }
        $this->ajaxReturn(0, $reg['error']);
    }

    /**
     * 初始化照片/作品的扫码监听
     */
    public function ajax_resume_img_scan()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        S('resume_img_count' . $resume['id'], null);
        $img_count = M('ResumeImg')->where(array('resume_id' => $resume['id']))->count(); //获取照片作品数量
        S('resume_img_count' . $resume['id'], $img_count);
        $this->ajaxReturn(1, '开始监听！');
    }

    /**
     * 心跳监听照片/作品的扫码上传
     */
    public function ajax_resume_img_waiting()
    {
        if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
        $img_count_cache = S('resume_img_count' . $resume['id']);
        $img_count = M('ResumeImg')->where(array('resume_id' => $resume['id']))->count(); //获取照片作品数量
        if (false === $img_count_cache) {
            S('resume_img_count' . $resume['id'], $img_count);
            $this->ajaxReturn(0, '暂无更新数！');
        }
        if ($img_count_cache != $img_count) {
            S('resume_img_count' . $resume['id'], $img_count);
            $data['total'] = $img_count; //照片作品数量
            $img_arr = M('ResumeImg')->where(array('resume_id' => $resume['id']))->select(); //照片作品数据
            foreach ($img_arr as $item) {
                $item['img_'] = $item['img'];
                $item['img'] = attach($item['img'], 'resume_img');
                $data['img'][] = $item;
            }
            $this->ajaxReturn(1, '有更新数据！', $data);
        } else {
            $this->ajaxReturn(0, '暂无更新数据！');
        }
    }

    /*
    **删除word简历
    */
    public function ajax_word_del()
    {
        $warning = I('request.warning', 0, 'intval');
        if ($warning) {
            $tip = '删除后将无法恢复，您确定要删除该word简历吗？';
            $this->ajax_warning($tip);
        } else {
            if (false === $resume = $this->_is_resume()) $this->ajaxReturn(0, '简历不存在！');
            if ($resume['word_resume']) {
                @unlink(C('qscms_attach_path') . "word_resume/" . $resume['word_resume']);
                if (C('qscms_qiniu_open') == 1) {
                    $qiniu = new \Common\ORG\qiniu;
                    $qiniu->delete($resume['word_resume']);
                }
                $resume_mod = D('Resume');
                if (false === $resume_mod->where(array('id' => $resume['id']))->setfield('word_resume', '')) $this->ajaxReturn(1, '删除失败！');
                //写入会员日志
                write_members_log(C('visitor'), 'resume', '删除word简历（简历id：' . intval($resume['id']) . '）', false, array('resume_id' => intval($resume['id'])));
                $resume_mod->check_resume(C('visitor.uid'), $resume['id']); //更新简历完成状态
                $this->ajaxReturn(1, '删除成功！');
            }
            $this->ajaxReturn(0, 'word简历已删除或不存在！');
        }
    }

    /*
        面试邀请 start
    */
    public function jobs_interview()
    {
        $this->check_params();
        $where['resume_uid'] = C('visitor.uid');
        $look = I('get.look', 0, 'intval');
        $look && $where['personal_look'] = $look;
        $settr = I('get.settr', 0, 'intval');
        if ($settr > 0) {
            $settr_val = strtotime("-{$settr} day");
            $where['interview_addtime'] = array('EGT', $settr_val);
        }
        $company_interview_mod = D('CompanyInterview');
        $interview = $company_interview_mod->get_invitation_pre($where);
        // 最近三天收到的面试邀请数
        $count_three_day = $company_interview_mod->where(array('resume_uid' => C('visitor.uid'), 'interview_addtime' => array('egt', strtotime('-3 day'))))->count();
        $this->assign('count_three_day', $count_three_day);
        $this->assign('interview', $interview);
        $this->assign('resume_id', $resume_id);
        $this->assign('look', $look);
        $this->assign('settr', $settr);
        $this->assign('personal_nav', 'apply');
        $this->_config_seo(array('title' => '收到的面试邀请 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }
    /*
        视频面试邀请
    */
    public function video_interview()
    {
        $where['v.personal_uid'] = C('visitor.uid');
        $join = 'left join ' . C('DB_PREFIX') . 'company_profile c on c.uid=v.company_uid';
        $count = D('VideoInterview v')->join($join)->where($where)->count();
        $pager = pager($count, 10);
        $list = D('VideoInterview v')->field('v.*,c.companyname,c.id as company_id')->join($join)->where($where)->order('v.id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        foreach ($list as $key => $val) {
            $val['link_url'] = C('qscms_site_domain') . C('qscms_site_dir') . 'vi/test?code=' . $val['personal_code'];
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

            $list[$key] = $val;
        }
        $this->assign('list', $list);
        $this->assign('pager', $pager->fshow());
        $this->assign('personal_nav', 'video_interview');
        $this->_config_seo(array('title' => '收到的视频面试邀请 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }
    /**
     * [视频面试详情]
     */
    public function video_interview_details()
    {
        if (IS_AJAX) {
            $id = I('get.id', 0, 'intval');
            !$id && $this->ajaxReturn(0, '请正确选择面试信息！');
            $interview = D('VideoInterview')->where(array('id' => $id, 'personal_uid' => C('visitor.uid')))->find();
            !$interview && $this->ajaxReturn(0, '面试信息不存在！');
            $this->assign('interview', $interview);
            $this->assign('fullname', D('Resume')->where(array('uid' => C('visitor.uid')))->getField('fullname'));
            $html = $this->fetch('Company/ajax_tpl/ajax_show_video_interview');
            $this->ajaxReturn(1, '面试信息获取成功！', $html);
        }
    }
    /**
     * 提醒
     */
    public function video_interview_notice()
    {
        $id = I('get.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请正确选择面试信息！');
        $interview = D('VideoInterview')->where(array('id' => $id, 'personal_uid' => C('visitor.uid')))->find();
        !$interview && $this->ajaxReturn(0, '面试信息不存在！');
        D('VideoInterview')->notice_company($interview);
        $this->ajaxReturn(1, '提醒成功');
    }

    // ajax 获取面试邀请 详情
    public function ajax_interview_detail()
    {
        if (IS_AJAX) {
            $id = I('get.id', 0, 'intval');
            !$id && $this->ajaxReturn(0, '请正确选择面试信息！');
            $interview = M('CompanyInterview')->where(array('did' => $id, 'resume_uid' => C('visitor.uid')))->find();
            !$interview && $this->ajaxReturn(0, '面试信息不存在！');
            M('CompanyInterview')->where(array('did' => $id, 'resume_uid' => C('visitor.uid')))->setField('personal_look', 2);
            $this->assign('interview', $interview);
            $html = $this->fetch('Company/ajax_tpl/ajax_show_interview');
            $this->ajaxReturn(1, '面试信息获取成功！', $html);
        }
    }

    // 删除面试邀请
    public function interview_del()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除选中的面试邀请吗？';
            $this->ajax_warning($tip);
        } else {
            $yid = I('request.y_id', '', 'trim,badword');
            !$yid && $this->error("你没有选择项目！");
            $rst = D('CompanyInterview')->del_interview($yid, C('visitor'));
            if (intval($rst['state']) == 1) {
                $this->success("删除成功！共删除 " . $rst['num'] . " 行！", U('personal/jobs_interview'));
            } else {
                $this->error("删除失败！", U('personal/jobs_interview'));
            }
        }
    }

    // 面试邀请设为已看
    public function set_interview()
    {
        $yid = I('request.y_id', '', 'trim,badword');
        !$yid && $this->ajaxReturn(0, "你没有选择项目！");
        $jobs_type = I('get.jobs_type', 0, 'intval');
        $rst = D('CompanyInterview')->set_invitation($yid, C('visitor'), 2);
        if (!$rst['state']) $this->ajaxReturn(0, $rst['error']);
        $this->ajaxReturn(1, '设置成功！');
    }

    /*
        已申请的职位 start
    */
    public function jobs_apply()
    {
        $this->check_params();
        $where['personal_uid'] = C('visitor.uid');
        $settr = I('get.settr', 0, 'intval');
        $settr && $where['apply_addtime'] = array('gt', strtotime("-" . $settr . " day")); //筛选 申请时间
        //筛选 反馈
        $feedbackArr = array(1 => '企业未查看', 2 => '待反馈', 3 => '合适', 4 => '不合适', 5 => '待定', 6 => '未接通');
        $feedback = I('get.feedback', 0, 'intval');
        switch ($feedback) {
            case 1:
                $where['personal_look'] = 1;
                break;
            case 2:
                $where['personal_look'] = 2;
                $where['is_reply'] = 0;
                break;
            case 3:
                $where['personal_look'] = 2;
                $where['is_reply'] = 1;
                break;
            case 4:
                $where['personal_look'] = 2;
                $where['is_reply'] = 2;
                break;
            case 5:
                $where['personal_look'] = 2;
                $where['is_reply'] = 3;
                break;
            case 6:
                $where['personal_look'] = 2;
                $where['is_reply'] = 4;
                break;
            default:
                break;
        }
        $personal_apply_mod = D('PersonalJobsApply');
        $apply_list = $personal_apply_mod->get_apply_jobs($where);
        $this->assign('feedback', $feedback);
        $this->assign('settr', $settr);
        $this->assign('resume_id', $resume_id);
        $this->assign('feedbackArr', $feedbackArr);
        $this->assign('apply_list', $apply_list);
        $this->assign('resume_id', $resume_id);
        $this->assign('personal_nav', 'apply');
        $this->_config_seo(array('title' => '已申请的职位 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    // 删除已申请职位
    public function del_jobs_apply()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除选中的职位吗？';
            $this->ajax_warning($tip);
        } else {
            $yid = I('request.y_id', '', 'trim,badword');
            !$yid && $this->error("你没有选择项目！");
            $n = D('PersonalJobsApply')->del_jobs_apply($yid, C('visitor'));
            if ($n['state'] == 1) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    /**
     *  职位收藏夹
     */
    public function jobs_favorites()
    {
        $this->check_params();
        $where['personal_uid'] = C('visitor.uid');
        $settr = I('get.settr', 0, 'intval');
        $settr && $where['addtime'] = array('gt', strtotime("-" . $settr . " day")); //筛选 收藏时间
        $favorites = D('PersonalFavorites')->get_favorites($where);
        $this->assign('favorites', $favorites);
        $this->_config_seo(array('title' => '职位收藏夹 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 删除收藏 职位
     */
    public function del_favorites()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除选中的职位吗？';
            $this->ajax_warning($tip);
        } else {
            $did = I('request.did', '', 'trim,badword');
            !$did && $this->error("你没有选择项目！");
            $reg = D('PersonalFavorites')->del_favorites($did, C('visitor'));
            if ($reg['state'] === true) {
                $this->success("删除成功！", U('jobs_favorites'));
            } else {
                $this->error($reg['error']);
            }
        }
    }

    /**
     * 简历被查看/谁在关注我
     */
    public function attention_me()
    {
        $this->check_params();
        $resume_id = M('Resume')->where(array('uid' => C('visitor.uid'), 'def' => 1))->limit(1)->getfield('id');
        $where['resumeid'] = array('eq', $resume_id);
        $where['resume_uid'] = array('eq', C('visitor.uid'));
        $settr = I('get.settr', 0, 'intval');
        $settr && $where['addtime'] = array('gt', strtotime("-" . $settr . " day")); //筛选 查看时间
        $view_list = D('ViewResume')->get_view_resume($where); //获取列表
        $this->assign('view_list', $view_list);
        $this->assign('resume_id', $resume_id);
        $this->assign('settr', $settr);
        $this->assign('personal_nav', 'apply');
        $this->_config_seo(array('title' => '简历被查看 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 删除谁在关注我
     */
    public function del_view_resume()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除被关注记录吗？';
            $this->ajax_warning($tip);
        } else {
            $yid = I('request.y_id', '', 'trim,badword');
            !$yid && $this->error("你没有选择项目！");
            $reg = D('ViewResume')->del_view_resume($yid);
            if ($reg['state'] == 1) {
                $this->success("删除成功！", U('attention_me'));
            } else {
                $this->error("删除失败！", U('attention_me'));
            }
        }
    }

    /**
     *  浏览过的职位
     */
    public function attention_jobs()
    {
        $this->check_params();
        $where['uid'] = C('visitor.uid');
        $settr = I('get.settr', 0, 'intval');
        $settr && $where['addtime'] = array('gt', strtotime("-" . $settr . " day")); //筛选 查看时间
        $jobs_list = D('ViewJobs')->get_view_jobs($where); //获取列表
        $this->assign('jobs_list', $jobs_list);
        $this->assign('settr', $settr);
        $this->assign('personal_nav', 'apply');
        $this->_config_seo(array('title' => '浏览记录 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 删除浏览过的职位
     */
    public function del_view_jobs()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除选中的职位吗？';
            $this->ajax_warning($tip);
        } else {
            $yid = I('request.y_id', '', 'trim,badword');
            !$yid && $this->error("你没有选择项目！");
            $reg = D('ViewJobs')->del_view_jobs($yid);
            if ($reg['state'] == 1) {
                $this->success("删除成功！", U('attention_jobs'));
            } else {
                $this->error("删除失败！", U('attention_jobs'));
            }
        }
    }

    public function save_photo_display()
    {
        $setsqlarr = I('post.');
        if ($setsqlarr['photo_display'] == 1) {
            $setsqlarr['photo'] = 1;
        } else {
            $setsqlarr['photo'] = 0;
        }
        if (true !== $reg = D('Members')->update_user_info($setsqlarr, C('visitor'))) $this->ajaxReturn(0, $reg);
        $this->ajaxReturn(1, '保存成功！');
    }

    /**
     * [authenticate 账号安全]
     */
    public function user_safety()
    {
        $uid = C('visitor.uid');
        $user_bind = M('MembersBind')->where(array('uid' => $uid))->limit('10')->getfield('type,keyid,info');
        foreach ($user_bind as $key => $val) {
            $user_bind[$key] = unserialize($val['info']);
        }
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('members_info', D('Members')->get_user_one(array('uid' => $uid)));
        $this->assign('user_bind', $user_bind);
        $this->assign('oauth_list', $oauth_list);
        $this->assign('personal_nav', 'user_info');
        $this->_config_seo(array('title' => '账号安全 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * [avatar 头像修改]
     */
    public function user_avatar()
    {
        $this->_config_seo(array('title' => '个人头像 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->assign('personal_nav', 'user_info');
        $this->display();
    }

    /*
    **登录日志
    */
    public function user_loginlog()
    {
        $where = array('log_uid' => C('visitor.uid'), 'log_type' => 'login');
        $loginlog = D('MembersLog')->get_members_log($where, 15);
        $this->assign('loginlog', $loginlog);
        $this->assign('personal_nav', 'user_info');
        $this->_config_seo(array('title' => '会员登录日志 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 关注的企业
     */
    public function attention_com()
    {
        $this->check_params();
        $company = D('PersonalFocusCompany')->get_focus_company(array('uid' => C('visitor.uid')), 10, true);
        $this->assign('company', $company);
        $this->assign('personal_nav', 'jobs_favorites');
        $this->_config_seo(array('title' => '关注的企业 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 删除关注的企业
     */
    public function del_focus_company()
    {
        if (IS_POST) {
            $id = I('request.id', 0, 'intval');
            !$id && $this->ajaxReturn(0, '请选择要删除的企业！');
            $reg = M('PersonalFocusCompany')->where(array('uid' => C('visitor.uid'), 'company_id' => $id))->delete();
            if ($reg === false) {
                $this->ajaxReturn(0, '删除失败，请重新操作！');
            }
            //写入会员日志
            write_members_log(C('visitor'), '', '删除关注的企业（记录id：' . $id . '）');
            $this->ajaxReturn(1, '成功删除关注的企业！');
        } else {
            $tip = '取消关注后将不再接收该企业的招聘动态，您确定要取消关注吗？';
            $this->ajax_warning($tip);
        }
    }

    /**
     * 系统消息提醒
     */
    public function msg_pms()
    {
        $settr = I('get.settr', 0, 'intval');
        $new = I('get.new', 0, 'intval');
        $map = array();
        if ($settr > 0) {
            $tmp_addtime = strtotime('-' . $settr . ' day');
            $map['dateline'] = array('egt', $tmp_addtime);
        }
        if ($new > 0) {
            $map['new'] = $new;
        }
        $msg = D('Pms')->update_pms_read(C('visitor'), 10, $map);
        $this->assign('msg', $msg);
        $this->_config_seo(array('title' => '消息提醒 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * [msg_check 系统消息查看]
     */
    public function msg_check()
    {
        $ids = I('request.id', '', 'trim');
        $reg = D('Pms')->msg_check($ids, C('visitor'));
        if ($reg['state']) {
            $this->assign('msg', $reg['data']);
            $html = $this->fetch('Personal/ajax_tpl/ajax_show_message');
            $this->ajaxReturn(1, '系统信息获取成功！', $html);
        } else {
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    /**
     * [msg_del 系统消息删除]
     */
    public function msg_del()
    {
        if (!IS_POST) {
            $tip = '删除后将无法恢复，您确定要删除选择的系统消息吗？';
            $this->ajax_warning($tip);
        } else {
            $ids = I('request.id', 0, 'intval');
            $reg = D('Pms')->msg_del($ids, C('visitor'));
            if ($reg['state']) {
                IS_AJAX && $this->ajaxReturn(1, '删除成功！');
                $this->success('删除成功！');
            } else {
                IS_AJAX && $this->ajaxReturn(0, '删除失败！');
                $this->error('删除失败！');
            }
        }
    }

    /**
     * 咨询反馈
     */
    public function msg_feedback()
    {
        $msg_list = D('Msg')->msg_list(C('visitor'));
        $this->assign('msg_list', $msg_list);
        $this->_config_seo(array('title' => '咨询反馈 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * [msg_feedback_send 发送咨询反馈]
     */
    public function msg_feedback_send()
    {
        if (IS_AJAX) {
            $visitor_resume_id = M('Resume')->where(array('uid' => C('visitor.uid')))->order('def desc')->getfield('id');
            if (!$visitor_resume_id) {
                $this->ajaxReturn(0, '请先填写简历基本信息！');
            }
            $data['pid'] = I('post.pid', 0, 'intval');
            $data['touid'] = I('post.touid', 0, 'intval');
            $data['message'] = I('post.message', '', 'trim');
            $reg = D('Msg')->msg_send($data, C('visitor'));
            if ($reg['state']) $this->ajaxReturn(1, '消息发送成功！', $reg['data']);
            $this->ajaxReturn(0, $reg['error']);
        }
    }

    /**
     * [msg_feedback_del 删除咨询反馈]
     */
    public function msg_feedback_del()
    {
        if (!IS_POST) {
            $tip = '删除后将无法恢复，您确定要删除选择的咨询消息吗？';
            $this->ajax_warning($tip);
        } else {
            $ids = I('post.id', 0, 'intval');
            $reg = D('Msg')->msg_del($ids, C('visitor'));
            $this->ajaxReturn($reg['state'], $reg['tip']);
        }
    }

    /**
     * 我的红包
     */
    public function allowance()
    {
        if (!isset($this->apply['Allowance'])) $this->_empty();
        $type = I('get.type', '', 'trim');
        $field = D('Allowance/AllowanceRecord')->getDbFields();
        if ($type && !in_array($type, $field)) {
            $this->error('请求参数错误！');
        }
        $type && $map[$type] = array('eq', 1);
        $status = I('get.status', '', 'trim');
        $status != '' && $map['status'] = array('eq', $status);
        $member_turn = I('get.member_turn', 0, 'intval');
        $member_turn > 0 && $map['member_turn'] = array('eq', $member_turn);
        $map['personal_uid'] = array('eq', C('visitor.uid'));
        $data_count = D('Allowance/AllowanceRecord')->where($map)->count();
        $pagesize = 10;
        $pager = pager($data_count, $pagesize);
        $page = $pager->fshow();
        $list = D('Allowance/AllowanceRecord')->where($map)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        $infoid_arr = array();
        foreach ($list as $key => $value) {
            $infoid_arr[] = $value['info_id'];
        }
        if (!empty($infoid_arr)) {
            $info_list = D('Allowance/AllowanceInfo')->where(array('id' => array('in', $infoid_arr)))->index('id')->select();
        } else {
            $info_list = array();
        }

        foreach ($info_list as $key => $value) {
            $info_list[$key]['type_cn'] = D('Allowance/AllowanceInfo')->get_alias_cn($value['type_alias']);
        }
        foreach ($list as $key => $value) {
            $list[$key]['status_cn'] = D('Allowance/AllowanceRecord')->get_status_cn($value['status']);
            $recordid_arr[] = $value['id'];
        }
        if (!empty($recordid_arr)) {
            $log = D('Allowance/AllowanceRecordLog')->where(array('record_id' => array('in', $recordid_arr)))->select();
            foreach ($list as $key => $value) {
                foreach ($log as $k => $v) {
                    if ($value['id'] == $v['record_id']) {
                        $list[$key]['log'][$v['step']] = $v;
                    }
                }
            }
        }
        $record['list'] = $list;
        $this->assign("page", $page);
        $this->assign('info_list', $info_list);
        $this->assign('type', $type);
        $this->assign('status', $status);
        $this->assign('member_turn', $member_turn);
        $this->assign('record', $record);
        $this->assign('personal_nav', 'allowance');
        $this->assign('status_list', D('Allowance/AllowanceRecord')->status_cn);
        $this->assign('type_list', D('Allowance/AllowanceRecord')->type_cn);
        $this->_config_seo(array('title' => '我的红包 - 个人会员中心 - ' . C('qscms_site_name')));
        $this->display();
    }

    /**
     * 检查完整度，是否需要赠送红包
     */
    public function check_complete_percent_allowance()
    {
        if (C('qscms_perfected_resume_allowance_open') == 1 && C('qscms_perfected_resume_allowance_percent') > 0) {
            $uid = C('visitor.uid');
            $resume_info = D('Resume')->where(array('uid' => $uid, 'def' => 1))->find();
            if ($resume_info['complete_percent'] >= C('qscms_perfected_resume_allowance_percent') && $resume_info['audit'] == 1) {
                $r = D('Resume')->perfected_resume_allowance($resume_info['complete_percent'], C('visitor'));
                if ($r['status'] == 2) {
                    if (!C('qscms_weixin_apiopen')) $this->ajaxReturn(0, '未配置微信参数！');
                    $qrimg = \Common\qscmslib\weixin::qrcode_img(array('type' => 'bind', 'width' => 115, 'height' => 115));
                    $this->assign('qrimg', $qrimg);
                    $this->assign('tip', '你的简历完整度超过' . C('qscms_perfected_resume_allowance_percent') . '%，已获得系统赠送的' . $r['data'] . '元随机红包！微信关注公众号进行账号绑定后即可领取');
                    $html = $this->fetch('Personal/ajax_tpl/ajax_bind_weixin');
                    $this->ajaxReturn(2, '请先绑定微信', $html);
                } else {
                    $this->ajaxReturn($r['status'], $r['msg'], $r['data']);
                }
            } else {
                $this->ajaxReturn(0, '条件不满足');
            }
        } else {
            $this->ajaxReturn(0, '条件不满足');
        }
    }
    /**
     * [share_allowance_partake 分享红包列表]
     */
    public function share_allowance_partake()
    {
        $completion_status = I('get.completion_status');
        $pay_status = I('get.pay_status');
        $partake = D('ShareAllowancePartake')->get_partake($completion_status, $pay_status, C('visitor'));
        $this->assign('partake', $partake);
        $this->_config_seo(array('title' => '分享红包', 'header_title' => '分享红包'));
        $this->display();
    }
    public function share_allowance_partake_del()
    {
        if (IS_AJAX) {
            $tip = '删除后将无法恢复，您确定要删除选中的分享红包吗？';
            $this->ajax_warning($tip);
        } else {
            $id = I('request.id', '', 'trim,badword');
            !$id && $this->error("你没有选择项目！");
            $reg = D('ShareAllowancePartake')->share_allowance_partake_del($id);
            if ($reg['state'] == 1) {
                $this->success("删除成功！", U('share_allowance_partake'));
            } else {
                $this->error("删除失败！", U('share_allowance_partake'));
            }
        }
    }
    /**
     * 不再提示
     */
    public function check_complete_percent_allowance_nolonger_notice()
    {
        M('MembersPerfectedAllowance')->where(array('uid' => C('visitor.uid')))->save(array('notice' => 0));
    }

    public function oauth_bind_per_close()
    {
        S('oauth_bind_per_close_' . C('visitor.uid'), 1);
    }
    /**
     * 邀请红包首页
     */
    public function invite_friend()
    {
        $map['inviter_uid'] = array('eq', C('visitor.uid'));
        $data_count = D('InviteAllowance')->where($map)->count();
        $pager = pager($data_count, 5);
        $page = $pager->fshow();
        $list = D('InviteAllowance')->where($map)->order('id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $userbind = M('MembersBind')->where(array('uid' => C('visitor.uid'), 'type' => 'weixin'))->find();
        $this->assign('userbind', $userbind);
        $this->_config_seo(array('title' => '邀请红包详情', 'header_title' => '邀请红包详情'));
        $this->display();
    }
    /*
    报名网络招聘会
    */
    public function ajax_enroll()
    {
        $subject_id = I('post.subject_id', 0, 'intval');
        !$subject_id && $this->ajaxReturn(0, '请选择招聘会！');
        $is_enroll = M('SubjectPersonal')->where(array('subject_id' => $subject_id, 'resume_uid' => C('visitor.uid')))->find();
        $is_enroll && $this->ajaxReturn(0, '您已经报名此招聘会，请勿重复报名！');
        $subject = M('Subject')->where(array('id' => $subject_id))->field('holddate_end,resume_percent')->find();
        if (time() > $subject['holddate_end']+86400) {
            $this->ajaxReturn(0, '该招聘会已经预定结束，非常抱歉');
        }
        $resume = M('Resume')->where(array('uid' => C('visitor.uid')))->find();
        !$resume && $this->ajaxReturn(0, '您还没创建简历，请先去创建吧！');
        if ($subject['resume_percent'] > 0) {
            if ($resume['complete_percent'] < $subject['resume_percent'])
                $this->ajaxReturn(0, '您的简历完整度较低，暂不能报名本场招聘会。建议您完善至' . $subject['resume_percent'] . '%再报名参会', U('Personal/index'));
        }
        $post_data['resume_uid'] = C('visitor.uid');
        $post_data['subject_id'] = $subject_id;
        $post_data['robot'] = 0;
        $post_data['s_audit'] = 2;
        $post_data['addtime'] = time();
        $insert_id = M('SubjectPersonal')->add($post_data);
        if ($insert_id) {
            $this->ajaxReturn(1, '报名成功。我们将在1个工作日内进行审核，请留意消息通知。');
        } else {
            $this->ajaxReturn(0, '报名失败！');
        }
    }
}
