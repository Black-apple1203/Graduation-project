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
		<link href="../public/css/company/common.css" rel="stylesheet" type="text/css" />
		<link href="../public/css/company/company_user.css" rel="stylesheet" type="text/css" />
		<link href="../public/css/company/company_ajax_dialog.css" rel="stylesheet" type="text/css" />
		<script src="../public/js/company/jquery.common.js" type="text/javascript" language="javascript"></script>
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
        <div class="ot_nav_logo"><a href="/"><?php if(C('qscms_subsite_open') == 1 && C('subsite_info.s_id') > 0): ?><img src="<?php if(C('subsite_info.s_pc_logo')): echo attach(C('subsite_info.s_pc_logo'),'subsite'); else: echo C('TPL_HOME_PUBLIC_DIR');?>/images/logo.gif<?php endif; ?>" alt="">
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
            <li class="on_li J_hoverbut <?php if($company_nav == 'index'): ?>select<?php endif; ?>"><a href="<?php echo U('company/index',array('uid'=>$visitor['uid']));?>">企业中心</a></li>
            <li class="on_li J_hoverbut <?php if($company_nav == 'jobs_list'): ?>select<?php endif; ?>"><a href="<?php echo U('company/jobs_list',array('type'=>1));?>">职位管理</a></li>
            <li class="on_li J_hoverbut <?php if($company_nav == 'jobs_apply'): ?>select<?php endif; ?>"><a href="<?php echo U('company/jobs_apply');?>">简历管理</a></li>
            <li class="on_li J_hoverbut <?php if(CONTROLLER_NAME == 'CompanyService' || $company_nav == 'service'): ?>select<?php endif; ?>"><a href="<?php echo U('companyService/index');?>">会员服务</a></li>
            <?php if(!empty($apply['Jobfair'])): ?><li class="on_li J_hoverbut <?php if($company_nav == 'jobfair_list'): ?>select<?php endif; ?>"><a href="<?php echo U('company/jobfair_list');?>">招聘会</a></li><?php endif; ?>
            <?php if(!empty($apply['Seniorjobfair'])): ?><li class="on_li J_hoverbut <?php if($company_nav == 'seniorjobfair_list'): ?>select<?php endif; ?>"><a href="<?php echo U('company/seniorjobfair_list');?>">招聘会</a></li><?php endif; ?>
            <?php if(!empty($apply['School'])): ?><li class="on_li J_hoverbut <?php if($company_nav == 'school_talk_list'): ?>select<?php endif; ?>"><a href="<?php echo U('home/school/talk_list');?>">校园宣讲会</a></li><?php endif; ?>
            <li class="on_li J_hoverbut <?php if($company_nav == 'com_info'): ?>select<?php endif; ?>"><a href="<?php echo U('company/com_info');?>">账号管理</a></li>
            <?php if(C('qscms_beidiao_status') == '1'): ?><li class="on_li J_hoverbut"><a target = "_blank" href="<?php echo U('home/Beidiao/index');?>">背景调查</a></li><?php endif; ?>
        </ul>
        <div class="clear"></div>
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
		<div class="user_main">
			<div class="mleft">
				<div class="left_jobs">
  <div class="li link_gray6 J_hoverbut t18 <?php if(ACTION_NAME == 'com_info'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/com_info');?>'"><a href="<?php echo U('company/com_info');?>">企业资料</a></div>
  <div class="li link_gray6 J_hoverbut t19 <?php if(ACTION_NAME == 'com_img'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/com_img');?>'"><a href="<?php echo U('company/com_img');?>">企业风采</a></div>
  <div class="li link_gray6 J_hoverbut t20 <?php if(ACTION_NAME == 'com_auth'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/com_auth');?>'"><a href="<?php echo U('company/com_auth');?>">企业认证</a></div>
  <div class="li link_gray6 J_hoverbut t21 <?php if(ACTION_NAME == 'user_security' || $left_nav == 'user_security'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/user_security');?>'"><a href="<?php echo U('company/user_security');?>">账号安全</a></div>
  <div class="li link_gray6 J_hoverbut t22 <?php if(ACTION_NAME == 'pms_sys' || ACTION_NAME == 'pms_consult'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/pms_sys');?>'"><a href="<?php echo U('company/pms_sys');?>">我的消息</a></div>
  <link href="../public/css/common_ajax_dialog.css" rel="stylesheet" type="text/css" />
