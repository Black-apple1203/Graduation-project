<?php
namespace Home\Controller;

use Common\Controller\FrontendController;

class AjaxCompanyController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login) $this->ajaxReturn(0, L('login_please'), '', 1);
        if (C('visitor.utype') != 1) $this->ajaxReturn(0, '请登录企业帐号！');
    }

    /**
     * 下载简历
     */
    public function resume_down() {
        $addarr['rid'] = I('request.rid', 0, 'intval');
        $save = I('get.save', false);
        $r = D('CompanyDownResume')->add_down_resume($addarr, C('visitor'), $save);
        $this->ajaxReturn($r['state'], $r['msg'], $r['data']);
    }

    /**
     * 标记简历
     */
    public function resume_label() {
        $resume_id = I('get.resume_id', 0, 'intval');
        $label = I('get.label', 0, 'intval');
        $label_type = I('get.label_type', 0, 'intval');
        if ($label_type == 1) {
            $model_name = 'CompanyDownResume';
        } else {
            $model_name = 'PersonalJobsApply';
        }
        $data = D($model_name)->where(array('resume_id' => $resume_id, 'company_uid' => C('visitor.uid')))->find();
        if ($data) {
            $r = D('Resume')->company_label_resume($data['did'], $model_name, C('visitor.uid'), $label);
            $this->ajaxReturn($r['state'], $r['msg']);
        } else {
            $this->ajaxReturn(0, '参数错误！');
        }
    }

    /**
     * 收藏简历
     */
    public function resume_favor() {
        $rid = I('request.rid');
        $r = D('CompanyFavorites')->add_favorites($rid, C('visitor'));
        $this->ajaxReturn($r['state'], $r['error']);
    }

    /**
     * 举报简历
     */
    public function report_resume() {
        $resume_id = I('request.resume_id', 0, 'intval');
        if (!$resume_id) {
            $this->ajaxReturn(0, '参数错误！');
        }
        if (IS_POST) {
            $report_type = I('request.report_type', 1, 'intval');
            $data['resume_id'] = $resume_id;
            $data['report_type'] = $report_type;
            $r = D('ReportResume')->add_report($data, C('visitor'));
            $this->ajaxReturn($r['state'], $r['msg']);
        } else {
            $taskinfo = D('Task')->get_task_cache(1, 'report_resume');
            $this->assign('taskinfo', $taskinfo);
            $this->assign('resume_id', $resume_id);
            $this->assign('type_arr', D('ReportResume')->type_arr);
            $html = $this->fetch('AjaxCommon/report_resume');
            $this->ajaxReturn(1, '获取数据成功！', $html);
        }
    }

    /**
     * [jobs_add_guide_dig 发布职位获取引导弹窗]
     */
    public function jobs_add_guide_dig() {
        $setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        if ($setmeal['is_free'] == 1) {
            $tip = '您当前是' . $setmeal['setmeal_name'] . '，<span class="font_yellow">发布中的职位数已达到最大限制</span>，升级VIP会员后可继续发布职位，建议您立即升级VIP会员套餐。';
            $is_free = "1";
        } else {
            $tip = '您当前是' . $setmeal['setmeal_name'] . '，<span class="font_yellow">发布中的职位数已达到最大限制</span>，建议您先关闭暂时不需要招聘的职位后再发布新的职位。';
            $is_free = "0";
        }

        $this->ajax_warning($tip, '', $is_free);
    }

    /**
     * 职位分类对应描述及关联分类ajax返回
     */
    public function ajax_get_category_content() {
        $id = I('get.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请选择分类！');
        $current_category = D('CategoryJobs')->find($id);
        $data = array();
        $data['show_relation'] = 0;
        $data['relation_data'][] = array('id' => $id, 'name' => $current_category['categoryname'], 'desc' => $current_category['content'], 'current' => 1);
        if ($current_category['relation1']) {
            $idarr = explode(".", $current_category['relation1']);
            $relation_id = $idarr[2] == 0 ? $idarr[1] : $idarr[2];
            $relation1 = D('CategoryJobs')->find($relation_id);
            if ($relation1['content']) {
                $data['show_relation'] = 1;
                $data['relation_data'][] = array('id' => $relation1['id'], 'name' => $relation1['categoryname'], 'desc' => $relation1['content'], 'current' => 0);
            }
        }
        if ($current_category['relation2']) {
            $idarr = explode(".", $current_category['relation2']);
            $relation_id = $idarr[2] == 0 ? $idarr[1] : $idarr[2];
            $relation2 = D('CategoryJobs')->find($relation_id);
            if ($relation2['content']) {
                $data['show_relation'] = 1;
                $data['relation_data'][] = array('id' => $relation2['id'], 'name' => $relation2['categoryname'], 'desc' => $relation2['content'], 'current' => 0);
            }
        }
        $this->ajaxReturn(1, '获取数据成功！', $data);
    }
    /**
     * 职位分类对应模板关联分类ajax返回
     */
    public function ajax_get_category() {
        $id = I('get.id', 0, 'intval');
        !$id && $this->ajaxReturn(0, '请选择分类！');
        $current_category = D('CategoryJobs')->find($id);
        if($current_category['jobs_tpl']){
        	$data = unserialize($current_category['jobs_tpl']);
        }else{
        	$data = '';
        }
        $this->ajaxReturn(1, '获取数据成功！', $data);
    }
    /**
     * 更新简历列表操作状态（下载、收藏等）
     */
    public function refresh_resume_list() {
        $rids = I('request.rids', '', 'trim');
        foreach ($rids as $rid) {
            $d = M('CompanyDownResume')->where(array('resume_id' => $rid, 'company_uid' => C('visitor.uid')))->find();
            $d && $list[$rid]['had_down'] = 1 && $list[$rid]['url'] = url_rewrite('QS_resumeshow', array('id' => $rid));
            $f = M('CompanyFavorites')->where(array('resume_id' => $rid, 'company_uid' => C('visitor.uid')))->find();
            $f && $list[$rid]['had_fav'] = 1;
        }
        $this->ajaxReturn(1, '获取成功！', $list);
    }
}

?>