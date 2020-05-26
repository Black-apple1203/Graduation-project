<?php
/*
 *简历模型类
 */
namespace Common\Model;

use Think\Model\RelationModel;
use DateTime;

class ResumeModel extends RelationModel {
    protected $_user = array();
    protected $_validate = array(
        //array('title', '1,24', '{%resume_title_length_error}', 0, 'length'), // 简历标题
        array('fullname', '4,16', '{%resume_fullname_length_error}', 0, 'length'), // 姓名
        array('sex', array(1, 2), '{%resume_sex_format_error}', 0, 'in'), // 性别
        //array('marriage', array(1, 2, 3), '{%resume_marriage_between_error}', 0, 'in'), // 婚姻状况
        array('uid,fullname,nature,birthdate,experience,wage,education,current', 'identicalNull', '', 0, 'callback'),
        array('uid,nature,birthdate,experience,wage,education,major,current,height', 'identicalEnum', '', 2, 'callback'),
        array('telephone', 'mobile', '{%resume_telephone_format_error}', 2), // 手机号
        array('email', 'email', '{%resume_email_format_error}', 2), // 邮箱
        array('email', '2,60', '{%resume_email_length_error}', 2, 'length'), // 邮箱
        //array('telephone','_repetition_mobile','{%resume_repetition_mobile}',2,'callback'),
        //array('email','_repetition_email','{%resume_repetition_email}',2,'callback'),
        array('residence', '0,30', '{%resume_residence_length_error}', 0, 'length'), // 现居住地
        array('householdaddress', '2,60', '{%resume_householdaddress_length_error}', 2, 'length'), // 户口所在地
        array('specialty', '1,4000', '{%resume_specialty_length_error}', 0, 'length'), // 自我描述
        array('height', '0,3', '{%resume_height_length_error}', 0, 'length'), // 身高
        array('qq', 'number', '{%resume_error_qq}', 2),
        array('qq', '0,11', '{%resume_error_qq}', 2, 'length'),
        array('weixin', '6,20', '{%resume_length_error_weixin}', 2, 'length'),
        array('weixin','english_number','{%resume_weixin_format_error}',2),
        array('idcard', '_idcard', '{%resume_format_idcard}', 2, 'callback'),
        array('idcard', '_repetition_idcard', '{%resume_repetition_idcard}', 2, 'callback'),
    );
    protected $_auto = array(
        array('title', '_title', 1, 'callback'),
        array('display', 1),//是否显示
        array('display_name', 1), // 显示简历名称
        array('audit', 2), // 简历审核
        array('email_notify', 1), // 邮件接收通知
        array('photo', 0), // 是否为照片简历
        array('photo_audit', 2), // 照片审核
        array('addtime', 'time', 1, 'function'), //添加时间
        array('refreshtime', 'time', 1, 'function'), //简历刷新时间
        array('stime', 'time', 1, 'function'),
        array('photo_display', 1), // 是否显示照片
        array('entrust', 0), // 简历委托
        array('talent', 0), // 高级人才
        array('complete_percent', 0), // 简历完整度
        array('click', 1), // 查看次数
        array('tpl', 'default'),//简历模板
        array('resume_from_pc', 0), // 简历来自PC(1->是)
        array('marriage', 3, 1)
    );

    protected function _title() {
        return '我的简历' . date('Ymd', time());
    }

    protected function _repetition_email($data) {
        $uid = M('Members')->where(array('email' => $data))->getfield('uid');
        if ($uid && $uid != $this->_user['uid']) return false;
        return true;
    }

    protected function _repetition_mobile($data) {
        $uid = M('Members')->where(array('mobile' => $data))->getfield('uid');
        if ($uid && $uid != $this->_user['uid']) return false;
        return true;
    }

    protected function _repetition_idcard($data) {
        $uid = $this->where(array('idcard' => $data))->getfield('uid');
        if ($uid && $uid != $this->_user['uid']) return false;
        return true;
    }

