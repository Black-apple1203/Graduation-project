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
    <link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/common.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/swiper.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/index.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/common_ajax_dialog.css" rel="stylesheet" type="text/css" />
</head>
<body>
<!-- 固定导航 -->
<div class="qs_search_fixed" id="J_qs_search_fixed">
    <div class="qs_sf_box">
        <?php if(C('qscms_subsite_open') == 1 && C('subsite_info.s_id') > 0): ?><img class="lg" src="<?php if(C('subsite_info.s_pc_logo')): echo attach(C('subsite_info.s_pc_logo'),'subsite'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt=""><?php else: ?><a href="<?php echo url_rewrite('QS_index');?>"><img class="lg" src="<?php if(C('qscms_logo_home')): echo attach(C('qscms_logo_home'),'resource'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt=""></a><?php endif; ?>
        <div id="J_fixed_sb">
            <div class="fixed_sea_cho">
                <div class="fixed_txt J_sea_choose_fx">找工作</div>
                <div class="fixed_sea_down" id="J_sea_down_box_fx">
                    <div class="fixed_sea_cell J_sli_fx" type="QS_resumelist">招人才</div>
                    <div class="fixed_sea_cell J_sli_fx" type="QS_companylist">搜企业</div>
                </div>
            </div>
            <form action="" id="fixed_search_location">
                <input class="fixed_sea_inp" type="text" name="key" value="" id="fixed_search_input" placeholder="请输入关键字" />
                <input type="hidden" name="act" id="fixed_search_type" value="QS_jobslist" />
            </form>
            <div class="fixed_sea_bt" id="fixed_search_btn">搜 索</div>
            <div class="clear"></div>
        </div>
        <div id="J_fixed_na">
            <div class="fixed_sea_nav">
                <ul class="fx_nav_channel">
                    <?php $tag_nav_class = new \Common\qscmstag\navTag(array('列表名'=>'nav','调用名称'=>'QS_top','显示数目'=>'7','cache'=>'0','type'=>'run',));$nav = $tag_nav_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$nav);?>
                    <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?><li class="fx_nav_channel_item <?php if(MODULE_NAME == C('DEFAULT_MODULE')): if($nav['tag'] == strtolower(CONTROLLER_NAME)): ?>active<?php endif; else: if($nav['tag'] == strtolower(MODULE_NAME)): ?>active<?php endif; endif; ?>"><a href="<?php echo ($nav['url']); ?>" target="<?php echo ($nav["target"]); ?>" class="fx_nav_channel_link"><span class="fx_nav_channel_name"><span><span><?php echo ($nav["title"]); ?></span></span></span></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
                <div class="clear"></div>
            </div>
        </div>
        <div class="fixed_sw_ic" id="J_fixed_sb_sw">导航</div><div class="fixed_sw_ic_s" id="J_fixed_na_sw">搜索</div>
    </div>
</div>
<!--顶部-->
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

<!--顶部 end-->
<div class="top_con">
    <div class="logo_nav_group <?php if(C('qscms_theme_tpl') == 1 ): ?>chunjie<?php endif; if(C('qscms_theme_tpl') == 2): ?>duanwu<?php endif; if(C('qscms_theme_tpl') == 3 ): ?>laodong<?php endif; if(C('qscms_theme_tpl') == 4 ): ?>zhongqiu<?php endif; if(C('qscms_theme_tpl') == 5 ): ?>guoqing<?php endif; ?>">
    <!--LOGO 搜素-->
    <div class="in_sea_group">
        <div class="in_sea_wra">
		<?php if(C('qscms_subsite_open') == 1 && C('subsite_info.s_id') > 0): ?><a href=""><img class="lg" src="<?php if(C('subsite_info.s_pc_logo')): echo attach(C('subsite_info.s_pc_logo'),'subsite'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt=""></a>
		<?php else: ?>
		  <a href=""><img class="lg" src="<?php if(C('qscms_logo_home')): echo attach(C('qscms_logo_home'),'resource'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt=""></a><?php endif; ?>		
            <div class="in_sea_cho">
                <div class="in_txt J_sea_choose">找工作</div>
                <div class="in_sea_down" id="J_sea_down_box">
                    <div class="in_sea_cell J_sli" type="QS_resumelist">招人才</div>
                    <div class="in_sea_cell J_sli" type="QS_companylist">搜企业</div>
                </div>
            </div>
            <form action="" id="ajax_search_location">
                <input class="in_sea_inp" type="text" name="key" value="" id="top_search_input" placeholder="请输入关键字" />
                <input type="hidden" name="act" id="top_search_type" value="QS_jobslist" />
            </form>
            <div class="in_sea_bt" id="top_search_btn">搜 索</div>
            <div class="in_sea_tb">
                <a href="<?php echo url_rewrite('QS_jobs');?>" target="_blank" class="lik_txt">职位分类</a><a href="<?php echo url_rewrite('QS_jobslist');?>" target="_blank" class="lik_txt">高级搜索</a>
            </div>
            <div class="clear"></div>
            <div class="in_sea_qr">
                <?php if($show_backtop_app == 1 && $show_backtop_weixin == 1): ?><img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt="" class="in_sea_img">
                    <img src="<?php echo attach(C('qscms_weixinapp_qrcode'),'images');?>" alt="" class="in_sea_img" style="display:none">
                    <?php elseif($show_backtop_app == 1 && $show_backtop_weixin == 0): ?>
                    <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt="" class="in_sea_img">
                    <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="" class="in_sea_img" style="display:none">
                    <?php elseif($show_backtop_app == 0 && $show_backtop_weixin == 1): ?>
                    <img src="<?php echo attach(C('qscms_weixinapp_qrcode'),'images');?>" alt="" class="in_sea_img">
                    <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="" class="in_sea_img" style="display:none">
                    <?php elseif($show_backtop_app == 0 && $show_backtop_weixin == 0): ?>
                    <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="" class="in_sea_img">
                    <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url());?>" alt="" class="in_sea_img" style="display:none"><?php endif; ?>
                <?php if($show_backtop_app == 1 && $show_backtop_weixin == 1): ?><span class="in_sea_qr_txt active">扫码下载APP</span>
                    <span class="in_sea_qr_txt">扫码进小程序</span>
                    <?php elseif($show_backtop_app == 1 && $show_backtop_weixin == 0): ?>
                    <span class="in_sea_qr_txt active">扫码下载APP</span>
                    <span class="in_sea_qr_txt">微信公众号</span>
                    <?php elseif($show_backtop_app == '' && $show_backtop_weixin == 1): ?>
                    <span class="in_sea_qr_txt active">扫码进小程序</span>
                    <span class="in_sea_qr_txt">微信公众号</span>
                    <?php elseif($show_backtop_app == 0 && $show_backtop_weixin == 0): ?>
                    <span class="in_sea_qr_txt active">微信公众号</span>
                    <span class="in_sea_qr_txt">触屏端</span><?php endif; ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--LOGO 搜素 end-->
    <!--导航-->
    <div class="in_nav_all">
        <div class="in_nav">
            <div class="inn_l"><a href="<?php echo url_rewrite('QS_jobs');?>" target="_blank">全部职位分类</a></div>
            <div class="inn_r">
                <ul class="nav_channel">
                    <?php $tag_nav_class = new \Common\qscmstag\navTag(array('列表名'=>'nav','调用名称'=>'QS_top','显示数目'=>'7','cache'=>'0','type'=>'run',));$nav = $tag_nav_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$nav);?>
                    <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?><li class="nav_channel_item <?php if(MODULE_NAME == C('DEFAULT_MODULE')): if($nav['tag'] == strtolower(CONTROLLER_NAME)): ?>active<?php endif; else: if($nav['tag'] == strtolower(MODULE_NAME)): ?>active<?php endif; endif; ?>"><a href="<?php echo ($nav['url']); ?>" target="<?php echo ($nav["target"]); ?>" class="nav_channel_link"><span class="nav_channel_name"><span><span><?php echo ($nav["title"]); ?></span></span></span></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                    <li class="nav_channel_item last J_hire_service">
                        <a href="javascript:;" class="nav_channel_link last"><span class="nav_channel_name "><span><span>更多服务</span></span></span></a>
                        <div class="new_ico"></div>
                    </li>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
        <div class="hire_ser_pop J_hire_service_pop">
            <div class="hire_ser_grid">
                <img class="arrow_up" src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/arrow_up.png" alt="">
                <div id="J_pc_g">
                    <?php if(!empty($apply['School'])): ?><a href="<?php echo url_rewrite('QS_school_index');?>" target="_blank" class="hsg_cell"><div class="img m_school"></div><div class="span"><h4>校园招聘</h4><p>梦想从这里开始</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Parttime'])): ?><a href="<?php echo url_rewrite('QS_parttime');?>" target="_blank" class="hsg_cell"><div class="img m_part_time"></div><div class="span"><h4>兼职招聘</h4><p>闲时也能体现你的价值</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Store'])): ?><a href="<?php echo url_rewrite('QS_store');?>" target="_blank" class="hsg_cell"><div class="img m_store"></div><div class="span"><h4>门店招聘</h4><p>这里有离家最近的工作</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Allowance'])): ?><a href="<?php echo url_rewrite('QS_jobslist',array('search_cont'=>'allowance'));?>" target="_blank" class="hsg_cell"><div class="img m_allowance"></div><div class="span"><h4>红包职位</h4><p>只送红包不玩套路</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['House'])): ?><a href="<?php echo url_rewrite('QS_house_rent');?>" target="_blank" class="hsg_cell"><div class="img m_near"></div><div class="span"><h4>附近租房</h4><p>让你离家更近一点</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Gworker'])): ?><a href="<?php echo url_rewrite('QS_gworker');?>" target="_blank" class="hsg_cell"><div class="img m_gworker"></div><div class="span"><h4>普工招聘</h4><p>工厂直招安全放心</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Mall'])): ?><a href="<?php echo url_rewrite('QS_mall_index');?>" target="_blank" class="hsg_cell"><div class="img m_mall"></div><div class="span"><h4>积分商城</h4><p>积分不仅仅只是积分</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Interview'])): ?><a href="<?php echo url_rewrite('QS_interview_list');?>" target="_blank" class="hsg_cell"><div class="img m_interview"></div><div class="span"><h4>企业专访</h4><p>理想抱负并不遥远</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Career'])): ?><a href="<?php echo url_rewrite('QS_career_list');?>" target="_blank" class="hsg_cell"><div class="img m_kao"></div><div class="span"><h4>直通招考</h4><p>最新事业单位招聘</p></div><div class="clear"></div></a><?php endif; ?>
                    <?php if(!empty($apply['Jobfair'])): ?><a href="<?php echo url_rewrite('QS_jobfairlist');?>" target="_blank" class="hsg_cell"><div class="img m_jobfair"></div><div class="span"><h4>招聘会</h4><p>大型本地招聘会</p></div><div class="clear"></div></a><?php endif; ?>
                </div>
                <div class="less_pop J_lessPop"></div>
            </div>
        </div>
        <!--导航 end-->
    </div>
