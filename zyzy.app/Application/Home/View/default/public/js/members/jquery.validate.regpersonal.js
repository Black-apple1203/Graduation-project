 /* ============================================================
 * jquery.validate.regpersonal.js 个人注册验证
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

(function($) {
    'use strict';

    function remoteValid(value, element) {
        var result = false, eletype = element.name;
        $.ajax({
            url: qscms.root + '?m=Home&c=Members&a=ajax_check',
            cache: false,
            async: false,
            type: 'post',
            dataType: 'json',
            data: { type: eletype, param: value },
            success: function(json) {
                if (json && json.status) {
                    result = true;
                } else {
                    result = false;
                }
            }
        });
        return result;
    }
    // 自定义验证方法，验证是否被注册
    $.validator.addMethod('IsRegistered', remoteValid, '已被注册');

    // 若检测到手机号存在则提示解绑原帐号or更换新手机
    var regularMobile = qscms.regularMobile;
    $('input[name=mobile]').keyup(function () {
        var currentValue = $(this).val();
        if (currentValue.length >= 11) {
            if(regularMobile.test(currentValue) && !remoteValid(currentValue,this)) {
                $('.had-remind').show();
            } else {
                $('.had-remind').hide();
            }
        } else {
            $('.had-remind').hide();
        }
    });
    $('#J_change_mobile').live('click', function () {
        window.location.reload();
    });
    $('#J_login').live('click', function () {
        window.location.href = qscms.root + '?m=Home&c=Members&a=login';
    });

    // 获取后台验证码配置
    var captcha_open = eval($('#J_captcha_open').val());

    // 点击获取验证码先判断是否输入了手机号
    $('#J_getverificode').click(function() {
        var mobileValue = $.trim($('#mobile').val());
        if (mobileValue == '') {
            disapperTooltip("remind", "请输入手机号码");
            $('#mobile').focus();
            return false;
        };
        if (mobileValue != "" && !regularMobile.test(mobileValue)) {
            disapperTooltip("remind", "请输入正确的手机号码");
            $('#mobile').focus();
            return false;
        }

        if (captcha_open) {
            // 后台开启验证
            $('.geetest_panel').remove();
            // 手机发送验证码配置极验
            if (parseInt(qscms.smsTatus)) {
                if (eval($('#J_captcha_varify_send').val())) {
                    qsCaptchaHandler(function(callBackArr) {
                        var dataArr = {mobile: $.trim($('#mobile').val())};
                        $.extend(dataArr, callBackArr);
                        function settime(countdown) {
                            if (countdown == 0) {
                                $('#J_getverificode').prop("disabled", 0);
                                $('#J_getverificode').removeClass('btn_disabled hover');
                                $('#J_getverificode').val('获取验证码');
                                countdown = 60;
                                return;
                            } else {
                                $('#J_getverificode').prop("disabled", !0);
                                $('#J_getverificode').addClass('btn_disabled');
                                $('#J_getverificode').val('重发' + countdown + '秒');
                                countdown--;
                            }
                            setTimeout(function() {
                                settime(countdown)
                            },1000)
                        }
                        $('#J_getverificode').prop("disabled", !0);
                        $('#J_getverificode').addClass('btn_disabled');
                        $('#J_getverificode').val('发送中...');
                        $.ajax({
                            url: qscms.root+'?m=Home&c=Members&a=reg_send_sms',
                            type: 'POST',
                            dataType: 'json',
                            data: dataArr
                        })
                        .done(function(data) {
                            if (parseInt(data.status)) {
                                setTimeout(function() {
                                    disapperTooltip("success", "验证码已发送，请注意查收");
                                    // 开始倒计时
                                    var countdowns = 60;
                                    settime(countdowns);
                                },800)
                            } else {
                                setTimeout(function() {
                                    $('#J_getverificode').prop("disabled", 0);
                                    $('#J_getverificode').removeClass('btn_disabled hover');
                                    $('#J_getverificode').val('获取验证码');
                                    disapperTooltip("remind", data.msg);
                                },1500)
                            }
                        });
                    });
                } else {
                    toSetSms();
                }
            } else {
                disapperTooltip("remind", "短信未开启");
            }
        } else {
            // 后台未开启验证
            toSetSms();
        }
    });

    // 个人手机注册验证程序
    $("#regMobileForm").validate({
        submitHandler: function(form) {
            if (!$('#regMobileForm input[name="agreement"]').is(':checked')) {
                disapperTooltip("remind", '请同意注册协议');
                return false;
            }
            if (captcha_open) {
                // 开启注册验证
                $('.geetest_panel').remove();
                // 通过手机注册配置极验
                if (parseInt(qscms.smsTatus)) {
                    regPerByMobileHandler();
                } else {
                    disapperTooltip("remind", '短信未开启');
                }
            } else {
                // 未开启注册验证
                regPerByMobileHandler();
            }
        },
        rules: {
            mobile: {
                required: true, 
                match: qscms.regularMobile,
                //IsRegistered: true
            },
            mobile_vcode: {
                required: true
                //match: /\d{6}$/
            },
            mobilepassword: {
                required: true,
                rangelength: [6, 16]
            },
            mobilepasswordVerify: {
                required: true,
                rangelength: [6, 16],
                equalTo: "#mobilepassword"
            }
        },
        messages: {
            mobile: {
                required: '<div class="ftxt">请输入手机号码</div><div class="fimg"></div>',
                match: '<div class="ftxt">手机号格式不正确</div><div class="fimg"></div>',
                //IsRegistered: '<div class="ftxt">该手机号已被注册</div><div class="fimg"></div>'
            },
            mobile_vcode: {
                required: '<div class="ftxt">请输入验证码</div><div class="fimg"></div>',
                //match: '<div class="ftxt">手机验证码为4位纯数字</div><div class="fimg"></div>'
            },
            mobilepassword: {
                required: '<div class="ftxt">请输入密码</div><div class="fimg"></div>',
                rangelength: '<div class="ftxt">密码长度要求为6-16个字符</div><div class="fimg"></div>'
            },
            mobilepasswordVerify: {
                required: '<div class="ftxt">请输入确认密码</div><div class="fimg"></div>',
                rangelength: '<div class="ftxt">密码长度要求为6-16个字符</div><div class="fimg"></div>',
                equalTo: '<div class="ftxt">两次输入的密码不一致</div><div class="fimg"></div>'
            }
        },
        errorClasses: {
            mobile: {
                required: 'tip err',
                match: 'tip err',
                //IsRegistered: 'tip err'
            },
            mobile_vcode: {
                required: 'tip err',
                match: 'tip err'
            },
            mobilepassword: {
                required: 'tip err',
                rangelength: 'tip err',
                match: 'tip err'
            },
            mobilepasswordVerify: {
                required: 'tip err',
                rangelength: 'tip err',
                equalTo: 'tip err'
            }
        },
        tips: {
            mobile: '<div class="ftxt">手机号可用于登录网站和找回密码</div><div class="fimg"></div>',
            mobile_vcode: '<div class="ftxt">请输入手机验证码</div><div class="fimg"></div>',
            mobilepassword: '<div class="ftxt">6-16位字符组成，区分大小写</div><div class="fimg"></div>',
            mobilepasswordVerify: '<div class="ftxt">请再次输入密码</div><div class="fimg"></div>'
        },
        tipClasses: {
            mobile: 'tip',
            mobile_vcode: 'tip',
            mobilepassword: 'tip',
            mobilepasswordVerify: 'tip'
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            if (element.attr('name') == 'mobile_vcode') {
                element.closest('.J_validate_group').find('.J_showtip_box').append(error);
            }  else {
                element.closest('.J_validate_group').find('.J_showtip_box').append(error);
            }
        },
        success: function(label) {
            label.append('<div class="ok"></div>');
        }
    });
    var register = {
        initialize: function() {
            this.initControl();
        },
        initControl: function() {
            // 手机注册提交
            $('#btnMoilbPhoneRegister').die().live('click', function() {
                $(this).submitForm({
                    beforeSubmit: $.proxy(frmMobilValid.form, frmMobilValid),
                    success: function(data) {
                        if (data.status) {
                            window.location.href = data.data.url;
                        } else {
                            $('#btnMoilbPhoneRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                            disapperTooltip("remind", data.msg);
                        }
                    },
                    clearForm: false
                });
                if (frmMobilValid.valid()) {
                    $('#btnMoilbPhoneRegister').val('注册中...').addClass('btn_disabled').prop('disabled', !0);
                }
                return false;
            });
        }
    }

    // 发送手机验证码
    function toSetSms() {
        function settime(countdown) {
            if (countdown == 0) {
                $('#J_getverificode').prop("disabled", 0);
                $('#J_getverificode').removeClass('btn_disabled hover');
                $('#J_getverificode').val('获取验证码');
                countdown = 180;
                return;
            } else {
                $('#J_getverificode').prop("disabled", !0);
                $('#J_getverificode').addClass('btn_disabled');
                $('#J_getverificode').val('重新发送' + countdown + '秒');
                countdown--;
            }
            setTimeout(function() {
                settime(countdown)
            },1000)
        }
        $('#J_getverificode').prop("disabled", !0);
        $('#J_getverificode').addClass('btn_disabled');
        $('#J_getverificode').val('发送中...');
        $.ajax({
            url: qscms.root+'?m=Home&c=Members&a=reg_send_sms',
            type: 'POST',
            dataType: 'json',
            data: {mobile: $.trim($('#mobile').val())}
        })
        .done(function(data) {
            if (parseInt(data.status)) {
                setTimeout(function() { 
                    disapperTooltip("success", "验证码已发送，请注意查收");
                    // 开始倒计时
                    var countdowns = 60;
                    settime(countdowns);
                },800)
            } else {
                setTimeout(function() {
                    $('#J_getverificode').prop("disabled", 0);
                    $('#J_getverificode').removeClass('btn_disabled hover');
                    $('#J_getverificode').val('获取验证码');
                    disapperTooltip("remind", data.msg);
                },1500)
            }
        });
    }

    // 个人手机注册处理程序
    function regPerByMobileHandler() {
        $('#btnMoilbPhoneRegister').val('注册中...').addClass('btn_disabled').prop('disabled', !0);
        $.ajax({
            url: qscms.root+'?m=Home&c=Members&a=register',
            type: 'POST',
            dataType: 'json',
            data: $('#regMobileForm').serialize(),
            success: function (data) {
                if(data.status == 1){
                    window.location.href = data.data.url;
                }else{
                    if ($('#regMobileForm input[name="agreement"]').is(':checked')) {
                        $('#btnMoilbPhoneRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                    }
                    disapperTooltip("remind", data.msg);
                }
            },
            error:function(data){
                if ($('#regMobileForm input[name="agreement"]').is(':checked')) {
                    $('#btnMoilbPhoneRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                }
                disapperTooltip("remind", data.msg);
            }
        });
    }
})(jQuery);