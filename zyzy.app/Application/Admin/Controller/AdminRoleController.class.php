<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class AdminRoleController extends BackendController{
    public function _initialize(){
        parent::_initialize();
        $this->_mod = D('AdminRole');
    }
    public function _before_index(){
        $this->order = 'ordid desc';
    }
    public function _before_edit(){
        $this->assign('mark','index');
    }
    public function auth(){
        $auth_mod = D('AdminAuth');
        if(IS_POST){
            $id = I('post.id',0,'intval');
            $group_id = I('post.group_id');
            $group_msid = I('post.group_msid');
            if ((is_array($group_id) && count($group_id) > 0) || (is_array($group_msid) && count($group_msid) > 0)) {
                $groups = implode(',',$group_id)?:'';
                $mids = implode(',',$group_msid)?:'';
                $this->_mod->where(array('id'=>$id))->save(array('groups'=>$groups,'mids'=>$mids));
                $menus = array();
                if(false === $auth_group_list = F('auth_group_list')) $auth_group_list = D('AdminAuthGroup')->auth_group_list_cache();
                foreach ($group_id as $val) {
                    $auth_group_list['groups'][$val] && $menus = array_merge($menus,explode(',',$auth_group_list['groups'][$val]));
                }
                foreach ($group_msid as $val) {
                    $auth_group_list['mids'][$val] && $menus = array_merge($menus,explode(',',$auth_group_list['mids'][$val]));
                }
                //清空权限
                $auth_mod->where(array('role_id'=>$id))->delete();
                if($menus = array_unique($menus)){
                    foreach ($menus as $key=>$menu_id) {
                        $menus[$key] = array('role_id' => $id,'menu_id' => $menu_id);
                    }
                    $auth_mod->addAll($menus);
                }
                $this->success(L('operation_success'));
            }
        }else{
            $id = I('get.id',0,'intval');
            !$id && $this->error('请选择管理员角色！');
            $role = M('AdminRole')->field('id,name,groups,mids')->where(array('id'=>$id))->find();
            !$role && $this->error('管理员角色不存在！');
            $role['groups'] = explode(',',$role['groups']);
            $role['mids'] = explode(',',$role['mids']);
            if(false === $auth_group = F('auth_group')) $auth_group = D('AdminAuthGroup')->auth_group_cache();
            if(false === $menus = F('menu_list')) $menus = D('Menu')->menu_cache();
            $this->assign('role',$role);
            $this->assign('menus',$menus['parent']);
            $this->assign('auth_group',$auth_group);
            $this->display();
        }
    }
    /**
     * [group 权限分组]
     */
    public function group(){
        if(IS_AJAX){
            $mid = I('get.pid',0,'intval');
            $menu_list = D('AdminAuthGroup')->field('id,name,ordid')->where(array('mid'=>$mid))->order('ordid,id')->select();
            $this->ajaxReturn(1,'菜单获取成功！',$menu_list);
        }else{
            $menu_list = D('Menu')->field('id,name')->where(array('pid'=>0))->order('ordid,id')->select();
            $this->assign('menu_list', $menu_list);
            $this->display();
        }
    }
    public function group_init(){
        $reg = D('AdminAuthGroup')->menu_group_init();
        !$reg && $this->error('初始化失败，请重新操作！');
        $this->success('初始化成功！');
    }
    /**
     * [group_add 添加权限分组]
     */
    public function group_add(){
        if(!IS_POST){
            if(false === $menus = F('menu_list')){
                $menus = D('Menu')->menu_cache();
            }
            $this->assign('menus',$menus);
        }
        $this->_name = 'AdminAuthGroup';
        $this->add();
    }
    /**
     * [group_edit 修改权限分组]
     */
    public function group_edit(){
        if(!IS_POST){
            if(false === $menus = F('menu_list')){
                $menus = D('Menu')->menu_cache();
            }
            $this->assign('menus',$menus);
        }
        $this->_name = 'AdminAuthGroup';
        $this->edit();
    }
    /**
     * [group_delete 删除权限分组]
     */
    public function group_delete(){
        $this->_name = 'AdminAuthGroup';
        $this->delete();
    }
    /**
     * [group_auth ajax设置分组权限]
     */
    public function ajax_group_auth(){
        $this->_name = 'AdminAuthGroup';
        $this->edit();
    }
    /**
     * [authAllSave 批量保存权限分组]
     */
    public function authAllSave(){
        $save_id = I('post.save_id');
        $name = I('post.name');
        $ordid = I('post.ordid');
        $add_pid = I('post.add_pid');
        $add_name = I('post.add_name');
        $add_ordid = I('post.add_ordid');
        $mod = M('AdminAuthGroup');
        if(is_array($save_id) && count($save_id)>0){
            foreach($save_id as $k=>$v){
                $setsqlarr['name']=trim($name[$k]);
                $setsqlarr['ordid']=intval($ordid[$k]);
                if(false === $mod->where(array('id'=>intval($save_id[$k])))->save($setsqlarr)){
                    $this->error('保存失败！');
                }
                $num++;
            }
        }
        //新增的入库
        if (is_array($add_pid) && count($add_pid)>0){
            for ($i =0; $i <count($add_pid);$i++){
                if (!empty($add_name[$i])){   
                    $data['name']=trim($add_name[$i]);
                    $data['ordid']=intval($add_ordid[$i]);
                    $data['mid']=intval($add_pid[$i]);   
                    $m = $mod->create($data);
                    if($m){
                        if($id = $mod->add()) $num++;
                    }
                }
            }
        }
        $this->success('成功保存'.$num.'个权限分组！');
    }
    /**
     * [ajax_auth ajax获取权限分组列表]
     */
    public function ajax_auth(){
        $menu_mod = D('Menu');
        $auth_mod = D('AdminAuth');
            $id = I('get.id',0,'intval');
            $tree = new \Common\ORG\Tree();
            $tree->icon = array('│ ','├─ ','└─ ');
            $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
            $result = $menu_mod->order('ordid')->select();
            $priv_ids = M('AdminAuthGroup')->where(array('id'=>$id))->getfield('mids');
            $priv_ids = explode(',',$priv_ids);
            foreach($result as $k=>$v) {
                $result[$k]['level'] = $menu_mod->get_level($v['id'],$result);
                $result[$k]['checked'] = (in_array($v['id'], $priv_ids))? ' checked' : '';
                $result[$k]['parentid_node'] = ($v['pid'])? ' class="child-of-node-'.$v['pid'].'"' : '';
            }
            $str  = "<tr id='node-\$id' \$parentid_node>" .
                        "<td style='padding-left:10px;'>\$spacer<input type='checkbox' name='menu_id[]' value='\$id' class='J_checkitem' level='\$level' \$checked> \$name</td>
                    </tr>";
            $tree->init($result);
            $menu_list = $tree->get_tree(0, $str);
            $this->assign('list', $menu_list);
            $this->assign('role', $role_data);
        $html = $this->fetch('ajax_auth');
        $this->ajaxReturn(1,'获取数据成功！',$html);
    }
}
?>