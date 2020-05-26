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
		var URL = '/index.php/Admin/Config',
			SELF = '/index.php?m=Admin&amp;c=Config&amp;a=map',
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
    <div class="toptip link_lan">
        <div class="toptit">提示：</div>
        <p class="link_green_line">电子地图API的服务是由<a href="http://developer.baidu.com/" target="_blank">百度</a>提供，任何非盈利性网站均可使用。请参阅<a href="http://developer.baidu.com/" target="_blank">使用条款</a>获得详细信息。</p>
        <p class="link_green_line">如用于商业用途，需要同百度地图另行达成协议或获得百度地图的书面许可使用电子地图，百度地图相应的ak。 现在就去<a href="http://developer.baidu.com/" target="_blank">申请</a></p>
    </div>
<form id="form1">
    <div class="toptit">电子地图设置</div>
    <div class="form_main width200">
        <div class="fl">百度地图AK：</div>
        <div class="fr">
            <input name="map_ak" id="map_ak" type="text" class="input_text_default middle" value="<?php echo C('qscms_map_ak');?>" style="color:#009900; font-weight:bold"/>
        </div>
        <div class="fl">默认中心点X坐标：</div>
        <div class="fr">
            <input name="map_center_x" id="map_center_x" type="text" class="input_text_default middle" value="<?php echo C('qscms_map_center_x');?>" style="color:#009900; font-weight:bold"/>
            <label>推动电子地图，系统将自动获取坐标</label>
        </div>
        <div class="fl">默认中心点Y坐标：</div>
        <div class="fr">
            <input name="map_center_y" id="map_center_y" type="text" class="input_text_default middle" value="<?php echo C('qscms_map_center_y');?>" style="color:#009900; font-weight:bold"/>
        </div>
        <div class="fl">默认缩放级别：</div>
        <div class="fr">
            <input name="map_zoom" id="map_zoom" type="text" class="input_text_default middle" value="<?php echo C('qscms_map_zoom');?>" style="color:#009900; font-weight:bold"/>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="hidden" name="type" value="map">
            <input type="button" class="admin_submit" id="J_submit" value="保存修改"/>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <div style="width:600px;height:400px; border:1px #CCCCCC solid" id="container"></div>
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
<script src="https://api.map.baidu.com/api?v=1.3" type="text/javascript"></script>
<script type="text/javascript">
    $('#J_submit').click(function(){
        var that = $(this);
        if(that.hasClass('disabled')){
            return false;
        }
        that.val('正在保存...').addClass('disabled');
        $.post("<?php echo U('config/edit');?>",$('#form1').serialize(),function(result){
            if(result.status==1){
                disapperTooltip("success", result.msg,function(){
                    that.val('保存修改').removeClass('disabled');
                });
            }else{
                disapperTooltip("remind", result.msg,function(){
                    that.val('保存修改').removeClass('disabled');
                });
                return false;
            }
        },'json');
    });
  var map = new BMap.Map("container");       // 创建地图实例
  var point = new BMap.Point(<?php echo C('qscms_map_center_x');?>,<?php echo C('qscms_map_center_y');?>);  // 创建点坐标
  map.centerAndZoom(point, <?php echo C('qscms_map_zoom');?>);
  map.addControl(new BMap.NavigationControl());
  map.addEventListener("moveend", function(){
    var center = map.getCenter();
    document.getElementById("map_center_x").value=center.lng;
    document.getElementById("map_center_y").value= center.lat;
    document.getElementById("map_zoom").value=map.getZoom();
  });
</script>
</body>
</html>