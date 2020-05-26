<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CategoryController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Category');
    }
    public function group_list(){
        $this->assign('group',D('CategoryGroup')->select());
        $this->display();
    }
    public function group_add(){
        if(IS_POST){
            $setsqlarr['g_name']=I('post.g_name','','trim') ?I('post.g_name','','trim') : $this->error("请填写分组名");
            $setsqlarr['g_alias']=I('post.g_alias','','trim') ?I('post.g_alias','','trim') : $this->error("请填写调用名");
            $info=D('CategoryGroup')->where(array('g_alias'=>$setsqlarr['g_alias']))->find();
            if (!$info)
            {
                if (stripos($setsqlarr['g_alias'],"qs_")===0)
                {
                    $this->error("调用名不能用“qs_”开头");
                }
                else
                {
                    $insert_id = D('CategoryGroup')->add($setsqlarr);
                    if($insert_id){
                        $this->success('添加成功！');exit;
                    }else{
                        $this->error('添加失败！');
                    }
                }
            }
            else
            {
                $this->error("添加失败,调用名有重复");
            }
        }
        $this->display();
    }
    public function group_edit(){
        if(IS_POST){
            $setsqlarr['g_name']=I('post.g_name','','trim') ?I('post.g_name','','trim') : $this->error("请填写分组名");
            $setsqlarr['g_alias']=I('post.g_alias','','trim') ?I('post.g_alias','','trim') : $this->error("请填写调用名");
            $info=D('CategoryGroup')->where(array('g_alias'=>$setsqlarr['g_alias']))->find();
            if (!$info || $info['g_id']==I('post.g_id',0,'intval'))
            {
                if (stripos($setsqlarr['g_alias'],"qs_")===0)
                {
                    $this->error("调用名不能用“qs_”开头");
                }
                else
                {
                    $r = D('CategoryGroup')->where(array('g_id'=>I('post.g_id',0,'intval')))->save($setsqlarr);
                    if($r){
                        //同时修改分类组下的分类别名
                        $catarr['c_alias']=$setsqlarr['g_alias'];
                        $this->_mod->where(array('c_alias'=>I('post.old_g_alias')))->save($catarr);
                        $this->success('保存成功！');exit;
                    }else{
                        $this->error('保存失败！');
                    }
                }
            }
            else
            {
                $this->error("保存失败,调用名有重复");
            }
        }
        $this->assign('info',D('CategoryGroup')->where(array('g_alias'=>I('get.alias','','trim')))->find());
        $this->display();
    }
    public function group_delete(){
        $alias = I('request.alias');
        if(!is_array($alias)) $alias=array($alias);
        $return=0;
        foreach($alias as $a)
        {
            $map['g_alias'] = array('eq',trim($a));
            $map['g_sys'] = array('neq',1);
            $return = D('CategoryGroup')->where($map)->delete();
            $num = $this->_mod->where(array('c_alias'=>trim($a)))->delete();
            $return=$return+$num;
        }
        $this->success("成功删除分类 , 共删除".$return."行！");
    }
    /**
     * 分类
     */
    public function show_category(){
        $alias = I('get.alias','','trim');
        $this->assign('group',D('CategoryGroup')->where(array('g_alias'=>$alias))->find());
        $this->assign('category',$this->_mod->where(array('c_alias'=>$alias))->order('c_order desc,c_id')->select());   
        $this->display('index');
    }
    /**
     * 批量保存分类
     */
    public function categoryAllSave(){
        $save_id = I('post.save_id');
        $c_name = I('post.c_name');
        $c_order = I('post.c_order');
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                $setsqlarr['c_name']=trim($c_name[$k]);
                $setsqlarr['c_order']=intval($c_order[$k]);
                $this->_mod->where(array('c_id'=>array('eq',intval($save_id[$k]))))->save($setsqlarr);
            }
        }
        $this->success('保存成功！');
    }
    public function add(){
        $this->_name = 'Category';
        if(IS_POST){
            $c_name=I("post.c_name");
            $c_order=I("post.c_order");
            $c_alias=I("post.c_alias");
            //新增的入库
            $num = 0;
            if (is_array($c_name) && count($c_name)>0)
            {
                for ($i =0; $i <count($c_name);$i++){
                    if (!empty($c_name[$i]))
                    {   
                        $setsqlarr['c_name']=trim($c_name[$i]);
                        $setsqlarr['c_order']=intval($c_order[$i]);
                        $setsqlarr['c_alias']=trim($c_alias[$i]);
                        $r = $this->_mod->add($setsqlarr);
                        $r && $num++;
                    }

                }
            }
            $this->success('添加成功！本次添加了'.$num.'个分类');
        }else{
            $this->assign('group',D('CategoryGroup')->where(array('g_alias'=>I('get.alias')))->find());
            $this->display();
        }
    }
    public function edit(){
        $this->_name = 'Category';
        if(!IS_POST){
            $this->assign('group',D('CategoryGroup')->where(array('g_alias'=>I('get.alias')))->find());
        }
        parent::edit();
    }
}
?>