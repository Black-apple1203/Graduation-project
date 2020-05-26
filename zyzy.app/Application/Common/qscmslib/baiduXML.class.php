<?php
 /*
 * 74cms 百度开放平台
 * ============================================================================
 * 版权所有: 骑士网络，并保留所有权利。
 * 网站地址: http://www.74cms.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
*/
namespace Common\qscmslib;
class baiduXML
{
	var $XML_head = '';
	var $XML_foot = '';
	var $XML = '';
	var $encoding = 'GBK';
	function BaiduXML()
	{
	$this->__construct();
	}
	function __construct()
	{
		$this->XML_head  = "<?xml version=\"1.0\" encoding=\"{$this->encoding}\"?>\n";
		$this->XML_head .= "<urlset>\n";
		$this->XML_foot  = "</urlset>";
	}	
	function XML_url($str)
	{
	$this->XML.="<url>\n";
	$this->XML.="<loc>{$str[0]}</loc>\n";
	$this->XML.="<lastmod>{$str[1]}</lastmod>\n";
	$this->XML.="<changefreq>daily</changefreq>\n";
	$this->XML.="<priority>0.8</priority>\n";
	$this->XML.="<data>\n";
	$this->XML.="<display>\n";
	$this->XML.="<title>{$str[2]}</title>\n";
	$this->XML.="<expirationdate>{$str[3]}</expirationdate>\n";
	$this->XML.="<description>{$str[4]}</description>\n";
	$this->XML.="<type>{$str[5]}</type>\n";
	$this->XML.="<city>{$str[6]}</city>\n";
	$this->XML.="<employer>{$str[7]}</employer>\n";
	$this->XML.="<email>{$str[8]}</email>\n";
	$this->XML.="<jobfirstclass>{$str[9]}</jobfirstclass>\n";
	$this->XML.="<jobsecondclass>{$str[10]}</jobsecondclass>\n";
	$this->XML.="<education>{$str[11]}</education>\n";
	$this->XML.="<experience>{$str[12]}</experience>\n";
	$this->XML.="<startdate>{$str[13]}</startdate>\n";
	$this->XML.="<enddate>{$str[14]}</enddate>\n";
	$this->XML.="<salary>{$str[15]}</salary>\n";
	$this->XML.="<industry>{$str[16]}</industry>\n";
	$this->XML.="<employertype>{$str[17]}</employertype>\n";
	$this->XML.="<source>{$str[18]}</source>\n";
	$this->XML.="<sourcelink>{$str[19]}</sourcelink>\n";
	$this->XML.="</display>\n";
	$this->XML.="</data>\n";
	$this->XML.="</url>\n";
	}
	function XML_index_put($path,$index=array())
	{
	$this->XML="<?xml version=\"1.0\" encoding=\"{$this->encoding}\"?>\n"; 
	$this->XML.="<sitemapindex>\n";
	foreach ($index as $a)
	{
	$this->XML.="<sitemap>\n"; 
	$this->XML.="<loc>{$a[0]}</loc> \n"; 
	$this->XML.="<lastmod>{$a[1]}</lastmod> \n"; 
	$this->XML.="</sitemap>\n";
	} 
	$this->XML.="</sitemapindex>\n"; 
	return file_put_contents($path,$this->XML, LOCK_EX);
	}
	function XML_put($path)
	{
		$return=file_put_contents($path,$this->XML_head.$this->XML.$this->XML_foot, LOCK_EX);
		$this->XML='';
		return $return;
	}
}
?>