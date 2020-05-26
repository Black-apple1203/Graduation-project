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
	<link href="../public/css/company/company_index.css?v=<?php echo time();?>" rel="stylesheet" type="text/css" />
	<link href="../public/css/common_ajax_dialog.css" rel="stylesheet" type="text/css" />
	<link href="../public/css/company/company_ajax_dialog.css" rel="stylesheet" type="text/css" />
	<script src="../public/js/company/jquery.common.js" type="text/javascript" language="javascript"></script>
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
<div class="index_main">
	<div class="ileft">
		<div class="hellow">
			<div class="tit"><span><?php echo ($am_pm); ?>，亲爱的HR</span><br />今天是<?php echo date('Y年m月d日',time());?>&nbsp;&nbsp;&nbsp;星期<?php echo ($week[date('w')]); ?></div>
			<div class="td1">
				<div class="mygold link_yellow"><?php echo C('qscms_points_byname');?>：&nbsp;<a href="<?php echo U('companyService/points');?>"><span class="my_points_num"><?php echo ($points); ?></span></a></div>
				<div class="link_blue"><a href="<?php echo U('companyService/points');?>">做任务赚<?php echo C('qscms_points_byname');?></a></div>
			</div>
			<div class="td2">
				<div id="J_sign_in" class="J_hoverbut <?php if($issign): ?>btn_lightgray<?php else: ?>btn_yellow<?php endif; ?>"><?php if($issign): ?>已签到<?php else: ?>未签到<?php endif; ?></div>
			</div>
			<div class="clear"></div>
			<div class="btnbox link_gray6">
				<?php if($upper_limit != 1): ?><a href="<?php echo U('jobs_add');?>" class="btn1">发布职位</a>
				<?php else: ?>
					<a href="javascript:;" class="J_addJobsDig btn1">发布职位</a><?php endif; ?>
				<a href="javascript:;" class="btn2" id="refresh_jobs_all">刷新职位</a>
				<a target="_blank" href="<?php echo url_rewrite('QS_resumelist');?>" class="btn3">搜索简历</a>
			</div>
		</div>
		<?php if($setmeal): ?><div class="vip">
				<?php if($setmeal['setmeal_id'] == 1): ?><div class="tit link_gray6">
						我的服务：<a href="<?php echo U('companyService/index');?>" class="vipname"><?php echo ($setmeal["setmeal_name"]); ?></a><br/>
						<span><a target="_blank" href="<?php echo U('CompanyService/explain');?>" target="_blank">VIP会员享受更多招聘特权</a></span>
					</div>
					<div class="open">
						<a class="openbtn J_hoverbut"  href="<?php echo U('companyService/index');?>">开通VIP会员</a>
					</div>
				<?php else: ?>
					<div class="tit link_gray6">
						我的服务：<a href="<?php echo U('companyService/index');?>"><?php echo ($setmeal["setmeal_name"]); ?></a><br/>
						<span>
							(<?php echo date('Y-m-d',$setmeal['starttime']);?> 至 <?php if(($setmeal['endtime']) == "0"): ?>--<?php else: echo date('Y-m-d',$setmeal['endtime']); endif; ?>)
						</span>
						<div class="btnbox">
						    <div class="viewbtn J_hoverbut" onclick="window.location='<?php echo U('jobs_list');?>'">可发布职位：<strong title="<?php echo ($surplus_jobs_num); ?>"><?php echo ($surplus_jobs_num); ?></strong></div>
							<div class="viewbtn btn2 J_hoverbut" onclick="window.location='<?php echo U('resume_down');?>'">剩余简历点：<strong title="<?php echo ($setmeal['download_resume']); ?>"><?php echo ($setmeal['download_resume']); ?></strong></div>
						</div>
					</div><?php endif; ?>
			</div><?php endif; ?>
		<!-- 客服-->
		<?php if(!empty($consultant)): ?><div class="service">
				<div class="tit">专属客服</div>
				<div class="pic">
					<div class="td1"><img src="<?php echo attach($consultant['pic'],'consultant');?>"  width="70"  height="70"  border="0"/></div>
					<div class="td2">
						<div class="name"><?php echo ($consultant["name"]); ?></div>
							<a target="blank" href="tencent://message/?uin=<?php echo ($consultant["qq"]); ?>&Site=menu&Menu=yes"><img border="0" SRC=http://wpa.qq.com/pa?p=1:<?php echo ($consultant["qq"]); ?>:1 alt="点击这里给我发消息"></a>
						</div>
					<div class="clear"></div>
				</div>
				<?php if($consultant['mobile'] || $consultant['tel']): ?><div class="tel">
					<?php if($consultant['mobile']): ?>手机：<?php echo ($consultant["mobile"]); ?><br /><?php endif; ?>
					<?php if($consultant['tel']): ?>电话：<?php echo ($consultant["tel"]); endif; ?>
				</div><?php endif; ?>
				<div class="btnbox">
					<div class="J_hoverbut btn_complaint">投诉TA</div>
				</div>
			</div><?php endif; ?>
	</div>
	<div class="iright">
		<?php if(!$cominfo_flge): ?><div class="cominfotip">
				<div class="td1">
					<div class="t">贵公司的资料还未填写完整，可能会影响您的招聘效果哦！</div>
					<div class="v">完善公司基本信息、联系方式、上传营业执照…等，可以有效提高招聘成功率	 </div> 
				</div>
				<div class="td2">
					<a href="<?php echo U('com_info');?>" class="btn_yellow J_hoverbut btn_100_38 btn_border">马上完善资料</a>
					<div class="J_companyinfo closs J_hoverbut" title="关闭"></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php else: ?>
			<?php if($comgift_flge): ?><div class="comgifttip">
				<div class="td1">
					<div class="t">恭喜您获得<?php echo ($giftcount); ?>张会员优惠券，请及时使用哦！</div>
					<div class="v">使用会员优惠券可以更低价获得招聘特权，会员有效期内享受增值服务专享折扣！</div> 
				</div>
				<div class="td2">
					<a href="<?php echo U('CompanyService/gifts');?>" class="btn_yellow J_hoverbut btn_100_38 btn_border">立即查看</a>
					<div class="J_companygift closs J_hoverbut" title="关闭"></div>
				</div>
				<div class="clear"></div>
			</div><?php endif; endif; ?>
		
			
		
		<div class="i_main">
			<div class="comlogo">
				<div class="log link_blue">
		        	上次登录：<?php if(($visitor['last_login_time']) == "0"): ?>从未登录<?php else: echo date('Y-m-d H:i',$visitor['last_login_time']); endif; ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo U('user_loginlog');?>">[查看登录日志]</a>
		        </div>
				<div class="td1">
					<div class="logobox">
						<a href="<?php echo U('com_info');?>"><img src="<?php if($company_profile['logo']): echo attach($company_profile['logo'],'company_logo'); else: echo attach('no_logo.png','resource'); endif; ?>?<?php echo time();?>" width="120" height="120" border="0" /></a>
					</div>
				</div>
				<div class="td2">
					<?php if($company_profile['companyname']): ?><div class="comname link_blue substring">
							<?php echo ($company_profile["companyname"]); ?> <a href="<?php echo U('com_info');?>">编辑</a>
						</div><?php endif; ?>
	  		  		<?php if($company_profile['nature']): ?><div class="txt_line mt8 link_blue"><?php echo ($company_profile["nature_cn"]); ?> | <?php echo ($company_profile["trade_cn"]); ?> | <?php echo ($company_profile["scale_cn"]); ?> | <?php echo ($company_profile["district_cn"]); ?></div>
	  		  		<?php else: ?>
	  		  		<div class="txt_line mt8 link_blue">完善企业基本资料是招聘的第一步，立即 <a href="<?php echo U('com_info');?>">完善资料</a></div><?php endif; ?>
	  		  		<?php if($tag_arr): ?><div class="tag_group mt14">
					<?php if(is_array($tag_arr)): $i = 0; $__LIST__ = $tag_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="group_cell"><div class="tag_cell"><?php echo ($vo); ?></div></div><?php endforeach; endif; else: echo "" ;endif; ?>
	  		  		<div class="clear"></div>
	  		  		</div>
	  		  		<?php else: ?>
					<div class="txt_line mt17 link_blue"><a href="<?php echo U('com_info');?>">添加企业福利</a>，让职位更有魅力</div><?php endif; ?>
			        <div class="checkbox mt15 link_blue">
						<!--已经认证的叠加css  ok -->
						<?php if($company_profile['audit'] == 1): ?><a href="<?php echo U('com_auth');?>" class="btns btn1 ok">企业已认证</a>
						<?php else: ?>
							<a href="<?php echo U('com_auth');?>" class="btns btn1">企业未认证</a><?php endif; ?>
						<?php if(!empty($wx_status)): if($weixin_bind): ?>|<a href="<?php echo U('user_security');?>" class="btns btn4 ok">微信已认证</a>
							<?php else: ?>
								|<a href="<?php echo U('user_security');?>" class="btns btn4 J_bind_wx">微信未认证</a><?php endif; endif; ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
	        <div class="counts">
				<div class="cli">
					<div class="item J_hoverbut" onclick="window.location='<?php echo U('jobs_list');?>'">
						<div class="n"><?php echo ($total_audit_jobs); ?></div>
						<div class="t">招聘中的职位</div>
					</div>
				</div>
				<div class="cli">
					<div class="item J_hoverbut" onclick="window.location='<?php echo U('jobs_apply');?>'">
						<div class="n"><?php echo ($total_nolook_resume); ?></div>
						<div class="t">待处理简历</div>
					</div>
				</div>
				<div class="cli">
					<div class="item J_hoverbut" onclick="window.location='<?php echo U('jobs_interview');?>'">
						<div class="n"><?php echo ($total_interview); ?></div>
						<div class="t">面试邀请</div>
					</div>
				</div>
				<div class="cli">
					<div class="item J_hoverbut" onclick="window.location='<?php echo U('company/jobs_viewlog');?>'">
						<div class="n"><?php echo ($total_view); ?></div>
						<div class="t">谁在关注我</div>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="data">
				<div class="titbg">
					<div class="btns">
						<div class="select_input input_140_30_div J_hoverinput J_dropdown J_listitme_parent">
							<span class="J_listitme_text">
								访客统计
							</span>
							<div class="dropdowbox12 J_dropdown_menu">
					            <div class="dropdow_inner12">
					                <ul class="nav_box">
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="type" data_val="visitor">访客统计</a></li>
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="type" data_val="viewjobs">职位浏览</a></li>
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="type" data_val="apply">应聘统计</a></li>
					                </ul>
					            </div>
					        </div>
					        <input class="J_listitme_code statistics_type" name="" type="hidden" value="visitor" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="btns1">
						<div class="select_input input_140_30_div J_hoverinput J_dropdown J_listitme_parent">
							<span class="J_listitme_text">
								最近7天
							</span>
							<div class="dropdowbox12 J_dropdown_menu">
					            <div class="dropdow_inner12">
					                <ul class="nav_box">
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="settr" data_val="7">最近7天</a></li>
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="settr" data_val="15">最近15天</a></li>
					                	<li><a class="J_listitme statistics_select" href="javascript:;" data_k="settr" data_val="30">最近30天</a></li>
					                </ul>
					            </div>
					        </div>
					        <input class="J_listitme_code statistics_settr" name="" type="hidden" value="7" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="more link_blue"><a href="<?php echo U('company/statistics_visitor');?>">查看详细>></a></div>
					<div class="clear"></div>
				</div>
				<div class="info statistics_wrap">暂无数据...</div>
			</div>
			<div class="resumetit">
			  		<div class="lt">推荐简历</div>
					<div class="rt link_blue"><a href="javascript:;" class="more J_refresh" data-ajaxtype="recommend_jobs" ajaxpage="2">换一批</a></div>
					<div class="clear"></div>
			</div>
	        <div class="resumelist">
	        	<?php if(!empty($resume_list)): if(is_array($resume_list)): $i = 0; $__LIST__ = $resume_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$resume): $mod = ($i % 2 );++$i;?><div class="li">
							<div class="bg J_hoverbut">
							 	<div class="photobox">
							 		<a target="_blank" href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>">
							 			<img  border="0" src="<?php echo ($resume['photosrc']); ?>" />
							 		</a>
							 	</div>
								<div class="info">
									  <div class="name link_blue"><a target="_blank" href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>"><?php echo ($resume["fullname"]); ?></a></div>
									  <div class="time"><?php echo date('Y-m-d',$resume['refreshtime']);?></div>
									  <div class="clear"></div>
								      <div class="txt"><?php echo ($resume['sex_cn']); ?> | <?php echo ($resume['age']); ?>岁 | <?php echo ($resume['education_cn']); ?> | <?php echo ($resume['experience_cn']); ?> <br /><div class="substring"><?php echo ($resume['intention_jobs']); ?></div> </div>
								</div>
								<div class="clear"></div>
							</div>
						</div><?php endforeach; endif; else: echo "" ;endif; ?>
				<?php else: ?>
					<div class="res_empty link_blue">
						抱歉，没有找到相关简历，请<a href="<?php echo U('jobs_add');?>">发布职位</a>让求职者主动投递简历。
					</div><?php endif; ?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>
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
<script type="text/javascript" src="../public/js/company/fusioncharts/fusioncharts.js"></script>
<script type="text/javascript" src="../public/js/company/fusioncharts/fusioncharts.theme.fint.js"></script>
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
	$(document).ready(function(){
		$(".J_bind_wx").click(function(){
			$('#J_popWeixin').show();
		});
		// 关闭微信绑定提示
        $('#J_popWeixin .close').on('click', function(){
            $('#J_popWeixin').hide();
        });
        $('.J_companyinfo').click(function(){
        	$(this).closest('.cominfotip').remove();
        });
        $('.J_companygift').click(function(){
        	$(this).closest('.comgifttip').remove();
        });
		$('#J_sign_in').click(function(){
			var f = $(this);
	    	$.getJSON("<?php echo U('members/sign_in');?>",function(result){
	    		if(result.status == 1){
	    			disapperTooltip("goldremind", '每天签到增加'+result.data+'<?php echo C('qscms_points_byname');?><span class="point">+'+result.data+'</span>');
					f.addClass('btn_gray9').text('已签到');
	    			$(".my_points_num").html(parseInt($(".my_points_num").html())+parseInt(result.data));
	    		}else{
	    			disapperTooltip('remind',result.msg);
	    		}
	    	});
	    });
		get_statistics_data();
		$(".statistics_select").click(function(){
			var data_k = $(this).attr('data_k');
			var data_val = $(this).attr('data_val');
			$(".statistics_"+data_k).val(data_val);
			get_statistics_data();
		});
		function get_statistics_data(){
			var type = $(".statistics_type").val();
			var settr = $(".statistics_settr").val();
			$.getJSON("<?php echo U('company/ajax_get_statistics');?>",{type:type,settr:settr},function(result){
				$(".statistics_wrap").html(result.data);
			});
		}
        var isDone = true; // 防止重复点击
		// 换一批
        $('.J_refresh').on('click', function(event) {
        	var obj = $(this);
        	var ajaxtype = obj.data('ajaxtype');
        	var ajaxpage = parseInt(obj.attr('ajaxpage')); // 记录页数
        	if (isDone) {
        		isDone = false;
	        	$.getJSON("<?php echo U('company/ajax_get_interest_resume');?>", {type: ajaxtype, p: ajaxpage}, function(data) {
		        		$('.resumelist').html(data.data.html);
		        		if(data.data.page==ajaxpage){
		        			obj.attr('ajaxpage', 1);
		        		}else{
		        			obj.attr('ajaxpage', ajaxpage+1);
		        		}
		        		isDone = true;
	        	});
        	};
        });
        $("#refresh_jobs_all").click(function(){
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
		<?php if($confirm_setmeal == 1): ?>var confirmDialog = $(this).dialog({
        		title: '温馨提示',
				loading: true,
				btns: ['立即缴费', '残忍拒绝'],
				yes: function () {
                    location.href="<?php echo U('companyService/index');?>";
                }
			});
			$.getJSON("<?php echo U('company/confirm_setmeal');?>",function(result){
				confirmDialog.setContent(result.data.html);
			});<?php endif; ?>
	});
</script>
</body>
</html>