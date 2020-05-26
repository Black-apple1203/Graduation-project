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
	<link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/common_ajax_dialog.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo C('TPL_PUBLIC_DIR');?>/css/resume.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.common.js" type="text/javascript" language="javascript"></script>
	<?php $tag_load_class = new \Common\qscmstag\loadTag(array('type'=>'category','search'=>'1','cache'=>'0','列表名'=>'list',));$list = $tag_load_class->category();?>
	<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'city','类型'=>'QS_citycategory','地区分类'=>_I($_GET['citycategory']),'显示数目'=>'100','cache'=>'0','type'=>'run',));$city = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$city);?>
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
                <?php $tag_nav_class = new \Common\qscmstag\navTag(array('列表名'=>'nav','调用名称'=>'QS_top','显示数目'=>'8','cache'=>'0','type'=>'run',));$nav = $tag_nav_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$nav);?>
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
<div class="new-search-box" style="background-image: url(<?php echo C('TPL_PUBLIC_DIR');?>/images/sebanner<?php echo rand(1,5);?>.jpg);">
    <div class="ns-main">
        <div class="main-sty">
            <?php if(C('qscms_resumesearch_key_first_choice') == 1): ?><div class="sty-cell J_sli <?php if($_GET['search_type']== 'precise' or $_GET['search_type']== ''): ?>select<?php endif; ?>" data-type="precise">精准<div class="sty-aow"></div></div>
                <div class="sty-cell J_sli <?php if($_GET['search_type']== 'full'): ?>select<?php endif; ?>" data-type="full">全文<div class="sty-aow"></div></div>
            <?php else: ?>
             	<div class="sty-cell J_sli <?php if($_GET['search_type']== 'full' or $_GET['search_type']== ''): ?>select<?php endif; ?>" data-type="full">全文<div class="sty-aow"></div></div>
                <div class="sty-cell J_sli <?php if($_GET['search_type']== 'precise'): ?>select<?php endif; ?>" data-type="precise">精准<div class="sty-aow"></div></div><?php endif; ?>
            <div class="clear"></div>
        </div>
        <div class="main-sip">
            <div class="ip-group">
                <form id="ajax_search_location" action="<?php echo U('ajaxCommon/ajax_search_location',array('type'=>'QS_resumelist'));?>" method="get">
                    <div class="ip-box"><input name="key" id="autoKeyInput" type="text" data-original="<?php echo (urldecode(urldecode(_I($_GET['key'])))); ?>" value="<?php echo (urldecode(urldecode(_I($_GET['key'])))); ?>" placeholder="请输入关键字" /></div>
                    <div class="for-border"></div>
                    <div class="ip-city" data-toggle="funCityModal" data-title="请选择地区" data-multiple="false" data-maximum="0" data-width="630"><?php if($_GET['citycategory']!= ''): echo ($city['parent']['categoryname']); else: ?>选择地区<?php endif; ?></div>
                    <input type="hidden" name="search_type" value="<?php echo (_I($_GET['search_type'])); ?>" />
                    <input id="searchCityModalCode" type="hidden" name="citycategory" value="<?php if($_GET['citycategory']!= ''): echo ($city['select']['citycategory']); endif; ?>" />
					<input id="recoverSearchCityModalCode" type="hidden" name="citycategory" value="<?php if($_GET['citycategory']!= ''): echo ($city['select']['citycategory']); endif; ?>" />
                    <input class="J_forclear" type="hidden" name="jobcategory" value="<?php if(C('qscms_resumesearch_key_open_jobcategory') == 1): echo (_I($_GET['jobcategory'])); endif; ?>" />
                    <input class="J_forclear" type="hidden" name="resumetag" value="<?php echo (_I($_GET['resumetag'])); ?>" />
                    <input class="J_forclear" type="hidden" name="experience" value="<?php echo (_I($_GET['experience'])); ?>" />
                    <input class="J_forclear" type="hidden" name="sex" value="<?php echo (_I($_GET['sex'])); ?>" />
                    <input class="J_forclear" type="hidden" name="age" value="<?php echo (_I($_GET['age'])); ?>" />
                    <input class="J_forclear" type="hidden" name="trade" value="<?php echo (_I($_GET['trade'])); ?>" />
                    <input class="J_forclear" type="hidden" name="major" value="<?php echo (_I($_GET['major'])); ?>" />
                    <input class="J_forclear" type="hidden" name="education" value="<?php echo (_I($_GET['education'])); ?>" />
                    <input class="J_forclear" type="hidden" name="nature" value="<?php echo (_I($_GET['nature'])); ?>" />
                    <input class="J_forclear" type="hidden" name="wage" value="<?php echo (_I($_GET['wage'])); ?>" />
                    <input class="J_forclear" type="hidden" name="settr" value="<?php echo (_I($_GET['settr'])); ?>" />
                    <div class="ip-btn"><input type="submit" class="sobut J_hoverbut" value="招人才" /></div>
                </form>
            </div>
            <div class="ip-txt link_white J_sub_s"><a href="<?php echo url_rewrite('QS_resume');?>">分类搜索</a></div>
            <div class="clear"></div>
        </div>
    </div>
