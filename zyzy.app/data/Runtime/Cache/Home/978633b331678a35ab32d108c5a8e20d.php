<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo ($page_seo["title"]); ?></title>
<meta name="keywords" content="<?php echo ($page_seo["keywords"]); ?>"/>
<meta name="description" content="<?php echo ($page_seo["description"]); ?>"/>
<meta name="author" content="zy拉钩人才系统"/>
<meta name="copyright" content="74cms.com"/>
<link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
<?php if($canonical != ''): ?><link rel="canonical" href="<?php echo ($canonical); ?>"/><?php endif; ?>
<script src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/jquery.min.js"></script>
<script src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/htmlspecialchars.js"></script>
<?php switch($captcha_type = C('qscms_captcha_type')): case "geetest": ?><script src="https://static.geetest.com/static/tools/gt.js"></script><?php break;?>
    <?php case "vaptcha": ?><script src="https://v.vaptcha.com/v3.js"></script><?php break;?>
    <?php case "tencent": ?><script src="https://ssl.captcha.qq.com/TCaptcha.js"></script><?php break; endswitch;?>
<script type="text/javascript">
	var app_spell = "<?php echo APP_SPELL;?>";
	var qscms = {
		base : "",
		keyUrlencode:"<?php echo C('qscms_key_urlencode');?>",
		domain : "http://<?php echo ($_SERVER['HTTP_HOST']); ?>",
		root : "/index.php",
		companyRepeat:"<?php echo C('qscms_company_repeat');?>",
		regularMobile: /^13[0-9]{9}$|14[0-9]{9}$|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$|16[0-9]{9}$|19[0-9]{9}$/,
		district_level : "<?php echo C('qscms_category_district_level');?>",
		smsTatus: "1",
		captcha_open:"<?php echo C('qscms_captcha_open');?>",
		varify_mobile:"<?php echo C('qscms_captcha_config.varify_mobile');?>",
		varify_suggest:"<?php echo C('qscms_captcha_config.varify_suggest');?>",
        varify_user_login:"<?php echo ($verify_userlogin); ?>",
		is_login:"<?php if($visitor): ?>1<?php else: ?>0<?php endif; ?>",
		default_district : "<?php echo C('qscms_default_district');?>",
		default_district_spell : "<?php echo C('qscms_default_district_spell');?>",
        subsite: "<?php echo C('qscms_subsite_open');?>"
	};
    /*ie兼容 Promise*/
    isIE();
    function isIE() {
        if ( !! window.ActiveXObject || "ActiveXObject" in window) {
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.src = "<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/bluebird.js";
            document.getElementsByTagName('head')[0].appendChild(script);
        }
    }
    /*ie兼容 Promise end*/
	$(function(){
	    if (eval(qscms.subsite)) {
	        // 开启分站
            $.getJSON("<?php echo U('Home/AjaxCommon/get_sub_header_min');?>",function(result){
                if(eval(result.status) === 1){
                    $('#J_header_sub').html(result.data.html);
                }
            });
        } else {
            $.getJSON("<?php echo U('Home/AjaxCommon/get_header_min');?>",function(result){
                if(eval(result.status) === 1){
                    $('#J_header').html(result.data.html);
                }
            });
        }
	})
	// 验证码统一处理
	function qsCaptchaHandler(passCallback) {
		var callBackArr = new Array();
		$.ajax({
			url: qscms.root + '?m=Home&c=captcha&t=' + (new Date()).getTime(),
			type: 'get',
			dataType: 'json',
			success: function(config) {
				if (config.verify_type == 'vaptcha') {
					// 手势验证码
					vaptcha({
					    vid: config.vid,
					    type: 'invisible',
					    scene: 1,
					    https: config.https,
					    offline_server:qscms.root+'?m=Home&c=captcha&a=vaptcha_outage',
					}).then(function (vaptchaObj) {
					    obj = vaptchaObj;
					    vaptchaObj.listen('pass', function() {
							callBackArr['token'] = vaptchaObj.getToken();
							passCallback(callBackArr);
						});
					    vaptchaObj.listen('close', function() {});
					    vaptchaObj.validate();
					});
				} else if (config.verify_type == 'tencent') {
					// 腾讯云验证码
					var TCaptchaObj = new TencentCaptcha(config.vid, function(res) {
						if(res.ret === 0){
							callBackArr['Ticket'] = res.ticket;
							callBackArr['Randstr'] = res.randstr;
							passCallback(callBackArr);
						}
					});
					TCaptchaObj.show();
				} else {
					// 极验
					initGeetest({
					    gt: config.gt,
					    challenge: config.challenge,
					    offline: !config.success,
					    new_captcha: config.new_captcha,
					    product: 'bind',
						https: true
					}, function(captchaObj) {
					    captchaObj.appendTo("#pop");
					    captchaObj.onSuccess(function() {
							var captChaResult = captchaObj.getValidate();
							callBackArr['geetest_challenge'] = captChaResult.geetest_challenge;
							callBackArr['geetest_validate'] = captChaResult.geetest_validate;
							callBackArr['geetest_seccode'] = captChaResult.geetest_seccode;
							if ($('.J_gee_cha')) {
								$('.J_gee_cha').val(captChaResult.geetest_challenge);
								$('.J_gee_val').val(captChaResult.geetest_validate);
								$('.J_gee_sec').val(captChaResult.geetest_seccode);
							}
					        passCallback(callBackArr);
					    })
					    captchaObj.onReady(function() {
					        captchaObj.verify();
					    });
					    $('#btnCheck').on('click', function() {
					        captchaObj.verify();
					    })
					    window.captchaObj = captchaObj;
					});
				}
			}
		})
	}
