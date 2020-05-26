<?php

/**
 * 职位详情
 */

namespace Common\qscmstag;

defined('THINK_PATH') or exit();
class jobs_showTag
{
    protected $params = array();
    protected $map = array();
    function __construct($options)
    {
        $array = array(
            '列表名'           =>  'listname',
            '职位id'          =>  'id'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['id'] = array('eq', intval($this->params['id']));
        $this->params['listname'] = isset($this->params['listname']) ? $this->params['listname'] : "info";
    }
    public function run()
    {
        $val = M('Jobs')->where($this->map)->find();
        $validation = I('get.validation', 0, 'intval');
        $tmp = 0;
        if (($val['audit'] == 2 && C('qscms_jobs_display') == 1) || $val['audit'] == 3) {
            $tmp = 1;
        } else if (!$val) {
            $valtmp = M('JobsTmp')->where($this->map)->find();
            $valdel = M('JobsCould')->where($this->map)->find();
            $valtmp = $valtmp ?: array();
            $valdel = $valdel ?: array();
            $val = array_merge($valtmp, $valdel);
            $tmp = 1;
        }
        if (!C('visitor.uid')) {
            if (($tmp || !$val) && $validation != 1) {
                $controller = new \Common\Controller\BaseController;
                $controller->_empty();
            }
        }
        if ($val['deadline'] < time()) {
            $val['jobs_overtime'] = 1;
        } elseif ($val['audit'] <> '1' || $val['display'] <> '1' || ($val['setmeal_deadline'] <> '0' && $val['setmeal_deadline'] < time())) {
            $val['jobs_overtime'] = 2;
        }
        if ($val['setmeal_deadline'] < time() && $val['setmeal_deadline'] <> "0" && $val['add_mode'] == "2") {
            $val['deadline'] = $val['setmeal_deadline'];
        }
        $val['amount'] = $val['amount'] == "0" ? '若干' : $val['amount'];
        $val['jobs_url'] = url_rewrite('QS_jobsshow', array('id' => $val['id']));
        $profile = M('CompanyProfile')->where(array('id' => $val['company_id']))->find();
        $val['company'] = $profile;
        $setmeal = D('MembersSetmeal')->get_user_setmeal($val['uid']);
        $val['show_contact_direct'] = $setmeal['show_contact_direct'];
        $val['company']['setmeal_id'] = $setmeal['setmeal_id'];
        $val['company']['setmeal_name'] = $setmeal['setmeal_name'];
        $val['contact'] = M('JobsContact')->where(array('pid' => $val['id']))->find();
        $val['contact']['telephone_'] = $val['contact']['telephone'];
        $val['expire'] = sub_day($val['deadline'], time());
        $val['contents'] = htmlspecialchars_decode($val['contents'], ENT_QUOTES);
        $val['contents'] = strip_tags($val['contents']);
        $val['refreshtime_cn'] = daterange(time(), $val['refreshtime'], 'Y-m-d', "#FF3300");
        $val['company_url'] = url_rewrite('QS_companyshow', array('id' => $val['company_id']));
        if ($val['company']['logo']) {
            $val['company']['logo'] = attach($val['company']['logo'], 'company_logo');
        } else {
            $val['company']['logo'] = attach('no_logo.png', 'resource');
        }
        if ($val['company']['website']) {
            $val['company']['website_'] = $val['company']['website'];
            if (!preg_match('/^http(s)?:\\/\\/.+/',$val['company']['website'])) {
                $val['company']['website'] = "http://" . $val['company']['website'];
            } else {
                $val['company']['website_'] = str_replace('http://', '', $val['company']['website_']);
                $val['company']['website_'] = str_replace('https://', '', $val['company']['website_']);
            }
        }
        if ($val['negotiable'] == 0) {
            if (C('qscms_wage_unit') == 1) {
                $val['minwage'] = $val['minwage'] % 1000 == 0 ? (($val['minwage'] / 1000) . 'K') : (round($val['minwage'] / 1000, 1) . 'K');
                $val['maxwage'] = $val['maxwage'] ? ($val['maxwage'] % 1000 == 0 ? (($val['maxwage'] / 1000) . 'K') : (round($val['maxwage'] / 1000, 1) . 'K')) : 0;
            } elseif (C('qscms_wage_unit') == 2) {
                if ($val['minwage'] >= 10000) {
                    if ($val['minwage'] % 10000 == 0) {
                        $val['minwage'] = ($val['minwage'] / 10000) . '万';
                    } else {
                        $val['minwage'] = round($val['minwage'] / 10000, 1);
                        $val['minwage'] = strpos($val['minwage'], '.') ? str_replace('.', '万', $val['minwage']) : $val['minwage'] . '万';
                    }
                } else {
                    if ($val['minwage'] % 1000 == 0) {
                        $val['minwage'] = ($val['minwage'] / 1000) . '千';
                    } else {
                        $val['minwage'] = round($val['minwage'] / 1000, 1);
                        $val['minwage'] = strpos($val['minwage'], '.') ? str_replace('.', '千', $val['minwage']) : $val['minwage'] . '千';
                    }
                }
                if ($val['maxwage'] >= 10000) {
                    if ($val['maxwage'] % 10000 == 0) {
                        $val['maxwage'] = ($val['maxwage'] / 10000) . '万';
                    } else {
                        $val['maxwage'] = round($val['maxwage'] / 10000, 1);
                        $val['maxwage'] = strpos($val['maxwage'], '.') ? str_replace('.', '万', $val['maxwage']) : $val['maxwage'] . '万';
                    }
                } elseif ($val['maxwage']) {
                    if ($val['maxwage'] % 1000 == 0) {
                        $val['maxwage'] = ($val['maxwage'] / 1000) . '千';
                    } else {
                        $val['maxwage'] = round($val['maxwage'] / 1000, 1);
                        $val['maxwage'] = strpos($val['maxwage'], '.') ? str_replace('.', '千', $val['maxwage']) : $val['maxwage'] . '千';
                    }
                } else {
                    $val['maxwage'] = 0;
                }
            }
            if ($val['maxwage'] == 0) {
                $val['wage_cn'] = '面议';
            } else {
                if ($val['minwage'] == $val['maxwage']) {
                    $val['wage_cn'] = $val['minwage'] . '/月';
                } else {
                    $val['wage_cn'] = $val['minwage'] . '-' . $val['maxwage'] . '/月';
                }
            }
        } else {
            $val['wage_cn'] = '面议';
        }
        $age = explode('-', $val['age']);
        if (!$age[0] && !$age[1]) {
            $val['age_cn'] = '不限';
        } else {
            $age[0] && $val['age_cn1'] = $age[0] . '岁';
            $age[1] && $val['age_cn2'] = $age[1] . '岁';
        }
        if ($val['tag_cn']) {
            $tag_cn = explode(',', $val['tag_cn']);
            $val['tag_cn'] = $tag_cn;
        } else {
            $val['tag_cn'] = array();
        }
        //简历处理率
        $apply = M('PersonalJobsApply')->where(array('company_uid' => $val['uid'], 'apply_addtime' => array('gt', strtotime("-14day"))))->select();
        foreach ($apply as $key => $v) {
            if ($v['is_reply']) {
                $reply++;
                $v['reply_time'] && $reply_time += $v['reply_time'] - $v['apply_addtime'];
            }
        }
        $val['company']['reply_ratio'] = !$apply ? 100 : intval($reply / count($apply) * 100);
        $val['company']['reply_time'] = !$reply_time ? '0天' : sub_day(intval($reply_time / count($apply)), 0);
        $last_login_time = M('Members')->where(array('uid' => $val['uid']))->getfield('last_login_time');
        $val['company']['last_login_time'] = $last_login_time ? fdate($last_login_time) : '未登录';
        if (C('visitor.utype') == 2) {
            $val['resume'] = M('Resume')->where(array('uid' => C('visitor.uid')))->order('def desc,id asc')->find();
            if ($val['resume']) {
                $val['resume']['url'] = url_rewrite('QS_resumeshow', array('id' => $val['resume']['id'], 'tpl' => $val['resume']['tpl'], 'preview' => 1));
            }
            $view_log = M('ViewJobs')->where(array('uid' => C('visitor.uid'), 'jobsid' => $val['id']))->find();
            if ($view_log) {
                M('ViewJobs')->where(array('uid' => C('visitor.uid'), 'jobsid' => $val['id']))->setField('addtime', time());
            } else {
                M('ViewJobs')->add(array('uid' => C('visitor.uid'), 'jobs_uid' => $val['uid'], 'jobsid' => $val['id'], 'addtime' => time()));
            }
        }
        $hide = true;
        $showjobcontact = C('LOG_SOURCE') == 2 ? C('qscms_showjobcontact_wap') : C('qscms_showjobcontact');
        if ($showjobcontact == 0) {
            $hide = false;
        } elseif ($showjobcontact == 4 && C('LOG_SOURCE') == '') {
            $hide = false;
        } else {
            if (C('visitor')) {
                //分站添加
                if (C('qscms_subsite_open') == 1 && C('subsite_info.s_id') > 0) {
                    C('subsite_info.s_id', 0);
                }
                $resume = D('Resume')->where(array('uid' => C('visitor.uid')))->find();
                $apply = D('PersonalJobsApply')->where(array('personal_uid' => C('visitor.uid'), 'jobs_id' => $val['id']))->find();
                if ($showjobcontact == 1) {
                    $hide = false;
                } elseif ($resume && $showjobcontact == 2) {
                    $hide = false;
                } elseif ($apply && $showjobcontact == 3) {
                    $hide = false;
                }
            }
        }
        if ($hide) {
            if ($val['contact']['telephone'] && $val['contact']['telephone_show'] == 1) {
                $val['contact']['telephone'] = contact_hide($val['contact']['telephone']);
            } else {
                $val['contact']['telephone'] = contact_hide(trim($val['contact']['landline_tel'], '-'), 1);
            }
            if ($val['contact']['telephone_show'] == 0 && $val['contact']['landline_tel_show'] == 0) {
                $val['contact']['telephone_show'] = 0;
            } elseif (($val['contact']['telephone_show'] == 0 && $val['contact']['landline_tel_show'] == 1) || ($val['contact']['telephone_show'] == 1 && $val['contact']['landline_tel_show'] == 0) || ($val['contact']['telephone_show'] == 1 && $val['contact']['landline_tel_show'] == 1)) {
                $val['contact']['telephone_show'] = 1;
            }
        } else {
            if ($showjobcontact == 4 && C('LOG_SOURCE') == '') {
                $val['contact']['telephone'] = \Common\qscmslib\weixin::qrcode_img(array('type' => 'job', 'width' => 85, 'height' => 85, 'params' => $val['id']));
            } else {
                if ($val['contact']['telephone_show'] == 0 && $val['contact']['landline_tel_show'] == 0) {
                    $val['contact']['telephone_show'] = 0;
                } elseif ($val['contact']['telephone_show'] == 1 && $val['contact']['telephone']) {
                    $val['contact']['telephone_show'] = $val['contact']['telephone_show'];
                } elseif ($val['contact']['telephone'] && !trim($val['contact']['landline_tel'], '-') && $val['contact']['telephone_show'] == 0) {
                    $val['contact']['telephone_show'] = $val['contact']['telephone_show'];
                } else {
                    $val['contact']['telephone'] = trim($val['contact']['landline_tel'], '-');
                    $val['contact']['telephone_show'] = $val['contact']['landline_tel_show'];
                }
            }
        }
        if (C('qscms_contact_img_com') == 2 && $showjobcontact != 4) {
            $val['contact']['telephones'] = '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($val['contact']['telephone'], C('PWDHASH'))), '', '', true) . '" style="vertical-align:middle;"/>';
        }
        if(C('qscms_contact_img_com') == 1){
            $val['contact']['telephones'] = $val['contact']['telephone'];
        }
        if (APP_SPELL) {
            if (false === $jobs_cate = F('jobs_cate_list')) $jobs_cate = D('CategoryJobs')->jobs_cate_cache();
            $spell = $val['category'] ? $val['category'] : $val['topclass'];
            $val['jobcategory'] = $jobs_cate['id'][$spell]['spell'];
        } else {
            $val['jobcategory'] = intval($val['topclass']) . "." . intval($val['category']) . ".0";
        }
        $val['tpl'] = $val['tpl'] ? $val['tpl'] : C('qscms_tpl_company');
        $val['tmp'] = $tmp;
        $val['hide'] = $hide;
        //检测是否已收藏
        $val['favor'] = $this->_check_favor($val['id'], C('visitor.uid'));
        //检测是否已申请
        $val['is_apply'] = $this->_check_apply($val['id'], C('visitor.uid'));
        $allowance_record = array();
        if (C('apply.Allowance')) {
            $val['allowance_open'] = 1;
            if ($val['allowance_id'] > 0) {
                $allowance = D('Allowance/AllowanceInfo')->find($val['allowance_id']);
                $allowance['type_cn'] = D('Allowance/AllowanceInfo')->get_alias_cn($allowance['type_alias']);
                if (C('visitor.uid') && C('visitor.utype') == 2) {
                    $allowance_record = D('Allowance/AllowanceRecord')->where(array('personal_uid' => C('visitor.uid'), 'info_id' => $val['allowance_id']))->find();
                }
            } else {
                $allowance = array();
            }
        } else {
            $val['allowance_open'] = 0;
            $val['allowance_id'] = 0;
        }
        $val['allowance'] = $allowance;
        $val['allowance_record'] = $allowance_record;
        // 企业实地报告
        if (C('apply.Report')) {
            $where['com_id'] = $val['company_id'];
            $where['status'] = 1;
            $report = M('CompanyReport')->where($where)->find();
            $report && $val['company']['report'] = 1;
        }
        $count_map['uid'] = $val['uid'];
        if (C('qscms_jobs_display') == 1) {
            $count_map['audit'] = 1;
        }
        $val['jobs_count'] = D('Jobs')->where($count_map)->count();
        if (C('qscms_share_allowance_open') == 1 && $val['share_allowance'] == 1 && $val['tmp'] != 1) {
            $share_allowance = M('ShareAllowance')->field('id,amount,count,task_views,issued,complete,status')->where(array('jobs_id' => $val['id'], 'pay_status' => 1))->order('id desc')->find();
            if ($share_allowance && $share_allowance['issued'] < $share_allowance['count'] && $share_allowance['complete'] < $share_allowance['count']) {
                $val['share_allowance_info'] = array(
                    'id' => $share_allowance['id'],
                    'amount' => $share_allowance['amount'],
                    'task_views' => $share_allowance['task_views']
                );
            } else {
                $val['share_allowance'] = 0;
                D('Jobs')->jobs_setfield(array('id' => $val['id']), array('share_allowance' => 0));
                $share_allowance['status'] && M('ShareAllowance')->where(array('id' => $share_allowance['id']))->setfield('status', 0);
            }
        } else {
            $val['share_allowance'] = 0;
        }
        if(C('qscms_weixin_apiopen') && C('qscms_weixin_public_type') == 1){
            $val['subscribe_job'] = \Common\qscmslib\weixin::qrcode_img(array('type' => 'subscribe_job', 'width' => 85, 'height' => 85, 'params' => $val['id']));
        }
        return $val;
    }
    //检测是否已收藏
    protected function _check_favor($jobs_id, $uid)
    {
        $r = D('PersonalFavorites')->where(array('jobs_id' => $jobs_id, 'personal_uid' => $uid))->find();
        if ($r) {
            return 1;
        } else {
            return 0;
        }
    }
    //检测是否已申请
    protected function _check_apply($jobs_id, $uid)
    {
        $r = D('PersonalJobsApply')->where(array('jobs_id' => $jobs_id, 'personal_uid' => $uid))->find();
        if ($r && C('visitor.utype') == 2) {
            return 1;
        } else {
            return 0;
        }
    }
}
