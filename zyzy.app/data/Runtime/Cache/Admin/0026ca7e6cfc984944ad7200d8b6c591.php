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
			SELF = '/index.php?m=Admin&amp;c=navigation&amp;a=edit&amp;id=1&amp;url=%2Findex.php%3Fm%3DAdmin%26amp%3Bc%3DNavigation%26amp%3Ba%3Dindex',
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
        <p>如果是本站的网址，可缩写为与根目录相对地址，如 index.php</p>
        <p>其他情况都应该输入完整的网址，如：http://www.74cms.com/bbs</p>
    </div>
    <form action="<?php echo U('navigation/edit');?>" method="post" enctype="multipart/form-data"  name="FormData" id="FormData" >
        <div class="toptit">修改导航栏</div>
        <div class="form_main width150">
            <div class="fl">类型：</div>
            <div class="fr">
                <div class="imgradio">
                    <input name="urltype" class="J_urltype_val" type="hidden" value="<?php echo ($info["urltype"]); ?>">
                    <div class="J_urltype radio <?php if($info['urltype'] == 0): ?>select<?php endif; ?>" data="0" title="系统内容">系统内容</div>
                    <div class="J_urltype radio <?php if($info['urltype'] == 1): ?>select<?php endif; ?>" data="1" title="其他链接">其他链接</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="fl">栏目名称(必填)：</div>
            <div class="fr">
                <input name="title" type="text" maxlength="30" class="input_text_default" value="<?php echo ($info["title"]); ?>"/>
            </div>
            <div class="http" style="display:none;">
                <div class="fl">链接地址：</div>
                <div class="fr">
                    <input name="url" type="text" maxlength="200" class="input_text_default" value="<?php echo ($info["url"]); ?>"/>
                </div>
            </div>
            <div class="sys">
                <div class="fl">系统页面：</div>
                <div class="fr">
                    <div class="select_input_new J_hoverinput J_dropdown J_listitme_parent">
                        <span class="J_listitme_text">选择页面</span>
                        <div class="dropdowbox_sn J_dropdown_menu">
                            <div class="dropdow_inner_sn">
                                <ul class="nav_box">
                                    <?php if(is_array($page_list)): $i = 0; $__LIST__ = $page_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$page): $mod = ($i % 2 );++$i;?><li><a class="J_listitme <?php if($key == $info['pagealias']): ?>list_sel<?php endif; ?>" href="javascript:;" data-code="<?php echo ($key); ?>,<?php echo ($page["tag"]); ?>"><?php echo ($page["pname"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                                </ul>
                            </div>
                        </div>
                        <input class="J_listitme_code" name="systemalias" id="systemalias" type="hidden" value="<?php echo ($info["systemalias"]); ?>" />
                    </div>
                    <!-- <select name="systemalias" style="width:205px; font-size:12px;"  onchange="selChangesystemalias(this.value)">
                      <?php if(is_array($page_list)): $i = 0; $__LIST__ = $page_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$page): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>,<?php echo ($page["tag"]); ?>" <?php if($key == $info['pagealias']): ?>selected="selected"<?php endif; ?>><?php echo ($page["pname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select> -->
                </div>
            </div>
            <div class="sys">
                <div class="fl">系统页面ID：</div>
                <div class="fr">
                    <input name="pagealias" type="text" class="input_text_default" value="<?php echo ($info["pagealias"]); ?>"/>
                </div>
            </div>
            <div class="sys">
                <div class="fl">分类ID：</div>
                <div class="fr">
                    <input name="list_id" type="text" class="input_text_default middle" value="<?php echo ($info["list_id"]); ?>"/>
                    <label class="no-fl-note">如该栏目为信息列表页则需要填写分类ID</label>
                </div>
            </div>
            <div class="fl">类别：</div>
            <div class="fr">
                <div class="imgradio">
                    <input name="alias" type="hidden" value="<?php echo ($info["alias"]); ?>">
                    <?php if(is_array($categroy)): $i = 0; $__LIST__ = $categroy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$categroy): $mod = ($i % 2 );++$i;?><div class="radio <?php if($i == 1 or $info['alias'] == $key): ?>select<?php endif; ?>" data="<?php echo ($key); ?>" title="<?php echo ($categroy); ?>"><?php echo ($categroy); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="fl">打开方式：</div>
            <div class="fr">
                <div class="imgradio">
                    <input name="target" type="hidden" value="">
                    <div class="radio <?php if($info['target'] == '_blank'): ?>select<?php endif; ?>" data="_blank" title="新窗口">新窗口</div>
                    <div class="radio <?php if($info['target'] == '_self'): ?>select<?php endif; ?>" data="_self" title="当前窗口">当前窗口</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="fl">显示顺序：</div>
            <div class="fr">
                <input name="navigationorder" type="text" class="input_text_default middle" value="<?php echo ($info["navigationorder"]); ?>"  maxlength="3"/>
            </div>
            <div class="fl">是否显示：</div>
            <div class="fr">
                <div data-code="0,1" class="imgchecked_small <?php if($info['display'] == 1): ?>select<?php endif; ?>"><input name="display" type="hidden" value="<?php echo ($info["display"]); ?>" /></div>
                <div class="clear"></div>
            </div>
            <div class="fl">显示颜色：</div>
            <div class="fr" style="padding-top:12px;">
              <div class="color_layer"> 
                <input type="text" name="color" id="tit_color" value="<?php echo ($info['color']); ?>" style="display:none">
                <div id="color_box" onclick="color_box_display()" style="background:<?php echo ($info['color']); ?>;"></div>
                <div id="select_color_box">
	<div class="color_title">选择标题颜色：</div>
	<div class="color_box_close" onclick="color_box_display()">关闭</div>
	<div class="clear"></div>
	<a onclick="set_color('');color_box_display()" href="javascript:void(0);" style="background-image:url(../public/images/color_box_bg.gif)"></a>
	<a onclick="set_color('#000000');color_box_display()" href="javascript:void(0);" style=" background-color:#000000"></a>
	<a onclick="set_color('#333333');color_box_display()" href="javascript:void(0);" style=" background-color:#333333"></a>
	<a onclick="set_color('#666666');color_box_display()" href="javascript:void(0);" style=" background-color:#666666"></a>
	<a onclick="set_color('#000099');color_box_display()" href="javascript:void(0);" style=" background-color:#000099"></a>
	<a onclick="set_color('#0066FF');color_box_display()" href="javascript:void(0);" style=" background-color:#0066FF"></a>
	<a onclick="set_color('#9900FF');color_box_display()" href="javascript:void(0);" style=" background-color:#9900FF"></a>
	<a onclick="set_color('#990000');color_box_display()" href="javascript:void(0);" style=" background-color:#990000"></a>
	<a onclick="set_color('#FF0000');color_box_display()" href="javascript:void(0);" style=" background-color:#FF0000"></a>
	<a onclick="set_color('#FF6600');color_box_display()" href="javascript:void(0);" style=" background-color:#FF6600"></a>
	<a onclick="set_color('#669900');color_box_display()" href="javascript:void(0);" style=" background-color:#669900"></a>
	<a onclick="set_color('#336600');color_box_display()" href="javascript:void(0);" style=" background-color:#336600"></a>
	<div class="clear"></div>