</script>
<?php echo ($synsitegroupregister); ?>
<?php echo ($synsitegroupunbindmobile); ?>
<?php echo ($synsitegroupedit); ?>
<?php echo ($synsitegroup); ?>
	<link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/common.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/m.css" rel="stylesheet" type="text/css" />
	<?php $tag_load_class = new \Common\qscmstag\loadTag(array('type'=>'category','cache'=>'0','列表名'=>'list',));$list = $tag_load_class->category();?>
</head>
<body>
<div class="header_min" id="header">
	<div class="header_min_top <?php if(C('qscms_subsite_open') == 1): ?>sub<?php endif; ?>">
		<div id="J_header" class="itopl font_gray6 link_gray6">
			<?php if(C('qscms_subsite_open') == 1): ?><!--分站-->
				<div class="sub_city_box">
					<div class="city_switch">
						<div class="c_item"><span><?php echo ($subsite_list[$subsite['s_id']]['s_sitename']); ?></span>&nbsp;&nbsp;[&nbsp;&nbsp;切换站点</div>
						<div class="city_drop">
							<div class="d_tit">点击进入&nbsp;&nbsp;<a href="<?php echo C('qscms_site_domain');?>">[&nbsp;&nbsp;总站&nbsp;&nbsp;]</a></div>
							<div class="d_list">
								<?php if(is_array($subsite_list)): $i = 0; $__LIST__ = array_slice($subsite_list,0,20,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['s_id'] == 0): ?><a href="<?php echo C('qscms_site_domain');?>" class="d_item">总站</a><?php else: ?>
										<a href="<?php echo ($subsite_list[$vo['s_id']]['pc_type']); echo ($subsite_list[$vo['s_id']]['s_domain']); ?>" class="d_item"><?php echo ($subsite_list[$vo['s_id']]['s_sitename']); ?></a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
							<?php if(count($subsite_list) > 20): ?><a href="<?php echo U('Home/Index/subsite');?>" class="d_item">更多分站>></a><?php endif; ?><div class="clear"></div>
							</div>
						</div>
					</div>
					<div class="city_near">
					<a href="<?php echo C('qscms_site_domain');?>" class="d_item">总站</a>
						<!-- <?php if(is_array($subsite_list)): $i = 0; $__LIST__ = array_slice($subsite_list,0,2,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['s_id'] != C('subsite_info.s_id')): if($vo['s_id'] == 0): ?><a href="<?php echo C('qscms_site_domain');?>" class="d_item">总站</a><?php else: ?>
							<a href="<?php echo ($subsite_list[$vo['s_id']]['pc_type']); echo ($subsite_list[$vo['s_id']]['s_domain']); ?>" class="c_name"><?php echo ($subsite_list[$vo['s_id']]['s_sitename']); ?></a><?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>	 -->
					</div>
					&nbsp;&nbsp;]
					<div class="clear"></div>
				</div>
			<?php else: ?>
				<span class="link_yellow">欢迎登录<?php echo C('qscms_site_name');?>！请 <a id="J_site_login" href="javascript:;">登录</a> 或 <a id="J_site_reg" href="javascript:;">免费注册</a></span><?php endif; ?>
		</div>
		<div class="itopr font_gray9 link_gray6" id="J_header_sub">
			<a href="/" class="home">网站首页</a>|
			<a href="<?php echo url_rewrite('QS_m');?>" class="m">手机访问</a>|
			<a href="<?php echo url_rewrite('QS_help');?>" class="help">帮助中心</a>|
			<a href="<?php echo U('Home/Index/shortcut');?>" class="last">保存到桌面</a>
		</div>
	    <div class="clear"></div>
	</div>