<?php if(!empty($consultant)): ?><div class="service">
	<div class="tit"><strong>专属客服</strong></div>
	<div class="photo"><img src="<?php echo attach($consultant['pic'],'consultant');?>"  width="70"  height="70"   border="0"/></div>
	<div class="name"><?php echo ($consultant["name"]); ?></div>
	<div class="qq"><a target="blank" href="tencent://message/?uin=<?php echo ($consultant["qq"]); ?>&Site=menu&Menu=yes"><img border="0" SRC=http://wpa.qq.com/pa?p=1:<?php echo ($consultant["qq"]); ?>:1 alt="点击这里给我发消息"></a></div>
	<?php if($consultant['mobile'] || $consultant['tel']): ?><div class="tel">
		<?php if($consultant['mobile']): echo ($consultant["mobile"]); ?><br /><?php endif; ?>
		<?php if($consultant['tel']): echo ($consultant["tel"]); endif; ?>
	</div><?php endif; ?>
	<div class="btnbox">
	  <div class="btn_complaint J_hoverbut ">投诉TA</div>
	</div>
</div>
<script type="text/javascript">
	$(".btn_complaint").click(function(){
    	var url = "<?php echo U('Company/complaint_consultant');?>";
        var qsDialog = $(this).dialog({
            title: '投诉客服',
            loading: true,
            border: false,
            yes: function () {
            	var notes = $("#notes").val();
                $.post(url, {notes:notes},function (result) {
	                if (result.status == 1) {
	                	disapperTooltip('success', result.msg);
	                    qsDialog.setCloseDialog(true);
	                } else {
	                    disapperTooltip('remind', result.msg);
	                    qsDialog.setCloseDialog(false);
	                }
	            },'json');
            }
        });
        $.getJSON(url, function (result) {
            if (result.status == 1) {
                qsDialog.setContent(result.data);
            } else {
                disapperTooltip('remind', result.msg);
            }
        });
    });
