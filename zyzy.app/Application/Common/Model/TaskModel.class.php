<?php
namespace Common\Model;
use Think\Model;
class TaskModel extends Model {
    /**
     * 生成缓存文件
     */
    public function task_cache() {
        $task = $this->where(array('status' => 1))->getField('t_alias,id,title,points,once,becount,times,utype');
        F('task', $task);
        return $task;
    }

    /**
     * 获取指定条件的任务
     */
    public function get_task_cache($utype = 2, $alias = '') {
        if (false === F('task')) {
            $cache = $this->task_cache();
        } else {
            $cache = F('task');
        }
        $return = array();
        // 获取开通的第三方帐号
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $bindingArr = array('binding_weibo' => 'sina', 'binding_qq' => 'qq', 'binding_weixin' => 'weixin');
        foreach ($cache as $key => $value) {
            if ($value['utype'] == $utype || $value['utype'] == 0) {
                // 若任务中有未开通的第三方帐号的绑定任务，则不显示该任务
                if (array_key_exists($key, $bindingArr)) {
                    if (!array_key_exists($bindingArr[$key], $oauth_list)) {
                        continue;
                    }
                }
                $return[$key] = $value;
            }
        }
        if ($alias) {
            return $return[$alias];
        } else {
            return $return;
        }
    }

    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('task', NULL);
    }

    /**
     * 获取任务链接地址(PC)
     */
    public function task_url($utype = 1) {
        $data['sign'] = array('type' => 'ajax', 'url' => U('Members/sign_in'));
        $data['upload_logo'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['upload_companyimg'] = array('type' => 'self', 'url' => U('Company/com_img'));
        $data['report_resume'] = array('type' => 'blank', 'url' => url_rewrite('QS_resume'));
        $data['verified_mobile'] = array('type' => 'self', 'url' => U('Company/user_security'));
        $data['binding_weixin'] = array('type' => 'self', 'url' => U('Company/user_security'));
        $data['binding_weibo'] = array('type' => 'self', 'url' => U('Company/user_security'));
        $data['binding_qq'] = array('type' => 'self', 'url' => U('Company/user_security'));
        $data['done_profile'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['handle_resume'] = array('type' => 'self', 'url' => U('Company/jobs_apply'));
        $data['set_map'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['license_audit'] = array('type' => 'self', 'url' => U('Company/com_auth'));
        $data['reply_consultation'] = array('type' => 'self', 'url' => U('Company/pms_consult'));
        $url[1] = $data;
        unset($data);

        $data['sign'] = array('type' => 'ajax', 'url' => U('Members/sign_in'));
        $data['submit_resume'] = array('type' => 'blank', 'url' => url_rewrite('QS_jobs'));
        $data['upload_avatar'] = array('type' => 'self', 'url' => U('personal/user_avatar'));
        $data['refresh_resume'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['verified_mobile'] = array('type' => 'self', 'url' => U('personal/user_safety'));
        $data['binding_weixin'] = array('type' => 'self', 'url' => U('personal/user_safety'));
        $data['binding_weibo'] = array('type' => 'self', 'url' => U('personal/user_safety'));
        $data['binding_qq'] = array('type' => 'self', 'url' => U('personal/user_safety'));
        $data['complete_60'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['complete_90'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['report_jobs'] = array('type' => 'blank', 'url' => url_rewrite('QS_jobs'));
        $url[2] = $data;
        unset($data);

        return $url[$utype];
    }

    /**
     * 获取任务链接地址(触屏)
     */
    public function task_url_mobile($pid, $utype = 1) {
        $data['sign'] = array('type' => 'ajax', 'url' => U('Members/sign_in'));
        $data['upload_logo'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['upload_companyimg'] = array('type' => 'self', 'url' => U('Company/com_info'));//修改上传企业风采的地址com_img   h 2019-10-29
        $data['report_resume'] = array('type' => 'nourl', 'url' => '');
        $data['verified_mobile'] = array('type' => 'self', 'url' => U('Company/com_security_tel'));
        $data['binding_weixin'] = array('type' => 'nourl', 'url' => '');
        $data['binding_weibo'] = array('type' => 'self', 'url' => U('Company/com_security'));//修改上传企业风采的地址com_binding   h 2019-10-29
        $data['binding_qq'] = array('type' => 'self', 'url' => U('Company/com_security'));//修改上传企业风采的地址com_binding   h 2019-10-29
        $data['done_profile'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['handle_resume'] = array('type' => 'self', 'url' => U('Company/jobs_apply'));
        $data['set_map'] = array('type' => 'self', 'url' => U('Company/com_info'));
        $data['license_audit'] = array('type' => 'self', 'url' => U('Company/com_auth'));
        $data['reply_consultation'] = array('type' => 'self', 'url' => U('Company/pms_list', array('type' => 1)));
        $url[1] = $data;
        unset($data);

        $data['sign'] = array('type' => 'ajax', 'url' => U('Members/sign_in'));
        $data['submit_resume'] = array('type' => 'blank', 'url' => url_rewrite('QS_jobslist'));
        $data['upload_avatar'] = array('type' => 'self', 'url' => U('personal/resume_edit_basis', array('pid' => $pid)));
        $data['refresh_resume'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['verified_mobile'] = array('type' => 'self', 'url' => U('personal/per_security_tel'));
        $data['binding_weixin'] = array('type' => 'nourl', 'url' => '');
        $data['binding_weibo'] = array('type' => 'self', 'url' => U('personal/per_binding'));
        $data['binding_qq'] = array('type' => 'self', 'url' => U('personal/per_binding'));
        $data['complete_90'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['complete_60'] = array('type' => 'self', 'url' => U('personal/index'));
        $data['report_jobs'] = array('type' => 'nourl', 'url' => '');
        $url[2] = $data;
        unset($data);

        return $url[$utype];
    }
}

?>