</div>

<div id="mobile-header">
    <div class="container clearfix">
        <div class="m-logo f-left"><a class="f-left" href="/"><img src="<?php if(C('qscms_logo_home')): echo attach(C('qscms_logo_home'),'resource'); else: echo C('TPL_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" border="0"></a><a href="/" class="back-index f-left"></a></div>
        <ul class="mobile-nav f-right">
            <li <?php if($type == 'android' || $type == ''): ?>class="active"<?php endif; ?>><a href="<?php echo url_rewrite('QS_m');?>">安卓版</a></li>
            <li <?php if($type == 'ios'): ?>class="active"<?php endif; ?>><a href="<?php echo url_rewrite('QS_m',array('type'=>'ios'));?>">苹果版</a></li>
            <li <?php if($type == 'touch'): ?>class="active"<?php endif; ?>><a href="<?php echo url_rewrite('QS_m',array('type'=>'touch'));?>">触屏版</a></li>
            <li class="last <?php if($type == 'weixin'): ?>active<?php endif; ?>"><a href="<?php echo url_rewrite('QS_m',array('type'=>'weixin'));?>">微信版</a></li>
        </ul>
    </div>
</div>
<div id="banner-block">
    <div class="banner-wrap android">
        <div class="container">
            <div class="a-text-wrap">
                <h1>好工作，尽在您的手机里</h1>
                <h3>全新的交互体验  轻轻松松找工作</h3>
                <div class="down-code clearfix">
                    <a href="<?php echo C('qscms_site_domain').U('Mobile/Index/app_download');?>" class="f-left android-download"></a>
                    <div class="down-code-box f-left">
                        <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt="二维码" width="100" height="100" />
                        <p>手机扫一扫，下载APP</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mobile-app-sp container clearfix">
    <div class="mobile-app-item f-left">
        <div class="ma-icon icon-one"></div>
        <h4>淘人才</h4>
        <p>快捷搜索人才简历库</p>
        <p>智能匹配合适的人才简历</p>
    </div>
    <div class="mobile-app-item f-left">
        <div class="ma-icon icon-two"></div>
        <h4>摇一摇</h4>
        <p>轻松摇出附近</p>
        <p>的职位信息，方便快捷</p>
    </div>
    <div class="mobile-app-item f-left">
        <div class="ma-icon icon-three"></div>
        <h4>地图搜索</h4>
        <p>实时定位，直观地在地</p>
        <p>图上看到该公司所在位置</p>
    </div>
    <div class="mobile-app-item f-left">
        <div class="ma-icon icon-four"></div>
        <h4>消息推送</h4>
        <p>实时接受最新招聘信</p>
        <p>息、求职动态，高效便捷</p>
    </div>
