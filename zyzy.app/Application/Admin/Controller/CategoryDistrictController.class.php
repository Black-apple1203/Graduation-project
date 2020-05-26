<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class CategoryDistrictController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('CategoryDistrict');
    }
    /**
     * 地区分类
     */
    public function index(){
        $pid = I('get.pid',0,'intval');
        $district = $this->_mod->field('id,parentid,categoryname,category_order,spell')->where(array('parentid'=>$pid))->order('category_order desc,id')->select();
        if(IS_AJAX) $this->ajaxReturn(1,'地区获取成功！',$district);
        if(C('qscms_default_district')){
            $district_end = end(explode('.',C('qscms_default_district')));
            $city = get_city_info($district_end);
            $this->assign('district_end',$district_end);
            $this->assign('default_district',$city);
        }
        $this->assign('district', $district);
        $this->display();
    }
    /**
     * 批量保存地区分类
     */
    public function districtAllSave(){
        $py = new \Common\qscmslib\pinyin;
        $save_id = I('post.save_id');
        $categoryname = I('post.categoryname');
        $category_order = I('post.category_order');
        $add_pid = I('post.add_parentid');
        $add_categoryname = I('post.add_categoryname');
        $add_category_order = I('post.add_category_order');
        $num = 0;
        if (is_array($save_id) && count($save_id)>0)
        {
            foreach($save_id as $k=>$v)
            {
                unset($setsqlarr);
                $setsqlarr['id'] = intval($v);
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
                    unset($setsqlarr); 
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
        $this->_name = 'CategoryDistrict';
        if(IS_POST){
            $parentid=I("post.parentid");
            $categoryname=I("post.categoryname");
            $category_order=I("post.category_order");
            $spell=I("post.spell");
            //新增的入库
            $num = 0;
            if (is_array($categoryname) && count($categoryname)>0)
            {
                for ($i =0; $i <count($categoryname);$i++){
                    if (!empty($categoryname[$i]))
                    {   
                        unset($setsqlarr);
                        $setsqlarr['categoryname']=trim($categoryname[$i]);
                        $setsqlarr['category_order']=intval($category_order[$i]);
                        $setsqlarr['parentid']=intval($parentid[$i]); 
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
                $this->success('成功添加'.$num.'个分类！');
            }else{
                $this->error('成功添加'.$num.'个分类！');
            }
        }else{
            $province = $this->_mod->field('id,parentid,categoryname,category_order')->order('category_order desc,id')->select();
            foreach ($province as $key => $value) {
                $cate_arr[$value['parentid']][] = $value;
            }
            $this->assign('province',$cate_arr);
            $this->display();
        }
    }
    public function edit(){
        $py = new \Common\qscmslib\pinyin;
        $this->_name = 'CategoryDistrict';
        if(!IS_POST){
            $province = $this->_mod->field('id,parentid,categoryname,category_order')->order('category_order desc,id')->select();
            foreach ($province as $key => $value) {
                $cate_arr[$value['parentid']][] = $value;
            }
            $this->assign('province',$cate_arr);
        }else{
            $_POST['spell'] = I('post.spell','','trim')?I('post.spell','','trim'):$py->getAllPY(I('post.categoryname','','trim'));
            $_POST['spell'] = $this->check_spell_repeat($_POST['spell'],0,$_POST['id']);
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
    private function check_spell_repeat($spell,$index=0,$id=0){
        $spell = del_punctuation($spell);
        $spell_index = $index==0?$spell:($spell.$index);
        $map['spell'] = array('eq',$spell_index);
        if($id>0){
            $map['id'] = array('neq',$id);
        }
        $has = D('CategoryDistrict')->where($map)->find();
        if($has){
            $index++;
            $spell_index = $this->check_spell_repeat($spell,$index,$id);
        }
        return $spell_index;
    }
}
?>