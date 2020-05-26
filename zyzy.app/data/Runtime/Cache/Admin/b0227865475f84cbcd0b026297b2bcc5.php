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
		var URL = '/index.php/Admin/SetCom',
			SELF = '/index.php?m=Admin&amp;c=SetCom&amp;a=index',
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
    <p>不同的运营阶段您可以选择不同的设置。</p>
</div>
<div class="toptit">基本设置</div>
<form id="form1" action="<?php echo U('index');?>" method="post">
    <div class="form_main width200">
        <div class="fl">上传营业执照文件限制:</div>
        <div class="fr">
            <input name="certificate_max_size" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_certificate_max_size');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/> kb
        </div>
        <div class="fl">企业LOGO文件限制:</div>
        <div class="fr">
            <input name="logo_max_size" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_logo_max_size');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/> kb
        </div>
        <div class="fl">职位列表数最大值:</div>
        <div class="fr">
            <input name="jobs_list_max" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_jobs_list_max');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/> 条
        </div>
        <div class="fl">是否开启订单发票:</div>
        <div class="fr">
            <div data-code="0,1" class="imgchecked_small <?php if((C("qscms_open_invoice")) == "1"): ?>select<?php endif; ?>"><input name="open_invoice" type="hidden" value="<?php echo C('qscms_open_invoice');?>" /></div>
            <div class="clear"></div>
        </div>
        <div class="fl">申请发票的订单最小金额:</div>
        <div class="fr">
            <input name="invoice_cash_min" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_invoice_cash_min');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/> 元
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" value="保存修改" id="J_submit1"/>
            <input type="button" class="admin_submit" value="返回" onClick="history.go(-1)"/>
        </div>
        <div class="clear"></div>
    </div>
</form>

<div class="toptit">显示设置</div>
<form id="form2" action="<?php echo U('index');?>" method="post">
    <div class="form_main width200">
        <div class="fl">职位显示:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="jobs_display" type="hidden" value="<?php echo C('qscms_jobs_display');?>">
                <div class="radio <?php if((C("qscms_jobs_display")) == "1"): ?>select<?php endif; ?>" data="1" title="先审核再显示">先审核再显示</div>
                <div class="radio <?php if((C("qscms_jobs_display")) == "2"): ?>select<?php endif; ?>" data="2" title="先显示再审核">先显示再审核</div>
                <label class="note-radio">（先显示后审核可提高用户体验和程序执行效率)</label>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl">企业风采图片:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="companyimg_display" type="hidden" value="<?php echo C('qscms_companyimg_display');?>">
                <div class="radio <?php if((C("qscms_companyimg_display")) == "1"): ?>select<?php endif; ?>" data="1" title="先审核再显示">先审核再显示</div>
                <div class="radio <?php if((C("qscms_companyimg_display")) == "2"): ?>select<?php endif; ?>" data="2" title="先显示再审核">先显示再审核</div>
                <label class="note-radio">（先显示后审核可提高用户体验和程序执行效率)</label>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl">职位列表默认显示方式:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="jobs_list_show_type" type="hidden" value="<?php echo C('qscms_jobs_list_show_type');?>">
                <div class="radio <?php if(C('qscms_jobs_list_show_type') == 1): ?>select<?php endif; ?>" data="1" title="详细">详细</div>
                <div class="radio <?php if(C('qscms_jobs_list_show_type') == 2): ?>select<?php endif; ?>" data="2" title="列表">列表</div>
                <label class="note-radio">（职位搜索页，职位显示方式)</label>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" value="保存修改" id="J_submit2"/>
            <input type="button" class="admin_submit" value="返回" onClick="history.go(-1)"/>
        </div>
        <div class="clear"></div>
    </div>
</form>

<div class="toptit">查看联系方式设置</div>
<form id="form3" action="<?php echo U('index');?>" method="post">
    <div class="form_main width200">
        <div class="fl">web端允许查看联系方式:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="showjobcontact" type="hidden" value="<?php echo C('qscms_showjobcontact');?>">
                <div class="radio <?php if((C("qscms_showjobcontact")) == "0"): ?>select<?php endif; ?>" data="0" title="游客">游客</div>
                <div class="radio <?php if((C("qscms_showjobcontact")) == "1"): ?>select<?php endif; ?>" data="1" title="已登录">已登录</div>
                <div class="radio <?php if((C("qscms_showjobcontact")) == "2"): ?>select<?php endif; ?>" data="2" title="已登录有简历">已登录有简历</div>
                <div class="radio <?php if((C("qscms_showjobcontact")) == "3"): ?>select<?php endif; ?>" data="3" title="投递后显示">投递后显示</div>
                <div class="radio <?php if((C("qscms_showjobcontact")) == "4"): ?>select<?php endif; ?>" data="4" title="微信扫码获取">微信扫码获取</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl">移动端允许查看联系方式:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="showjobcontact_wap" type="hidden" value="<?php echo C('qscms_showjobcontact_wap');?>">
                <div class="radio <?php if((C("qscms_showjobcontact_wap")) == "0"): ?>select<?php endif; ?>" data="0" title="游客">游客</div>
                <div class="radio <?php if((C("qscms_showjobcontact_wap")) == "1"): ?>select<?php endif; ?>" data="1" title="已登录">已登录</div>
                <div class="radio <?php if((C("qscms_showjobcontact_wap")) == "2"): ?>select<?php endif; ?>" data="2" title="已登录有简历">已登录有简历</div>
                <div class="radio <?php if((C("qscms_showjobcontact_wap")) == "3"): ?>select<?php endif; ?>" data="3" title="投递后显示">投递后显示</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" value="保存修改" id="J_submit3"/>
            <input type="button" class="admin_submit" value="返回" onClick="history.go(-1)"/>
        </div>
        <div class="clear"></div>
    </div>