    protected function _idcard($idcard) {
        if (empty($idcard)) return false;
        $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            $d = new DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) {
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9);//15to18
            $Bit18 = $this->getVerifyBit($idcard);//算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }
        //18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        $d = new DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        //身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != $this->getVerifyBit($idcard_base)) {
            return false;
        }
        return true;
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    protected function getVerifyBit($idcard_base) {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    /*
		获取简历列表 get_resume_list
		@data array 简历条件
		@countinterview 面试邀请数
		@countdown 下载数
		@countapply 申请职位数
		@views  关注我的数量
	*/
    public function get_resume_list($data) {
        $init = array('where' => array(), 'field' => '*', 'order' => '', 'countinterview' => false, 'countdown' => false, 'countapply' => false, 'views' => false, 'stick' => false, 'strong_tag' => false);
        $init = array_merge($init, $data);
        $list = $this->field($init['field'])->where($init['where'])->order($init['order'])->limit($init['limit'])->select();
        foreach ($list as $key => $value) {
            $value['number'] = "N" . str_pad($value['id'], 7, "0", STR_PAD_LEFT);
            if ($init['countinterview']) {
                $value['countinterview'] = M('CompanyInterview')->where(array('resume_uid' => $value['uid'], 'resume_id' => $value['id']))->count();
            }
            if ($init['countdown']) {
                $value['countdown'] = M('CompanyDownResume')->where(array('resume_uid' => $value['uid'], 'resume_id' => $value['id']))->count();
            }
            if ($init['countapply']) {
                $value['countapply'] = M('PersonalJobsApply')->where(array('personal_uid' => $value['uid'], 'resume_id' => $value['id']))->count();
            }
            if ($init['views']) {
                $value['views'] = M('ViewResume')->where(array('resumeid' => $value['id']))->count('id');
            }
            if ($init['stick']) {
                $value['stick_info'] = D('PersonalServiceStickLog')->check_stick_log(array('resume_id' => $value['id']));
            }
            if ($init['strong_tag']) {
                $tag_log = D('PersonalServiceTagLog')->check_tag_log(array('resume_id' => $value['id']));
                $tag_log['tag_name'] = D('PersonalServiceTagCategory')->where(array('id' => $tag_log['tag_id']))->getField('name');
                $value['tag_info'] = $tag_log;
            }
            if ($value['audit'] == 2) {
                $value['_audit'] = C('qscms_resume_display') == 2 ? 1 : $value['audit'];
            } else {
                $value['_audit'] = $value['audit'];
            }
            if ($init['_audit'] == 1) {
                $value['_audit'] == 1 && $resume_list[] = $value;
            } else {
                $resume_list[] = $value;
            }
        }
        return $resume_list;
    }

    public function resume_one($uid) {
        $where['uid'] = $uid;
        $resume_one = $this->where($where)->find();
        return $resume_one;
    }

    public function get_resume_one($pid) {
        $where['id'] = $pid;
        $resume_one = $this->where($where)->find();
        return $resume_one;
    }

    // 统计简历数
    public function count_resume($data) {
        $resume_num = $this->where($data)->count();
        return $resume_num;
    }

    public function get_resume_basic($uid, $id) {
        $id = intval($id);
        $uid = intval($uid);
        $where['uid'] = $uid;
        $where['id'] = $id;
        $info = $this->where($where)->find();
        if (!$info) return false;
        $info['age'] = date("Y") - $info['birthdate'];
        $info['number'] = "N" . str_pad($info['id'], 7, "0", STR_PAD_LEFT);
        $info['lastname'] = $info['fullname'];
        return $info;
    }

    public function count_personal_attention_me($uid) {
        $id_arr = array();
        $id_str = "";
        $total = 0;
        $where['uid'] = $uid;
        $personal_resume = $this->where($where)->select();
        if ($personal_resume) {
            foreach ($personal_resume as $key => $value) {
                $id_arr[] = $value["id"];
            }
            $view_resume = M('ViewResume'); // 实例化User对象
            $count_attention_me = $view_resume->where(array('resumeid' => array('in', $id_arr)))->count();
        }
        return $count_attention_me;
    }

    /**
     * [refresh_resume 刷新简历更新‘简历刷新时间’，记录操作日志]
     */
    public function refresh_resume($pid, $user) {
        $uid = $user['uid'];
        $time = time();
        if (!is_array($pid)) $pid = array($pid);
        $pid_num = count($pid);
        $sqlin = implode(",", $pid);
        $data = array('refreshtime' => $time, 'stime' => $time);
        $where['id'] = array('in', $sqlin);
        $where['uid'] = $uid;
        if (!$this->where($where)->save($data)) return false;
        if (!M('ResumeSearchPrecise')->where($where)->save($data)) return false;
        if (!M('ResumeSearchFull')->where($where)->save($data)) return false;
        $where['stick'] = 1;
        if ($rids = $this->where($where)->getfield('id', true)) {
            $data = array('stime' => $time + 100000000);
            $where = array('id' => array('in', $rids));
            if (!$this->where($where)->save($data)) return false;
            if (!M('ResumeSearchPrecise')->where($where)->save($data)) return false;
            if (!M('ResumeSearchFull')->where($where)->save($data)) return false;
        }
        $r = D('TaskLog')->do_task($user, 'refresh_resume');
        //写入会员日志
        foreach ($pid as $k => $v) {
            write_members_log($user, 'resume', '刷新简历（简历id：' . $v . '）', false, array('resume_id' => $v));
        }

        // 刷新日志
        $refresh_log['uid'] = $uid;
        $refresh_log['type'] = '2001';
        $refresh_log['mode'] = 0;
        write_refresh_log($refresh_log);
        return $r;
    }

    //删除简历
    public function del_resume($user, $pid) {
        if (!is_array($pid)) $pid = array($pid);
        $sqlin = implode(',', $pid);
        $where['id'] = array('in', $pid);
        $where['uid'] = $user['uid'];
        $_where['pid'] = array('in', $pid);
        $_where['uid'] = $user['uid'];
        if (false === $pid_num = $this->where($where)->delete()) return false;
        if (false === M('ResumeEducation')->where($_where)->delete()) return false;
        if (false === M('ResumeTraining')->where($_where)->delete()) return false;
        if (false === M('ResumeWork')->where($_where)->delete()) return false;
        if (false === M('ResumeCredent')->where($_where)->delete()) return false;
        if (false === M('ResumeLanguage')->where($_where)->delete()) return false;
        if (false === M('ResumeSearchPrecise')->where($where)->delete()) return false;
        if (false === M('ResumeSearchFull')->where($where)->delete()) return false;
        if (false === M('ViewResume')->where(array('resumeid' => array('in', $sqlin)))->delete()) return false;
        if (false === M('ResumeEntrust')->where(array('resume_id' => array('in', $sqlin)))->delete()) return false;

        //写入会员日志
        foreach ($pid as $k => $v) {
            write_members_log($user, 'resume', '删除简历（简历id：' . $v . '）', false, array('resume_id' => $v));
        }
        return true;
    }

    /*
		创建简历
	*/
    public function add_resume($data, $user) {
        $this->_user = $user;
        $resume = $this->where(array('uid' => $user['uid']))->find();
        if ($resume) {
            return array('state' => 0, 'error' => '该会员已添加简历！');
        }
        if (false === $d = $this->create($data)) {
            return array('state' => 0, 'error' => $this->getError());
        } else {
            $data['title'] == '' && $data['title'] = '我的简历' . date('Ymd');
            if ($user['mobile']) $this->telephone = $d['mobile'] = $user['mobile'];
            $category = D('Category')->get_category_cache();
            $major_category = D('CategoryMajor')->get_major_list();
            $sex = array('1' => '男', '2' => '女');
            $marriage = array('1' => '未婚', '2' => '已婚', '3' => '保密');
            //意向行业
            if ($d['trade']) {
                foreach (explode(',', $d['trade']) as $val) {
                    $trade_cn[] = $category['QS_trade'][$val];
                }
            } else {
                $trade_cn = array();
            }

            //意向地区
            $city = get_city_info($data['district']);
            $this->district = $data['district'] = $city['district'];

            //意向职位
            $jobs = D('CategoryJobs')->get_jobs_cache('all');
            foreach (explode(',', $data['intention_jobs_id']) as $val) {
                $val = explode('.', $val);
                $intention[] = $val[2] ? $jobs[$val[1]][$val[2]] : ($val[1] ? $jobs[$val[0]][$val[1]] : $jobs[0][$val[0]]);
            }
            $this->uid = $d['uid'] = $user['uid'];
            $this->sex_cn = $d['sex_cn'] = $sex[$d['sex']];
            $this->marriage_cn = $d['marriage_cn'] = $marriage[$d['marriage']];
            $this->education_cn = $d['education_cn'] = $category['QS_education'][$d['education']];
            $this->experience_cn = $d['experience_cn'] = $category['QS_experience'][$d['experience']];
            $this->wage_cn = $d['wage_cn'] = $category['QS_wage_k'][$d['wage']];
            $this->current_cn = $d['current_cn'] = $category['QS_current'][$d['current']];
            $this->nature_cn = $d['nature_cn'] = $category['QS_jobs_nature'][$d['nature']];
            $this->trade_cn = $d['trade_cn'] = implode(',', $trade_cn);
            $this->district_cn = $d['district_cn'] = $city['district_cn'];
            $this->intention_jobs = $d['intention_jobs'] = implode(',', $intention);
            $this->audit = 2;
            $this->photo_img = $user['is_avatars'] ? $user['avatar'] : '';
            $d['major'] && $this->major_cn = $d['major_cn'] = $major_category[$d['major']]['categoryname'];
            $this->resume_from_pc = 1;
            if (false === $insert_id = $this->add()) return array('state' => 0, 'error' => '数据添加失败！');
        }
        $data = array_merge($data, $d);
        $searchtab['id'] = $insert_id;
        $searchtab['uid'] = $user['uid'];

        // 检查完整度,整理索引表
        $this->check_resume($user['uid'], $insert_id);
        // 委托投递
        if (intval($data['entrust'])) {
            D('ResumeEntrust')->set_resume_entrust($insert_id, $user['uid']);
        }

        //写入会员日志
        write_members_log($user, 'resume', '创建简历（简历id：' . $insert_id . '）', false, array('resume_id' => $insert_id));
        if (C('qscms_resume_display') == 2) {
            baidu_submiturl(url_rewrite('QS_resumeshow', array('id' => $insertid)), 'addresume');
        }
        if (true !== $reg = D('Members')->update_user_info($data, $user)) array('state' => 0, 'error' => $reg);
        if (C('apply.Statistics')) {
            $idata['pid'] = $insert_id;
            $idata['sex'] = $data['sex'];
            $idata['birthdate'] = $data['birthdate'];
            $idata['education'] = $data['education'];
            $idata['experience'] = $data['experience'];
            $idata['major'] = $data['major'];
            $idata['addtime'] = time();
            $class = new \Statistics\Model\CModel($idata);
            $class->resume_add();
        }
        if (C('apply.Allowance')) {
            $setsqlarr['uid'] = $user['uid'];
            $setsqlarr['resume_id'] = $insert_id;
            $setsqlarr['intention_jobs'] = $data['intention_jobs_id'];
            $setsqlarr['intention_jobs_cn'] = $data['intention_jobs'];
            $setsqlarr['is_new_record'] = 1;
            D('Allowance/AllowanceEditIntentionLog')->add($setsqlarr);
        }

        //才情start
        $talent_api = new \Common\qscmslib\talent;
        $talent_api->act='resume_add';
        $talent_api->data = array(
            'pid'=>$insert_id,
            'sex'=>$data['sex'],
            'birthdate'=>$data['birthdate'],
            'education'=>$data['education'],
            'experience'=>$data['experience'],
            'major'=>$data['major']
        );
        $talent_api->send();
        //才情end
        if($data['intention_jobs_id']){
            $intention_arr = explode(",", $data['intention_jobs_id']);
            //才情start
            foreach ($intention_arr as $k1 => $v1) {
                $v2 = explode(".", $v1);
                $talent_api = new \Common\qscmslib\talent;
                $talent_api->act='resume_jobs_add';
                $talent_api->data = array(
                    'pid'=>$insert_id,
                    'category'=>$v2[1],
                    'subclass'=>$v2[2]
                );
                $talent_api->send();
                unset($talent_api);
            }
            //才情end
        }

        return array('state' => 1, 'id' => $insert_id);
    }

    /*
	**	修改简历
	*/
    public function save_resume($data, $pid, $user) {
        $this->_user = $user;
        $data['id'] = $pid;
        $data['uid'] = $user['uid'];
        if (C('qscms_audit_edit_resume') != '-1') {
            $data['audit'] = intval(C('qscms_audit_edit_resume'));
        } else {
            $resume = $this->where(array('id' => $pid))->field('audit')->find();
            if ($resume['audit'] == 3) {
                $data['audit'] = 2;
            }
        }
        if (false === $d = $this->create($data)) {
            return array('state' => 0, 'error' => $this->getError());
        } else {
            if ($user['mobile']) $this->telephone = $d['mobile'] = $user['mobile'];
            $category = D('Category')->get_category_cache();
            $major_category = D('CategoryMajor')->get_major_list();
            $sex = array('1' => '男', '2' => '女');
            $marriage = array('1' => '未婚', '2' => '已婚', '3' => '保密');
            $d['sex'] && $this->sex_cn = $d['sex_cn'] = $sex[$d['sex']];
            $d['major'] && $this->major_cn = $d['major_cn'] = $major_category[$d['major']]['categoryname'];
            $d['marriage'] && $this->marriage_cn = $d['marriage_cn'] = $marriage[$d['marriage']];
            $d['education'] && $this->education_cn = $d['education_cn'] = $category['QS_education'][$d['education']];
            $d['experience'] && $this->experience_cn = $d['experience_cn'] = $category['QS_experience'][$d['experience']];
            $d['wage'] && $this->wage_cn = $d['wage_cn'] = $category['QS_wage_k'][$d['wage']];
            $d['current'] && $this->current_cn = $d['current_cn'] = $category['QS_current'][$d['current']];
            $d['nature'] && $this->nature_cn = $d['nature_cn'] = $category['QS_jobs_nature'][$d['nature']];
            //意向行业
            if (isset($data['trade'])) {
                if ($data['trade']) {
                    foreach (explode(',', $data['trade']) as $val) {
                        $trade_cn[] = $category['QS_trade'][$val];
                    }
                    $this->trade_cn = $d['trade_cn'] = implode(',', $trade_cn);
                } else {
                    $this->trade_cn = $d['trade_cn'] = '';
                }
            }

            //意向地区
            if ($data['district']) {
                $city = get_city_info($data['district']);
                $this->district = $data['district'] = $city['district'];
                $this->district_cn = $d['district_cn'] = $city['district_cn'];
            }
            $attach = '';
            //意向职位
            if ($data['intention_jobs_id']) {
                $jobs = D('CategoryJobs')->get_jobs_cache('all');
                foreach (explode(',', $data['intention_jobs_id']) as $val) {
                    $val = explode('.', $val);
                    if (isset($val[2]) && $val[2] > 0) {
                        $intention[] = $jobs[$val[1]][$val[2]];
                    } else if (isset($val[1]) && $val[1] > 0) {
                        $intention[] = $jobs[$val[0]][$val[1]];
                    } else {
                        $intention[] = $jobs[0][$val[0]];
                    }
                    // $intention[] = $val[2] ? $jobs[$val[1]][$val[2]] : $jobs[$val[0]][$val[1]];
                }
                $this->intention_jobs = $d['intention_jobs'] = implode(',', $intention);
                if (C('apply.Allowance')) {
                    $check_result = D('Allowance/AllowanceEditIntentionLog')->check_intention_jobs($data['intention_jobs_id'], $d['intention_jobs'], $pid);
                    if (!$check_result) {
                        if (false === $allowance_config = F('allowance_config')) {
                            $allowance_config = D('Allowance/AllowanceConfig')->config_cache();
                        }
                        $attach = '意向职位' . $allowance_config['resume_intentionjobs_edit_timespace'] . '小时内只能修改一次';
                        unset($this->intention_jobs);
                        unset($this->intention_jobs_id);
                    }
                }
                //才情start
                $intention_arr = explode(",", $data['intention_jobs_id']);
                $tmp_arr = array();
                foreach ($intention_arr as $k1 => $v1) {
                    $v2 = explode(".", $v1);
                    if(in_array($v2[0].'-'.$v2[1],$tmp_arr)){
                        continue;
                    }
                    $tmp_arr[] = $v2[0].'-'.$v2[1];
                    $talent_api = new \Common\qscmslib\talent;
                    $talent_api->act='resume_jobs_add';
                    $talent_api->data = array(
                        'pid'=>$pid,
                        'category'=>$v2[1],
                        'subclass'=>$v2[2]
                    );
                    $talent_api->send();
                    unset($talent_api);
                }
                //才情end
            }
            if (false === $this->save()) {
                return array('state' => 0, 'error' => '更新失败！');
            }
        }
        $data = array_merge($data, $d);
        $this->check_resume($user['uid'], intval($pid));
        $this->refresh_resume($pid, $user);
        //写入会员日志
        write_members_log($user, 'resume', '修改简历（简历id：' . $pid . '）', false, array('resume_id' => $pid));
        if (true !== $reg = D('Members')->update_user_info($data, $user)) return array('state' => 0, 'error' => $reg);
        return array('state' => 1, 'id' => $pid, 'attach' => $attach);
    }

    /*
		检查简历的完整度，并且完善简历索引信息
		@ $uid 会员uid
		@ $pid 简历id
	*/
    public function check_resume($uid, $pid) {
        $uid = intval($uid);
        $pid = intval($pid);
        $percent = 0;
        $resume = $this->get_resume_basic($uid, $pid);
        $where = array('uid' => $uid, 'pid' => $pid);
        $resume_education = M('ResumeEducation')->where($where)->select();
        $resume_work = M('ResumeWork')->where($where)->select();
        $resume_training = M('ResumeTraining')->where($where)->select();
        $resume_tag = $resume['tag_cn'];
        $resume_specialty = $resume['specialty'];
        $resume_photo = $resume['photo_img'];
        $resume_language = M('ResumeLanguage')->where($where)->select();
        $resume_credent = M('ResumeCredent')->where($where)->select();
        $resume_project = M('ResumeProject')->where($where)->select();
        $where = array('uid' => $uid, 'resume_id' => $pid);
        $resume_img = M('ResumeImg')->where($where)->select();
        if ($resume) $percent = $percent + 35;//基本
        if ($resume_education) $percent = $percent + 15;//教育
        if ($resume_work) $percent = $percent + 15;//工作
        if ($resume_training) $percent = $percent + 5;//培训
        if ($resume_project) $percent = $percent + 7;//培训
        if ($resume_tag) $percent = $percent + 5;//标签
        if ($resume_specialty) $percent = $percent + 5;//自我描述
        if ($resume_photo) $percent = $percent + 5;//照片
        if ($resume_language) $percent = $percent + 3;//语言
        if ($resume_credent) $percent = $percent + 2;//证书
        if ($resume_img) $percent = $percent + 3;//附件
        if ($resume['photo_img'] && ($resume['photo_audit'] == 1 || C('qscms_resume_img_display') == 2 && $resume['photo_audit'] == 2) && $resume['photo_display'] == "1") {
            $setsqlarr['photo'] = $resume['photo'] = 1;
        } else {
            $setsqlarr['photo'] = $resume['photo'] = 0;
        }
        $setsqlarr['complete_percent'] = $percent;
        //邀请红包 更新简历完整度
        $invite = M('InviteAllowance')->where(array('invitee_uid'=>$uid))->find();
        if($invite){
            M('InviteAllowance')->where(array('invitee_uid'=>$uid))->setfield('resume_percent', $percent);
        }
        //省市,职位,行业,标签,专业
        if ($resume['district']) {
            $t = explode(',', $resume['district']);
            foreach ($t as $key => $val) {
                $a = array_filter(explode('.', $val));
                for ($i = count($a) - 1; $i >= 0; $i--) {
                    $d[] = 'city' . implode('_', $a);
                    $a[$i] = 0;
                }
            }
        }
        if ($resume['intention_jobs_id']) {
            $t = explode(',', $resume['intention_jobs_id']);
            foreach ($t as $key => $val) {
                $a = array_filter(explode('.', $val));
                for ($i = count($a) - 1; $i >= 0; $i--) {
                    $d[] = 'jobs' . implode('_', $a);
                    $a[$i] = 0;
                }
            }
        }
        if ($resume['trade']) {
            $t = explode(',', $resume['trade']);
            foreach ($t as $key => $val) {
                $d[] = 'trade' . $val;
            }
        }
        if ($resume['tag']) {
            $t = explode(',', $resume['tag']);
            foreach ($t as $key => $val) {
                $d[] = 'tag' . $val;
            }
        }
        //工作年限,学历,性别,是否照片简历,简历等级,简历更新时间
        foreach (array('sex' => 'sex', 'audit' => 'audit', 'nat' => 'nature', 'bir' => 'birthdate', 'mar' => 'marriage', 'exp' => 'experience', 'wage' => 'wage', 'edu' => 'education', 'major' => 'major', 'photo' => 'photo', 'talent' => 'talent', 'level' => 'level', 'cur' => 'current','sub'=>'subsite_id') as $key => $val) {
            if (isset($resume[$val])) $d[] = $key . $resume[$val];
        }
        /* 分词 start */
        $setsqlarr['key_precise'] = $resume['intention_jobs'];
        $setsqlarr['key_full'] = $resume['intention_jobs'] . $resume['education_cn'];
        $setsqlarr['key_full'] .= $resume['specialty'];
        if (!empty($resume_education)) {
            foreach ($resume_education as $li) {
                $setsqlarr['key_full'] .= $li['school'] . $li['speciality'];
            }
            //$setsqlarr['key_precise'].=$resume_education[0]['school'].$resume_education[0]['speciality'];
        }
        if (!empty($resume_work)) {
            foreach ($resume_work as $li) {
                $setsqlarr['key_full'] .= $li['companyname'] . $li['jobs'] . $li['achievements'];
                $setsqlarr['key_precise'] .= $li['jobs'];
            }
            //$setsqlarr['key_precise'].=$resume_work[0]['companyname'].$resume_work[0]['jobs'].$resume_work[0]['achievements'];
        }
        if (!empty($resume_training)) {
            foreach ($resume_training as $li) {
                $setsqlarr['key_full'] .= $li['agency'] . $li['course'] . $li['description'];
            }
            //$setsqlarr['key_precise'].=$resume_training[0]['agency'].$resume_training[0]['course'].$resume_training[0]['description'];
        }
        if (!empty($resume_language)) {
            foreach ($resume_language as $li) {
                $setsqlarr['key_full'] .= $li['language_cn'];
            }
        }
        $setsqlarr['key_full'] = implode(' ', array_merge($d, get_tags($setsqlarr['key_full'], 100)));
        $setsqlarr['key_precise'] = implode(' ', array_merge($d, get_tags($setsqlarr['key_precise'], 100)));
        /* 分词 end */
        $setsqlarr['refreshtime'] = time();
        if ($setsqlarr['complete_percent'] < 60) {
            $setsqlarr['level'] = 1;
        } elseif ($setsqlarr['complete_percent'] >= 60 && $setsqlarr['complete_percent'] < 90) {
            $setsqlarr['level'] = 2;
        } elseif ($setsqlarr['complete_percent'] >= 90) {
            $setsqlarr['level'] = 3;
        }
        $reg = $this->where(array('uid' => $uid, 'id' => $pid))->save($setsqlarr);
        if ($reg === false) return array('state' => 0, 'error' => '简历信息保存失败！');
        //记录之前的完整度
        $old_percent = $resume['complete_percent'];
        // 更新索引表
        $resume = array_merge($resume, $setsqlarr);
        $this->resume_index(false, $resume);
        if ($setsqlarr['complete_percent'] >= 60) {
            D('TaskLog')->do_task(C('visitor'), 'complete_60');
        }
        if ($setsqlarr['complete_percent'] >= 90) {
            D('TaskLog')->do_task(C('visitor'), 'complete_90');
        }
        if (C('qscms_perfected_resume_allowance_open') == 1 && $setsqlarr['complete_percent'] >= C('qscms_perfected_resume_allowance_percent') && $old_percent < C('qscms_perfected_resume_allowance_percent')) {
            $perfected_info = M('MembersPerfectedAllowance')->where(array('uid' => $uid))->find();
            if (!$perfected_info) {
                $insert_data['uid'] = $uid;
                $insert_data['percent'] = C('qscms_perfected_resume_allowance_percent');
                if (C('qscms_perfected_resume_allowance_value_min') > C('qscms_perfected_resume_allowance_value_max') || C('qscms_perfected_resume_allowance_value_min') == C('qscms_perfected_resume_allowance_value_max')) {
                    $insert_data['value'] = C('qscms_perfected_resume_allowance_value_min');
                } else {
                    $insert_data['value'] = rand(C('qscms_perfected_resume_allowance_value_min'), C('qscms_perfected_resume_allowance_value_max'));
                }
                $insert_data['addtime'] = time();
                $insert_data['status'] = 0;
                $insert_data['reason'] = '';
                $insert_data['nobind'] = 0;
                $insert_data['notice'] = 1;
                M('MembersPerfectedAllowance')->add($insert_data);
            }
        }
        $im = new \Common\qscmslib\im();
        $im->refresh($uid);
        return array('state' => 1);
    }

    /**
     * [resume_index 简历索引表更新]
     */
    public function resume_index($id, $resume) {
        if ($id && !$resume) $resume = $this->where(array('id' => $id))->find();
        if (!$resume) return array('state' => 0, 'error' => '简历不存在！');
        $precise = M('ResumeSearchPrecise');
        $full = M('ResumeSearchFull');
        $where = array('id' => $resume['id']);
        if ($resume['display'] != 1) {
            $precise->where($where)->delete();
            $full->where($where)->delete();
        } else {
            $data['id'] = $resume['id'];
            $data['uid'] = $resume['uid'];
            $data['key'] = $resume['key_precise'];
            $data['stime'] = $resume['stime'];
            $data['refreshtime'] = $resume['refreshtime'];
            $data['percent'] = $resume['complete_percent'];
            if ($precise->where($where)->find()) {
                $reg = $precise->where($where)->save($data);
            } else {
                $reg = $precise->add($data);
            }
            if ($reg === false) return array('state' => 0, 'error' => '简历索引表更新失败！');
            $data['key'] = $resume['key_full'];
            if ($full->where($where)->find()) {
                $reg = $full->where($where)->save($data);
            } else {
                $reg = $full->add($data);
            }
            if ($reg === false) return array('state' => 0, 'error' => '简历索引表更新失败！');
        }
        return array('state' => 1);
    }


    /**
     * ========================后台用的function====================================
     */
    public function admin_edit_resume_audit($id, $audit, $reason, $pms_notice, $audit_man) {
        !is_array($id) && $id = array($id);
        $sqlin = implode(",", $id);
        if (fieldRegex($sqlin, 'in')) {
            $reasona = $reason == '' ? '无' : $reason;
            $resume_list = $this->field('id,uid,display,audit,stime,refreshtime,key_full,key_precise,fullname,title,complete_percent')->where(array('id' => array('in', $sqlin)))->select();
            foreach ($resume_list as $key => $val) {
                $search = '/audit(\d+)/';
                $replace = 'audit' . $audit;
                $val['key_precise'] = $d['key_precise'] = preg_replace($search, $replace, $val['key_precise']);
                $val['key_full'] = $d['key_full'] = preg_replace($search, $replace, $val['key_full']);
                $val['audit'] = $d['audit'] = $audit;
                if (false === $this->where(array('id' => $val['id']))->save($d)) return false;
                if ($audit == 1) {
                    baidu_submiturl(url_rewrite('QS_resumeshow', array('id' => $val['id'])), 'addresume');
                }
                $this->resume_index(false, $val);
                write_members_log(array('uid' => $val['uid'], 'utype' => 2, 'username' => ''), 'resume_audit', "将简历id为" . $val['id'] . "的简历审核状态设置为" . ($audit == 1 ? '审核通过' : '审核未通过') . '；备注：' . $reasona, false, array('resume_id' => $val['id']), $audit_man['id'], $audit_man['username']);
            }
            foreach ($id as $key => $value) {
                $this->admin_set_resume_entrust($value);
            }
            // distribution_resume($id);
            //发送站内信
            if ($pms_notice == '1') {
                foreach ($resume_list as $key => $value) {
                    $user_info = D('Members')->find($value['uid']);
                    $setsqlarr['message'] = $audit == '1' ? "您创建的简历：{$value['title']}（真实姓名：{$value['fullname']}）成功通过网站管理员审核！" : "您创建的简历：{$value['title']}（真实姓名：{$value['fullname']}）未通过网站管理员审核，原因：{$reasona}";
                    D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $setsqlarr['message'],2);
                }
            }
            foreach ($id as $list) {
                $auditsqlarr['resume_id'] = $list;
                $auditsqlarr['reason'] = $reasona;
                $auditsqlarr['status'] = $audit == 1 ? '审核通过' : '审核未通过';
                $auditsqlarr['addtime'] = time();
                $auditsqlarr['audit_man'] = $audit_man['username'] ? $audit_man['username'] : '未知';
                M('AuditReason')->data($auditsqlarr)->add();
            }
            //sms
            $sms = D('SmsConfig')->get_cache();
            if ($audit == "1" && $sms['set_resumeallow'] == "1") {
                $mobilearray = array();
                foreach ($resume_list as $key => $value) {
                    $usermobile = D('Members')->get_user_one(array('uid' => $value['uid']));
                    if (!is_array($value['mobile'], $mobilearray)) {
                        $mobilearray[] = $usermobile['mobile'];
                    }
                }
                if (!empty($mobilearray)) {
                    $mobilestr = implode(",", $mobilearray);
                    D('Sms')->sendSms('notice', array('mobile' => $mobilestr, 'tpl' => 'set_resumeallow'));
                }
            }
            if ($audit == "3" && $sms['set_resumenotallow'] == "1")//认证未通过
            {
                $mobilearray = array();
                foreach ($resume_list as $key => $value) {
                    $usermobile = D('Members')->get_user_one(array('uid' => $value['uid']));
                    if (!is_array($value['mobile'], $mobilearray)) {
                        $mobilearray[] = $usermobile['mobile'];
                    }
                }
                if (!empty($mobilearray)) {
                    $mobilestr = implode(",", $mobilearray);
                    D('Sms')->sendSms('notice', array('mobile' => $mobilestr, 'tpl' => 'set_resumenotallow'));
                }
            }
            //微信通知
            if (C('apply.Weixin')) {
                if ($audit == "1") {
                    foreach ($resume_list as $k => $v) {
                        D('Weixin/TplMsg')->set_resumeallow($v['uid'], $v['title'], '审核通过', $reasona);
                    }
                }
                if ($audit == "3") {
                    foreach ($resume_list as $k => $v) {
                        D('Weixin/TplMsg')->set_resumeallow($v['uid'], $v['title'], '审核未通过', $reasona);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function admin_edit_resume_photo_audit($id, $audit, $reason, $pms_notice, $audit_man) {
        !is_array($id) && $id = array($id);
        $sqlin = implode(",", $id);
        if (fieldRegex($sqlin, 'in')) {
            $reason = $reason == '' ? '未知' : $reason;
            $resume_list = $this->field('id,uid,photo_img,photo_display,fullname')->where(array('id' => array('in', $sqlin)))->select();
            foreach ($resume_list as $key => $val) {
                if ($val['photo_img'] && $audit == 1 && $val['photo_display'] == 1) {
                    $d['photo'] = 1;
                } else {
                    $d['photo'] = 0;
                }
                $d['photo_audit'] = $audit;
                if (true === D('Members')->update_user_info($d, array('uid' => $val['uid'], 'utype' => 2))) {
                    write_members_log(array('uid' => $val['uid'], 'utype' => 2, 'username' => ''), 'resume_audit', "将简历id为" . $val['id'] . "的简历头像审核状态设置为" . ($audit == 1 ? '审核通过' : '审核未通过') . '；备注：' . $reasona, false, array('resume_id' => $val['id']), $audit_man['id'], $audit_man['username']);
                    //站内信
                    if ($pms_notice == '1') {
                        $user_info = D('Members')->find($val['uid']);
                        $setsqlarr['message'] = $audit == '1' ? "你的简历头像成功通过网站管理员审核！" : "你的简历头像未通过网站管理员审核，原因：{$reason}";
                        D('Pms')->write_pmsnotice($user_info['uid'], $user_info['username'], $setsqlarr['message'],2);          
                    }
                    //sms
                    $sms = D('SmsConfig')->get_cache();
                    if ($audit == "1" && $sms['set_resume_photoallow'] == "1") {
                        $usermobile = D('Members')->get_user_one(array('uid' => $val['uid']));
                        if ($usermobile['mobile']) {
                            D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_resume_photoallow'));
                        }
                    }
                    if ($audit == "3" && $sms['set_resume_photonotallow'] == "1")//认证未通过
                    {
                        $usermobile = D('Members')->get_user_one(array('uid' => $val['uid']));
                        if ($usermobile['mobile']) {
                            D('Sms')->sendSms('notice', array('mobile' => $usermobile['mobile'], 'tpl' => 'set_resume_photonotallow'));
                        }
                    }
                    if (C('apply.Weixin')) {
                        D('Weixin/TplMsg')->set_resume_photoallow($val['uid'], $audit == 1 ? '审核通过' : '审核未通过', $reason);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 刷新简历
     */
    public function admin_refresh_resume($id) {
        $return = 0;
        $time = time();
        if (!is_array($id)) $id = array($id);
        $sqlin = implode(",", $id);
        if (fieldRegex($sqlin, 'in')) {
            $data = array('refreshtime' => $time, 'stime' => $time);
            $return = $this->where(array('id' => array('in', $sqlin)))->save($data);
            if (false === M('ResumeSearchPrecise')->where(array('id' => array('in', $sqlin)))->save($data)) return false;
            if (false === M('ResumeSearchFull')->where(array('id' => array('in', $sqlin)))->save($data)) return false;
            if ($rids = $this->where(array('id' => array('in', $sqlin), 'stick' => 1))->getfield('id', true)) {
                $data = array('stime' => $time + 100000000);
                if (false === $this->where(array('id' => array('in', $rids)))->save($data)) return false;
                if (false === M('ResumeSearchPrecise')->where(array('id' => array('in', $rids)))->save($data)) return false;
                if (false === M('ResumeSearchFull')->where(array('id' => array('in', $rids)))->save($data)) return false;
            }
            $return = $return === false ? 0 : $return;
        }
        return $return;
    }

    /**
     * 刷新简历
     */
    public function admin_refresh_resume_by_uid($uid) {
        $return = 0;
        $time = time();
        if (!is_array($uid)) $uid = array($uid);
        $sqlin = implode(",", $uid);
        if (fieldRegex($sqlin, 'in')) {
            $data = array('refreshtime' => $time, 'stime' => $time);
            $return = $this->where(array('uid' => array('in', $sqlin)))->save($data);
            if (false === M('ResumeSearchPrecise')->where(array('uid' => array('in', $sqlin)))->save($data)) return false;
            if (false === M('ResumeSearchFull')->where(array('uid' => array('in', $sqlin)))->save($data)) return false;
            if ($rids = $this->where(array('uid' => array('in', $sqlin), 'stick' => 1))->getfield('id', true)) {
                $data = array('stime' => $time + 100000000);
                if (false === $this->where(array('id' => array('in', $rids)))->save($data)) return false;
                if (false === M('ResumeSearchPrecise')->where(array('id' => array('in', $rids)))->save($data)) return false;
                if (false === M('ResumeSearchFull')->where(array('id' => array('in', $rids)))->save($data)) return false;
            }
            $return = $return === false ? 0 : $return;
        }
        return $return;
    }

    /**
     * 根据uid删除简历
     */
    public function admin_del_resume_for_uid($uid) {
        if (!is_array($uid)) $uid = array($uid);
        $sqlin = implode(",", $uid);
        $return = 0;
        if (fieldRegex($sqlin, 'in')) {
            $resumelist = $this->where(array('uid' => array('in', $sqlin)))->select();
            foreach ($resumelist as $key => $value) {
                $rid[] = $value['id'];
            }
            if (empty($rid)) {
                return true;
            } else {
                return $this->admin_del_resume($rid);
            }
        }
    }

    /**
     * 删除简历
     */
    public function admin_del_resume($id) {
        if (!is_array($id)) $id = array($id);
        $sqlin = implode(",", $id);
        $return = 0;
        if (fieldRegex($sqlin, 'in')) {
            $return = $this->where(array('id' => array('in', $sqlin)))->delete();
            if (false === M('ResumeEducation')->where(array('pid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeTraining')->where(array('pid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeWork')->where(array('pid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeCredent')->where(array('pid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeLanguage')->where(array('pid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeSearchPrecise')->where(array('id' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeSearchFull')->where(array('id' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ViewResume')->where(array('resumeid' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeEntrust')->where(array('resume_id' => array('in', $sqlin)))->delete()) return false;
            if (false === M('ResumeImg')->where(array('resume_id' => array('in', $sqlin)))->delete()) return false;
        }
        return $return;
    }

    /**
     * 设置简历委托
     */
    public function admin_set_resume_entrust($resume_id) {
        $resume = $this->field('audit,uid,fullname,addtime,entrust')->where(array('id' => $resume_id))->find();
        if ($resume["audit"] == "1" && $resume["entrust"] == "1") {
            $has = M('ResumeEntrust')->where(array('id' => $resume_id))->find();
            if (!$has) {
                $setsqlarr['id'] = $resume_id;
                $setsqlarr['uid'] = $resume['uid'];
                $setsqlarr['fullname'] = $resume['fullname'];
                $setsqlarr['resume_addtime'] = $resume['addtime'];
                M('ResumeEntrust')->data($setsqlarr)->add();
                $this->where(array('id' => $resume_id))->data(array("entrust" => 0))->save();
            }
        } else {
            M('ResumeEntrust')->where(array('id' => $resume_id))->delete();
        }
        return true;
    }

    /**
     * 保存word简历
     * 传值时注意：如果$id是数组，说明传值是did，需要先查出简历id；如果$id不是数组，那么$id就是简历id
     */
    public function save_as_doc_word($id, $mod, $user, $zip = 0) {
        if (is_array($id) && $mod)//如果是did
        {
            // 批量导出为word  先查询简历id
            $sqlin = implode(",", $id);
            if (!fieldRegex($sqlin, 'in')) return false;
            $idarr = $mod->where(array('did' => array('in', $sqlin)))->field('resume_id')->select();
            foreach ($idarr as $key => $value) {
                $idarr[$key] = $value['resume_id'];
            }
            $id = $idarr;
        } else//如果是简历id
        {
            $id = array($id);
        }
        $sqlin = implode(",", $id);
        if (!fieldRegex($sqlin, 'in')) return false;
        $result = $this->where(array('id' => array('in', $sqlin)))->select();
        if (!$result) {
            return false;
        }
        $list = array();
        foreach ($result as $n) {
            $val = $n;
            $val['education_list'] = D('ResumeEducation')->get_resume_education($val['id'], $val['uid']);
            $val['work_list'] = D('ResumeWork')->get_resume_work($val['id'], $val['uid']);
            $val['training_list'] = D('ResumeTraining')->get_resume_training($val['id'], $val['uid']);
            $val['project_list'] = D('ResumeProject')->get_resume_project($val['id'], $val['uid']);
            $val['age'] = date("Y") - $val['birthdate'];
            $val['tagcn'] = preg_replace("/\d+/", '', $val['tag']);
            $val['tagcn'] = preg_replace('/\,/', '', $val['tagcn']);
            $val['tagcn'] = preg_replace('/\|/', '&nbsp;&nbsp;&nbsp;', $val['tagcn']);

            // 最近登录时间
            $last_login_time = D('Members')->where(array('uid' => array('eq', $val['uid'])))->getField('last_login_time');
            $val['last_login_time'] = date('Y-m-d', $last_login_time);
            $down_resume = D('CompanyDownResume')->check_down_resume($val['id'], $user['uid']);
            if (!$down_resume) {
                if ($val['display_name'] == "2") {
                    $val['fullname'] = "N" . str_pad($val['id'], 7, "0", STR_PAD_LEFT);
                } elseif ($val['display_name'] == "3") {
                    if ($val['sex'] == 1) {
                        $val['fullname'] = cut_str($val['fullname'], 1, 0, "先生");
                    } elseif ($val['sex'] == 2) {
                        $val['fullname'] = cut_str($val['fullname'], 1, 0, "女士");
                    }
                }
            } else {
                $val['fullname'] = $val['fullname'];
            }
            $val['has_down'] = false;
            $val['is_apply'] = false;
            $val['label_id'] = 0;
            $val['show_contact'] = $this->_get_show_contact($val, $val['has_down'], $val['is_apply'], $val['label_id'], $user);
            if ($val['show_contact'] === false) {
                $val['telephone'] = contact_hide($val['telephone'], 2);
                $val['email'] = contact_hide($val['email'], 3);
            }
            $avatar_default = $val['sex'] == 1 ? 'no_photo_male.png' : 'no_photo_female.png';
            if ($val['photo'] == "1") {
                $val['photosrc'] =  attach($val['photo_img'], 'avatar');
            } else {
                $val['photosrc'] =  attach($avatar_default, 'resource');
            }
            $list[] = $val;
        }
        $controller = new \Common\Controller\BaseController;
        if ($zip) {
            $path = QSCMS_DATA_PATH . 'upload/resume_tmp/' . C('visitor.uid') . '/';
            if (is_dir($path)) {//如果目录已存在，先删掉，以防将之前的文档也打包
                rmdirs($path);
            }
            mkdir($path, 0777, true);
            foreach ($list as $key => $value) {
                $word = new \Common\qscmslib\word();
                $wordname = $value['fullname'] . "的个人简历.doc";
                $wordname = iconv("UTF-8", "GBK", $wordname);
                $html = $controller->assign_resume_tpl(array('list' => array($value)), 'Emailtpl/word_resume');
                echo $html;
                $word->save($path . $wordname);
            }
            $savename = '来自' . C('qscms_site_name') . '的简历.zip';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $filename = $path . iconv("UTF-8", "GBK", $savename);
            } else {
                $filename = $path . $savename;
            }
            $zip = new \Common\qscmslib\phpzip;
            $done = $zip->zip($path . '/', $filename);
            if ($done) {
                //写入会员日志
                foreach ($id as $k => $v) {
                    write_members_log($user, 'resume', '保存word简历（简历id：' . $v . '）', false, array('resume_id' => $v));
                }

                return array('zip' => 1, 'name' => $savename, 'dir' => 'resume_tmp/' . C('visitor.uid'), 'path' => $path);
                //
            }
        } else {
            $html = $controller->assign_resume_tpl(array('list' => $list), 'Emailtpl/word_resume');
            //写入会员日志
            foreach ($id as $k => $v) {
                write_members_log($user, 'resume', '保存word简历（简历id：' . $v . '）', false, array('resume_id' => $v));
            }
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-Type: application/doc");
            $ua = $_SERVER["HTTP_USER_AGENT"];
            $filename = "{$val['fullname']}的个人简历.doc";
            $filename = urlencode($filename);
            $filename = str_replace("+", "%20", $filename);
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }
            echo $html;
        }
    }

    /**
     * 是否显示联系方式
     */
    protected function _get_show_contact($val, &$down, &$apply, &$label_id, $user) {
        $show_contact = false;
        //情景1：游客访问
        if (!$user) {
            C('qscms_showresumecontact') == 0 && $show_contact = true;
        } //情景2：个人会员访问并且是该简历发布者
        else if ($user['utype'] == 2 && $user['uid'] == $val['uid']) {
            $show_contact = true;
        } //情景3：企业会员访问
        else if ($user['utype'] == 1) {
            //情景3-1：其他企业会员
            if (C('qscms_showresumecontact') == 1) {
                $show_contact = true;
            }
            //情景3-2：下载过该简历
            $down_resume = D('CompanyDownResume')->check_down_resume($val['id'], $user['uid']);
            if ($down_resume) {
                $label_id = $down_resume['is_reply'];
                $show_contact = true;
                $down = true;
            }
            //情景3-3：该简历申请过当前企业发布的职位
            $jobs_apply = D('PersonalJobsApply')->check_jobs_apply($val['id'], $user['uid']);
            /*  if($jobs_apply){
                $label_id = $jobs_apply['is_reply'];
                $show_contact = true;
                $apply = true;
            } */
            //情景3-3：该简历申请过当前企业发布的职位
            $setmeal = D('MembersSetmeal')->get_user_setmeal($user['uid']);
            if ($jobs_apply && $setmeal['show_apply_contact'] == '1') {
                $show_contact = true;
            }
        }
        return $show_contact;
    }

    /**
     * 标记简历
     */
    public function company_label_resume($did, $mod_name, $company_uid, $state) {
        if ($mod_name == 'PersonalJobsApply') {
            $data['personal_look'] = 2;
            $data['reply_time'] = time();
            $old_apply_info = D('PersonalJobsApply')->where(array('did' => $did, 'is_reply' => 0))->find();
        }
        $data['is_reply'] = $state;
        $num = D($mod_name)->where(array('did' => $did, 'company_uid' => $company_uid))->save($data);
        if (false === $num) {
            return array('state' => 0, 'msg' => '标记失败');
        } else {
            $user = D('Members')->get_user_one(array('uid' => $company_uid));
            $r = false;
            if ($mod_name == 'PersonalJobsApply') {
                $apply_info = D('PersonalJobsApply')->where(array('did' => $did))->find();
                //处理时间为3天内
                if ($old_apply_info && $data['reply_time'] - $apply_info['apply_addtime'] <= 3600 * 24 * 3) {
                    $userinfo = D('Members')->get_user_one(array('uid' => $company_uid));
                    $r = D('TaskLog')->do_task($userinfo, 'handle_resume');
                }
                //写入会员日志
                write_members_log($user, 'resume', '标记简历（记录id：' . $did . '）');
            } else {
                //写入会员日志
                write_members_log($user, 'resume', '标记简历（记录id：' . $did . '）');
            }
            return array('state' => 1, 'msg' => '标记成功', 'task' => $r);
        }
    }

    /**
     * 获取用户简历
     */
    public function get_user_resume($uid, $audit = 1) {
        $map['uid'] = $uid;
        $map['audit'] = $audit;
        return $this->where($map)->getField('id,title');
    }

    /**
     * 完善简历送红包
     */
    public function perfected_resume_allowance($complete_percent, $userinfo) {
        if (C('qscms_perfected_resume_allowance_open') == 1 && C('qscms_perfected_resume_allowance_percent') > 0 && $complete_percent >= C('qscms_perfected_resume_allowance_percent')) {
            $perfected_info = M('MembersPerfectedAllowance')->where(array('uid' => $userinfo['uid']))->find();
            if ($perfected_info && $perfected_info['status'] == 0) {
                $userbind = D('MembersBind')->get_members_bind(array('uid' => $userinfo['uid'], 'type' => 'weixin'));
                if (!$userbind) {
                    $perfected_info['nobind'] == 0 && M('MembersPerfectedAllowance')->where(array('uid' => $userinfo['uid']))->save(array('nobind' => 1, 'reason' => '未绑定微信'));
                    if ($perfected_info['notice'] == 1) {
                        if (strtolower(MODULE_NAME) == 'home') {
                            return array('status' => 2, 'msg' => '未绑定微信', 'data' => $perfected_info['value']);
                        } else {
                            return array('status' => 0, 'msg' => '未绑定微信', 'data' => $perfected_info['value']);
                        }
                    } else {
                        return array('status' => 0, 'msg' => '未绑定微信', 'data' => $perfected_info['value']);
                    }
                } else {
                    $perfected_info['nobind'] == 1 && M('MembersPerfectedAllowance')->where(array('uid' => $userinfo['uid']))->save(array('nobind' => 0));
                }
                include QSCMSLIB_PATH . "pay/wxpay/wxpay.class.php";
                $pay_type = D('Common/Payment')->get_cache();
                $setting = $pay_type['wxpay'];
                $payObj = new \wxpay_pay($setting);
                $data['openid'] = $userbind['openid'];
                $data['partner_trade_no'] = 'PraUid' . $userinfo['uid'] . 'T' . time();
                $data['amount'] = $perfected_info['value'];
                $result = $payObj->payment($data);
                if ($result) {
                    M('MembersPerfectedAllowance')->where(array('uid' => $userinfo['uid']))->save(array('status' => 1, 'reason' => '', 'notice' => 0));
                    return array('status' => 1, 'msg' => '红包已发放', 'data' => $data['amount']);
                } else {
                    M('MembersPerfectedAllowance')->where(array('uid' => $userinfo['uid']))->save(array('status' => 0, 'reason' => $payObj->getError()));
                    return array('status' => 0, 'msg' => $payObj->getError(), 'data' => '');
                }
            } else {
                return array('status' => 0, 'msg' => '不通知', 'data' => '');
            }
        } else {
            return array('status' => 0, 'msg' => '未开启', 'data' => '');
        }
    }
}

?>