</div>
<div class="main_con">
    <div class="cat_nav">
        <?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'jobsCate','类型'=>'QS_jobs','cache'=>'0','type'=>'run',));$jobsCate = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$jobsCate);?>
        <div class="job_nav_ol">
            <?php if(C('qscms_category_jobs_level') == 3): if(is_array($jobsCate[0])): $i = 0; $__LIST__ = array_slice($jobsCate[0],0,8,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$pcate): $mod = ($i % 2 );++$i;?><li class="jno_item">
                        <div class="jno_item_text"><?php echo ($pcate["categoryname"]); ?></div>
                        <div class="jno_item_pop">
                            <?php if(is_array($jobsCate[$key])): $i = 0; $__LIST__ = $jobsCate[$key];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$scate): $mod = ($i % 2 );++$i;?><div class="jno_item_pop_container">
                                    <div class="jno_item_pop_title"><?php echo ($scate["categoryname"]); ?></div>
                                    <div class="jno_item_pop_list">
                                        <?php if(is_array($jobsCate[$key])): $i = 0; $__LIST__ = $jobsCate[$key];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): $mod = ($i % 2 );++$i;?><a href="<?php echo url_rewrite('QS_jobslist',array('jobcategory'=>$cate['spell']));?>" target="_blank" class="jno_item_pop_href"><?php echo ($cate["categoryname"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </div>
                                </div><?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
                <?php else: ?>
                <?php if(is_array($jobsCate[0])): $i = 0; $__LIST__ = array_slice($jobsCate[0],0,8,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$pcate): $mod = ($i % 2 );++$i;?><li class="jno_item">
                        <div class="jno_item_text"><?php echo ($pcate["categoryname"]); ?></div>
                        <div class="jno_item_pop">
                            <div class="jno_item_pop_container">
                                <div class="jno_item_pop_title"><?php echo ($pcate["categoryname"]); ?></div>
                                <div class="jno_item_pop_list">
                                    <?php if(is_array($jobsCate[$key])): $i = 0; $__LIST__ = $jobsCate[$key];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): $mod = ($i % 2 );++$i;?><a href="<?php echo url_rewrite('QS_jobslist',array('jobcategory'=>$cate['spell']));?>" target="_blank" class="jno_item_pop_href"><?php echo ($cate["categoryname"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                            </div>
                        </div>
                    </li><?php endforeach; endif; else: echo "" ;endif; endif; ?>
            <li class="jno_item">
                <a href="<?php echo url_rewrite('QS_jobs');?>" target="_blank" class="jno_item_text2">全部职类</a>
            </li>
        </div>
    </div>
    <div class="sw_group">
        <div class="online_box">
            <div class="on_app">
                <img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/on_img.png" alt="">
                <span><?php echo ($count); ?>人在线</span>
            </div>
            <div class="app_live">
                <ul id="J_ajax_scroll"></ul>
                <script type="text/javascript">
                    var ajaxScrollHtml = '';
                    $.getJSON('<?php echo U("Index/ajax_scroll");?>',function(result){
                        if (result.status) {
                            var dataArr = result.data;
                            for (var i = 0; i < dataArr.length; i++) {
                                if (dataArr[i]['utype'] == '1') {
                                    if (dataArr[i]['type'] == 'add') {
                                        ajaxScrollHtml += '<li><span></span><a href="'+dataArr[i]['company_url']+'" target="_blank">'+dataArr[i]['companyname']+'</a>发布了<a href="'+dataArr[i]['job_url']+'" target="_blank">'+dataArr[i]['jobs_name']+'</a></li>';
                                    } else {
                                        ajaxScrollHtml += '<li><span></span><a href="'+dataArr[i]['company_url']+'" target="_blank">'+dataArr[i]['companyname']+'</a>刷新了<a href="'+dataArr[i]['job_url']+'" target="_blank">'+dataArr[i]['jobs_name']+'</a></li>';
                                    }
                                } else {
                                    if (dataArr[i]['type'] == 'add') {
                                        ajaxScrollHtml += '<li><span>'+dataArr[i]['time_cn']+'</span><a href="'+dataArr[i]['url']+'" target="_blank">'+dataArr[i]['fullname']+'</a>发布了新简历</li>';
                                    } else {
                                        ajaxScrollHtml += '<li><span>'+dataArr[i]['time_cn']+'</span><a href="'+dataArr[i]['url']+'" target="_blank">'+dataArr[i]['fullname']+'</a>刷新了简历</li>';
                                    }
                                }
                            }
                            $('#J_ajax_scroll').html(ajaxScrollHtml);
                        }
                    })
                </script>
            </div>
        </div>
        <div class="sw_con">
            <div class="swiper-container">
                <?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_indextopimg','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
                <div class="swiper-wrapper">
                    <?php if(!empty($ad['list'])): if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><div class="swiper-slide"><a href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img class="swiper-item" src="<?php echo attach($ad_info['content'],'attach_img');?>"></a></div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
    <div class="sw_sing">
        <div class="sws_up" id="J_sing">
            <!--未登录-->
            <?php if($visitor == ''): ?><div class="sing_no">
                    <!-- 站位 -->
                    <div class="sing_no_box"></div>
                    <!-- 手机登录 -->
                    <div class="mobile_login_wrap">
                        <div class="sn_txt" id="J_now_txt">hi，下午好</div>
                        <div class="sn_txt">登录后查看专属职位</div>
                        <div class="password_login">密码登录 ></div>
                        <div class="clear"></div>
                        <div class="d_inp">
                            <input type="number" name="mobile" class="inp" value="" placeholder="请输入手机号" />
                        </div>
                        <div class="d_inp">
                            <input type="number" name="verfy_code" class="inp" value="" placeholder="请输入验证码" />
                            <div class="sf"></div>
                            <input type="button" class="get_bt" id="J_get_code" value="获取验证码" >
                        </div>
                        <input type="button" class="sn_login_btn" id="J_login_btn" value="登录" >
                        <div class="get_pwd"><a href="<?php echo U('members/user_getpass');?>" target="_blank">忘记密码？</a></div>
                        <div class="clear"></div>
                    </div>
                    <!-- 手机登录结束 -->
                    <!-- 密码登录 -->
                    <div class="password_login_wrap" >
                        <div class="sn_txt" id="J_now_txt2">hi，下午好！</div>
                        <div class="sn_txt">登录后查看专属职位</div>
                        <div class="reg_login">验证码登录 ></div>
                        <div class="clear"></div>
                        <div class="d_inp">
                            <input type="text" name="username" class="inp" value="" placeholder="请输入用户名/手机号" />
                        </div>
                        <div class="d_inp">
                            <input type="password" name="password_code" class="inp" value="" placeholder="请输入密码" />
                        </div>
                        <input type="button" class="sn_login_btn" id="P_login_btn" value="登录" >
                        <div class="get_pwd"><a href="<?php echo U('members/user_getpass');?>" target="_blank">忘记密码？</a></div>
                        <div class="clear"></div>
                    </div>
                    <!-- 密码登录结束 -->
                    <div class="sn_other">
                        <span>合作账号登录</span>
                        <div class="alb">
                            <?php if(is_array($oauth_list)): $i = 0; $__LIST__ = $oauth_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$oauth): $mod = ($i % 2 );++$i;?><a href="<?php echo U('callback/index',array('mod'=>$key,'type'=>'login'));?>" target="_blank" class="a_l <?php echo ($key); ?>"></a><?php endforeach; endif; else: echo "" ;endif; ?>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="sn_re_btn_big" id="J_reg_now">立即注册</div>
                    <input type="button" id="btnIndexCheck" style="display:none;">
                    <input type="button" id="btnIndexLoginMobile" style="display:none;">
                    <input type="button" id="btnIndexLoginMobile2" style="display:none;">
                    <div id="indexCaptchaBox"></div>
                    <div id="indexCaptchaBoxLogMobile"></div>
                </div>
                <script type="text/javascript">
                    var now = new Date(),hour = now.getHours(), nowHtml = 'hi，';
                    if(hour < 6){
                        nowHtml += "凌晨好！";
                    } else if (hour < 9){nowHtml += "早上好！";}
                    else if (hour < 12){nowHtml += "上午好！";}
                    else if (hour < 14){nowHtml += "中午好！";}
                    else if (hour < 17){nowHtml += "下午好！";}
                    else if (hour < 19){nowHtml += "傍晚好！";}
                    else if (hour < 22){nowHtml += "晚上好！";}
                    else {nowHtml += "夜里好！";}
                    $('#J_now_txt').html(nowHtml);
                    $('#J_now_txt2').html(nowHtml);
                </script>
                <?php else: ?>
                <!--已登录个人-->
                <?php if($visitor['utype'] == 2): ?><div class="sing_in" id="J_lor_tj"></div>
                    <script type="text/javascript">
                        var creatsUrl = qscms.root + '?m=Home&c=index&a=ajax_recommend_jobs';
                        $.getJSON(creatsUrl, function(result){
                            if(eval(result.status) === 1){
                                $('#J_lor_tj').html(result.data.html);
                            } else {
                                disapperTooltip("remind", result.msg);
                            }
                        })
                    </script>
                    <?php else: ?>
                    <!--已登录企业-->
                    <div class="sing_in" id="J_loc_tj"></div>
                    <script type="text/javascript">
                        var creatsUrl = qscms.root + '?m=Home&c=index&a=ajax_recommend_resume';
                        $.getJSON(creatsUrl, function(result){
                            if(eval(result.status) === 1){
                                $('#J_loc_tj').html(result.data.html);
                                $('#J_refresh_jobs').click(function(){
                                    $.getJSON("<?php echo U('CompanyService/jobs_refresh_all');?>",function(result){
                                        if(result.status==1){
                                            disapperTooltip('success',result.msg);
                                        }
                                        else if(result.status==2)
                                        {
                                            var qsDialog = $(this).dialog({
                                                title: '批量刷新职位',
                                                loading: true,
                                                border: false,
                                                yes: function () {
                                                    window.location.href=result.data;
                                                }
                                            });
                                            qsDialog.setBtns(['单条刷新', '取消']);
                                            qsDialog.setContent('<div class="refresh_jobs_all_confirm">' + result.msg + '</div>');
                                        }
                                        else
                                        {
                                            disapperTooltip('remind',result.msg);
                                        }
                                    });
                                });
                            } else {
                                disapperTooltip("remind", result.msg);
                            }
                        })
                    </script><?php endif; endif; ?>
        </div>
        <?php $tag_notice_list_class = new \Common\qscmstag\notice_listTag(array('列表名'=>'notice_list','显示数目'=>'4','分类'=>'1','cache'=>'0','type'=>'run',));$notice_list = $tag_notice_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$notice_list);?>
        <div class="sws_down <?php if(!$notice_list['list']): ?>no<?php endif; ?>" id="J_msg_box">
        <ul>
            <?php if(is_array($notice_list['list'])): $i = 0; $__LIST__ = $notice_list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$notice): $mod = ($i % 2 );++$i;?><li><a href="<?php echo ($notice["url"]); ?>" target="_blank"><?php echo ($notice["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>
<div class="clear"></div>
<div class="source_1">
    <?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_indexcenterrecommend','职位数量'=>'12','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
    <?php if(!empty($ad['list'])): if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><div class="sou_cell">
                <div class="igb"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></div>
                <div class="name substring"><a href="<?php echo ($ad_info["href"]); ?>" target="_blank"><?php echo ($ad_info["title"]); ?></a></div>
                <?php if(!empty($ad_info['company']['companyname'])): ?><div class="sou_layer">
                        <a href="<?php echo ($ad_info['company']['company_url']); ?>" target="_blank" class="sou_layer_box">
                            <p><?php echo ($ad_info['company']['jobs_count']); ?>个在招职位</p><span>查看详情</span>
                        </a>
                    </div><?php endif; ?>
            </div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
    <div class="clear"></div>
</div>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_employer','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_5">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
    </div><?php endif; ?>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_employer_two','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_6">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
        <div class="clear"></div>
    </div><?php endif; ?>
<div class="pub_t">
    <div class="put_l">明星雇主</div>
    <div class="put_r"><a href="<?php echo url_rewrite('QS_explainshow',array('id'=>10,'type_id'=>2));?>" target="_blank">企业如何在这里展示？</a></div>
    <div class="clear"></div>
</div>
<div class="source_2">
    <?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_star_employer','职位数量'=>'4','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
    <?php if(!empty($ad['list'])): if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><div class="sou_cell">
                <div class="sc_ab">
                    <a href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a>
                    <?php if(!empty($ad_info['company']['companyname'])): ?><div class="a2_intro"><a href="<?php echo ($ad_info["company"]["company_url"]); ?>" target="_blank"><?php if($ad_info['company']['short_name']): echo ($ad_info["company"]["short_name"]); else: echo ($ad_info["company"]["companyname"]); endif; ?></a></div>
                        <?php if(!empty($ad_info['company']['jobs'])): ?><div class="a2_jobs">
                                <?php if(is_array($ad_info[company]['jobs'])): $i = 0; $__LIST__ = $ad_info[company]['jobs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$job): $mod = ($i % 2 );++$i;?><a href="<?php echo ($job["jobs_url"]); ?>" target="_blank"><?php echo ($job["jobs_name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                            </div><?php endif; ?>
                        <div class="a2_ent"><a href="<?php echo ($ad_info["company"]["company_url"]); ?>" target="_blank"><h4><?php echo ($ad_info["company"]["companyname"]); ?></h4><span><?php echo ($ad_info["company"]["nature_cn"]); ?>｜<?php echo ($ad_info["company"]["scale_cn"]); ?></span></a></div><?php endif; ?>
                </div>
            </div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
    <div class="clear"></div>
</div>
<div class="pub_t">
    <div class="put_l">紧急招聘</div>
    <div class="put_r">
        <?php if($visitor == ''): ?>求职快人一步，立即<a class="blue" href="javascript:;" id="J_reg_resume">免费登记简历</a>
            <?php else: ?>
            <?php if($visitor['utype'] == 2): ?>完整度高的简历更容易获得青睐，<a class="blue" href="<?php echo U('personal/index');?>" target="_blank">立即完善简历</a>
                <?php else: ?>
                提升职位排名，立即<a class="blue" href="javascript:;" id="J_res_job_t">刷新职位</a><?php endif; endif; ?>

    </div>
    <div class="clear"></div>
</div>
<div class="source_7">
    <?php $tag_jobs_list_class = new \Common\qscmstag\jobs_listTag(array('列表名'=>'emergency_jobs','紧急招聘'=>'1','职位名长度'=>'7','显示数目'=>'15','cache'=>'0','type'=>'run',));$emergency_jobs = $tag_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$emergency_jobs);?>
    <?php if(is_array($emergency_jobs['list'])): $i = 0; $__LIST__ = $emergency_jobs['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$jobs): $mod = ($i % 2 );++$i;?><div class="sou_cell">
            <div class="job_name substring"><a href="<?php echo ($jobs["jobs_url"]); ?>" target="_blank"><?php echo ($jobs["jobs_name"]); ?></a><?php if($jobs["emergency"] == 1 ): ?><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/job_jp.png" alt=""><?php endif; ?></div>
            <div class="com_name substring"><a href="<?php echo ($jobs["company_url"]); ?>" target="_blank"><?php echo ($jobs["companyname"]); ?></a></div>
            <div class="job_tag_group">
                <?php if(is_array($jobs['tag_cn'])): $i = 0; $__LIST__ = $jobs['tag_cn'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><div class="jtg_cell"><?php echo ($tag); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
                <div class="clear"></div>
            </div>
            <span class="job_wage"><?php echo ($jobs["wage_cn"]); ?></span>
            <span class="job_edg">
							<span class="first"><?php echo ($jobs["education_cn"]); ?></span><span class="last"><?php echo ($jobs["experience_cn"]); ?></span>
						</span>
            <div class="job_dis"><?php echo ($jobs["city"]); ?></div>
            <div class="job_apy" data-jid="<?php echo ($jobs["id"]); ?>">我要应聘</div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    <div class="clear"></div>
</div>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_index_newjobs_san','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_4">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
        <div class="clear"></div>
    </div><?php endif; ?>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_index_newjobs','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_5">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
    </div><?php endif; ?>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_index_newjobs_er','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_6">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
        <div class="clear"></div>
    </div><?php endif; ?>
<div class="pub_t">
    <div class="put_l J_sw">最新职位</div>
    <div class="put_l sw J_sw">热门职位</div>
    <div class="put_l sw J_sw">高薪职位</div>
    <div class="put_r"><a href="<?php echo url_rewrite('QS_jobslist');?>" target="_blank">更多职位>></a></div>
    <div class="clear"></div>
</div>
<!--最新职位-->
<div class="source_3 J_sw_job">
    <?php $tag_company_jobs_list_class = new \Common\qscmstag\company_jobs_listTag(array('列表名'=>'new_jobs','分页显示'=>'1','职位名长度'=>'7','职位数量'=>'1','排序'=>'rtime','显示数目'=>'16','cache'=>'0','type'=>'run',));$new_jobs = $tag_company_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$new_jobs);?>
    <?php if(is_array($new_jobs['list'])): $i = 0; $__LIST__ = $new_jobs['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$company): $mod = ($i % 2 );++$i; if(is_array($company['jobs'])): $i = 0; $__LIST__ = $company['jobs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$jobs): $mod = ($i % 2 );++$i;?><div class="sou_cell">
                <div class="job_name substring"><a href="<?php echo ($jobs["jobs_url"]); ?>" target="_blank"><?php echo ($jobs["jobs_name"]); ?></a><?php if($jobs["emergency"] == 1 ): ?><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/job_jp.png" alt=""><?php endif; ?></div>
                <div class="job_oth substring">
                    <span class="first"><?php echo ($jobs["city"]); ?></span><span><?php echo ($jobs["experience_cn"]); ?></span><span class="last"><?php echo ($jobs["education_cn"]); ?></span>
                </div>
                <div class="com_name substring"><a href="<?php echo ($company["company_url"]); ?>" target="_blank"><?php echo ($company["companyname"]); ?></a></div>
                <span class="job_wage"><?php echo ($jobs["wage_cn"]); ?></span>
            </div><?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
    <div class="clear"></div>
</div>
<!--热门职位-->
<div class="source_3 hid J_sw_job">
    <?php $tag_jobs_list_class = new \Common\qscmstag\jobs_listTag(array('列表名'=>'hot_jobs','显示数目'=>'16','职位名长度'=>'7','排序'=>'hot','cache'=>'0','type'=>'run',));$hot_jobs = $tag_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$hot_jobs);?>
    <?php if(is_array($hot_jobs['list'])): $i = 0; $__LIST__ = $hot_jobs['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$jobs): $mod = ($i % 2 );++$i;?><div class="sou_cell">
            <div class="job_name substring"><a href="<?php echo ($jobs["jobs_url"]); ?>" target="_blank"><?php echo ($jobs["jobs_name"]); ?></a><?php if($jobs["emergency"] == 1 ): ?><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/job_jp.png" alt=""><?php endif; ?></div>
            <div class="job_oth substring">
                <span class="first"><?php echo ($jobs["city"]); ?></span><span><?php echo ($jobs["experience_cn"]); ?></span><span class="last"><?php echo ($jobs["education_cn"]); ?></span>
            </div>
            <div class="com_name substring"><a href="<?php echo ($jobs["company_url"]); ?>" target="_blank"><?php echo ($jobs["companyname"]); ?></a></div>
            <span class="job_wage"><?php echo ($jobs["wage_cn"]); ?></span>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    <div class="clear"></div>
</div>
<!--高薪职位-->
<div class="source_3 hid J_sw_job">
    <?php $tag_jobs_list_class = new \Common\qscmstag\jobs_listTag(array('列表名'=>'high_jobs','显示数目'=>'16','排序'=>'wage','cache'=>'0','type'=>'run',));$high_jobs = $tag_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$high_jobs);?>
    <?php if(is_array($high_jobs['list'])): $i = 0; $__LIST__ = $high_jobs['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$jobs): $mod = ($i % 2 );++$i;?><div class="sou_cell">
            <div class="job_name substring"><a href="<?php echo ($jobs["jobs_url"]); ?>" target="_blank"><?php echo ($jobs["jobs_name"]); ?></a><?php if($jobs["emergency"] == 1 ): ?><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/job_jp.png" alt=""><?php endif; ?></div>
            <div class="job_oth substring">
                <span class="first"><?php echo ($jobs["city"]); ?></span><span><?php echo ($jobs["experience_cn"]); ?></span><span class="last"><?php echo ($jobs["education_cn"]); ?></span>
            </div>
            <div class="com_name substring"><a href="<?php echo ($jobs["company_url"]); ?>" target="_blank"><?php echo ($jobs["companyname"]); ?></a></div>
            <span class="job_wage"><?php echo ($jobs["wage_cn"]); ?></span>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    <div class="clear"></div>
</div>
<?php $tag_ad_class = new \Common\qscmstag\adTag(array('列表名'=>'ad','广告位名称'=>'QS_index_highjob','cache'=>'0','type'=>'run',));$ad = $tag_ad_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$ad);?>
<?php if(!empty($ad['list'])): ?><div class="source_5">
        <?php if(is_array($ad['list'])): $i = 0; $__LIST__ = $ad['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ad_info): $mod = ($i % 2 );++$i;?><a class="img" href="<?php echo ($ad_info["href"]); ?>" target="_blank"><img src="<?php echo attach($ad_info['content'],'attach_img');?>" alt=""></a><?php endforeach; endif; else: echo "" ;endif; ?>
    </div><?php endif; ?>
<div class="pub_t">
    <div class="put_l">人才推荐</div>
    <div class="put_r">简历完整度越高，越有机会在首页出现哦！</div>
    <div class="clear"></div>
</div>
<div class="source_8">
    <?php $tag_resume_list_class = new \Common\qscmstag\resume_listTag(array('列表名'=>'resumelist','照片'=>'1','显示数目'=>'15','排序'=>'percent','cache'=>'0','type'=>'run',));$resumelist = $tag_resume_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$resumelist);?>
    <?php if(is_array($resumelist['list'])): $i = 0; $__LIST__ = $resumelist['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$resume): $mod = ($i % 2 );++$i;?><div class="sou_cell">
            <img src="<?php echo ($resume["photosrc"]); ?>" alt="" class="res_ava">
            <div class="res_name substring"><a href="<?php echo ($resume["resume_url"]); ?>" target="_blank"><?php echo ($resume["fullname"]); ?></a></div>
            <div class="res_oth substring">
                <span class="first"><?php echo ($resume["sex_cn"]); ?></span><span><?php echo ($resume["age"]); ?></span><span><?php echo ($resume["education_cn"]); ?></span><span class="last"><?php echo ($resume["experience_cn"]); ?></span>
            </div>
            <div class="res_int substring"><?php echo ($resume["intention_jobs"]); ?></div>
            <div class="res_dis substring"><?php echo (($resume["district_cn"] != "")?($resume["district_cn"]):'未填写'); ?></div>
            <div class="res_per">完整度：<span><?php echo ($resume["complete_percent"]); ?>%</span></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
    <div class="clear"></div>
</div>
<div class="pub_t">
    <div class="put_l">职场资讯</div>
    <div class="put_r"><a href="<?php echo url_rewrite('QS_news');?>" target="_blank">更多资讯>></a></div>
    <div class="clear"></div>
</div>
<div class="source_9">
    <div class="s9_l">
        <!--职业指导-->
        <div class="s9_cell new1">
            <?php $tag_news_list_class = new \Common\qscmstag\news_listTag(array('列表名'=>'article_list','显示数目'=>'3','资讯小类'=>'2','cache'=>'0','type'=>'run',));$article_list = $tag_news_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$article_list);?>
            <?php if(is_array($article_list['list'])): $i = 0; $__LIST__ = $article_list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?><div class="s9_lik substring"><a href="<?php echo ($article["url"]); ?>" target="_blank" style="<?php if($article['tit_color']): ?>color:<?php echo ($article["tit_color"]); ?>;<?php endif; if($article['tit_b'] > 0): ?>font-weight:bold<?php endif; ?>"><?php echo ($article["title"]); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!--简历指南-->
        <div class="s9_cell new2">
            <?php $tag_news_list_class = new \Common\qscmstag\news_listTag(array('列表名'=>'article_list','显示数目'=>'3','资讯小类'=>'3','cache'=>'0','type'=>'run',));$article_list = $tag_news_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$article_list);?>
            <?php if(is_array($article_list['list'])): $i = 0; $__LIST__ = $article_list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?><div class="s9_lik substring"><a href="<?php echo ($article["url"]); ?>" target="_blank" style="<?php if($article['tit_color']): ?>color:<?php echo ($article["tit_color"]); ?>;<?php endif; if($article['tit_b'] > 0): ?>font-weight:bold<?php endif; ?>"><?php echo ($article["title"]); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!--面试宝典-->
        <div class="s9_cell new3">
            <?php $tag_news_list_class = new \Common\qscmstag\news_listTag(array('列表名'=>'article_list','显示数目'=>'3','资讯小类'=>'4','cache'=>'0','type'=>'run',));$article_list = $tag_news_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$article_list);?>
            <?php if(is_array($article_list['list'])): $i = 0; $__LIST__ = $article_list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?><div class="s9_lik substring"><a href="<?php echo ($article["url"]); ?>" target="_blank" style="<?php if($article['tit_color']): ?>color:<?php echo ($article["tit_color"]); ?>;<?php endif; if($article['tit_b'] > 0): ?>font-weight:bold<?php endif; ?>"><?php echo ($article["title"]); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!--劳动法苑-->
        <div class="s9_cell new4">
            <?php $tag_news_list_class = new \Common\qscmstag\news_listTag(array('列表名'=>'article_list','显示数目'=>'3','资讯小类'=>'6','cache'=>'0','type'=>'run',));$article_list = $tag_news_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$article_list);?>
            <?php if(is_array($article_list['list'])): $i = 0; $__LIST__ = $article_list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?><div class="s9_lik substring"><a href="<?php echo ($article["url"]); ?>" target="_blank" style="<?php if($article['tit_color']): ?>color:<?php echo ($article["tit_color"]); ?>;<?php endif; if($article['tit_b'] > 0): ?>font-weight:bold<?php endif; ?>"><?php echo ($article["title"]); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="s9_r">
        <div class="s9r_t">热门资讯推荐</div>
        <?php $tag_news_list_class = new \Common\qscmstag\news_listTag(array('列表名'=>'hot_news','显示数目'=>'6','属性'=>'2','cache'=>'0','type'=>'run',));$hot_news = $tag_news_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$hot_news);?>
        <?php if(is_array($hot_news['list'])): $i = 0; $__LIST__ = $hot_news['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$news): $mod = ($i % 2 );++$i;?><div class="s9r_lik substring"><a  href="<?php echo ($news["url"]); ?>" style="<?php if($news['tit_color']): ?>color:<?php echo ($news["tit_color"]); ?>;<?php endif; if($news['tit_b'] > 0): ?>font-weight:bold<?php endif; ?>" target="_blank"><?php echo ($news["title"]); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    <div class="clear"></div>
</div>
</div>
<div class="in_foot">
    <div class="inf_con">
        <!--友情链接-->
        <div class="inf_lik_group">
            <a href="javascript:;" class="inf_lf"></a>
            <?php $tag_link_class = new \Common\qscmstag\linkTag(array('列表名'=>'links','类型'=>'1','cache'=>'0','type'=>'run',));$links = $tag_link_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$links);?>
            <?php if(is_array($links)): $i = 0; $__LIST__ = $links;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$link): $mod = ($i % 2 );++$i;?><a href="<?php echo ($link["link_url"]); ?>" target="_blank" class="inf_cell"><?php echo ($link["title"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
            <div class="clear"></div>
        </div>
        <div class="inf_lb">
            <div class="lb_t">关于我们</div>
            <?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','显示数目'=>'4','分类id'=>'1','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$list);?>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo ($vo['url']); ?>" target="_blank" class="lb_a"><?php echo ($vo['title']); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="inf_lb">
            <div class="lb_t">个人求职</div>
            <a href="<?php echo U('personal/index');?>" target="_blank" class="lb_a">注册简历</a>
            <a href="<?php echo url_rewrite('QS_jobslist');?>" target="_blank" class="lb_a">投递职位</a>
            <a href="<?php echo url_rewrite('QS_jobslist',array('search_cont'=>'allowance'));?>" target="_blank" class="lb_a">红包职位</a>
            <a href="<?php echo U('personal/jobs_interview');?>" target="_blank" class="lb_a">求职管理</a>
        </div>
        <div class="inf_lb">
            <div class="lb_t">企业招聘</div>
            <a href="<?php echo U('company/index');?>" target="_blank" class="lb_a">企业注册</a>
            <a href="<?php echo U('company/jobs_add');?>" target="_blank" class="lb_a">职位发布</a>
            <a href="<?php echo url_rewrite('QS_resumelist');?>" target="_blank" class="lb_a">搜索人才</a>
            <a href="<?php echo url_rewrite('QS_jobfairlist');?>" target="_blank" class="lb_a">招聘会</a>
        </div>
        <div class="inf_lb">
            <div class="lb_t">特色栏目</div>
            <?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','显示数目'=>'4','分类id'=>'2','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"zy拉钩人才系统","keywords"=>"y拉钩人才系统，zy，人才网站源码，php人才网程序","description"=>"y拉钩人才系统是基于PHP+MYSQL的免费网站管理系统，提供完善的人才招聘网站建设方案","header_title"=>""),$list);?>
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><a href="<?php echo ($vo['url']); ?>" target="_blank" class="lb_a"><?php echo ($vo['title']); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="inf_lb">
            <div class="lb_t">帮助中心</div>
            <a href="<?php echo url_rewrite('QS_helplist',array('key'=>'收费标准'));?>" target="_blank" class="lb_a">收费标准</a>
            <a href="<?php echo url_rewrite('QS_helplist',array('key'=>'常见问题'));?>" target="_blank" class="lb_a">常见问题</a>
            <a href="<?php echo url_rewrite('QS_helplist',array('key'=>'账号申诉'));?>" target="_blank" class="lb_a">账号申诉</a>
        </div>
        <div class="inf_lbl"></div>
        <?php if($show_backtop_app == 1 && $show_backtop_weixin == 1): ?><div class="inf_lb gz">
                <div class="lb_t">扫码下载APP</div>
                <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt="">
            </div>
            <div class="inf_lb gz xc">
                <div class="lb_t">扫码进小程序</div>
                <img src="<?php echo attach(C('qscms_weixinapp_qrcode'),'images');?>" alt="">
            </div>
            <?php elseif($show_backtop_app == 1 && $show_backtop_weixin == 0): ?>
            <div class="inf_lb gz">
                <div class="lb_t">扫码下载APP</div>
                <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(C('qscms_site_domain').U('Mobile/Index/app_download'));?>" alt="">
            </div>
            <div class="inf_lb gz xc">
                <div class="lb_t">微信公众号</div>
                <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="">
            </div>
            <?php elseif($show_backtop_app == 0 && $show_backtop_weixin == 1): ?>
            <div class="inf_lb gz">
                <div class="lb_t">扫码进小程序</div>
                <img src="<?php echo attach(C('qscms_weixinapp_qrcode'),'images');?>" alt="">
            </div>
            <div class="inf_lb gz xc">
                <div class="lb_t">微信公众号</div>
                <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="">
            </div>
            <?php elseif($show_backtop_app == 0 && $show_backtop_weixin == 0): ?>
            <div class="inf_lb gz">
                <div class="lb_t">微信公众号</div>
                <img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt="">
            </div>
            <div class="inf_lb gz xc">
                <div class="lb_t">触屏端</div>
                <img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url());?>" alt="">
            </div><?php endif; ?>
        <div class="inf_lbl"></div>
        <div class="inf_lb ser">
            <div class="lb_t">服务信息</div>
            <div class="ser_txt">联系电话：<?php echo C('qscms_bootom_tel');?></div>
            <div class="ser_txt">服务时间：08:00-18:00</div>
        </div>
        <div class="clear"></div>
        <div class="inf_text first">联系地址：<?php echo C('qscms_address');?>   网站备案：<?php if(C('qscms_icp') != ''): ?><a href="http://www.beian.miit.gov.cn" target="_blank"><?php echo C('qscms_icp');?></a><?php endif; ?></div>
        <div class="inf_text"><?php echo C('qscms_bottom_other');?> Powered by <a href="http://www.74cms.com">74cms</a> v<?php echo C('QSCMS_VERSION');?> <?php echo htmlspecialchars_decode(C('qscms_statistics'));?></div>
    </div>
