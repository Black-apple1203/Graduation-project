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
		var URL = '/index.php/Admin/Ad',
			SELF = '/index.php?m=Admin&amp;c=Ad&amp;a=index',
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
        <p>系统将自动下架到期广告。</p>
        <p>按广告位显示广告请点击右下角按钮</p>
    </div>
<div class="seltpye_y">
    <div class="tit">到期时间</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['settr']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['settr_cn']) != "")?(_I($_GET['settr_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('settr'=>'','settr_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('settr'=>'0','settr_cn'=>'已经到期'));?>">已经到期</li>
        <li url="<?php echo P(array('settr'=>'3','settr_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('settr'=>'7','settr_cn'=>'一周内'));?>">一周内</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">显示状态</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['is_display']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['is_display_cn']) != "")?(_I($_GET['is_display_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('is_display'=>'','is_display_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('is_display'=>'1','is_display_cn'=>'正常'));?>">正常</li>
        <li url="<?php echo P(array('is_display'=>'0','is_display_cn'=>'停止'));?>">停止</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">广告位</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['alias'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['alias_cn']) != "")?(_I($_GET['alias_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('alias'=>'','alias_cn'=>'不限'));?>">不限</li>
        <?php if(is_array($category_list)): $i = 0; $__LIST__ = $category_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li url="<?php echo P(array('alias'=>$vo['alias'],'alias_cn'=>$vo['categoryname']));?>" title="<?php echo ($vo["categoryname"]); ?>"><?php echo ($vo["categoryname"]); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
<?php if(C('qscms_subsite_open') == 1): ?><div class="seltpye_y">
    <div class="tit">分站</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['subsite_id'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['s_sitename']) != "")?(_I($_GET['s_sitename'])):"不限"); ?></div>
    </div>
    <div class="downlist">
		<li url="<?php echo P(array('subsite_id'=>'',s_sitename=>'不限'));?>" <?php if($_GET['subsite_id']== ''): ?>class="select"<?php endif; ?>>不限</a>
			<?php if(is_array($subsite_list)): $i = 0; $__LIST__ = $subsite_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$subsite): $mod = ($i % 2 );++$i;?><li url="<?php echo P(array('subsite_id'=>$subsite['s_id'],s_sitename=>$subsite['s_sitename']));?>" <?php if($_GET['subsite_id']== $subsite['s_id']): ?>class="select"<?php endif; ?>><?php echo ($subsite["s_sitename"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div><?php endif; ?>
<div class="clear"></div>

<form id="form1" name="form1" method="post" action="<?php echo U('delete');?>">
    <div class="list_th">
        <div class="td" style=" width:32%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>广告标题
            </label>
        </div>
        <div class="td" style=" width:20%;">所属广告位</div>
        <div class="td center" style=" width:6%;">类型</div>
        <div class="td center" style=" width:10%;">开始时间</div>
        <div class="td center" style=" width:10%;">到期时间</div>
        <div class="td center" style=" width:6%;">状态</div>
        <div class="td center" style=" width:6%;">排序</div>
        <div class="td" style=" width:10%;">操作</div>
        <div class="clear"></div>
    </div>

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_blue">
            <div class="td" style=" width:32%;">
                <div class="left_padding striking">
                    <input name="id[]" type="checkbox" id="id" value="<?php echo ($vo['id']); ?>"/>
                    <a href="<?php echo U('edit',array('id'=>$vo['id']));?>" <?php if($vo['text_color']): ?>style="color:<?php echo ($vo["text_color"]); endif; ?> <?php if($vo['type_id'] == 1): ?>class="vtip" title="<?php echo ($vo["content"]); ?>"<?php endif; ?> <?php if($vo['type_id'] == 2): ?>class="vtip" title='<img src="<?php echo attach($vo['content'],'attach_img');?>"/>'<?php endif; ?>><?php echo ($vo["title"]); if(C('qscms_subsite_open') == 1): ?>(<?php echo (($subsite_list[$vo['subsite_id']]['s_sitename'] != "")?($subsite_list[$vo['subsite_id']]['s_sitename']):"总站"); ?>)<?php endif; ?><!--分站标识--></a>
                    <?php if($vo['note'] != ''): ?><img src="__ADMINPUBLIC__/images/comment_alert.gif" border="0" class="vtip" title="<?php echo ($vo["note"]); ?>"/><?php endif; ?>
                </div>
            </div>
            <div class="td" style=" width:20%;"><?php echo (($category_list[$vo['alias']]['categoryname'] != "")?($category_list[$vo['alias']]['categoryname']):"-"); ?></div>
            <div class="td center" style=" width:6%;">
                <?php switch($vo['type_id']): case "1": ?>文字<?php break;?>
                    <?php case "2": ?>图片<?php break;?>
                    <?php case "3": ?>代码<?php break;?>
                    <?php case "4": ?>FLASH<?php break;?>
                    <?php case "5": ?>浮动<?php break;?>
                    <?php case "6": ?>视频<?php break; endswitch;?>
            </div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['starttime'] == 0): ?>无限制<?php else: echo date('Y-m-d',$vo['starttime']); endif; ?>
            </div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['deadline'] == 0): ?>无限制
                    <?php elseif($vo['deadline'] <= time()): ?>
                    <span style="color:#FF6600">已经到期</span>
                    <?php else: ?>
                    <?php echo date('Y-m-d',$vo['deadline']); endif; ?>
            </div>
            <div class="td center" style=" width:6%;">
                <?php if($vo['is_display'] == 1): ?>正常<?php else: ?><span style="color:#999999">暂停</span><?php endif; ?>
            </div>
            <div class="td center" style=" width:6%;"><?php echo ($vo["show_order"]); ?></div>
            <div class="td edit" style=" width:10%;">
                <a href="<?php echo U('edit',array('id'=>$vo['id']));?>">修改</a>
                <a href="<?php echo U('delete',array('id'=>$vo['id']));?>" class="gray" onclick="return confirm('你确定要删除吗？')">删除</a>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
</form>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
        <input type="button" class="admin_submit" id="ButAdd" value="添加广告" onclick="window.location='<?php echo U('add');?>'"/>
        <input type="button" class="admin_submit" id="ButDel" value="删除所选"/>
    </div>

    <div class="footso">
        <form action="?" method="get">
            <div class="sobox">
                <input type="hidden" name="m" value="<?php echo C('admin_alias');?>">
                <input type="hidden" name="c" value="<?php echo CONTROLLER_NAME;?>">
                <input type="hidden" name="a" value="<?php echo ACTION_NAME;?>">
                <input name="key" type="text" class="sinput" value="<?php echo (_I($_GET['key'])); ?>"/>
                <input name="key_type" id="J_key_type_id" type="hidden" value="<?php echo ((_I($_GET['key_type']) != "")?(_I($_GET['key_type'])):'1'); ?>" />
                <input name="key_type_cn" id="J_key_type_cn" type="hidden" value="<?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'标题'); ?>"/>
                <input name="" type="submit" value="" class="sobtn"/>
                <div class="sotype" id="J_key_click"><?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'标题'); ?></div>
                <div class="mlist" id="J_mlist">
                    <ul>
                        <li id="1" title="标题">标题</li>
                    </ul>
                </div>
            </div>
        </form>
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

</body>
<script type="text/javascript">
    $(document).ready(function () {
        //点击批量删除
        $("#ButDel").click(function () {
            var ids = $("input[name='id[]']:checked");
            if(ids.length == 0){
                disapperTooltip('remind','请选择广告！');
            } else {
                if(confirm('确定删除吗？')){
                    $("form[name=form1]").submit();
                }
            }
        });
    });
</script>
</html>