</div>
<!--搜索 -->
<div class="so_condition J_so_condition">
    <?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'jobsCate','类型'=>'QS_jobs','cache'=>'0','type'=>'run',));$jobsCate = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$jobsCate);?>
    <?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'jobs_cate_info','类型'=>'QS_jobs_info','cache'=>'0','type'=>'run',));$jobs_cate_info = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$jobs_cate_info);?>
	<?php if(!empty($city['list'])): ?><div class="lefttit">地标地段</div>
		<div class="rs">
			<div onclick="javascript:location.href='<?php echo P(array('citycategory'=>$city['parent']['citycategory']));?>'" class="li <?php if($_GET['citycategory']!= '' and $city['parent']['id'] == $city['select']['id']): ?>select<?php endif; ?>">全<?php echo ($city['parent']['categoryname']); ?></div>
			<?php if(is_array($city['list'])): $i = 0; $__LIST__ = $city['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$district): $mod = ($i % 2 );++$i;?><div onclick="javascript:location.href='<?php echo P(array('citycategory'=>$district['citycategory']));?>'" class="li <?php if($city['select']['id'] == $key): ?>select<?php endif; ?>"><?php echo ($district['categoryname']); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
			<div class="clear"></div>
		</div>
		<div class="clear"></div><?php endif; ?>
	<div class="lefttit">工作经验</div>
	<div class="rs">
		<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'experience_list','类型'=>'QS_experience','显示数目'=>'100','cache'=>'0','type'=>'run',));$experience_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$experience_list);?>
		<div onclick="javascript:location.href='<?php echo P(array('experience'=>''));?>'" class="li <?php if($_GET['experience']== ''): ?>select<?php endif; ?>">不限</div>
		<?php if(is_array($experience_list)): $i = 0; $__LIST__ = $experience_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$experience): $mod = ($i % 2 );++$i;?><div onclick="javascript:location.href='<?php echo P(array('experience'=>$key));?>'" class="li <?php if($_GET['experience']== $key): ?>select<?php endif; ?>"><?php echo ($experience); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	 
	<div class="lefttit">简历标签</div>
	<div class="rs">
		<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'tag_list','类型'=>'QS_resumetag','显示数目'=>'100','cache'=>'0','type'=>'run',));$tag_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$tag_list);?>
		<div onclick="javascript:location.href='<?php echo P(array('resumetag'=>''));?>'" class="li <?php if($_GET['resumetag']== ''): ?>select<?php endif; ?>">不限</div>
		<?php if(is_array($tag_list)): $i = 0; $__LIST__ = $tag_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><div onclick="javascript:location.href='<?php echo P(array('resumetag'=>$key));?>'" class="li <?php if($_GET['resumetag']== $key): ?>select<?php endif; ?>"><?php echo ($tag); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>

	<div class="lefttit">更多筛选</div>
	<div class="rs">
		<div class="bli J_dropdown">
			<span class="txt">性别</span>
			<div class="dropdowbox_noa J_dropdown_menu">
	            <div class="dropdow_inner_noa">
	                <ul class="nav_box">
	                	<li onclick="javascript:location.href='<?php echo P(array('sex'=>1));?>'" class="<?php if($_GET['sex']== 1): ?>select<?php endif; ?>" data-code="1">男</li>
	                	<li onclick="javascript:location.href='<?php echo P(array('sex'=>2));?>'" class="<?php if($_GET['sex']== 2): ?>select<?php endif; ?>" data-code="2">女</li>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		
		<div class="bli J_dropdown">
			<span>年龄</span>
			<div class="dropdowbox_noa J_dropdown_menu">
	            <div class="dropdow_inner_noa">
	                <ul class="nav_box">
	                	<li onclick="javascript:location.href='<?php echo P(array('age'=>''));?>'" class="<?php if($_GET['age']== 24): ?>select<?php endif; ?>">全部</li>
	                	<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'age_list','类型'=>'QS_age','cache'=>'0','type'=>'run',));$age_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$age_list);?>
	                	<?php if(is_array($age_list)): $i = 0; $__LIST__ = $age_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$age): $mod = ($i % 2 );++$i;?><li onclick="javascript:location.href='<?php echo P(array('age'=>$key));?>'" class="<?php if($_GET['age']== $key): ?>select<?php endif; ?>"><?php echo ($age); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'trade_list','类型'=>'QS_trade','显示数目'=>'100','cache'=>'0','type'=>'run',));$trade_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$trade_list);?>
			<span class="txt" title="<?php echo (($trade_list[$_GET['trade']] != "")?($trade_list[$_GET['trade']]):'行业类别'); ?>"><?php echo (($trade_list[$_GET['trade']] != "")?($trade_list[$_GET['trade']]):'行业类别'); ?></span>
			<div class="dropdowbox_searchtrade J_dropdown_menu">
	            <div class="dropdow_inner_searchtrade">
	                <ul class="nav_box">
	                	<?php if(is_array($trade_list)): $i = 0; $__LIST__ = $trade_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$trade): $mod = ($i % 2 );++$i;?><li onclick="javascript:location.href='<?php echo P(array('trade'=>$key));?>'" class="<?php if($_GET['trade']== $key): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>" title="<?php echo ($trade); ?>"><?php echo ($trade); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
						<div class="clear"></div>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'major_list','类型'=>'QS_major','显示数目'=>'100','cache'=>'0','type'=>'run',));$major_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$major_list);?>
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'major_info','类型'=>'QS_major_info','id'=>_I($_GET['major']),'cache'=>'0','type'=>'run',));$major_info = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$major_info);?>
			<span class="txt" title="<?php echo (($major_list[$_GET['major']] != "")?($major_list[$_GET['major']]):'所学专业'); ?>"><?php echo (($major_list[$_GET['major']] != "")?($major_list[$_GET['major']]):'所学专业'); ?></span>
			<div class="dropdowbox_searchmajor J_dropdown_menu">
	            <div class="dropdow_inner_searchmajor">
	                <ul class="nav_box">
						<div class="tabnav">
							<?php if(is_array($major_list[0])): $i = 0; $__LIST__ = $major_list[0];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$major): $mod = ($i % 2 );++$i;?><div class="tab_list <?php if($major_info['parentid'] == $key): ?>select<?php endif; ?>"><?php echo ($major); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
                            <div class="clear"></div>
                        </div>
                        <?php if(is_array($major_list[0])): $i = 0; $__LIST__ = $major_list[0];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$smajor): $mod = ($i % 2 );++$i;?><div class="tab_content" data-code="<?php echo ($key); ?>">
	                            <?php if(is_array($major_list[$key])): $i = 0; $__LIST__ = $major_list[$key];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$major): $mod = ($i % 2 );++$i;?><div class="content_list">
	                                <li onclick="javascript:location.href='<?php echo P(array('major'=>$key));?>'" class="<?php if($_GET['major']== $key): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>"><?php echo ($major); ?></li>
	                                </div><?php endforeach; endif; else: echo "" ;endif; ?>
	                            <div class="clear"></div>
	                        </div><?php endforeach; endif; else: echo "" ;endif; ?>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<span>学历要求</span>
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'education_list','类型'=>'QS_education','显示数目'=>'100','cache'=>'0','type'=>'run',));$education_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$education_list);?>
			<div class="dropdowbox_noa J_dropdown_menu">
	            <div class="dropdow_inner_noa">
	                <ul class="nav_box">
	                	<?php if(is_array($education_list)): $i = 0; $__LIST__ = $education_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$education): $mod = ($i % 2 );++$i;?><li onclick="javascript:location.href='<?php echo P(array('education'=>$key));?>'" class="<?php if($_GET['education']== $key): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>"><?php echo ($education); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<span>工作性质</span>
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'nature_list','类型'=>'QS_jobs_nature','显示数目'=>'100','cache'=>'0','type'=>'run',));$nature_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$nature_list);?>
			<div class="dropdowbox_noa J_dropdown_menu">
	            <div class="dropdow_inner_noa">
	                <ul class="nav_box">
	                	<?php if(is_array($nature_list)): $i = 0; $__LIST__ = $nature_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nature): $mod = ($i % 2 );++$i;?><li onclick="javascript:location.href='<?php echo P(array('nature'=>$key));?>'" class="<?php if($_GET['nature']== $key): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>"><?php echo ($nature); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<span class="txt">期望薪资</span>
			<?php $tag_classify_class = new \Common\qscmstag\classifyTag(array('列表名'=>'wage_list','类型'=>'QS_wage','显示数目'=>'100','cache'=>'0','type'=>'run',));$wage_list = $tag_classify_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$wage_list);?>
			<div class="dropdowbox_searchwage J_dropdown_menu">
	            <div class="dropdow_inner_searchwage">
	                <ul class="nav_box">
	                	<?php if(is_array($wage_list)): $i = 0; $__LIST__ = $wage_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$wage): $mod = ($i % 2 );++$i;?><li onclick="javascript:location.href='<?php echo P(array('wage'=>$key));?>'" class="<?php if($_GET['wage']== $key): ?>select<?php endif; ?>" data-code="<?php echo ($key); ?>"><?php echo ($wage); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="bli J_dropdown">
			<span>更新时间</span>
			<div class="dropdowbox_noa J_dropdown_menu">
	            <div class="dropdow_inner_noa">
	                <ul class="nav_box">
	                	<li onclick="javascript:location.href='<?php echo P(array('settr'=>3));?>'" class="<?php if($_GET['settr']== 3): ?>select<?php endif; ?>" data-code="3">3天内</li>
	                	<li onclick="javascript:location.href='<?php echo P(array('settr'=>7));?>'" class="<?php if($_GET['settr']== 7): ?>select<?php endif; ?>" data-code="7">7天内</li>
	                	<li onclick="javascript:location.href='<?php echo P(array('settr'=>15));?>'" class="<?php if($_GET['settr']== 15): ?>select<?php endif; ?>" data-code="15">15天内</li>
	                	<li onclick="javascript:location.href='<?php echo P(array('settr'=>30));?>'" class="<?php if($_GET['settr']== 30): ?>select<?php endif; ?>" data-code="30">30天内</li>
	                </ul>
	            </div>
	        </div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>

	<!--已选条件，当没有条件的时候不显示下面的DIV -->
	<?php if($_GET): ?><div class="selected J_selected">
			<div class="stit">已选条件</div>
		    <div class="sc">
		    	<?php if($_GET['key']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('key'=>''));?>';"><span>关键字：</span><?php echo (urldecode(urldecode(_I($_GET['key'])))); ?></div><?php endif; ?>
				<?php if($_GET['jobcategory']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('jobcategory'=>''));?>';">
						<span>职位分类：</span>
						<?php echo ($jobs_cate_info['spell'][$_GET['jobcategory']]['categoryname']); ?>
					</div><?php endif; ?>
				<?php if($_GET['citycategory']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('citycategory'=>''));?>';">
						<span>地区类别：</span>
						<?php echo ($city['select']['categoryname']); ?>
					</div><?php endif; ?>
				<?php if($_GET['resumetag']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('resumetag'=>''));?>';"><span>简历标签：</span><?php echo ($tag_list[$_GET['resumetag']]); ?></div><?php endif; ?>
				<?php if($_GET['experience']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('experience'=>''));?>';"><span>工作经验：</span><?php echo ($experience_list[$_GET['experience']]); ?></div><?php endif; ?>
				<?php if(!empty($_GET['sex'])): ?><div class="slist" onclick="window.location='<?php echo P(array('sex'=>''));?>';">
						<span>性别：</span>
						<?php if($_GET['sex']== 1): ?>男<?php else: ?>女<?php endif; ?>
					</div><?php endif; ?>
				<?php if($_GET['age']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('age'=>''));?>';"><span>年龄：</span><?php echo ($age_list[$_GET['age']]); ?></div><?php endif; ?>
				<?php if($_GET['trade']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('trade'=>''));?>';">
						<span>行业类别：</span>
						<?php echo ($trade_list[$_GET['trade']]); ?>
					</div><?php endif; ?>
				<?php if($_GET['major']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('major'=>''));?>';"><span>所学专业：</span><?php echo ($major_info['categoryname']); ?></div><?php endif; ?>
				<?php if($_GET['education']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('education'=>''));?>';"><span>学历要求：</span><?php echo ($education_list[$_GET['education']]); ?></div><?php endif; ?>
				<?php if($_GET['nature']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('nature'=>''));?>';"><span>工作性质：</span><?php echo ($nature_list[$_GET['nature']]); ?></div><?php endif; ?>
				<?php if($_GET['wage']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('wage'=>''));?>';"><span>期望薪资：</span><?php echo ($wage_list[$_GET['wage']]); ?></div><?php endif; ?>
				<?php if($_GET['settr']!= ''): ?><div class="slist" onclick="window.location='<?php echo P(array('settr'=>''));?>';">
						<span>更新时间：</span>
						<?php switch($_GET['settr']): case "3": ?>3天内<?php break;?>
							<?php case "7": ?>7天内<?php break;?>
							<?php case "15": ?>15天内<?php break;?>
							<?php case "30": ?>30天内<?php break; endswitch;?>
					</div><?php endif; ?>
				<div class="clear"></div>
			</div>
			<div class="sr">
				<div class="empty" onclick="window.location='<?php echo url_rewrite('QS_resumelist');?>';">清空</div>
			</div>
			<div class="clear"></div>
		</div><?php endif; ?>
