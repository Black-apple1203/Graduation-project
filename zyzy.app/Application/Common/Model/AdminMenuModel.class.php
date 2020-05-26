<?php
namespace Common\Model;
use Think\Model;
class AdminMenuModel extends Model{
    public function get_menu($role_id,$pid){
        $menu_cache = $this->menu_cache($role_id);
        return $menu_cache[$pid];
    }
    protected function menu_cache($role_id){
        $cache = F('admin_menu_'.$role_id);
        if(false===$cache){
            $cache = $this->witer_cache($role_id);
        }
        return $cache;
    }
    public function witer_cache($role_id){
        // $cache = array(
        //     0=>array(
        //         array('m_id'=>1,'m_title'=>'企业'),
        //         array('m_id'=>2,'m_title'=>'个人')
        //     ),
        //     1=>array(
        //         array('m_id'=>3,'m_title'=>'职位管理'),
        //         array('m_id'=>4,'m_title'=>'企业管理')
        //     ),
        // );
        
        if($role_id==1){
            $menu_map['m_pid'] = array('neq',0);
        }else{
            $role_info = D('AdminRole')->find($role_id);
            if(!$role_info || !$role_info['mids']){
                return;
            }
            $menu_map['m_id'] = array('in',$role_info['mids']);
        }
        
        $top_nav_list = D('AdminMenu')->where(array('m_pid'=>array('eq',0)))->order('m_ordid desc,m_id asc')->select();
        $list = D('AdminMenu')->where($menu_map)->order('m_ordid desc,m_id asc')->select();
        foreach ($list as $key => $value) {
            $cache[$value['m_pid']][] = $value;
        }
        foreach ($cache as $key => $value) {
            $first_arr[$key] = current($value);
        }
        foreach ($top_nav_list as $key => $value) {
            if(array_key_exists($value['m_id'], $first_arr)){
                $value['m_module'] = $first_arr[$value['m_id']]['m_module'];
                $value['m_controller'] = $first_arr[$value['m_id']]['m_controller'];
                $value['m_action'] = $first_arr[$value['m_id']]['m_action'];
                $cache[0][] = $value;
            }
        }
        var_dump($cache);die;
        F('admin_menu_'.$role_id,$cache);
        return $cache;
    }
}