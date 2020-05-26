<?php
namespace Common\Model;
use Think\Model;
class MembersLogModel extends Model{
	public $type_arr = array(
			1000=>array('type'=>'注册','content'=>'注册成功'),
			1001=>array('type'=>'登录','content'=>'登录成功'),
			1002=>array('type'=>'发布职位','content'=>'发布了职位(id:%s)'),
			1003=>array('type'=>'修改职位','content'=>'修改了职位(id:%s)'),
			1004=>array('type'=>'保存企业资料','content'=>'保存企业资料'),
			1005=>array('type'=>'上传营业执照','content'=>'上传营业执照'),
			1006=>array('type'=>'刷新职位','content'=>'刷新职位(id:%s,方式:%s)'),
			1007=>array('type'=>'关闭职位','content'=>'关闭职位(id:%s)'),
			1008=>array('type'=>'恢复显示职位','content'=>'恢复显示职位(id:%s)'),
			1009=>array('type'=>'删除职位','content'=>'删除职位(id:%s)'),
			1010=>array('type'=>'邀请面试','content'=>'邀请面试(职位id:%s,简历id:%s)'),
			1011=>array('type'=>'删除邀请面试记录','content'=>'删除邀请面试记录(记录id:%s)'),
			1012=>array('type'=>'下载简历','content'=>'下载简历(简历id:%s,方式:%s)'),
			1013=>array('type'=>'删除职位申请','content'=>'删除职位申请(记录id:%s)'),
			1014=>array('type'=>'简历保存到电脑','content'=>'将简历保存到电脑(简历id:%s)'),
			1015=>array('type'=>'修改过滤条件','content'=>'修改职位接收简历的过滤条件(职位id:%s)'),
			1016=>array('type'=>'已收到简历设为已查看','content'=>'将已收到简历设为已查看(记录id:%s)'),
			1017=>array('type'=>'删除已下载简历','content'=>'删除已下载简历(记录id:%s)'),
			1018=>array('type'=>'收藏简历','content'=>'收藏简历(简历id:%s)'),
			1019=>array('type'=>'删除收藏简历','content'=>'删除收藏简历(记录id:%s)'),
			1020=>array('type'=>'删除浏览过的简历','content'=>'删除浏览过的简历(记录id:%s)'),
			1021=>array('type'=>'删除谁看过我的职位','content'=>'删除谁看过我的职位(记录id:%s)'),
			1022=>array('type'=>'删除企业风采','content'=>'删除企业风采(记录id:%s)'),
			1023=>array('type'=>'修改企业风采备注','content'=>'修改企业风采备注(记录id:%s)'),
			1024=>array('type'=>'查看系统消息','content'=>'查看系统消息(消息id:%s)'),
			1025=>array('type'=>'删除系统消息','content'=>'删除系统消息(消息id:%s)'),
			1026=>array('type'=>'发送反馈消息','content'=>'发送反馈消息(发送者uid:%s,接收者uid:%s,消息内容:%s)'),
			1027=>array('type'=>'删除反馈消息','content'=>'删除反馈消息(消息id:%s)'),
			1028=>array('type'=>'发送简历到邮箱','content'=>'发送简历到邮箱(简历id:%s,接收邮箱:%s)'),
			1029=>array('type'=>'标记收到的简历','content'=>'标记收到的简历(记录id:%s,标记为:%s)'),
			1030=>array('type'=>'标记已下载简历','content'=>'标记已下载简历(记录id:%s,标记为:%s)'),
			1031=>array('type'=>'更换企业模板','content'=>'更换企业模板(模板目录名:%s)'),
			1032=>array('type'=>'举报简历','content'=>'举报简历(简历id:%s)'),
			1033=>array('type'=>'预定招聘会展位','content'=>'预定招聘会展位(招聘会id:%s,展位id:%s)'),
			8001=>array('type'=>'邮箱验证','content'=>'邮箱验证'),
			8002=>array('type'=>'手机验证','content'=>'手机验证'),
			8003=>array('type'=>'签到','content'=>'签到'),
			9001=>array('type'=>'创建订单','content'=>'创建订单(订单号:%s,付款方式:%s)'),
			9002=>array('type'=>'开通服务','content'=>'开通服务(服务名称:%s,付款方式:%s)'),
			9003=>array('type'=>'索取发票','content'=>'索取发票(订单ID:%s)'),
			9004=>array('type'=>'取消订单','content'=>'取消订单(订单号:%s)'),
			9005=>array('type'=>'删除订单','content'=>'删除订单(订单号:%s)'),
			9006=>array('type'=>'申请退出诚聘通','content'=>'申请退出诚聘通会员'),
			2001=>array('type'=>'刷新简历','content'=>'刷新简历(简历id:%s)'),
			2002=>array('type'=>'添加屏蔽企业','content'=>'添加屏蔽企业关健字:%s'),
			2003=>array('type'=>'删除屏蔽企业','content'=>'删除屏蔽企业关健字(关键字id:%s)'),
			2004=>array('type'=>'保存隐私设置','content'=>'保存隐私设置(简历id:%s)'),
			2005=>array('type'=>'设置简历委托','content'=>'设置简历委托(简历id:%s)'),
			2006=>array('type'=>'取消简历委托','content'=>'取消简历委托(简历id:%s)'),
			2007=>array('type'=>'更换简历模板','content'=>'更换简历模板(简历id:%s,模板目录名:%s)'),
			2008=>array('type'=>'删除简历','content'=>'删除简历(简历id:%s)'),
			2009=>array('type'=>'设置默认简历','content'=>'设置默认简历(简历id:%s)'),
			2010=>array('type'=>'创建简历','content'=>'创建简历(简历id:%s)'),
			2011=>array('type'=>'复制简历','content'=>'复制简历(被复制简历id:%s,新简历id:%s)'),
			2012=>array('type'=>'修改简历','content'=>'修改简历(简历id:%s)'),
			2013=>array('type'=>'删除教育经历','content'=>'删除教育经历(简历id:%s)'),
			2014=>array('type'=>'删除工作经历','content'=>'删除工作经历(简历id:%s)'),
			2015=>array('type'=>'删除培训经历','content'=>'删除培训经历(简历id:%s)'),
			2016=>array('type'=>'删除语言能力','content'=>'删除语言能力(简历id:%s)'),
			2017=>array('type'=>'删除证书','content'=>'删除证书(简历id:%s)'),
			2018=>array('type'=>'添加教育经历','content'=>'添加教育经历(简历id:%s)'),
			2019=>array('type'=>'修改教育经历','content'=>'修改教育经历(简历id:%s)'),
			2020=>array('type'=>'添加工作经历','content'=>'添加工作经历(简历id:%s)'),
			2021=>array('type'=>'修改工作经历','content'=>'修改工作经历(简历id:%s)'),
			2022=>array('type'=>'添加培训经历','content'=>'添加培训经历(简历id:%s)'),
			2023=>array('type'=>'修改培训经历','content'=>'修改培训经历(简历id:%s)'),
			2024=>array('type'=>'保存语言能力','content'=>'保存语言能力(简历id:%s)'),
			2025=>array('type'=>'添加证书','content'=>'添加证书(简历id:%s)'),
			2026=>array('type'=>'修改证书','content'=>'修改证书(简历id:%s)'),
			2027=>array('type'=>'保存个人简介','content'=>'保存个人简介(简历id:%s)'),
			2028=>array('type'=>'保存特长标签','content'=>'保存特长标签(简历id:%s)'),
			2029=>array('type'=>'删除简历附件','content'=>'删除简历附件(简历id:%s)'),
			2030=>array('type'=>'保存附件','content'=>'保存附件(简历id:%s)'),
			2031=>array('type'=>'删除word简历','content'=>'删除word简历(简历id:%s)'),
			2032=>array('type'=>'简历外发','content'=>'简历外发(简历id:%s,接收邮箱:%s)'),
			2033=>array('type'=>'删除简历外发','content'=>'删除简历外发(记录id:%s)'),
			2034=>array('type'=>'面试邀请设为已查看','content'=>'面试邀请设为已查看(记录id:%s)'),
			2035=>array('type'=>'删除收藏的职位','content'=>'删除收藏的职位(记录id:%s)'),
			2036=>array('type'=>'删除谁在关注我','content'=>'删除谁在关注我(记录id:%s)'),
			2037=>array('type'=>'删除浏览过的职位','content'=>'删除浏览过的职位(记录id:%s)'),
			2038=>array('type'=>'删除关注的企业','content'=>'删除关注的企业(记录id:%s)'),
			2039=>array('type'=>'保存职位订阅器','content'=>'保存职位订阅器(记录id:%s)'),
			2040=>array('type'=>'退订职位订阅器','content'=>'退订职位订阅器(记录id:%s)'),
			2041=>array('type'=>'订阅职位订阅器','content'=>'订阅职位订阅器(记录id:%s)'),
			2042=>array('type'=>'举报职位','content'=>'举报职位(职位id:%s)'),
			2043=>array('type'=>'修改个人资料','content'=>'修改个人资料'),
			2044=>array('type'=>'修改了个人头像','content'=>'修改了个人头像')
		);
	protected $_validate = array(
		array('log_uid,log_utype,log_value','identicalNull','',0,'callback'),
	);
	protected $_auto = array (
		array('log_addtime','time',1,'function'),
		array('log_ip','get_client_ip',1,'callback'),
		array('log_address','get_address',1,'callback'),
	);
   	/*
		获取IP地址以及端口号
	*/
	protected function get_client_ip($type = 0,$adv=false) {
	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if($adv){
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            $pos    =   array_search('unknown',$arr);
	            if(false !== $pos) unset($arr[$pos]);
	            $ip     =   trim($arr[0]);
	        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	            $ip     =   $_SERVER['REMOTE_ADDR'];
	        }
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    $port = $_SERVER['REMOTE_PORT'];
	    return $ip[$type].':'.$port;
    }
	/*
		根据ip 获取地址
	*/
	protected function get_address()
	{
		$Ip = new \Common\ORG\IpLocation('UTFWry.dat');
		$rst = $Ip->getlocation();
		return $rst['country'];
	}
	/*
		查询会员日志单条
	*/
	public function get_members_log_one($data)
	{
		return $this->where($data)->find();
	}
	/*
		获取会员日志 
		@$data members_log 中的查询条件
	*/
	public function get_members_log($data,$pagesize=10)
	{
		$rst['count'] = $this->where($data)->count();
		if($rst['count']){
			$pager =  pager($rst['count'], $pagesize);
			$rst['list'] = $this->where($data)->order('log_id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
			$rst['page'] = $pager->fshow();
		}
		return $rst;
	}
}
?>