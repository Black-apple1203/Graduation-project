<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CategoryJobsController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CategoryJobs');
    }
    /**
     * 职位分类
     */
    public function index(){
        $pid = I('get.pid',0,'intval');
        $jobs = $this->_mod->field('id,parentid,categoryname,category_order,spell')->where(array('parentid'=>$pid))->order('category_order desc,id')->select();
        if(IS_AJAX) $this->ajaxReturn(1,'分类获取成功！',$jobs);
        $this->assign('jobs', $jobs);
        $this->display();
    }
    /**
     * 批量保存职位分类
     */
    public function jobsAllSave(){
        $py = new \Common\qscmslib\pinyin;
        $save_id = I('post.save_id');
        $categoryname = I('post.categoryname');
        $category_order = I('post.category_order');
        $spell = I('post.spell');
        $add_pid = I('post.add_parentid');
        $add_categoryname = I('post.add_categoryname');
        $add_category_order = I('post.add_category_order');
        $num = 0;
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                $setsqlarr['categoryname']=trim($categoryname[$k]);
                $setsqlarr['category_order']=intval($category_order[$k]);
                $m = $this->_mod->create($setsqlarr);
                if($m){
                    $r = $this->_mod->where(array('id'=>array('eq',intval($save_id[$k]))))->save();
                    $r && $num++;
                }
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
                    $setsqlarr['spell'] = $py->getAllPY($setsqlarr['categoryname']);
                    $setsqlarr['spell'] = $this->check_spell_repeat($setsqlarr['spell']);
                    $m = $this->_mod->create($setsqlarr);
                    if($m){
                        $r = $this->_mod->add();
                        $r && $num++;
                    }
                    
                }
            }
        }
        if($num>0){
            $this->success('成功保存'.$num.'个分类！');
        }else{
            $this->error('成功保存'.$num.'个分类！');
        }
    }
    public function add(){
        $py = new \Common\qscmslib\pinyin;
        $this->_name = 'CategoryJobs';
        if(IS_POST){
            $parentid=I("post.parentid");
            $categoryname=I("post.categoryname");
            $category_order=I("post.category_order");
            $spell=I("post.spell");
            $info['minwage'] = I('post.minwage',0,'intval');
            $info['maxwage'] = I('post.maxwage',0,'intval');
            $info['negotiable'] = I('post.negotiable',0,'intval');
            $info['department'] = I('post.department','','trim');
            $info['education'] = I('post.education',0,'intval');
            $info['education_cn'] = I('post.education_cn','','trim');
            $info['experience'] = I('post.experience',0,'intval');
            $info['experience_cn'] = I('post.experience_cn','','trim');
            $info['minage'] = I('post.minage',0,'intval');
            $info['maxage'] = I('post.maxage',0,'intval');
            $info['amount'] = I('post.amount',0,'intval');
            $info['contents'] = I('post.contents','','trim');
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
                        $setsqlarr['jobs_tpl']=serialize($info);
                        $setsqlarr['spell'] = $spell[$i]?$spell[$i]:$py->getAllPY($setsqlarr['categoryname']); 
                        $setsqlarr['spell'] = $this->check_spell_repeat($setsqlarr['spell']);
                        $m = $this->_mod->create($setsqlarr);
                        if($m){
                            $r = $this->_mod->add();
                            $r && $num++;
                        }
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
            $category = D('Category')->get_category_cache();
            $this->assign('category',$category);
            $this->assign('cate',$cate_arr);
            $this->display();
        }
    }
    public function _before_update($data){
        $info['minwage'] = I('post.minwage',0,'intval');
        $info['maxwage'] = I('post.maxwage',0,'intval');
        $info['negotiable'] = I('post.negotiable',0,'intval');
        $info['department'] = I('post.department','','trim');
        $info['education'] = I('post.education',0,'intval');
        $info['education_cn'] = I('post.education_cn','','trim');
        $info['experience'] = I('post.experience',0,'intval');
        $info['experience_cn'] = I('post.experience_cn','','trim');
        $info['minage'] = I('post.minage',0,'intval');
        $info['maxage'] = I('post.maxage',0,'intval');
        $info['amount'] = I('post.amount',0,'intval');
        $info['contents'] = I('post.contents','','trim');
        $data['jobs_tpl'] = serialize($info);
        return $data;
    }
    public function edit(){
        $py = new \Common\qscmslib\pinyin;
        $this->_name = 'CategoryJobs';
        if(!IS_POST){
            $cate = $this->_mod->field('id,parentid,categoryname,category_order')->order('category_order desc,id')->select();
            foreach ($cate as $key => $value) {
                $cate_arr[$value['parentid']][] = $value;
            }
            $category = D('Category')->get_category_cache();
            $this->assign('category',$category);
            $this->assign('cate',$cate_arr);
        }else{
            $_POST['spell'] = I('post.spell','','trim')?I('post.spell','','trim'):$py->getAllPY(I('post.categoryname','','trim'));
            $_POST['spell'] = $this->check_spell_repeat($_POST['spell'],0,$_POST['id']);
        }
        parent::edit();
    }
    public function _after_select($info){
        $tpl =unserialize($info['jobs_tpl']);
        $info['jobs_tpl'] =$tpl;
        return $info;
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
    private function check_spell_repeat($spell,$index=0,$id=0){
        $spell = del_punctuation($spell);
        $spell_index = $index==0?$spell:($spell.$index);
        $map['spell'] = array('eq',$spell_index);
        if($id>0){
            $map['id'] = array('neq',$id);
        }
        $has = D('CategoryJobs')->where($map)->find();
        if($has){
            $index++;
            $spell_index = $this->check_spell_repeat($spell,$index,$id);
        }
        return $spell_index;
    }
}
?>