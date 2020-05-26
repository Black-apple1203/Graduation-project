<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class DownloadController extends FrontendController{
	public function _initialize() {
        parent::_initialize();
    }
    public function word_resume(){
    	$id = I('get.id',0,'intval');
    	!$id && $this->error('请选择简历！');
    	$where = array('简历id' => $id);
        $resume_mod = new \Common\qscmstag\resume_showTag($where);
        $resume = $resume_mod->run();
    	if(!$resume || !$resume['_word_resume']) $this->error('word简历不存在！');
    	if(!$resume['show_contact']) $this->error('获取简历联系方式以后方可下载!');
    	ob_end_clean();
        $hfile = fopen($resume['_word_resume'], "rb") or die("Can not find file\n");
    	header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: application/doc");
		Header("Accept-Ranges: bytes");
		Header("Content-Length: ".filesize($resume['_word_resume']));
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename=$resume['word_resume_title'];
		$filename = urlencode($filename);
		$filename = str_replace("+", "%20", $filename);
		if (preg_match("/MSIE/", $ua)) {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		while (!feof($hfile)) {
			echo fread($hfile, 32768);
		}
		fclose($hfile);
    }
    public function adv_word_resume(){
    	$id = I('get.id',0,'intval');
    	!$id && $this->error('请选择高级简历！');
    	if(!C('visitor.uid')) $this->error('登录后可下载简历！');
        $resume = M('AdvResume')->field('word_resume,word_resume_title')->where(array('id'=>$id))->find();
    	if(!$resume || !$resume['word_resume']) $this->error('word简历不存在！');
    	if(C('visitor.uid') != $resume['uid']) $this->error('无权下载该简历!');
    	$resume['word_resume'] = attr($resume['word_resume'],'word_resume');
    	ob_end_clean();
        $hfile = fopen($resume['word_resume'], "rb") or die("Can not find file\n");
    	header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: application/doc");
		Header("Accept-Ranges: bytes");
		Header("Content-Length: ".filesize($resume['word_resume']));
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename=$resume['word_resume_title'];
		$filename = urlencode($filename);
		$filename = str_replace("+", "%20", $filename);
		if (preg_match("/MSIE/", $ua)) {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		while (!feof($hfile)) {
			echo fread($hfile, 32768);
		}
		fclose($hfile);
    }
    public function hrtools(){
    	$id = I('get.id',0,'intval');
    	!$id && $this->error('请选择Hr工具箱内容！');
    	$hrtools = M('Hrtools')->where(array('h_id'=>$id))->find();
    	if(!$hrtools) $this->error('Hr工具箱不存在！');
    	$hrtools['h_fileurl']=substr($hrtools['h_fileurl'],0,7)=="http://"?$hrtools['h_fileurl']:attach($hrtools['h_fileurl'],'hrtools');
    	ob_end_clean();
        $hfile = fopen($hrtools['h_fileurl'], "rb") or die("Can not find file\n");
    	header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: application/doc");
		Header("Accept-Ranges: bytes");
		Header("Content-Length: ".filesize($hrtools['h_fileurl']));
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename=$hrtools['h_filename'].'.doc';
		$filename = urlencode($filename);
		$filename = str_replace("+", "%20", $filename);
		if (preg_match("/MSIE/", $ua)) {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		while (!feof($hfile)) {
			echo fread($hfile, 32768);
		}
		fclose($hfile);
    }
}
?>