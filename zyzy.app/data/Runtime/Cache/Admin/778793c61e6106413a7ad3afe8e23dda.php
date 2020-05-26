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
		var URL = '/index.php/Admin/Page',
			SELF = '/index.php?m=Admin&amp;c=Page&amp;a=edit',
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
<!--<div class="allpagetop">系统提示</div>-->
<div class="admin_main_nr_dbox">
    <div class="adminshowmsg">
        <div class="msgtxt link_blue <?php if(($status) == "0"): ?>tip<?php endif; ?>">
            <?php if(($status) == "1"): ?><div class="tit"><?php echo ($message); ?></div><?php else: ?><div class="tit"><?php echo ($message); ?></div><?php endif; ?>
            <div class="jump">
                页面将自动跳转，等待时间 <span class="countdown_box"><?php echo ($waitSecond); ?></span> 秒<br>
                如果您的浏览器没有自动跳转，请点击 <a href="javascript:;" onClick="javascript:location.href='<?php echo ($jumpUrl); ?>';">[返回]</a>
            </div>
        </div>
    </div>
</div>
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
<script type="text/javascript">
    var count = <?php echo ($waitSecond); ?>;
    CountDown();
    var countdown = setInterval(CountDown, 1000);
    function CountDown() {
        $(".countdown_box").text(count);
        if (count == 0) {
            clearInterval(countdown);
            window.location.href = '<?php echo ($jumpUrl); ?>';
        }
        count--;
    }
    function refresh_affair(){
        window.parent.window.document.getElementById('leftFrame').contentWindow.refresh_affair();
        window.parent.window.document.getElementById('topFrame').contentWindow.refresh_affair();
    }
    <?php if($status == 1): ?>refresh_affair();<?php endif; ?>
</script>

</body>
</html>