</div>
<script type="text/javascript">
	function set_color(x){
		var rgb=x;
		if (rgb==""){
			document.getElementById('color_box').style.background='url(../public/images/color_box_bg.gif)';
		}else{
			document.getElementById('color_box').style.background=rgb;
		}
		//alert(rgb);
		document.getElementById('tit_color').value= rgb;
	}
	function color_box_display(){
		target=document.getElementById('select_color_box');
		if (target.style.display=="block"){
			target.style.display="none";
		} else {
			target.style.display="block";
		}
		//document.bgColor =rgb;
	}
</script>
              </div>
            </div>
            <div class="clear"></div>
            <div class="fl">导航关联标记：</div>
            <div class="fr">
                <input name="tag" type="text" class="input_text_default middle" value="<?php echo ($info["tag"]); ?>"  maxlength="30"/>
            </div>
            <div class="fl"></div>
            <div class="fr">
                <input name="id" type="hidden" value="<?php echo ($info["id"]); ?>">
                <input type="submit" name="Submit3" value="确定提交" class="admin_submit"   />
                <input name="submit222" type="button" class="admin_submit" value="返 回" <?php if($_GET['url']!= ''): ?>onclick="window.location='<?php echo (_I($_GET['url'])); ?>'"<?php else: ?>onclick="window.location='<?php echo U('navigation/index');?>'"<?php endif; ?>/>
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
    function hide_show(){
        if (!eval($('.J_urltype_val').val())) {
            $(".sys").show();
            $(".http").hide();
        } else {
            $(".sys").hide();
            $(".http").show();
        }
    }
    $(document).ready(function() {
        if ($('.J_listitme.list_sel').length) {
            var listSelCn = $('.J_listitme.list_sel').text();
            $('.J_listitme.list_sel').closest('.J_listitme_parent').find('.J_listitme_text').text(listSelCn);
        }
        hide_show();
        $('.J_urltype').click(function(){
            if (eval($('.J_urltype_val').val())) {
                $(".sys").show();
                $(".http").hide();
            } else {
                $(".sys").hide();
                $(".http").show();
            }
        })
    })
</script>
<script>
$('.J_listitme').live('click', function(event) {
    selChangesystemalias($(this).data('code'));
});
function selChangesystemalias(obj)
{
var str=obj.split(",");
$('input[name="pagealias"]').val(str[0]);
$('input[name="tag"]').val(str[1]);
}
selChangesystemalias($('.J_listitme.list_sel').data('code'));
</script>
</body>
</html>