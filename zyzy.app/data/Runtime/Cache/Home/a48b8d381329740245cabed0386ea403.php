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
		<link href="../public/css/company/company_points.css" rel="stylesheet" type="text/css" />
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
		<div class="user_main">
			<div class="mleft">
				<div class="left_jobs">
  <div class="li link_gray6 J_hoverbut t11 <?php if($left_nav == 'setmeal'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('CompanyService/index');?>'"><a href="<?php echo U('CompanyService/index');?>">我的套餐</a></div>
  <div class="li link_gray6 J_hoverbut t12 <?php if($left_nav == 'points'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('CompanyService/points');?>'"><a href="<?php echo U('CompanyService/points');?>">我的<?php echo C('qscms_points_byname');?></a></div>
  <div class="li link_gray6 J_hoverbut t13 <?php if($left_nav == 'increment'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('CompanyService/increment');?>'"><a href="<?php echo U('CompanyService/increment');?>">增值服务</a></div>
  <div class="li link_gray6 J_hoverbut t14 <?php if($left_nav == 'order_list'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('CompanyService/order_list');?>'"><a href="<?php echo U('CompanyService/order_list');?>">我的订单</a></div>
  <div class="li link_gray6 J_hoverbut t15 <?php if($left_nav == 'company_tpl'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('company/company_tpl');?>'"><a href="<?php echo U('company/company_tpl');?>">模板切换</a></div>
  <?php if(!empty($apply['Rpo'])): ?><div class="li link_gray6 J_hoverbut t23 <?php if($left_nav == 'rpo'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('Home/CompanyService/rpo');?>'"><a href="<?php echo U('Home/CompanyService/rpo');?>">招聘外包</a></div><?php endif; ?>
  <?php if(C('qscms_open_give_gift') == 1): ?><div class="li link_gray6 J_hoverbut t25 <?php if($left_nav == 'gifts'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('CompanyService/gifts');?>'"><a href="<?php echo U('CompanyService/gifts');?>">优惠券<span style="color:red">(<?php echo ($gift_issue_count); ?>)<span></a></div><?php endif; ?>
  <div class="li link_gray6 J_hoverbut t25 <?php if($left_nav == 'subject_list'): ?>select<?php endif; ?>" onclick="window.location='<?php echo U('Company/subject_list');?>'"><a href="<?php echo U('Company/subject_list');?>">网络招聘会</a></div>
  <!-- -->
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
 <!-- -->
