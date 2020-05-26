<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class SmsQueueController extends BackendController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Smsqueue');
        $this->_name = 'Smsqueue';
    }
    public function index(){
        $map = array();
        $s_type=I('get.s_type',0,'intval');
        $key_type = I('request.key_type',0,'intval');
        $key = I('request.key','','trim');
        $order_by = 's_id desc';
        if ($key && $key_type>0)
        {
            switch($key_type){
                case 1:
                    $map['s_body']=array('like','%'.$key.'%');break;
                case 2:
                    $map['s_mobile']=array('eq',$key);break;
            }
        }
        else
        {
            $s_type>0 && $map['s_type'] = array('eq',$s_type);
        }
        parent::_list($this->_mod,$map,$order_by,"*",'','',10,'_format_list');
        $this->display();
    }
    /**
     * 格式化列表
     */
    public function _format_list($list){
        $arr = array();
        foreach ($list as $key => $value) {
            $value['s_body']=$value['s_body'];
            $value['s_body_']=cut_str(strip_tags($value['s_body']),18,0,"...");
            $arr[] = $value;
        }
        return $arr;
    }
    /**
     * 添加发送任务
     */
    public function smsqueue_add(){
        if(IS_POST){
            $time = time();
            $setsqlarr['s_sms']=I('post.s_sms','','trim')?I('post.s_sms','','trim'):$this->error('手机号码必须填写！');
            $s_body=I('post.s_body','','trim')?I('post.s_body','','trim'):$this->error('请填写短信内容');
            mb_strlen($s_body,'utf-8')>70 && $this->error('短信内容超过70个字，请重新输入！');
            $mobile_arr=explode('|',$setsqlarr['s_sms']);
            $mobile_arr=array_unique($mobile_arr);
            $m_arr = array();
            foreach($mobile_arr as $key => $value){
                fieldRegex($value,'mobile') && $m_arr[] = $value;
            }
            $num = 0;
            if(!empty($m_arr)){
                foreach ($m_arr as $key => $value) {
                    $smssqlarr['s_type']=1;
                    $smssqlarr['s_body']=$s_body;
                    $smssqlarr['s_addtime']=$time;
                    $smssqlarr['s_mobile']=$value;
                    $smssqlarr['s_tplid']=I('post.s_tplid','','trim');
                    $this->_mod->add($smssqlarr);
                    $num++;
                }
            }
            $this->success("成功添加{$num}条任务！");
            exit;
        }else{
            $label[]=array('{sitename}','网站名称');
            $label[]=array('{sitedomain}','网站域名');
            $label[]=array('{address}','联系地址');
            $label[]=array('{tel}','联系电话');
            $this->assign('label',$label);
        }
        $this->display();
    }
    /**
     * 修改发送任务
     */
    public function smsqueue_edit(){
        if(IS_POST){
            $setsqlarr['s_sms']=I('post.s_sms','','trim')?I('post.s_sms','','trim'):$this->error('手机号码必须填写！');
            $s_body=I('post.s_body','','trim')?I('post.s_body','','trim'):$this->error('请填写短信内容');
            mb_strlen($s_body,'utf-8')>70 && $this->error('短信内容超过70个字，请重新输入！');
            if (fieldRegex($setsqlarr['s_sms'],'mobile'))
            {
                $_POST['s_type']=1;
                $_POST['s_body']=$s_body;
                $_POST['s_addtime']=time();
                $_POST['s_mobile']=$setsqlarr['s_sms'];
            }else{
                $this->error('手机号格式错误！');
            }
        }else{
            $label[]=array('{sitename}','网站名称');
            $label[]=array('{sitedomain}','网站域名');
            $label[]=array('{address}','联系地址');
            $label[]=array('{tel}','联系电话');
            $this->assign('label',$label);
        }
        parent::edit();
    }
    /**
     * 批量添加发送任务
     */
    public function smsqueue_batchadd(){
        if(IS_POST){
            $time = time();
            $s_body=I('post.s_body','','trim')?I('post.s_body','','trim'):$this->error('请填写短信内容');
            mb_strlen($s_body,'utf-8')>70 && $this->error('短信内容超过70个字，请重新输入！');
            $selutype=I('post.selutype',0,'intval');
            $selsettr=I('post.selsettr',0,'intval');
            $map = array();
            if ($selutype>0)
            {
                $map['utype'] = $selutype;
            }   
            if ($selsettr>0)
            {
                $data=strtotime("-{$selsettr} day");
                $map['last_login_time'] = array('lt',$data);
            }
            $select = D('Members');
            if(!empty($map)){
                $select = $select->where($map);
            }
            $num = 0;
            $result = $select->select();
            foreach ($result as $key => $value) {
                if(fieldRegex($value['mobile'],'mobile')){
                    $smssqlarr['s_uid']=$value['uid'];
                    $smssqlarr['s_type']=1;
                    $smssqlarr['s_body']=$s_body;
                    $smssqlarr['s_addtime']=$time;
                    $smssqlarr['s_mobile']=$value['mobile'];
                    $smssqlarr['s_tplid']=I('post.s_tplid','','trim');
                    $this->_mod->add($smssqlarr);
                    $num++;
                }
            }
            $this->success("成功添加{$num}条任务！");
            exit;
        }else{
            $this->display();
        }
    }
    /**
     * 批量发送
     */
    public function totalsend(){
        $sendtype=I('post.sendtype',1,'intval');
        $intervaltime=I('post.intervaltime',3,'intval');
        $sendmax=I('post.sendmax',0,'intval');
        $senderr=I('post.senderr',0,'intval');
        if ($sendmax>0)
        {
            $limit = $sendmax;
        }else{
            $limit = false;
        }
        if ($sendtype==1)
        {
            $id=I('post.id');
            if (empty($id))
            {
                $this->error("请选择项目！",1);
            }
            if(!is_array($id)) $id=array($id);
            $sqlin=implode(",",$id);
            if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
            {
                $select = $this->_mod->where(array('s_id'=>array('in',$sqlin)));
                if($limit){
                    $select = $select->limit($limit);
                }
                $result = $select->select();
                $idarr = array();
                foreach ($result as $key => $value) {
                    $idarr[] = $value['s_id'];
                }
                if (empty($idarr))
                {
                    $this->error("没有可发送的短信");
                }
                @file_put_contents(RUNTIME_PATH."Temp/sendsms.txt", serialize($idarr));
                $this->redirect(U('send',array('senderr'=>$senderr,'intervaltime'=>$intervaltime)));
            }
            
        }
        elseif ($sendtype==2)
        {
            $select = $this->_mod->where(array('s_type'=>1));
            if($limit){
                $select = $select->limit($limit);
            }
            $result = $select->select();
            $idarr = array();
            foreach ($result as $key => $value) {
                $idarr[] = $value['s_id'];
            }
            if (empty($idarr))
            {
                $this->error("没有可发送的短信");
            }
            @file_put_contents(RUNTIME_PATH."Temp/sendsms.txt", serialize($idarr));
            $this->redirect(U('send',array('senderr'=>$senderr,'intervaltime'=>$intervaltime)));
        }
        elseif ($sendtype==3)
        {
            $select = $this->_mod->where(array('s_type'=>3));
            if($limit){
                $select = $select->limit($limit);
            }
            $result = $select->select();
            $idarr = array();
            foreach ($result as $key => $value) {
                $idarr[] = $value['s_id'];
            }
            if (empty($idarr))
            {
                $this->error("没有可发送的短信");
            }
            @file_put_contents(RUNTIME_PATH."Temp/sendsms.txt", serialize($idarr));
            $this->redirect(U('send',array('senderr'=>$senderr,'intervaltime'=>$intervaltime)));
        }
    }
    /**
     * 执行发送
     */
    public function send(){
        $sendtype=I('get.sendtype',1,'intval');
        $intervaltime=I('get.intervaltime',3,'intval');
        $tempdir=RUNTIME_PATH."Temp/sendsms.txt";
        $content = file_get_contents($tempdir);
        $idarr = unserialize($content);
        $totalid=count($idarr);
        if (empty($idarr))
        {
            $this->success('任务执行完毕！',U('index'));
            exit;
        }
        else
        {
            $s_id=array_shift($idarr);
            @file_put_contents($tempdir,serialize($idarr));
            $sms = $this->_mod->where(array('s_id'=>array('eq',intval($s_id))))->find();
            if($sms['s_tplid']){
                $data = array('mobile'=>$sms['s_mobile'],'tpl'=>$sms['s_body'],'tplId'=>$sms['s_tplid'],'data'=>array());
                $service = C('qscms_sms_other_service');
                $sms = new \Common\qscmslib\sms($service);
                $r = $sms->sendTemplateSMS('other',$data);
            }else{
                $r = D('Sms')->sendSms('other',array('mobile'=>$sms['s_mobile'],'tplStr'=>$sms['s_body']));
            }
            if($r!==true){
                $this->_mod->where(array('s_id'=>array('eq',intval($s_id))))->setField('s_type',3);
                $this->error('发生错误，准备发送下一条，剩余任务总数：'.($totalid-1),U('send',array('senderr'=>$senderr,'intervaltime'=>$intervaltime)));
            }
            else
            {
                $this->_mod->where(array('s_id'=>array('eq',intval($s_id))))->save(array('s_type'=>2,'s_sendtime'=>time()));
                $this->success('发送成功，准备发送下一条，剩余任务总数：'.($totalid-1),U('send',array('senderr'=>$senderr,'intervaltime'=>$intervaltime)));
            }
        }   
    }
    /**
     * 删除邮件队列任务
     */
    public function del(){
        $n=0;
        $deltype=I('post.deltype',0,'intval');
        $map = false;
        if ($deltype==1)
        {
            $id=I('post.id');
            if (empty($id))
            {
                $this->error("请选择项目！");
            }
            if(!is_array($id)) $id=array($id);
            $sqlin=implode(",",$id);
            if (preg_match("/^(\d{1,10},)*(\d{1,10})$/",$sqlin))
            {
                $map['s_id'] = array('in',$sqlin); 
            }
        }
        elseif ($deltype==2)
        {
            $map['s_type'] = array('eq',1); 
        }
        elseif ($deltype==3)
        {
            $map['s_type'] = array('eq',2); 
        }
        elseif ($deltype==4)
        {
            $map['s_type'] = array('eq',3); 
        }
        elseif ($deltype==5)
        {
            $map['s_id'] = array('gt',0);
        }
        $this->_del($map);
        $this->success('删除成功！');
        exit;
    }
    /**
     * 删除公用方法
     */
    protected function _del($map=false){
        $model = $this->_mod;
        if($map){
            $model = $model->where($map);
        }
        $model->delete();
    }
    /**
     * 导入号码
     */
    public function import_num(){
        $file = $_FILES['number_file']['tmp_name'];
        $content = file_get_contents($file);
        $array =explode("\r\n", $content);
        foreach ($array as $key => $value) {
            $array1[] = trim($value);
        }
        $str = implode("|",$array1);
        $str = trim($str,"|");
        $this->assign('s_sms',$str);
        $this->display('smsqueue_add');
    }
    /**
     * 导出信息
     */
    public function export_info(){
        $selutype = I('post.selutype',0,'intval');
        $selsettr = I('post.selsettr',0,'intval');
        $map = array();
        if ($selutype>0)
        {
            $map['utype'] = $selutype;
        }   
        if ($selsettr>0)
        {
            $data=strtotime("-{$selsettr} day");
            $map['last_login_time'] = array('lt',$data);
        }
        $select = D('Members');
        if(!empty($map)){
            $select = $select->where($map);
        }
        $num = 0;
        $result = $select->select();
        $total_val = count($result);
        foreach ($result as $key => $v) {
            $v['mobile']=$v['mobile']?$v['mobile']:'未填写';
            $v['email']=$v['email']?$v['email']:'未填写';
            $contents.= '★ 用户名：'.$v['username'].'                 手机号：'.$v['mobile'].'                     邮箱：'.$v['email']."\r\n\r\n"; 
        }
        $time=date("Y-m-d H:i:s",time());
        $header="===================================会员信息文件，符合条件的总计{$total_val}个，导出时间：{$time}========================================"."\r\n\r\n";
        $txt=$header.$contents;
        header("Content-type:application/octet-stream"); 
        header("Content-Disposition: attachment; filename=userinfo.txt"); 
        exit($txt);  
    }
}
?>