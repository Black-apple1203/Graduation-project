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
		var URL = '/index.php/Admin/Safety',
			SELF = '/index.php?m=Admin&amp;c=Safety&amp;a=index&amp;type=captcha',
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
    <p class="link_green_line">骑士人才系统已集成多种渠道安全验证，使用验证需正确配置相关渠道信息。</p>
    <p class="link_green_line">配置极验渠道请先<a href="http://www.geetest.com" target="_blank">申请极验密钥</a>，配置Vaptcha手势验证请先<a href="https://www.vaptcha.com/" target="_blank">申请Vaptcha密钥</a>，配置腾讯验证请先<a href="https://cloud.tencent.com/product/captcha" target="_blank">申请腾讯密钥</a>。</p>
</div>
<form id="form1">
    <div class="toptit">PC端极验设置</div>
    <div class="form_main width150">
        <div class="fl">开启验证：</div>
        <div class="fr">
            <div data-code="0,1" class="J_gt imgchecked_small <?php if(C('qscms_captcha_open') == 1): ?>select<?php endif; ?>"><input name="captcha_open" type="hidden" value="<?php echo C('qscms_captcha_open');?>" /></div>
            <div class="clear"></div>
        </div>
        <div id="gtBox" <?php if(C('qscms_captcha_open') == 0): ?>style="display:none;"<?php endif; ?>>
            <div class="fl">验证渠道：</div>
            <div class="fr">
                <div class="imgradio J_job_pw_type">
                    <input name="captcha_type" type="hidden" value="<?php echo C('qscms_captcha_type');?>">
                    <div class="radio <?php echo C('qscms_captcha_type')=='geetest' ? 'select' : '';?>" data="geetest" title="极验">极验</div>
                    <div class="radio <?php echo C('qscms_captcha_type')=='vaptcha' ? 'select' : '';?>" data="vaptcha" title="vaptcha">vaptcha(手势验证)</div>
                    <div class="radio <?php echo C('qscms_captcha_type')=='tencent' ? 'select' : '';?>" data="tencent" title="tencent">腾讯</div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="J_type" data="geetest" <?php if(C('qscms_captcha_type') != 'geetest'): ?>style="display:none"<?php endif; ?>>
                <div class="fl">验证ID：</div>
                <div class="fr">
                    <input name="captcha_geetest[id]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_geetest.id');?>"/>
                </div>
                <div class="fl">验证KEY：</div>
                <div class="fr">
                    <input name="captcha_geetest[key]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_geetest.key');?>"/>
                </div>
            </div>
            <div class="J_type" data="vaptcha" <?php if(C('qscms_captcha_type') != 'vaptcha'): ?>style="display:none"<?php endif; ?>>
                <div class="fl">验证ID：</div>
                <div class="fr">
                    <input name="captcha_vaptcha[id]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_vaptcha.id');?>"/>
                </div>
                <div class="fl">验证KEY：</div>
                <div class="fr">
                    <input name="captcha_vaptcha[key]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_vaptcha.key');?>"/>
                </div>
            </div>
            <div class="J_type" data="tencent" <?php if(C('qscms_captcha_type') != 'tencent'): ?>style="display:none"<?php endif; ?>>
                <div class="fl">验证ID：</div>
                <div class="fr">
                    <input name="captcha_tencent[id]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_tencent.id');?>"/>
                </div>
                <div class="fl">验证KEY：</div>
                <div class="fr">
                    <input name="captcha_tencent[key]" type="text" class="input_text_default" value="<?php echo C('qscms_captcha_tencent.key');?>"/>
                </div>
            </div>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input name="submit1" id="J_submit1" type="button" class="admin_submit" value="保存"/>
        </div>
        <div class="clear"></div>
    </div>
</form>

<form id="form2" >
    <div class="toptit">验证项目</div>
    <div class="form_main width150">
        <div class="fl">手机验证：</div>
        <div class="fr">
            <div data-code="0,1" class="imgchecked_small <?php if(C('qscms_captcha_config.varify_mobile') == 1): ?>select<?php endif; ?>"><input name="captcha_config[varify_mobile]" type="hidden" value="<?php echo C('qscms_captcha_config.varify_mobile');?>" /></div>
            <div class="note">（开启后，用户在注册、验证手机号发送短信时发起验证）</div>
            <div class="clear"></div>
        </div>
        <div class="fl">意见/建议：</div>
        <div class="fr">
            <div data-code="0,1" class="imgchecked_small <?php if(C('qscms_captcha_config.varify_suggest') == 1): ?>select<?php endif; ?>"><input name="captcha_config[varify_suggest]" type="hidden" value="<?php echo C('qscms_captcha_config.varify_suggest');?>" /></div>
            <div class="note">（开启后，用户在提交意见建议时发起验证）</div>
            <div class="clear"></div>
        </div>
        <div class="fl">会员登录：</div>
        <div class="fr">
            <input name="captcha_config[user_login]" type="text" class="input_text_default small" value="<?php echo C('qscms_captcha_config.user_login');?>"/>
            <label class="no-fl-note">（填写数字，0为开启验证码，如设置为3，则错误3次才发起验证）</label>
        </div>
        <div class="fl">后台登录：</div>
        <div class="fr">
            <input name="captcha_config[admin_login]" type="text" class="input_text_default small" value="<?php echo C('qscms_captcha_config.admin_login');?>"/>
            <label class="no-fl-note">（填写数字，0为开启验证码，如设置为3，则错误3次才发起验证）</label>
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input name="submit2" id="J_submit2" type="button" class="admin_submit" value="保存"/>
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
<script src="https://static.geetest.com/static/tools/gt.js"></script>
<script type="text/javascript">
      $(".J_gt").click(function(){
        if($(this).hasClass('select')){
          $("#gtBox").hide();
        }else{
          $("#gtBox").show();
        }
      })
    $('.J_job_pw_type .radio').click(function(){
        var type = $(this).attr('data');
        $('.J_type').hide();
        $('.J_type[data="'+type+'"]').show();
    });
    $('#J_submit1').click(function(){
        var that = $(this);
        if(that.hasClass('disabled')){
            return false;
        }
        that.val('正在保存...').addClass('disabled');
        $.post("<?php echo U('safety/index');?>",$('#form1').serialize(),function(result){
            if(result.status==1){
                disapperTooltip("success", result.msg,function(){
                    that.val('保存').removeClass('disabled');
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
        $.post("<?php echo U('safety/index');?>",$('#form2').serialize(),function(result){
            if(result.status==1){
                disapperTooltip("success", result.msg,function(){
                    that.val('保存').removeClass('disabled');
                });
            }else{
                disapperTooltip("remind", result.msg,function(){
                    that.val('保存').removeClass('disabled');
                });
                return false;
            }
        },'json');
    });
</script>
</body>
</html>