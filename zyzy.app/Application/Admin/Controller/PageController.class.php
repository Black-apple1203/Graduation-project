<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class PageController extends BackendController{
    protected $norewrite;
    protected $nocaching;
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Page');
        $this->norewrite=array('QS_login');
        $this->nocaching=array('QS_login');
    }
    /**
     * 页面列表
     */
    public function index(){
        $_GET['type'] = $_REQUEST['type'] = 'Home';
        $this->order = 'id asc';
        parent::index();
    }
    public function add(){
        if(IS_POST){
            substr(I('post.alias'),0,3)=='QS_'?$this->error('调用名称不允许 QS_ 开头！'):'';
            if ($this->_mod->ck_page_alias(I('post.alias'),null)){
                $this->error("调用ID ".$_POST['alias']." 已经存在！请重新填写");
            }
        }
        parent::add();
    }
    public function _after_select($data){
        $data['variate'] = unserialize($data['variate']);
        return $data;
    }
    public function edit(){
        if(IS_POST){
            if (I('post.systemclass')<>"1")//非系统内置
            {
            $_POST['pagetpye']=I('post.pagetpye',1,'trim');
            $_POST['alias']=I('post.alias','','trim')?I('post.alias','','trim'):$this->error('调用ID不能为空！');
            substr($_POST['alias'],0,3)=='QS_'?$this->error('调用名称不允许 QS_ 开头！'):'';
            }
            if (in_array(trim($_POST['alias']),$this->norewrite) && $_POST['url']=='1')
            {
                $_POST['url']=0;
            }
            if (in_array(trim($_POST['alias']),$this->nocaching))
            {
                $_POST['caching']=0;
            }
            if ($this->_mod->ck_page_alias($_POST['alias'],I('post.id')))
            {
                $this->error("调用ID ".$_POST['alias']." 已经存在！请重新填写");
            }
        }
        parent::edit();
    }
    /**
     * 设置页面URL
     */
    public function set_url(){
        $id =I('post.id',0,'intval')?I('post.id',0,'intval'):$this->error("你没有选择页面！");
        if ($this->_mod->set_page_url($id,I('post.url',0,'intval'),$this->norewrite))
        {
            $this->success("设置成功！");
        }
        else
        {
            $this->error("设置失败！");
        }
    }
    /**
     * 设置页面缓存时间
     */
    public function set_caching(){
        $id =I('post.id',0,'intval')?I('post.id',0,'intval'):$this->error("你没有选择页面！");
        if ($this->_mod->set_page_caching($id,I('post.caching',0,'intval'),$this->nocaching))
        {
            $this->success("设置成功！");
        }
        else
        {
            $this->error("设置失败！");
        }
    }
    /**
     * 删除页面
     */
    public function delete(){
        $id=I('request.id');
        if (empty($id)) $this->error("请选择项目！");
        if ($num=$this->_mod->del_page($id))
        {
            $this->success("删除成功！共删除".$num."行");
        }
        else
        {
            $this->error("删除失败！");
        }
    }
    /**
     * [rewrite 页面伪静态规则]
     */
    public function rewrite(){
        $rewrite = $this->getRewrite(REWRITE_PATH);
        foreach ($rewrite as $key => $val) {
            $rewrite[$key] = F($val,'',REWRITE_PATH);
            $rewrite[$key]['filename'] = $val;
            unset($rewrite[$key]['config_url']);
        }
        $normal = array(
            'alias' => 'normal',
            'name' => '系统原始URL链接（不使用伪静态）',
            'explain' => '说明',
            'versions' => C('QSCMS_VERSION'),
            'update_time' => C('QSCMS_RELEASE'),
            'author' => '74cms'
        );
        $this->assign('normal',$normal);
        $this->assign('list',$rewrite);
        $this->display();
    }
    /**
     * [rewrite_set 设置伪静态]
     */
    public function rewrite_set(){
        $type = I('get.type','default','trim');
        if(!is_writable(CONF_PATH.'url.php')){
            $this->error('请为该文件设置读写权限“./Application/Common/Conf/url.php”');
        }
        if($type == 'normal'){
            $rewrite_list['alias'] = 'normal';
            D('Page')->where(array('id'=>array('gt',0)))->setfield('rewrite','');
            $config['URL_MODEL'] = 0;
            $config['URL_HTML_SUFFIX'] = '.html';
        }elseif($type == 'default'){
            $rewrite_list['alias'] = 'default';
            D('Page')->where(array('id'=>array('gt',0)))->setfield('rewrite','');
            $config['URL_MODEL'] = 2;
            $config['URL_HTML_SUFFIX'] = '.html';
        }else{
            $rewrite_list = F($type,'',REWRITE_PATH);
            if($rewrite_list['config_url']){
                if(false === $page = F('page_list')) $page = D('Page')->page_cache();
                foreach ($page as $key => $val) {
                    $rewrite = $rewrite_list['config_url'][$val['alias']];
                    if($rewrite){
                        $str = $rewrite['rewrite'];
                    }else{
                        $str = '';
                    }
                    D('Page')->where(array('id'=>$val['id']))->setfield('rewrite',$str);
                }
                foreach ($rewrite_list['config_url'] as $val) {
                    if($val['url_reg'] && $val['url']) $url[$val['url_reg']] = $val['url'];
                }
            }else{
                $this->error('请正确设置伪静态规则！');
            }
            $config['URL_MODEL'] = 2;
            $config['URL_HTML_SUFFIX'] = $rewrite_list['suffix'];
        }
        $sys_url = require CONF_PATH.'sys_url.php';
        if($url) $sys_url = array_merge($url,$sys_url);
        $config['URL_ROUTE_RULES'] = $sys_url;
        if($this->update_config($config,CONF_PATH.'url.php')){
            D('Config')->where(array('name'=>'rewrite_type'))->setField('value',$rewrite_list['alias']);
            $this->success("设置成功！");
        }else{
            $this->error('设置失败！');
        }
    }
    /**
     * [rewrite_details 伪静态详情]
     */
    public function rewrite_details(){
        $type = I('get.type','default','trim');
        $rewrite = F($type,'',REWRITE_PATH);
        $page = D('Page')->page_cache();
        $this->assign('info',$rewrite);
        $this->assign('page',$page);
        $this->display();
    }
    /**
     * [rewrite_delete 删除伪静态]
     */
    public function rewrite_delete(){
        $type = I('get.type','','trim');
        if($type == 'default') $this->error('不能删除系统默认伪静态规则！');
        F($type,NULL,REWRITE_PATH);
    }
    //获取文件目录列表,该方法返回数组
    protected function getRewrite($dir) {
        if (false != ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".." && false !== strpos($file,".php")) {
                    $dirArray[]=preg_replace('/(\w+).php/','\\1',$file);
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }
}
?>