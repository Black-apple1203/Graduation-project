<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CategoryMajorController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CategoryMajor');
    }
    /**
     * 专业分类
     */
    public function index(){
        $pid = I('get.pid',0,'intval');
        $major = $this->_mod->field('id,parentid,categoryname,category_order')->where(array('parentid'=>$pid))->order('category_order desc,id')->select();
        if(IS_AJAX) $this->ajaxReturn(1,'专业获取成功！',$major);
        $this->assign('major', $major);
        $this->display();
    }
    /**
     * 批量保存专业分类
     */
    public function majorAllSave(){
        $save_id = I('post.save_id');
        $categoryname = I('post.categoryname');
        $category_order = I('post.category_order');
        $add_pid = I('post.add_parentid');
        $add_categoryname = I('post.add_categoryname');
        $add_category_order = I('post.add_category_order');
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                $setsqlarr['categoryname']=trim($categoryname[$k]);
                $setsqlarr['category_order']=intval($category_order[$k]);
                $this->_mod->where(array('id'=>array('eq',intval($save_id[$k]))))->save($setsqlarr);
            }
        }
        //新增的入库
        if (is_array($add_pid) && count($add_pid)>0)
        {
            for ($i =0; $i <count($add_pid);$i++){
                if (!empty($add_categoryname[$i]))
                {   
                    $setsqlarr['categoryname']=trim($add_categoryname[$i]);
                    $setsqlarr['category_order']=intval($add_category_order[$i]);
                    $setsqlarr['parentid']=intval($add_pid[$i]);   
                    $this->_mod->add($setsqlarr);
                }
            }
        }
        $this->success('保存成功！');
    }
    public function add(){
        $this->_name = 'CategoryMajor';
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
                        $r = $this->_mod->add($setsqlarr);
                        $r && $num++;
                    }

                }
            }
            if($num>0){
                $this->success('成功添加'.$num.'个分类');
            }else{
                $this->error('成功添加'.$num.'个分类');
            }
        }else{
            $cate = $this->_mod->field('id,parentid,categoryname,category_order')->order('category_order desc,id')->select();
            foreach ($cate as $key => $value) {
                $cate_arr[$value['parentid']][] = $value;
            }
            $this->assign('cate',$cate_arr);
            $this->display();
        }
    }
    public function edit(){
        $py = new \Common\qscmslib\pinyin;
        $this->_name = 'CategoryMajor';
        if(!IS_POST){
            $cate = $this->_mod->field('id,parentid,categoryname,category_order')->order('category_order desc,id')->select();
            foreach ($cate as $key => $value) {
                $cate_arr[$value['parentid']][] = $value;
            }
            $this->assign('cate',$cate_arr);
        }
        parent::edit();
    }
    /**
     * 删除分类
     */
    public function delete(){
        $id = I('request.id','','trim');
        if(!$id){
            $this->error('请选择分类！');
        }
        $r = $this->_mod->category_delete($id);
        if($r>0){
            $this->success('成功删除'.$r.'条分类！');
        }else{
            $this->error('成功删除'.$r.'条分类！');
        }
    }
}
?>