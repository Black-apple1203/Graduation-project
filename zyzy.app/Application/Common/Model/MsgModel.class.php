<?php
namespace Common\Model;
use Think\Model;
class MsgModel extends Model {
    protected $_validate = array(
        array('pid', 'number', '{%msg_format_error_pid}', 2, 'regex', 3),
        array('touid', 'require', '{%msg_format_null_touid}'),
        array('touid', 'number', '{%msg_format_error_touid}', 2, 'regex', 3),
        array('message', '2,200', '{%msg_format_length_message}', 1, 'length', 3),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('mutually', 3),
        array('spid', 0),
    );

    /**
     * [add_msg 发送消息]
     */
    public function msg_send($data, $user) {
        if (false === $this->create($data)) return array('state' => 0, 'error' => $this->getError());
        $this->fromuid = $user['uid'];
        if (false === $data['id'] = $this->add()) return array('state' => 0, 'error' => '消息发送失败，请重新操作！');
        if ($data['pid']) $this->where(array('id' => $data['pid']))->setfield('spid', 1);
        if (M('MembersMsgtip')->where(array('uid' => $data['touid'], 'type' => 3))->find()) {
            M('MembersMsgtip')->where(array('uid' => $data['touid'], 'type' => 3))->save(array('update_time' => time(), 'unread' => array('exp', 'unread+1')));
        } else {
            M('MembersMsgtip')->add(array('uid' => $data['touid'], 'type' => 3, 'update_time' => time(), 'unread' => 1));
        }
        if ($user['utype'] == 1) {
            $id = M('Resume')->where(array('uid' => $data['touid']))->order('def desc')->getfield('id');
            $data['to_url'] = $id ? url_rewrite('QS_resumeshow', array('id' => $id)) : '';
            $data['to_name'] = D('Resume')->where(array('uid' => $data['touid'], 'def' => 1))->limit(1)->getfield('fullname');
            if ($data['pid']) {
                $time = $this->where(array('id' => $data['pid']))->getfield('addtime');
                $time = $time + 86400 * 3;
                $count = $this->where(array('pid' => $data['pid'], 'addtime' => array('lt', $time)))->count('id');
                if ($count <= 1) {
                    D('TaskLog')->do_task($user, 'reply_consultation');
                }
            }
        } else {
            $company = M('CompanyProfile')->field('id,companyname')->where(array('uid' => $data['touid']))->find();
            $data['to_url'] = url_rewrite('QS_companyshow', array('id' => $company['id']));
            $data['to_name'] = $company['companyname'];
        }
        //写入会员日志
        write_members_log($user, '', '发送站内信消息（消息id：' . $data['pid'] . '，接收人uid：' . $data['touid'] . '，消息内容：' . $data['message'] . '）');
        return array('state' => 1, 'data' => $data);
    }