</div>
<?php $tag_resume_list_class = new \Common\qscmstag\resume_listTag(array('列表名'=>'resumelist','搜索类型'=>_I($_GET['search_type']),'显示数目'=>'15','分页显示'=>'1','关键字'=>_I($_GET['key']),'职位分类'=>_I($_GET['jobcategory']),'地区分类'=>_I($_GET['citycategory']),'日期范围'=>_I($_GET['settr']),'学历'=>_I($_GET['education']),'工作经验'=>_I($_GET['experience']),'工资'=>_I($_GET['wage']),'工作性质'=>_I($_GET['nature']),'标签'=>_I($_GET['resumetag']),'照片'=>_I($_GET['photo']),'所学专业'=>_I($_GET['major']),'行业'=>_I($_GET['trade']),'年龄'=>_I($_GET['age']),'性别'=>_I($_GET['sex']),'特长描述长度'=>'100','排序'=>_I($_GET['sort']),'检测登录'=>'1','cache'=>'0','type'=>'run',));$resumelist = $tag_resume_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$resumelist);?>
<div class="plist">
	<div class="pl">
			<div class="toptitle">
			<div class="ltype">
				<div class="typeli <?php if($_GET['sort']== ''): ?>select<?php endif; ?>" onclick="window.location='<?php echo P(array('sort'=>''));?>';">综合排序</div>
				<div class="typeli <?php if($_GET['sort']== 'rtime'): ?>select<?php endif; ?>" onclick="window.location='<?php echo P(array('sort'=>'rtime'));?>';">更新时间</div>
				<div class="typeli <?php if($_GET['sort']== 'percent'): ?>select<?php endif; ?>" onclick="window.location='<?php echo P(array('sort'=>'percent'));?>';">简历完整度</div>
				<?php if($_GET['key']!= '' && $search_type == 'full'): ?><div class="typeli <?php if($_GET['sort']== 'score'): ?>select<?php endif; ?>" onclick="window.location='<?php echo P(array('sort'=>'score'));?>';">相关度</div><?php endif; ?>
				<div class="clear"></div>
			</div>
			<div class="ts">
				<div class="l1 <?php if($resumelist['show_login_notice'] == 1): ?>width830<?php endif; ?>"></div>
				<div class="l2 <?php if($_GET['photo']== 1): ?>select<?php endif; ?>" <?php if($_GET['photo']== 1): ?>onclick="window.location='<?php echo P(array('photo'=>''));?>';"<?php else: ?>onclick="window.location='<?php echo P(array('photo'=>1));?>';"<?php endif; ?>>
					<div class="radiobox"></div>
					<div class="radiotxt">有照片</div>
					<div class="clear"></div>
				</div>
				<div class="J_detailList l3 <?php if($_COOKIE['resume_show_type']== 0 or ($_COOKIE['resume_show_type']== null and C('qscms_resume_list_show_type') == 1)): ?>select<?php endif; ?>" title="切换到详细列表"></div>
				<div class="J_detailList l4 <?php if($_COOKIE['resume_show_type']== 1 or ($_COOKIE['resume_show_type']== null and C('qscms_resume_list_show_type') == 2)): ?>select<?php endif; ?>" title="切换到简易列表" show_type="1"></div>
				<?php if(($resumelist['show_login_notice']) == "0"): ?><div class="l5">
					<?php if($resumelist['page_params']['nowPage'] > 1): ?><div class="prev" title="上一页" onclick="window.location='<?php echo P(array('page'=>$resumelist['page_params']['nowPage']-1));?>';"><</div><?php endif; ?>
				  	<?php if($resumelist['page_params']['nowPage'] < $resumelist['page_params']['totalPages']): ?><div class="next"  title="下一页" onclick="window.location='<?php echo P(array('page'=>$resumelist['page_params']['nowPage']+1));?>';">></div><?php endif; ?>
					<?php if($resumelist['page_params']['totalRows'] > 0): ?><span><?php echo ($resumelist["page_params"]["nowPage"]); ?></span>/<?php echo ($resumelist["page_params"]["totalPages"]); ?>页<?php endif; ?>
					<div class="clear"></div>
				</div><?php endif; ?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="listb J_allListBox">
			<?php if(!empty($resumelist['list'])): if(is_array($resumelist['list'])): $i = 0; $__LIST__ = $resumelist['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$resume): $mod = ($i % 2 );++$i;?><div class="J_resumeList yli" data-rid="<?php echo ($resume["id"]); ?>">
						<div class="td1"><div class="J_allList radiobox"></div></div>
	                    <div class="td4 link_blue substring link_visited"><a href="<?php echo ($resume["resume_url"]); ?>" target="_blank"><?php echo (($resume["intention_jobs"] != "")?($resume["intention_jobs"]):"未填写求职意向"); ?></a></div>
						<div class="td2 link_blue substring link_visited">
							<a href="<?php echo ($resume["resume_url"]); ?>" target="_blank"><?php echo ($resume["fullname"]); ?></a>
							<?php if($resume['talent'] == 1): ?><a class="adv"></a><?php endif; ?>
							<?php if($resume['strong_tag']): ?><span><?php echo ($resume['strong_tag']); ?></span><?php endif; ?>
						</div>
						<div class="td3 substring"><?php echo ($resume["sex_cn"]); ?><span>|</span><?php echo ($resume["age"]); ?>岁<span>|</span><?php echo ($resume["education_cn"]); ?><span>|</span><?php echo ($resume["experience_cn"]); ?></div>
						<div class="td5 substring"><?php echo (($resume["district_cn"] != "")?($resume["district_cn"]):'未填写'); ?></div>
						<div class="td6"><?php if($resume['stick'] == 1 && !$_GET['sort']): ?><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/stick.png"><?php else: echo ($resume['refreshtime_cn']); endif; ?></div>
						<div class="td7"><div class="J_resumeStatus hide <?php if($_COOKIE['resume_show_type']== 1 or ($_COOKIE['resume_show_type']== null and C('qscms_resume_list_show_type') == 2)): ?>show<?php endif; ?>"></div></div>
						<div class="clear"></div>
						<div class="detail" <?php if($_COOKIE['resume_show_type']== 1 or ($_COOKIE['resume_show_type']== null and C('qscms_resume_list_show_type') == 2)): ?>style="display:none"<?php endif; ?>>
							<div class="ltx">
				   				<div class="photo"><img src="<?php echo ($resume["photosrc"]); ?>" border="0"/></div>
							    <div class="tcent">
									<div class="txt font_gray6"><?php echo ($resume["nature_cn"]); if($resume['major_cn']): ?><span>|</span>专业：<?php echo ($resume["major_cn"]); endif; ?><span>|</span><?php echo ($resume["wage_cn"]); ?><span>|</span><?php echo ($resume["current_cn"]); ?></div>
									<div class="dlabs">
									<?php if($resume['tag_cn'] || $resume['specialty']): if(empty($resume['tag_cn'])): echo ($resume["specialty"]); ?>
										<?php else: ?>
											<?php if(is_array($resume['tag_cn'])): $i = 0; $__LIST__ = $resume['tag_cn'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?><div class="dl"><?php echo ($tag); ?></div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
									<?php else: ?>
										该简历没有填写自我描述<?php endif; ?>
										<div class="clear"></div>
									</div>
				       			</div>
								<div class="clear"></div> 
							</div>
							<div class="rbtn">
								<div class="deliver J_downResume" data-batch="false" data-url="<?php echo U('CompanyService/resume_download',array('to_detail'=>1));?>">下载简历</div>
								<div class="favorites J_collectForResume" data-batch="false" data-url="<?php echo U('AjaxCompany/resume_favor');?>">收藏</div>
							</div>
							<div class="clear"></div>
						</div>	
					</div><?php endforeach; endif; else: echo "" ;endif; ?>
				<?php if($resumelist["show_login_notice"] == 1): ?><div class="resumelist-login-layer">
						<div class="tip-block">
							<div class="tip-block-title">
								<p class="middle"><span class="font_red">免费注册</span>成为企业会员，海量简历任你选！</p>
								<p class="small">登录或注册会员后可以查看更多数据，各种免费权限爽不停！</p>
							</div> 
							<a href="javascript:;" class="btn_red J_hoverbut btn_inline" id="J_login">已有账号登录</a>
							<a href="javascript:;" id="J_site_reg" class="btn_lightblue J_hoverbut btn_inline">免费注册会员</a>
						</div>
					</div>
				<?php else: ?>
					<div class="listbtn">
						<div class="td1"><div class="radiobox J_allSelected"></div></div>
						<div class="td2">
							<div class="lbts J_collectForResume" data-batch="true" data-url="<?php echo U('AjaxCompany/resume_favor');?>">收藏简历</div>
                            <div class="lbts J_batch_resume">批量浏览</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
					<div class="qspage"><?php echo ($resumelist["page"]); ?></div><?php endif; ?>
			<?php else: ?>
				<?php if($resumelist["hidden_all_result"] == 1 or $resumelist["show_login_notice"] == 1): ?><div class="resumelist-login-layer">
						<div class="tip-block">
							<div class="tip-block-title">
								<p class="middle"><span class="font_red">免费注册</span>成为企业会员，海量简历任你选！</p>
								<p class="small">登录或注册会员后可以查看更多数据，各种免费权限爽不停！</p>
							</div> 
							<a href="javascript:;" class="btn_red J_hoverbut btn_inline" id="J_login">已有账号登录</a>
							<a href="javascript:;" id="J_site_reg" class="btn_lightblue J_hoverbut btn_inline">免费注册会员</a>
						</div>
					</div>
				<?php else: ?>
					<div class="list_empty_group">
						<div class="list_empty">
							<div class="list_empty_left"></div>
							<div class="list_empty_right">
								<div class="sorry_box">对不起，没有找到符合您条件的简历！</div>
								<div class="stips_box">放宽您的查找条件也许有更多合适您的简历哦~</div>
							</div>
							<div class="clear"></div>
						</div>
					</div><?php endif; endif; ?>
		</div>
		<?php if($_GET['citycategory']!= ''): ?><div class="bot_info link_gray6">
				<div class="topnavbg">
					<div class="topnav">
						<?php if($_GET['key'] != '' or $_GET['jobcategory'] != ''): ?><div class="tl J_resume_hotnear select">周边人才</div><?php endif; ?>
						<div class="tl J_resume_hotnear">热门人才</div>
						<div class="clear"></div>
					</div>
				</div>
				<?php if($_GET['key'] != ''): ?><div class="showbotinfo J_resume_hotnear_show show">
			        	<?php if(is_array($city['list'])): $i = 0; $__LIST__ = array_slice($city['list'],0,21,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$district): $mod = ($i % 2 );++$i;?><div class="ili"><a href="<?php echo P(array('citycategory'=>$district['citycategory'],'key'=>$_GET['key']));?>"><?php echo ($district["categoryname"]); echo (urldecode(urldecode(_I($_GET['key'])))); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
						<div class="clear"></div>
					</div>
				<?php elseif($_GET['jobcategory'] != ''): ?>
					<div class="showbotinfo J_resume_hotnear_show show">
			        	<?php if(is_array($city['list'])): $i = 0; $__LIST__ = array_slice($city['list'],0,21,true);if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$district): $mod = ($i % 2 );++$i;?><div class="ili"><a href="<?php echo P(array('citycategory'=>$district['citycategory'],'jobcategory'=>$_GET['jobcategory']));?>"><?php echo ($district["categoryname"]); echo ($jobs_cate_info[$_GET['jobcategory']]['categoryname']); ?></a></div><?php endforeach; endif; else: echo "" ;endif; ?>
						<div class="clear"></div>
					</div><?php endif; ?>
				<div class="showbotinfo J_resume_hotnear_show">
					<?php $tag_hotword_class = new \Common\qscmstag\hotwordTag(array('列表名'=>'hotword_list','显示数目'=>'22','cache'=>'0','type'=>'run',));$hotword_list = $tag_hotword_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$hotword_list);?>
					<?php if(is_array($hotword_list)): $i = 0; $__LIST__ = $hotword_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$hotword): $mod = ($i % 2 );++$i; if(C('qscms_key_urlencode') == 1): ?><div class="ili"><a href="<?php echo P(array('citycategory'=>$city['select']['citycategory'],'key'=>urlencode($hotword['w_word_code'])));?>"><?php echo ($city['select']['categoryname']); echo ($hotword["w_word"]); ?></a></div>
						<?php else: ?>
						<div class="ili"><a href="<?php echo P(array('citycategory'=>$city['select']['citycategory'],'key'=>$hotword['w_word_code']));?>"><?php echo ($city['select']['categoryname']); echo ($hotword["w_word"]); ?></a></div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					<div class="clear"></div>
				</div>
			</div><?php endif; ?>
	</div>
	<div class="clear"></div>