</div>
<div class="m-main">
    <div class="m-row odd">
        <div class="container clearfix">
            <div class="row-left-text f-left">
                <h2>搜职位</h2>
                <p>支持多种职位搜索条件，便捷的求职平<br />台，为求职者提供最大的职位选择空间。</p>
            </div>
            <div class="row-right-box f-right">
                <div class="phone-block android-phone">
                    <div class="app-img"><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/179.jpg" alt="" /></div>
                </div>
                <div class="enlarge-block"></div>
            </div>
        </div>
    </div>
    <div class="m-row">
        <div class="container clearfix">
            <div class="row-left-img f-left">
                <img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/180.png" alt="" />
            </div>
            <div class="row-right-text f-right">
                <h2>创简历</h2>
                <p>个人会员发布简历，让企业找到您的简历，<br />畅快体验手机找工作的高效快捷。</p>
            </div>
        </div>
    </div>
    <div class="m-row odd">
        <div class="container clearfix">
            <div class="row-left-text f-left">
                <h2>发职位</h2>
                <p>实现掌上智能注册会员发布相关职位，即使<br />不在电脑旁，也能随时随地实现人才招聘。</p>
            </div>
            <div class="row-right-box f-right">
                <div class="phone-block android-phone">
                    <div class="app-img"><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/181.jpg" alt="" /></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="footer_min" id="footer">
	<div class="links link_gray6">
	<a target="_blank" href="<?php echo url_rewrite('QS_index');?>">网站首页</a>
	<?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','分类id'=>'1','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"手机频道 - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$list);?>

	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>|   <a target="_blank" href="<?php echo ($vo['url']); ?>"><?php echo ($vo['title']); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
	|   <a target="_blank" href="<?php echo url_rewrite('QS_suggest');?>">意见建议</a>
	</div>
	<div class="txt">

		联系地址：<?php echo C('qscms_address');?>      联系电话：<?php echo C('qscms_bootom_tel');?><br />

		<?php echo C('qscms_bottom_other');?>     <?php if(C('qscms_icp') != ''): ?><a href="http://www.beian.miit.gov.cn" target="_blank"><?php echo C('qscms_icp');?></a><?php endif; ?>
		<?php echo htmlspecialchars_decode(C('qscms_statistics'));?>

	</div>
</div>

<div class="">
	<div class=""></div>
</div>
<!--[if lt IE 9]>
	<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/PIE.js"></script>
  <script type="text/javascript">
    (function($){
        $.pie = function(name, v){
            // 如果没有加载 PIE 则直接终止
            if (! PIE) return false;
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
                if ($.browser.version*1 <= version*1) {
                    obj.each(function(){
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
<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/jquery.disappear.tooltip.js"></script>
<div class="floatmenu">
<?php if(($show_backtop) == "1"): ?><div class="item mobile">
    <a class="blk"></a>
    <?php if(($show_backtop_app) == "1"): ?><div class="popover <?php if($show_backtop_weixin == 1): ?>popover1<?php endif; ?>">
      <div class="popover-bd">
        <label>手机APP</label>
        <span class="img-qrcode img-qrcode-mobile"><img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt=""></span>
      </div>
    </div><?php endif; ?>
    <?php if(($show_backtop_weixin) == "1"): ?><div class="popover">
      <div class="popover-bd">
        <label class="wx">企业微信</label>
        <span class="img-qrcode img-qrcode-wechat"><img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt=""></span>
      </div>
      <div class="popover-arr"></div>
    </div><?php endif; ?>
  </div><?php endif; ?>
  <div class="item ask">
    <a class="blk" target="_blank" href="<?php echo url_rewrite('QS_suggest');?>"></a>
  </div>
  <div id="backtop" class="item backtop" style="display: none;"><a class="blk"></a></div>
</div>
<SCRIPT LANGUAGE="JavaScript">

var global = {
    h:$(window).height(),
    st: $(window).scrollTop(),
    backTop:function(){
      global.st > (global.h*0.5) ? $("#backtop").show() : $("#backtop").hide();
    }
  }
  $('#backtop').on('click',function(){
    $("html,body").animate({"scrollTop":0},500);
  });
  global.backTop();
  $(window).scroll(function(){
      global.h = $(window).height();
      global.st = $(window).scrollTop();
      global.backTop();
  });
  $(window).resize(function(){
      global.h = $(window).height();
      global.st = $(window).scrollTop();
      global.backTop();
  });
  // 客服QQ
  var app_qq = "<?php echo C('apply.Qqfloat');?>";
  var qq_open = "<?php echo C('qscms_qq_float_open');?>";
  if(app_qq != '' && qq_open == 1){
      var QQFloatUrl = "<?php echo U('Qqfloat/Index/index');?>";
      $.getJSON(QQFloatUrl, function (result) {
          if (result.status == 1) {
              //$(".qq-float").html(result.data);
              $("body").append(result.data);
          }
      });
  }
</SCRIPT>
</body>
</html>