</div>
<div class="bt_guider J_bt_guider">
    <p class="shadow"></p>
    <div class="bt_guider_wrap">
        <div class="guider_icon"></div>
        <a href="javascript:;" class="guider_close J_bt_gui_close"></a>
        <div class="gm_qr_code">
            <div class="qr_code_box">
                <img src="<?php if(C('qscms_index_bottom_wx')): echo attach(C('qscms_index_bottom_wx'),'resource'); else: echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url()); endif; ?>" alt="触屏端">
            </div>
            <div class="hs_6"></div>
            <div class="qr_other">扫一扫手机也能找工作</div>
        </div>
        <div class="gm_gr_sha"></div>
        <div class="guider_main">
            <div class="gm_left">
                <div class="hs_16"></div>
                <div class="gm_site_name"><?php echo C('qscms_index_bottom_title');?></div>
                <div class="hs_12"></div>
                <div class="gm_other"><?php echo C('qscms_index_bottom_info');?></div>
            </div>
            <div class="gm_right">
                <div class="hs_20"></div>
                <a href="javascript:;" id="J_reg_qick" class="gm_btn">立即加入</a>
                <input type="hidden" id="JRegHidVal" value="<?php echo C('qscms_rapid_registration_resume');?>" />
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <script type="text/javascript">
        $('.J_bt_gui_close').click(function () {
            $('.J_bt_guider').hide();
        })
    </script>
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
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.modal.dialog.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/swiper.min.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.disappear.tooltip.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.vticker-min.js"></script>
<?php if($apply['Recommend'] and $isRecommend): ?><script type="text/javascript" src="/<?php echo (APP_NAME); ?>/Recommend/View/default/public/Recommend.js"></script>
    <link href="/<?php echo (APP_NAME); ?>/Recommend/View/default/public/plugin-recomment.css" rel="stylesheet" type="text/css" /><?php endif; ?>

