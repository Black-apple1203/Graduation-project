<?php
/**
 * 后台控制器基类
 *
 * @author andery
 */
namespace Common\Controller;
use Common\Controller\BaseController;
class BackendController extends BaseController{
    protected $_name = '';
    protected $_map = array();
    protected $menuid = 0;
    protected $pid = 0;
    public function _initialize() {
        parent::_initialize();
        $this->_check_allow_ip();
        $this->_name = $this->getActionName();
        $this->sitegroup();
        $this->check_priv();
        if(!$this->menuid) $this->menuid = I('request.menuid',0,'intval');
        if($this->menuid) {
            $sub_menu = $this->get_menus($this->menuid);
            if($sub_menu['menu']){//默认页面导航选中样式
                foreach($sub_menu['menu'] as $key=>$val) {
                    if((MODULE_NAME == $val['module_name'] && CONTROLLER_NAME == $val['controller_name'] && ACTION_NAME == $val['action_name']) || $val['id'] == $this->pid) {
                        $sub_menu['menu'][$key]['class'] = 'select';
                        break;
                    }
                }
                $this->assign('isget',$this->isget);
            }
            $this->assign('sub_menu', $sub_menu);
        }
        $this->get_bread_crumb_menu();
        C('visitor',session('admin'));
        C('backend',1);
        $this->assign('visitor',session('admin'));
        $this->assign('menuid', $this->menuid);
		//分站判断
		$subsite = new \Common\qscmstag\subsiteTag($where);
		$subsite_list = $subsite->run();
		$this->assign('subsite_list', $subsite_list);
		//end
        if(C('URL_MODULE_MAP')){
            foreach (C('URL_MODULE_MAP') as $key => $value) {
                if('admin'==$value){
                    C('admin_alias',$key);
                }
            }
        }else{
            C('admin_alias','Admin');
        }
        admin_write_log(session('admin'));
    }
    public function returnMsg($status,$msg='',$data=array()){
        if(IS_AJAX){
            $this->ajaxReturn($status,$msg,$data);
        }else{
            if($status==1){
                $this->success($msg);
            }else{
                $this->error($msg);
            }
        }
    }
    /**
     * 检查ip白名单
     */
    private function _check_allow_ip(){
        $current_ip = get_client_ip(0,true);
        $allow_ip = true;
        $allow_city = true;
        if(C('qscms_backend_allow_ip')){
            $allow_ip = false;
            $ip_rule_arr = explode("|", C('qscms_backend_allow_ip'));
            foreach ($ip_rule_arr as $key => $value) {
                if(false!==stripos($value,'*'))
                {
                    $ips_segment_current = substr($current_ip,0,strrpos($current_ip,'.'));
                    $ips_segment = substr($value,0,strrpos($current_ip,'.'));
                    if(strcmp($ips_segment_current,$ips_segment) == 0){
                        $allow_ip = true;
                        break;
                    }
                }
                else if(false!==stripos($value,'-'))
                {
                    $sub_arr = explode("-", $value);
                    $start_ip = ip2long($sub_arr[0]);
                    $end_ip = ip2long($sub_arr[1]);
                    if(ip2long($current_ip)>=$start_ip && ip2long($current_ip) <=$end_ip){
                        $allow_ip = true;
                        break;
                    }
                }
                else if($value==$current_ip)
                {
                    $allow_ip = true;
                    break;
                }
            }
        }
        if(C('qscms_backend_allow_city')){
            $allow_city = false;
            $city_rule_arr = explode("|", C('qscms_backend_allow_city'));
            $address_info = GetIpLookup($current_ip);
            if($address_info && (in_array($address_info['country'],$city_rule_arr) || in_array($address_info['province'],$city_rule_arr) || in_array($address_info['city'],$city_rule_arr) || in_array($address_info['district'],$city_rule_arr))){
                $allow_city = true;
            }
        }
        if (!$allow_ip || !$allow_city) {
            header('Content-Type:text/html; charset=utf-8');
            exit('你当前的ip被禁止访问!');
        }
    }
    // 面包屑导航
    public function get_bread_crumb_menu(){
        //echo $this->menuid;
        $spid = M('Menu')->getFieldById($this->menuid,'spid');
        $spid_arr = array_filter(explode('|',$spid));
        //dump($spid_arr);
        if(false === $menus = F("menu_list")){
            $menus = D('Menu')->menu_cache();
        }
        $this->menu_title = $menus['parent'][$spid_arr[0]]['name'];
        $sub_menus = $menus['sub'][$spid_arr[0]];
        foreach ($sub_menus as $sub){
            if($sub['id'] == $spid_arr[1]){
                $this->sub_menu_title = $sub['name'];
                break;
            }
        }
    }
    /**
     * 列表页面
     */
    public function index() {
        $map = $this->_search();//调用本类下面的_search方法生成查询条件
        if(method_exists($this,'_before_search')) {
            $map = $this->_before_search($map);
        }else{
            $this->where && $map = array_merge($map,$this->where);
        }
        $mod = D($this->_name);
        !empty($mod) && $this->_list($mod, $map);
        if(method_exists($this,'_after_search')) {
            $this->_after_search();
        }
        $this->display($this->_tpl);
    }
    /**
     * 添加
     */
    public function add() {
        $mod = D($this->_name);
        $pk = $mod->getPk();
        if(IS_POST){
            if(false === $data = $mod->create()){
                IS_AJAX && $this->ajaxReturn(0,$mod->getError());
                $this->error($mod->getError());
            }
            if(method_exists($this,'_before_insert')) {
                $data = $this->_before_insert($data);
            }
			$field = $mod->getDbFields();
            if(C('qscms_subsite_open')==1 && in_array('subsite_id',$field) && D('Subsite')->get_subsite_domain()){
                $subsites = I('request.subsite_id');
                if($subsites == ''){
                    IS_AJAX && $this->ajaxReturn(0, '请选择站点！');
                    $this->error('请选择站点！');
                }
                $subsites = is_array($subsites)? $subsites : array($subsites);
                foreach ($subsites as $val) {
                    $data['subsite_id'] = intval($val);
                    if($id = $mod->add($data)){
                        if(method_exists($this,'_after_insert')){
                            $data[$pk] = $id;
                            $this->_after_insert($id,$data);
                        }
                        
                    }else{
                        $reg = true;
                        break;
                    }
                }
                if(!$reg){
                    IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
                    $this->success(L('operation_success'));
                }else{
                    IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                    $this->error(L('operation_failure'));
                }
            }else{
				if($id = $mod->add($data)){
					if(method_exists($this,'_after_insert')){
						$data[$pk] = $id;
						$this->_after_insert($id,$data);
					}
					IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
					$this->success(L('operation_success'));
				}else{
					IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
					$this->error(L('operation_failure'));
				}
			}
        }else{
            $this->assign('open_validator',true);
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(1,'',$response);
            }else{
                $this->display($this->_tpl);
            }
        }
    }
    /**
     * 修改
     */
    public function edit(){
        $mod = D($this->_name);
        $pk = $mod->getPk();
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_update')) {
                $data = $this->_before_update($data);
            }
            if (false !== $mod->save($data)) {
                if( method_exists($this, '_after_update')){
                    $id = $data[$pk];
                    $this->_after_update($id,$data);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'edit');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
			$id = I('get.'.$pk,0,'intval');
            $info = $mod->find($id);
            if( method_exists($this, '_after_select')){
                if($data = $this->_after_select($info)) $info = $data;
            }
            $this->assign('info', $info);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display($this->_tpl);
            }
        }
    }
    /**
     * ajax修改单个字段值
     */
    public function ajax_edit(){
        //AJAX修改数据
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $id = I('get.'.$pk,0,'intval');
        $field = I('get.field','','trim');
        $val = I('get.val','','trim');
        //允许异步修改的字段列表  放模型里面去 TODO
        $mod->where(array($pk=>$id))->setField($field, $val);
        $this->ajaxReturn(1);
    }
    /**
     * 删除
     */
    public function delete(){
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $ids = I('request.'.$pk);
        $ids = is_array($ids)?implode(",",$ids):$ids;
        if ($ids) {
            $map[$pk] = array('in',$ids);
            $this->_map && $map = array_merge($map,$this->_map);
            if( method_exists($this, '_before_del')){
                $after_data = $mod->where($map)->select();
                $this->_before_del($after_data);
            }
            if (false !== $reg = $mod->where($map)->delete()) {
                if( method_exists($this, '_after_del')){
                    $this->_after_del($ids);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, '请选择要删除的内容！');
            $this->error('请选择要删除的内容！');
        }
    }
    /**
     * 获取请求参数生成条件数组
     */
    protected function _search() {
        //生成查询条件
        $mod = D($this->_name);
        $map = array();
        $field = $mod->getDbFields();
        $tablename = $mod->getTableName();
        foreach ($field as $key => $val) {//getDbFields函数用于获得数据表的所有字段名称
            if (substr($key, 0, 1) == '_') {
                continue;//continue方法用于跳出单次循环
            }
            if ('' != I('request.'.$val)) {
                $t = $this->join ? $tablename.'.'.$val : $val;
                $map[$t] = I('request.'.$val);
            }
        }
        return $map;
    }
    /**
     * 列表处理
     *
     * @param obj $model  实例化后的模型
     * @param array $map  条件数据（默认为空））
     * @param string $order_by  排序（默认为降序）
     * @param string $field_list 显示字段（默认为'*'，全部显示）
     * @param string $union     union查询sql
     * @param string $join     join连表，传入join字句的数组
     * @param intval $pagesize 每页数据行数（默认为40条计录）
     * @param string $custom_fun 自定义方法名称
     */
    protected function _list($model, $map = array(), $order='', $field_list='', $union='', $join=array(),$pagesize_by=10,$custom_fun){
        //排序
        $mod_pk = $model->getPk();//getPK函数用于获得实例化对象后的数据表主健的字段名称
        $fields = $model->getDbFields();
        if ($sort = I("request.sort")) {
            if(in_array($sort,$fields)){
                if ($order = I('request.order')) {
                    if(in_array(strtolower($order),array('desc','asc'))) $order_by = $sort.' '.$order;
                }else{
                    $order_by = $sort.' desc';
                }
            }
        }elseif($order){
            $order_by = $order;
        }elseif($this->order){
            $order_by = $this->order;
        }elseif (empty($order_by)){
            $order_by = $mod_pk.' desc';
        }

        // if (I("request.sort")) {
        //     $sort = I("request.sort");
        // } else if (!empty($sort_by)) {
        //     $sort = $sort_by;
        // } else if ($this->sort) {
        //     $sort = $this->sort;
        // } else {
        //     $sort = $mod_pk;
        // }
        // if (I("request.order")) {
        //     $order = I("request.order");
        // } else if (!empty($order_by)) {
        //     $order = $order_by;
        // } else if ($this->order) {
        //     $order = $this->order;
        // } else {
        //     $order = 'DESC';//DESC数据为降序
        // }
        if($field_list){
            $field = $field_list;
        }elseif($this->field){
            $field = $this->field;
        }else{
            $field = '*';
        }
        //如果需要分页
        if(I('request.pagesize',0,'intval')){
            $pagesize = I('request.pagesize',0,'intval');
        }else if(isset($this->pagesize)){
            $pagesize = $this->pagesize;
        }else{
            $pagesize = $pagesize_by;
        }
        if(!$join && $this->join) $join = $this->join;
        if(!is_array($join)) $join = array($join);
        if(!$union && $this->union) $union = $this->union;
        if($this->group) $group = $this->group;
        if ($pagesize) {
            $count = $model->where($map);//获得数据表查询结果的总条数
            if(!empty($join)){
                foreach ($join as $key => $value) {
                    $count = $count->join($value);
                }
            }
            $distinct = $this->distinct ? 'distinct '.$this->distinct : '*';
            if($union){
                $count = $model->query('select count('.$distinct.') as tp_count from('.$count->buildSql().' union all '.$union.') as tp_t');
                $count = $count[0]['tp_count'];
            }else{
                $count = $count->count($distinct);
            }
            $pager = pager($count, $pagesize);//实例化thinkphp内置的分页显示类
        }
        $select = $model->field($field)->where($map)->order($order_by);
        if($union){
            $select = $select->union($union,true);
        }
        if(!empty($join)){
            foreach ($join as $key => $value) {
                $select = $select->join($value);
            }
        }
        $this->list_relation && $select->relation(true);
        if ($pagesize) {
            $select->limit($pager->firstRow.','.$pager->listRows);
            $page = $pager->fshow(true);
            $this->assign("page", $page);
        }
        $list = $select->group($this->distinct)->select();
        
        //dump($model->getlastSql());
        if($custom_fun){
            $fun = $custom_fun;
        }elseif($this->custom_fun){
            $fun = $this->custom_fun;
        }else{
            $fun = '_custom_fun';
        }
        if(method_exists($this,$fun)) {
            $list = $this->$fun($list);
        }
        $this->assign('list', $list);
        $this->assign('total', $count?$count:count($list));
        $this->assign('pagesize',$pagesize);
        $this->assign('list_table', true);
    }
    /**
     * [pending 待处理事件统计]
     */
    protected function _pending($mod,$where,$distinct,$join=''){
        $field = M($mod)->getDbFields();
        $distinct && $distinct = 'distinct '.$distinct;
        return M($mod)->join($join)->where($where)->count($distinct);
    }
    protected function check_priv(){
        if(false === $authList = F("admin_menu/{$_SESSION['admin']['role_id']}/auth")){
            $authList = D('Menu')->auth_cache($_SESSION['admin']['role_id']);
        }
        if($child = I('request.child',0,'intval')){
            $this->menuid = I('request.menuid',0,'intval');
            $sub_menu = $this->get_menus($this->menuid);
            if($sub_menu['menu']){
                $menu = $sub_menu['menu'][0];
                $this->redirect($menu['module_name'].'/'.$menu['controller_name'].'/'.$menu['action_name'],array('menu_id'=>$this->menuid,'sub_menu_id'=>$menu['id']));
            }
        }
		//添加'get_weixin_qrcode','waiting_weixin_login'的权限
        if(!session('admin') && !in_array(ACTION_NAME, array('login','verify_code','get_weixin_qrcode','waiting_weixin_login'))) exit('<script type="text/javascript">top.location="'.U('Admin/index/login').'";</script>');
        if(CONTROLLER_NAME == 'attachment') return true;
        if (in_array(CONTROLLER_NAME, explode(',', 'Index'))) return true;
		if (CONTROLLER_NAME == 'Qrcode') return true;//后台生成二维码的权限
        $f = $_REQUEST['_k_v'] ? '_isget' : '';
        $reg = $authList[MODULE_NAME.'_'.CONTROLLER_NAME.'_'.ACTION_NAME.$f];
        !$reg && $reg = $authList[MODULE_NAME.'_'.CONTROLLER_NAME.'_'.ACTION_NAME];
        if($reg){
            $this->menuid = $reg['id'];
            $this->pid = $reg['pid'];
            $this->isget = $reg['isget'];
            return true;
        }
        if($_SESSION['admin']['role_id'] == 1) return true;
        IS_AJAX && $this->ajaxReturn(0,L('_VALID_ACCESS_'));
        $this->error(L('_VALID_ACCESS_'));
    }
    protected function get_menus($pid){
        if(false === $auth_menu = F("admin_menu/{$_SESSION['admin']['role_id']}/auth_menu")){
            $auth_menu = D('Menu')->auth_menu_cache();
        }
        if(isset($auth_menu[$pid])){
            if(false === $sub_menu = F("admin_menu/{$_SESSION['admin']['role_id']}/sub_menu_{$pid}")) $sub_menu = D('Menu')->sub_menu_cache($pid);
            $sub_menu['pageheader'] = $auth_menu[$pid];
            return $sub_menu;
        }
        return false;
    }
    protected function sitegroup(){
        if(IS_AJAX) return false;
        if($synsitegroupregister = cookie('members_sitegroup_register')){
            $this->assign('synsitegroupregister',$synsitegroupregister);
            cookie('members_sitegroup_register',null);
        }elseif($synsitegroup = cookie('members_sitegroup_action')){
            $this->assign('synsitegroup',$synsitegroup);
            cookie('members_sitegroup_action',null);
        }
        if($synsitegroupunbindmobile = cookie('members_sitegroup_unbind_mobile')){
            $this->assign('synsitegroupunbindmobile',$synsitegroupunbindmobile);
            cookie('members_sitegroup_unbind_mobile',null);
        }
        if($synsitegroupedit = cookie('members_sitegroup_edit')){
            $this->assign('synsitegroupedit',$synsitegroupedit);
            cookie('members_sitegroup_edit',null);
        }
    }
    public function update_config($new_config, $config_file = '') {
        !is_file($config_file) && $config_file = HOME_CONFIG_PATH . 'config.php';
        if (is_writable($config_file)) {
            $config = require $config_file;
            $config = multimerge($config, $new_config);
            if($config['SESSION_OPTIONS']){
                $config['SESSION_OPTIONS']['path'] = SESSION_PATH;
            }
            file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
            @unlink(RUNTIME_FILE);
            return true;
        } else {
            return false;
        }
    }
}