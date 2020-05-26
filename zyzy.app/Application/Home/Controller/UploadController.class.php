<?php
namespace Home\Controller;

use Common\Controller\FrontendController;
use Common\ORG\qiniu;

class UploadController extends FrontendController {
    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'), '', 1);
            //非ajax的跳转页面
            $this->redirect('members/login');
        }
    }

    /**
     * [attach 附件上传]
     * @return [type] [description]
     */
    public function attach() {
        if (IS_POST) {
            $type = I('post.type', 'image', 'trim');
            if (!in_array($type, array('subject_message_img','top_resume_img', 'resume_img', 'top_word_resume', 'word_resume', 'company_logo', 'company_img', 'certificate_img'))) return false;
            if($type == 'certificate_img'){
                if(($_FILES['certificate_img']['size']/1024)>C('qscms_certificate_max_size')){
                    $this->ajaxReturn(0, '图片大小超出最大限制');
                }
            }
            if (!empty($_FILES[$type]['name'])) {
                $this->$type();
            } else {
                $this->ajaxReturn(0, L('illegal_parameters'));
            }
        }
    }

    /**
     * [resume_img 个人简历图片上传]
     * @return [type] [description]
     */
    protected function resume_img() {
        $pid = I('post.pid', 0, 'intval');
        $config_params = array(
            'upload_ok' => false,
            'img_url'   => '',
            'img_path'  => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_resume_photo_max'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'resume_img');
            if ($img_url) {
                $size = explode(',', C('qscms_resume_img_size'));
                foreach ($size as $val) {
                    $thumb_name = $qiniu->getThumbName($img_url, $val, $val);
                    $qiniu->upload($_FILES, 'resume_img', $thumb_name, $val, $val, true);
                }
                $config_params['upload_ok'] = true;
                $config_params['img_url'] = $img_url;
                $config_params['img_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['resume_img'], 'resume_img/' . $date, array(
                'maxSize'       => C('qscms_resume_photo_max'),//图片大小上限
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ));
            if ($result['error']) {
                $image = new \Common\ORG\ThinkImage();
                $path = $result['info'][0]['savepath'] . $result['info'][0]['savename'];
                $size = explode(',', C('qscms_resume_img_size'));
                foreach ($size as $val) {
                    $image->open($path)->thumb($val, $val, 3)->save("{$path}_{$val}x{$val}.jpg");
                }
                $config_params['upload_ok'] = true;
                $config_params['img_url'] = $date . $result['info'][0]['savename'];
                $config_params['img_path'] = attach($config_params['img_url'], 'resume_img');
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            $img_mod = M('ResumeImg');
            $setsqlarr['resume_id'] = $pid;
            $setsqlarr['uid'] = C('visitor.uid');
            $setsqlarr['title'] = '';
            $setsqlarr['img'] = $config_params['img_url'];
            $setsqlarr['id'] = I('post.id', 0, 'intval');
            if ($setsqlarr['id'] == 0) {
                $count = M('ResumeImg')->where(array('resume_id' => $pid, 'uid' => C('visitor.uid')))->count('id');
                if ($count >= 6) {
                    $this->ajaxReturn(0, '简历附件最多只可上传6张！');
                    exit;
                }
            }
            $rst = D('ResumeImg')->save_resume_img($setsqlarr);
            $data = array('path' => $config_params['img_path'], 'img' => $config_params['img_url'], 'id' => $rst['id']);
            $this->ajaxReturn(1, L('upload_success'), $data, '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    /**
     * [resume_img 个人高级简历图片上传]
     * @return [type] [description]
     */
    protected function top_resume_img() {
        $pid = I('post.pid', 0, 'intval');
        $config_params = array(
            'upload_ok' => false,
            'img_url'   => '',
            'img_path'  => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_resume_photo_max'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'top_resume_img');
            if ($img_url) {
                $size = explode(',', C('qscms_resume_img_size'));
                foreach ($size as $val) {
                    $thumb_name = $qiniu->getThumbName($img_url, $val, $val);
                    $qiniu->upload($_FILES, 'top_resume_img', $thumb_name, $val, $val, true);
                }
                $config_params['upload_ok'] = true;
                $config_params['img_url'] = $img_url;
                $config_params['img_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['top_resume_img'], 'top_resume_img/' . $date, array(
                'maxSize'       => C('qscms_resume_photo_max'),//图片大小上限
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ));
            if ($result['error']) {
                $image = new \Common\ORG\ThinkImage();
                $path = $result['info'][0]['savepath'] . $result['info'][0]['savename'];
                $size = explode(',', C('qscms_resume_img_size'));
                foreach ($size as $val) {
                    $image->open($path)->thumb($val, $val, 3)->save("{$path}_{$val}x{$val}.jpg");
                }
                $config_params['upload_ok'] = true;
                $config_params['img_url'] = $date . $result['info'][0]['savename'];
                $config_params['img_path'] = attach($config_params['img_url'], 'top_resume_img');
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            $img_mod = M('AdvResumeImg');
            $setsqlarr['resume_id'] = $pid;
            $setsqlarr['uid'] = C('visitor.uid');
            $setsqlarr['title'] = '';
            $setsqlarr['img'] = $config_params['img_url'];
            $setsqlarr['id'] = I('post.id', 0, 'intval');
            if ($setsqlarr['id'] == 0) {
                $count = M('AdvResumeImg')->where(array('resume_id' => $pid, 'uid' => C('visitor.uid')))->count('id');
                if ($count >= 6) {
                    $this->ajaxReturn(0, '简历附件最多只可上传6张！');
                    exit;
                }
            }
            $rst = D('AdvResumeImg')->save_resume_img($setsqlarr);
            $data = array('path' => $config_params['img_path'], 'img' => $config_params['img_url'], 'id' => $rst['id']);
            $this->ajaxReturn(1, L('upload_success'), $data, '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    /**
     * [word_resume 上传高级word简历]
     * @return [type] [description]
     */
    protected function top_word_resume() {
        $pid = I('post.pid', 0, 'intval');
        $config_params = array(
            'upload_ok'         => false,
            'word_resume_title' => '',
            'save_path'         => '',
            'show_path'         => '',
            'info'              => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => 2 * 1024,
                'exts'    => 'doc,docx'
            ));
            $word_url = $qiniu->upload($_FILES, 'top_word_resume');
            if ($word_url) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $word_url;
                $config_params['show_path'] = $word_url;
                $config_params['info'] = '';
                $config_params['word_resume_title'] = badword($_FILES['top_word_resume']['name']);
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['top_word_resume'], 'top_word_resume/' . $date, array(
                'maxSize'       => 2 * 1024,//word最大2M
                'uploadReplace' => true,
                'attach_exts'   => 'doc,docx'
            ));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $date . $result['info'][0]['savename'];
                $config_params['show_path'] = U('download/adv_word_resume',array('id'=>$pid));
                $config_params['info'] = '';
                $config_params['word_resume_title'] = badword($_FILES['top_word_resume']['name']);
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            $resume_mod = M('AdvResume');
            $where = array('id' => $pid, 'uid' => C('visitor.uid'));
            if (!$word = $resume_mod->where($where)->getfield('word_resume')) $this->ajaxReturn(0, '简历不存在或已经删除！');
            $save_arr['word_resume'] = $config_params['save_path'];
            $save_arr['word_resume_title'] = _I($config_params['word_resume_title']);
            $save_arr['word_resume_addtime'] = time();
            $rid = $resume_mod->where($where)->save($save_arr);
            D('AdvResume')->save_resume('', $pid, C('visitor'));
            @unlink(C('qscms_attach_path') . "top_word_resume/" . $word);
            if (C('qscms_qiniu_open') == 1) {
                $qiniu->delete($word);
            }
            $this->ajaxReturn(1, L('upload_success'), array('name' => $save_arr['word_resume_title'], 'path' => $config_params['show_path'], 'time' => date('Y-m-d H:i', time())), '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    /**
     * [word_resume 上传word简历]
     * @return [type] [description]
     */
    protected function word_resume() {
        $pid = I('post.pid', 0, 'intval');
        $config_params = array(
            'upload_ok'         => false,
            'word_resume_title' => '',
            'save_path'         => '',
            'show_path'         => '',
            'info'              => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => 2 * 1024,
                'exts'    => 'doc,docx,pdf'
            ));
            $word_url = $qiniu->upload($_FILES, 'word_resume');
            if ($word_url) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $word_url;
                $config_params['show_path'] = $word_url;
                $config_params['info'] = '';
                $config_params['word_resume_title'] = badword($_FILES['word_resume']['name']);
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['word_resume'], 'word_resume/' . $date, array(
                'maxSize'       => 2 * 1024,//word最大2M
                'uploadReplace' => true,
                'attach_exts'   => 'doc,docx,pdf'
            ));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $date . $result['info'][0]['savename'];
                $config_params['show_path'] = U('download/word_resume',array('id'=>$pid));
                $config_params['info'] = '';
                $config_params['word_resume_title'] = badword($_FILES['word_resume']['name']);
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            $resume_mod = M('Resume');
            $where = array('id' => $pid, 'uid' => C('visitor.uid'));
            if (false === $word = $resume_mod->where($where)->getfield('word_resume')) $this->ajaxReturn(0, '简历不存在或已经删除！');
            $save_arr['word_resume'] = $config_params['save_path'];
            $save_arr['word_resume_title'] = _I($config_params['word_resume_title']);
            $save_arr['word_resume_addtime'] = time();
            $rid = $resume_mod->where($where)->save($save_arr);
            D('Resume')->save_resume('', $pid, C('visitor'));
            @unlink(C('qscms_attach_path') . "word_resume/" . $word);
            if (C('qscms_qiniu_open') == 1) {
                $qiniu->delete($word);
            }
            $this->ajaxReturn(1, L('upload_success'), array('name' => $save_arr['word_resume_title'], 'path' => $config_params['show_path'], 'time' => date('Y-m-d H:i', time())), '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    /**
     * [company_logo 企业logo]
     */
    protected function company_logo() {
        $company_id = I('post.company_id', 0, 'intval');
        $config_params = array(
            'upload_ok' => false,
            'path'      => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_logo_max_size'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'company_logo');
            if ($img_url) {
                $config_params['upload_ok'] = true;
                $config_params['path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
            if ($config_params['upload_ok']) {
                $rst = M('CompanyProfile')->where(array('id' => $company_id, 'uid' => C('visitor.uid')))->setfield('logo', $config_params['path']);
                $r = D('TaskLog')->do_task(C('visitor'), 'upload_logo');
                $this->ajaxReturn(1, L('upload_success'), array('path' => $config_params['path'], 'points' => $r['data']), '', 'HTML');
            } else {
                $this->ajaxReturn(0, $config_params['info']);
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['company_logo'], 'company_logo/' . $date, array(
                'maxSize'       => C('qscms_logo_max_size'),
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ), md5($company_id));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['path'] = attach($date . $result['info'][0]['savename'], 'company_logo') . '?' . time();
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $result['info'];
            }
            if ($config_params['upload_ok']) {
                $rst = M('CompanyProfile')->where(array('id' => $company_id, 'uid' => C('visitor.uid')))->setfield('logo', $date . $result['info'][0]['savename']);
                $im = new \Common\qscmslib\im();
                $im->refresh(C('visitor.uid'));
                $r = D('TaskLog')->do_task(C('visitor'), 'upload_logo');
                $this->ajaxReturn(1, L('upload_success'), array('path' => $config_params['path'], 'points' => $r['data']), '', 'HTML');
            } else {
                $this->ajaxReturn(0, $config_params['info']);
            }
        }

    }

    protected function company_img() {
        $company_id = I('post.company_id', 0, 'intval');
        $num = M('CompanyImg')->where(array('company_id' => $company_id, 'uid' => C('visitor.uid')))->count();
        if ($num >= 8) $this->ajaxReturn(0, '企业风采不能超过8张！');
        $config_params = array(
            'upload_ok' => false,
            'save_path' => '',
            'show_path' => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_company_img_max'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'company_img');
            if ($img_url) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $img_url;
                $config_params['show_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['company_img'], 'company_img/' . $date, array(
                'maxSize'       => C('qscms_company_img_max'),
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $date . $result['info'][0]['savename'];
                $config_params['show_path'] = attach($config_params['save_path'], 'company_img');
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            $setsqlarr['uid'] = C('visitor.uid');
            $setsqlarr['company_id'] = $company_id;
            $setsqlarr['img'] = $config_params['save_path'];
            $rst = D('CompanyImg')->add_company_img($setsqlarr, C('visitor'));
            !$rst['state'] && $this->ajaxReturn(0, $rst['error']);
            $r = D('TaskLog')->do_task(C('visitor'), 'upload_companyimg');
            $data = array('path' => $config_params['show_path'], 'date' => $rst['date'], 'id' => $rst['id'], 'deleteUrl' => U('company/del_company_img', array('id' => $rst['id'])), 'remarkUrl' => U('company/set_company_img_title', array('id' => $rst['id'])), 'points' => $r['data']);
            $this->ajaxReturn(1, L('upload_success'), $data, '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    // 企业营业执照 上传
    protected function certificate_img() {
        $config_params = array(
            'upload_ok' => false,
            'save_path' => '',
            'show_path' => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_certificate_max_size'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'certificate_img');
            if ($img_url) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $img_url;
                $config_params['show_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['certificate_img'], 'certificate_img/' . $date, array(
                'maxSize'       => C('qscms_certificate_max_size'),
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ), md5(C('visitor.uid')));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $date . $result['info'][0]['savename'];
                $config_params['show_path'] = attach($config_params['save_path'], 'certificate_img');
                $config_params['info'] = '';

            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
		$data['certificate_img'] = $config_params['save_path'];
            $data['audit'] = 2;
            $rst = D('CompanyProfile')->add_certificate_img($data, C('visitor'));
        	!$rst['state'] && $this->ajaxReturn(0,$rst['error']);
            $data['img'] = $config_params['save_path'];
            $data['url'] = $config_params['show_path'];
            $this->ajaxReturn(1, L('upload_success'), $data, '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }

    /**
     * [avatar 头像上传保存]
     */
    public function avatar() {
        $config_params = array(
            'upload_ok' => false,
            'save_path' => '',
            'show_path' => '',
            'info'      => ''
        );
        $uid = C('visitor.uid');
        $savePicName = md5($uid . time());
        $pic = base64_decode($_POST['pic1']);
        $size = explode(',', C('qscms_avatar_size'));
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'stream' => true
            ));
            $img_url = $qiniu->uploadStream($pic, $savePicName . ".jpg");
            if ($img_url) {
                foreach ($size as $val) {
                    $thumb_name = $qiniu->getThumbName($img_url, $val, $val);
                    $qiniu->uploadStream($pic, $thumb_name, $val, $val, true);
                }
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $img_url;
                $config_params['show_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            //日期路径
            $date = date('ym/d/');
            $save_avatar = C('qscms_attach_path') . 'avatar/' . $date;//图片存储路径
            if (!is_dir($save_avatar)) {
                mkdir($save_avatar, 0777, true);
            }
            $filename = $save_avatar . $savePicName . ".jpg";
            file_put_contents($filename, $pic);
            $image = new \Common\ORG\ThinkImage();
            foreach ($size as $val) {
                $image->open($filename)->thumb($val, $val, 3)->save($filename . "_" . $val . "x" . $val . ".jpg");
            }
            $config_params['upload_ok'] = true;
            $config_params['save_path'] = $date . $savePicName . ".jpg";
            $config_params['show_path'] = $savePicName . ".jpg";
            $config_params['info'] = '';
        }
        if ($config_params['upload_ok']) {
            $setsqlarr['avatars'] = $config_params['save_path'];
            $setsqlarr['photo'] = 0;
            $setsqlarr['photo_audit'] = 2;
            if (true !== $reg = D('Members')->update_user_info($setsqlarr, C('visitor'))) $this->ajaxReturn(0, $reg);
            $user_resume_list = D('Resume')->where(array('uid' => $uid))->select();
            foreach ($user_resume_list as $key => $value) {
                D('Resume')->check_resume($uid, $value['id']);//更新简历完成状态
            }
            $im = new \Common\qscmslib\im();
            $im->refresh($uid);
            D('TaskLog')->do_task(C('visitor'), 'upload_avatar');
            write_members_log(C('visitor'), '', '上传头像');
            $rs['status'] = 1;
            $rs['picUrl'] = $config_params['show_path'];
            print json_encode($rs);
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }
    }
    /**
     * [subject_message_img 网络招聘会聊天图片]
     */
    protected function subject_message_img() {
        $subject_id = I('post.id', 0, 'intval');
        $config_params = array(
            'upload_ok' => false,
            'save_path' => '',
            'show_path' => '',
            'info'      => ''
        );
        //如果开启七牛云，执行七牛云接口，否则执行系统内置程序
        if (C('qscms_qiniu_open') == 1) {
            $qiniu = new qiniu(array(
                'maxSize' => C('qscms_company_img_max'),
                'exts'    => 'bmp,png,gif,jpeg,jpg'
            ));
            $img_url = $qiniu->upload($_FILES, 'subject_message_img');
            if ($img_url) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $img_url;
                $config_params['show_path'] = $img_url;
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $qiniu->getError();
            }
        } else {
            $date = date('ym/d/');
            $result = $this->_upload($_FILES['subject_message_img'], 'subject_message_img/' . $date, array(
                'maxSize'       => C('qscms_company_img_max'),
                'uploadReplace' => true,
                'attach_exts'   => 'bmp,png,gif,jpeg,jpg'
            ));
            if ($result['error']) {
                $config_params['upload_ok'] = true;
                $config_params['save_path'] = $date . $result['info'][0]['savename'];
                $config_params['show_path'] = attach($config_params['save_path'], 'subject_message_img');
                $config_params['info'] = '';
            } else {
                $config_params['info'] = $result['info'];
            }
        }
        if ($config_params['upload_ok']) {
            if(C('visitor.utype') == 1){
                $company = M('CompanyProfile')->where(array('uid'=>C('visitor.uid')))->find();
                !$company && $this->ajaxReturn(0,'请先去完善企业资料呦~！');
                $arr['name'] = $company['companyname'];
                if($company['logo']){
                   $arr['head_img'] = $company['logo']; 
                }
            }else{
                $resume = M('Resume')->where(array('uid'=>C('visitor.uid')))->find();
                !$resume && $this->ajaxReturn(0,'请先去创建简历呦~！');
                $arr['name'] = $resume['fullname'];
                if($resume['photo_img']){
                   $arr['head_img'] = $resume['photo_img']; 
                }
                $arr['sex'] =$resume['sex'];
            }
            $arr['s_id'] = $subject_id;
            $arr['note_img'] = $config_params['save_path'];
            $arr['uid'] = C('visitor.uid');
            $arr['utype'] = C('visitor.utype');
            $arr['addtime'] = time();
            M('SubjectMessageLog')->add($arr);
            $data = array('path' => $config_params['show_path']);
            $this->ajaxReturn(1, L('upload_success'), $data, '', 'HTML');
        } else {
            $this->ajaxReturn(0, $config_params['info']);
        }

    }
}

?>