<?php
namespace Common\Model;
use Think\Model;
class AdminAuthGroupModel extends Model{
	protected $_validate = array(
        array('mid,mids','identicalNull','',0,'callback'),
        array('mid,ordid','identicalEnum','',0,'callback'),
        array('name','1,40','{%admin_role_group_name_length}',0,'length'),
        array('remark','0,200','{%admin_role_group_remark_length}',0,'length'),
    );
    protected $_auto = array (
        array('ordid',255),
        array('addtime','time',1,'function'),
        array('update_time','time',3,'function'),
    );
    /**
     * [auth_group 读取权限组缓存信息]
     */
    public function auth_group_cache(){
    	$group_data = $this->field('id,mid,name,msid,mids')->order('ordid,id asc')->select();
        foreach ($group_data as $val) {
            $group[$val['mid']][] = $val;
        }
    	F('auth_group',$group);
    	return $group;
    }
    public function auth_group_list_cache(){
        $group = $this->field('id,msid,mids')->order('ordid')->select();
        foreach ($group as $key => $val) {
            if($val['msid']){
                $groupList['mids'][$val['msid']] = $val['mids'];
            }else{
                $groupList['groups'][$val['id']] = $val['mids'];
            }
        }
        F('auth_group_list',$groupList);
        return $group;
    }
    /**
     * [menu_group_init 权限组初始化数据]
     */
    public function menu_group_init(){
        if(false === $menus = F('menu_list')) $menus = D('Menu')->menu_cache();
        $time = time();
        foreach ($menus['parent'] as $key => $val) {
            foreach ($menus['sub'][$val['id']] as $key => $_val) {
                $mids = M('Menu')->where(array('spid'=>array('like','%|'.$_val['id'].'|%')))->getfield('id',true);
                $mids[] = $val['id'];
                $m[] = array('name'=>$_val['name'],'mid'=>$val['id'],'msid'=>$_val['id'],'mids'=>implode(',',$mids),'addtime'=>$time,'update_time'=>$time);
            }
        }
        if($m){
            $this->where(array('id'=>array('gt',0)))->delete();
            D('AdminAuth')->where(array('role_id'=>array('gt',0)))->delete();
            if(false === $reg = $this->addAll($m)) return false;
            return true;
        }
    }
    protected function _after_update(){
        D('Menu')->update_cache();
    }
    /**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('auth_group_list',NULL);
        F('auth_group',NULL);
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        D('Menu')->update_cache();
        F('auth_group_list',NULL);
        F('auth_group',NULL);
    }
}
?>