<script type="text/javascript">
    // 判断导航数量
    var cellNum = $('#J_pc_g .hsg_cell').length;
    if (cellNum <= 4) {
        var newPopHtml = '';
        $('#J_pc_g .hsg_cell').each(function() {
            var cuHref = $(this).attr('href'), cuText = $(this).find('h4').html();
            newPopHtml += '<a href="' +cuHref + '" target="_blank" class="lep">'+cuText+'</a>';
        });
        $('.J_lessPop').html(newPopHtml);
        $('.J_hire_service_pop').addClass('less');
        $('#J_pc_g').remove();
    } else {
        $('.J_lessPop').remove();
    }
    // 搜素类型切换
    $('.J_sea_choose').click(function(event) {
        event.stopPropagation();
        var $thisP = $(this).parent(), $thisDown = $thisP.find('.in_sea_down');
        if ($thisP.hasClass('open')) {
            $thisP.removeClass('open');
        } else {
            $thisP.addClass('open');
        }
        $(document).click(function(event){
            var _con = $('#J_sea_down_box');
            if(!_con.is(event.target) && _con.has(event.target).length === 0){
                $thisP.removeClass('open');
            }
        });
    });
    $('.J_sli').click(function() {
        var oldType = $('#top_search_type').val(), oldText = $('.J_sea_choose').html(), newType = $(this).attr('type'), newText = $(this).html();
        $('#top_search_type').val(newType);$('.J_sea_choose').html(newText);
        $(this).attr('type', oldType);
        $(this).html(oldText);
        $(this).closest('.in_sea_cho').removeClass('open');
    });
    $('.J_sea_choose_fx').click(function(event) {
        event.stopPropagation();
        var $thisP = $(this).parent(), $thisDown = $thisP.find('.fixed_sea_down');
        if ($thisP.hasClass('open')) {
            $thisP.removeClass('open');
        } else {
            $thisP.addClass('open');
        }
        $(document).click(function(event){
            var _con = $('#J_sea_down_box_fx');
            if(!_con.is(event.target) && _con.has(event.target).length === 0){
                $thisP.removeClass('open');
            }
        });
    });
    $('.J_sli_fx').click(function() {
        var oldType = $('#fixed_search_type').val(), oldText = $('.J_sea_choose_fx').html(), newType = $(this).attr('type'), newText = $(this).html();
        $('#fixed_search_type').val(newType);$('.J_sea_choose_fx').html(newText);
        $(this).attr('type', oldType);
        $(this).html(oldText);
        $(this).closest('.fixed_sea_cho').removeClass('open');
    });
    // 顶部搜索跳转
    $('#top_search_btn').click(function() {
        $('#top_search_input').val(htmlspecialchars($('#top_search_input').val()));
        var post_data = $('#ajax_search_location').serialize();
        if(qscms.keyUrlencode==1){
            post_data = encodeURI(post_data);
        }
        $.post(qscms.root + '?m=Home&c=Index&a=search_location',post_data,function(result){
            if(result.status == 1){
                window.location.href=result.data;
            }
        },'json');
    });
    $('#fixed_search_btn').click(function() {
        $('#fixed_search_input').val(htmlspecialchars($('#fixed_search_input').val()));
        var post_data = $('#fixed_search_location').serialize();
        if(qscms.keyUrlencode==1){
            post_data = encodeURI(post_data);
        }
        $.post(qscms.root + '?m=Home&c=Index&a=search_location',post_data,function(result){
            if(result.status == 1){
                window.location.href=result.data;
            }
        },'json');
    });
    // 回车搜素
    $('#top_search_input').bind('keypress', function(event) {
        e = event ? event : (window.event ? window.event : null);
        if (e.keyCode == 13) {
            $("#top_search_btn").click();
            return false;
        }
    });
    $('#fixed_search_input').bind('keypress', function(event) {
        e = event ? event : (window.event ? window.event : null);
        if (e.keyCode == 13) {
            $("#fixed_search_btn").click();
            return false;
        }
    });
    $('#top_search_input').keyup(function () {
        var currentValue = $(this).val();
        $('#fixed_search_input').val(currentValue);
    });
    $('#fixed_search_input').keyup(function () {
        var currentValue = $(this).val();
        $('#top_search_input').val(currentValue);
    });
    // 固定导航切换
    $('#J_fixed_sb_sw').click(function() {
        $(this).hide();
        $('#J_fixed_sb').hide();
        $('#J_fixed_na').show();
        $('#J_fixed_na_sw').show();
    });
    $('#J_fixed_na_sw').click(function() {
        $(this).hide();
        $('#J_fixed_na').hide();
        $('#J_fixed_sb').show();
        $('#J_fixed_sb_sw').show();
    });
    $(function() {
        var offset = 220;
        $(window).scroll(function(){(
            $(this).scrollTop() > offset ) ? $("#J_qs_search_fixed").addClass('open') : $("#J_qs_search_fixed").removeClass('open');
        });
    });
    // 顶部二维码切换
    $('.in_sea_qr_txt').hover(function() {
        var cuIn = $('.in_sea_qr_txt').index(this);
        $(this).addClass('active').siblings('.in_sea_qr_txt').removeClass('active');
        if (cuIn === 1) {
            $('.in_sea_qr').addClass('open');
        } else {
            $('.in_sea_qr').removeClass('open');
        }
        $('.in_sea_img').eq(cuIn).show().siblings('.in_sea_img').hide();
    });
    // 更多导航
    function hireServicePop() {
        var hireService = $(".J_hire_service")
        var hireServicePop = $(".J_hire_service_pop")
        hireService.hover(function() {
            hireServicePop.show()
        }, function() {
            //hireServicePop.hide()
        });
        hireServicePop.hover(function() {
            // hireServicePop.show()
        }, function() {
            hireServicePop.hide()
        })
    }
    hireServicePop();
    // 职位分类位置调整
    var topArr = [10, 54, 98, 142, 186, 230, 274, 318];
    $('.jno_item_pop').each(function(index) {
        $(this).css('top', '-' + topArr[index] + 'px');
    });
    // 在线人数滚动
    function textSlide(liveNum, a, b) {
        liveNum.animate({
            marginLeft: "-550px"
        }, "slow", function() {
            liveNum.children().slice(a, b).remove().appendTo(liveNum);
            liveNum.css({
                marginLeft: 0
            })
        })
    }
    var applyLive = $(".app_live ul");
    setInterval(function() {
        textSlide(applyLive, 0, 2)
    }, 3000);
    // 轮播
    window.onload = function() {
        var mySwiper = new Swiper('.swiper-container', {
            autoplay: true,
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            }
        })
    };
    var regularMobile = qscms.regularMobile;
    // 获取验证码
    $('#J_get_code').click(function() {
        var mobileValue = $.trim($('#J_sing input[name=mobile]').val());
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
        $.ajax({
            url: qscms.root + '?m=Home&c=Members&a=ajax_check',
            cache: false,
            async: false,
            type: 'post',
            dataType: 'json',
            data: { type: 'mobile', param: mobileValue },
            success: function(result) {
                if (!result.status) {
                    if (eval(qscms.smsTatus)) {
                        if (eval(qscms.varify_mobile) && eval(qscms.captcha_open)) {
                            qsCaptchaHandler(function(callBackArr) {
                                var mobileValue = $.trim($('#J_sing input[name=mobile]').val());
                                var dataArr = {sms_type: 'login', mobile: mobileValue};
                                $.extend(dataArr, callBackArr);
                                $('#J_get_code').val('发送中...').addClass('disabled').prop("disabled",!0);
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
                                                    $('#J_get_code').prop("disabled",0).val('获取验证码').removeClass('disabled');
                                                    countdown = 60;
                                                    return;
                                                } else {
                                                    $('#J_get_code').val('重发' + countdown + '秒').addClass('disabled').prop("disabled",!0);
                                                    countdown--;
                                                }
                                                setTimeout(function() {
                                                    settime()
                                                },1000)
                                            }
                                            settime();
                                        } else {
                                            $('#J_get_code').prop("disabled",0).val('获取验证码').removeClass('disabled');
                                            disapperTooltip("remind", result.msg);
                                        }
                                    }
                                });
                            });
                        } else {
                            // 直接发送验证码
                            $('#J_get_code').val('发送中...').addClass('disabled').prop("disabled",!0);
                            var mobileValue = $.trim($('#J_sing input[name=mobile]').val());
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
                                                $('#J_get_code').prop("disabled",0).val('获取验证码').removeClass('disabled');
                                                countdown = 60;
                                                return;
                                            } else {
                                                $('#J_get_code').val('重发' + countdown + '秒').addClass('disabled').prop("disabled",!0);
                                                countdown--;
                                            }
                                            setTimeout(function() {
                                                settime()
                                            },1000)
                                        }
                                        settime();
                                    } else {
                                        $('#J_get_code').prop("disabled",0).val('获取验证码').removeClass('disabled');
                                        disapperTooltip("remind", result.msg);
                                    }
                                }
                            });
                        }
                    } else {
                        disapperTooltip("remind", "短信未开启");
                    }
                } else {
                    disapperTooltip("remind", "账号不存在！");
                }
            }
        });
    });
    // 申请职位点击事件绑定
    $(".job_apy").die().live('click',function(){
        var qsDialog = $(this).dialog({
            loading: true,
            footer: false,
            header: false,
            border: false,
            backdrop: false
        });
        $('.J_dismiss_modal_close').die().live('click',function(){
            location.reload();
        });
        if (eval(qscms.smsTatus)) {
            var url = "<?php echo U('ajaxPersonal/resume_apply');?>";
            var jid = $(this).data('jid');
            $.getJSON(url,{jid:jid},function(result){
                if(result.status==1) {
                    if(result.data.html){
                        qsDialog.hide();
                        if(result.data.rid){
                            var qsDialogSon = $(this).dialog({
                                title: '申请职位',
                                content: result.data.html,
                                yes: function () {
                                    var url = "<?php echo U('Personal/index');?>";
                                    url = url.replace('rid',result.data.rid);
                                    location.href = url;
                                },
                                btns: ['完善简历', '放弃申请']
                            });
                        }else{
                            var qsDialogSon = $(this).dialog({
                                title: '申请职位',
                                content: result.data.html,
                                yes: function () {
                                    window.location.reload();
                                },
                            });
                        }
                    }
                    else {
                        qsDialog.hide();
                        disapperTooltip("remind", result.msg);
                    }
                } else if(result.data==1){
                    qsDialog.hide();
                    disapperTooltip('remind',result.msg);
                    setTimeout(function() {
                        location.href="<?php echo U('Personal/resume_add');?>";
                    },1000);
                } else {
                    /*if(!result.data && result.status==0){
                        console.log(result);
                        qsDialog.hide();
                        disapperTooltip('remind',result.msg);
                        return false;
                    }*/
                    var regResume = "<?php echo C('qscms_rapid_registration_resume');?>";
                    if (regResume== 1){
                        if (eval(result.dialog)) {
                            var creatsUrl = "<?php echo U('AjaxPersonal/resume_add_dig');?>";
                            $.getJSON(creatsUrl,{jid:jid}, function(result){
                                if(result.status==1){
                                    qsDialog.hide();
                                    var qsDialogSon = $(this).dialog({
                                        content: result.data.html,
                                        footer: false,
                                        header: false,
                                        border: false
                                    });
                                    qsDialogSon.setInnerPadding(false);
                                } else {
                                    qsDialog.hide();
                                    disapperTooltip('remind',result.msg);
                                }
                            });
                        } else {
                            qsDialog.hide();
                            disapperTooltip('remind',result.msg);
                        }
                    }else{
                        var loginUrl = "<?php echo U('AjaxCommon/ajax_login');?>";
                        $.getJSON(loginUrl, function(result){
                            if(result.status==1){
                                qsDialog.hide();
                                var qsDialogSon = $(this).dialog({
                                    header: false,
                                    content: result.data.html,
                                    footer: false,
                                    border: false
                                });
                                qsDialogSon.setInnerPadding(false);
                            } else {
                                qsDialog.hide();
                                disapperTooltip('remind',result.msg);
                            }
                        });
                    }
                }
            });
        } else {
            if (eval(qscms.is_login)) {
                        var url = "<?php echo U('ajaxPersonal/resume_apply');?>";
                    var jid = $(this).data('jid');
                    $.getJSON(url,{jid:jid},function(result){
                        if(result.status==1) {
                            if(result.data.html){
                                qsDialog.hide();
                                if(result.data.rid){
                                    var qsDialogSon = $(this).dialog({
                                        title: '申请职位',
                                        content: result.data.html,
                                        yes: function () {
                                            var url = "<?php echo U('Personal/index');?>";
                                            url = url.replace('rid',result.data.rid);
                                            location.href = url;
                                        },
                                        btns: ['完善简历', '放弃申请']
                                    });
                                }else{
                                    var qsDialogSon = $(this).dialog({
                                        title: '申请职位',
                                        content: result.data.html,
                                        yes: function () {
                                            window.location.reload();
                                        },
                                    });
                                }
                            }
                            else {
                                qsDialog.hide();
                                disapperTooltip("remind", result.msg);
                            }
                        }
                        else if(result.data==1){
                            qsDialog.hide();
                            disapperTooltip('remind',result.msg);
                            setTimeout(function() {
                                location.href="<?php echo U('Personal/resume_add');?>";
                            },1000);
                        }
                        else
                        {
                            if (eval(result.dialog)) {
                                var creatsUrl = "<?php echo U('AjaxPersonal/resume_add_dig');?>";
                                $.getJSON(creatsUrl,{jid:jid}, function(result){
                                    if(result.status==1){
                                        qsDialog.hide();
                                        var qsDialogSon = $(this).dialog({
                                            content: result.data.html,
                                            footer: false,
                                            header: false,
                                            border: false
                                        });
                                        qsDialogSon.setInnerPadding(false);
                                    } else {
                                        qsDialog.hide();
                                        disapperTooltip('remind',result.msg);
                                    }
                                });
                            } else {
                                qsDialog.hide();
                                disapperTooltip('remind',result.msg);
                            }
                        }
                    });
            } else {
                var loginUrl = "<?php echo U('AjaxCommon/ajax_login');?>";
                $.getJSON(loginUrl, function(result){
                    if(result.status==1){
                        qsDialog.hide();
                        var qsDialogSon = $(this).dialog({
                            header: false,
                            content: result.data.html,
                            footer: false,
                            border: false
                        });
                        qsDialogSon.setInnerPadding(false);
                    } else {
                        qsDialog.hide();
                        disapperTooltip('remind',result.msg);
                    }
                });
            }
        }
    });
    // 登录
    $('#J_login_btn').click(function() {
        var mobileValue = $.trim($('#J_sing input[name=mobile]').val());
        var verfyCodeValue = $.trim($('#J_sing input[name=verfy_code]').val());
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
        $("#J_login_btn").val('登录中...').addClass('disabled').prop("disabled",!0);
        if (eval(qscms.varify_user_login) && eval(qscms.captcha_open)) {
            qsCaptchaHandler(function(callBackArr) {
                var mobileValue = $.trim($('#J_sing input[name=mobile]').val());
                var verfyCodeValue = $.trim($('#J_sing input[name=verfy_code]').val());
                var dataArr = {mobile: mobileValue, mobile_vcode: verfyCodeValue};
                $.extend(dataArr, callBackArr);
                $.ajax({
                    url: qscms.root+'?m=Home&c=Members&a=login',
                    type: "post",
                    dataType: "json",
                    data: dataArr,
                    success: function(result) {
                        if (parseInt(result.status)) {
                            window.location.reload();
                        } else {
                            qscms.varify_user_login = result.data;
                            disapperTooltip('remind', result.msg);
                            $("#J_login_btn").val('登录').removeClass('disabled').prop("disabled",0);
                        }
                    }
                });
            });
        } else {
            // 未开启验证码
            $.ajax({
                url: qscms.root+'?m=Home&c=Members&a=login',
                type: "post",
                dataType: "json",
                data: {
                    mobile: mobileValue,
                    mobile_vcode: verfyCodeValue
                },
                success: function (result) {
                    if (parseInt(result.status)) {
                        window.location.reload();
                    } else {
                        qscms.varify_user_login = 3; //原来是result.data
                        disapperTooltip('remind', result.msg);
                        $("#J_login_btn").val('登录').removeClass('disabled').prop("disabled",0);
                    }
                }
            });
        }
    });
    // 公告轮播
    $('#J_msg_box').vTicker({
        speed: 700,
        pause: 2000,
        animation: 'fade',
        mousePause: true,
        showItems: 2
    });
    // 最新、热门、推荐职位切换
    $('.J_sw').hover(function() {
        var cuIn = $('.J_sw').index(this);
        $(this).removeClass('sw').siblings('.J_sw').addClass('sw');
        $('.J_sw_job').eq(cuIn).removeClass('hid').siblings('.J_sw_job').addClass('hid');
    });
    // 快速加入
    $('#J_reg_qick').click(function(){
        var qsDialog = $(this).dialog({
            loading: true,
            footer: false,
            header: false,
            border: false,
            backdrop: false
        });
        if($('#JRegHidVal').val() == 1){
            var creatsUrl = qscms.root + '?m=Home&c=AjaxPersonal&a=resume_add_dig';
        }else{
            var creatsUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_regisiter';
        }
        $.getJSON(creatsUrl, {no_apply:1},function(result){
            if(result.status==1){
                qsDialog.hide();
                var qsDialogSon = $(this).dialog({
                    content: result.data.html,
                    footer: false,
                    header: false,
                    border: false
                });
                qsDialogSon.setInnerPadding(false);
            } else {
                qsDialog.hide();
                disapperTooltip("remind", result.msg);
            }
        })
    });
    // 立即注册
    $('#J_reg_now').click(function(){
        var qsDialog = $(this).dialog({
            loading: true,
            footer: false,
            header: false,
            border: false,
            backdrop: false
        });
        var creatsUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_regisiter';
        $.getJSON(creatsUrl, {no_apply:1},function(result){
            if(result.status==1){
                qsDialog.hide();
                var qsDialogSon = $(this).dialog({
                    content: result.data.html,
                    footer: false,
                    header: false,
                    border: false
                });
                qsDialogSon.setInnerPadding(false);
            } else {
                qsDialog.hide();
                disapperTooltip("remind", result.msg);
            }
        })
    });
    // 立即免费邓丽简历
    $('#J_reg_resume').click(function(){
        var qsDialog = $(this).dialog({
            loading: true,
            footer: false,
            header: false,
            border: false,
            backdrop: false
        });
        var creatsUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_regisiter';
        $.getJSON(creatsUrl, {no_apply:1},function(result){
            if(result.status==1){
                qsDialog.hide();
                var qsDialogSon = $(this).dialog({
                    content: result.data.html,
                    footer: false,
                    header: false,
                    border: false
                });
                qsDialogSon.setInnerPadding(false);
            } else {
                qsDialog.hide();
                disapperTooltip("remind", result.msg);
            }
        })
    });
    // 客服QQ
    var app_qq = "<?php echo C('apply.Qqfloat');?>";
    var qq_open = "<?php echo C('qscms_qq_float_open');?>";
    if(app_qq != '' && qq_open == 1){
        var QQFloatUrl = "<?php echo U('Qqfloat/Index/index');?>";
        $.getJSON(QQFloatUrl, function (result) {
            if (result.status == 1) {
                $("body").append(result.data);
            }
        });
    }
    // 返回顶部
    var global = {
        h: $(window).height(),
        st: $(window).scrollTop(),
        backTop: function () {
            global.st > (global.h * 0.5) ? $("#backtop").show() : $("#backtop").hide();
        }
    }
    $('.footer-txt-group .hl').eq($('.footer-txt-group .hl').length-1).addClass('last');
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
    // 刷新职位
    $('#J_res_job_t').click(function(){
        $.getJSON("<?php echo U('CompanyService/jobs_refresh_all');?>",function(result){
            if(result.status==1){
                disapperTooltip('success',result.msg);
            }
            else if(result.status==2)
            {
                var qsDialog = $(this).dialog({
                    title: '批量刷新职位',
                    loading: true,
                    border: false,
                    yes: function () {
                        window.location.href=result.data;
                    }
                });
                qsDialog.setBtns(['单条刷新', '取消']);
                qsDialog.setContent('<div class="refresh_jobs_all_confirm">' + result.msg + '</div>');
            }
            else
            {
                disapperTooltip('remind',result.msg);
            }
        });
    });

    //账号密码登录切换
    $(".password_login").click(function(){
        $(".mobile_login_wrap").hide();
        $(".password_login_wrap").show();
    });
    $(".reg_login").click(function(){
        $(".mobile_login_wrap").show();
        $(".password_login_wrap").hide();
    });
    //用户名和密码登录
    $("#P_login_btn").die().live('click', function() {
        var usernameValue = $.trim($('#J_sing input[name=username]').val());
        var passwordValue = $.trim($('#J_sing input[name=password_code]').val());
        if (usernameValue == '' || passwordValue == '') {
            disapperTooltip('remind', '用户名和密码不能为空');
            return false;
        } else {
            // 判断是否需要出现验证
            if (eval(qscms.varify_user_login) && eval(qscms.captcha_open)) {
                qsCaptchaHandler(function(callBackArr) {
                    var usernameValue = $.trim($('#J_sing input[name=username]').val());
                    var passwordValue = $.trim($('#J_sing input[name=password_code]').val());
                    var dataArr = {username: usernameValue, password: passwordValue};
                    $.extend(dataArr, callBackArr);
                    // 提交表单
                    $.ajax({
                        url: qscms.root + '?m=Home&c=Members&a=login',
                        type: "post",
                        dataType: "json",
                        data: dataArr,
                        success: function(result) {
                            if (parseInt(result.status)) {
                                window.location.reload();
                            } else {
                                disapperTooltip("remind", "密码错误");
                                qscms.varify_user_login = result.data;
                                $("#topLoginBtnMobile").val('登录').prop("disabled", 0);
                            }
                        }
                    });
                });
            } else {
                doLoginByAccount();
            }
        }
        return false;
    });

    function doLoginByAccount() {
        var usernameValue = $.trim($('#J_sing input[name=username]').val());
        var passwordValue = $.trim($('#J_sing input[name=password_code]').val());
        $("#P_login_btn").val('登录中...').prop("disabled", !0);
        // 提交表单
        $.ajax({
            url: qscms.root + '?m=Home&c=Members&a=login',
            type: "post",
            dataType: "json",
            data: {
                username: usernameValue,
                password: passwordValue,
            },
            success: function(result) {
                if (parseInt(result.status)) {
                    window.location.reload();
                } else {
                    qscms.varify_user_login = result.data; //原来是result.data
                    disapperTooltip('remind', result.msg);
                    $("#P_login_btn").val('登录').prop("disabled", 0);
                }
            }
        });
    }
