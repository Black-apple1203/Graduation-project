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
		var URL = '/index.php/Admin/Navigation',
			SELF = '/index.php?m=Admin&amp;c=Navigation&amp;a=index',
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
		<p>页面关联标记与导航关联标记相同时(导航关联标记请在页面管理中查看)，与之关联的页面将高亮显示</p>
		<p>例如：在页面管理中，首页的导航关联标记为homepage,在自定义导航中增加网站首页栏目，页面关联标为homepage，那么打开网站首页页面，则此栏目高亮显示。</p>
    </div>
    <div class="seltpye_x">
		<div class="left">导航分类</div>	
		<div class="right">
			<?php if(is_array($categroy)): $i = 0; $__LIST__ = $categroy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$categroy): $mod = ($i % 2 );++$i;?><a href="<?php echo P(array('alias'=>$key));?>" <?php if($_REQUEST['alias']== $key): ?>class="select"<?php endif; ?>><?php echo ($categroy); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<form action="<?php echo U('navigation/nav_all_save');?>" method="post" enctype="multipart/form-data"  name="FormData" id="FormData" >
		<div class="list_th">
	        <div class="td" style=" width:29%;">
	            <label id="chkAll" class="left_padding">
	                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>名称
	            </label>
	        </div>
	        <div class="td" style=" width:10%;">显示</div>
	        <div class="td center" style=" width:20%;">页面关联标记</div>
	        <div class="td center" style=" width:8%;">位置</div>
	        <div class="td center" style=" width:10%;">打开方式</div>
	        <div class="td center" style=" width:10%;">排序</div>
	        <div class="td center" style=" width:13%;">操作</div>
	        <div class="clear"></div>
	    </div>
		<?php if(!empty($list)): if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$li): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
					<div class="td" style=" width:29%;">
			            <div class="left_padding striking">
		                    <input name="id[]" type="checkbox" id="id" value="<?php echo ($li['id']); ?>"/>
		                    <input name="save_id[]" type="hidden" value="<?php echo ($li["id"]); ?>"/>
							<input name="title[]" type="text"  class="input_text_default small" id="title" value="<?php echo ($li["title"]); ?>"   />
		                </div>
			        </div>
			        <div class="td" style=" width:10%;">
			        	<?php if($li['display'] == 1): ?><span style="color: #FF3300">显示</span>
						<?php else: ?>
							<span style="color:#999999">不显示</span><?php endif; ?>
						&nbsp;
			        </div>
			        <div class="td center" style=" width:20%;"><?php echo ($li["tag"]); ?>&nbsp;</div>
			        <div class="td center" style=" width:8%;"><?php echo ($li["categoryname"]); ?>&nbsp;</div>
			        <div class="td center" style=" width:10%;">
			        	<?php if($li['target'] == '_blank'): ?>新窗口<?php endif; ?>
						<?php if($li['target'] == '_self'): ?><span style="color:#666666">原窗口</span><?php endif; ?>
						&nbsp;
			        </div>
			        <div class="td center" style=" width:10%;">
			        	<input name="navigationorder[]" type="text"  class="input_text_default small" value="<?php echo ($li["navigationorder"]); ?>"  style="width:50px;" />
			        </div>
			        <div class="td center edit" style=" width:13%;">
			        	<a href="<?php echo U('navigation/edit',array('id'=>$li['id'],'url'=>"/index.php?m=Admin&amp;c=Navigation&amp;a=index"));?>">修改</a>
						<?php if($li['systemclass'] != 1): ?><a href="<?php echo U('navigation/delete',array('id'=>$li['id']));?>"  onclick="return confirm('你确定要删除吗？')">删除</a><?php endif; ?>
			        </div>
			        <div class="clear"></div>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>
		<?php else: ?>
			<div class="list_empty">没有任何信息！</div><?php endif; ?>
		<div class="list_foot">
			<div class="btnbox">
		        <input type="submit" value="保存修改" class="admin_submit"   />
				<input type="button" class="admin_submit" id="add"    value="添加栏目"  onclick="window.location='<?php echo U('navigation/add');?>'"/>
				<input id="ButDel" type="submit" value="删除栏目" class="admin_submit"   />
		    </div>
		</div>
	</form>
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
	$("#chk").live('click',function(){
	     $("#list").find("input[type=checkbox]").attr("checked",this.checked);
	});
	$("#ButDel").click(function(e){
		e.preventDefault();
      var ids = $("input[name='id[]']:checked");
      if(!ids.length) {
        disapperTooltip('remind','请选择微导航！');
      } else {
        if(confirm('确定删除选中的分类吗？')){
          $("#FormData").attr("action","<?php echo U('navigation/delete');?>");
          $("#FormData").submit();
        }
      }
	})
</script>
</body>
</html>