    /**
     * [get_msg 消息列表]
     */
    public function msg_list($user, $s = true, $pagesize = 20) {
        $strsql = '(fromuid=' . $user['uid'] . ' AND mutually<>1) OR (touid=' . $user['uid'] . ' AND mutually<>2)';
        $where['pid'] = 0;
        $where['_string'] = $strsql;
        if (!$s) {
            $count = $this->where($where)->order('id desc')->count('id');
            $pager = pager($count, $pagesize);
            $limit = $pager->firstRow . ',' . $pager->listRows;
            $rst['count'] = $count;
            $rst['page'] = $pager->fshow();
        }
        $msgList = $this->where($where)->order('id desc')->limit($limit)->getfield('id,spid,fromuid,touid,message,addtime');
        if ($msgList) {
            if ($user['utype'] == 1) {
                foreach ($msgList as $key => $val) {
                    $val['spid'] && $sids[] = $val['id'];
                    if ($val['fromuid'] == $user['uid']) {
                        $msgList[$key]['from_name'] = '我';
                        $cids[] = $val['fromuid'];
                        $mids[] = $val['touid'];
                    } else {
                        $msgList[$key]['to_name'] = '我';
                        $cids[] = $val['touid'];
                        $mids[] = $val['fromuid'];
                    }
                }
                $members = M('Members')->where(array('uid' => array('in', $mids)))->getfield('uid,username,avatars');
                $company = M('CompanyProfile')->where(array('uid' => array('in', $cids)))->getfield('uid,companyname,logo');
                $resume = M('Resume')->where(array('uid' => array('in', $mids)))->order('def desc')->group('uid')->getfield('uid,id,fullname');
                foreach ($msgList as $key => $val) {
                    if ($val['fromuid'] == $user['uid']) {
                        $msgList[$key]['from_avatars'] = $company[$val['fromuid']]['logo'] ? attach($company[$val['fromuid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                        $msgList[$key]['to_avatars'] = $members[$val['touid']]['avatars'] ? attach($members[$val['touid']]['avatars'], 'avatar') : attach('no_photo_male.png', 'resource');
                        $msgList[$key]['to_name'] = $resume[$val['touid']]['fullname'] ?: $members[$val['touid']]['username'];
                        $msgList[$key]['to_url'] = $resume[$val['touid']] ? url_rewrite('QS_resumeshow', array('id' => $resume[$val['touid']]['id'])) : '';
                    } else {
                        $msgList[$key]['to_avatars'] = $company[$val['touid']]['logo'] ? attach($company[$val['touid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                        $msgList[$key]['from_avatars'] = $members[$val['fromuid']]['avatars'] ? attach($members[$val['fromuid']]['avatars'], 'avatar') : attach('no_photo_male.png', 'resource');
                        $msgList[$key]['from_name'] = $resume[$val['fromuid']]['fullname'] ?: $members[$val['fromuid']]['username'];
                        $msgList[$key]['from_url'] = $resume[$val['fromuid']] ? url_rewrite('QS_resumeshow', array('id' => $resume[$val['fromuid']]['id'])) : '';
                    }
                }
            } else {
                foreach ($msgList as $key => $val) {
                    $val['spid'] && $sids[] = $val['id'];
                    if ($val['fromuid'] == $user['uid']) {
                        $msgList[$key]['from_avatars'] = $user['avatars'];
                        $msgList[$key]['from_name'] = '我';
                        $cids[] = $val['touid'];
                    } else {
                        $msgList[$key]['to_avatars'] = $user['avatars'];
                        $msgList[$key]['to_name'] = '我';
                        $cids[] = $val['fromuid'];
                    }
                }
                $company = M('CompanyProfile')->where(array('uid' => array('in', $cids)))->getfield('uid,id,companyname,logo');
                foreach ($msgList as $key => $val) {
                    if ($val['fromuid'] == $user['uid']) {
                        $msgList[$key]['to_avatars'] = $company[$val['touid']]['logo'] ? attach($company[$val['touid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                        $msgList[$key]['to_name'] = $company[$val['touid']]['companyname'];
                        $msgList[$key]['to_url'] = url_rewrite('QS_companyshow', array('id' => $company[$val['touid']]['id']));
                    } else {
                        $msgList[$key]['from_avatars'] = $company[$val['fromuid']]['logo'] ? attach($company[$val['fromuid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                        $msgList[$key]['from_name'] = $company[$val['fromuid']]['companyname'];
                        $msgList[$key]['from_url'] = url_rewrite('QS_companyshow', array('id' => $company[$val['fromuid']]['id']));
                    }
                }
            }
        }
        if ($s && $sids) {
            $smsg = $this->smsg_list($sids, $user, false);
            foreach ($smsg as $key => $val) {
                $msgList[$key]['smsg'] = $val;
            }
        }
        M('MembersMsgtip')->where(array('uid' => $user['uid'], 'type' => 3))->setfield('unread', 0);
        $rst['list'] = $msgList;
        return $rst;
    }

    public function smsg_list($sids, $user, $s = true) {
        $strsql = '(fromuid=' . $user['uid'] . ' AND mutually<>1) OR (touid=' . $user['uid'] . ' AND mutually<>2)';
        !is_array($sids) && $sids = array($sids);
        $where['pid'] = array('in', $sids);
        if ($s) {
            $where['id'] = array('in', $sids);
            $where['_logic'] = 'OR';
        }
        $map['_complex'] = $where;
        $map['_string'] = $strsql;
        $msgsList = $this->where($map)->order('id asc')->getfield('id,pid,fromuid,touid,message,addtime');
        if ($user['utype'] == 1) {
            foreach ($msgsList as $key => $val) {
                if ($val['fromuid'] == $user['uid']) {
                    $msgsList[$key]['from_name'] = '我';
                    $cids[] = $val['fromuid'];
                    $mids[] = $val['touid'];
                } else {
                    $msgsList[$key]['to_name'] = '我';
                    $cids[] = $val['touid'];
                    $mids[] = $val['fromuid'];
                }
            }
            $members = M('Members')->where(array('uid' => array('in', $mids)))->getfield('uid,username,avatars');
            $company = M('CompanyProfile')->where(array('uid' => array('in', $cids)))->getfield('uid,companyname,logo');
            $resume = M('Resume')->where(array('uid' => array('in', $mids)))->order('def desc')->group('uid')->getfield('uid,id,fullname,sex_cn,birthdate,current_cn,intention_jobs,telephone');
            foreach ($msgsList as $val) {
                if ($val['fromuid'] == $user['uid']) {
                    $val['to_name'] = $resume[$val['touid']]['fullname'] ?: $members[$val['touid']]['username'];
                    $val['from_avatars'] = $company[$val['fromuid']]['logo'] ? attach($company[$val['fromuid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                    $val['to_avatars'] = $members[$val['touid']]['avatars'] ? attach($members[$val['touid']]['avatars'], 'avatar') : attach('no_photo_male.png', 'resource');
                    $val['to_url'] = $resume[$val['touid']] ? url_rewrite('QS_resumeshow', array('id' => $resume[$val['touid']]['id'])) : '';
                    $val['resume'] = $resume[$val['touid']];
                } else {
                    $val['from_name'] = $resume[$val['touid']]['fullname'] ?: $members[$val['fromuid']]['username'];
                    $val['to_avatars'] = $company[$val['touid']]['logo'] ? attach($company[$val['touid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                    $val['from_avatars'] = $members[$val['fromuid']]['avatars'] ? attach($members[$val['fromuid']]['avatars'], 'avatar') : attach('no_photo_male.png', 'resource');
                    $val['from_url'] = $resume[$val['fromuid']] ? url_rewrite('QS_resumeshow', array('id' => $resume[$val['fromuid']]['id'])) : '';
                    $val['resume'] = $resume[$val['fromuid']];
                }
                $s ? $msgList[] = $val : $msgList[$val['pid']][] = $val;
            }
        } else {
            foreach ($msgsList as $key => $val) {
                if ($val['fromuid'] == $user['uid']) {
                    $msgsList[$key]['from_name'] = '我';
                    $cids[] = $val['touid'];
                } else {
                    $msgsList[$key]['to_name'] = '我';
                    $cids[] = $val['fromuid'];
                }
            }
            $company = M('CompanyProfile')->where(array('uid' => array('in', $cids)))->getfield('uid,id,companyname,logo');
            foreach ($msgsList as $val) {
                if ($val['fromuid'] == $user['uid']) {
                    $val['to_name'] = $company[$val['touid']]['companyname'];
                    $val['to_avatars'] = $company[$val['touid']]['logo'] ? attach($company[$val['touid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                    $val['to_url'] = url_rewrite('QS_companyshow', array('id' => $company[$val['touid']]['id']));
                } else {
                    $val['from_name'] = $company[$val['fromuid']]['companyname'];
                    $val['from_avatars'] = $company[$val['fromuid']]['logo'] ? attach($company[$val['fromuid']]['logo'], 'company_logo') : attach('no_logo.png', 'resource');
                    $val['from_url'] = url_rewrite('QS_companyshow', array('id' => $company[$val['fromuid']]['id']));
                }
                $s ? $msgList[] = $val : $msgList[$val['pid']][] = $val;
            }
        }
        return $msgList;
    }

    /**
     * [del_msg 删除消息]
     */
    public function msg_del($id, $user) {
        $msg = $this->field('id,spid,fromuid,touid,mutually')->where('id=' . intval($id) . ' AND ((fromuid=' . $user['uid'] . ' AND mutually<>1) OR (touid=' . $user['uid'] . ' AND mutually<>2))')->find();
        $where['id'] = $msg['id'];
        if ($msg['mutually'] != 3) {
            $where['pid'] = $msg['id'];
            $where['_logic'] = 'OR';
            $result = $this->where($where)->delete();
        } else {
            $val = $user['uid'] == $msg['fromuid'] ? 1 : 2;
            $result = $this->where($where)->setField('mutually', $val);
            if ($msg['spid']) {
                $this->where('pid=' . $msg['id'] . ' AND ((fromuid=' . $user['uid'] . ' AND mutually=2) OR (touid=' . $user['uid'] . ' AND mutually=1))')->delete();
                $this->where(array('pid' => $msg['id'], 'fromuid' => $user['uid'], 'mutually' => 3))->setfield('mutually', 1);
                $this->where(array('pid' => $msg['id'], 'touid' => $user['uid'], 'mutually' => 3))->setfield('mutually', 2);
            }
        }
        if (false === $result) return array('state' => 0, 'tip' => '删除失败！');
        //写入会员日志
        write_members_log($user, '', '删除站内信消息（消息id：' . $id . '）');
        return array('state' => 1, 'tip' => '删除成功！');
    }
}

?>