</div>


			</div>
			<div class="mright">
				<!-- 任务 -->
				<div class="gold_top_gold">
					<div class="gold w276">
						<div class="rightbox">
							<div class="txt">
								<a class="name" title="<?php echo ($company_profile['companyname']); ?>"><?php echo ($company_profile['companyname']); ?></a>
								<div class="imgbox">
									<?php if(($$company_profile['audit']) == "1"): ?><div class="company_v"></div><?php endif; ?>
									<?php if($my_setmeal['id'] > 0): ?><div class="company_vip"><img src="<?php echo attach($my_setmeal['id'].'.png','setmeal_img');?>" alt="<?php echo ($my_setmeal['setmeal_name']); ?>" /></div><?php endif; ?>
									<?php if(($$company_profile['famous']) == "1"): ?><div class="company_bao"></div><?php endif; ?>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
							<div class="txt link_blue"><span class="gray">剩余<?php echo C('qscms_points_byname');?>：</span><strong><?php echo ($my_points); ?></strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo U('points_detail');?>"><?php echo C('qscms_points_byname');?>收支明细</a></div>
						</div>
						<div class="clear"></div>
						
					</div>
					<div class="todaygold">
						<div class="txt">今天已获得 <strong><?php echo ($points_count['obtain']); ?></strong>  <?php echo C('qscms_points_byname');?></div>
						<div class="txt link_blue">您还可免费获得 <strong><?php echo ($points_count['able']); ?></strong> <?php echo C('qscms_points_byname');?></div>
					</div>
					<div class="btnbox w180">
						<?php if(C('qscms_enable_com_buy_points') == 1): ?><a class="btn3" href="<?php echo U('points_add');?>"><?php echo C('qscms_points_byname');?>充值</a><?php endif; ?>
						<?php if(!empty($apply['Mall'])): ?><a class="btn2" target="_blank" href="<?php echo url_rewrite('QS_mall_index');?>"><?php echo C('qscms_points_byname');?>商城</a><?php endif; ?>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="thtab virtual_currency">
					<div class="li J_hoverbut select">未完成任务</div>
					<div class="li J_hoverbut">已完成任务</div>
				  	<div class="clear"></div>
				</div>
				<div class="task_th">
					<div class="th1">任务标题</div>
					<div class="th2"><?php echo C('qscms_points_byname');?></div>
					<div class="th3">类型</div>
					<div class="th4">操作</div>
					<div class="clear"></div>
				</div>
				<div class="tabshow" style="display:block;">
					<?php if(is_array($task)): $i = 0; $__LIST__ = $task;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(!isset($done_task[$vo['id']]) || $done_task[$vo['id']] > 0): ?><div class="task J_hoverbut">
						<div class="td1 <?php echo ($vo['t_alias']); ?>">
						<?php echo ($vo['title']); ?>
						<?php if($done_task[$vo['id']] > 0 && $vo['once'] == '0' && $vo['times'] > $done_task[$vo['id']]): ?>(<?php echo ($done_task[$vo['id']]); ?> / <?php echo ($vo['times']); ?>)<?php endif; ?>
						</div>
						<div class="td2">
							+ <?php echo ($vo['points']); ?>
							<?php if($done_task[$vo['id']] > 0 && $vo['once'] == '0' && $vo['times'] > $done_task[$vo['id']]): ?><span>( x<?php echo ($done_task[$vo['id']]); ?> )</span><?php endif; ?>
						</div>
						<div class="td3"><?php if($vo['once'] == '1'): ?>一次性任务<?php else: ?>日常任务<?php endif; ?></div>
						<div class="td4 link_blue"><a data-type="<?php echo ($vo['t_alias']); ?>" <?php if($task_url[$vo['t_alias']]['type'] == 'blank'): ?>target="_blank"<?php elseif($task_url[$vo['t_alias']]['type'] == 'ajax'): ?>data="<?php echo ($vo['t_alias']); ?>" class="ajax_do"<?php else: endif; ?> href="<?php echo ($task_url[$vo['t_alias']]['url']); ?>">去完成</a></div>
						<div class="clear"></div>
					</div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
				</div>
				<div class="tabshow">
					<?php if(is_array($task)): $i = 0; $__LIST__ = $task;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(isset($done_task[$vo['id']]) && ($done_task[$vo['id']] == $vo['times'] || $vo['id'] == 18)): ?><div class="task J_hoverbut">
						<div class="td1 <?php echo ($vo['t_alias']); ?>"><?php echo ($vo['title']); ?></div>
						<div class="td2">+ <?php echo ($vo['points']); ?></div>
						<div class="td3"><?php if($vo['once'] == '1'): ?>一次性任务<?php else: ?>日常任务<?php endif; ?></div>
						<div class="td4 ok">已完成</div>
						<div class="clear"></div>
					</div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
				</div>
				<!-- 任务 end -->
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
		<script src="../public/js/company/jquery.zclip.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$('.thtab .li').click(function(event) {
				$(this).addClass('select').siblings().removeClass('select');
				var positionValue = $('.thtab .li').index(this);
				$('.tabshow').eq(positionValue).show().siblings('.tabshow').hide();
			});
			$(".ajax_do").click(function(){
				var type = $(this).attr('data-type');
				var url = $(this).attr('href');
				if(type=='sign'){
					$.getJSON(url,function(result){
			    		if(result.status == 1){
			    			disapperTooltip("goldremind", '每天签到增加'+result.data+'<?php echo C('qscms_points_byname');?><span class="point">+'+result.data+'</span>');
			    			setTimeout(function () {
								window.location.reload();
							}, 2000);
			    		}else{
			    			disapperTooltip('remind',result.msg);
			    		}
			    	});
				}
				return false;
			});
		</script>
	</body>
</html>