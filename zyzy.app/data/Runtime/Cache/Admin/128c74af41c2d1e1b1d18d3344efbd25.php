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
			SELF = '/index.php?m=Admin&amp;c=Page&amp;a=index',
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
    <p>你可以通过全选来设置所有页面的链接方式和缓存时间</p>
    <p>职位列表页，人才列表页，会员中心页面均不能开启缓存</p>
    <p>系统内内置页面无法删除！</p>
    <p>强烈建议开启页面缓存，缓存让系统性能显著提高！</p>
    <p class="link_green_line">骑士人才系统支持多种URL样式，无论您是<strong>asp , aspx , jsp ，shtml , ......</strong>程序都可以完美转换为骑士系统，且URL可以保持不变，具体请咨询<a href="http://www.74cms.com" target="_blank">骑士客服</a></p>
</div>
<form action="<?php echo U('set_url');?>" method="post"  name="form1" id="form1">
    <div class="list_th">
        <div class="td" style=" width:30%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>页面名称
            </label>
        </div>
        <div class="td" style=" width:30%;">调用名</div>
        <div class="td center" style=" width:10%;">类型</div>
        <div class="td center" style=" width:10%;">链接</div>
        <div class="td center" style=" width:10%;">缓存</div>
        <div class="td" style=" width:10%;">编辑</div>
        <div class="clear"></div>
    </div>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
            <div class="td" style=" width:30%;">
                <div class="left_padding striking">
                    <input name="id[]" type="checkbox" id="id" value="<?php echo ($vo['id']); ?>"/>
                    <?php echo ($vo['pname']); ?>
                </div>
            </div>
            <div class="td" style=" width:30%;"><?php echo ($vo['alias']); ?></div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['systemclass'] == 1): ?><span style="color: #FF6600">系统内置</span>
                    <?php else: ?>
                    自定义页面<?php endif; ?>
            </div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['url'] == 0): ?>原始链接<?php endif; ?>
                <?php if($vo['url'] == 1): ?>伪静态<?php endif; ?>
            </div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['caching'] == 0): ?><span style="color:#999999">已关闭</span>
                    <?php else: ?>
                    <em><?php echo ($vo['caching']); ?></em> 分<?php endif; ?>
            </div>
            <div class="td edit" style=" width:10%;">
                <a href="<?php echo U('edit',array('id'=>$vo['id']));?>">修改</a>
                <?php if($vo['systemclass'] != 1): ?><a href="<?php echo U('delete',array('id'=>$vo['id']));?>" class="gray" onclick="return confirm('你确定要删除吗？')">删除</a><?php endif; ?>
            </div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    <input type="hidden" name="url" id="url" value="">
    <input type="hidden" name="caching" id="caching" value="">
</form>
<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>
<div class="list_foot">
    <div class="btnbox">
        <input name="add" type="button" class="admin_submit" id="add" value="添加页面"  onclick="window.location='<?php echo U('add');?>'"/>
        <input type="button" name="open" value="设置链接" class="admin_submit"  id="SetUrl"/>
        <input type="button" name="open1" value="设置缓存" class="admin_submit"  id="SetCaching"/>
        <input type="button" name="ButDel" id="ButDel" value="删除页面" class="admin_submit"   />
    </div>
</div>
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
    $(document).ready(function() {
      // 批量设置链接
      $("#SetUrl").click(function () {
        var ids = $("input[name='id[]']:checked");
        if (!ids.length) {
          disapperTooltip('remind','您没有选择页面！');
          return false;
        }
        var qsDialog = $(this).dialog({
          title: '请选择',
          loading: true,
          footer : false
        });
        var url = "<?php echo U('Ajax/page_set_url');?>";
        $.post(url, function (result) {
          if(result.status == 1){
            qsDialog.setContent(result.data);
            $("#set_url").live('click',function(){
              $('#url').val($('#J_url').val());
              $("form[name=form1]").submit()
            })
          } else {
            qsDialog.hide();
            disapperTooltip('remind',result.msg);
          }
        });
      })
      // 批量设置缓存
      $("#SetCaching").click(function () {
        var ids = $("input[name='id[]']:checked");
        if (!ids.length) {
          disapperTooltip('remind','您没有选择页面！');
          return false;
        }
        var qsDialog = $(this).dialog({
          title: '设置缓存',
          loading: true,
          footer : false
        });
        var url = "<?php echo U('Ajax/page_set_caching');?>";
        $.post(url, function (result) {
          if(result.status == 1){
            qsDialog.setContent(result.data);
            $("#set_caching").live('click',function(){
              $('#caching').val($('#J_caching').val());
              $("form[name=form1]").attr("action","<?php echo U('set_caching');?>");
              $("form[name=form1]").submit();
            })
          } else {
            qsDialog.hide();
            disapperTooltip('remind',result.msg);
          }
        });
      })
      //点击批量删除
      $("#ButDel").click(function(){
        var ids = $("input[name='id[]']:checked");
        if (!ids.length) {
          disapperTooltip('remind','您没有选择页面！');
        } else {
          if (confirm('你确定要删除吗？')) {
            $("form[name=form1]").attr("action","<?php echo U('delete');?>");
            $("form[name=form1]").submit()
          }
        }
      })
    })
</script>
</body>
</html>