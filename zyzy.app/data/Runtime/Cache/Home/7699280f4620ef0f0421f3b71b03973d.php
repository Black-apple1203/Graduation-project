<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php $tag_company_show_class = new \Common\qscmstag\company_showTag(array('列表名'=>'info','企业id'=>_I($_GET['id']),'cache'=>'0','type'=>'run',));$info = $tag_company_show_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"{companyname} - {site_name}","keywords"=>"{companyname},公司简介","description"=>"{contents},公司简介","header_title"=>""),$info);?>
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
    <link href="<?php echo C('TPL_COMPANY_DIR');?>/default/css/jobs.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=<?php echo C('qscms_map_ak');?>"></script>
    <!--	<script src="../default/public/js/jquery.common.js" type="text/javascript" language="javascript"></script> -->
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
                <?php $tag_nav_class = new \Common\qscmstag\navTag(array('列表名'=>'nav','调用名称'=>'QS_top','显示数目'=>'8','cache'=>'0','type'=>'run',));$nav = $tag_nav_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"{companyname} - {site_name}","keywords"=>"{companyname},公司简介","description"=>"{contents},公司简介","header_title"=>""),$nav);?>
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

<div class="new-se-group">
    <div class="new-se-main">
        <div class="comshow_new no_pt">
            <div class="comlogo">
                <img src="<?php echo ($info['logo']); ?>">
            </div>
            <div class="cominfo">
                <div class="cname">
                    <?php echo ($info['companyname']); ?>
                    <?php if($info['audit'] == 1): ?><img src="<?php echo attach('auth.png','resource');?>" title="认证企业"><?php endif; ?>
                    <?php if($info['setmeal_id'] > 1): ?><img src="<?php echo attach($info['setmeal_id'].'.png','setmeal_img');?>" title="<?php echo ($info["setmeal_name"]); ?>"><?php endif; ?>
                    <?php if($info['famous'] == 1): ?><img src="<?php if(C('qscms_famous_company_img') == ''): echo attach('famous.png','resource'); else: echo attach(C('qscms_famous_company_img'),'images'); endif; ?>" title="诚聘通企业"/><?php endif; ?>
                    <?php if(($info["report"]) == "1"): ?><a href="<?php echo url_rewrite('QS_company_report',array('id'=>$info['id']));?>" target="_blank"><img src="<?php echo attach('report.png','resource');?>" title="实地认证企业"/></a><?php endif; ?>
                </div>
                <div class="stat">
                    <div class="li">
                        <div class="t"><?php echo ($info['jobs_count']); ?>个</div>
                        在招职位
                    </div>
                    <div class="li">
                        <div class="t"><?php echo ($info['reply_ratio']); ?>%</div>
                        简历及时处理率
                    </div>
                    <div class="li">
                        <div class="t"><?php echo ($info['reply_time']); ?></div>
                        简历处理平均用时
                    </div>
                    <div class="li  clear_right_border">
                        <div class="t"><?php echo ($info['last_login_time']); ?></div>
                        企业最近登录
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="share bdsharebuttonbox" data-tag="share_1">
                    <a class="li s1 bds_tsina" data-cmd="tsina"></a>
                    <a class="li s2 bds_renren" data-cmd="renren"></a>
                    <a class="li s3 bds_qzone" data-cmd="qzone"></a>
                    <a class="li s5 bds_sqq" data-cmd="sqq"></a>
                    <a class="li s6 bds_weixin" data-cmd="weixin"></a>
                    <div class="clear"></div>
                </div>

                <div class="attention">
                    <div class="abtn <?php if(($info['focus']) == "1"): ?>for_cancel<?php else: endif; ?>"><?php if(($info['focus']) == "1"): ?>取消关注<?php else: ?>关注<?php endif; ?></div>
                <div class="fans">粉丝：<span><strong class="fans_num"><?php echo ($info['fans']); ?></strong></span></div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