</script>

<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/jquery.modal.dialog.js"></script>
<?php if(C('qscms_subsite_open') == 1): ?><!-- 分站定位代码-->
	<div id="J_sub_dialog" style="display:none">
		<div class="new_choose_city">
			<div class="t1">亲爱的用户您好:</div>
			<div class="t2">请您切换到对应的地区分站，让我们为您提供更准确的职位信息</div>
			<div class="t3">您当前在<?php echo ($subsite_org['district']); ?></div>
			<div class="t4">
				<div class="t41">点击进入</div>
				<a href="<?php echo ($subsite_org['domain']); ?>" class="t42"><?php echo ($subsite_org["s_sitename"]); ?></a>
				<div class="t43">或者切换到以下站点</div>
				<div class="clear"></div>
			</div>
			<div class="t5">
				<?php if(is_array($subsite_list)): $i = 0; $__LIST__ = array_slice($subsite_list,0,10,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$dis): $mod = ($i % 2 );++$i; if($dis['s_id'] == 0): ?><a href="<?php echo C('qscms_site_domain');?>" class="t_item">总站</a>
					<?php else: ?>
						<a href="<?php echo ($dis['pc_type']); echo ($dis['s_domain']); ?>" class="t_item"><?php echo ($dis["s_sitename"]); ?></a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					<?php if(count($subsite_list) > 11): ?><a href="<?php echo U('Home/Index/subsite');?>" class="t_item">更多地区</a><?php endif; ?>
			   
				<div class="clear"></div>
			</div>
			<div class="t6">如果您在使用中遇到任何问题，请随时联系 <?php echo C('qscms_bootom_tel');?> 寻求帮助</div>
		</div>
	</div>
    <script type="text/javascript">
        <?php if(!empty($subsite_org)): ?>showSubDialog();<?php endif; ?>
        $('#J-choose-subcity').click(function () {
            showSubDialog();
        });
        function showSubDialog() {
            var qsDialog = $(this).dialog({
                title: '切换地区',
                showFooter: false,
                border: false
            });
            qsDialog.setContent($('#J_sub_dialog').html());
            $('.sdg-sub-city').each(function (index, value) {
                if ((index + 1) % 4 == 0) {
                    $(this).addClass('no-mr');
                }
            });
        }
    </script><?php endif; ?>
</body>
</html>