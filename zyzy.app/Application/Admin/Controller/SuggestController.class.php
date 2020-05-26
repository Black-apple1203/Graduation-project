<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class SuggestController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * [index 新增反馈]
     */
    public function add(){
        if(IS_POST){
            $data = I('post.');
            $data['domain'] = $_SERVER['HTTP_HOST'];
            $data['imgs'] =serialize($data['imgs']);
            //$data['imgs'] =json_encode($data['imgs']);//&& $data['imgs'] = serialize($data['imgs']);
            $result = https_request('https://www.74cms.com/plus/feedback_add',$data);
            //var_dump($result);die;
            $result = json_decode($result,1);
            $this->ajaxReturn($result['state'],$result['msg']);
        }else{
            $html = $this->fetch('ajax_suggest_add');
            $this->ajaxReturn(1,'',$html);
        }
    }
    /**
     * [index 反馈列表]
     */
    public function index(){
        $data['domain'] = $_SERVER['HTTP_HOST'];
        $data['page'] = I('get.page',0,'intval');
        $result = https_request('https://www.74cms.com/plus/feedback_list',$data);
        /*$result = https_request('http://www.74cms.com/index.php?m=home&c=plus&a=feedback_list&page='.$page.'&domain='.urlencode($_SERVER['HTTP_HOST']));*/
        $result = json_decode($result,1);
        foreach($result['data']['list'] as $key=>$val){
            if(70 < mb_strlen($val['feedback'], 'utf-8')){
                $list['_feedback'] = cut_str($val['feedback'],70,0,'...');
                $list['unfold'] = 1;
            }
            $list[] =$val;
        }
        $pager =  pager($result['data']['count'],10);
        $page_html = $pager->fshow();
        $this->assign('page_html',$page_html);
        //dump($list);
        $this->assign('list',$list);
        $html = $this->fetch('ajax_suggest_list');
        $this->ajaxReturn(1,'',$html);
    }
}
?>