</div>
<div class="comshowmain">
    <div class="l">
        <div class="comnav">
            <a class="select" href="<?php echo url_rewrite('QS_companyshow',array('id'=>$info['id']));?>">公司主页</a>
            <a href="<?php echo url_rewrite('QS_companyjobs',array('id'=>$info['id']));?>">在招职位<span>(<?php echo ($info['jobs_count']); ?>)</span></a>
            <?php if(C('apply.Allowance')): ?><div class="for-al" style="display:none;">
                    <div class="m-re-box">
                        当前共有 <span class="red-txt" id="money_count_jobs">0</span> 个红包职位，红包总额 <span class="red-txt" id="money_count_amount">0</span> 元
                        <div class="m-re-arr"></div>
                        <div class="m-re-clo"></div>
                    </div>
                </div><?php endif; ?>
            <div class="clear"></div>
        </div>

        <div class="infobox">
            <div class="t t1">企业简介</div>
            <div class="txt"><?php echo nl2br($info['company_profile']);?></div>
            <?php if($info['img']): ?><div class="t t4">企业风采</div>
                <div class="comimg" id="comimg">
                    <div class="box">
                        <ul class="list">
                            <?php if(is_array($info['img'])): $i = 0; $__LIST__ = $info['img'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a class="J_for_bigimg" data-src="<?php echo ($vo['img']); ?>" target="_blank" title="<?php echo ($vo['title']); ?>"><img src="<?php echo ($vo['img']); ?>" border="0"></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>
                    <div class="plus"></div>
                    <div class="minus"></div>
                </div><?php endif; ?>
        </div>
        <div class="qcc">
            <span class="qcc-img"></span>
            <span class="qcc-txt link_blue">
    		该企业的信用信息可访问 <a href="https://www.qichacha.com/search?key=<?php echo urlencode($info['companyname']);?>" target="_myzl">企查查</a> 进行查询
    	</span></div>
        <?php if($info['tag_arr']): ?><div class="infobox">
                <div class="t t2">企业福利</div>
                <div class="lab">
                    <?php if(is_array($info['tag_arr'])): $i = 0; $__LIST__ = $info['tag_arr'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="labsli"><?php echo ($vo); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
                    <div class="clear"></div>
                </div>
            </div><?php endif; ?>
        <?php $tag_jobs_list_class = new \Common\qscmstag\jobs_listTag(array('列表名'=>'jobs','显示数目'=>'4','会员uid'=>_I($info['uid']),'cache'=>'0','type'=>'run',));$jobs = $tag_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"{companyname} - {site_name}","keywords"=>"{companyname},公司简介","description"=>"{contents},公司简介","header_title"=>""),$jobs);?>
        <?php if($jobs['list']): ?><div class="infobox">
                <div class="t t3">在招职位</div>
                <div class="more link_gray6"><a href="<?php echo url_rewrite('QS_companyjobs',array('id'=>$info['id']));?>">全部职位>></a></div>
                <div class="jobs">
                    <?php if(is_array($jobs['list'])): $i = 0; $__LIST__ = $jobs['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="jobsli link_blue">
                            <div class="ljob">
                                <a target="_blank" href="<?php echo ($vo['jobs_url']); ?>"><?php echo ($vo['jobs_name']); ?></a>
                                <?php if(C('apply.Allowance') && $vo['allowance_id'] > 0): ?><img class="al-img" src="<?php echo C('TPL_COMPANY_DIR');?>/default/images/12.png" alt="<?php echo ($vo['allowance_info']['type_cn']); ?>" title="【<?php echo ($vo['allowance_info']['type_cn']); ?>】<?php echo ($vo['allowance_info']['per_amount']); ?>元 x <?php echo ($vo['allowance_info']['surplus_num']); ?>个"><?php endif; ?>
                                <span>[<?php echo ($vo['amount']); ?>人]</span>
                            </div>
                            <div class="rjob c"><?php echo ($vo['wage_cn']); ?></div>
                            <div class="ljob"><?php echo ($vo['district_cn']); ?><span>&nbsp;</span><?php echo ($vo['experience_cn']); ?><span>&nbsp;</span><?php echo ($vo['education_cn']); ?></div>
                            <div class="rjob"><?php echo ($vo['refreshtime_cn']); ?></div>
                            <div class="clear"></div>
                        </div><?php endforeach; endif; else: echo "" ;endif; ?>
                    <div class="clear"></div>
                </div>
            </div><?php endif; ?>

    </div>
    <!-- -->
    <div class="r">
        <div class="contact link_gray6">
            <div class="t">企业资料</div>
            <div class="txt">
                <div class="fl txt_t">性质</div>
                <div class="fl"><?php echo ($info['nature_cn']); ?></div>
                <div class="clear"></div>
            </div>
            <div class="txt">
                <div class="fl txt_t">行业</div>
                <div class="fl"><?php echo ($info['trade_cn']); ?></div>
                <div class="clear"></div>
            </div>
            <div class="txt">
                <div class="fl txt_t">规模</div>
                <div class="line_substring" ><?php echo ($info['scale_cn']); ?></div>
                <div class="clear"></div>
            </div>
            <div class="txt">
                <div class="fl txt_t">地址</div>
                <div class="fl content_c" title="<?php echo ($info['address']); ?>"><?php echo ($info['district_cn']); ?></div>
                <div class="clear"></div>
            </div>
            <?php if($info['map_x'] && $info['map_y'] && $info['map_zoom']): ?><div class="map" id="map"></div>
                <script type="text/javascript">
                    var map = new BMap.Map("map");       // 创建地图实例
                    var point = new BMap.Point(<?php echo ($info['map_x']); ?>,<?php echo ($info['map_y']); ?>);  // 创建点坐标
                    map.centerAndZoom(point, <?php echo ($info['map_zoom']); ?>);
                    var qs_marker = new BMap.Marker(point);        // 创建标注
                    map.addOverlay(qs_marker);
                    map.setCenter(point);
                </script><?php endif; ?>
        </div>
        <?php if($info['famous'] == 1): ?><div class="famousWrap">
                <img src="<?php echo attach('famous_img.png','resource');?>" title="诚聘通企业">
            </div><?php endif; ?>
        <div class="weixin link_gray6">
            <div class="t">微信招聘</div>
            <?php if($info['subscribe_company']): ?><div class="code no_border">
                    <?php echo ($info['subscribe_company']); ?>
                </div>
                <div class="code_txt">使用微信扫一扫<br>企业信息秒传到手机</div>
            <?php else: ?>
                <div class="code"><img src="<?php echo C('qscms_site_dir');?>index.php?m=Home&c=Qrcode&a=index&url=<?php echo urlencode(build_mobile_url(array('c'=>'Wzp','a'=>'com','params'=>'id='.$info['id'])));?>" /></div><?php endif; ?>
        </div>

        <div class="leave_msg J_realyWrap">
            <div class="t">给我留言</div>
            <div class="msg_textarea">
                <textarea name="" id="" placeholder="请输入您的疑问。比如工作地点、年薪、福利等等，我会及时给您回复！期待与您合作。"></textarea>
            </div>
            <div class="send_btn_group">
                <div class="txt_num"></div>
                <div class="send_btn J_realyBth" touid="<?php echo ($info["uid"]); ?>">发 送</div>
            </div>
        </div>

        <?php $tag_company_jobs_list_class = new \Common\qscmstag\company_jobs_listTag(array('列表名'=>'similar','行业'=>_I($info['trade']),'显示数目'=>'5','去除id'=>_I($info['uid']),'分站'=>'1','cache'=>'0','type'=>'run',));$similar = $tag_company_jobs_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"{companyname} - {site_name}","keywords"=>"{companyname},公司简介","description"=>"{contents},公司简介","header_title"=>""),$similar);?>
        <?php if($similar['list']): ?><div class="same link_gray6">
                <div class="t">看过该公司的人还看过</div>
                <?php if(is_array($similar['list'])): $i = 0; $__LIST__ = $similar['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list link_gray6">
                        <div class="pic"><a target="_blank" href="<?php echo ($vo['company_url']); ?>"><img src="<?php echo ($vo['logo']); ?>" /></a></div>
                        <div class="txt">
                            <div class="comname"><a href="<?php echo ($vo['company_url']); ?>" target="_blank"><?php echo ($vo['companyname']); ?></a></div>
                            <div class="count"><a target="_blank" href="<?php echo ($vo['company_url']); ?>">本站<span><?php echo ($vo['jobs_num']); ?></span></a>个招聘职位</div>
                        </div>
                        <div class="clear"></div>
                    </div><?php endforeach; endif; else: echo "" ;endif; ?>
            </div><?php endif; ?>
    </div>
    <div class="clear"></div>
</div>


<div class="footer_min" id="footer">
	<div class="links link_gray6">
	<a target="_blank" href="<?php echo url_rewrite('QS_index');?>">网站首页</a>
	<?php $tag_explain_list_class = new \Common\qscmstag\explain_listTag(array('列表名'=>'list','分类id'=>'1','cache'=>'0','type'=>'run',));$list = $tag_explain_list_class->run();$frontend = new \Common\Controller\FrontendController;$page_seo = $frontend->_config_seo(array("pname"=>"","title"=>"{companyname} - {site_name}","keywords"=>"{companyname},公司简介","description"=>"{contents},公司简介","header_title"=>""),$list);?>

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
<script type="text/javascript" src="<?php echo C('TPL_PUBLIC_DIR');?>/js/jquery.modal.dialog.js"></script>
<script type="text/javascript" src="<?php echo C('TPL_COMPANY_DIR');?>/default/js/jquery.cxscroll.js"></script>
<script>
    // 企业风采切换
    $("#comimg").cxScroll();

    // 点击显示企业风采大图
    $('.J_for_bigimg').die().live('click', function(event) {
        var src = $(this).data('src');
        var qsDialog = $(this).dialog({
            title: '企业风采',
            innerPadding: false,
            border: false,
            content: '<div style="max-width: 900px;max-height: 600px;"><img style="max-width: 900px;max-height: 600px;" src="'+src+'" /></div>',
            showFooter: false
        });
    });

    window._bd_share_config = {
        common : {
            bdText : "<?php echo ($info['companyname']); ?>-<?php echo C('qscms_site_name');?>",
            bdDesc : "<?php echo ($info['companyname']); ?>-<?php echo C('qscms_site_name');?>",
            bdUrl : "<?php echo C('qscms_site_domain'); echo url_rewrite('QS_companyshow',array('id'=>$info['id']));?>",
            bdPic : "<?php echo ($info['logo']); ?>"
        },
        share : [{
            "tag" : "share_1",
            "bdCustomStyle":"<?php echo C('TPL_COMPANY_DIR');?>/default/css/jobs.css"
        }]
    }
    with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];
    $(document).ready(function(){
        $.getJSON("<?php echo U('ajaxCommon/company_statistics_add');?>",{comid:"<?php echo ($info['id']); ?>"});
        var isVisitor = "<?php echo ($visitor['uid']); ?>";
        // 关注
        $(".abtn").die().live('click',function(){
            var url = "<?php echo U('ajaxPersonal/company_focus');?>";
            var company_id = "<?php echo ($info['id']); ?>";
            var thisObj = $(this);
            if ((isVisitor > 0)) {
                $.getJSON(url,{company_id:company_id},function(result){
                    if(result.status==1){
                        disapperTooltip('success',result.msg);
                        thisObj.html(result.data.html).toggleClass('for_cancel');
                        if(result.data.op==1){
                            $(".fans_num").html(parseInt($(".fans_num").html())+1);
                        }else{
                            $(".fans_num").html(parseInt($(".fans_num").html())-1);
                        }
                    } else {
                        disapperTooltip('remind',result.msg);
                    }
                });
            } else {
                var qsDialog = $(this).dialog({
                    loading: true,
                    footer: false,
                    header: false,
                    border: false,
                    backdrop: false
                });
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
        });

        // 查看联系方式
        $('.J_check_truenum').die().live('click', function() {
            if (!(isVisitor > 0)) {
                var qsDialog = $(this).dialog({
                    loading: true,
                    footer: false,
                    header: false,
                    border: false,
                    backdrop: false
                });
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
            }else{
                var utype = "<?php echo ($visitor['utype']); ?>";
                if(utype == 1){
                    disapperTooltip('remind','请登录个人账号！');
                }else{
                    disapperTooltip('remind','请先填写一份简历');
                    setTimeout(function() {
                        location.href="<?php echo U('Personal/resume_add');?>";
                    },1000);
                }
            }
        });

        // 给我留言
        $('.J_realyBth').die().live('click', function() {
            var u = $(this),
                f = u.closest('.J_realyWrap').find('textarea'),
                t = $.trim(f.val()),
                touid = u.attr('touid');
            if ((isVisitor > 0)) {
                $.post("<?php echo U('Personal/msg_feedback_send');?>",{touid:touid,message:t},function(result){
                    if(result.status == 1){
                        f.val('');
                        disapperTooltip('success',result.msg);
                    }else{
                        disapperTooltip('remind',result.msg);
                    }
                },'json');
            } else {
                var qsDialog = $(this).dialog({
                    loading: true,
                    footer: false,
                    header: false,
                    border: false,
                    backdrop: false
                });
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
        });
    <?php if(C('apply.Allowance')): ?>// 关闭红包提醒
            $('.for-al .m-re-clo').click(function() {
                $(this).closest('.m-re-box').hide();
            });
        $.getJSON(qscms.root+'?m=Allowance&c=Ajax&a=counter_per_company',{uid:"<?php echo ($info['uid']); ?>"},function(result){
            if(result.status==1){
                $('#money_count_jobs').html(result.data.money_count_jobs);
                $('#money_count_amount').html(result.data.money_count_amount);
                $('.for-al').show();
            }
        });<?php endif; ?>
    });
</script>
</body>
</html>