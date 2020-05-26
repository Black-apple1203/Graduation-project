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
	<link href="../public/css/members/common.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css" />
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

<div class="user_head_bg">
	<div class="user_head">
		<div class="logobox">
			<a href="/"><img src="<?php if(C('qscms_logo_home')): echo attach(C('qscms_logo_home'),'resource'); else: ?>../public/images/logo.gif<?php endif; ?>" border="0"/></a>
		</div>
		<div class="logotxt">
			<!-- |&nbsp;&nbsp;
			<?php if(ACTION_NAME == 'login'): ?>会员登录
			<?php else: ?>
				<?php if($utype == 0): ?>会员注册<?php endif; ?>
				<?php if($utype == 1): ?>企业会员注册<?php endif; ?>
				<?php if($utype == 2): ?>个人会员注册<?php endif; endif; ?> -->
		</div>
		<div class="reg">
			<?php if(ACTION_NAME == 'login'): ?>还没有账号？ <a id="J_site_reg" href="javascript:;" class="btn_blue J_hoverbut btn_inline">立即注册</a>
			<?php else: ?>
				已经有账号？ <a href="<?php echo U('members/login');?>" class="btn_blue J_hoverbut btn_inline">立即登录</a><?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
	<div class="nl_con_box">
		<div class="nl_con">
			<div class="nc_tit">会员登录</div>
			<div class="nc_box">
				<div class="ncb_t">
					<div class="ncb_sf"></div>
					<div class="ncb_tc"><a class="J_sw_dt active" href="javascript:;">手机登录</a></div>
					<div class="ncb_tc"><a class="J_sw_dt" href="javascript:;">密码登录</a></div>
					<div class="clear"></div>
				</div>
				<div class="ty_box J_ty_box active">
					<div class="ncb_ib">
						<input type="text" class="ncb_ibr mob J_for_focus" name="mobile" id="mobile" placeholder="请输入手机号" />
						<input type="button" class="ncb_ibb" id="getVerfyCode" value="获取验证码" />
					</div>
					<div class="ncb_ib last">
						<input type="text" class="ncb_ibr J_loginword J_for_focus" name="verfy_code" id="verfy_code" placeholder="请输入手机验证码" />
					</div>
				</div>
				<div class="ty_box J_ty_box">
					<div class="ncb_ib">
						<input type="text" class="ncb_ibr J_for_focus" name="username" id="username" placeholder="手机号/会员名/邮箱" />
					</div>
					<div class="ncb_ib last">
						<input type="password" class="ncb_ibr J_loginword J_for_focus" name="password" id="password" placeholder="请输入密码" />
					</div>
				</div>
				<div class="ncb_ot link_gray9">
					<div class="not_l">
						<label>
							<input name="expire_obile" class="J_expire not_lc" checked="checked" type="checkbox" value="1" /> 
							<span class="not_ls">下次自动登录</span>
						</label>
					</div>
					<div class="not_r"><a href="<?php echo U('members/user_getpass');?>">忘记密码?</a></div>
					<div class="clear"></div>
				</div>
				<div class="ncb_bx">
					<input class="ncb_bx_bt J_ncb_bx_bt active" type="button" id="J_dologinByMobile" value="立即登录" />
					<input class="ncb_bx_bt J_ncb_bx_bt" type="button" id="J_dologin" value="立即登录" />
				</div>
				<div class="ncb_hz">
					<div class="nhz_tx">合作账号登录</div>
				</div>
				<div class="ncb_au_box">
					<div class="nab">
						<?php if(is_array($oauth_list)): $i = 0; $__LIST__ = $oauth_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$oauth): $mod = ($i % 2 );++$i;?><a class="nab_<?php echo ($key); ?>" href="<?php echo U('callback/index',array('mod'=>$key,'type'=>'login'));?>" title="<?php echo ($oauth["name"]); ?>账号登录"></a><?php endforeach; endif; else: echo "" ;endif; ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="new-footer">
    <div class="footer-txt-group nl">
        <div class="ftg-main">
            <div class="ftg-left">
                <div class="ftg-a-group">
                    <?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','显示数目'=>'4','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"会员登录","keywords"=>"","description"=>"","header_title"=>""),$list);?>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo ($vo['url']); ?>" target="_blank" class="fag-link-cell"><?php echo ($vo['title']); ?></a><span class="hl">|</span><?php endforeach; endif; else: echo "" ;endif; ?>
                    <span class="tel">联系电话：<?php echo C('qscms_bootom_tel');?></span>
                </div>
                <p class="copyright">联系地址：<?php echo C('qscms_address');?> &nbsp;&nbsp;网站备案：<?php if(C('qscms_icp') != ''): ?><a href="http://www.beian.miit.gov.cn" target="_blank"><?php echo C('qscms_icp');?></a><?php endif; ?></p>
                <p class="copyright"><?php echo C('qscms_bottom_other');?> &nbsp;&nbsp;Powered by <a href="http://www.74cms.com">74cms</a> v<?php echo C('QSCMS_VERSION');?> <?php echo htmlspecialchars_decode(C('qscms_statistics'));?></p>
            </div>
            <div class="ftg-right">
                <div class="qr-box">
                    <div class="img"><img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>"></div>
                    <div class="qr-txt">公众号</div>
                </div>
                <?php if(!empty($apply['Mobile'])): ?><div class="qr-box">
                        <div class="img"><img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url());?>"></div>
                        <div class="qr-txt">触屏端</div>
                    </div><?php endif; ?>
                <?php if(C('qscms_weixinapp_qrcode') && $apply['Weixinapp']): ?><div class="qr-box">
                    <div class="img"><img src="<?php echo attach(C('qscms_weixinapp_qrcode'),'images');?>"></div>
                    <div class="qr-txt">微信小程序</div>
                </div><?php endif; ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div class="floatmenu">
    <?php if(($show_backtop) == "1"): ?><div class="item mobile">
            <a class="blk"></a>
            <?php if(($show_backtop_app) == "1"): ?><div class="popover <?php if( $show_backtop_weixin == 1): ?>popover1<?php endif; ?>">
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
    <div class="item ask"><a class="blk" target="_blank" href="<?php echo url_rewrite('QS_suggest');?>"></a></div>
    <div id="backtop" class="item backtop" style="display: none;"><a class="blk"></a></div>
