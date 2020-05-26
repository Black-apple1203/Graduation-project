<?php

/**
 * 企业详情
 */

namespace Common\qscmstag;

defined('THINK_PATH') or exit();
class company_showTag
{
    protected $params = array();
    protected $map = array();
    function __construct($options)
    {
        $array = array(
            '列表名'           =>  'listname',
            '企业id'          =>  'id'
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }

        $this->map['user_status'] = array('eq', 1);
        $this->map['id'] = array('eq', intval($this->params['id']));
        $this->params['listname'] = isset($this->params['listname']) ? $this->params['listname'] : "info";
    }
    public function run()
    {
        $profile = M('CompanyProfile')->where($this->map)->find();
        if (!$profile) {
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $profile['company_url'] = url_rewrite('QS_companyshow', array('id' => $profile['id']));
        $profile['company_profile'] = htmlspecialchars_decode($profile['contents'], ENT_QUOTES);
        $profile['company_profile'] = strip_tags($profile['company_profile']);

        if ($profile['logo']) {
            $profile['logo'] = attach($profile['logo'], 'company_logo');
        } else {
            $profile['logo'] = attach('no_logo.png', 'resource');
        }
        //简历处理率
        $apply = M('PersonalJobsApply')->where(array('company_uid' => $profile['uid'], 'addtime' => array('gt', strtotime("-14day"))))->select();
        foreach ($apply as $key => $val) {
            if ($val['is_reply']) {
                $reply++;
                $val['reply_time'] && $reply_time += $val['reply_time'] - $val['apply_addtime'];
            }
        }
        $profile['reply_ratio'] = !$apply ? 100 : intval($reply / count($apply) * 100);
        $profile['reply_time'] = !$reply_time ? '0天' : sub_day(intval($reply_time / count($apply)), 0);
        $last_login_time = M('Members')->where(array('uid' => $profile['uid']))->getfield('last_login_time');
        $profile['last_login_time'] = $last_login_time ? fdate($last_login_time) : '未登录';
        $jobs_count_map['uid'] = $profile['uid'];
        if (C('qscms_jobs_display') == 1) {
            $jobs_count_map['audit'] = 1;
        }

        $profile['jobs_count'] = D('Jobs')->where($jobs_count_map)->count();
        $profile['fans'] = D('PersonalFocusCompany')->where(array('company_id' => $profile['id']))->count();
        $profile['preview'] = C('visitor.uid') == $profile['uid'] ? 1 : 0;
        $img_map['uid'] = $profile['uid'];
        if (C('qscms_companyimg_display') == 1) {
            $img_map['audit'] = 1;
        } else {
            $img_map['audit'] = array(array('eq', 1), array('eq', 2), 'or');
        }
        $profile['img'] = D('CompanyImg')->where($img_map)->select();

        foreach ($profile['img'] as $key => $value) {
            $profile['img'][$key]['img'] = attach($value['img'], 'company_img');
        }
        $tag = $profile['tag'];
        $profile['tag_arr'] = array();
        if ($tag) {
            $tag_arr = explode(",", $tag);
            foreach ($tag_arr as $key => $value) {
                $arr = explode("|", $value);
                $profile['tag_arr'][] = $arr[1];
            }
        }

        $profile['tag_arrs'] = implode(",", $profile['tag_arr']);
        if ($profile['website']) {
            $profile['website_'] = $profile['website'];
            if (!preg_match('/^http(s)?:\\/\\/.+/',$profile['website'])) {
                $profile['website'] = "http://" . $profile['website'];
            } else {
                $profile['website_'] = str_replace('http://', '', $profile['website_']);
                $profile['website_'] = str_replace('https://', '', $profile['website_']);
            }
        }
        if (C('qscms_apply_jobs_contact')) {
            $apply = D('PersonalJobsApply')->where(array('personal_uid' => C('visitor.uid'), 'company_id' => $profile['id']))->find();
            if ($apply) {
                $profile['telephone'] = $profile['telephone'];
                $profile['landline_tel'] = $profile['landline_tel'];
                $profile['email'] = $profile['email'];
            } else {
                $profile['telephone'] = contact_hide($profile['telephone'], 2);
                $profile['landline_tel'] = contact_hide($profile['landline_tel'], 1);
                $profile['email'] = contact_hide($profile['email'], 3);
            }
        } else {
            $hide = true;
            $showjobcontact = C('LOG_SOURCE') == 2 ? C('qscms_showjobcontact_wap') : C('qscms_showjobcontact');
            if ($showjobcontact == 0) {
                $hide = false;
            } else {
                if (C('visitor')) {
                    $resume = D('Resume')->where(array('uid' => C('visitor.uid')))->find();
                    if ($showjobcontact == 1) {
                        $hide = false;
                    } elseif ($resume && $showjobcontact == 2) {
                        $hide = false;
                    }
                }
            }
            $profile['landline_tel'] = trim($profile['landline_tel'], '-');
            $hide && $profile['telephone'] = contact_hide($profile['telephone'], 2);
            $hide && $profile['landline_tel'] = contact_hide($profile['landline_tel'], 1);
            $hide && $profile['email'] = contact_hide($profile['email'], 3);
        }
        $profile['contact_show'] == 0 && $profile['contact'] = '企业设置不公开';
        if (C('qscms_contact_img_com') == 2) {
            if ($profile['telephone_show'] == 0) {
                $profile['telephone'] = '企业设置不公开';
            } else {
                $profile['telephone'] = $profile['telephone'] ? '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($profile['telephone'], C('PWDHASH'))), '', '', true) . '"/>' : '';
            }
            if ($profile['email_show'] == 0) {
                $profile['email'] = '企业设置不公开';
            } else {
                $profile['email'] = $profile['email'] ? '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($profile['email'], C('PWDHASH'))), '', '', true) . '"/>' : '';
            }
            if ($profile['landline_tel_show'] == 0) {
                $profile['landline_tel'] = '企业设置不公开';
            } else {
                $profile['landline_tel'] = $profile['landline_tel'] ? '<img src="' . C('qscms_site_domain') . U('Home/Qrcode/get_font_img', array('str' => encrypt($profile['landline_tel'], C('PWDHASH'))), '', '', true) . '"/>' : '';
            }
        } elseif (C('qscms_contact_img_com') == 3 && C('LOG_SOURCE') != 2) { // 扫码获取
            $profile['telephone'] = \Common\qscmslib\weixin::qrcode_img(array('type' => 'company', 'width' => 85, 'height' => 85, 'params' => $profile['id']));
        } else {
            $profile['telephone_show'] == 0 && $profile['telephone'] = '企业设置不公开';
            $profile['email_show'] == 0 && $profile['email'] = '企业设置不公开';
            $profile['landline_tel_show'] == 0 && $profile['landline_tel'] = '企业设置不公开';
        }
        $profile['focus'] = 0;
        if (C('visitor.uid')) {
            $focus = D('PersonalFocusCompany')->check_focus($profile['id'], C('visitor.uid'));
            if ($focus) {
                $profile['focus'] = 1;
            }
        }
        $profile['hide'] = $hide;
        // if(C('qscms_contact_img_com') == 2){
        //     $profile['telephone_img'] = $v;
        //     Image::BuildImageVerify($config,'captcha');
        // }
        $setmeal = D('MembersSetmeal')->get_user_setmeal($profile['uid']);
        $profile['setmeal_id'] = $setmeal['setmeal_id'];
        $profile['setmeal_name'] = $setmeal['setmeal_name'];
        // 企业实地报告
        if (C('apply.Report')) {
            $where['com_id'] = $profile['id'];
            $where['status'] = 1;
            $report = M('CompanyReport')->where($where)->find();
            $report && $profile['report'] = 1;
        }
        if(C('qscms_weixin_apiopen') && C('qscms_weixin_public_type') == 1){
            $profile['subscribe_company'] = \Common\qscmslib\weixin::qrcode_img(array('type' => 'subscribe_company', 'width' => 85, 'height' => 85, 'params' => $profile['id']));
        }
        return $profile;
    }
}
