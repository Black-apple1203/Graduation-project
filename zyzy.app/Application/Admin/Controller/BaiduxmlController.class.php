<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class BaiduxmlController extends BackendController {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('Baiduxml');
    }
    /**
     * 资源文档列表
     */
    public function index(){
    	$xmlset = $this->_mod->get_baiduxml_cache();
		$flist = array();
		$xmldir = QSCMS_DATA_PATH.$xmlset['xmldir'];
		$trimxmldir=ltrim($xmldir,'../');
		$trimxmldir=ltrim($trimxmldir,'..\\');
		$flist[] =$xmlset['indexname'];
		$opendir=opendir($xmldir);
		while($file = readdir($opendir))
		{
			if(strpos($file,'.xml')!==false && $file!==$xmlset['indexname'])
			{
			$flist[] = $file;
			}
		}
		foreach($flist as $key => $file)
		{
			if (file_exists($xmldir.$file))
			{
			$flistd[$key]['file_type'] = $file==$xmlset['indexname']?'<span style="color:#FF6600">索引文档</span>':'资源文档';
			$flistd[$key]['file_size'] = round(filesize($xmldir.$file)/1024/1024,2);
			$flistd[$key]['file_time'] = filemtime($xmldir.$file);	
			$flistd[$key]['file_url'] = C('qscms_site_domain').C('qscms_site_dir').$trimxmldir.$file;
			$flistd[$key]['file_name']  = $file;
			}
		}
		$this->assign('list',$flistd);
		$this->display();
    }
    /**
     * 生成资源文档
     */
    public function make(){
    	$xmlset = $this->_mod->get_baiduxml_cache();
		$xmldir = QSCMS_DATA_PATH.$xmlset['xmldir'];
		$xmlorder=$xmlset['order'];
		if ($xmlorder=='1')
		{
		$order="addtime DESC";
		}
		else
		{
		$order="refreshtime DESC";
		}
		$jid = I('get.jid',0,'intval');
		$total = I('get.total',0,'intval');
		$err = I('get.err',0,'intval');
		$pageli = I('get.pageli',0,'intval');
		$pageli = $pageli>0?$pageli:1;
		$limit = $total.",".$xmlset['xmlpagesize'];
		if ($xmlset['xmlmax']>0 && $xmlset['xmlmax']<$xmlset['xmlpagesize'])
		{
			$limit = $total.",".$xmlset['xmlmax'];
		}
		$baiduxml = new \Common\qscmslib\baiduXML();
		if(C('qscms_jobs_display')==1){
			$list_map['audit'] = 1;
		}else{
			$list_map['id'] = array('gt',0);
		}
		$jobslist = D('Jobs')->where($list_map)->order($order)->limit($limit)->select();	
		foreach ($jobslist as $key => $value) {
			$total++;
			$contact = M('JobsContact')->where('pid='.$value['id'])->limit(1)->select();
			$com = M('CompanyProfile')->where('id='.$value['company_id'])->limit(1)->select();
			$category = M('CategoryJobs')->where('id='.$value['category'])->limit(1)->select();
			$subclass = M('CategoryJobs')->where('id='.$value['subclass'])->limit(1)->select();
			$value['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$value['id']));
			$x=array($value['jobs_url'],date("Y-m-d",$value['refreshtime']),$value['jobs_name'],date("Y-m-d",$value['deadline']),$value['contents'],$value['nature_cn'],   str_replace('/','',$value['district_cn']),$value['companyname'],$contact['email'],$category['categoryname'],$subclass['categoryname'],$value['education_cn'],$value['experience_cn'],date("Y-m-d",$value['addtime']),date("Y-m-d",$value['deadline']),str_replace('~','-',$value['wage_cn']),$value['trade_cn'],$com['nature_cn'],$_CFG['site_name'],$_CFG['site_domain'].$_CFG['site_dir']);
			foreach ($x as $key => $value) {
				$x[$key] = strip_tags(str_replace("&","&amp;",$value));
			}
			if (in_array('',$x))
			{
			$err++;
			continue;
			}
			$baiduxml->XML_url($x);
			$rowid=$value['id'];
		}
		if (empty($rowid))
		{
			if ($total===0)
			{
				$this->error('没有数据可以生成！');
				exit;
			}
			else
			{
				for($b=1;$b<$pageli;$b++)
				{
					$xmlfile=$xmldir.$xmlset['xmlpre'].$b.'.xml';
					$xmlfile=ltrim($xmlfile,'../');
					$xmlfile=ltrim($xmlfile,'..\\');
					$atime=filemtime($xmldir.$xmlset['xmlpre'].$b.'.xml');
					$atime=date("Y-m-d",$atime);
					$index[]=array(C('qscms_site_domain').C('qscms_site_domain').$xmlfile,$atime);
				}
				$baiduxml->XML_index_put($xmldir.$xmlset['indexname'],$index);
				$pageli--;
				$total=$total-$err;
				$this->success("生成完成！总计生成{$pageli}个资源文档，1个索引文档，{$total}个职位生成成功，{$err}个职位生成失败",U('baiduxml/index'));
				exit;
			}	
		}
		else
		{
			$xmlname=$xmldir.$xmlset['xmlpre'].$pageli.'.xml';
			if ($baiduxml->XML_put($xmlname))
			{
				$pageli++;
				$this->success("{$xmlname}生成成功,系统将自动继续...",U('baiduxml/make',array('total'=>$total,'pageli'=>$pageli,'err'=>$err)));
				exit;
			}
			else
			{
				$this->error('生成失败！');
				exit;
			}
		}	
    }
    /**
     * 删除资源文档
     */
    public function delete(){
    	$xmlset = $this->_mod->get_baiduxml_cache();
		$xmldir = QSCMS_DATA_PATH.$xmlset['xmldir'];
		$file_name=I('post.file_name');
		if (empty($file_name))
		{
			$this->error('请选择文档！');exit;
		}
		if (!is_array($file_name)) $file_name=array($file_name);
		foreach($file_name as $f )
		{
			@unlink($xmldir.$f);
		}
		$this->success('删除成功！');exit;
    }
    /**
     * 配置
     */
    public function config(){
    	if(IS_POST){
    		foreach (I('post.') as $key => $val) {
	        	$val = is_array($val) ? serialize($val) : $val;
	        	$this->_mod->where(array('name' => $key))->save(array('value' => $val));
	        }
	        $this->success(L('operation_success'));exit;
    	}
    	$info = $this->_mod->get_baiduxml_cache();
    	$this->assign('info',$info);
    	$this->display();
    }
}
?>