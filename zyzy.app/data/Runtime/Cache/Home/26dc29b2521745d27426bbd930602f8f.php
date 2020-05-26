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
	<link href="../public/css/personal/common.css" rel="stylesheet" type="text/css" />
	<link href="../public/css/personal/personal_ajax_dialog.css" rel="stylesheet" type="text/css" />
	<link href="../public/css/personal/personal_index.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css" />
    <link href="../public/css/common_ajax_dialog.css" rel="stylesheet" type="text/css" />
	<script src="../public/js/personal/jquery.common.js" type="text/javascript" language="javascript"></script>
	<script src="../public/js/jquery.cookie.js" type="text/javascript" language="javascript"></script>
	<?php $tag_load_class = new \Common\qscmstag\loadTag(array('type'=>'category','search'=>'1','cache'=>'0','列表名'=>'list',));$list = $tag_load_class->category();?>
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
	<?php if(C('qscms_perfected_resume_allowance_open') == 1): ?><!--红包领取成功弹出框-->
	<div class="get-money-fail-suc perfected_resume" style="display: none">
	    <div class="gm-fs-group">
	        <div class="gm-fs-clo"></div>
	        <div class="cash-line">
	            <div class="cl-cell cl-big">000</div>
	            <div class="cl-cell">元</div>
	            <div class="clear"></div>
	        </div>
	        <div class="h119"></div>
	        <div class="qr-group"><img src="<?php echo attach(C('qscms_weixin_img'),'resource');?>" alt=""></div>
	        <div class="h16"></div>
	        <div class="t-co-f">你的简历完整度超过<?php echo C('qscms_perfected_resume_allowance_percent');?>%，已获得系统赠送的随机红包</div>
	        <div class="h15"></div>
	        <div class="t-co-dr">本活动最终解释权归<?php echo C('qscms_site_name');?>所有</div>
	    </div>
	</div><?php endif; ?>
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
        <div class="index-right">
			<div class="ph-28"></div>
			<div class="res_name_group">
				<div class="rg1 link_gray6"><a target="_blank" href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume_info['id'],'preview'=>1));?>">我的简历</a></div>
				<div class="rg2 J_tpl_btn" pid="<?php echo ($resume_info["id"]); ?>"></div>
                <div class="rg_privacy J_privacy_btn <?php if($resume_close == 0): ?>close<?php endif; ?>" pid="<?php echo ($resume_info["id"]); ?>"></div>
				<div class="clear"></div>
			</div>
			<div class="ph-30"></div>
			<div class="res_app_gropu">
				<div class="res_app_cell">
					<div class="ph-12"></div>
					<div class="res_cell_line">完整度<?php if($visitor['level'] == 3): ?><span class="green-txt"> (优)</span><?php elseif($visitor['level'] == 1): ?><span class="red-txt"> (差)</span><?php else: ?><span class="yellow-txt"> (良)</span><?php endif; ?></div>
					<div class="ph-14"></div>
					<div class="res_cell_line"><span class="<?php if($visitor['level'] == 3): ?>green-txt<?php elseif($visitor['level'] == 1): ?>red-txt<?php else: ?>yellow-txt<?php endif; ?>"><?php echo ($resume_info['complete_percent']); ?>%</span></div>
				</div>
				<div class="hm_64_42"></div>
				<div class="res_app_cell">
					<div class="ph-12"></div>
					<div class="res_cell_line">刷新时间</div>
					<div class="ph-14"></div>
					<div class="res_cell_line"><?php echo fdate($resume_info['refreshtime']);?></div>
				</div>
				<div class="hm_64_42"></div>
				<div class="res_app_cell" onclick="window.location='<?php echo U('personal/attention_me',array('resume_id'=>$resume_info['id']));?>'">
					<div class="ph-12"></div>
					<div class="res_cell_line">简历被关注</div>
					<div class="ph-14"></div>
					<div class="res_cell_line"><?php echo ($resume_info['views']); ?></div>
				</div>
				<div class="hm_64_42"></div>
				<div class="res_app_cell" onclick="window.location='<?php echo U('personal/jobs_apply',array('resume_id'=>$resume_info['id']));?>'">
					<div class="ph-12"></div>
					<div class="res_cell_line">已申请职位</div>
					<div class="ph-14"></div>
					<div class="res_cell_line"><?php echo ($resume_info['countapply']); ?></div>
				</div>
				<div class="hm_64_42"></div>
				<div class="res_app_cell" onclick="window.location='<?php echo U('personal/jobs_interview',array('resume_id'=>$resume_info['id']));?>'">
					<div class="ph-12"></div>
					<div class="res_cell_line">邀请面试</div>
					<div class="ph-14"></div>
					<div class="res_cell_line"><?php echo ($resume_info['countinterview']); ?></div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="ph-30"></div>
			<div class="re_l">
				<!--基本信息 -->
				<input type="hidden" name="pid" id="pid" value="<?php echo ($resume_info["id"]); ?>">
				<div id="positionInfo" class="J_itemsbox">
					<div class="items J_hoverbut">
					  	<div class="il">基本资料
                            <?php switch($resume_info['_audit']): case "1": ?><span class="txt_status font_green">审核通过</span><?php break;?>
                                <?php case "2": ?><span class="txt_status font_yellow">审核中</span><?php break;?>
                                <?php case "3": ?><span class="txt_status font_red">审核未通过</span><div class="no_rea">
                                    <div class="des_box">
                                        <div class="desarrow"></div>
                                        <div class="des_txt">原因：<?php echo ($audit_reason['reason']); ?></div>
                                    </div>
                                    </div><?php break; endswitch;?>
                        </div>
					    <div class="ir"> <div class="but_gray_70_res_edit J_hoverbut J_showitems">修改</div> </div>
						<div class="clear"></div>
						<div class="photobox">
							<div class="pright">
								<div class="sex_age">
                                    <div class="td1">姓名：<span data-sub="fullname"><?php echo ($resume_info["fullname"]); ?></span></div>
									<div class="td1">性别：<span data-sub="sex"><?php echo ($resume_info["sex_cn"]); ?></span></div>
									<div class="td1">年龄：<span data-sub="birthdate"><?php echo Date('Y')-$resume_info['birthdate'];?></span></div>
									<div class="td1">婚否：<span data-sub="marriage"><?php echo ($resume_info["marriage_cn"]); ?></span></div>
									<div class="td1">身高：
									<?php if(!empty($resume_info['height'])): ?><span data-sub="height"><?php echo ($resume_info["height"]); ?></span>cm
									<?php else: ?>
									<span data-sub="height">未填写</span><?php endif; ?>
									</div>
									<div class="td1">学历：<span data-sub="education"><?php echo ($resume_info["education_cn"]); ?></span></div>
									<div class="td1">工作经验：<span data-sub="experience"><?php echo ($resume_info["experience_cn"]); ?></span></div>
									<div class="td1 substring">居住地：
									<?php if(!empty($resume_info['residence'])): ?><span data-sub="residence" title="<?php echo ($resume_info["residence"]); ?>"><?php echo ($resume_info["residence"]); ?></span>
									<?php else: ?>
									<span data-sub="major">未填写</span><?php endif; ?>
									</div>
									<div class="td1">专业：
									<?php if(!empty($resume_info['major_cn'])): ?><span data-sub="major"><?php echo ($resume_info["major_cn"]); ?></span>
									<?php else: ?>
									<span data-sub="major">未填写</span><?php endif; ?>
									</div>
									<div class="td1 substring">籍贯：
									<?php if(!empty($resume_info['householdaddress'])): ?><span data-sub="householdaddress" title="<?php echo ($resume_info["householdaddress"]); ?>"><?php echo ($resume_info["householdaddress"]); ?></span>
									<?php else: ?>
									<span data-sub="householdaddress">未填写</span><?php endif; ?>
									</div>
									<div class="td1 substring">QQ：
									<?php if(!empty($resume_info['qq'])): ?><span data-sub="qq" title="<?php echo ($resume_info["qq"]); ?>"><?php echo ($resume_info["qq"]); ?></span>
									<?php else: ?>
									<span data-sub="qq">未填写</span><?php endif; ?>
									</div>
									<div class="td1 substring">微信号：
									<?php if(!empty($resume_info['weixin'])): ?><span data-sub="weixin" title="<?php echo ($resume_info["weixin"]); ?>"><?php echo ($resume_info["weixin"]); ?></span>
									<?php else: ?>
									<span data-sub="weixin">未填写</span><?php endif; ?>
									</div>
									<div class="td1 substring">手机号：
									    <span class="font_blue" id="mobileWap" data-sub="mobile" title="<?php echo ($resume_info['telephone']); ?>"><?php echo (($resume_info['telephone'] != "")?($resume_info['telephone']):'未填写'); ?></span>
                                        <div class="i_edit" id="J_auth_mobile"></div>
									</div>
									<div class="clear"></div>
								</div>
							</div>
							<div class="pleft">
						   		<div class="pic">
                                    <a href="<?php echo U('personal/user_avatar');?>"><img  border="0" src="<?php echo ($visitor['avatars']); ?>"  width="140" height="140" /></a>
					   		  </div>
                                <div class="img_pri"><label><input name="photo_display" id="photo_display" type="checkbox" value="2" <?php if($resume_info['photo_display'] == 2): ?>checked="checked"<?php endif; ?>/>&nbsp;头像不公开</label></div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div class="itemsform J_itemsmenu">
						<div class="ftit">基本资料<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0">
								<tr>
									<td class="w1"><span>&nbsp;</span>姓名：</td>
									<td>
										<div class="input_205_34_div2">
											<input name="fullname" id="fullname" type="text"  dir="no_focus" class="inputst" value="<?php echo ($resume_info["fullname"]); ?>"/>
											<div class="righttxt J_tooltip J_listitme_parent">
												<span class="J_listitme_text">
													<?php switch($resume_info['display_name']): case "1": ?>完全公开<?php break;?>
														<?php case "2": ?>显示编号<?php break;?>
														<?php case "3": ?>隐藏名字<?php break; endswitch;?>
												</span>
												<div class="dropdowbox15 J_tooltip_menu">
										            <div class="dropdow_inner15">
										                <ul class="nav_box">
										                    <li><a class="J_listitme" href="javascript:;" data-code="1">完全公开</a></li>
										                    <li><a class="J_listitme" href="javascript:;" data-code="2">显示编号</a></li>
										                    <li><a class="J_listitme" href="javascript:;" data-code="3">隐藏名字</a></li>
										                </ul>
										            </div>
										        </div>
												<input class="J_listitme_code" name="display_name" id="display_name" type="hidden"  value="<?php echo ($resume_info["display_name"]); ?>"/>
											</div>
											<div class="clear"></div>
										</div>
									</td>
									<td  class="w1"><span>&nbsp;</span>性别：</td>
									<td>
										<div class="sex_radio_list J_radioitme_parent">
										    <div class="n <?php if($resume_info['sex'] == 1): ?>checked<?php endif; ?> for_sex J_radioitme" data-code="1">男</div>
											<div class="w <?php if($resume_info['sex'] == 2): ?>checked<?php endif; ?> for_sex J_radioitme" data-code="2">女</div>
											<div class="clear"></div>
											<input class="J_radioitme_code" name="sex" id="sex" type="hidden" value="<?php echo ($resume_info["sex"]); ?>" />
										</div>
									</td>
								</tr>
								<tr>
									<td class="w1"><span>&nbsp;</span>出生年份：</td>
									<td>
										<div class="input_205_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="J_listitme_text"><?php echo ($resume_info['birthdate']); ?></span>
											<div class="dropdowbox9 J_dropdown_menu">
									            <div class="dropdow_inner9">
									                <ul class="nav_box J_birthdy">
									                	<div class="J_birthday_box active"></div>
									                	<div class="J_birthday_box"></div>
									                	<div class="J_birthday_box"></div>
									                </ul>
								                	<a href="javascript:;" class="prev J_birthday_prev"></a>
								                	<a href="javascript:;" class="next J_birthday_next"></a>
									            </div>
									        </div>
											<input class="J_listitme_code" name="birthdate" id="birthdate" type="hidden" value="<?php echo ($resume_info["birthdate"]); ?>">
										</div>
									</td>
									<td class="w1">现居住地：</td>
									<td><input name="residence" id="residence" type="text" class="input_205_34" value="<?php echo ($resume_info["residence"]); ?>"/></td>
								</tr>
								<tr>
									<td class="w1"><span>&nbsp;</span>最高学历：</td>
									<td>
										<div class="input_205_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="J_listitme_text for_education"><?php echo (($resume_info["education_cn"] != "")?($resume_info["education_cn"]):'请选择'); ?></span>
											<div class="dropdowbox10 J_dropdown_menu">
									            <div class="dropdow_inner10">
									                <ul class="nav_box">
									                	<?php if(is_array($category['QS_education'])): $i = 0; $__LIST__ = $category['QS_education'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$education): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($education); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
									                </ul>
									            </div>
									        </div>
									        <input class="J_listitme_code" name="education" id="education" type="hidden" value="<?php echo ($resume_info["education"]); ?>" />
										</div>
									</td>
									<td class="w1"><span>&nbsp;</span>工作经验：</td>
									<td>
										<div class="input_205_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="J_listitme_text for_experience"><?php echo (($resume_info["experience_cn"] != "")?($resume_info["experience_cn"]):'请选择'); ?></span>
											<div class="dropdowbox10 J_dropdown_menu">
									            <div class="dropdow_inner10">
									                <ul class="nav_box">
									                	<?php if(is_array($category['QS_experience'])): $i = 0; $__LIST__ = $category['QS_experience'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$experience): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($experience); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
									                </ul>
									            </div>
									        </div>
									        <input class="J_listitme_code" name="experience" id="experience" type="hidden" value="<?php echo ($resume_info["experience"]); ?>" />
										</div>
									</td>
								</tr>
							</table>
							<div class="imgtitleshow font_blue" id="J_addmore">展开更多信息</div>
							<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" id="J_addmore_show" style="display:none">
								<tr>
									<td class="w1">所学专业：</td>
									<td>
										<div class="input_205_34_div1 J_hoverinput" id="J_showmodal_major" data-title="请选择所学专业" data-multiple="false" data-maxnum="3" data-width="520">
											<span title="" class="for_major result J_resuletitle_major"><?php echo (($resume_info["major_cn"] != "")?($resume_info["major_cn"]):'请选择'); ?></span>
											<input class="J_resultcode_major" name="major" id="major" type="hidden" value="<?php echo ($resume_info["major"]); ?>" />
											<div class="clear"></div>
										</div>
									</td>
									<td class="w1">身高：</td>
									<td>
									<div class="input_205_34_div3">
										<input name="height" id="height" type="text"  dir="no_focus" class="inputst" onkeyup="if(event.keyCode !=37 &amp;&amp; event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))" value="<?php if($resume_info['height'] == 0): else: echo ($resume_info["height"]); endif; ?>"/>
										<div class="righttxt">CM</div>
									  </div>
									</td>
								</tr>
								<tr>
									<td class="w1">籍贯：</td>
									<td>
										<input name="householdaddress" id="householdaddress" type="text" value="<?php echo ($resume_info["householdaddress"]); ?>" class="input_205_34"/>
									</td>
									<td class="w1">婚姻状况：</td>
									<td>
										<div class="radio_list J_radioitme_parent">
										    <div class="rli <?php if($resume_info['marriage'] == 1): ?>checked<?php endif; ?> for_marriage J_radioitme" data-code="1">未婚</div>
											<div class="rli <?php if($resume_info['marriage'] == 2): ?>checked<?php endif; ?> for_marriage J_radioitme" data-code="2">已婚</div>
											<div class="rli <?php if($resume_info['marriage'] == 3): ?>checked<?php endif; ?> for_marriage J_radioitme" data-code="3">保密</div>
											<div class="clear"></div><input class="J_radioitme_code" name="marriage" id="marriage" type="hidden" value="<?php echo ($resume_info["marriage"]); ?>" />
										</div>
								    </td>
								</tr>
								<tr>
									<td class="w1">QQ：</td>
									<td>
										<input name="qq" id="qq" type="text" value="<?php echo ($resume_info["qq"]); ?>" class="input_205_34"/>
									</td>
									<td class="w1">微信号：</td>
									<td>
										<input name="weixin" id="weixin" type="text" value="<?php echo ($resume_info["weixin"]); ?>" class="input_205_34"/>
								    </td> 
								</tr> 
								<tr>
									<td class="w1">邮箱：</td>
									<td>
										<input name="email" id="email" type="text" value="<?php echo ($resume_info["email"]); ?>" class="input_205_34"/>
									</td>
									<td class="w1">身份证号：</td>
									<td>
										<input name="idcard" id="idcard" type="text" value="<?php echo ($resume_info["idcard"]); ?>" class="input_205_34"/>
								    </td> 
								</tr> 
								<input name="audit" id="audit" type="hidden" value="<?php echo ($resume_info["audit"]); ?>"> 
							</table>
							<div class="butbox">
						   		<div class="td1"><input type="button"  class="but_blue_115 J_hoverbut J_saveitems_baseinfo" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--求职意向 -->
				<div id="positionJobintention" class="J_itemsbox">
					<div class="items J_hoverbut">
					  	<div class="il">求职意向</div>
					    <div class="ir"><div class="but_gray_70_res_edit J_hoverbut J_showitems">修改</div></div>
						<div class="clear"></div>
						<table  border="0" align="center" cellpadding="0" cellspacing="0" class="b_table">
							<tr>
								<td width="50%"><div class="overhide">求职状态：</div><span class="overhide" data-sub="current" title="<?php echo ($resume_info["current_cn"]); ?>"><?php echo ($resume_info["current_cn"]); ?></span></td>
								<td><div class="overhide">工作性质：</div><span class="overhide" data-sub="nature" title="<?php echo ($resume_info["nature_cn"]); ?>"><?php echo ($resume_info["nature_cn"]); ?></span></td>
							</tr>
							<tr>
								<td><div class="overhide">期望行业：</div><span class="overhide" data-sub="trade" title="<?php echo ($resume_info["trade_cn"]); ?>"><?php if($resume_info['trade_cn']): echo ($resume_info["trade_cn"]); else: ?>不限<?php endif; ?></span></td>
								<td><div class="overhide">期望职位：</div><span class="overhide" data-sub="intention_jobs" title="<?php echo ($resume_info["intention_jobs"]); ?>"><?php echo ($resume_info["intention_jobs"]); ?></span></td>
							</tr>
							<tr>
								<td><div class="overhide">工作地区：</div><span class="overhide" data-sub="district" title="<?php echo ($resume_info["district_cn"]); ?>"><?php echo ($resume_info["district_cn"]); ?></span></td>
								<td><div class="overhide">期望薪资：</div><span class="overhide" data-sub="wage" title="<?php echo ($resume_info["wage_cn"]); ?>"><?php echo ($resume_info["wage_cn"]); ?></span></td>
							</tr>
						</table>
					</div>
					<div class="itemsform J_itemsmenu">
						<div class="ftit">求职意向<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody">
							<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="b_table">
								<tr>
									<td class="w1"><span>&nbsp;</span>目前状态：</td>
									<td>
										<div class="input_205_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="result J_listitme_text for_current"><?php echo (($resume_info["current_cn"] != "")?($resume_info["current_cn"]):'请选择'); ?></span>
											<div class="dropdowbox10 J_dropdown_menu">
									            <div class="dropdow_inner10">
									                <ul class="nav_box">
									                	<?php if(is_array($category['QS_current'])): $i = 0; $__LIST__ = $category['QS_current'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$current): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>" title="<?php echo ($current); ?>"><?php echo ($current); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
									                </ul>
									            </div>
									        </div>
									        <input class="J_listitme_code" name="current" id="current" type="hidden" value="<?php echo ($resume_info["current"]); ?>">
										</div>
									</td>
									<td  class="w1"><span>&nbsp;</span>工作性质：</td>
									<td>
										<div class="radio_list J_radioitme_parent">
											<?php if(is_array($category['QS_jobs_nature'])): $i = 0; $__LIST__ = $category['QS_jobs_nature'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nature): $mod = ($i % 2 );++$i;?><div class="rli <?php if($key == $resume_info['nature']): ?>checked<?php endif; ?> J_radioitme for_nature" data-code="<?php echo ($key); ?>"><?php echo ($nature); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
											<div class="clear"></div>
											<input class="J_radioitme_code" name="nature" id="nature" type="hidden" value="<?php echo ($resume_info["nature"]); ?>" />
										</div>
									</td>
								</tr>
								<tr>
									<td class="w1">期望行业：</td>
									<td>
										<div class="input_205_34_div1 J_hoverinput" id="J_showmodal_trade" data-title="请选择期望行业" data-multiple="true" data-maxnum="3" data-width="682">
											<span title="" class="for_trade result J_resuletitle_trade"><?php echo (($resume_info["trade_cn"] != "")?($resume_info["trade_cn"]):'不限'); ?></span>
											<input class="J_resultcode_trade" name="trade" id="trade" type="hidden" value="<?php echo ($resume_info["trade"]); ?>" />
											<div class="clear"></div>
										</div>
									</td>
									<td class="w1"><span>&nbsp;</span>期望职位：</td>
									<td>
										<div class="input_205_34_div1 J_hoverinput" id="J_showmodal_jobs" data-title="请选择期望职位" data-multiple="true" data-maxnum="5" <?php if(C('qscms_category_jobs_level') > 2): ?>data-width="667"<?php else: ?>data-width="520"<?php endif; ?> data-category="<?php echo C('qscms_category_jobs_level');?>">
											<span title="" class="for_intention_jobs result J_resuletitle_jobs"><?php echo (($resume_info["intention_jobs"] != "")?($resume_info["intention_jobs"]):'请选择'); ?></span>
											<input class="J_resultcode_jobs" name="intention_jobs_id" id="intention_jobs_id" type="hidden" value="<?php echo ($resume_info["intention_jobs_id"]); ?>" />
										</div>
									</td>
								</tr>
								<tr>
									<td class="w1"><span>&nbsp;</span>工作地区：</td>
									<td>
										<div class="input_205_34_div1 J_hoverinput" data-toggle="funCityModal" data-title="请选择工作地区" data-multiple="true" data-maximum="3" data-width="760">
			                <span title="" class="for_district result J_resuletitle_city"><?php echo (($resume_info["district_cn"] != "")?($resume_info["district_cn"]):'请选择'); ?></span>
			                <input class="J_resultcode_city" name="district" id="district" type="hidden" value="<?php echo ($resume_info["district"]); ?>" keep="<?php echo ($resume_info["district"]); ?>" />
			              </div>
									</td>
									<td class="w1"><span>&nbsp;</span>期望薪资：</td>
									<td>
										<div class="input_205_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="J_listitme_text for_wage"><?php echo (($resume_info["wage_cn"] != "")?($resume_info["wage_cn"]):'请选择'); ?></span>
											<div class="dropdowbox10 J_dropdown_menu">
									            <div class="dropdow_inner10">
									                <ul class="nav_box">
									                	<?php if(is_array($category['QS_wage'])): $i = 0; $__LIST__ = $category['QS_wage'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$wage): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>" title="<?php echo ($wage); ?>"><?php echo ($wage); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
									                </ul>
									            </div>
									        </div>
									        <input class="J_listitme_code" name="wage" id="wage" type="hidden" value="<?php echo ($resume_info["wage"]); ?>">
										</div>
									</td>
								</tr>
							</table>
							<div class="butbox">
						   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_intention" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--自我描述 -->
				<div id="positionSpecialty" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">自我描述</div>
						<div class="ir"><div class="but_gray_70_res_edit J_hoverbut J_showitems">修改</div></div>
						<div class="clear"></div>
						<?php if(empty($resume_info['specialty'])): ?><div class="empty font_gray9 specialty_box">自我描述是你客观认识自己的基石，快来填写吧！</div>
						<?php else: ?>
							<div class="describe font_gray6 specialty_box"><?php echo ($resume_info["specialty"]); ?></div><?php endif; ?>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="specialty">
						<div class="ftit">自我描述<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<tr>
									<td colspan="2" ><div class="toptip">客观全面的自我评价更容易获得企业的好感，快来完善以获得HR的亲睐！</div></td>
								</tr>
								<tr>
									<td class="w1">自我描述：</td>
									<td><textarea name="specialty" id="specialty" class="textarea_638_80" placeholder="最多可输入2000字"><?php echo ($resume_info["specialty"]); ?></textarea></td>
								</tr>	  
							</table>
							<div class="butbox">
						   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_specialty" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--自我描述结束 -->
				<!--教育经历 -->
				<div id="positionEducation" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">教育经历</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems" id="J_clearinput_edu">添加</div></div>
						<div class="clear"></div>
						<div id="educationListBox"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="education">
						<div class="ftit">教育经历<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
						<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<input type="hidden" class="J_listid" value="" />
								  <tr>
									<td class="w1"><span>&nbsp;</span>学校名称：</td>
									<td><input name="school" id="school" type="text"    class="input_245_34"/></td>
								  </tr>
								  <tr>
									<td class="w1"><span>&nbsp;</span>专业名称：</td>
									<td><input name="speciality" id="speciality" type="text"    class="input_245_34"/></td>
								  </tr>
								  	 <tr>
									<td class="w1"><span>&nbsp;</span>学历：</td>
									<td>
										<div name="education" class="input_245_34_div J_hoverinput J_dropdown J_listitme_parent">
											<span class="J_listitme_text for_education1">请选择</span>
											<div class="dropdowbox4 J_dropdown_menu">
									            <div class="dropdow_inner4">
									                <ul class="nav_box">
									                	<?php if(is_array($category['QS_education'])): $i = 0; $__LIST__ = $category['QS_education'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$education): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>"><?php echo ($education); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
									                </ul>
									            </div>
									        </div>
											<input class="J_listitme_code" name="education1" id="education1" type="hidden" value="" />
										</div>
									</td>
								  </tr>
								   <tr>
									<td class="w1"><span>&nbsp;</span>就读时间：</td>
									<td>
										  <table border="0" cellpadding="0" cellspacing="0">
											<tr>
											  <td width="100" style="padding-left:0px;">
											  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
											  		<span class="J_listitme_text">年份</span>
											  		<div class="dropdowbox9 J_dropdown_menu">
											            <div class="dropdow_inner9">
											                <ul class="nav_box J_birthdy_exp">
											                	<div class="J_birthday_box_exp active"></div>
											                	<div class="J_birthday_box_exp"></div>
											                	<div class="J_birthday_box_exp"></div>
											                </ul>
										                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
										                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
											            </div>
											        </div>
											  		<input class="J_listitme_code" type="hidden" name="startyearEdu" id="startyearEdu" value="">
											  	</div>
											  </td>
											  <td width="100">
											  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
											  		<span class="J_listitme_text">月份</span>
											  		<div class="dropdowbox11 J_dropdown_menu">
											            <div class="dropdow_inner11">
											                <ul class="nav_box J_month">
											                	<div class="J_month_box active"></div>
											                </ul>
											            </div>
											        </div>
											  		<input class="J_listitme_code" type="hidden" name="startmonthEdu" id="startmonthEdu" value="">
											  	</div>
											  </td>
											  <td class="J_fortonow" width="30">至</td>
											  <td class="J_fortonow" width="100">
											  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
											  		<span class="J_listitme_text">年份</span>
											  		<div class="dropdowbox9 J_dropdown_menu">
											            <div class="dropdow_inner9">
											                <ul class="nav_box J_birthdy_exp">
											                	<div class="J_birthday_box_exp active"></div>
											                	<div class="J_birthday_box_exp"></div>
											                	<div class="J_birthday_box_exp"></div>
											                </ul>
										                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
										                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
											            </div>
											        </div>
											  		<input class="J_listitme_code" type="hidden" name="endyearEdu" id="endyearEdu" value="">
											  	</div>
											  </td>
											  <td class="J_fortonow" width="100">
											  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
											  		<span class="J_listitme_text">月份</span>
											  		<div class="dropdowbox11 J_dropdown_menu">
											            <div class="dropdow_inner11">
											                <ul class="nav_box J_month">
											                	<div class="J_month_box active"></div>
											                </ul>
											            </div>
											        </div>
											  		<input class="J_listitme_code" type="hidden" name="endmonthEdu" id="endmonthEdu" value="">
											  	</div>
											  </td>
											  <td width="100"><label><input class="J_tonow" name="tonowEdu" id="tonowEdu" type="checkbox" value="" />&nbsp;至今</label>
									          </td>
											</tr>
							  		  </table>
								     </td>
								  </tr>
						  </table>
							 <div class="butbox">
							   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_education" value="保存" /></div>
									<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
									<div class="clear"></div>
							 </div>
						</div>
					</div>
				</div>
				<!--教育经历结束 -->
				<!--工作经历 -->
				<div id="positionExperience" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">工作经历</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems" id="J_clearinput_exp">添加</div></div>
						<div class="clear"></div>
						<div id="experienceListBox"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="experience">
					  <div class="ftit">工作经历<div class="close J_hoverbut J_closeitems"></div></div>
					  <div class="fbody J_focus">
					  <table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
					  	<input type="hidden" class="J_listid" value="" />
						  <tr>
							<td class="w1"><span>&nbsp;</span>公司名称：</td>
							<td><input name="companyname" id="companyname" type="text"    class="input_245_34"/></td>
						  </tr>
						  <tr>
							<td class="w1"><span>&nbsp;</span>职位名称：</td>
							<td><input name="jobs" id="experienceJobname" type="text"    class="input_245_34"/></td>
						  </tr>
						   <tr>
							<td class="w1"><span>&nbsp;</span>任职时间：</td>
							<td>
							  <table border="0" cellpadding="0" cellspacing="0">
								<tr>
								  <td width="100" style="padding-left:0px;">
								  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
								  		<span class="J_listitme_text">年份</span>
								  		<div class="dropdowbox9 J_dropdown_menu">
								            <div class="dropdow_inner9">
								                <ul class="nav_box J_birthdy_exp">
								                	<div class="J_birthday_box_exp active"></div>
								                	<div class="J_birthday_box_exp"></div>
								                	<div class="J_birthday_box_exp"></div>
								                </ul>
							                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
							                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
								            </div>
								        </div>
								  		<input class="J_listitme_code" type="hidden" name="startyearExp" id="startyearExp" value="">
								  	</div>
								  </td>
								  <td width="100">
								  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
								  		<span class="J_listitme_text">月份</span>
								  		<div class="dropdowbox11 J_dropdown_menu">
								            <div class="dropdow_inner11">
								                <ul class="nav_box J_month">
								                	<div class="J_month_box active"></div>
								                </ul>
								            </div>
								        </div>
								  		<input class="J_listitme_code" type="hidden" name="startmonthExp" id="startmonthExp" value="">
								  	</div>
								  </td>
								  <td class="J_fortonow" width="30">至</td>
								  <td class="J_fortonow" width="100">
								  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
								  		<span class="J_listitme_text">年份</span>
								  		<div class="dropdowbox9 J_dropdown_menu">
								            <div class="dropdow_inner9">
								                <ul class="nav_box J_birthdy_exp">
								                	<div class="J_birthday_box_exp active"></div>
								                	<div class="J_birthday_box_exp"></div>
								                	<div class="J_birthday_box_exp"></div>
								                </ul>
							                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
							                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
								            </div>
								        </div>
								  		<input class="J_listitme_code" type="hidden" name="endyearExp" id="endyearExp" value="">
								  	</div>
								  </td>
								  <td class="J_fortonow" width="100">
								  	<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
								  		<span class="J_listitme_text">月份</span>
								  		<div class="dropdowbox11 J_dropdown_menu">
								            <div class="dropdow_inner11">
								                <ul class="nav_box J_month">
								                	<div class="J_month_box active"></div>
								                </ul>
								            </div>
								        </div>
								  		<input class="J_listitme_code" type="hidden" name="endmonthExp" id="endmonthExp" value="">
								  	</div>
								  </td>
								  <td width="100"><label><input class="J_tonow" name="tonowExp" id="tonowExp" type="checkbox" value="" />&nbsp;至今</label>
								  </td>
								</tr>
							  </table>
							</td>	  
						<tr>
							<td class="w1"><span>&nbsp;</span>工作职责：</td>
							<td><textarea name="jobrespons" id="jobrespons" class="textarea_438_34"></textarea></td>
					    </tr>  
						</table>
						<div class="butbox">
							<div class="td1"><input type="button" class="but_blue_115 J_hoverbut J_saveitems_experience" value="保存" /></div>
							<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
							<div class="clear"></div>
						</div>
					  </div>
					</div>
				</div>
				<!--工作经历结束 -->
				<!--培训经历 -->
				<div id="positionTrain" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">培训经历</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems" id="J_clearinput_tra">添加</div></div>
						<div class="clear"></div>
						<div id="trainListBox"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="train">
						<div class="ftit">培训经历<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<input type="hidden" class="J_listid" value="" />
								  <tr>
									<td class="w1"><span>&nbsp;</span>培训机构：</td>
									<td><input name="agency" id="agency" type="text"    class="input_245_34"/></td>
								  </tr>
								  <tr>
									<td class="w1"><span>&nbsp;</span>培训课程：</td>
									<td><input name="course" id="course" type="text"    class="input_245_34"/></td>
								  </tr>
								   <tr>
									<td class="w1"><span>&nbsp;</span>培训时间：</td>
									<td>
										<table border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td width="100" style="padding-left:0px;">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">年份</span>
														<div class="dropdowbox9 J_dropdown_menu">
												            <div class="dropdow_inner9">
												                <ul class="nav_box J_birthdy_exp">
												                	<div class="J_birthday_box_exp active"></div>
												                	<div class="J_birthday_box_exp"></div>
												                	<div class="J_birthday_box_exp"></div>
												                </ul>
											                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
											                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="startyearTra" id="startyearTra" value="">
													</div>
												</td>
												<td width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">月份</span>
														<div class="dropdowbox11 J_dropdown_menu">
												            <div class="dropdow_inner11">
												                <ul class="nav_box J_month">
												                	<div class="J_month_box active"></div>
												                </ul>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="startmonthTra" id="startmonthTra" value="">
													</div>
												</td>
												<td class="J_fortonow" width="30">至</td>
												<td class="J_fortonow" width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">年份</span>
														<div class="dropdowbox9 J_dropdown_menu">
												            <div class="dropdow_inner9">
												                <ul class="nav_box J_birthdy_exp">
												                	<div class="J_birthday_box_exp active"></div>
												                	<div class="J_birthday_box_exp"></div>
												                	<div class="J_birthday_box_exp"></div>
												                </ul>
											                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
											                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="endyearTra" id="endyearTra" value="">
													</div>
												</td>
												<td class="J_fortonow" width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">月份</span>
														<div class="dropdowbox11 J_dropdown_menu">
												            <div class="dropdow_inner11">
												                <ul class="nav_box J_month">
												                	<div class="J_month_box active"></div>
												                </ul>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="endmonthTra" id="endmonthTra" value="">
													</div>
												</td>
												<td width="100"><label><input class="J_tonow" name="tonowTra" id="tonowTra" type="checkbox" value="" />&nbsp;至今</label>
												</td>
											</tr>
										</table>
									</td>	  
							  <tr>
									<td class="w1"><span>&nbsp;</span>培训内容：</td>
									<td><textarea name="description" id="description" class="textarea_438_34"></textarea></td>
							  </tr>
							</table>
							<div class="butbox">
						   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_train" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--培训经历结束 -->
				<!--项目经历 -->
				<div id="positionTrain" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">项目经历</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems" id="J_clearinput_pro">添加</div></div>
						<div class="clear"></div>
						<div id="projectListBox"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="project">
						<div class="ftit">项目经历<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<input type="hidden" class="J_listid" value="" />
								  <tr>
									<td class="w1"><span>&nbsp;</span>项目名称：</td>
									<td><input name="projectname" id="projectname" type="text"    class="input_245_34"/></td>
								  </tr>
								  <tr>
									<td class="w1"><span>&nbsp;</span>担任角色：</td>
									<td><input name="role" id="role" type="text"    class="input_245_34"/></td>
								  </tr>
								   <tr>
									<td class="w1"><span>&nbsp;</span>项目时间：</td>
									<td>
										<table border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td width="100" style="padding-left:0px;">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">年份</span>
														<div class="dropdowbox9 J_dropdown_menu">
												            <div class="dropdow_inner9">
												                <ul class="nav_box J_birthdy_exp">
												                	<div class="J_birthday_box_exp active"></div>
												                	<div class="J_birthday_box_exp"></div>
												                	<div class="J_birthday_box_exp"></div>
												                </ul>
											                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
											                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="startyearPro" id="startyearPro" value="">
													</div>
												</td>
												<td width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">月份</span>
														<div class="dropdowbox11 J_dropdown_menu">
												            <div class="dropdow_inner11">
												                <ul class="nav_box J_month">
												                	<div class="J_month_box active"></div>
												                </ul>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="startmonthPro" id="startmonthPro" value="">
													</div>
												</td>
												<td class="J_fortonow" width="30">至</td>
												<td class="J_fortonow" width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">年份</span>
														<div class="dropdowbox9 J_dropdown_menu">
												            <div class="dropdow_inner9">
												                <ul class="nav_box J_birthdy_exp">
												                	<div class="J_birthday_box_exp active"></div>
												                	<div class="J_birthday_box_exp"></div>
												                	<div class="J_birthday_box_exp"></div>
												                </ul>
											                	<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
											                	<a href="javascript:;" class="next J_birthday_next_exp"></a>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="endyearTra" id="endyearPro" value="">
													</div>
												</td>
												<td class="J_fortonow" width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">月份</span>
														<div class="dropdowbox11 J_dropdown_menu">
												            <div class="dropdow_inner11">
												                <ul class="nav_box J_month">
												                	<div class="J_month_box active"></div>
												                </ul>
												            </div>
												        </div>
														<input class="J_listitme_code" type="hidden" name="endmonthTra" id="endmonthPro" value="">
													</div>
												</td>
												<td width="100"><label><input class="J_tonow" name="tonowPro" id="tonowPro" type="checkbox" value="" />&nbsp;至今</label>
												</td>
											</tr>
										</table>
									</td>	  
							  <tr>
									<td class="w1"><span>&nbsp;</span>项目描述：</td>
									<td><textarea name="descriptionpro" id="descriptionpro" class="textarea_438_34"></textarea></td>
							  </tr>
							</table>
							<div class="butbox">
						   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_project" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--培训经历结束 -->
				<!--获得证书 -->
				<div id="positionCredent" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">获得证书</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems" id="J_clearinput_cre">添加</div></div>
						<div class="clear"></div>
						<div id="credentListBox"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="credent">
						<div class="ftit">获得证书<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<input type="hidden" class="J_listid" value="" />
								<tr>
									<td colspan="2"><div class="toptip">添加你获得的专业技能、职业证书或职称；最多可以添加6份证书</div></td>
								</tr>
								<tr>
									<td class="w1"><span>&nbsp;</span>证书名称：</td>
									<td><input name="credent" id="credent" type="text"    class="input_205_34"/></td>
								</tr>
								<tr>
									<td class="w1">
										<span>&nbsp;</span>获得时间：
									</td>
									<td>
										<table border="0" cellpadding="0" cellspacing="0">
											<input type="hidden" class="J_listid" value="" />
											<tr>
												<td width="110" style="padding-left:0px;">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">年份</span>
														<div class="dropdowbox9 J_dropdown_menu">
															<div class="dropdow_inner9">
																<ul class="nav_box J_birthdy_exp">
																	<div class="J_birthday_box_exp active"></div>
																	<div class="J_birthday_box_exp"></div>
																	<div class="J_birthday_box_exp"></div>
																</ul>
																<a href="javascript:;" class="prev J_birthday_prev_exp"></a>
																<a href="javascript:;" class="next J_birthday_next_exp"></a>
															</div>
														</div>
														<input class="J_listitme_code" type="hidden" name="yearCredent" id="yearCredent" value="">
													</div>
												</td>
												<td width="100">
													<div class="input_90_34_div J_hoverinput J_dropdown J_listitme_parent">
														<span class="J_listitme_text">月份</span>
														<div class="dropdowbox11 J_dropdown_menu">
															<div class="dropdow_inner11">
																<ul class="nav_box J_month">
																	<div class="J_month_box active"></div>
																</ul>
															</div>
														</div>
														<input class="J_listitme_code" type="hidden" name="monthCredent" id="monthCredent" value="">
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<div class="butbox">
								<div class="td1">
									<input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_credent" value="保存" />
								</div>
								<div class="td1">
									<input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--获得证书结束 -->
				<!--语言能力 -->
				<div id="positionLanguage" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">语言能力</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems_edit" id="JLanguageStatus">添加</div></div>
						<div class="clear"></div>
						<div id="languageListBox"></div>
				    </div>
					<div class="itemsform J_itemsmenu" data-waitsome="language">
						<div class="ftit">语言能力 <div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus" id="ajaxEditLanguageListBox"></div>
					</div>
					<input type="hidden" id="J-for-last-del" value="0">
				</div>
				<!--语言能力结束 -->
				<!--特长标签 -->
				<div id="positionTag" class="J_itemsbox">
					<div class="items J_hoverbut">
						<div class="il">特长标签</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems"><?php if($resume_info['tag']): ?>修改<?php else: ?>添加<?php endif; ?></div></div>
						<div class="clear"></div>
						<div id="tagListBox">
							<?php if(empty($resume_info['tag_cn'])): ?><div class="empty font_gray9">快来秀出你的亮点！不要被别人比下去啦！</div>
							<?php else: ?>
								<?php if(is_array($resume_info['tag_cn'])): $i = 0; $__LIST__ = $resume_info['tag_cn'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><div class="label_speciality <?php if(in_array($tag,$category['QS_resumetag'])): ?>sys<?php endif; ?>"><?php echo ($tag); ?></div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
						</div>
						<input name="tag" type="hidden" id="tag" value="<?php echo ($resume_info["tag"]); ?>" />
						<div class="clear"></div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="tag">
						<div class="ftit">特长标签<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table" border="0" align="center" cellpadding="0" cellspacing="0" >
								<tr>
									<td colspan="2" ><div class="toptip">是金子总会发光的，快主动秀出你的闪光点！</div></td>
								</tr>
								<tr>
									<td valign="top" class="w1">特长标签：</td>
									<td id="ajaxtagListBox">
										<?php if(is_array($category['QS_resumetag'])): $i = 0; $__LIST__ = $category['QS_resumetag'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><div class="sp_label J_hoverbut nowrap J_taglist <?php if(in_array($tag,$resume_info['tag_cn'])): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>" data-title="<?php echo ($tag); ?>" data-type="1">
												<?php echo ($tag); ?>
												<div class="choose"></div>
											</div><?php endforeach; endif; else: echo "" ;endif; ?>
										<?php if(is_array($resume_info['tag_cn'])): $i = 0; $__LIST__ = $resume_info['tag_cn'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i; if(!in_array($tag,$category['QS_resumetag'])): ?><div class="sp_label J_hoverbut nowrap J_taglist select" data-title="<?php echo ($tag); ?>" data-type="0">
													<?php echo ($tag); ?>
													<div class="choose"></div>
												</div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
								  	</td>
									<div class="clear"></div>
								</tr>
							 
								<tr>
									<td   ></td>
									<td>
									  <div class="addtag">
								  			<div class="tagleft"><input name="selftag" id="" type="text"    class="input_205_34" placeholder="输入特长，不超过8个汉字"/></div>
											<div class="tagright">
											  <div class="but_yellow_115 J_hoverbut" id="J_add_selftag">添加特长</div>
											</div>
											<div class="clear"></div>
									  </div>
								  </td>
								</tr>	 
						  </table>
							<div class="sp_label_butbox">
						   		<div class="td1"><input type="submit"  class="but_blue_115 J_hoverbut J_saveitems_tag" value="保存" /></div>
								<div class="td1"><input type="reset"  class="but_gray_115 J_hoverbut J_closeitems" value="取消" /></div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!--特长标签结束 -->
				<!--附件上传 -->
				<div id="positionResumeimg" class="J_itemsbox">
				  <div class="items J_hoverbut">
						<div class="il">照片/作品</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_showitems_saveimg J_scan_img">上传</div></div>
						<div class="clear"></div>
						<div class="photo link_blue">
							<div id="J_resumeimg_box">
								<?php if(empty($resume_img)): ?><div class="empty font_gray9">最多上传6张，每张最大800KB,支持jpg/gif/bmp/png格式，建议上传清晰自然生活照，或者您的专业代表作品。</div>
								<?php else: ?>
									<?php if(is_array($resume_img)): $i = 0; $__LIST__ = $resume_img;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$resume_info_img): $mod = ($i % 2 );++$i;?><div class="plist J_resumeimg_group" data-code="<?php echo ($resume_info_img["id"]); ?>"  data-img="<?php echo ($resume_info_img["img"]); ?>">
											<div class="bg J_hoverbut">
												<div class="pic"><img src="<?php echo attach($resume_info_img['img'],'resume_img');?>" alt="" height="100%" width="100%" /></div>
												<?php switch($resume_info_img['audit']): case "1": ?><div class="audit font_green">审核通过</div><?php break;?>
													<?php case "2": ?><div class="audit font_yellow">等待审核</div><?php break;?>
													<?php case "3": ?><div class="audit font_red">审核未通过</div><?php break; endswitch;?>
												<div class="edit J_resumeimg_edit"></div>
												<div class="picdel J_resumeimg_del"><a href="javascript:;">删除</a></div>
												<div class="clear"></div>
											</div>
										</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="resumeimg">
						<div class="ftit">照片/作品<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus new-up">
			                <div class="new-up-cell">
			                    <div class="img-box">
									<img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url(array('c'=>'ScanUpload','a'=>'resume_img','params'=>'uname='.$visitor['username'].'&pwd='.$visitor['password'].'&utype='.$visitor['utype'].'&rid='.$resume_info['id'])));?>" />
								</div>
			                    <div class="sp-line-16"></div>
			                    <div class="l-txt co-333 ft-16">方式一：手机扫码上传</div>
			                    <div class="sp-line-13"></div>
			                    <div class="l-txt co-b9 ft-14">推荐使用手机扫码上传图片，更方便</div>
			                </div>
			                <div class="new-up-cell last">
			                    <div class="img-box local-up J_hoverbut" id="resume_img" name="resume_img">
			                        <div class="i-ic"></div>
			                        <div class="sp-line-19"></div>
			                        <div class="l-txt ft-16">点击上传</div>
			                    </div>
			                    <div class="sp-line-16"></div>
			                    <div class="l-txt co-333 ft-16">方式二：本地上传</div>
			                    <div class="sp-line-13"></div>
			                    <div class="l-txt co-b9 ft-14">支持jpg/gif/bmp/png格式（文件大小800K以内）</div>
			                </div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<!--附件上传结束 -->
				<!--word简历 -->
				<div id="positionResumeword" class="J_itemsbox">
					<div class="items J_hoverbut" >
						<div class="il">附件简历</div>
						<div class="ir"><div class="but_gray_70_res_add J_hoverbut J_show_itemsform" id="J_has_resume_word_sub">上传</div></div>
						<div class="clear"></div>
						<div class="doc" id="J_resumeword_box">
							<?php if(empty($resume_info['word_resume'])): ?><div class="empty font_gray9">请上传doc/docx/pdf格式的附件(文件大小2M以内)</div>
							<?php else: ?>
								<div class="dleft_tit link_gray6 substring"><?php echo ($resume_info["word_resume_title"]); ?></div>
								<div class="dtime">上传于：<?php echo date('Y-m-d H:i',$resume_info['word_resume_addtime']);?></div>
								<div class="ddown link_yellow">
									<a href="javascript:;" class="J_resumeword_update">更换</a>&nbsp;&nbsp;&nbsp;
									<a href="<?php echo U('download/word_resume',array('id'=>$resume_info['id']));?>">下载</a>&nbsp;&nbsp;&nbsp;
									<a href="javascript:;" class="J_resumeword_del">删除</a>
								</div>
								<div class="clear"></div><?php endif; ?>
						</div>
					</div>
					<div class="itemsform J_itemsmenu" data-waitsome="resumeword">
						<div class="ftit">附件简历<div class="close J_hoverbut J_closeitems"></div></div>
						<div class="fbody J_focus">
							<table class="b_table b_resume_word" border="0" align="center" cellpadding="0" cellspacing="0" >
								<tr>
									<td colspan="2" ><div class="toptip">请上传doc/docx/pdf格式的附件(文件大小2M以内)</div></td>
								</tr>
								<tr>
									<td valign="top" class="w1">上传文件：</td>
									<td>
										<a href="javascript:;" name="word_resume" id="word_resume" class="upload J_hoverbut J_upload_wordarea">点击上传文件</a>
									</td>
								</tr>	  
							</table>
						</div>
					</div>
				</div>
                <!--微信提示-->
                <?php if($weixin_img): ?><div class="per-index-wx">
                        <div class="wx-img"><?php echo ($weixin_img); ?></div>
                        <div class="wx-txt">
                            <div class="wxl font_yellow">微信扫码关注【<?php echo C('qscms_site_name');?>】公众号</div>
                            <div class="wxl">第一时间接收企业邀请面试通知，不再错过每个工作机会！</div>
                        </div>
                        <div class="clear"></div>
                    </div><?php endif; ?>
                <!--微信提示-->
				<div class="btnbox">
					<form id="J_auto_apply_form" method="post">
			            <div class="btn_refresh link_blue">
							<label>
								<?php if($auto_refresh): ?>您的简历自动刷新期限：<?php echo date('Y-m-d',$start);?> 至 <?php echo date('Y-m-d',$auto_refresh['refreshtime']);?>&nbsp;&nbsp;[<a class="J_refresh_resume" pid="<?php echo ($auto_refresh["pid"]); ?>" href="javascript:;">取消自动刷新</a>]
									<?php else: ?>
									<input name="auto_refresh" type="checkbox" value="1" checked="checked" />&nbsp;三天内帮我自动刷新简历<?php endif; ?>
							</label>
						</div>
						<div class="btn_refresh link_blue">
							<label>
								<?php if($auto_apply): ?>您的简历委托投递期限：<?php echo date('Y-m-d',$auto_apply['entrust_start']);?> 至 <?php echo date('Y-m-d',$auto_apply['entrust_end']);?>&nbsp;&nbsp;[<a class="J_apply_resume" resume_id="<?php echo ($auto_apply["resume_id"]); ?>" href="javascript:;">取消委托投递</a>]
									<?php else: ?>
									<input name="auto_apply" type="checkbox" value="1" <?php if(($resume["_audit"]) == "1"): ?>checked="checked"<?php endif; ?>/>&nbsp;三天内有好工作帮我投递<?php endif; ?>
							</label>
						</div>
						<input type="hidden" name="pid" value="<?php echo ($resume_info["id"]); ?>">
					    <div class="btn_save"><input type="button" class="btn J_hoverbut" id="J_auto_apply" value="保存简历" /></div>
				    </form>
				</div>
				<!--word简历结束 -->	 
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
	<script type="text/javascript" src="../public/js/jquery.modal.userselectlayer.js"></script>
	<script type="text/javascript" src="../public/js/jquery.user.city.js"></script>
	<script src="../public/js/ajaxfileupload.js"></script>
	<script src="../public/js/qscms.js"></script>
	<script type="text/javascript" src="../public/js/jquery.modal.dialog.js"></script>
	<?php if($weixin_focus): ?><script id="weixin_focus" type="html/javascript">
			<div class="dia_wx">
				<div class="dia_tit">公众号绑定</div><div class="dia_close"></div><div class="dia_never" id="J_never_tip">不再提示</div>
				<div class="dia_t1">扫码关注微信公众号</div><div class="dia_t2">及时接收简历投递通知 优秀人才一手掌握</div>
				<div class="dia_qr"><?php echo ($weixin_img); ?></div>
				<div class="dia_t3">手机微信扫一扫</div>
			</div>
		</script>
		<script type="text/javascript">
	        var qsDialog = $(this).dialog({
	            loading: false,
	            header: false,
	            showFooter: false,
	            border: false,
	            innerPadding: false,
	            content:$('#weixin_focus').html()
	        });
	        var tipSw = true;
	        $('#J_never_tip').live('click', function() {
	        	if (!tipSw) { return false; }
	        	$(this).toggleClass('check');
				tipSw = false;
				$.getJSON("<?php echo U('members/sign_wx');?>",function(result){
					if(result.status == 1){
						$('.modal_backdrop').remove();
						$('.modal').remove();
					}else{
						disapperTooltip('remind',result.msg);
					}
					tipSw = true;
				});
	        });
	        $('.dia_close').live('click', function() {
	        	$('.modal_backdrop').remove();
				$('.modal').remove();
	        });
	    </script><?php endif; ?>
	<script type="text/javascript">
		$('.get-money-fail-suc').css({
	        left: ($(window).width() - $('.get-money-fail-suc').outerWidth())/2,
	        top: ($(window).height() - $('.get-money-fail-suc').outerHeight())/2 + $(document).scrollTop()
	    });
	    $('.gm-fs-group .gm-fs-clo').die().live('click', function () {
	        $(this).closest('.get-money-fail-suc').remove();
	        $('.modal_backdrop').remove();
	    });
	    // 认证手机
        $('#J_auth_mobile').click(function(){
            var title = '修改手机';
            var qsDialog = $(this).dialog({
                title: title,
                loading: true,
                showFooter: false,
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
                            qsDialog.hide();
                        }else{
                            disapperTooltip('remind',result.msg);
                        }
                    },'json');
                }
            });
            $.getJSON("<?php echo U('Members/user_mobile');?>",function(result){
                if(result.status == 1){
                    qsDialog.setCloseDialog(false);
                    qsDialog.setContent(result.data);
                    qsDialog.showFooter(true);
                }else{
                    qsDialog.setContent('<div class="confirm">' + result.msg + '</div>');
                }
            });
        });
	    // 头像不公开
        $('#photo_display').click(function () {
            var pDisplay = 1;
            if ($(this).is(":checked")) {
                pDisplay = 2;
            }
            $.post("<?php echo U('personal/save_photo_display');?>",{photo_display:pDisplay},function(result){
                if(result.status==1){
                    disapperTooltip("success", result.msg);
                }
            },'json');
        });
	    // 简历显示关闭
        $('.J_privacy_btn').click(function () {
            var objThis = $(this);
            var pid = $(this).attr('pid');
            if ($(this).hasClass('close')) {
                var qsDialog = $(this).dialog({
                    title: '提示',
                    content: '<div style="width:340px;">简历显示后将在列表页显示以及被搜索，确定显示吗？</div>',
                    btns: ['确定', '取消'],
                    yes: function() {
                        $.post("<?php echo U('save_resume_privacy');?>",{pid:pid,display:1},function(result){
                            if(result.status==1){
                                objThis.removeClass('close');
                                disapperTooltip("success", result.msg);
                            }
                        },'json');
                    }
                });
            } else {
                var qsDialog = $(this).dialog({
                    title: '提示',
                    content: '<div style="width:340px;">简历关闭后将不能显示以及被搜索，确定关闭吗？</div>',
                    btns: ['确定', '取消'],
                    yes: function() {
                        $.post("<?php echo U('save_resume_privacy');?>",{pid:pid,display:0},function(result){
                            if(result.status==1){
                                objThis.addClass('close');
                                disapperTooltip("success", result.msg);
                            }
                        },'json');
                    }
                });
            }
        });
	    // 更换模板
        $('.J_tpl_btn').click(function(){
            var pid = $(this).attr('pid');
            var qsDialog = $(this).dialog({
                title: '更换模板',
                loading: true,
                showFooter: false,
                btns: ['立即使用', '取消'],
                yes: function() {
                    var tpl = $('#J_tplVal').val();
                    $.post("<?php echo U('Personal/set_tpl');?>",{pid:pid,tpl:tpl},function(result){
                        if(result.status == 1){
                            disapperTooltip('success',result.msg);
                        }else{
                            disapperTooltip('remind',result.msg);
                        }
                    },'json');
                }
            });
            $.getJSON("<?php echo U('Personal/resume_tpl');?>",{pid:pid},function(result){
                if(result.status == 1){
                    qsDialog.setContent(result.data.html);
                    qsDialog.showFooter(true);
                }else{
                    qsDialog.setContent('<div class="confirm">' + result.msg + '</div>');
                }
            });
        });
	    // 取消自动刷新
	    $('.J_refresh_resume').click(function(){
			if (confirm('你确定要取消自动刷新简历吗？')) {
				var f = $(this),
						pid = $(this).attr('pid');
				$.post("<?php echo U('Personal/del_refresh_resume');?>", {pid: pid}, function (result) {
					if (result.status == 1) {

						window.location.reload();
					}
				}, 'json');
			}
	    });
		// 取消自动投递
		$('.J_apply_resume').click(function(){
			if (confirm('你确定要取消委托投递吗？')) {
				var f = $(this),
						resume_id = $(this).attr('resume_id');
				$.post("<?php echo U('Personal/del_apply_resume');?>",{resume_id:resume_id},function(result){
					if(result.status == 1){
						window.location.reload();
					}
				},'json');
			}
	    });
	    // 添加通用
		$('.J_showitems').live('click', function(e) {
			$('.J_itemsmenu').hide();
			$('.items').show();
			var $parentDom = $(this).closest('.J_itemsbox');
		    $(this).closest('.items').hide();
		    $parentDom.find('.J_itemsmenu').show();
		});

		// 取消通用
		$('.J_closeitems').live('click', function(e) {
		    var $parentDom = $(this).closest('.J_itemsbox');
		    $(this).closest('.J_itemsmenu').hide();
		    $parentDom.find('.items').show();
		});

		var regularMobile = qscms.regularMobile; // 验证手机号正则
		var regularEmail = /^[_\.0-9a-zA-Z-]+[_0-9a-zA-Z-]@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$/; // 验证邮箱正则
		var regularHeight = /^1[2-9][0-9]$|^2[0-2][0-9]$|^230$/; // 验证身高正则
		var pid = $('#pid').val(); // 简历id

		// 加载教育经历
		$.getJSON("<?php echo U('Personal/ajax_get_education_list');?>", {pid: pid}, function(data) {
			$('#educationListBox').html(data.data.html);
			listHoveEdit();
			// 修改教育经历
			$('.J_editedu').live('click', function() {
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				$.getJSON("<?php echo U('Personal/edit_education');?>", {pid: pid, id: thisid}, function(data) {
					if (data.status) {
						$('#school').val(data.data.school);
						$('#speciality').val(data.data.speciality);
						$('#education1').val(data.data.education);
						$('#education1').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.education_cn);
						$('#startyearEdu').val(data.data.startyear);
						$('#startyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startyear);
						$('#startmonthEdu').val(data.data.startmonth);
						$('#startmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startmonth);
						$('#endyearEdu').val(data.data.endyear);
						$('#endyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endyear);
						$('#endmonthEdu').val(data.data.endmonth);
						$('#endmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endmonth);
						$('[data-waitsome="education"]').find('.J_listid').val(data.data.id);
						if (data.data.todate==1) {
							$('#tonowEdu').attr('checked', !0);
							$('#tonowEdu').val('1');
							$('#tonowEdu').closest('.J_itemsmenu').find('.J_fortonow').hide();
						}
						var $parentDom = $('[data-waitsome="education"]').closest('.J_itemsbox');
					    $('.J_itemsmenu').hide();
						$('.items').show();
					    $parentDom.find('.items').hide();
					    $parentDom.find('.J_itemsmenu').show();
					}
				});
			});
			// 删除教育经历
			$('.J_deledu').live('click', function() {
				var url = "<?php echo U('Personal/del_education');?>";
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				var qsDialog=$(this).dialog({
					title: '删除教育经历',
					loading: true,
					border: false,
					yes: function() {
						$.post(url,{pid:pid,id:thisid},function(data){
			        		if (data.status) {
								$('#educationListBox').find('[data-id="'+thisid+'"]').remove();
								if (!$('#educationListBox .J_listhover_edit').length) {
									$.getJSON("<?php echo U('Personal/ajax_get_education_list');?>", {pid: pid}, function(data) {
										$('#educationListBox').html(data.data.html);
									});
								};
							} else {
								disapperTooltip("remind", "删除失败");
							}
			        	},'json');
					}
				});
				$.getJSON(url,{pid:pid,id:thisid},function(result){
	        		if(result.status == 1){
	        			qsDialog.setContent(result.data.html);
	        		}else{
	                    qsDialog.hide();
	        			disapperTooltip('remind',result.msg);
	        		}
	        	});
			});
		});

		// 加载工作经历
		$.getJSON("<?php echo U('Personal/ajax_get_work_list');?>", {pid: pid}, function(data) {
			$('#experienceListBox').html(data.data.html);
			listHoveEdit();
			// 修改工作经历
			$('.J_editexp').live('click', function() {
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				$.getJSON("<?php echo U('Personal/edit_work');?>", {pid: pid, id: thisid}, function(data) {
					if (data.status) {
						$('#companyname').val(data.data.companyname);
						$('#experienceJobname').val(data.data.jobs);
						$('#startyearExp').val(data.data.startyear);
						$('#startyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startyear);
						$('#startmonthExp').val(data.data.startmonth);
						$('#startmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startmonth);
						$('#endyearExp').val(data.data.endyear);
						$('#endyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endyear);
						$('#endmonthExp').val(data.data.endmonth);
						$('#endmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endmonth);
						$('#jobrespons').val(data.data.achievements);
						$('[data-waitsome="experience"]').find('.J_listid').val(data.data.id);
						if (data.data.todate==1) {
							$('#tonowExp').attr('checked', !0);
							$('#tonowExp').val('1');
							$('#tonowExp').closest('.J_itemsmenu').find('.J_fortonow').hide();
						}
						var $parentDom = $('[data-waitsome="experience"]').closest('.J_itemsbox');
					    $('.J_itemsmenu').hide();
						$('.items').show();
					    $parentDom.find('.items').hide();
					    $parentDom.find('.J_itemsmenu').show();
					}
				});
			});
			// 删除工作经历
			$('.J_delexp').live('click', function() {
				var url = "<?php echo U('Personal/del_work');?>";
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				var qsDialog=$(this).dialog({
					title: '删除工作经历',
					loading: true,
					border: false,
					yes: function() {
						$.post(url,{pid:pid,id:thisid},function(data){
			        		if (data.status) {
								$('#experienceListBox').find('[data-id="'+thisid+'"]').remove();
								if (!$('#experienceListBox .J_listhover_edit').length) {
									$.getJSON("<?php echo U('Personal/ajax_get_work_list');?>", {pid: pid}, function(data) {
										$('#experienceListBox').html(data.data.html);
									});
								}
							} else {
								disapperTooltip("remind", "删除失败");
							}
			        	},'json');
					}
				});
				$.getJSON(url,{pid:pid,id:thisid},function(result){
	        		if(result.status == 1){
	        			qsDialog.setContent(result.data.html);
	        		}else{
	                    qsDialog.hide();
	        			disapperTooltip('remind',result.msg);
	        		}
	        	});
			});
		});

		// 加载培训经历
		$.getJSON("<?php echo U('Personal/ajax_get_training_list');?>", {pid: pid}, function(data) {
			$('#trainListBox').html(data.data.html);
			listHoveEdit();
			// 修改培训经历
			$('.J_edittra').live('click', function() {
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				$.getJSON("<?php echo U('Personal/edit_training');?>", {pid: pid, id: thisid}, function(data) {
					if (data.status) {
						$('#agency').val(data.data.agency);
						$('#course').val(data.data.course);
						$('#startyearTra').val(data.data.startyear);
						$('#startyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startyear);
						$('#startmonthTra').val(data.data.startmonth);
						$('#startmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startmonth);
						$('#endyearTra').val(data.data.endyear);
						$('#endyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endyear);
						$('#endmonthTra').val(data.data.endmonth);
						$('#endmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endmonth);
						$('#description').val(data.data.description);
						$('[data-waitsome="train"]').find('.J_listid').val(data.data.id);
						if (data.data.todate==1) {
							$('#tonowTra').attr('checked', !0);
							$('#tonowTra').val('1');
							$('#tonowTra').closest('.J_itemsmenu').find('.J_fortonow').hide();
						}
						var $parentDom = $('[data-waitsome="train"]').closest('.J_itemsbox');
					    $('.J_itemsmenu').hide();
						$('.items').show();
					    $parentDom.find('.items').hide();
					    $parentDom.find('.J_itemsmenu').show();
					}
				});
			});
			// 删除培训经历
			$('.J_deltra').live('click', function() {
				var url = "<?php echo U('Personal/del_training');?>";
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				var qsDialog=$(this).dialog({
					title: '删除培训经历',
					loading: true,
					border: false,
					yes: function() {
						$.post(url,{pid:pid,id:thisid},function(data){
			        		if (data.status) {
								$('#trainListBox').find('[data-id="'+thisid+'"]').remove();
								if (!$('#trainListBox .J_listhover_edit').length) {
									$.getJSON("<?php echo U('Personal/ajax_get_training_list');?>", {pid: pid}, function(data) {
										$('#trainListBox').html(data.data.html);
									});
								}
							} else {
								disapperTooltip("remind", "删除失败");
							}
			        	},'json');
					}
				});
				$.getJSON(url,{pid:pid,id:thisid},function(result){
	        		if(result.status == 1){
	        			qsDialog.setContent(result.data.html);
	        		}else{
	                    qsDialog.hide();
	        			disapperTooltip('remind',result.msg);
	        		}
	        	});
			});
		});
		// 加载项目经历
		$.getJSON("<?php echo U('Personal/ajax_get_project_list');?>", {pid: pid}, function(data) {
			$('#projectListBox').html(data.data.html);
			listHoveEdit();
			// 修改项目经历
			$('.J_editpro').live('click', function() {
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				$.getJSON("<?php echo U('Personal/edit_project');?>", {pid: pid, id: thisid}, function(data) {
					if (data.status) {
						$('#projectname').val(data.data.projectname);
						$('#role').val(data.data.role);
						$('#startyearPro').val(data.data.startyear);
						$('#startyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startyear);
						$('#startmonthPro').val(data.data.startmonth);
						$('#startmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.startmonth);
						$('#endyearPro').val(data.data.endyear);
						$('#endyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endyear);
						$('#endmonthPro').val(data.data.endmonth);
						$('#endmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.endmonth);
						$('#descriptionpro').val(data.data.description);
						$('[data-waitsome="project"]').find('.J_listid').val(data.data.id);
						if (data.data.todate==1) {
							$('#tonowPro').attr('checked', !0);
							$('#tonowPro').val('1');
							$('#tonowPro').closest('.J_itemsmenu').find('.J_fortonow').hide();
						}
						var $parentDom = $('[data-waitsome="project"]').closest('.J_itemsbox');
					    $('.J_itemsmenu').hide();
						$('.items').show();
					    $parentDom.find('.items').hide();
					    $parentDom.find('.J_itemsmenu').show();
					}
				});
			});
			// 删除项目经历
			$('.J_delpro').live('click', function() {
				var url = "<?php echo U('Personal/del_project');?>";
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				var qsDialog=$(this).dialog({
					title: '删除项目经历',
					loading: true,
					border: false,
					yes: function() {
						$.post(url,{pid:pid,id:thisid},function(data){
			        		if (data.status) {
								$('#projectListBox').find('[data-id="'+thisid+'"]').remove();
								if (!$('#projectListBox .J_listhover_edit').length) {
									$.getJSON("<?php echo U('Personal/ajax_get_project_list');?>", {pid: pid}, function(data) {
										$('#projectListBox').html(data.data.html);
									});
								}
							} else {
								disapperTooltip("remind", "删除失败");
							}
			        	},'json');
					}
				});
				$.getJSON(url,{pid:pid,id:thisid},function(result){
	        		if(result.status == 1){
	        			qsDialog.setContent(result.data.html);
	        		}else{
	                    qsDialog.hide();
	        			disapperTooltip('remind',result.msg);
	        		}
	        	});
			});
		});

		// 加载语言能力
		$.getJSON("<?php echo U('Personal/ajax_get_language_list');?>", {pid: pid}, function(data) {
			$('#languageListBox').html(data.data.html);
			listHoveEdit();
			if ($('#languageListBox div.label').length) {
				$('#JLanguageStatus').removeClass('but_gray_70_res_add').addClass('but_gray_70_res_edit').text('修改');
			}
		});

		// 加载获得证书
		$.getJSON("<?php echo U('Personal/ajax_get_credent_list');?>", {pid: pid}, function(data) {
			$('#credentListBox').html(data.data.html);
			listHoveEdit();
			// 修改获得证书
			$('.J_editcre').live('click', function() {
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				$.getJSON("<?php echo U('Personal/edit_credent');?>", {pid: pid, id: thisid}, function(data) {
					if (data.status) {
						$('#credent').val(data.data.name);
						$('#yearCredent').val(data.data.year);
						$('#yearCredent').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.year);
						$('#monthCredent').val(data.data.month);
						$('#monthCredent').closest('.J_listitme_parent').find('.J_listitme_text').text(data.data.month);
						$('[data-waitsome="credent"]').find('.J_listid').val(data.data.id);
						var $parentDom = $('[data-waitsome="credent"]').closest('.J_itemsbox');
					    $('.J_itemsmenu').hide();
						$('.items').show();
					    $parentDom.find('.items').hide();
					    $parentDom.find('.J_itemsmenu').show();
					}
				});
			});
			// 删除获得证书
			$('.J_delcre').live('click', function() {
				var url = "<?php echo U('Personal/del_credent');?>";
				var thisid = $(this).closest('.J_listhover_edit').data('id');
				var qsDialog=$(this).dialog({
					title: '删除获得证书',
					loading: true,
					border: false,
					yes: function() {
						$.post(url,{pid:pid,id:thisid},function(data){
			        		if (data.status) {
								$('#credentListBox').find('[data-id="'+thisid+'"]').remove();
								if (!$('#credentListBox .J_listhover_edit').length) {
									$.getJSON("<?php echo U('Personal/ajax_get_credent_list');?>", {pid: pid}, function(data) {
										$('#credentListBox').html(data.data.html);
									});
								}
							} else {
								disapperTooltip("remind", "删除失败");
							}
			        	},'json');
					}
				});
				$.getJSON(url,{pid:pid,id:thisid},function(result){
	        		if(result.status == 1){
	        			qsDialog.setContent(result.data.html);
	        		}else{
	                    qsDialog.hide();
	        			disapperTooltip('remind',result.msg);
	        		}
	        	});
			});
		});

		// 特长标签
		$('.J_taglist').die().live('click', function() {
			var selectArr = $('.J_taglist.select');
			if ($(this).hasClass('select')) {
				$(this).toggleClass('select');
			} else {
				if (selectArr.length >= 5) {
					disapperTooltip("remind", "最多可选5个标签！");
				} else {
					$(this).toggleClass('select');
				}
			}
		});

		// 判断是否有附件简历
		if ($('#J_resumeword_box .dleft_tit').length) {
			$('#J_has_resume_word_sub').text('修改');
		}

		//点击重命名简历标题，切换出文本框
		$("#J_edit_title_edit").click( function () {
			$("#J_edit_title_input_edit").show();
			$("#J_edit_title_txt_edit").hide();
		});

		$("#J_edit_title_edit").click( function () {
			$("#J_edit_title_input_edit").show();
			var oval = $("#J_edit_title_input_edit").find('input[name="title"]').val();
			$("#J_edit_title_input_edit").find('input[name="title"]').val('').focus().val(oval).addClass('input_focus');
			$("#J_edit_title_txt_edit").hide();
		});

		// 保存简历标题
		$('.J_save_basetitle').live('click', function(e) {
			// 提交之前先验证
			var titleValue = $.trim($('#J_titleinput').val());
			if (titleValue == "") {
				disapperTooltip("remind", "请填写简历标题");
				return false;
			}
			if (titleValue != "" && titleValue.length > 12) {
				disapperTooltip("remind", "简历标题应在1~12个字内");
				return false;
			}
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/ajax_save_title');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, title: titleValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					disapperTooltip("success", '保存成功！');
					$("#J_edit_title_input_edit").hide();
					$("#J_edit_title_txt_edit").find('span').text($('#J_titleinput').val());
					$("#J_edit_title_txt_edit").show();
				} else {
					disapperTooltip("remind", data.msg);
				}
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
			});
		});

		// 保存基本信息
		$('.J_saveitems_baseinfo').live('click', function(e) {
			// 提交之前先验证
			var fullnameValue = $.trim($('#fullname').val());
			var displaynameValue = $.trim($('#display_name').val());
			var sexValue = $.trim($('#sex').val());
			var birthdateValue = $.trim($('#birthdate').val());
			var residenceValue = $.trim($('#residence').val());
			var educationValue = $.trim($('#education').val());
			var experienceValue = $.trim($('#experience').val());
			var telephoneValue = $.trim($('#telephone').val());
			var emailValue = $.trim($('#email').val());
			var idcardValue = $.trim($('#idcard').val());
			var majorValue = $.trim($('#major').val());
			var heightValue = $.trim($('#height').val());
			var householdaddressValue = $.trim($('#householdaddress').val());
			var marriageValue = $.trim($('#marriage').val());
			var qq = $.trim($('#qq').val());
			var weixin = $.trim($('#weixin').val());
			if (fullnameValue == "") {
				disapperTooltip("remind", "请填写姓名");
				return false;
			}
			if (!(fullnameValue.match(/^[\u4e00-\u9fa5]+$/))) {
	            disapperTooltip("remind", "姓名只能为汉字");
	            return false;
	        }
			if (displaynameValue == "") {
				disapperTooltip("remind", "请选择简历保密方式");
				return false;
			}
			if (sexValue == "") {
				disapperTooltip("remind", "请选择性别");
				return false;
			}
			if (birthdateValue == "") {
				disapperTooltip("remind", "请选择出生年份");
				return false;
			}
			if (residenceValue != "" && residenceValue.length > 20) {
				disapperTooltip("remind", "现居住地应在1~20个字内");
				return false;
			}
			if (educationValue == "") {
				disapperTooltip("remind", "请选择学历");
				return false;
			}
			if (experienceValue == "") {
				disapperTooltip("remind", "请选择工作经验");
				return false;
			}
			if (heightValue != "" && !regularHeight.test(heightValue)) {
				disapperTooltip("remind", "请输入正确的身高（120-230）CM");
				return false;
			}
			if (householdaddressValue != "" && householdaddressValue.length > 20) {
				disapperTooltip("remind", "籍贯应在1~20个字内");
				return false;
			}
			$('.J_saveitems_baseinfo').val('保存中...').addClass('btn_disabled').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/ajax_save_basic_info');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, fullname: fullnameValue, display_name: displaynameValue, sex: sexValue, birthdate: birthdateValue, residence: residenceValue, education: educationValue, experience: experienceValue, telephone: telephoneValue, email: emailValue, major: majorValue, height: heightValue, householdaddress: householdaddressValue, marriage: marriageValue,qq:qq,weixin:weixin,idcard:idcardValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					// 如果操作成功就同步修改的值
					$('[data-sub="fullname"]').text($('#fullname').val());
					$('[data-sub="telephone"]').text($('#telephone').val());
					$('[data-sub="email"]').text($('#email').val());
					$('[data-sub="sex"]').text($('.for_sex.checked').text());
					// 计算年龄
					var cYear = parseInt($('#birthdate').val());
					var nowDate = new Date();
			    	var nowYear = nowDate.getFullYear();
			    	var ageNum = parseInt(nowYear-cYear);
					$('[data-sub="birthdate"]').text(ageNum);
					$('[data-sub="marriage"]').text($('.for_marriage.checked').text());
					$('[data-sub="height"]').text($('#height').val());
					$('[data-sub="education"]').text($('.for_education').text());
					$('[data-sub="experience"]').text($('.for_experience').text());
					$('[data-sub="residence"]').text($('#residence').val());
					$('[data-sub="major"]').text($('.for_major').text());
					$('[data-sub="householdaddress"]').text($('#householdaddress').val());
					$('[data-sub="qq"]').text($('#qq').val());
					$('[data-sub="weixin"]').text($('#weixin').val());
					$('.J_saveitems_baseinfo').val('保存').removeClass('btn_disabled').prop('disabled', 0);
					// 关闭操作框
					var $parentDom = $('.J_saveitems_baseinfo').closest('.J_itemsbox');
				    $('.J_saveitems_baseinfo').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_baseinfo').val('保存').removeClass('btn_disabled').prop('disabled', 0);
			})
			.fail(function() {
				$('.J_saveitems_baseinfo').val('保存').removeClass('btn_disabled').prop('disabled', 0);
				disapperTooltip("remind", "更新失败请重新提交");
			});
		});

		// 保存求职意向
		$('.J_saveitems_intention').live('click', function(e) {
			// 提交之前先验证
			var currentValue = $.trim($('#current').val());
			var natureValue = $.trim($('#nature').val());
			var tradeValue = $.trim($('#trade').val());
			var intentionJobsValue = $.trim($('#intention_jobs_id').val());
			var districtValue = $.trim($('#district').val());
			var wageValue = $.trim($('#wage').val());
			if (currentValue == "") {
				disapperTooltip("remind", "请选择目前状态");
				return false;
			}
			if (natureValue == "") {
				disapperTooltip("remind", "请选择工作性质");
				return false;
			}
			if (intentionJobsValue == "") {
				disapperTooltip("remind", "请选择期望职位");
				return false;
			}
			if (districtValue == "") {
				disapperTooltip("remind", "请选择工作地区");
				return false;
			}
			if (wageValue == "") {
				disapperTooltip("remind", "请选择期望薪资");
				return false;
			}
			$('.J_saveitems_intention').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/ajax_save_basic');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, current: currentValue, nature: natureValue, trade: tradeValue, intention_jobs_id: intentionJobsValue, district: districtValue, wage: wageValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					// 如果操作成功就同步修改的值
				    $('[data-sub="current"]').text($('.for_current').text());
				    $('[data-sub="nature"]').text($('.for_nature.checked').text());
				    $('[data-sub="trade"]').text($('.for_trade').text());
				    $('[data-sub="district"]').text($('.for_district').text());
				    $('[data-sub="wage"]').text($('.for_wage').text());

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_intention').closest('.J_itemsbox');
				    $('.J_saveitems_intention').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    if(data.data!=''){
				    	disapperTooltip("remind", data.msg);
				    }else{
				    	$('[data-sub="intention_jobs"]').text($('.for_intention_jobs').text());
				    }
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_intention').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_intention').prop('disabled', 0);
			});
		});

		// 添加之前先清空
		$('#J_clearinput_edu').live('click', function(event) {
			// 清空表单
			$('#school').val('');
			$('#speciality').val('');
			$('#education1').val('');
			$('#education1').closest('.J_listitme_parent').find('.J_listitme_text').text('请选择');
			$('#startyearEdu').val('');
			$('#startyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#startmonthEdu').val('');
			$('#startmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#endyearEdu').val('');
			$('#endyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#endmonthEdu').val('');
			$('#endmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('[data-waitsome="education"]').find('.J_listid').val('');
			$('#tonowEdu').attr('checked', false);
			$('#tonowEdu').val('');
			$('#tonowEdu').closest('.J_itemsmenu').find('.J_fortonow').show();
		});

		// 添加教育经历 保存
		$('.J_saveitems_education').live('click', function(e) {
		    // 提交之前先验证
			var schoolValue = $.trim($('#school').val());
			var specialityValue = $.trim($('#speciality').val());
			var education1Value = $.trim($('#education1').val());
			var startyearEduValue = $.trim($('#startyearEdu').val());
			var startmonthEduValue = $.trim($('#startmonthEdu').val());
			var endyearEduValue = $.trim($('#endyearEdu').val());
			var endmonthEduValue = $.trim($('#endmonthEdu').val());
			var todateEduValue = $.trim($('#tonowEdu').val());
			var listidValue = $.trim($('[data-waitsome="education"]').find('.J_listid').val());
			if (schoolValue == "") {
				disapperTooltip("remind", "请填写学校名称");
				return false;
			}
			if (specialityValue == "") {
				disapperTooltip("remind", "请填写专业名称");
				return false;
			}
			if (education1Value == "") {
				disapperTooltip("remind", "请选择学历");
				return false;
			}
			if (startyearEduValue == "") {
				disapperTooltip("remind", "请选择就读开始时间");
				return false;
			}
			if (startmonthEduValue == "") {
				disapperTooltip("remind", "请选择就读开始月份");
				return false;
			}
			if (!parseInt(todateEduValue)) { // 不选择至今才验证结束时间
				if (endyearEduValue == "") {
					disapperTooltip("remind", "请选择就读结束时间");
					return false;
				}
				if (endmonthEduValue == "") {
					disapperTooltip("remind", "请选择就读结束月份");
					return false;
				}
				if (wrongTime(startyearEduValue, startmonthEduValue, endyearEduValue, endmonthEduValue)) {
					disapperTooltip("remind", "结束时间应大于开始时间");
					return false;
				}
			}
			$('.J_saveitems_education').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_education');?>",
				type: 'POST',
				dataType: 'json',
				data: {id:listidValue, pid: pid, school: schoolValue, speciality: specialityValue, education: education1Value, startyear: startyearEduValue, startmonth: startmonthEduValue, endyear: endyearEduValue, endmonth: endmonthEduValue, todate: todateEduValue}

			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (listidValue == "") {
						if ($('#educationListBox .J_listhover_edit').length) {
							$('#educationListBox').append(data.data.html);
						} else {
							$('#educationListBox').html(data.data.html);
						}
					} else {
						$('#educationListBox').find('[data-id="'+listidValue+'"]').replaceWith(data.data.html);
					}
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_education').closest('.J_itemsbox');
				    $('.J_saveitems_education').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    // 清空表单
					$('#school').val('');
					$('#speciality').val('');
					$('#education1').val('');
					$('#education1').closest('.J_listitme_parent').find('.J_listitme_text').text('请选择');
					$('#startyearEdu').val('');
					$('#startyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#startmonthEdu').val('');
					$('#startmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#endyearEdu').val('');
					$('#endyearEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#endmonthEdu').val('');
					$('#endmonthEdu').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('[data-waitsome="education"]').find('.J_listid').val('');
					$('#tonowEdu').attr('checked', false);
					$('#tonowEdu').val('');
					$('#tonowEdu').closest('.J_itemsmenu').find('.J_fortonow').show();
					check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_education').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_education').prop('disabled', !0);
			});
		});

		// 添加之前先清空
		$('#J_clearinput_exp').live('click', function(event) {
			// 清空表单
		    $('#companyname').val('');
			$('#experienceJobname').val('');
			$('#startyearExp').val('');
			$('#startyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#startmonthExp').val('');
			$('#startmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#endyearExp').val('');
			$('#endyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#endmonthExp').val('');
			$('#endmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#jobrespons').val('');
			$('[data-waitsome="experience"]').find('.J_listid').val('');
			$('#tonowExp').attr('checked', false);
			$('#tonowExp').val('');
			$('#tonowExp').closest('.J_itemsmenu').find('.J_fortonow').show();
		});

		// 添加工作经历 保存
		$('.J_saveitems_experience').live('click', function(e) {
		    // 提交之前先验证
		    var f = $(this);
			var companynameValue = $.trim($('#companyname').val());
			var experiencenameValue = $.trim($('#experienceJobname').val());
			var startyearExpValue = $.trim($('#startyearExp').val());
			var startmonthExpValue = $.trim($('#startmonthExp').val());
			var endyearExpValue = $.trim($('#endyearExp').val());
			var endmonthExpValue = $.trim($('#endmonthExp').val());
			var todateExpValue = $.trim($('#tonowExp').val());
			var jobresponsValue = $.trim($('#jobrespons').val());
			var listidValue = $.trim($('[data-waitsome="experience"]').find('.J_listid').val());
			if (companynameValue == "") {
				disapperTooltip("remind", "请填写公司名称");
				return false;
			}
			if (experiencenameValue == "") {
				disapperTooltip("remind", "请填写职位名称");
				return false;
			}
			if (startyearExpValue == "") {
				disapperTooltip("remind", "请选择任职开始时间");
				return false;
			}
			if (startmonthExpValue == "") {
				disapperTooltip("remind", "请选择任职开始月份");
				return false;
			}
			if (!parseInt(todateExpValue)) {
				if (endyearExpValue == "") {
					disapperTooltip("remind", "请选择任职结束时间");
					return false;
				}
				if (endmonthExpValue == "") {
					disapperTooltip("remind", "请选择任职结束月份");
					return false;
				}
				if (wrongTime(startyearExpValue, startmonthExpValue, endyearExpValue, endmonthExpValue)) {
					disapperTooltip("remind", "结束时间应大于开始时间");
					return false;
				}
			}
			if (jobresponsValue == "") {
				disapperTooltip("remind", "请填写工作职责");
				return false;
			}
			f.attr('disabled',true);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_work');?>",
				type: 'POST',
				dataType: 'json',
				data: {id: listidValue, pid: pid, companyname: companynameValue, jobs: experiencenameValue, startyear: startyearExpValue, startmonth: startmonthExpValue, endyear: endyearExpValue, endmonth: endmonthExpValue, todate: todateExpValue, achievements: jobresponsValue}

			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (listidValue == "") {
						if ($('#experienceListBox .J_listhover_edit').length) {
							$('#experienceListBox').append(data.data.html);
						} else {
							$('#experienceListBox').html(data.data.html);
						}
					} else {
						$('#experienceListBox').find('[data-id="'+listidValue+'"]').replaceWith(data.data.html);
					}
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_experience').closest('.J_itemsbox');
				    $('.J_saveitems_experience').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    // 清空表单
				    $('#companyname').val('');
					$('#experienceJobname').val('');
					$('#startyearExp').val('');
					$('#startyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#startmonthExp').val('');
					$('#startmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#endyearExp').val('');
					$('#endyearExp').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#endmonthExp').val('');
					$('#endmonthExp').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#jobrespons').val('');
					$('[data-waitsome="experience"]').find('.J_listid').val('');
					$('#tonowExp').attr('checked', false);
					$('#tonowExp').val('');
					$('#tonowExp').closest('.J_itemsmenu').find('.J_fortonow').show();
					check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				f.attr('disabled',false);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				f.attr('disabled',false);
			});
		});
		// 项目添加之前先清空
		$('#J_clearinput_pro').live('click', function(event) {
			$('#projectname').val('');
			$('#role').val('');
			$('#startyearPro').val('');
			$('#startyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#startmonthPro').val('');
			$('#startmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#endyearPro').val('');
			$('#endyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#endmonthPro').val('');
			$('#endmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#descriptionpro').val('');
			$('[data-waitsome="project"]').find('.J_listid').val('');
			$('#tonowPro').attr('checked', false);
			$('#tonowPro').val('');
			$('#tonowPro').closest('.J_itemsmenu').find('.J_fortonow').show();
		});

		// 添加项目经历 保存
		$('.J_saveitems_project').live('click', function(e) {
			// 提交之前先验证
			var projectnameValue = $.trim($('#projectname').val());
			var roleValue = $.trim($('#role').val());
			var startyearProValue = $.trim($('#startyearPro').val());
			var startmonthProValue = $.trim($('#startmonthPro').val());
			var endyearProValue = $.trim($('#endyearPro').val());
			var endmonthProValue = $.trim($('#endmonthPro').val());
			var todateProValue = $.trim($('#tonowPro').val());
			var descriptionproValue = $.trim($('#descriptionpro').val());
			var listidValue = $.trim($('[data-waitsome="project"]').find('.J_listid').val());
			if (projectnameValue == "") {
				disapperTooltip("remind", "请填写项目名称");
				return false;
			}
			if (roleValue == "") {
				disapperTooltip("remind", "请填写担任角色");
				return false;
			}
			if (startyearProValue == "") {
				disapperTooltip("remind", "请选择项目开始时间");
				return false;
			}
			if (startmonthProValue == "") {
				disapperTooltip("remind", "请选择项目开始月份");
				return false;
			}
			if (!parseInt(todateProValue)) {
				if (endyearProValue == "") {
					disapperTooltip("remind", "请选择项目结束时间");
					return false;
				}
				if (endmonthProValue == "") {
					disapperTooltip("remind", "请选择项目结束月份");
					return false;
				}
				if (wrongTime(startyearProValue, startmonthProValue, endyearProValue, endmonthProValue)) {
					disapperTooltip("remind", "结束时间应大于开始时间");
					return false;
				}
			}
			if (descriptionproValue == "") {
				disapperTooltip("remind", "请填写项目描述");
				return false;
			}
			$('.J_saveitems_project').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_project');?>",
				type: 'POST',
				dataType: 'json',
				data: {id: listidValue, pid: pid, projectname: projectnameValue, role: roleValue, startyear: startyearProValue, startmonth: startmonthProValue, endyear: endyearProValue, endmonth: endmonthProValue, todate: todateProValue, description: descriptionproValue}

			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (listidValue == "") {
						if ($('#projectListBox .J_listhover_edit').length) {
							$('#projectListBox').append(data.data.html);
						} else {
							$('#projectListBox').html(data.data.html);
						}
					} else {
						$('#projectListBox').find('[data-id="'+listidValue+'"]').replaceWith(data.data.html);
					}
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_project').closest('.J_itemsbox');
				    $('.J_saveitems_project').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    $('#projectname').val('');
					$('#role').val('');
					$('#startyearPro').val('');
					$('#startyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#startmonthPro').val('');
					$('#startmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#endyearPro').val('');
					$('#endyearPro').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#endmonthPro').val('');
					$('#endmonthPro').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#descriptionpro').val('');
					$('[data-waitsome="PROJ"]').find('.J_listid').val('');
					$('#tonowPro').attr('checked', false);
					$('#tonowPro').val('');
					$('#tonowPro').closest('.J_itemsmenu').find('.J_fortonow').show();
					check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_project').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_project').prop('disabled', 0);
			});
		});
		// 添加之前先清空
		$('#J_clearinput_tra').live('click', function(event) {
			$('#agency').val('');
			$('#course').val('');
			$('#startyearTra').val('');
			$('#startyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#startmonthTra').val('');
			$('#startmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#endyearTra').val('');
			$('#endyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#endmonthTra').val('');
			$('#endmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('#description').val('');
			$('[data-waitsome="train"]').find('.J_listid').val('');
			$('#tonowTra').attr('checked', false);
			$('#tonowTra').val('');
			$('#tonowTra').closest('.J_itemsmenu').find('.J_fortonow').show();
		});
		// 添加培训经历 保存
		$('.J_saveitems_train').live('click', function(e) {
			// 提交之前先验证
			var agencyValue = $.trim($('#agency').val());
			var courseValue = $.trim($('#course').val());
			var startyearTraValue = $.trim($('#startyearTra').val());
			var startmonthTraValue = $.trim($('#startmonthTra').val());
			var endyearTraValue = $.trim($('#endyearTra').val());
			var endmonthTraValue = $.trim($('#endmonthTra').val());
			var todateTraValue = $.trim($('#tonowTra').val());
			var descriptionValue = $.trim($('#description').val());
			var listidValue = $.trim($('[data-waitsome="train"]').find('.J_listid').val());
			if (agencyValue == "") {
				disapperTooltip("remind", "请填写培训机构名称");
				return false;
			}
			if (courseValue == "") {
				disapperTooltip("remind", "请填写培训课程");
				return false;
			}
			if (startyearTraValue == "") {
				disapperTooltip("remind", "请选择培训开始时间");
				return false;
			}
			if (startmonthTraValue == "") {
				disapperTooltip("remind", "请选择培训开始月份");
				return false;
			}
			if (!parseInt(todateTraValue)) {
				if (endyearTraValue == "") {
					disapperTooltip("remind", "请选择培训结束时间");
					return false;
				}
				if (endmonthTraValue == "") {
					disapperTooltip("remind", "请选择培训结束月份");
					return false;
				}
				if (wrongTime(startyearTraValue, startmonthTraValue, endyearTraValue, endmonthTraValue)) {
					disapperTooltip("remind", "结束时间应大于开始时间");
					return false;
				}
			}
			if (descriptionValue == "") {
				disapperTooltip("remind", "请填写培训内容");
				return false;
			}
			$('.J_saveitems_train').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_training');?>",
				type: 'POST',
				dataType: 'json',
				data: {id: listidValue, pid: pid, agency: agencyValue, course: courseValue, startyear: startyearTraValue, startmonth: startmonthTraValue, endyear: endyearTraValue, endmonth: endmonthTraValue, todate: todateTraValue, description: descriptionValue}

			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (listidValue == "") {
						if ($('#trainListBox .J_listhover_edit').length) {
							$('#trainListBox').append(data.data.html);
						} else {
							$('#trainListBox').html(data.data.html);
						}
					} else {
						$('#trainListBox').find('[data-id="'+listidValue+'"]').replaceWith(data.data.html);
					}
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_train').closest('.J_itemsbox');
				    $('.J_saveitems_train').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    $('#agency').val('');
					$('#course').val('');
					$('#startyearTra').val('');
					$('#startyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#startmonthTra').val('');
					$('#startmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#endyearTra').val('');
					$('#endyearTra').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#endmonthTra').val('');
					$('#endmonthTra').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('#description').val('');
					$('[data-waitsome="train"]').find('.J_listid').val('');
					$('#tonowTra').attr('checked', false);
					$('#tonowTra').val('');
					$('#tonowTra').closest('.J_itemsmenu').find('.J_fortonow').show();
					check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_train').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_train').prop('disabled', 0);
			});
		});
		
		// 修改语言能力
		$('.J_showitems_edit').live('click', function(e) {
			$.getJSON("<?php echo U('Personal/edit_language');?>", {pid: pid}, function(data) {
				if (data.status) {
					$('#ajaxEditLanguageListBox').html(data.data.html);
					$('.J_itemsmenu').hide();
					$('.items').show();
					var $parentDom = $('.J_showitems_edit').closest('.J_itemsbox');
				    $('.J_showitems_edit').closest('.items').hide();
				    $parentDom.find('.J_itemsmenu').show();

				    // 删除语言能力
				    $('.J_dellanguage').click(function() {
						if ($('#ajaxEditLanguageListBox .J_listhover_edit').length > 1) {
							$(this).closest('.J_listhover_edit').remove();
						} else if ($('#ajaxEditLanguageListBox .J_listhover_edit').length == 1) {
							$('#J-for-last-del').val('1');
							$(this).closest('.J_listhover_edit').data('id','0');
							$(this).closest('.J_listhover_edit').find('.J_listitme_text').eq(0).text('选择语种');
							$(this).closest('.J_listhover_edit').find('.J_listitme_text').eq(1).text('熟悉程度');
							$(this).closest('.J_listhover_edit').find('.J_listitme_code').val('');
	                        $('#JLanguageStatus').removeClass('but_gray_70_res_edit').addClass('but_gray_70_res_add').text('添加');
						}
					});

					// 新增一项
					$('.J_addlanguage').click(function() {
						var existingLength = $('#ajaxEditLanguageListBox .J_listhover_edit').length;
						if (existingLength < 6) {
							var $last = $('#ajaxEditLanguageListBox .J_listhover_edit').last();
							var $obj = $last.clone(true);
							$obj.data('id','0');
							$obj.find('.J_listitme_text').eq(0).text('选择语种');
							$obj.find('.J_listitme_text').eq(1).text('熟悉程度');
							$obj.find('.J_listitme_code').val('');
							$obj.insertAfter($last);
						} else {
							disapperTooltip("remind", "最多可添加6种语言");
						}
					});
				}
			});
		});

		// 添加语言能力 保存
		$('.J_saveitems_language').live('click', function(e) {
		    // 提交之前先验证
		    var switchReq = true;
		    var remindType;
		    var languageArray = new Array();
		    var levelArray = new Array();
		    $('[data-waitsome="language"] .J_listitme_code').each(function(index, el) {
		    	if ($.trim($(this).val()) == '') {
		    		switchReq = false;
		    		if ($(this).attr('name') == 'language') {
		    			remindType = 0;
		    		} else {
		    			remindType = 1;
		    		}
		    		return false;
		    	} else {
		    		if ($(this).attr('name') == 'language') {
		    			languageArray.push($.trim($(this).val()));
		    		} else {
		    			levelArray.push($.trim($(this).val()));
		    		}
		    		
		    	}
		    });

		    // 去除重复
		    var sortArray = languageArray.sort();
		    for (var i = 0; i < sortArray.length; i++) {
		    	if (sortArray[i] == sortArray[i+1]) {
		    		disapperTooltip("remind", "不能重复选择");
		    		return false;
		    	}
		    }

			if (!eval($('#J-for-last-del').val())) {
				if (!switchReq) {
					if (remindType) {
						disapperTooltip("remind", "请选择熟悉程度");
					} else {
						disapperTooltip("remind", "请选择语种");
					}
					return false;
				}
			}
			$('.J_saveitems_language').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_language');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, language: languageArray, level: levelArray}

			})
			.done(function(data) {
				if (parseInt(data.status)) {
					$('#languageListBox').html(data.data.html);
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_language').closest('.J_itemsbox');
				    $('.J_saveitems_language').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
					$('#J-for-last-del').val('0');
					if (languageArray.length) {
	                    $('#JLanguageStatus').removeClass('but_gray_70_res_add').addClass('but_gray_70_res_edit').text('修改');
	                } else {
	                    $('#JLanguageStatus').removeClass('but_gray_70_res_edit').addClass('but_gray_70_res_add').text('添加');
	                }
	                check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_language').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_language').prop('disabled', 0);
			});
		});

		// 添加之前先清空
		$('#J_clearinput_cre').live('click', function(event) {
			$('#credent').val('');
			$('#yearCredent').val('');
			$('#yearCredent').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
			$('#monthCredent').val('');
			$('#monthCredent').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
			$('[data-waitsome="credent"]').find('.J_listid').val('');
		});

		// 添加获得证书 保存
		$('.J_saveitems_credent').live('click', function(e) {
		    // 提交之前先验证
			var credentValue = $.trim($('#credent').val());
			var yearCredentValue = $.trim($('#yearCredent').val());
			var monthCredentValue = $.trim($('#monthCredent').val());
			var listidValue = $.trim($('[data-waitsome="credent"]').find('.J_listid').val());
			if (credentValue == "") {
				disapperTooltip("remind", "请填写证书名称");
				return false;
			}
			if (yearCredentValue == "") {
				disapperTooltip("remind", "请选择获得证书年份");
				return false;
			}
			if (monthCredentValue == "") {
				disapperTooltip("remind", "请选择获得证书月份");
				return false;
			}
			if (!listidValue) {
				// 添加证书数量限制
				if ($('#credentListBox .J_listhover_edit').length >= 6) {
					disapperTooltip("remind", "最多可以添加6份证书");
					return false;
				}
			}
			$('.J_saveitems_credent').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/save_credent');?>",
				type: 'POST',
				dataType: 'json',
				data: {id: listidValue, pid: pid, name: credentValue, year: yearCredentValue, month: monthCredentValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (listidValue == "") {
						if ($('#credentListBox .J_listhover_edit').length) {
							$('#credentListBox').append(data.data.html);
						} else {
							$('#credentListBox').html(data.data.html);
						}
					} else {
						$('#credentListBox').find('[data-id="'+listidValue+'"]').replaceWith(data.data.html);
					}
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_credent').closest('.J_itemsbox');
				    $('.J_saveitems_credent').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    $('#credent').val('');
					$('#yearCredent').val('');
					$('#yearCredent').closest('.J_listitme_parent').find('.J_listitme_text').text('年份');
					$('#monthCredent').val('');
					$('#monthCredent').closest('.J_listitme_parent').find('.J_listitme_text').text('月份');
					$('[data-waitsome="credent"]').find('.J_listid').val('');
					check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_credent').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_credent').prop('disabled', 0);
			});
		});

		// 添加自我描述 保存
		$('.J_saveitems_specialty').live('click', function(e) {
		    // 提交之前先验证
			var specialtyValue = $.trim($('#specialty').val());
			if (specialtyValue == "") {
				disapperTooltip("remind", "请填写自我描述");
				return false;
			}
			$('.J_saveitems_specialty').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/ajax_save_specialty');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, specialty: specialtyValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					var htmlVal = $('#specialty').val();
					$('.specialty_box').removeClass('empty font_gray9').addClass('describe font_gray6').text(htmlVal);
				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_specialty').closest('.J_itemsbox');
				    $('.J_saveitems_specialty').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_specialty').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_specialty').prop('disabled', 0);
			});
		});

		// 添加特长标签 保存
		$('.J_saveitems_tag').die().live('click', function(e) {
			var tagListArray = $('.J_taglist.select');
			var tagvalueArray = new Array();
			var tagcnvalueArray = new Array();
			if (tagListArray.length) {
				$.each(tagListArray, function(index, val) {
					if($(this).data('code')){
						tagvalueArray.push($(this).data('code'));
					}else{
						tagcnvalueArray.push($(this).data('title'));
					}
				});
			} else {
				disapperTooltip("remind", "请选择特长标签");
				return false;
			}
			$('.J_saveitems_tag').prop('disabled', !0);
			// 提交表单
			$.ajax({
				url: "<?php echo U('Personal/ajax_save_tag');?>",
				type: 'POST',
				dataType: 'json',
				data: {pid: pid, tag: tagvalueArray, tag_cn:tagcnvalueArray}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					var htmlPrevTag = '';
					$.each(tagListArray, function(index, val) {
						var typeNum = parseInt($(this).data('type'));
						if (typeNum) {
							htmlPrevTag += '<div class="label_speciality sys">' + $(this).data('title') + '</div>';
						} else {
							htmlPrevTag += '<div class="label_speciality">' + $(this).data('title') + '</div>';
						}
					});
					$('#tagListBox').html(htmlPrevTag);
					listHoveEdit();

				    // 关闭操作框
				    var $parentDom = $('.J_saveitems_tag').closest('.J_itemsbox');
				    $('.J_saveitems_tag').closest('.J_itemsmenu').hide();
				    $parentDom.find('.items').show();
				    check_complete_percent_allowance();
				} else {
					disapperTooltip("remind", data.msg);
				}
				$('.J_saveitems_tag').prop('disabled', 0);
			})
			.fail(function() {
				disapperTooltip("remind", "更新失败请重新提交");
				$('.J_saveitems_tag').prop('disabled', 0);
			});
		});

		// 自定义标签
		$('#J_add_selftag').die().live('click', function() {
			var tagValue = $.trim($('input[name="selftag"]').val());
			var isRepeat = false;
			if (tagValue == '') {
				disapperTooltip("remind", "您忘记填写亮点啦，不超过8个字！");
				return false;
			}
			if (tagValue.length > 8) {
				disapperTooltip("remind", "自定义标签不能超过8个字！");
				return false;
			}
			$('.J_taglist').each(function(index, el) {
				if (tagValue == $(this).data('title')) {
					isRepeat = true;
				}
			})
			if (isRepeat) {
				disapperTooltip("remind", "填写的亮点已经存在了哦！");
				return false;
			}
			var slefTagHtml = '<div class="sp_label J_hoverbut nowrap J_taglist" data-code="" data-title="'+tagValue+'" data-type="0">'+tagValue+'<div class="choose"></div></div>';
			$('#ajaxtagListBox').append(slefTagHtml);
		});

		// 点击显示上传图片
		$('.J_showitems_saveimg').live('click', function(e) {
			var groupLength = $('#J_resumeimg_box .J_resumeimg_group').length;
			if (groupLength >= 6) {
				disapperTooltip("remind", '最多可以上传6张');
			} else {
				$('.J_itemsmenu').hide();
				$('.items').show();
				var $parentDom = $('.J_showitems_saveimg').closest('.J_itemsbox');
			    $('.J_showitems_saveimg').closest('.items').hide();
			    $parentDom.find('.J_itemsmenu').show();
			}
		});

		// 删除作品/照片
		$('.J_resumeimg_del').die().live('click', function(e) {
			var thisGroup = $(this).closest('.J_resumeimg_group');
			var imgIdCode = thisGroup.data('code');
			var url = "<?php echo U('Personal/ajax_resume_img_del');?>";
			var qsDialog = $(this).dialog({
		        title: '删除照片/作品',
	            loading: true,
	            border: false,
	            footer: false
	        });
	        $.getJSON(url, function (result) {
	            if (result.status == 1) {
	            	qsDialog.hide();
	            	var qsDialogSon = $(this).dialog({
		                title: '删除照片/作品',
		                content: result.data.html,
		                border: false,
		                yes: function () {
		                    $.post(url, {id: imgIdCode}, function(data) {
								if (data.status) {
									$('#J_resumeimg_box').find('[data-code="'+imgIdCode+'"]').remove();
									if (!$('#J_resumeimg_box .J_resumeimg_group').length) {
									  $('#J_resumeimg_box').html('<div class="empty font_gray9">最多上传6张，每张最大800KB,支持jpg/gif/bmp/png格式，建议上传清晰自然生活照，或者您的专业代表作品。</div>');
										// 更改右侧菜单对应的状态
										$('[data-forsome="resumeimg"]').removeClass('ok').text('添加');
										$('[data-forsome="resumeimg"]').parent().prev().removeClass('ok');
										// 更改右侧简历完整度
										refreshResumePercent();
									}
								} else {
									disapperTooltip("remind", "删除失败");
								}
							},'json');
		                }
		            });
	            } else {
	            	qsDialog.hide();
	                disapperTooltip('remind', result.msg);
	            }
	        });
			
		});

		// 扫码上传
		var scanimg_time,
		waiting_img_scan = function(){
			$.getJSON("<?php echo U('Personal/ajax_resume_img_waiting');?>", {pid:pid}, function(result){
				if(result.status == 1){
					var htmlImg = '';
					var scanimgArr = result.data.img;
					var imgTotal = result.data.total;
					if (!eval(imgTotal)) {
						$('#J_resumeimg_box').html('<div class="empty font_gray9">最多上传6张，每张最大800KB,支持jpg/gif/bmp/png格式，建议上传清晰自然生活照，或者您的专业代表作品。</div>');
					} else {
						for (var i = 0; i < scanimgArr.length; i++) {
							htmlImg += [
				                '<div class="plist J_resumeimg_group" data-code="'+scanimgArr[i]['id']+'" data-img="'+scanimgArr[i]['img']+'">',
				                '<div class="bg J_hoverbut">',
				                '<div class="pic"><img src="'+scanimgArr[i]['img']+'" alt="" /></div>',
				                '<div class="audit font_yellow">等待审核</div>',
				                '<div class="edit J_resumeimg_edit"></div>',
				                '<div class="picdel J_resumeimg_del"><a href="javascript:;">删除</a></div>',
				                '<div class="clear"></div>',
				                '</div>',
				                '</div>'
				            ].join('');
				            $('#J_resumeimg_box').html(htmlImg);
						}
					}

					// 关闭操作框
					var $parentDom = $('#resume_img').closest('.J_itemsbox');
					$('#resume_img').closest('.J_itemsmenu').hide();
					$parentDom.find('.items').show();
					check_complete_percent_allowance();
				}
			})
		};
		$('.J_scan_img').click(function() {
			$.ajax({
			url: "<?php echo U('Personal/ajax_resume_img_scan');?>",
			type: 'POST',
			dataType: 'json',
			data: {pid: pid}
		}).done(function(data) {
				if (eval(data.status)) {
					scanimg_time=setInterval(waiting_img_scan,5000);
				} else {
					disapperTooltip("remind", data.msg);
				}
			})
		});

		// 上传作品/照片
		$.upload('#resume_img',{type:'resume_img',pid: pid},function(result){
			if(result.status == 1){
	          // 提交之前先验证
	          var imgValue = result.data.img;
	          var srcValue = result.data.path;
	          var codeValue = result.data.id;
	          // 提交表单
	          $.ajax({
	            url: "<?php echo U('Personal/ajax_resume_attach');?>",
	            type: 'POST',
	            dataType: 'json',
	            data: {pid: pid, id: codeValue, img: imgValue}
	          })
	          .done(function(data) {
	            if (parseInt(data.status)) {
	              var htmlImg = '';
	              htmlImg += [
	                '<div class="plist J_resumeimg_group" data-code="'+data.data+'" data-img="'+imgValue+'">',
	                '<div class="bg J_hoverbut">',
	                '<div class="pic"><img src="'+srcValue+'" alt="" /></div>',
	                '<div class="audit font_yellow">等待审核</div>',
	                '<div class="edit J_resumeimg_edit"></div>',
	                '<div class="picdel J_resumeimg_del"><a href="javascript:;">删除</a></div>',
	                '<div class="clear"></div>',
	                '</div>',
	                '</div>'
	              ].join('');
	              var groupLength = $('#J_resumeimg_box .J_resumeimg_group').length;
	              if (groupLength) {
	                var isAppend = true;
	                $('#J_resumeimg_box .J_resumeimg_group').each(function(index, el) {
	                  if ($(this).data('code') == codeValue) {
	                    isAppend = false;
	                  }
	                });
	                if (isAppend) {
	                  $('#J_resumeimg_box').append(htmlImg);
	                } else {
	                  $('#J_resumeimg_box').find('[data-code="'+codeValue+'"]').replaceWith(htmlImg);
	                }
	              } else {
	                $('#J_resumeimg_box').html(htmlImg);
	              }

	              // 关闭操作框
	              var $parentDom = $('#resume_img').closest('.J_itemsbox');
	              $('#resume_img').closest('.J_itemsmenu').hide();
	              $parentDom.find('.items').show();
	              check_complete_percent_allowance();
	            } else {
	              disapperTooltip("remind", data.msg);
	            }
	          })
	          .fail(function() {
	            disapperTooltip("remind", "更新失败请重新提交");
	          });
			}else{
				disapperTooltip("remind", result.msg);
			}
		});

		// 上传附件简历
		$.upload('#word_resume',{type:'word_resume',pid: pid},function(result){
			if(result.status == 1){
				var htmlWord = '';
				htmlWord += [
					'<div class="dleft_tit link_gray6 substring">'+result.data.name+'</div>',
					'<div class="dtime">上传于：'+result.data.time+'</div>',
					'<div class="ddown link_yellow">',
						'<a href="javascript:;" class="J_resumeword_update">更换</a>&nbsp;&nbsp;&nbsp;',
						'<a href="'+result.data.path+'">下载</a>&nbsp;&nbsp;&nbsp;',
						'<a href="javascript:;" class="J_resumeword_del">删除</a>',
					'</div>',
					'<div class="clear"></div>'
				].join('');
				$('#J_resumeword_box').html(htmlWord);
				$('#J_has_resume_word_sub').text('修改');
			    // 关闭操作框
			    var $parentDom = $('#word_resume').closest('.J_itemsbox');
			    $('#word_resume').closest('.J_itemsmenu').hide();
			    $parentDom.find('.items').show();
			    check_complete_percent_allowance();
			}else{
				disapperTooltip("remind", result.msg);
			}
		});

		// 更换附件简历
		$('.J_resumeword_update').die().live('click', function(e) {
			$('.J_itemsmenu').hide();
			$('.items').show();
			var $parentDom = $(this).closest('.J_itemsbox');
		    $(this).closest('.items').hide();
		    $parentDom.find('.J_itemsmenu').show();
		});
		
		// 删除附件简历
		$('.J_resumeword_del').die().live('click', function(e) {
			var thisObj = $(this);
			var qsDialog = $(this).dialog({
	            title: '删除附件简历',
	            loading: true,
	            border: false,
	            yes: function () {
	                var thisGroup = thisObj.closest('.J_resumeimg_group');
					var imgIdCode = thisGroup.data('code');
					$.getJSON("<?php echo U('Personal/ajax_word_del');?>", {pid: pid}, function(data) {
						if (data.status) {
							$('#J_resumeword_box').html('<div class="empty font_gray9">请上传doc/docx/pdf格式的附件(文件大小2M以内)</div>');
							if (!$('#J_resumeword_box .dleft_tit').length) {
								// 更改右侧菜单对应的状态
								$('[data-forsome="resumeword"]').removeClass('ok').text('添加');
								$('[data-forsome="resumeword"]').parent().prev().removeClass('ok');
								// 更改右侧简历完整度
								refreshResumePercent();
								$('#J_has_resume_word_sub').text('上传');
							};
						} else {
							disapperTooltip("remind", "删除失败");
						}
					});
	            }
	        });
	        $.getJSON("<?php echo U('Personal/ajax_word_del');?>",{warning:1}, function (result) {
	            if (result.status == 1) {
	                qsDialog.setContent(result.data.html);
	            } else {
	                qsDialog.hide();
	                disapperTooltip('remind', result.msg);
	            }
	        });
		});

		// 至今
		$('.J_tonow').live('click', function(e) {
			if ($(this).is(':checked')) {
				$(this).val('1');
			} else {
				$(this).val('0');
			}
			$(this).closest('.J_itemsmenu').find('.J_fortonow').toggle();
		});

		// 判断结束时间是否大于开始时间
		function wrongTime(sYear, sMonth, eYear, eMonth) {
			if (parseInt(sYear) > parseInt(eYear)) {
				return true;
			} else {
				if (parseInt(sYear) == parseInt(eYear)) {
					if (parseInt(sMonth) >= parseInt(eMonth)) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		}

		// 列表滑过显示删除和编辑选项
		function listHoveEdit() {
			$('.J_listhover_edit').hover(function() {
				$(this).find(".editbox").show();
			}, function() {
				$(this).find(".editbox").hide();
			});
		}
		var qrcode_bind_time,
			waiting_weixin_bind = function(){
				$.getJSON("<?php echo U('Home/Members/waiting_weixin_bind');?>");
			};
		function check_complete_percent_allowance(){
			if(eval("<?php echo C('qscms_perfected_resume_allowance_open');?>")==1){
				$.getJSON("<?php echo U('Personal/check_complete_percent_allowance');?>",function(result){
					if(result.status==1){
						$('body').append('<div class="modal_backdrop"></div>');
						$('.get-money-fail-suc').show();
			            $('.get-money-fail-suc .cash-line .cl-big').html(result.data);
					}else if(result.status==2){
						var qsDialogBind = $('.apply_allowance').dialog({
		                    title: '绑定微信',
		                    content: result.data,
		                    btnNum: 3,
		                    btns: ['我已绑定', '取消', '不再提示'],
		                    yes:function(){
		                    	clearInterval(qrcode_bind_time);
		                    	$.getJSON("<?php echo U('Personal/check_complete_percent_allowance');?>",function(t_r){
		                    		if(t_r.status==1){
		                    			qsDialogBind.hide();
			                        	$('body').append('<div class="modal_backdrop"></div>');
			                        	$('.get-money-fail-suc').show();
			                        	$('.get-money-fail-suc .cash-line .cl-big').html(t_r.data);
		                    		}else{
		                    			disapperTooltip('remind',t_r.msg);
										return false;
		                    		}
		                    	});
		                    },
		                    other:function() {
		                    	clearInterval(qrcode_bind_time);
		                    },
		                    cancel:function(){
		                    	clearInterval(qrcode_bind_time);
		                    	$.getJSON("<?php echo U('Personal/check_complete_percent_allowance_nolonger_notice');?>");
		                    }
		                });
						qsDialogBind.setCloseDialog(false);
		                clearInterval(qrcode_bind_time);
		                qrcode_bind_time=setInterval(waiting_weixin_bind,5000);
					}
				});
			}
		}
		<?php if(!$weixin_focus && !$oauth_bind): ?>$(document).ready(function(){
				check_complete_percent_allowance();
			});<?php endif; ?>
    	$('#J_auto_apply').click(function(){
    		$.post("<?php echo U('personal/resume_auto_apply');?>",$('#J_auto_apply_form').serialize(),function(result){
    			if(result.status==1){
    				disapperTooltip('success',result.msg);
    			}else{
    				disapperTooltip('remind',result.msg);
    			}
    		},'json');
    	});
    </script>
</body>
</html>