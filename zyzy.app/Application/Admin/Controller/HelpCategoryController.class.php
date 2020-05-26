<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class HelpCategoryController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('HelpCategory');
    }
    public function index(){
        $pid = I('get.pid',0,'intval');
        $where['parentid'] = $pid;
        $list = $this->_mod->where($where)->order('category_order desc,id')->select();
        if(IS_AJAX) $this->ajaxReturn(1,'分类获取成功！',$list);
        $this->assign('list', $list);
        $this->display();
    }
    public function add(){
        $this->_name = 'HelpCategory';
        if(IS_POST){
            $parentid=I("post.parentid");
            $categoryname=I("post.categoryname");
            $category_order=I("post.category_order");
            //新增的入库
            $num = 0;
            if (is_array($categoryname) && count($categoryname)>0)
            {
                for ($i =0; $i <count($categoryname);$i++){
                    if (!empty($categoryname[$i]))
                    {   
                        $setsqlarr['categoryname']=trim($categoryname[$i]);
                        $setsqlarr['category_order']=intval($category_order[$i]);
                        $setsqlarr['parentid']=intval($parentid[$i]); 
                        $m = $this->_mod->create($setsqlarr);
                        if($m){
                            $r = $this->_mod->add();
                            $r && $num++;
                        }
                    }
                }
            }
            $this->returnMsg(1,'添加成功！本次添加了'.$num.'个帮助分类');
        }else{
            $this->_before_add();
            $this->display();
        }
    }
    public function _before_add(){
        $help_category = $this->_mod->category_cache();
        $this->assign('help_category',$help_category);
    }
    public function _before_edit(){
        $this->_before_add();
    }
    public function allSave(){
        $save_id = I('post.save_id');
        $name = I('post.categoryname');
        $ordid = I('post.category_order');
        $add_pid = I('post.add_pid');
        $add_name = I('post.add_name');
        $add_ordid = I('post.add_ordid');
        $num = 0;
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                $setsqlarr['categoryname']=trim($name[$k]);
                $setsqlarr['category_order']=intval($ordid[$k]);
                $m = $this->_mod->create($setsqlarr);
                if($m){
                    if (false !== $this->_mod->where(array('id'=>array('eq',intval($save_id[$k]))))->save()) {
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
                    $setsqlarr['categoryname']=trim($add_name[$i]);
                    $setsqlarr['category_order']=intval($add_ordid[$i]);
                    $setsqlarr['parentid']=intval($add_pid[$i]);   
                    $m = $this->_mod->create($setsqlarr);
                    if($m){
                        if($id = $this->_mod->add()){
                            $num++;
                        }
                    }
                }
            }
        }
        $this->success('成功保存'.$num.'个分类！');
    }
}
?>