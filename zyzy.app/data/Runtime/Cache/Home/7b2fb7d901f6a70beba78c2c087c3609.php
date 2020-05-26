<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
    <link href="../public/css/personal/common.css" rel="stylesheet" type="text/css"/>
    <link href="../public/css/personal/personal_user.css" rel="stylesheet" type="text/css"/>
    <link href="../public/css/personal/personal_ajax_dialog.css" rel="stylesheet" type="text/css"/>
    <script src="../public/js/personal/jquery.common.js" type="text/javascript" language="javascript"></script>
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

<div class="other_top_nav">
    <div class="ot_nav_box">
        <div class="ot_nav_logo"><a href="/">
		<?php if(C('qscms_subsite_open') == 1 && C('subsite_info.s_id') > 0): ?><img src="<?php if(C('subsite_info.s_pc_logo')): echo attach(C('subsite_info.s_pc_logo'),'subsite'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt="">
		<?php else: ?>
		<img src="<?php if(C('qscms_logo_home')): echo attach(C('qscms_logo_home'),'resource'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" border="0"/><?php endif; ?></a></div>
		
        <div class="ot_nav_sub">
            <?php if(!empty($sitegroup)): ?><div class="ot_sub_group" id="J-choose-subcity">
                    <div class="ot_sub_icon"></div>
                    <div class="ot_sub_txt"><?php echo ($sitegroup_org["name"]); ?></div>
                    <div class="clear"></div>
                </div><?php endif; ?>
        </div>
        <div class="ot_nav_link <?php if($sitegroup): ?>has_sub<?php endif; ?>">
            <ul class="link_gray6 nowrap">
                <?php $tag_nav_class = new \Common\qscmstag\navTag(array('列表名'=>'nav','调用名称'=>'QS_top','显示数目'=>'8','cache'=>'0','type'=>'run',));$nav = $tag_nav_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"","keywords"=>"","description"=>"","header_title"=>""),$nav);?>
                <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?><li class="on_li J_hoverbut <?php if(MODULE_NAME == C('DEFAULT_MODULE')): if($nav['tag'] == strtolower(CONTROLLER_NAME)): ?>select<?php endif; else: if($nav['tag'] == strtolower(MODULE_NAME)): ?>select<?php endif; endif; ?>"><a href="<?php echo ($nav['url']); ?>" target="<?php echo ($nav["target"]); ?>"><?php echo ($nav["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="ot_nav_more">
            <span>更多服务</span>
            <div class="nmb_for"></div>
            <div class="nav_more_box">
                <?php if(!empty($apply['School'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_school_index');?>" target="_blank">校园招聘</a></div><?php endif; ?>
                <?php if(!empty($apply['Parttime'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_parttime');?>" target="_blank">兼职招聘</a></div><?php endif; ?>
                <?php if(!empty($apply['Store'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_store');?>" target="_blank">门店招聘</a></div><?php endif; ?>
                <?php if(!empty($apply['Allowance'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_jobslist',array('search_cont'=>'allowance'));?>" target="_blank">红包职位</a></div><?php endif; ?>
                <?php if(!empty($apply['House'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_house_rent');?>" target="_blank">附近租房</a></div><?php endif; ?>
                <?php if(!empty($apply['Gworker'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_gworker');?>" target="_blank">普工招聘</a></div><?php endif; ?>
                <?php if(!empty($apply['Mall'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_mall_index');?>" target="_blank">积分商城</a></div><?php endif; ?>
                <?php if(!empty($apply['Interview'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_interview_list');?>" target="_blank">企业专访</a></div><?php endif; ?>
                <?php if(!empty($apply['Career'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_career_list');?>" target="_blank">直通招考</a></div><?php endif; ?>
                <?php if(!empty($apply['Jobfair'])): ?><div class="nmb_cell"><a href="<?php echo url_rewrite('QS_jobfairlist');?>" target="_blank">现场招聘会</a></div><?php endif; ?>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo C('TPL_HOME_PUBLIC_DIR');?>/js/jquery.modal.dialog.js"></script>
<?php if(!empty($sitegroup)): ?><script id="J-sub-dialog-content" type="text/html">
        <div class="sub-dialog-group">
            <div class="sdg-title">亲爱的用户您好：</div>
            <div class="sdg-split-20"></div>
            <div class="sdg-h-tips">请您切换到对应的分站，让我们为您提供更准确的职位信息。</div>
            <div class="sdg-split-30"></div>
            <div class="sdg-h-line"></div>
            <div class="sdg-split-20"></div>
            <div class="sdg-master-group">
                <div class="sdg-txt-right">切换到以下城市</div>
                <div class="clear"></div>
            </div>
            <div class="sdg-split-20"></div>
            <div class="sdg-sub-city-group">
                <?php if(is_array($sitegroup)): $i = 0; $__LIST__ = array_slice($sitegroup,0,10,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$dis): $mod = ($i % 2 );++$i;?><a href="<?php echo ($dis["domain"]); ?>" class="sdg-sub-city"><?php echo ($dis["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                <?php if(count($sitegroup) > 11): ?><a href="<?php echo U('Home/Subsite/index');?>" class="sdg-sub-city more">更多分站</a><?php endif; ?>
                <div class="clear"></div>
            </div>
            <div class="sdg-split-16"></div>
            <div class="sdg-bottom-tips">如果您在使用中遇到任何问题，请随时联系 <?php if(C('qscms_top_tel')): echo C('qscms_top_tel'); else: echo C('qscms_bootom_tel'); endif; ?> 寻求帮助</div>
            <div class="sdg-split-11"></div>
        </div>
    </script>
    <script type="text/javascript">
      $('#J-choose-subcity').click(function () {
        showSubDialog();
      });
      function showSubDialog() {
        var qsDialog = $(this).dialog({
          title: '切换地区',
          showFooter: false,
          border: false
        });
        qsDialog.setContent($('#J-sub-dialog-content').html());
        $('.sdg-sub-city').each(function (index, value) {
          if ((index + 1) % 4 == 0) {
            $(this).addClass('no-mr');
          }
        });
      }
    </script><?php endif; ?>
<div class="us-top">
    <div class="us-main">
        <div class="us-top-box">
            <form id="ajax_search_location" action="<?php echo U('ajaxCommon/ajax_search_location',array('type'=>'QS_jobslist'));?>" method="get">
                <div class="input-box"><input type="text" name="key" data-original="请输入关键字" value="" placeholder="请输入关键字" autocomplete="off"></div>
                <div class="se-btn"><input type="submit" value="搜 索"></div>
                <div class="clear"></div>
            </form>
        </div>
    </div>
</div>
<div class="user_main">
    <div class="index-left">
    <div class="ph-28"></div>
    <div class="left-logo"><a href="<?php echo U('personal/user_avatar');?>"><img src="<?php echo ($visitor['avatars']); ?>?<?php echo time();?>" /></a></div>
    <div class="ph-30"></div>
    <div class="left-line link_gray6"><a target="_blank" href="<?php echo U('personal/index',array('uid'=>$visitor['uid']));?>"><?php echo ($visitor['fullname']); ?></a></div>
    <div class="ph-25"></div>
    <div class="left-line">简历完整度：<?php echo ($visitor['complete_percent']); ?>% <?php if($visitor['level'] == 3): ?><span class="green-txt">(优)</span><?php elseif($visitor['level'] == 1): ?><span class="red-txt">(差)</span><?php else: ?><span class="yellow-txt">(良)</span><?php endif; ?></div>
    <div class="ph-20"></div>
    <div class="per-box"><div class="gre-box" style="width: <?php echo ($visitor['complete_percent']); ?>%"></div></div>
    <div class="ph-20"></div>
    <div class="sign-box">
        <div class="sign-left"><?php echo C('qscms_points_byname');?>：<span class="J_points_num"><?php echo (($visitor['points'] != "")?($visitor['points']):0); ?></span></div>
        <div class="sign-right <?php if($visitor['issign']): ?>gr<?php else: ?>bl<?php endif; ?>" id="J_sign_in"><?php if($visitor['issign']): ?>已签到<?php else: ?>未签到<?php endif; ?></div>
    <div class="clear"></div>
</div>
<div class="ph-20"></div>
<div class="left-nav-box">
    <a href="<?php echo U('personal/index',array('uid'=>$visitor['uid']));?>" class="li-nav <?php if($personal_nav == 'index'): ?>select<?php endif; ?>">
    <div class="nav-ic index"></div>
    <div class="nav-name">我的简历</div>
    <div class="clear"></div>
    </a>
    <a href="<?php echo U('personal/jobs_interview');?>" class="li-nav <?php if($personal_nav == 'apply'): ?>select<?php endif; ?>">
    <div class="nav-ic job"></div>
    <div class="nav-name">求职管理</div>
    <div class="clear"></div>
    </a>
    <a href="<?php echo U('personal/jobs_favorites');?>" class="li-nav <?php if($personal_nav == 'jobs_favorites'): ?>select<?php endif; ?>">
    <div class="nav-ic att"></div>
    <div class="nav-name">收藏&关注</div>
    <div class="clear"></div>
    </a>
    <a href="<?php echo U('personalService/index');?>" class="li-nav <?php if($personal_nav == 'service'): ?>select<?php endif; ?>">
    <div class="nav-ic ser"></div>
    <div class="nav-name">会员服务</div>
    <div class="clear"></div>
    </a>
    <?php if(C('apply.Allowance')): ?><a href="<?php echo U('personal/allowance');?>" class="li-nav <?php if($personal_nav == 'allowance'): ?>select<?php endif; ?>">
    <div class="nav-ic all"></div>
    <div class="nav-name">我的红包</div>
    <div class="clear"></div>
    </a>
	<!-- <a href="<?php echo U('AdvPersonal/adv_resume');?>" class="li-nav <?php if($personal_nav == 'adv_resume' || $personal_nav == 'adv_index'): ?>select<?php endif; ?>">
        <div class="nav-ic adv"></div>
        <div class="nav-name">高级简历</div>
        <div class="clear"></div>
        </a> --><?php endif; ?>
    <?php if(C('qscms_share_allowance_open') || C('qscms_inviter_perfected_resume_allowance_open')): ?><a href="<?php echo ($url); ?>" class="li-nav <?php if($personal_nav == 'share_allowance_partake' || $personal_nav == 'invite_friend'): ?>select<?php endif; ?>">
        <div class="nav-ic share"></div>
        <div class="nav-name">分享赚钱</div>
        <div class="clear"></div>
        </a><?php endif; ?>
    <a href="<?php echo U('personal/user_safety');?>" class="li-nav <?php if($personal_nav == 'user_info'): ?>select<?php endif; ?>">
    <div class="nav-ic user"></div>
    <div class="nav-name">账号管理</div>
    <div class="clear"></div>
    </a>
</div>
</div>
<script>
    // 搜索
    $('#ajax_search_location').submit(function(){
        var nowKeyValue = $.trim($('input[name="key"]').val());
        var orgKeyValue = $.trim($('input[name="key"]').data('original'));
        if(nowKeyValue.length && nowKeyValue.length<2){
            disapperTooltip("remind",'关健字长度需大于2个字！');
            return !1;
        }
        if (!(nowKeyValue == orgKeyValue)) {
            $('.J_forclear').val('');
        }
        $('input[name="key"]').val(htmlspecialchars($('input[name="key"]').val()));
        var post_data = $('#ajax_search_location').serialize();
        if(qscms.keyUrlencode==1){
            post_data = encodeURI(post_data);
        }
        $.post($('#ajax_search_location').attr('action'),post_data,function(result){
            window.location=result.data;
        },'json');
        return false;
    });
    // 签到
    $('#J_sign_in').click(function(){
        var f = $(this);
        $.getJSON("<?php echo U('Members/sign_in');?>",function(result){
            if(result.status == 1){
                disapperTooltip("goldremind", '每天签到增加'+result.data+'<?php echo C("qscms_points_byname");?><span class="point">+'+result.data+'</span>');
                f.addClass('gr').text('已签到');
                $(".J_points_num").html(parseInt($(".J_points_num").html())+parseInt(result.data));
            }else{
                disapperTooltip('remind',result.msg);
            }
        });
    });
</script>
    <div class="mainbox">
        <!--切换卡 -->
        <div class="tab">
            <a class="li J_hoverbut" href="<?php echo U('index');?>">我的<?php echo C('qscms_points_byname');?></a>
            <a class="li  J_hoverbut" href="<?php echo U('task');?>">我的任务</a>
            <a class="li select ">增值服务</a>
            <a class="li J_hoverbut " href="<?php echo U('order_list');?>">服务订单</a>
            <?php if(!empty($apply['Mall'])): ?><a class="li J_hoverbut " href="<?php echo U('order_list_goods');?>">商城订单</a><?php endif; ?>
            <div class="clear"></div>
        </div>
        <!--切换卡结束 -->
        <div class="service">

            <div class="list">

                <div class="lib J_hoverbut">
                    <div class="timg">简历置顶</div>
                    <div class="txt">
                        1.投递简历时，简历将靠前显示<br/>

                        2.企业搜索简历时，系统将优先您的简历<br/>
                        3.专属推荐图标显示，让您的简历更醒目<br/>
                    </div>
                    <div class="btnbox">
                        <a href="<?php echo U('increment_add');?>" class="btn_yellow J_hoverbut btn_inline ">立即置顶</a>
                    </div>
                </div>

            </div>

            <div class="list">
                <div class="lib J_hoverbut">
                    <div class="timg m2">醒目标签</div>
                    <div class="txt">
                        1.一对一专业服务，全面分析您的求职情况<br/>
                        2.行业招聘专家专业制作，简历优势更突出<br/>
                        3.个性模版助您快速高效获得面试机会<br/>
                    </div>
                    <div class="btnbox"><a href="<?php echo U('increment_add',array('cat'=>'tag'));?>"
                                           class="btn_yellow J_hoverbut btn_inline ">添加标签</a></div>
                </div>
            </div>

            <div class="list">
                <div class="lib J_hoverbut">
                    <div class="timg m3">简历模板</div>
                    <div class="txt">
                        1.一对一专业服务，全面分析您的求职情况<br/>
                        2.行业招聘专家专业制作，简历优势更突出<br/>
                        3.个性模版助您快速高效获得面试机会
                    </div>
                    <div class="btnbox"><a href="<?php echo U('increment_add',array('cat'=>'tpl'));?>"
                                           class="btn_yellow J_hoverbut btn_inline ">更换模板</a></div>
                </div>
            </div>

            <!--<div class="list">
                <div class="lib J_hoverbut">
                    <div class="timg m4">人才测评</div>
                    <div class="txt">
                        1.深入了解自己，明确职业发展方向<br/>
                        2.根据测评结果，选择适合自己的工作<br/>
                        3.认清自我，扬长避短，更好的自我发展
                    </div>
                    <div class="btnbox"><a href="<?php echo U('personal/user_order_add');?>"
                                           class="btn_yellow J_hoverbut btn_inline ">立即测评</a></div>
                </div>
            </div>-->

            <?php if(!empty($apply['Mall'])): ?><div class="list">
                    <div class="lib J_hoverbut">
                        <div class="timg m5"><?php echo C('qscms_points_byname');?>商城</div>
                        <div class="txt">

                            1.使用<?php echo C('qscms_points_byname');?>可以在商城兑换海量精美礼品<br/>
                            2.可通过完善资料、签到等方式获得<?php echo C('qscms_points_byname');?><br/>
                            3.可通过我的任务模块赚取更多<?php echo C('qscms_points_byname');?>
                        </div>
                        <div class="btnbox"><a href="<?php echo url_rewrite('QS_mall_index');?>"
                                               class="btn_yellow J_hoverbut btn_inline ">立即兑换</a></div>
                    </div>
                </div><?php endif; ?>
        </div>


    </div>
    <div class="clear"></div>
</div>
<div class="user_foot font_gray9" id="footer"><?php echo C('qscms_bottom_other');?></div>
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
<script type="text/javascript" src="../public/js/jquery.modal.dialog.js"></script>
<script type="text/javascript" src="../public/js/jquery.tooltip.js"></script>
<script type="text/javascript" src="../public/js/jquery.disappear.tooltip.js"></script>
<script type="text/javascript" src="../public/js/jquery.listitem.js"></script>
<script type="text/javascript" src="../public/js/jquery.dropdown.js"></script>
<!--[if lt IE 9]>
	<script type="text/javascript" src="../public/js/PIE.js"></script>
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
<script type="text/javascript">
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
  })
  // 强制手机认证
  <?php if($visitor['utype'] == 2 and $visitor['mobile'] == ''): ?>var authMobileDialog = $(this).dialog({
        title: '认证手机',
        loading: true,
        showFooter: false,
        showClose: false,
        btnOne: true,
        btns: ['确定'],
        yes: function() {
            var verifycode  = $.trim($('#J_mobileWrap input[name="verifycode"]').val());
            if(!verifycode){
                disapperTooltip("remind", "请填写验证码！");
                return !1;
            }
            $.post("<?php echo U('Members/verify_mobile_code');?>",{verifycode:verifycode},function(result){
                if(result.status == 1){
                    $('#mobileWap').html(result.data.mobile);
                    if(result.data.points){
                        disapperTooltip("goldremind", '验证手机号增加'+result.data.points+'<?php echo C('qscms_points_byname');?><span class="point">+'+result.data.points+'</span>');
                    }else{
                        disapperTooltip('success',result.msg);
                    }
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }else{
                    disapperTooltip('remind',result.msg);
                }
            },'json');
        }
    });
    authMobileDialog.setCloseDialog(false);
    $.getJSON("<?php echo U('Members/user_mobile');?>",function(result){
        if(result.status == 1){
            authMobileDialog.setContent(result.data);
            authMobileDialog.showFooter(true);
        }else{
            authMobileDialog.setContent('<div class="confirm">' + result.msg + '</div>');
        }
    });<?php endif; ?>
</script>
</body>
</html>