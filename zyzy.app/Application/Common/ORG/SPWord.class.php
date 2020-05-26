<?php
 /*
 * 74cms ���ķִ�
 * ============================================================================
 * ��Ȩ����: ��ʿ���磬����������Ȩ����
 * ��վ��ַ: http://www.74cms.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
*/
class SPWord
{
	var $maxLen = 5;
	var $minlen = 2;
	var $spchar = ' ';
	var $dicword=array();
	
	function SPWord()
	{
	$this->__construct();
	}
  
	function __construct()
	{
		$dicfile = dirname(__FILE__)."/word.txt"; 
		$fp = fopen($dicfile,'r');
		while($line = fgets($fp,256))
		{
			$line = trim($line);
			$this->dicword[strlen($line)][$line]=1;
			
		}
		fclose($fp);
	}
	
	function extracttag($str)
	{
		if (empty($str))
		{
		return '';
		}
		$spwords = explode(" ",$this->revisestr($str));
		$tag='';
		foreach($spwords as $astr)
		{
			$tag.=$this->rwhods($astr);
		}
		return $tag;
	}
	
	function rwhods($str)
	{
		$str=trim($str);
		$length = strlen(trim($str)); 
		for ($i=0;$i<$length;$i++)
		{ 
        $retstr[]= ord($str[$i]) > 127 ? trim($str[$i].$str[++$i]) : trim($str[$i]); 
   		}
		return	 $this->matchesword($retstr);
	}
	
	function matchesword($arr,$oldstr='')
	{
		if (empty($arr))
		{
		return $oldstr;
		}
		$count=count($arr);
		if ($count>$this->maxLen)
		{
		$count=$this->maxLen;
		}
		$i=$this->minlen-1;
		$w="";
		for ($c = 0; $c <=$i-1; $c++)
		{
			$w.=$arr[$c];
		}
		for ($i=$this->minlen-1; $i <=$count-1; $i++)
		{
			$w.=$arr[$i];
			if ($this->isword($w))
			{
				$oldstr=$oldstr.$this->spchar.$w;		
			}			
		}
		if(array_shift($arr))
		{
		return $this->matchesword($arr,$oldstr);
		}
	}
	
	function revisestr($str)
	{
		$str = preg_replace("/[[:punct:]]/i", ' ', $str);
		$str = str_replace(PHP_EOL, ' ', $str);
		$str = str_replace(array(',','��', '��','��','��','��','��','��'),' ',$str); 
		return  $str;
	}
	
	function pad($str)
	{
		if (empty($str))
		{
			return '';
		}
		else
		{
			$str=explode(" ",$str);
			if (is_array($str))
			{
			$str=array_unique($str);
			$str=array_map(array(__CLASS__,'wordpad'),$str);			
			return implode($this->spchar,$str);
			}			
		}
	}
	
	function wordpad($str)
	{
		if (empty($str))
		{
		return '';
		}
		$leng=strlen($str);
		if ($leng>=8)
		{
		return $str;
		}
		else
		{
		$l=4-($leng/2);
		return str_pad($str,$leng+$l,'0');
		}
	}
	

	
	function isword($word)
	{
		$slen = strlen($word);
		if($slen > $this->maxLen*2)
		{
		return false;
		}
		else
		{
		return isset($this->dicword[$slen][$word]);
		}
	}
  
  
 
}
?>