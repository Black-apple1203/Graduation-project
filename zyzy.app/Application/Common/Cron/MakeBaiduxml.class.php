<?php
/*
* 74cms 计划任务 生成百度开放平台数据
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
defined('THINK_PATH') or exit();
ignore_user_abort(true);
class MakeBaiduxml{
	public function run(){
		$xmlset = D('Baiduxml')->get_baiduxml_cache();
		$xmlorder=$xmlset['order'];
		$baiduxml = new \Common\qscmslib\baiduXML();
		$this->makebaidu($baiduxml,$xmlorder,0,$xmlset['xmlpagesize'],1,0,$xmlset);
	}
	protected function makebaidu($baiduxml,$xmlorder,$start,$size,$li=1,$t=0,$xmlset){
		$xmldir = QSCMS_DATA_PATH.$xmlset['xmldir'];
		if ($xmlorder=='1')
		{
		$order="addtime DESC";
		}
		else
		{
		$order="refreshtime DESC";
		}
		$limit=$start.",".$size;
		if(C('qscms_jobs_display')==1){
			$list_map['audit'] = 1;
		}else{
			$list_map['id'] = array('gt',0);
		}
		$jobslist = D('Jobs')->where($list_map)->order($order)->limit($limit)->select();
		foreach ($jobslist as $key => $value) {
			$t++;
			$contact = M('JobsContact')->where('pid='.$value['id'])->limit(1)->select();
			$com = M('CompanyProfile')->where('id='.$value['company_id'])->limit(1)->select();
			$category = M('CategoryJobs')->where('id='.$value['category'])->limit(1)->select();
			$subclass = M('CategoryJobs')->where('id='.$value['subclass'])->limit(1)->select();
			$value['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$value['id']));
			$x=array($value['jobs_url'],date("Y-m-d",$value['refreshtime']),$value['jobs_name'],date("Y-m-d",$value['deadline']),$value['contents'],$value['nature_cn'],   str_replace('/','',$value['district_cn']),$value['companyname'],$contact['email'],$category['categoryname'],$subclass['categoryname'],$value['education_cn'],$value['experience_cn'],date("Y-m-d",$value['addtime']),date("Y-m-d",$value['deadline']),$value['trade_cn'],$com['nature_cn'],C('qscms_site_name'),C('qscms_site_domain').C('qscms_site_dir'));
			foreach ($x as $key => $value) {
				$x[$key] = strip_tags(str_replace("&","&amp;",$value));
			}
			if (in_array('',$x))
			{
				continue;
			}
			else
			{
				$baiduxml->XML_url($x);
				$rowid=$value['id'];
				if ($xmlset['xmlmax']>0 && $t>=$xmlset['xmlmax'])
				{
					for($b=1;$b<$li;$b++)
					{
						$xmlfile=$xmldir.$xmlset['xmlpre'].$b.'.xml';
						$xmlfile=ltrim($xmlfile,'../');
						$xmlfile=ltrim($xmlfile,'..\\');
						$atime=filemtime($xmldir.$xmlset['xmlpre'].$b.'.xml');
						$atime=date("Y-m-d",$atime);
						$index[]=array(C('qscms_site_domain').C('qscms_site_domain').$xmlfile,$atime);
					}
				$baiduxml->XML_index_put($xmldir.$xmlset['indexname'],$index);
				return true;
				}
			}
		}
		if (empty($rowid))
		{
			for($b=1;$b<$li;$b++)
			{
				$xmlfile=$xmldir.$xmlset['xmlpre'].$b.'.xml';
				$xmlfile=ltrim($xmlfile,'../');
				$xmlfile=ltrim($xmlfile,'..\\');
				$atime=filemtime($xmldir.$xmlset['xmlpre'].$b.'.xml');
				$atime=date("Y-m-d",$atime);
				$index[]=array(C('qscms_site_domain').C('qscms_site_domain').$xmlfile,$atime);
			}
			$baiduxml->XML_index_put($xmldir.$xmlset['indexname'],$index);
			return true;
		}
		else
		{
			$xmlname=$xmldir.$xmlset['xmlpre'].$li.'.xml';
			if ($baiduxml->XML_put($xmlname))
			{
			$li++;
			return $this->makebaidu($xmlorder,$t,$xmlset['xmlpagesize'],$li,$t,$xmlset);
			}
		}
	}
}
?>