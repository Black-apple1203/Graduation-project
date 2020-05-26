<?php if (!defined('THINK_PATH')) exit();?><!-- public:header 以下内容 -->
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>网站后台管理中心- Powered by 74cms.com</title>
	<link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
	<meta name="author" content="骑士CMS" />
	<meta name="copyright" content="74cms.com" />
	<!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">  -->
	<link href="__ADMINPUBLIC__/css/common.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css">
	<script src="__ADMINPUBLIC__/js/jquery.min.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.highlight-3.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.vtip-min.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.modal.dialog.js"></script>
	<script type="text/javascript" src="__ADMINPUBLIC__/js/laydate/laydate.js"></script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="__ADMINPUBLIC__/js/PIE.js"></script>
	<script type="text/javascript">
		(function ($) {
			$.pie = function (name, v) {
				// 如果没有加载 PIE 则直接终止
				if (!PIE) return false;
				// 是否 jQuery 对象或者选择器名称
				var obj = typeof name == 'object' ? name : $(name);
				// 指定运行插件的 IE 浏览器版本
				var version = 9;
				// 未指定则默认使用 ie10 以下全兼容模式
				if (typeof v != 'number' && v < 9) {
					version = v;
				}
				// 可对指定的多个 jQuery 对象进行样式兼容
				if ($.browser.msie && obj.size() > 0) {
					if ($.browser.version * 1 <= version * 1) {
						obj.each(function () {
							PIE.attach(this);
						});
					}
				}
			}
		})(jQuery);
		if ($.browser.msie) {
			$.pie('.pie_about');
		};
	</script>
	<![endif]-->
	<script src="__ADMINPUBLIC__/js/jquery.disappear.tooltip.js"></script>
	<script src="__ADMINPUBLIC__/js/common.js"></script>
	<script>
		var URL = '/index.php/Admin/Index',
			SELF = '/index.php?m=Admin&amp;c=index&amp;a=panel',
			ROOT_PATH = '/index.php/Admin',
			APP	 =	 '/index.php';

		var qscms = {
			district_level : "<?php echo C('qscms_category_district_level');?>",
			default_district : "<?php echo C('qscms_default_district');?>"
		};
	</script>
	<?php echo ($synsitegroupunbindmobile); ?>
	<?php echo ($synsitegroupregister); ?>
	<?php echo ($synsitegroupedit); ?>
	<?php echo ($synsitegroup); ?>
</head>
<body>

<!-- public:header 以上内容 -->
<?php if(empty($menu_title)): ?><div class="allpagetop">后台管理中心<strong>/</strong>首页</div>
	<?php else: ?>
	<div class="allpagetop"><?php echo ($menu_title); ?><strong>/</strong><?php echo ($sub_menu_title); ?></div><?php endif; ?>