</form>

<div class="toptit">企业联系方式设置<span style="color: #999999; font-size: 12px;">（图形化需要将中文字体文件上传到data/contactimgfont/，字体文件命名为“cn.ttc”)</span></div>
<form id="form4" action="<?php echo U('index');?>" method="post">
    <div class="form_main width200">
        <div class="fl">展示方式:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="contact_img_com" type="hidden" value="<?php echo C('qscms_contact_img_com');?>">
                <div class="radio <?php if((C("qscms_contact_img_com")) == "1"): ?>select<?php endif; ?>" data="1" title="文字">文字</div>
                <div class="radio <?php if((C("qscms_contact_img_com")) == "2"): ?>select<?php endif; ?>" data="2" title="图形化">图形化</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" value="保存修改" id="J_submit4"/>
            <input type="button" class="admin_submit" value="返回" onClick="history.go(-1)"/>
        </div>
        <div class="clear"></div>
    </div>
</form>

<div class="toptit">其他设置</div>
<form id="form5" action="<?php echo U('index');?>" method="post">
    <div class="form_main width200">
        <div class="fl">刷新职位时间间隔:</div>
        <div class="fr">
            <input name="refresh_jobs_space" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_refresh_jobs_space');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/> 分钟
            <label class="no-fl-note">(0表示不限制)</label>
        </div>
        <div class="fl">申请同一职位时间间隔:</div>
        <div class="fr">
            <input name="apply_jobs_space" type="text" class="input_text_default middle" maxlength="10" value="<?php echo C('qscms_apply_jobs_space');?>" onkeyup="if(event.keyCode !=37 && event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))"/>天
            <label class="no-fl-note">(0表示不限制)</label>
        </div>
        <div class="fl">允许公司名称重复:</div>
        <div class="fr">
            <div data-code="0,1" class="imgchecked_small <?php if((C("qscms_company_repeat")) == "1"): ?>select<?php endif; ?>"><input name="company_repeat" type="hidden" value="<?php echo C('qscms_company_repeat');?>" /></div>
            <div class="clear"></div>
        </div>
        <div class="fl">会员短信费用承担方:</div>
        <div class="fr">
            <div class="imgradio">
                <input name="company_sms" type="hidden" value="<?php echo C('qscms_company_sms');?>">
                <div class="radio <?php if((C("qscms_company_sms")) == "1"): ?>select<?php endif; ?>" data="1" title="企业承担">企业承担</div>
                <div class="radio <?php if((C("qscms_company_sms")) == "0"): ?>select<?php endif; ?>" data="0" title="运营者承担">运营者承担</div>
                <label class="note-radio">（如设置为企业承担，则企业需要在增值服务区购买短信包才能发送各种短信提醒）</label>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" value="保存修改" id="J_submit5"/>
            <input type="button" class="admin_submit" value="返回" onClick="history.go(-1)"/>
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
</body>
<script type="text/javascript">
    $(document).ready(function () {
        $('#J_submit1').click(function(){
            var that = $(this);
            if(that.hasClass('disabled')){
                return false;
            }
            that.val('正在保存...').addClass('disabled');
            $.post("<?php echo U('index');?>",$('#form1').serialize(),function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg,function(){
                        location.reload();
                    });
                }else{
                    disapperTooltip("remind", result.msg,function(){
                        that.val('保存').removeClass('disabled');
                    });
                    return false;
                }
            },'json');
        });
        $('#J_submit2').click(function(){
            var that = $(this);
            if(that.hasClass('disabled')){
                return false;
            }
            that.val('正在保存...').addClass('disabled');
            $.post("<?php echo U('index');?>",$('#form2').serialize(),function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg,function(){
                        location.reload();
                    });
                }else{
                    disapperTooltip("remind", result.msg,function(){
                        that.val('保存').removeClass('disabled');
                    });
                    return false;
                }
            },'json');
        });
        $('#J_submit3').click(function(){
            var that = $(this);
            if(that.hasClass('disabled')){
                return false;
            }
            that.val('正在保存...').addClass('disabled');
            $.post("<?php echo U('index');?>",$('#form3').serialize(),function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg,function(){
                        location.reload();
                    });
                }else{
                    disapperTooltip("remind", result.msg,function(){
                        that.val('保存').removeClass('disabled');
                    });
                    return false;
                }
            },'json');
        });
        $('#J_submit4').click(function(){
            var that = $(this);
            if(that.hasClass('disabled')){
                return false;
            }
            that.val('正在保存...').addClass('disabled');
            $.post("<?php echo U('index');?>",$('#form4').serialize(),function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg,function(){
                        location.reload();
                    });
                }else{
                    disapperTooltip("remind", result.msg,function(){
                        that.val('保存').removeClass('disabled');
                    });
                    return false;
                }
            },'json');
        });
        $('#J_submit5').click(function(){
            var that = $(this);
            if(that.hasClass('disabled')){
                return false;
            }
            that.val('正在保存...').addClass('disabled');
            $.post("<?php echo U('index');?>",$('#form5').serialize(),function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg,function(){
                        location.reload();
                    });
                }else{
                    disapperTooltip("remind", result.msg,function(){
                        that.val('保存').removeClass('disabled');
                    });
                    return false;
                }
            },'json');
        });
    });
</script>
</html>