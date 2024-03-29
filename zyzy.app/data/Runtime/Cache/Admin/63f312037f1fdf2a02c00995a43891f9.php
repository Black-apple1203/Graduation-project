<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=7">
    <link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
    <meta name="author" content="zy拉钩人才系统"/>
    <meta name="copyright" content="zy拉钩人才系统"/>
    <title>网站后台管理中心- zy拉钩人才系统</title>
    <link href="__ADMINPUBLIC__/css/common.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css"/>
</head>
<body style="background-color:#FFFFFF">
<div class="login_top">
    <div class="logo"><img src="__ADMINPUBLIC__/images/zylogin_logo.gif"/></div>
</div>
<form id="form1" name="form1" method="post" action="<?php echo U('index/login');?>">
    <div class="login_main">
        <div class="sw_nav">
            <div class="nav_item J_nav_item checked">账号登录
                <div class="bt_line"></div>
            </div>
            <div class="nav_item J_nav_item">微信登录
                <div class="bt_line"></div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="J_for_sw">
            <?php if(!empty($err)): ?><div class="ce">
                    <div class="err" id="J_errbox"><?php echo ($err); ?></div>
                </div><?php endif; ?>
            <div class="ce">
                <div class="imgbg"></div>
                <input name="username" type="text" maxlength="16" id="username" class="linput" placeholder="请输入用户名"/>
            </div>
            <div class="ce">
                <div class="imgbg pwd"></div>
                <input name="password" type="password" id="admin_pwd" value="" class="linput pwd" placeholder="请输入密码"/>
            </div>
            <div class="ce">
                <input class="btn" type="button" name="Submit" id="J_dologin" value="登录"/>
                <input type="button" id="btnCheck" style="display:none;">
            </div>
        </div>
        <div class="J_for_sw" style="display:none;">
            <div class="qr_box">
                <div class="qr_img" id="J_weixinQrCode"></div>
                <div class="qr_txt">请使用微信扫一扫</div>
            </div>
        </div>
    </div>
    <div id="popup-captcha"></div>
    <input type="hidden" name="geetest_challenge" class="J_gee_cha" value="">
    <input type="hidden" name="geetest_validate" class="J_gee_val" value="">
    <input type="hidden" name="geetest_seccode" class="J_gee_sec" value="">
    <input type="hidden" name="token" class="J_token_cha" value="">
    <input type="hidden" name="Ticket" class="J_tc_ket" value="">
    <input type="hidden" name="Randstr" class="J_tc_str" value="">
</form>



<script src="__ADMINPUBLIC__/js/jquery.min.js"></script>
<?php switch($captcha_type = C('qscms_captcha_type')): case "geetest": ?><script src="https://static.geetest.com/static/tools/gt.js"></script><?php break;?>
    <?php case "vaptcha": ?><script src="https://v.vaptcha.com/v3.js"></script><?php break;?>
    <?php case "tencent": ?><script src="https://ssl.captcha.qq.com/TCaptcha.js"></script><?php break; endswitch;?>
<script language="javascript">
    // 登录方式切换
    $('.J_nav_item').click(function() {
        var cuIndex = $('.J_nav_item').index(this);
        $(this).addClass('checked').siblings().removeClass('checked');
        $('.J_for_sw').eq(cuIndex).show().siblings('.J_for_sw').hide();
        if (cuIndex) {
            get_weixin_qrcode();
        }
    });
    var qrcode_time,
    waiting_weixin_scan = function(){
        $.getJSON('?m=<?php echo C(admin_alias);?>&c=Index&a=waiting_weixin_login',function(result){
            if(result.status == 1){
                window.location.href = result.data;
            }
        });
    };
    function get_weixin_qrcode(){
        $.getJSON('?m=<?php echo C(admin_alias);?>&c=Qrcode&a=get_weixin_qrcode',{type:'login'},function(result){
            if(result.status == 1){
                $('#J_weixinQrCode').empty().append(result.data);
                qrcode_time=setInterval(waiting_weixin_scan,5000);
            }else{
                $('#J_weixinQrCode').empty().html(result.msg);
            }
        });
    }
    function init() {
        var ctrl = document.getElementById("username");
        ctrl.focus();
    }
    init();
    $(document).ready(function () {
        $('#admin_pwd').bind('keypress', function (event) {
            if (event.keyCode == "13") {
                $("#J_dologin").click();
            }
        });
        $('#J_dologin').live('click', function () {
            if ("<?php echo ($verify_userlogin_admin); ?>" == 1) {
                $.ajax({
                    url: "<?php echo U('home/captcha/index');?>?t=" + (new Date()).getTime(),
                    type: "get",
                    dataType: "json",
                    success: function (data) {
                        if(data.verify_type == "vaptcha"){
                            vaptcha({
                                vid: data.vid,
                                type: 'invisible',
                                scene: 1,
                                https: data.https,
                                offline_server:'?m=Home&c=captcha&a=vaptcha_outage',
                            }).then(function (vaptchaObj) {
                                obj = vaptchaObj;
                                vaptchaObj.listen('pass', function() {
                                    $('.J_token_cha').val(vaptchaObj.getToken());
                                    doLogin();
                                });
                                vaptchaObj.listen('close', function() {
                                    
                                });
                                vaptchaObj.validate();
                            });
                        } else if (data.verify_type == "tencent") {
                            var TCaptchaObj = new TencentCaptcha(data.vid, function(res) {
                                if(res.ret === 0){
                                    $('.J_tc_ket').val(res.ticket);
                                    $('.J_tc_str').val(res.randstr);
                                    doLogin();
                                }
                            });
                            TCaptchaObj.show();
                        } else {
                            initGeetest({
                                gt: data.gt,
                                challenge: data.challenge,
                                offline: !data.success,
                                new_captcha: data.new_captcha,
                                product: 'bind'
                            }, function (captchaObj) {
                                captchaObj.appendTo("#popup-captcha");
                                captchaObj.onSuccess(function () {
                                    var captChaResult = captchaObj.getValidate();
                                    $('.J_gee_cha').val(captChaResult.geetest_challenge);
                                    $('.J_gee_val').val(captChaResult.geetest_validate);
                                    $('.J_gee_sec').val(captChaResult.geetest_seccode);
                                    doLogin();
                                })
                                captchaObj.onReady(function () {
                                    $("#btnCheck").click();
                                });
                                $('#btnCheck').click(function () {
                                    captchaObj.verify();
                                })
                                captchaObj.onError(function () {
                                    $('#J_errbox').text("网络错误，请稍候再试！").show();
                                });
                            });
                        }
                        
                    },
                    error: function (data) {
                        $('#J_errbox').text(data['responseText']).show();
                    }
                });
            } else {
                doLogin();
            }
        });

        function doLogin() {
            // 提交表单
            $("#form1").submit();
        }
    });
</script>
</body>
</html>