</div>

<!--[if lt IE 9]>
<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/PIE.js"></script>
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
    }
</script>
<![endif]-->
<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/jquery.disappear.tooltip.js"></script>
<script type="text/javascript">
  var global = {
    h: $(window).height(),
    st: $(window).scrollTop(),
    backTop: function () {
      global.st > (global.h * 0.5) ? $("#backtop").show() : $("#backtop").hide();
    }
  }
  $('#backtop').on('click', function () {
    $("html,body").animate({"scrollTop": 0}, 500);
  });
  global.backTop();
  $(window).scroll(function () {
    global.h = $(window).height();
    global.st = $(window).scrollTop();
    global.backTop();
  });
  $(window).resize(function () {
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
</script>
	<input type="hidden" id="verify_userlogin" value="<?php echo ($verify_userlogin); ?>">
    <input type="hidden" id="J_captcha_varify_send" value="<?php echo C('qscms_captcha_config.varify_mobile');?>" />
    <input type="hidden" id="J_captcha_open" value="<?php echo C('qscms_captcha_open');?>" />
	<input type="button" id="btnCheck" style="display:none;">
    <input type="button" id="btnCheckLoginMobile" style="display:none;">
    <input type="button" id="btnCheckLoginName" style="display:none;">
	<input type="hidden" id="J_sendVerifyType" value="0">
	<div id="popup-captcha"></div>
	<script src="../public/js/members/jquery.common.js" type="text/javascript" language="javascript"></script>
	<script type="text/javascript">
			$('.J_for_focus').focus(function(){
				$(this).closest('.ncb_ib').addClass("for_focus")
			});
			$('.J_for_focus').blur(function(){
				$(this).closest('.ncb_ib').removeClass("for_focus")
			});
			// 后台是否开启验证
    	var captcha_open = eval($('#J_captcha_open').val());

    	// 发送验证码
	    function sendSms() {
	        $('#getVerfyCode').prop("disabled", !0);
	        $('#getVerfyCode').addClass('btn_disabled');
	        $('#getVerfyCode').val('发送中...');
	        var mobileValue = $.trim($('input[name=mobile]').val());
	        $.ajax({
	            url: qscms.root + '?m=Home&c=Members&a=reg_send_sms',
	            cache: false,
	            async: false,
	            type: 'post',
	            dataType: 'json',
	            data: { sms_type: 'login', mobile: mobileValue},
	            success: function(result) {
	                if (result.status) {
	                    disapperTooltip("success", "验证码已发送，请注意查收");
	                    // 开始倒计时
	                    var countdown = 60;
	                    function settime() {
	                        if (countdown == 0) {
	                            $('#getVerfyCode').prop("disabled", 0);
	                            $('#getVerfyCode').removeClass('btn_disabled');
	                            $('#getVerfyCode').val('获取验证码');
	                            countdown = 60;
	                            return;
	                        } else {
	                            $('#getVerfyCode').prop("disabled", !0);
	                            $('#getVerfyCode').addClass('btn_disabled');
	                            $('#getVerfyCode').val('重发' + countdown + '秒');
	                            countdown--;
	                        }
	                        setTimeout(function() {
	                            settime()
	                        },1000)
	                    }
	                    settime();
	                } else {
	                    $('#getVerfyCode').prop("disabled", 0);
	                    $('#getVerfyCode').removeClass('btn_disabled');
	                    $('#getVerfyCode').val('获取验证码');
	                    disapperTooltip('remind', result.msg);
	                }
	            }
	        });
	    }

		// 获取验证码
		$('#getVerfyCode').click(function(event) {
			var mobileValue = $('input[name="mobile"]').val();
	        if (!mobileValue.length) {
	        	disapperTooltip('remind', '请填写手机号码');
	            $('input[name="mobile"]').focus();
	            return false;
	        }
	        if (mobileValue != "" && !regularMobile.test(mobileValue)) {
	        	disapperTooltip('remind', '手机号码格式不正确');
	            $('input[name="mobile"]').focus();
	            return false;
	        }
            $('#getVerfyCode').prop("disabled", !0);
            $('#getVerfyCode').addClass('btn_disabled');
            $('#getVerfyCode').val('发送中...');
	        $.ajax({
	            url: qscms.root + '?m=Home&c=Members&a=ajax_check',
	            cache: false,
	            async: false,
	            type: 'post',
	            dataType: 'json',
	            data: { type: 'mobile', param: mobileValue },
	            success: function(result) {
	                if (!result.status) {
	                    // 标识为发验证码
	                    $('#J_sendVerifyType').val('1');
	                    if (captcha_open) {
	                        // 后台开启验证
	                        if (parseInt(qscms.smsTatus)) {
	                            if (eval($('#J_captcha_varify_send').val())) {
	                            	qsCaptchaHandler(function(callBackArr) {
										var mobileValue = $.trim($('input[name=mobile]').val());
										var dataArr = {sms_type: 'login', mobile: mobileValue};
										$.extend(dataArr, callBackArr);
				                    	$.ajax({
				                    	 	url: qscms.root + '?m=Home&c=Members&a=reg_send_sms',
                                            cache: false,
                                            async: false,
                                            type: 'post',
                                            dataType: 'json',
                                            data: dataArr,
                                            success: function(result) {
                                            if (result.status) {
                                                    disapperTooltip("success", "验证码已发送，请注意查收");
                                                    // 开始倒计时
                                                    var countdown = 60;
                                                    function settime() {
                                                        if (countdown == 0) {
                                                            $('#getVerfyCode').prop("disabled", 0);
                                                            $('#getVerfyCode').removeClass('btn_disabled');
                                                            $('#getVerfyCode').val('获取验证码');
                                                            countdown = 60;
                                                            return;
                                                        } else {
                                                            $('#getVerfyCode').prop("disabled", !0);
                                                            $('#getVerfyCode').addClass('btn_disabled');
                                                            $('#getVerfyCode').val('重发' + countdown + '秒');
                                                            countdown--;
                                                        }
                                                        setTimeout(function() {
                                                            settime()
                                                        },1000)
                                                    }
                                                    settime();
                                                } else {
                                                    $('#getVerfyCode').prop("disabled", 0);
                                                    $('#getVerfyCode').removeClass('btn_disabled');
                                                    $('#getVerfyCode').val('获取验证码');
                                                    disapperTooltip('remind', result.msg);
                                                }
                                            }
				                    	});
									});
	                            } else {
	                                sendSms();
	                            }
	                        } else {
	                            disapperTooltip("remind", "短信未开启");
	                        }
	                    } else {
	                        sendSms();
	                    }
	                } else {
	                	$('#getVerfyCode').prop("disabled", 0);
                        $('#getVerfyCode').removeClass('btn_disabled');
                        $('#getVerfyCode').val('获取验证码');
	                	disapperTooltip("remind", "账号不存在！");
	                }
	            }
	        });
		});

		// 账号登录
	    $('#J_dologin').die().live('click', function() {
	        var usernameValue = $.trim($('#username').val());
	        var passwordValue = $.trim($('#password').val());
	        var expireValue = $.trim($('input[name=expire]').val());
	        if (usernameValue == "") {
	        	disapperTooltip('remind', '请填写手机号/会员名/邮箱');
	            $('#username').focus();
	            return false;
	        }
	        if (passwordValue == "") {
	        	disapperTooltip('remind', '请填写密码');
	            $('#password').focus();
	            return false;
	        }
	        // $('#J_dologin').val('登录中...').prop('disabled', !0).addClass('btn_disabled');
	        // 登录错误次数达到最大值
	        if(eval(qscms.varify_user_login)){
	            // 标识为登录
	            $('#J_sendVerifyType').val('0');
	            if (captcha_open) {
	                qsCaptchaHandler(function(callBackArr) {
						var usernameValue = $.trim($('#username').val());
                        var passwordValue = $.trim($('#password').val());
                        var expireValue = $.trim($('input[name=expire]').val());
						var dataArr = {username: usernameValue, password: passwordValue, expire: expireValue};
						$.extend(dataArr, callBackArr);
                        // 提交表单
                        $.ajax({
                            url: qscms.root + '?m=Home&c=Members&a=login',
                            type: "post",
                            dataType: "json",
                            data: dataArr,
                            success: function(result) {
                               if (parseInt(result.status)) {
                                    window.location.href = result.data;
                                } else {
                                	disapperTooltip('remind', result.msg);
                                    $('#J_dologin').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
									qscms.varify_user_login = result.data;
                                }
                            }
                        });
					});
	            } else {
	                var usernameValue = $.trim($('#username').val());
	                var passwordValue = $.trim($('#password').val());
	                var expireValue = $.trim($('input[name=expire]').val());
	                // 提交表单
	                $.ajax({
	                    url: qscms.root+'?m=Home&c=Members&a=login',
	                    type: "post",
	                    dataType: "json",
	                    data: {
	                        username: usernameValue,
	                        password: passwordValue,
	                        expire: expireValue
	                    },
	                    success: function (result) {
	                        if (parseInt(result.status)) {
	                            window.location.href = result.data;
	                        } else {
	                            disapperTooltip('remind', result.msg);
	                            $('#J_dologin').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
								qscms.varify_user_login = result.data;
	                        }
	                    }
	                });
	            }
	        } else {
	            // 直接登录
	            var usernameValue = $.trim($('#username').val());
	            var passwordValue = $.trim($('#password').val());
	            var expireValue = $.trim($('input[name=expire]').val());
	            // 提交表单
	            $.ajax({
	                url: qscms.root+'?m=Home&c=Members&a=login',
	                type: "post",
	                dataType: "json",
	                data: {
	                    username: usernameValue,
	                    password: passwordValue,
	                    expire: expireValue
	                },
	                success: function (result) {
	                    if (parseInt(result.status)) {
	                        window.location.href = result.data;
	                    } else {
	                        disapperTooltip('remind', result.msg);
	                        $('#J_dologin').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
							qscms.varify_user_login = result.data;
	                    }
	                }
	            });
	        }
	    });

		// 手机动态码登录
	    var regularMobile = qscms.regularMobile;
	    $('#J_dologinByMobile').die().live('click', function() {
	        var mobileValue = $.trim($('input[name=mobile]').val());
	        var verfyCodeValue = $.trim($('input[name=verfy_code]').val());
	        var expireValue = $.trim($('input[name=expire_obile]').val());
	        if (mobileValue == "") {
	        	disapperTooltip('remind', '请输入手机号');
	            $('input[name=mobile]').focus();
	            return false;
	        }
	        if (mobileValue != "" && !regularMobile.test(mobileValue)) {
	        	disapperTooltip('remind', '手机号码格式不正确');
	            $('input[name=mobile]').focus();
	            return false;
	        }
	        if (verfyCodeValue == "") {
	        	disapperTooltip('remind', '请填写验证码');
	            $('input[name=verfy_code]').focus();
	            return false;
	        }
	        $('#J_dologinByMobile').val('登录中...').prop('disabled', !0).addClass('btn_disabled');
	        // 判断登录错误次数是否达到最大值
	       
	         if(eval(qscms.varify_user_login)){
	   
	         	// 标识为登录
	            $('#J_sendVerifyType').val('0');
	            if (eval(qscms.captcha_open)) {
	            	qsCaptchaHandler(function(callBackArr) {
						var mobileValue = $.trim($('input[name=mobile]').val());
                        var verfyCodeValue = $.trim($('input[name=verfy_code]').val());
                        var expireValue = $.trim($('input[name=expire_obile]').val());
						var dataArr = {mobile: mobileValue, mobile_vcode: verfyCodeValue, expire: expireValue};
						$.extend(dataArr, callBackArr);
             			// 提交表单
                        $.ajax({
                        	url: qscms.root+'?m=Home&c=Members&a=login',
                            type: "post",
                            dataType: "json",
                            data: dataArr,
                            success: function (result) {
                            	if (parseInt(result.status)) {
                                    window.location.href = result.data;
                                } else {
                                	disapperTooltip('remind', result.msg);
                                    $('#J_dologinByMobile').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
									qscms.varify_user_login = result.data;
                                }
                            }
                        });
					});
	            } else {
	                var mobileValue = $.trim($('input[name=mobile]').val());
	                var verfyCodeValue = $.trim($('input[name=verfy_code]').val());
	                var expireValue = $.trim($('input[name=expire_obile]').val());
	                // 提交表单
	                $.ajax({
	                    url: qscms.root+'?m=Home&c=Members&a=login',
	                    type: "post",
	                    dataType: "json",
	                    data: {
	                        mobile: mobileValue,
	                        mobile_vcode: verfyCodeValue,
	                        expire: expireValue
	                    },
	                    success: function (result) {
	                        if (parseInt(result.status)) {
	                            window.location.href = result.data;
	                        } else {
	                        	disapperTooltip('remind', result.msg);
	                            $('#J_dologinByMobile').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
								qscms.varify_user_login = result.data;
	                        }
	                    }
	                })
	            }
	         }else{
	         
	         	 // 直接登录
	            var mobileValue = $.trim($('input[name=mobile]').val());
	            var verfyCodeValue = $.trim($('input[name=verfy_code]').val());
	            var expireValue = $.trim($('input[name=expire_obile]').val());
	            // 提交表单
	            $.ajax({
	                url: qscms.root+'?m=Home&c=Members&a=login',
	                type: "post",
	                dataType: "json",
	                data: {
	                    mobile: mobileValue,
	                    mobile_vcode: verfyCodeValue,
	                    expire: expireValue
	                },
	                success: function (result) {
	                    if (parseInt(result.status)) {
	                        window.location.href = result.data;
	                    } else {
	                        disapperTooltip('remind', result.msg);
	                        $('#J_dologinByMobile').val('立即登录').prop('disabled', 0).removeClass('btn_disabled');
							qscms.varify_user_login = result.data;
	                    }
	                }
	            })
	         }    
	        
	    });

		// 是否自动登录
	    $('.J_expire').click(function() {
	        if ($(this).is(':checked')) {
	            $(this).val('1');
	        } else {
	            $(this).val('0');
	        }
	    });

	    // 登录方式切换
	    $('.J_sw_dt').click(function() {
	        var cuIndex = $('.J_sw_dt').index(this);
	        $('.J_sw_dt').removeClass('active');
	        $(this).addClass('active');
	        $('.ty_box').eq(cuIndex).addClass('active').siblings('.ty_box').removeClass('active');
	        $('.ncb_bx_bt').eq(cuIndex).addClass('active').siblings('.ncb_bx_bt').removeClass('active');
	    });
	</script>
</body>
</html>