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
		var URL = '/index.php/Admin/MembersLog',
			SELF = '/index.php?m=Admin&amp;c=MembersLog&amp;a=index',
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
        <p>系统将会自动清除一个月前的会员日志。</p>
    </div>
<div class="seltpye_y">
    <div class="tit">日志类型</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['log_utype'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['log_utype_cn']) != "")?(_I($_GET['log_utype_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('log_utype'=>'','log_utype_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('log_utype'=>'1','log_utype_cn'=>'企业会员'));?>">企业会员</li>
        <li url="<?php echo P(array('log_utype'=>'2','log_utype_cn'=>'个人会员'));?>">个人会员</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">创建时间</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['settr'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['settr_cn']) != "")?(_I($_GET['settr_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('settr'=>'','settr_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('settr'=>'3','settr_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('settr'=>'7','settr_cn'=>'一周内'));?>">一周内</li>
        <li url="<?php echo P(array('settr'=>'30','settr_cn'=>'一月内'));?>">一月内</li>
        <li url="<?php echo P(array('settr'=>'180','settr_cn'=>'半年内'));?>">半年内</li>
        <li url="<?php echo P(array('settr'=>'360','settr_cn'=>'一年内'));?>">一年内</li>
    </div>
</div>
<div class="clear"></div>
<form id="form1" name="form1" method="post" action="<?php echo U('MembersLog/delete');?>">
	<div class="list_th">
        <div class="td" style=" width:15%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>会员名
            </label>
        </div>
        <div class="td center" style=" width:10%;">类型</div>
        <div class="td center" style=" width:10%;">创建时间</div>
        <div class="td center" style=" width:10%;"> IP </div>
        <div class="td center" style=" width:10%;"> 地址 </div>
        <div class="td" style=" width:45%;">描述</div>
        <div class="clear"></div>
    </div>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
	    	<div class="td" style=" width:15%;">
	            <div class="left_padding striking">
	                <input name="log_id[]" type="checkbox" id="id" value="<?php echo ($vo["log_id"]); ?>"/>
	                <?php echo ($vo["log_username"]); ?><span style="color: #CCCCCC">[uid:<?php echo ($vo["log_uid"]); ?>]</span>
	            </div>
	        </div>
	        <div class="td center" style=" width:10%;">
	        	<?php switch($vo['log_utype']): case "1": ?>企业会员<?php break;?>
					<?php case "2": ?>个人会员<?php break; endswitch;?>&nbsp;
	        </div>
	        <div class="td center" style=" width:10%;"><?php echo admin_date($vo['log_addtime']);?>&nbsp;</div>
	        <div class="td center" style=" width:10%;"><?php echo (($vo["log_ip"] != "")?($vo["log_ip"]):'&nbsp;'); ?></div>
	        <div class="td center" style=" width:10%;"><?php echo (($vo["log_address"] != "")?($vo["log_address"]):'&nbsp;'); ?></div>
	        <div class="td vtip" style="width:45%;" title="<?php echo ($vo["log_value"]); ?>"><?php echo ($vo["log_value"]); ?></div>
	        <div class="clear"></div>
	    </div><?php endforeach; endif; else: echo "" ;endif; ?>
	<span id="OpPi"></span>
</form>
<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>
<div class="list_foot">
    <div class="footso">
        <form action="?" method="get">
            <div class="sobox">
                <input type="hidden" name="m" value="<?php echo C('admin_alias');?>">
                <input type="hidden" name="c" value="<?php echo CONTROLLER_NAME;?>">
                <input type="hidden" name="a" value="<?php echo ACTION_NAME;?>">
                <input name="key" type="text" class="sinput" value="<?php echo (_I($_GET['key'])); ?>"/>
                <input name="key_type" id="J_key_type_id" type="hidden" value="<?php echo ((_I($_GET['key_type']) != "")?(_I($_GET['key_type'])):'1'); ?>" />
                <input name="key_type_cn" id="J_key_type_cn" type="hidden" value="<?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'会员名'); ?>"/>
                <input name="" type="submit" value="" class="sobtn"/>
                <div class="sotype" id="J_key_click"><?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'会员名'); ?></div>
                <div class="mlist" id="J_mlist">
                    <ul>
                    	<li id="1" title="会员名">会员名</li>
						<li id="2" title="UID">UID</li>
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
<script type="text/javascript">
	$(document).ready(function(){
		//点击批量取消	
		$("#ButDel").click(function(){
			if (confirm('你确定要删除吗？')){
				$("form[name=form1]").submit()
			}
		});
		//纵向列表排序
		$(".list .txt").each(function(i){
			var li=$(this).children(".select");
			var htm="<a href=\""+li.attr("href")+"\" class=\""+li.attr("class")+"\">"+li.text()+"</a>";
			li.detach();
			$(this).prepend(htm);
		});
		showmenu("#key_type_cn","#sehmenu","#key_type");
	});
</script>
</body>
</html>