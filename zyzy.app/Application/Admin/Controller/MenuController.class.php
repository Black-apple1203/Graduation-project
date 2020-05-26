<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class MenuController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Menu');
    }
    public function index(){
        $pid = I('get.pid',0,'intval');
        $type = I('get.type',0,'intval');
        $where['pid'] = $pid;
        $type && $where['menu_type'] = 1;
        $menu_list = $this->_mod->field('id,name,controller_name,action_name,ordid')->where($where)->order('ordid,id')->select();
        if(IS_AJAX) $this->ajaxReturn(1,'菜单获取成功！',$menu_list);
        $this->assign('menu_list', $menu_list);
        $this->display();
    }
    /**
     * 批量保存地区分类
     */
    public function menuAllSave(){
        $py = new \Common\qscmslib\pinyin;
        $save_id = I('post.save_id');
        $name = I('post.name');
        $ordid = I('post.ordid');
        $add_pid = I('post.add_pid');
        $add_name = I('post.add_name');
        $add_ordid = I('post.add_ordid');
        $num = 0;
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                $setsqlarr['name']=trim($name[$k]);
                $setsqlarr['ordid']=intval($ordid[$k]);
                $m = $this->_mod->create($setsqlarr);
                if($m){
                    if (method_exists($this, '_before_update')) {
                        $setsqlarr = $this->_before_update($setsqlarr);
                    }
                    if (false !== $this->_mod->where(array('id'=>array('eq',intval($save_id[$k]))))->save($setsqlarr)) {
                        if(method_exists($this, '_after_update')){
                            $id = $setsqlarr['id'];
                            $this->_after_update($id,$setsqlarr);
                        }
                        $num++;
                    }
                }
            }
        }
        //新增的入库
        if (is_array($add_pid) && count($add_pid)>0)
        {
            for ($i =0; $i <count($add_pid);$i++){
                if (!empty($add_name[$i]))
                {   
                    $setsqlarr['name']=trim($add_name[$i]);
                    $setsqlarr['ordid']=intval($add_ordid[$i]);
                    $setsqlarr['pid']=intval($add_pid[$i]);   
                    $m = $this->_mod->create($setsqlarr);
                    if($m){
                        if(method_exists($this,'_before_insert')) {
                            $setsqlarr = $this->_before_insert($setsqlarr);
                        }
                        if($id = $this->_mod->add()){
                            if(method_exists($this,'_after_insert')){
                                $this->_after_insert($id,$setsqlarr);
                            }
                            $num++;
                        }
                    }
                }
            }
        }
        $this->success('成功保存'.$num.'个分类！');
    }
    public function _before_add(){
        if(!IS_POST){
            if(false === $menus = F('menu_list')){
                $menus = $this->_mod->menu_cache();
            }
            $this->assign('menus',$menus);
            $this->assign('pid',I('get.pid',0,'intval'));
        }
    }
    public function _after_insert($id,$data){
        $spid = '';
        if($data['pid']){
            $spid = $this->_mod->where(array('id'=>$data['pid']))->getfield('spid');
        }
        $spid.=$id.'|';
        $this->_mod->where(array('id'=>$id))->setfield('spid',$spid);
    }
    public function _after_update($id,$data){
        $this->_after_insert($id,$data);
    }
    public function _before_edit(){
        $this->_before_add();
    }
}