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
		var URL = '/index.php/Admin/Sms',
			SELF = '/index.php?m=Admin&amp;c=Sms&amp;a=config_edit',
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
<div class="toptip">
    <div class="toptit">提示：</div>
    <p>短信模块属收费模块，需申请开通后才能使用，请联系我司客服申请开通。</p>
    <p class="link_green_line">资费标准请联系骑士销售获取，更多介绍请进入 <a href="http://www.74cms.com" target="_blank">骑士人才系统官方网站</a></p>
    <p><font color="red">使用短信发送服务前，请确认短信服务商已正确配置！</font></p>
    <p><font color="red">阿里大于的短信暂时不能正常发送招聘类信息，使用前请先向阿里大于官方核实，谨慎使用！</font></p>
</div>
	<form action="<?php echo U('sms/config_edit');?>" method="post" enctype="multipart/form-data" name="form1" id="form1">
		<div class="toptit">设置</div>
		<div class="form_main width200">
            <div id="j_show" >
            	<div class="fl">默认短信服务商：</div>
	            <div class="fr">
	            	<div class="select_input_new w400 flo J_hoverinput J_dropdown J_listitme_parent">
		                <span class="J_listitme_text">请选择短信服务商</span>
		                <div class="dropdowbox_sn J_dropdown_menu">
		                    <div class="dropdow_inner_sn">
		                        <ul class="nav_box">
		                            <?php if(is_array($sms_list)): $i = 0; $__LIST__ = $sms_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sms): $mod = ($i % 2 );++$i;?><li><a class="J_listitme <?php if($key == C('qscms_sms_default_service')): ?>list_sel<?php endif; ?>" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($sms["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
		                        </ul>
		                    </div>
		                </div>
		                <input class="J_listitme_code" name="sms_default_service" id="sms_default_service" type="hidden" value="" />
		            </div>
					<div class="sin_text">（请在“服务商接入”栏目下安装短信接口）</div>
					<div class="clear"></div>
	            </div>
	            <div class="fl">验证码类短信服务商：</div>
	            <div class="fr">
	            	<div class="select_input_new w400 flo J_hoverinput J_dropdown J_listitme_parent">
		                <span class="J_listitme_text">请选择短信服务商</span>
		                <div class="dropdowbox_sn J_dropdown_menu">
		                    <div class="dropdow_inner_sn">
		                        <ul class="nav_box">
		                            <?php if(is_array($sms_list)): $i = 0; $__LIST__ = $sms_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sms): $mod = ($i % 2 );++$i;?><li><a class="J_listitme <?php if($key == C('qscms_sms_captcha_service')): ?>list_sel<?php endif; ?>" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($sms["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
		                        </ul>
		                    </div>
		                </div>
		                <input class="J_listitme_code" name="sms_captcha_service" id="sms_captcha_service" type="hidden" value="" />
		            </div>
					<div class="sin_text">（请在“服务商接入”栏目下安装短信接口）</div>
					<div class="clear"></div>
	            </div>
	            <div class="fl">通知类短信服务商：</div>
	            <div class="fr">
	            	<div class="select_input_new w400 flo J_hoverinput J_dropdown J_listitme_parent">
		                <span class="J_listitme_text">请选择短信服务商</span>
		                <div class="dropdowbox_sn J_dropdown_menu">
		                    <div class="dropdow_inner_sn">
		                        <ul class="nav_box">
		                            <?php if(is_array($sms_list)): $i = 0; $__LIST__ = $sms_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sms): $mod = ($i % 2 );++$i;?><li><a class="J_listitme <?php if($key == C('qscms_sms_notice_service')): ?>list_sel<?php endif; ?>" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($sms["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
		                        </ul>
		                    </div>
		                </div>
		                <input class="J_listitme_code" name="sms_notice_service" id="sms_notice_service" type="hidden" value="" />
		            </div>
					<div class="sin_text">（请在“服务商接入”栏目下安装短信接口）</div>
					<div class="clear"></div>
	            </div>
	            <div class="fl">其它类短信服务商：</div>
	            <div class="fr">
	            	<div class="select_input_new w400 flo J_hoverinput J_dropdown J_listitme_parent">
		                <span class="J_listitme_text">请选择短信服务商</span>
		                <div class="dropdowbox_sn J_dropdown_menu">
		                    <div class="dropdow_inner_sn">
		                        <ul class="nav_box">
		                            <?php if(is_array($sms_list)): $i = 0; $__LIST__ = $sms_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sms): $mod = ($i % 2 );++$i;?><li><a class="J_listitme <?php if($key == C('qscms_sms_other_service')): ?>list_sel<?php endif; ?>" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($sms["name"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
		                        </ul>
		                    </div>
		                </div>
		                <input class="J_listitme_code" name="sms_other_service" id="sms_other_service" type="hidden" value="" />
		            </div>
					<div class="sin_text">（请在“服务商接入”栏目下安装短信接口）</div>
					<div class="clear"></div>
	            </div>
            </div>
            <div class="fl"></div>
	        <div class="fr">
	            <input type="submit" class="admin_submit" value="保存修改"/>
	        </div>
			<div class="clear"></div>
		</div>
	</form>
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
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.listitem.js"></script>
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.dropdown.js"></script>
<script type="text/javascript">
	if ($('.J_listitme.list_sel').length) {
		$('.J_listitme.list_sel').each(function(index, el) {
			var listSelCn = $.trim($(this).text());
            var listSelCode = $(this).data('code');
			console.log(listSelCn);
            $(this).closest('.J_listitme_parent').find('.J_listitme_text').text(listSelCn);
            $(this).closest('.J_listitme_parent').find('.J_listitme_code').val(listSelCode);
		})
	}
</script>
</body>
</html>