<div class="mains">
	<div class="h1tit">
		<div class="h1">
            <?php if($sub_menu['pageheader']): echo ($sub_menu["pageheader"]); ?>
                <?php else: ?>
                <!--欢迎登录 <?php echo C('qscms_site_name');?> 管理中心！--><?php endif; ?>
        </div>
        <?php if(!empty($sub_menu['menu'])): ?><div class="topnav">
                <?php if(is_array($sub_menu['menu'])): $i = 0; $__LIST__ = $sub_menu['menu'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><a href="<?php echo U($val['module_name'].'/'.$val['controller_name'].'/'.$val['action_name']); echo ($val["data"]); if($isget and $_GET): echo get_first(); endif; if($_GET['_k_v']): ?>&_k_v=<?php echo (_I($_GET['_k_v'])); endif; ?>" class="<?php echo ($val["class"]); ?>"><?php echo ($val["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                <div class="clear"></div>
            </div><?php endif; ?>
		<div class="clear"></div>
	</div>
<span id="version"></span>
<?php if(is_array($message)): $i = 0; $__LIST__ = $message;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$message): $mod = ($i % 2 );++$i; if($message['type'] == 'warning'): ?><div class="errtit"><?php echo ($message["content"]); ?></div>
        <?php else: ?>
        <div class="errtit"><?php echo ($message["content"]); ?></div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
<?php if($sms_notice): ?><div class="errtit link_blue"><?php echo ($sms_notice); ?></div><?php endif; ?>
<!-- 添加及时聊天到期提醒h-->
<?php if($im_notice): ?><div class="errtit link_blue"><?php echo ($im_notice); ?></div><?php endif; ?>
<div class="toptit">今日统计</div>
<div class="mainli num">新增个人会员<a href="<?php echo U('personal/member_list');?>" id="personal_users">&nbsp;</a></div>
<div class="mainli num">新增简历<a href="<?php echo U('personal/index');?>" id="resumes">&nbsp;</a></div>
<div class="mainli num">简历刷新次数<a href="<?php echo U('personal/index');?>" id="resume_refresh">&nbsp;</a></div>
<div class="mainli num">新增企业会员<a href="<?php echo U('company/index');?>" id="company_users">&nbsp;</a></div>
<div class="mainli num">新增职位<a href="<?php echo U('jobs/index');?>" id="jobs">&nbsp;</a></div>
<div class="mainli num">简历下载量<a href="<?php echo U('ResumeDown/index');?>" id="resume_down">&nbsp;</a></div>
<div class="mainli num">企业新增订单<a href="<?php echo U('order/index');?>" id="company_order">&nbsp;</a></div>
<div class="mainli num">个人新增订单<a href="<?php echo U('order/index_per');?>" id="personal_order">&nbsp;</a></div>
<div class="mainli num">发出面试邀请<a href="<?php echo U('CompanyInterview/index');?>" id="interview">&nbsp;</a></div>
<div class="mainli num">简历投递数<a href="<?php echo U('JobsApply/index');?>" id="jobs_apply">&nbsp;</a></div>
<div class="mainli num">职位刷新数<a href="<?php echo U('jobs/index');?>" id="jobs_refresh">&nbsp;</a></div>
<div class="clear"></div>

<div class="toptit">昨日统计</div>
<div class="mainli num">新增个人会员<a href="<?php echo U('personal/member_list');?>" id="yesterday_personal_users">&nbsp;</a></div>
<div class="mainli num">新增简历<a href="<?php echo U('personal/index');?>" id="yesterday_resumes">&nbsp;</a></div>
<div class="mainli num">简历刷新次数<a href="<?php echo U('personal/index');?>" id="yesterday_resume_refresh">&nbsp;</a></div>
<div class="mainli num">新增企业会员<a href="<?php echo U('company/index');?>" id="yesterday_company_users">&nbsp;</a></div>
<div class="mainli num">新增职位<a href="<?php echo U('jobs/index');?>" id="yesterday_jobs">&nbsp;</a></div>
<div class="mainli num">简历下载量<a href="<?php echo U('ResumeDown/index');?>" id="yesterday_resume_down">&nbsp;</a></div>
<div class="mainli num">企业新增订单<a href="<?php echo U('order/index');?>" id="yesterday_company_order">&nbsp;</a></div>
<div class="mainli num">个人新增订单<a href="<?php echo U('order/index_per');?>" id="yesterday_personal_order">&nbsp;</a></div>
<div class="mainli num">发出面试邀请<a href="<?php echo U('CompanyInterview/index');?>" id="yesterday_interview">&nbsp;</a></div>
<div class="mainli num">简历投递数<a href="<?php echo U('JobsApply/index');?>" id="yesterday_jobs_apply">&nbsp;</a></div>
<div class="mainli num">职位刷新数<a href="<?php echo U('jobs/index');?>" id="yesterday_jobs_refresh">&nbsp;</a></div>
<div class="clear"></div>

<div class="toptit">待处理事务</div>
<div class="mainli num">待审核职位<a href="<?php echo U('jobs/index_noaudit');?>" id="jobs_audit">&nbsp;</a></div>
<div class="mainli num">待认证企业<a href="<?php echo U('company/index',array('audit'=>2));?>" id="company_audit">&nbsp;</a></div>
<div class="mainli num">举报信息<a href="<?php echo U('report/index');?>" id="report">&nbsp;</a></div>
<div class="mainli num">待审核简历<a href="<?php echo U('personal/index_noaudit');?>" id="resume_audit">&nbsp;</a></div>
<div class="mainli num">待审核简历照片/作品<a href="<?php echo U('ResumeImg/index',array('audit'=>2));?>" id="resume_img_audit">&nbsp;</a></div>
<div class="mainli num">意见/建议<a href="<?php echo U('feedback/index');?>" id="feedback">&nbsp;</a></div>
<div class="clear"></div>

<div class="toptit">最近30天会员注册趋势</div>
<script language="JavaScript" src="__ADMINPUBLIC__/js/FusionCharts.js"></script>
<div id="chartdiv" style=" padding: 0 25px;">FusionCharts.</div>
<script type="text/javascript">
    var chart = new FusionCharts("__ADMINPUBLIC__/js/statement/area2D.swf", "ChartId", "1000", "200");
    chart.setDataURL("<?php echo ($charts); ?>");
    chart.render("chartdiv");
</script>
<script type="text/javascript">
    var tsTimeStamp= new Date().getTime();
    $.getJSON("<?php echo U('index/total');?>",function(result){
        for(var i in result.data){
            if(result.data[i]==0)
            {
                $("#"+i).html(result.data[i]);
            }
            else
            {
                $("#"+i).html('+'+result.data[i]);
            }

        }
    });
</script>

<div class="toptit">zy拉钩人才系统</div>
<div class="mainli">系统当前版本：v<?php echo C('QSCMS_VERSION');?><span id="update_notice"></span></div>
<div class="mainli link_blue">认证授权：<span id="certification">载入中...</span></div>
<div class="mainli link_blue">拉钩官网：<a href="https://www.lagou.com/" target="_blank">www.lagou.com</a></div>
<div class="mainli">程序开发：鲁东大学信电学院</div>
<div class="mainli">版权所有：计本1601张艳</div>
<div class="mainli link_blue">官方论坛：<a href="http://ask.74cms.com/" target="_blank">ask.74cms.com</a></div>
<div class="clear"></div>

<div class="toptit">服务器信息</div>
<div class="mainli">操作系统：windows10<?php echo ($system_info["server_os"]); ?></div>
<div class="mainli">PHP版本：<?php echo ($system_info["php_version"]); ?></div>
<div class="mainli">MySQL版本：5.7.26<?php echo ($system_info["mysql_version"]); ?></div>
<div class="mainli">服务器软件：<?php echo ($system_info["web_server"]); ?></div>
<div class="clear"></div>

<div class="toptit">官方动态</div>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr>
        <td style=" line-height:220%; padding-left:40px;" class="link_blue">
            <span id="announcement" class="link_lan">载入中...</span>
        </td>
    </tr>
</table>
<script src="https://www.74cms.com/plus/external.php?version=<?php echo ($system_info["version"]); ?>&release=<?php echo ($system_info["release"]); ?>&certification=<?php echo C('qscms_site_domain');?>&announcement=1" language="javascript"></script>
</div>

<!-- public:footer 以下内容 -->
<div class="footer link_blue">
    Powered by <a href="https://www.74cms.com" target="_blank"><span style="color:#009900">74</span><span
        style="color: #FF3300">cms</span></a> v<?php echo C('QSCMS_VERSION');?>
</div>
<div class="layui-floatmenu">
    <div class="J_suggest item" data-url="<?php echo U('Suggest/add');?>"></div>
    <div class="J_suggestList list item" data-url="<?php echo U('Suggest/index');?>">
      <?php if($suggest): ?><span></span><?php endif; ?>
    </div>
  </div>
<script src="__ADMINPUBLIC__/js/layui.js"></script>
<!-- public:footer 以上内容 -->
<script>
  layui.use(['form', 'element'], function(){
  var element = layui.element;
  var form = layui.form;
  var layer = layui.layer;
  var $ = layui.jquery;
  var loading = layer.load(0, {shade: [0.3,'#fff'],time:500});
  $('.J_suggest').click(function(){
    var url = $(this).data('url');
    $.getJSON(url,function(result){
      layer.open({
          title:'反馈',
          type: 1,
          area: ['800px', 'auto'], //宽高
          content: result.data
      });
    });
  });
  $('.J_suggestList').click(function(){
    var url = $(this).data('url');
    var f = $(this).find('span');
    $.getJSON(url,function(result){
      layer.open({
          title:'历史反馈记录',
          type: 1,
          area: ['800px', '690px'], //宽高
          content: result.data
      });
      f.remove();
    });
  });
});
</script>

</body>
</html>