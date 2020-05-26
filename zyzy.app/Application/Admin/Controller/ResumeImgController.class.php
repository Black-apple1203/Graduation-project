<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ResumeImgController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('ResumeImg');
    }

    public function index(){
        $db_pre = C('DB_PREFIX');
        $this_t = $db_pre.'resume_img';
    	$key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        if($key_type && $key){
            switch ($key_type){
                case 1:
                    $where['r.title'] = array('like','%'.$key.'%');
                    break;
                case 2:
                    $where['r.fullname'] = array('like','%'.$key.'%');
                    break;
                case 3:
                    $where['resume_id'] = intval($key);
                    break;
                case 4:
                    $where['title'] = array('like','%'.$key.'%');
                    break;
            }
        }else{
            if($settr=I('get.settr',0,'intval')){
                $where[$this_t.'.addtime']=array('gt',strtotime("-".$settr." day"));
            }
        }
        unset($where['audit']);
        if('' != $audit = I('request.audit')){
            $where[$this_t.'.audit'] = $audit;
        }
        $this->where = $where;
        $this->join = $db_pre .'resume as r on r.id='.$this_t.'.resume_id';
        $this->field = $this_t.'.*,r.title as resume_name,fullname';
        $this->order ='field('. $this_t.'.audit,2) desc ,id desc';
        $this->custom_fun = '_format_resume_list';
        $this->pagesize = 16;
        parent::index();
    }
    /**
     * [_format_resume_list 解析简历跳转链接(简历列表页用)]
     */
    protected function _format_resume_list($list){
        foreach ($list as $key => $val) {
            $list[$key]['resume_url'] = url_rewrite('QS_resumeshow',array('id'=>$val['resume_id']));
        }
        return $list;
    }
    public function set_audit(){
    	$id = I('request.id');
        if(!$id) $this->error('请选择图片');
        $audit = I('post.audit',0,'intval');
        $pms_notice = I('post.pms_notice',0,'intval');
        $reason = I('post.reason','','trim');
        $result = $this->_mod->set_audit($id,$audit,$reason,$pms_notice);
        if($result){
            $this->success("设置成功！");
        }else{
            $this->error('设置失败！');
        }
    }
}
?>