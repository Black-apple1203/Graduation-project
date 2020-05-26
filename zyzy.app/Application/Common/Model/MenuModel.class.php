<?php
namespace Common\Model;
use Think\Model;
class MenuModel extends Model {
    protected $_validate = array(
        array('name', 'require', '{%menu_name_require}'), //菜单名称为必须
        array('module_name', 'require', '{%module_name_require}'), //模块名称必须
        array('controller_name', 'require', '{%controller_name_require}'), //控制器名称必须
        array('action_name', 'require', '{%action_name_require}'), //方法名称必须
    );
    protected $_auto = array (
        array('ordid',255),
        array('display',1),
        array('sys_set',0),
        array('mods',0)
    );
    public function get_level($id,$array=array(),$i=0) {
        foreach($array as $n=>$value){
            if ($value['id'] == $id) {
                if($value['pid']== '0') return $i;
                $i++;
                return $this->get_level($value['pid'],$array,$i);
            }
        }
    }
    /**
     * [menu_cache 菜单缓存]
     */
    public function menu_cache(){
        !APP_DEVELOPER && $where['mods'] = array('neq',1);
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where['module_name'] = array('in',$apply);
        $data = $this->field('id,pid,is_parent,name')->where($where)->order('pid,ordid')->select();
        foreach ($data as $key => $val) {
            if(!$val['pid']){
                $menus['parent'][$val['id']] = $val;
            }else{
                $menus['sub'][$val['pid']][] = $val;
            }
        }
        F('menu_list',$menus);
        return $menus;
    }
    /**
     * [console 控制台便倢菜单]
     */
    public function console_cache(){
        $where = array('often'=>1,'display'=>1);
        !APP_DEVELOPER && $where['mods'] = array('neq',1);
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where['module_name'] = array('in',$apply);
        $menus = M('Menu')->field('id,name,module_name,controller_name,action_name,data,img')->where($where)->order('ordid')->select();
        F('console_menu',$menus);
        return $menus;
    }
    /*
     *读取管理员角色权限下，子菜单列表内容
     */
    public function sub_menu_cache($pid = 0){
        $db_pre = C('DB_PREFIX');
        $join = $db_pre . 'menu m ON m.id=' . $db_pre . 'admin_auth.menu_id';
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where = array('role_id'=>$_SESSION['admin']['role_id'],'m.pid'=>$pid,'m.display'=>1,'m.module_name'=>array('in',$apply));
        !APP_DEVELOPER && $where['m.mods'] = array('neq',1);
        $menuData = M('AdminAuth')->where($where)->join($join)->order('m.ordid,m.id')->getfield('m.id,m.name,m.menu_type,m.module_name,m.controller_name,m.action_name,m.data,m.stat,m.img');
        foreach($menuData as $val){
            if($val['menu_type']){
                $menuList['menu'][] = $val;
            }else{
                $menuList['btn'][] = $val;
            }
        }
        F("admin_menu/{$_SESSION['admin']['role_id']}/sub_menu_{$pid}",$menuList);
        return $menuList;
    }
    /*
     *读取管理员角色权限下，具有子菜单的可见菜列表
     */
    public function auth_menu_cache(){
        $db_pre = C('DB_PREFIX');
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where = array('role_id'=>$_SESSION['admin']['role_id'],'m.display'=>1,'m.module_name'=>array('in',$apply));
        !APP_DEVELOPER && $where['m.mods'] = array('neq',1);
        $menuList = M('AdminAuth')->where($where)->join($db_pre . 'menu m ON m.id=' . $db_pre . 'admin_auth.menu_id')->order('m.ordid')->getfield('m.id,m.name');
        F("admin_menu/{$_SESSION['admin']['role_id']}/auth_menu",$menuList);
        return $menuList;
    }
    /**
     * [menu_nav_cache 读取菜单所有导航列表写入缓存]
     */
    public function menu_nav_cache(){
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where = array('menu_type'=>1,'module_name'=>array('in',$apply));
        !APP_DEVELOPER && $where['mods'] = array('neq',1);
        $menus = $this->where($where)->getField('id,menu_type');
        F('menu_nav_list',$menus);
        return $menus;
    }
    /*
     *读取管理员角色权限内容
     */
    public function auth_cache(){
        $this->group_auth();
        $db_pre = C('DB_PREFIX');
        $apply = array_keys(C('apply'));
        $apply[] = 'Admin';
        $where = array('role_id'=>$_SESSION['admin']['role_id'],'m.module_name'=>array('in',$apply));
        !APP_DEVELOPER && $where['m.mods'] = array('neq',1);
        $authData = M('AdminAuth')->field('m.id,m.pid,m.spid,m.module_name,m.controller_name,m.action_name,m.menu_type,m.log_cn')->where($where)->join($db_pre . 'menu m ON m.id=' . $db_pre . 'admin_auth.menu_id')->order('pid desc,id desc')->select();
        if(false === $menu_nav = F('menu_nav_list')) $menu_nav = $this->menu_nav_cache();
        foreach($authData as $key=>$val){
            $spid = explode('|',$val['spid']);
            //$authList[$val['module_name'].'_'.$val['controller_name'].'_'.$val['action_name']] = array('id'=>$spid[1] ? $spid[1] : $spid[0],'pid'=>$val['pid']==$spid[2] ? $val['pid'] : $spid[2],'log_cn'=>$val['log_cn']);
            $c = count($spid);
            if($c<=3){
                $pid = $id = $spid[1] ? $spid[1] : $spid[0];
                $isget = false;
            }else{
                for($i=$c-2;$i>=0;$i--){
                    if($menu_nav[$spid[$i]]){
                        $pid = $spid[$i];
                        $id = $spid[--$i];
                        break;
                    }
                }
                if($i == 0) $pid = $id = $spid[1] ? $spid[1] : $spid[0];
                $isget = $i >= 2 ? true : false;
            }
            $f = $isget ? '_isget' : '';
            !$authList[$val['module_name'].'_'.$val['controller_name'].'_'.$val['action_name'].$f] && $authList[$val['module_name'].'_'.$val['controller_name'].'_'.$val['action_name'].$f] = array('id'=>$id,'pid'=>$pid,'isget'=>$isget,'log_cn'=>$val['log_cn']);
        }
        F("admin_menu/{$_SESSION['admin']['role_id']}/auth",$authList);
        return $authList;
    }
    public function update_cache(){
        $this->_before_write();
    }
    /**
     * [group_auth 权限分组编辑后，跟据分组结果生成权限列表“此段程序为后加”]
     */
    protected function group_auth(){
        if(1 == $role_id = $_SESSION['admin']['role_id']){
            !APP_DEVELOPER && $where['mods'] = array('neq',1);
            $apply = array_keys(C('apply'));
            $apply[] = 'Admin';
            $where['module_name'] = array('in',$apply);
            $menus = $this->where($where)->getfield('id',true);
        }else{
            $groups = M('AdminRole')->field('groups,mids')->where(array('id'=>$role_id))->find();
            $group_id = explode(',',$groups['groups']);
            $group_msid = explode(',',$groups['mids']);
            if(false === $auth_group_list = F('auth_group_list')) $auth_group_list = D('AdminAuthGroup')->auth_group_list_cache();
            $menus = array();
            foreach ($group_id as $val) {
                $auth_group_list['groups'][$val] && $menus = array_merge($menus,explode(',',$auth_group_list['groups'][$val]));
            }
            foreach ($group_msid as $val) {
                $auth_group_list['mids'][$val] && $menus = array_merge($menus,explode(',',$auth_group_list['mids'][$val]));
            }
            $menus = array_unique($menus);
        }
        //清空权限
        $auth_mod = D('AdminAuth');
        $auth_mod->where(array('role_id'=>$role_id))->delete();
        if($menus){
            foreach ($menus as $key=>$menu_id) {
                $menus[$key] = array('role_id' => $role_id,'menu_id' => $menu_id);
            }
            $auth_mod->addAll($menus);
        }
    }
    /*
     *添加子菜单时，父菜单状态is_parent
     */
    protected function _after_insert(&$data,&$options){
        $this->where(array('id'=>$data['pid']))->setfield('is_parent',1);
        D('AdminAuth')->add(array('role_id' => 1,'menu_id' => $data['id']));
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('console_menu', NULL);
        F('menu_list',NULL);
        F('menu_nav_list',NULL);
        $obj_dir = new \Common\ORG\Dir;
        if(false === $role_list = F('admin_role_list')){
            $role_list = D('AdminRole')->role_cache();
        }
        foreach($role_list as $key=>$val){
            is_dir(DATA_PATH."/admin_menu/{$key}") && $obj_dir->del(DATA_PATH."/admin_menu/{$key}");
        }
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        if($options['where']['id'][1]){
            $ids = $this->where(array('pid'=>array('in',$options['where']['id'][1])))->getfield('id',true);
            $ids && $this->where(array('id'=>array('in',$ids)))->delete();
        }
        $this->_before_write();
    }
}