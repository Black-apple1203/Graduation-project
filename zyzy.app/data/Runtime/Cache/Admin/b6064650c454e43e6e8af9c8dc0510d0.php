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
			SELF = '/index.php?m=Admin&amp;c=Page&amp;a=edit&amp;id=1',
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
    <p>若修改了伪静态规则，请修改服务器伪静态规则文件的对应规则。</p>
</div>
<form action="<?php echo U('edit');?>" method="post"   name="form1" id="form1">
    <div class="toptit">基础设置</div>
    <div class="form_main width150">
        <div class="fl">页面类型：</div>
        <div class="fr txt">
            <?php if($info['systemclass'] == 1): ?><span style="color:#FF0000">系统内置</span>
            <?php else: ?>
                自定义页面<?php endif; ?>
        </div>
        <div class="fl">调用ID：</div>
        <div class="fr <?php if($info['systemclass'] == 1): ?>txt<?php endif; ?>">
            <?php if($info['systemclass'] == 1): ?><strong><?php echo ($info['alias']); ?></strong>
                <input name="alias" type="hidden" value="<?php echo ($info['alias']); ?>" />
            <?php else: ?>
                <input name="alias" type="text" maxlength="30" class="input_text_default middle" value="<?php echo ($info['alias']); ?>"/>
                <label class="no-fl-note">自定义页面调用名称不可以用 &quot;QS_&quot; 开头</label><?php endif; ?>
        </div>
        <div class="fl">页面名称：</div>
        <div class="fr">
            <input name="pname" type="text" maxlength="60" class="input_text_default" value="<?php echo ($info['pname']); ?>"/>
        </div>
        <div class="fl">导航关联标记：</div>
        <div class="fr">
            <input name="tag" type="text" maxlength="60" class="input_text_default" value="<?php echo ($info['tag']); ?>"/>
        </div>
        <div class="fl">链接优化：</div>
        <div class="fr">
            <div class="imgradio">
                <input name="url" type="hidden" value="<?php echo ($info['url']); ?>">
                <div class="radio <?php if($info['url'] == 0): ?>select<?php endif; ?>" data="0" title="原始链接">原始链接</div>
                <div class="radio <?php if($info['url'] == 1): ?>select<?php endif; ?>" data="1" title="伪静态">伪静态</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl">页面类型：</div>
        <div class="fr">
            <div class="imgradio">
                <input name="pagetpye" type="hidden" value="<?php echo ($info['pagetpye']); ?>">
                <div class="radio <?php if($info['pagetpye'] == 1): ?>select<?php endif; ?>" data="1" title="首页或频道首页">首页或频道首页</div>
                <div class="radio <?php if($info['pagetpye'] == 2): ?>select<?php endif; ?>" data="2" title="信息列表页">信息列表页</div>
                <div class="radio <?php if($info['pagetpye'] == 3): ?>select<?php endif; ?>" data="3" title="信息内容页">信息内容页</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="toptit">页面设置</div>
    <div class="form_main width150">
        <div class="fl">模块：</div>
        <div class="fr">
            <input name="module" type="text" class="input_text_default middle" value="<?php echo ($info['module']); ?>"/>
            <label class="no-fl-note">(模块开头字母要大写)</label>
        </div>
        <div class="fl">控制器：</div>
        <div class="fr">
            <input name="controller" type="text" class="input_text_default middle" value="<?php echo ($info['controller']); ?>"/>
            <label class="no-fl-note">(控制器开头字母要大写)</label>
        </div>
        <div class="fl">方法：</div>
        <div class="fr">
            <input name="action" type="text" class="input_text_default middle" value="<?php echo ($info['action']); ?>"/>
        </div>
        <div class="fl link_blue"><a href="javascript:isdisplay('caching_help')">(?)</a> 缓存时间：</div>
        <div class="fr">
            <input name="caching" type="text" class="input_text_default middle" value="<?php echo ($info['caching']); ?>" maxlength="30"/>
            <label class="no-fl-note">(0为不缓存)</label>
        </div>
        <div id="caching_help" style="display:none">
            <div class="fl"></div>
            <div class="fr">
                <span style="color:#666666">
                    <strong style="color:#003399">缓存说明</strong><br />缓存是当查询数据时，会把结果序列化后保存到文件中，以后同样的查询就不用查询数据库，而是从缓存中获得。这一改进使得程序速度得以太幅度提升。<br />
                    缓存时间是缓存重新加载周期，周期越长，数据库的负荷越小，缓存周期假设为50秒，则每50秒刷新缓存一次。
                </span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="toptit">搜索引擎优化(SEO)</div>
    <div class="form_main width150">
        <div class="fl">可用标签：</div>
        <div class="fr txt">
            <div id="{site_name}" class="sellabel">网站名称</div>
            <div id="{site_domain}" class="sellabel">网站域名</div>
            <?php if(is_array($info['variate'])): $i = 0; $__LIST__ = $info['variate'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div id="{<?php echo ($key); ?>}" class="sellabel"><?php echo ($vo); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
            <div class="clear"></div>
        </div>
        <div class="fl">title：</div>
        <div class="fr">
            <input name="title" type="text" class="input_text_default" id="labtitle" value="<?php echo ($info['title']); ?>"/>
        </div>
        <div class="fl">keywords：</div>
        <div class="fr">
            <textarea name="keywords" class="input_text_default" id="labkeywords" style="height:60px;"><?php echo ($info['keywords']); ?></textarea>
        </div>
        <div class="fl">description：</div>
        <div class="fr">
            <textarea name="description" class="input_text_default" id="labdescription" style="height:60px;"><?php echo ($info['description']); ?></textarea>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input name="id" type="hidden" value="<?php echo ($info['id']); ?>" />
            <input name="systemclass" type="hidden" value="<?php echo ($info['systemclass']); ?>" />
            <input type="submit" name="Submi1t2" value="保存修改" class="admin_submit"   />
            <input name="submit222" type="button" class="admin_submit"    value="返 回" onclick="window.location='<?php echo U('page/index');?>'"/>
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
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.caretInsert.js"></script>
<script language="JavaScript" type="text/javascript"> 
//获取单选的值
function radios_val(val)
{
    var radios=document.getElementsByName(val);
    for(var i=0;i<radios.length;i++)
    {
        if(radios[i].checked==true)
        {
  return radios[i].value;
        break;
        }
    }
}
//show_seo("seo");
function show_seo(showid)
{
var caching_val=radios_val("pagetpye");
if (caching_val!="3")
{
 document.getElementById(showid).style.display="";   
}
else
{
document.getElementById(showid).style.display="none";   
}
}
function isdisplay(i)     
{      
if(document.getElementById(i).style.display=="")     
{     
 document.getElementById(i).style.display="none";     
}     
else     
{     
document.getElementById(i).style.display="";     
  
}     
 }
(function($)
{
  $(".sellabel").hover(function(){$(this).css("background-color","#ffffff");},function()  {$(this).css("background-color","#F4FAFF");});
  $("#labtitle").unbind().focus(function() {
    $('#labtitle').setCaret();
     $('.sellabel').unbind("click").click(function(){ 
       $('#labtitle').insertAtCaret($(this).attr("id"));
     });
  });
  $("#labkeywords").unbind().focus(function() {
    $('#labkeywords').setCaret();
     $('.sellabel').unbind("click").click(function(){ 
       $('#labkeywords').insertAtCaret($(this).attr("id"));
     });
  });
  $("#labdescription").unbind().focus(function() {
    $('#labdescription').setCaret();
     $('.sellabel').unbind("click").click(function(){ 
       $('#labdescription').insertAtCaret($(this).attr("id"));
     });
  }); 
})($);   
</script>  
</body>
</html>