</script><?php endif; ?>
</div>
 </div>
			<div class="mright">
				<div class="user_pagetitle">
					<div class="pat_l">基本资料</div>
					<div class="clear"></div>
				</div>
				<div class="profile_wrap">
					<div class="profile_title">基本信息</div>
					<div class="dashed_line"></div>
					<div class="clear"></div>
					<div class="basic_form J_focus pos_rel">
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>企业名称：</div>
							<div class="item_val">
							<?php if($company_profile['id']): ?><div class="line_substring" title="<?php echo ($company_profile['companyname']); ?>"><?php echo ($company_profile['companyname']); ?></div><span class="sm_tip">（修改企业名称请联系客服人员，服务热线：<span class="num"><?php echo C('qscms_bootom_tel');?></span>）</span>
							<input type="hidden" name="companyname" value="<?php echo ($company_profile['companyname']); ?>">
							<?php else: ?>
							<input type="text" class="input_205_34" name="companyname"><?php endif; ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">企业简称：</div>
							<div class="item_val">
								<?php if($company_profile['short_name']): ?><div class="line_substring" title="<?php echo ($company_profile['short_name']); ?>"><?php echo ($company_profile['short_name']); ?></div>
									<input type="hidden" name="short_name" value="<?php echo ($company_profile['short_name']); ?>">
									<?php else: ?>
									<input type="text" value="<?php echo ($company_profile["companyname"]); ?>" class="input_205_34 fl" name="short_name" placeholder="请填写大家最熟悉的名字"><span class="sm_tip">填写大家最熟悉的名字</span><?php endif; ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>企业性质：</div>
							<div class="item_val select_input select_205_34 J_hoverinput J_dropdown J_listitme_parent">
								<span class="J_listitme_text"><?php echo (($company_profile['nature_cn'] != "")?($company_profile['nature_cn']):"请选择"); ?></span>
								<div class="dropdowbox8 J_dropdown_menu">
						            <div class="dropdow_inner8">
						                <ul class="nav_box">
						                	<?php if(is_array($category['QS_company_type'])): $i = 0; $__LIST__ = $category['QS_company_type'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>" ><?php echo ($vo); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
						                </ul>
						            </div>
						        </div>
								<input class="J_listitme_code" name="nature" type="hidden" value="<?php echo ($company_profile["nature"]); ?>" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>企业规模：</div>
							<div class="item_val select_input select_205_34 J_hoverinput J_dropdown J_listitme_parent">
								<span class="J_listitme_text"><?php echo (($company_profile['scale_cn'] != "")?($company_profile['scale_cn']):"请选择"); ?></span>
								<div class="dropdowbox8 J_dropdown_menu">
									<div class="dropdow_inner8">
										<ul class="nav_box">
											<?php if(is_array($category['QS_scale'])): $i = 0; $__LIST__ = $category['QS_scale'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a class="J_listitme" href="javascript:;" data-code="<?php echo ($key); ?>" ><?php echo ($vo); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
										</ul>
									</div>
								</div>
								<input class="J_listitme_code" name="scale" type="hidden" value="<?php echo ($company_profile["scale"]); ?>" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
								<div class="item_label"><span class="asterisk"></span>所在地区：</div>
								<div class="item_val select_input_multi select_205_34 J_hoverinput" data-toggle="funCityModal" data-title="请选择工作地区" data-multiple="false" data-maximum="0" data-width="760">
								<span title="" class="result J_resuletitle_city"><?php echo (($company_profile["district_cn"] != "")?($company_profile["district_cn"]):"请选择"); ?></span>
								<input class="J_resultcode_city" name="district" id="district" type="hidden" value="<?php if($company_profile['district']): echo ($company_profile["district"]); endif; ?>" keep="<?php if($company_profile['district']): echo ($company_profile["district"]); endif; ?>" />
								<div class="clear"></div>
							</div>
							<div class="item_label"><span class="asterisk"></span>所属行业：</div>
							<div class="item_val select_input select_205_34 J_hoverinput" id="J_showmodal_trade" data-title="请选择所属行业" data-multiple="false" data-maxnum="0" data-width="682">
								<span title="" class="result J_resuletitle_trade"><?php echo (($company_profile['trade_cn'] != "")?($company_profile['trade_cn']):"请选择"); ?></span>
								<input class="J_resultcode_trade" type="hidden" name="trade" id="trade" value="<?php echo ($company_profile['trade']); ?>">
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
						<div class="logo_wrap pos_rel">
							<div class="logo">
								<?php if($company_profile['logo']): ?><img id="logo_res" width="120" height="120" src="<?php echo attach($company_profile['logo'],'company_logo');?>?<?php echo time();?>">
								<?php else: ?>
								<img id="logo_res" width="120" height="120" src="<?php echo attach('no_logo.png','resource');?>"><?php endif; ?>
							</div>
							<div class="logo_upload" id="hidden_file" name="company_logo">
								<div class="upimg"><div class="up">上传logo</div><div class="uptip"><p>要求：请用jpg,gif</p> <p>尺寸：120*120</p></div></div>
							</div>
						</div>
						<div class="clear_logo" <?php if($company_profile['logo']): ?>style="display:block;"<?php endif; ?>>清除</div>

						<div class="item">
							<div class="item_label">注册资金：</div>
							<div class="item_val">
								<div class="select_input_write">
									<input type="text" class="inputst" dir="no_focus" name="registered" value="<?php echo ($company_profile["registered"]); ?>" onkeyup="if(event.keyCode !=37 &amp;&amp; event.keyCode != 39) value=value.replace(/\D/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/\D/g,''))">
									<div class="righttxt for_tooltip J_tooltip J_listitme_parent">
										<span class="J_listitme_text"><?php echo (($company_profile["currency"] != "")?($company_profile["currency"]):"万人民币"); ?></span>
										<div class="dropdowbox14 J_tooltip_menu">
								            <div class="dropdow_inner14">
								                <ul class="nav_box">
								                    <li><a class="J_listitme" href="javascript:;" data-code="万人民币">万人民币</a></li>
								                    <li><a class="J_listitme" href="javascript:;" data-code="万美元">万美元</a></li>
								                </ul>
								            </div>
								        </div>
										<input class="J_listitme_code" name="currency" type="hidden" value="<?php echo (($company_profile["currency"] != "")?($company_profile["currency"]):"万人民币"); ?>"/>
									</div>
									<div class="clear"></div>
								</div>
							</div>
							<div class="item_label">企业网址：</div>
							<div class="item_val"><input type="text" value="<?php echo ($company_profile["website"]); ?>" placeholder="http://" class="input_205_34" name="website"></div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">企业福利：</div>
							<div class="item_val select_input_multi select_245_34 w518 J_hoverinput" id="J_showmodal_jobtag" data-title="请选择企业福利" data-multiple="true" data-maxnum="6" data-width="582">
								<span title="" class="result J_resuletitle_jobtag"><?php echo (($tagStr['cn'] != "")?($tagStr['cn']):"请选择"); ?></span>
								<input class="J_resultcode_jobtag" type="hidden" name="tag" id="tag" value="<?php echo ($tagStr['id']); ?>">
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">企业简介：</div>
							<div class="item_val">
								<input type="text" value="<?php echo ($company_profile["short_desc"]); ?>" class="input_205_34 w518" name="short_desc" placeholder="请用一句话描述企业的主营业务及行业地位">
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>企业介绍：</div>
							<div class="item_val">
								<textarea class="textarea_438_34 w518" rows="" cols="" name="contents"><?php echo ($company_profile["contents"]); ?></textarea>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>

				<div class="profile_wrap">
					<div class="profile_title contact">联系方式</div>
					<div class="dashed_line"></div>
					<div class="clear"></div>
					<div class="basic_form J_focus">
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>联系人：</div>
							<div class="item_val">
								<input type="text" value="<?php echo ($company_profile["contact"]); ?>" class="input_245_34 fl" name="contact">
								<label class="item_chk fl"><input type="checkbox" name="contact_show" <?php if($company_profile['contact_show'] == ''): ?>value="1"<?php elseif($company_profile['contact_show'] == 0): ?>checked="checked" value="0"<?php else: ?>value="1"<?php endif; ?>> 不公开</label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>联系电话：</div>
							<div class="item_val">
								<?php if($visitor['mobile']): ?><div class="input_unit disabled fl">
							          <input type="text" value="<?php echo ($visitor["mobile"]); ?>" dir="no_focus" class="input_val input_205_34 nopd disabled" disabled name="telephone">
							          <a href="javascript:;" id="J_auth_mobile" class="unit edit">[修改]</a>
							        </div>
							    <?php else: ?>
									<input type="text" value="<?php if($company_profile['telephone'] != ''): echo ($company_profile["telephone"]); else: echo ($visitor["mobile"]); endif; ?>" class="input_245_34 fl" name="telephone"><?php endif; ?>
								<label class="item_chk fl"><input type="checkbox" name="telephone_show" <?php if($company_profile['telephone_show'] == ''): ?>value="1"<?php elseif($company_profile['telephone_show'] == 0): ?>checked="checked" value="0"<?php else: ?>value="1"<?php endif; ?>> 不公开</label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">&nbsp;</div>
							<div class="item_val">
								<input type="text" value="<?php echo ($company_profile['landline_tel_first']); ?>" class="input_110_34 w60 mr4 fl" name="landline_tel_first" placeholder="区号">
								<input type="text" value="<?php echo ($company_profile['landline_tel_next']); ?>" class="input_110_34 w89 mr4 fl" name="landline_tel_next" placeholder="固定电话">
								<input type="text" value="<?php echo ($company_profile['landline_tel_last']); ?>" class="input_110_34 w52 fl" name="landline_tel_last" placeholder="分机号">
								<label class="item_chk fl"><input type="checkbox" name="landline_tel_show" <?php if($company_profile['landline_tel_show'] == ''): ?>value="1"<?php elseif($company_profile['landline_tel_show'] == 0): ?>checked="checked" value="0"<?php else: ?>value="1"<?php endif; ?>> 不公开</label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">联系邮箱：</div>
							<div class="item_val">
								<div class="reg-form-content fl">
									<input type="text" value="<?php if($company_profile['email'] != ''): echo ($company_profile["email"]); else: echo ($visitor["email"]); endif; ?>" class="input_245_34 inputElem" name="email">
								</div>
						        <label class="item_chk fl"><input type="checkbox" name="email_show" <?php if($company_profile['email_show'] == ''): ?>value="1"<?php elseif($company_profile['email_show'] == 0): ?>checked="checked" value="0"<?php else: ?>value="1"<?php endif; ?>> 不公开</label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">QQ：</div>
							<div class="item_val">
								<input type="text" value="<?php if($company_profile['qq'] != '' && $company_profile['qq'] != '0'): echo ($company_profile["qq"]); endif; ?>" class="input_245_34 fl" name="qq">
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label"><span class="asterisk"></span>联系地址：</div>
							<div class="item_val fl">
								<input type="text" value="<?php echo ($company_profile["address"]); ?>" id="suggestId" class="input_245_34 w415" name="address">
							</div>
							<div class="search fl btn_blue J_hoverbut btn_100_32 btn_inline" id="search">精确查找</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">&nbsp;</div>
							<div class="item_val">
								<div class="map" id="container" style="height: 300px;border:1px solid #dddddd"></div>
								<div id="searchResultPanel" style="border:1px solid #C0C0C0;width:150px;height:auto; display:none;"></div>
								<input type="hidden" name="map_x" id="map_x">
								<input type="hidden" name="map_y" id="map_y">
								<input type="hidden" name="map_zoom" id="map_zoom">
								<label class="synchro_chk"><input type="checkbox" name="sync" value="1"> 修改联系方式同步到职位</label>
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="item_label">&nbsp;</div>
							<div class="item_val">
								<div class="btn_blue J_hoverbut btn_115_38" id="save_info">保存</div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				
			</div>
			<div class="clear"></div>
		</div>
	</body>
	<div class="clear"></div>
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
  <?php if(C('qscms_login_com_audit_mobile') == 1 and $visitor['utype'] == 1 and $visitor['mobile'] == ''): ?>var authMobileDialog = $(this).dialog({
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
	<script type="text/javascript" src="../public/js/emailAutoComplete.js"></script>
	<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=<?php echo C('qscms_map_ak');?>"></script>
	<script type="text/javascript" src="https://api.map.baidu.com/library/DrawingManager/1.4/src/DrawingManager_min.js"></script>
	<script src="../public/js/ajaxfileupload.js" type="text/javascript" language="javascript"></script>
	<script src="../public/js/qscms.js" type="text/javascript" language="javascript"></script>
	<script type="text/javascript" src="../public/js/company/jquery.baidumap.js"></script>
	<script type="text/javascript">
		var comid = "<?php echo ($company_profile['id']); ?>";
		$.upload("#hidden_file",{company_id:comid,type:'company_logo'},function(result){
			$("#logo_res").attr("src",result.data.path);
			if(result.data.points){
				disapperTooltip("goldremind", '上传logo增加'+result.data.points+'<?php echo C('qscms_points_byname');?><span class="point">+'+result.data.points+'</span>');
			}else{
				disapperTooltip('success',result.msg);
			}
			$('.clear_logo').show();
		});
		$(document).ready(function(){
			$('.clear_logo').die().live('click',function(){
				var qsDialog = $(this).dialog({
	        		title: '系统提示',
	        		content: '企业logo代表了企业的品牌形象，确定要清除企业logo吗？',
					yes: function() {
						$.getJSON("<?php echo U('clear_logo');?>",function(result){
							if(result.status==1){
								$("#logo_res").attr("src","<?php echo attach('no_logo.png','resource');?>");
								$('.clear_logo').hide();
							}
						});
					}
				});
			});
			/* 保存企业基本资料 */
			var regularMobile = qscms.regularMobile; // 验证手机号
			var regularEmail = /^[_\.0-9a-zA-Z-]+[_0-9a-zA-Z-]@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$/; // 验证邮箱
			var regularTelFirst = /^[0-9]{3}[0-9]?$/; // 验证区号
			var regularTelNext = /^[0-9]{6,11}$/; // 验证电话号码
			var regularTelLast = /^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/; // 验证分机号码
			var regularWebsite = /^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/; // 验证企业网址
			var regularQQ = /^[1-9][0-9]{4,}$/; // 验证QQ
			$("#save_info").die().live('click',function(){
	            var that = $(this);
	            if(that.hasClass('disabled')){
	                return false;
	            }
				var companyname = $.trim($("input[name=companyname]").val());
				var short_name = $.trim($("input[name=short_name]").val());
				var nature = $.trim($("input[name=nature]").val());
				var trade = $.trim($("input[name=trade]").val());
				var scale = $.trim($("input[name=scale]").val());
				var district = $.trim($("input[name=district]").val());
				var registered = $.trim($("input[name=registered]").val());
				var currency = $.trim($("input[name=currency]").val());
				var website = $.trim($("input[name=website]").val());
				var tag = $.trim($("input[name=tag]").val());
				var short_desc = $.trim($("input[name=short_desc]").val());
				var contents = $.trim($("textarea[name=contents]").val());
				var contact = $.trim($("input[name=contact]").val());
				var telephone = $.trim($("input[name=telephone]").val());
				var landline_tel_first = $.trim($("input[name=landline_tel_first]").val());
				var landline_tel_next = $.trim($("input[name=landline_tel_next]").val());
				var landline_tel_last = $.trim($("input[name=landline_tel_last]").val());
				var email = $.trim($("input[name=email]").val());
				var address = $.trim($("input[name=address]").val());
				var qq = $.trim($("input[name=qq]").val());
				var map_x = $.trim($("input[name=map_x]").val());
				var map_y = $.trim($("input[name=map_y]").val());
				var map_zoom = $.trim($("input[name=map_zoom]").val());
				var sync = $("input[name=sync]").is(":checked")?1:0;
				var contact_show = $("input[name=contact_show]").is(":checked")?0:1;
				var telephone_show = $("input[name=telephone_show]").is(":checked")?0:1;
				var landline_tel_show = $("input[name=landline_tel_show]").is(":checked")?0:1;
				var email_show = $("input[name=email_show]").is(":checked")?0:1;
				var id = "<?php echo ($company_profile['id']); ?>";
				if (companyname == "") {
					disapperTooltip("remind", "请填写企业名称");
					return false;
				}
				if (nature == "0") {
					disapperTooltip("remind", "请选择企业性质");
					return false;
				}
				if (trade == "0") {
					disapperTooltip("remind", "请选择所属行业");
					return false;
				}
				if (scale == "0") {
					disapperTooltip("remind", "请选择企业规模");
					return false;
				}
				if (district == "0") {
					disapperTooltip("remind", "请选择所在地区");
					return false;
				}
				if (registered != "" && !regularTelLast.test(registered)) {
					disapperTooltip("remind", "注册资金应为数字");
					return false;
				}
				if (registered != "" && !regularTelLast.test(registered) || registered.length > 5) {
					disapperTooltip("remind", "注册资金不能超出5位数");
					return false;
				}
				if (website != "" && !regularWebsite.test(website)) {
					disapperTooltip("remind", "企业网址格式不正确");
					return false;
				}
				if (contents == "") {
					disapperTooltip("remind", "请填写企业介绍");
					return false;
				}
				if (contact == "") {
					disapperTooltip("remind", "请填写联系人");
					return false;
				}
				if (contact != "" && contact.length > 10) {
					disapperTooltip("remind", "联系人1-10个字");
					return false;
				}
				if(landline_tel_next=="" && telephone=="") {
					disapperTooltip("remind", "请填写联系手机或座机");
					return false;
				} else {
					if (telephone != "" && !regularMobile.test(telephone)) {
						disapperTooltip("remind", "手机号格式不正确");
						return false;
					}
					if (landline_tel_first != "" && !regularTelFirst.test(landline_tel_first)) {
						disapperTooltip("remind", "请填写正确的区号");
						return false;
					}
					if (landline_tel_next != "" && !regularTelNext.test(landline_tel_next)) {
						disapperTooltip("remind", "电话号码为6-11位数字");
						return false;
					}
					if (landline_tel_last != "" && !regularTelLast.test(landline_tel_last)) {
						disapperTooltip("remind", "分机号码为数字");
						return false;
					}
					if (landline_tel_last != "" && !regularTelLast.test(landline_tel_last) || landline_tel_last.length > 4) {
						disapperTooltip("remind", "分机号码不能超出4位");
						return false;
					}
				}
				if (email != "" && !regularEmail.test(email) || email.split("@")[0].length > 20) {
					disapperTooltip("remind", "邮箱格式不正确");
					return false;
				}
				if (qq != "" && !regularQQ.test(qq)) {
					disapperTooltip("remind", "请填写正确格式的QQ");
					return false;
				}
				if (address == "") {
					disapperTooltip("remind", "联系地址不能为空");
					return false;
				}
				if (address != "" && address.length > 30) {
					disapperTooltip("remind", "联系地址不能大于30个字");
					return false;
				}
				$(this).html('正在保存...');
				$(this).addClass('btn_gray9');
				$(this).removeClass('btn_blue');
				that.addClass('disabled');
				$.post("<?php echo U('company/com_info');?>",{id:id,companyname:companyname,short_name:short_name,nature:nature,trade:trade,scale:scale,registered:registered,currency:currency,district:district,website:website,tag:tag,short_desc:short_desc,contents:contents,contact:contact,telephone:telephone,landline_tel_first:landline_tel_first,landline_tel_next:landline_tel_next,landline_tel_last:landline_tel_last,email:email,address:address,map_x:map_x,map_y:map_y,map_zoom:map_zoom,sync:sync,contact_show:contact_show,telephone_show:telephone_show,landline_tel_show:landline_tel_show,email_show:email_show,qq:qq},function(r){
					if(r.status==1){
						var jump_auth = parseInt("<?php echo ($jump_certificate); ?>");
						if(jump_auth){
							if(r.data.points){
								disapperTooltip("goldremind", '完善企业资料增加'+r.data.points+'<?php echo C('qscms_points_byname');?><span class="point">+'+r.data.points+'</span>');
							}else{
								disapperTooltip('success',r.msg);
							}
							setTimeout(function () {
		                        location.href="<?php echo U('com_auth');?>";
		                    }, 2000);
						}else{
							var qsDialogTip = $(this).dialog({
								title: '企业资料',
								footer: false,
                                loading: true
							});
                            qsDialogTip.setContent(r.data.html);
						}
					}else{
						disapperTooltip('remind',r.msg);
					}
					$("#save_info").html('保存');
					$("#save_info").addClass('btn_blue');
					$("#save_info").removeClass('btn_gray9');
					that.removeClass('disabled');
				},'json');
			});

			// 修改手机
			$('#J_auth_mobile').click(function(){
				var f = $(this);
				var qsDialog = $(this).dialog({
	        		loading: true,
					footer: false,
					header: false,
					border: false,
					backdrop: false
				});
				$.getJSON("<?php echo U('members/user_mobile');?>",function(result){
		    		if(result.status == 1){
		    			qsDialog.hide();
		    			var qsDialogSon = $(this).dialog({
			        		title: '修改已认证手机',
			        		content: result.data,
							yes: function() {
								var verifycode  = $.trim($('#J_mobileWrap input[name="verifycode"]').val());
								if(!verifycode){
									$('#J_mobileWrap .J_errbox').text('请填写验证码！').show();
									return false;
								}
								$.post("<?php echo U('members/verify_mobile_code');?>",{verifycode:verifycode},function(result){
									if(result.status == 1){
										f.prev().val(result.data.mobile);
										qsDialogSon.hide();
										if(result.data.points){
											disapperTooltip("goldremind", '验证手机号增加'+result.data.points+'<?php echo C('qscms_points_byname');?><span class="point">+'+result.data.points+'</span>');
										}else{
											disapperTooltip('success',result.msg);
										}
									}else{
										$('#J_mobileWrap .J_errbox').text(result.msg).show();
									}
								},'json');
							}
						});
						qsDialogSon.setCloseDialog(false);
		    		} else {
		    			qsDialog.hide();
		    			disapperTooltip('remind',result.msg);
		    		}
		    	});
			});
		
			// 修改联系邮箱
			$('#J_auth_email').click(function(){
				var f = $(this);
				var qsDialog = $(this).dialog({
		    		loading: true,
					footer: false,
					header: false,
					border: false,
					backdrop: false
				});
				$.getJSON("<?php echo U('members/user_email');?>",function(result){
		    		if(result.status == 1){
		    			qsDialog.hide();
						var qsDialogSon = $(this).dialog({
			        		title: '修改已认证邮箱',
							content: result.data
						});
		    		}else{
		    			qsDialog.hide();
    					disapperTooltip('remind',result.msg);
		    		}
		    	});
			});

			// 百度地图
			baidumap("<?php echo ($company_profile["companyname"]); ?>","<?php echo ($company_profile["address"]); ?>","<?php echo ($company_profile["map_x"]); ?>","<?php echo ($company_profile["map_y"]); ?>","<?php echo ($company_profile["map_zoom"]); ?>","<?php echo C('qscms_map_center_x');?>","<?php echo C('qscms_map_center_y');?>","<?php echo C('qscms_map_zoom');?>");
		});
	</script>
</html>