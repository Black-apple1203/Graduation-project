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
		var URL = '/index.php/Admin/Apply',
			SELF = '/index.php?m=Admin&amp;c=Apply&amp;a=index',
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
<?php if($is_shift == 1): ?><div class="errtit">系统已安装4.2.56补丁,检测到项目未进行数据转换,无法正常运行。请下载“骑士人才系统工具箱(74cms_Tools_Setup_v1.0.9.zip)”，运行(工具箱->其他工具->4.2.56数据整理)</div><?php endif; ?>
<?php if($is_shift_4295 == 1): ?><div class="errtit">系统已安装4.2.95补丁,检测到项目未进行数据转换,无法正常运行。请下载“骑士人才系统工具箱(74cms_Tools_Setup_v1.0.10.zip)”，运行(工具箱->其他工具->4.2.95数据整理)</div><?php endif; ?>
<div class="list_th">
    <div class="td" style="width:52%;"><label class="left_padding">应用名称</label></div>
    <div class="td" style="width:16%;">当前版本</div>
    <div class="td" style="width:15%;">最新版本</div>
    <div class="td center" style="width:15%;">操作</div>
    <div class="clear"></div>
</div>
<div class="list_tr apply link_black J_mod" m="<?php echo ($base["module"]); ?>" v="<?php echo ($base["version"]["version"]); ?>">
	<div class="td apply_list" style="width:43%;">
		<a class="ico" href="<?php echo U('apply/details',array('mod'=>$base['module']));?>">
			<img src="<?php echo ($base["ico"]); ?>">
		</a>
		<div class="info">
			<p><a class="title" href="<?php echo U('apply/details',array('mod'=>$base['module']));?>"><?php echo ($base["version"]["module_name"]); ?>(<?php echo ($base["module"]); ?>)</a></p>
			<p><?php echo ($base["version"]["explain"]); ?></p>
		</div>
	</div>
    <div class="td apply_list" style="width:17%;">
    	<p style="margin-top: 5px;">版本号：<?php echo ($base["version"]["version"]); ?></p>
		<p>更新时间：<?php echo ($base["version"]["update_time"]); ?></p>
    </div>
    <div class="td apply_list" style="width:17%; color: #999999;">
    	<p class="J_version" style="margin-top: 5px;">版本号：</p>
		<p class="J_time">更新时间：</p>
    </div>
    <div class="td apply_list edit" style="width:17%;">
		<a href="<?php echo U('apply/details',array('mod'=>$base['module']));?>" >详情</a>
    	<?php if($Think.APP_UPDATER): ?><a class="J_updater gray" url="<?php echo U('apply/updater',array('mod'=>$base['module']));?>">升级</a>
			<?php if(($base['enable_rollback']) == "1"): ?><a class="J_rollback" href="<?php echo U('apply/rollback',array('mod'=>$base['module']));?>" >回滚</a><?php endif; endif; ?>
    </div>
    <div class="clear"></div>
</div>
<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="list_tr apply link_black J_mod" m="<?php echo ($list["module"]); ?>" v="<?php echo ($list["version"]["version"]); ?>">
		<div class="td apply_list" style="width:43%;">
			<a class="ico" href="<?php echo U('apply/details',array('mod'=>$list['module']));?>">
				<img src="<?php echo ($list["ico"]); ?>">
			</a>
			<div class="info">
				<p><a class="title" href="<?php echo U('apply/details',array('mod'=>$list['module']));?>"><?php echo ($list["version"]["module_name"]); ?></a>（<?php echo ($list["module"]); ?>）</p>
				<p><?php echo ($list["version"]["explain"]); ?></p>
			</div>
		</div>
	    <div class="td apply_list" style="width:17%;">
	    	<p style="margin-top: 5px;">版本号：<?php echo ($list["version"]["version"]); ?></p>
			<p>更新时间：<?php echo ($list["version"]["update_time"]); ?></p>
	    </div>
	    <div class="td apply_list" style="width:17%;color: #999999;">
			<p class="J_version" style="margin-top: 5px;">版本号：</p>
			<p class="J_time">更新时间：</p>
		</div>
	    <div class="td apply_list edit" style="width:17%;">
			<a href="<?php echo U('apply/details',array('mod'=>$list['module']));?>" >详情</a>
	    	<?php if($list['is_setup'] == 0): ?><a href="<?php echo U('apply/setup',array('mod'=>$list['module']));?>" >安装</a><?php endif; ?>
			<?php if($Think.APP_UPDATER): if($apply[$list['module']]): ?><a class="J_updater gray" url="<?php echo U('apply/updater',array('mod'=>$list['module']));?>">升级</a><?php endif; ?>
				<?php if(($list['enable_rollback']) == "1"): ?><a class="J_rollback" href="<?php echo U('apply/rollback',array('mod'=>$list['module']));?>" >回滚</a>&nbsp;<?php endif; endif; ?>
	    	<?php if($list['is_setup'] != 0): ?><a href="<?php echo U('apply/unload',array('mod'=>$list['module']));?>" class="gray">卸载</a><?php endif; ?>
	    </div>
	    <div class="clear"></div>
	</div><?php endforeach; endif; else: echo "" ;endif; ?>
<div class="pages"><?php echo ($page); ?></div>
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
	function callback(a){
		$.each(a.data,function(k,v){
			var version = $('.J_mod[m="'+k+'"]').attr('v'),h='';
			var version_arr =  version.split('.');
            var version_int = parseInt(version_arr[0] * 100000) + parseInt(version_arr[1] * 1000) + parseInt(version_arr[2]);
			$('.J_mod[m="'+k+'"]').find('.J_version').html('版本号：'+v.version);
			$('.J_mod[m="'+k+'"]').find('.J_time').html('更新时间：'+v.update_time);
			if(v.version){
				if(version_int < v.version_int){
					v.update_time = v.update_time ? v.update_time : '未发布';
					$('.J_mod[m="'+k+'"] .J_v').append('<a href="https://www.74cms.com/app/lists.html" target="_blank" class="newsv">有新版</a>');
					$('.J_mod[m="'+k+'"] .J_t').html(v.update_time);
					$('.J_mod[m="'+k+'"] .J_updater').attr('href',$('.J_mod[m="'+k+'"] .J_updater').attr('url')).removeClass('gray').addClass('orange');
				}
			}
		});
	}
</script>
<script src="https://www.74cms.com/plus/check_module.php?module_name=<?php echo ($module_name); ?>" language="javascript"></script>
</body>
</html>