</div>
<div class="new-footer">
    <div class="footer-txt-group nl">
        <div class="ftg-main">
            <div class="ftg-left">
                <div class="ftg-a-group">
                    <?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','显示数目'=>'4','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"简历列表  - {site_name}","keywords"=>"","description"=>"","header_title"=>""),$list);?>
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
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.resumelist.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.search.city.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.modal.dialog.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.dropdown.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.listitem.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.highlight-3.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.autocomplete.js"></script>
<script type="text/javascript">
	if ($('.J_selected .slist').length) {
		$('.J_selected').show();
	}

	$('.J_jobConditions .wli').each(function(index, el) {
		if (index > 6) {
			$(this).addClass('for_up');
		};
	});

	// 关键字高亮
	var keyWords = $('input[name="key"]').val();
	if (keyWords.length) {
		$('.J_resumeList').highlight(keyWords);
	}
	// 关键字联想
    var hotKey = $('#autoKeyInput').autocomplete({
        serviceUrl:"<?php echo U('ajaxCommon/hotword');?>",
        minChars:1,
        maxHeight:400,
        width:276,
        zIndex: 1,
        deferRequestBy: 0
    });
    $('#J_login').click(function(){
    	if(qscms.smsTatus==1){
	    	var qsDialog = $(this).dialog({
	    		loading: true,
				footer: false,
				header: false,
				border: false,
				backdrop: false
			});
	    	var loginUrl = qscms.root+"?m=Home&c=AjaxCommon&a=ajax_login";
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
	    }else{
	    	location.href="<?php echo U('Members/register',array('utype'=>1));?>";
	    }
    });
	// ajax加载简历列表操作状态
	function refresh_list(){
		var rids = $('.J_resumeList').map(function(){
			return $(this).data('rid');
		}).get();
		//alert(rids);
		$.post("<?php echo U('AjaxCompany/refresh_resume_list');?>",{rids:rids},function(result){
			if(result.status == 1 && result.data){
				$('.J_resumeList').each(function(){
					var rid = result.data[$(this).data('rid')];
					if(rid && rid['had_down']){
						var html_down = "<div class=\"had-deliver\" onclick=\"javascript:location.href='" + rid['url'] + "'\">简历已下载</div>";
						$(this).find('.rbtn').find('.deliver').replaceWith(html_down);
					}
					if(rid && rid['had_fav']){
						var html_fav = "<div class=\"had-fav\">已收藏</div>";
						$(this).find('.rbtn').find('.favorites').replaceWith(html_fav);
					}
				});
			}
		},'json');
	}
	var utype = "<?php echo C('visitor.utype');?>";
	var has_resume = $('.listb').has('.J_resumeList');
	if(utype == 1 && has_resume){
		refresh_list();
	}
	// 批量查看简历
    $('.J_batch_resume').click(function () {
        if (!$('.J_allListBox .J_allList.select').length) {
            disapperTooltip('remind','您还没有选择简历！');
            return false;
        }
        var resumeCheckedArr = $('.J_allListBox .J_allList.select');
        var resumeIdArr = new Array();
        var id = resumeCheckedArr.first().closest('.J_resumeList').data('rid'),
        	ids = resumeCheckedArr.map(function(){
        	return $(this).closest('.J_resumeList').data('rid');
        }).get().join(',');
        if(ids){
        	var url = "<?php echo url_rewrite('QS_resumeshow',array('id'=>'_id'));?>&ids=_ids";
        	url = url.replace('_id',id);
        	url = url.replace('_ids',ids);
        	location.href=url;
        }
    });
    // 下载、收藏简历
	resumeSomething('.J_downResume', '下载成功！', true);
	resumeSomething('.J_collectForResume', '收藏成功！', false);

	function resumeSomething (trigger, successMsg, isdown) {
		$(trigger).click(function() {
			var tag = $(this);
			var batch = eval($(this).data('batch'));
			var url = $(this).data('url');
			var ridValue = '';
			var isType = '';
			if (batch) {
				if (listCheckEmpty()) {
					disapperTooltip('remind','您还没有选择简历！');
					return false;
				} else {
					var listCheckedObjs = $('.J_allListBox .J_allList.select');
					var ridArray = new Array();
					$.each(listCheckedObjs, function(index, val) {
						ridArray[index] = $(this).closest('.J_resumeList').data('rid');
					});
					ridValue = ridArray.join(',');
				}
			} else {
				ridValue = $(this).closest('.J_resumeList').data('rid');
			}
			isdown ? isType = 'GET' : isType = 'POST';
			$.ajax({
				url: url,
				type: isType,
				dataType: 'json',
				data: {rid: ridValue}
			})
			.done(function(data) {
				if (parseInt(data.status)) {
					if (isdown) {
						$(this).dialog({
							title: '下载简历',
							border: false,
							footer: false,
							content:data.msg
						});
					} else {
						disapperTooltip('success', successMsg);
						tag.text('已收藏');
					}
				} else {
					if (eval(data.dialog)) {
						var qsDialog = $(this).dialog({
			        		loading: true,
							footer: false,
							header: false,
							border: false,
							backdrop: false
						});
						var loginUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_login';
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
				                disapperTooltip("remind", result.msg);
				            }
				        });
					} else {
						if(data.msg=='请登录企业帐号！'){
							var qsDialogSec = $(this).dialog({
		            			title: '账号切换',
								content: '<div style="width:300px;text-align:center;">您当前账号为个人，是否切换为企业？</div>',
								yes: function () {
									var url = "<?php echo U('Members/switch_utype',array('utype'=>1));?>";
									location.href = url;
								},
								footer: true,
								header: true,
								border: false
							});
							return false;
						}else{
							disapperTooltip("remind", data.msg);
							qsDialog.hide();
						}
						
					
					}
				}
			})
		});
	}
</script>
</body>
</html>