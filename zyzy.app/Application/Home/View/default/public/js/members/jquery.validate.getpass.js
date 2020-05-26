 /* ============================================================
 * jquery.validate.getpass.js 找回密码验证
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

(function($) {
    'use strict';

    // 验证是否被注册
    $.validator.addMethod('IsRegistered', function(value, element) {
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
                    result = false;
                } else {
                    result = true;
                }
            }
        });
        return result;
    }, '已被注册');

    $("#getPassByMobileForm").validate({
        rules: {
            mobile: {
                required: true, 
                match: qscms.regularMobile,
                IsRegistered: true
            },
            mobile_vcode: {
                required: true
                //match: /\d{6}$/
            }
        },
        messages: {
            mobile: {
                required: '<div class="ftxt">请输入手机号码</div><div class="fimg"></div>',
                match: '<div class="ftxt">手机号格式不正确</div><div class="fimg"></div>',
                IsRegistered: '<div class="ftxt">该手机号没有注册账号</div><div class="fimg"></div>'
            },
            mobile_vcode: {
                required: '<div class="ftxt">请输入验证码</div><div class="fimg"></div>',
               // match: '<div class="ftxt">手机验证码为6位纯数字</div><div class="fimg"></div>'
            }
        },
        errorClasses: {
            mobile: {
                required: 'tip err',
                match: 'tip err',
                IsRegistered: 'tip err'
            },
            mobile_vcode: {
                required: 'tip err',
                match: 'tip err'
            }
        },
        tips: {
            mobile: '<div class="ftxt">请填写账户绑定的手机号</div><div class="fimg"></div>',
            mobile_vcode: '<div class="ftxt">请输入手机验证码</div><div class="fimg"></div>'
        },
        tipClasses: {
            mobile: 'tip',
            mobile_vcode: 'tip'
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            element.closest('.J_validate_group').find('.J_showtip_box').append(error);
        },
        success: function(label) {
            label.append('<div class="ok"></div>');
        }
    });


    $("#getPassByEmailForm").validate({
        rules: {
            email: {
                required: true,
                email: true,
                IsRegistered: true
            }
        },
        messages: {
            email: {
                required: '<div class="ftxt">请输入邮箱</div><div class="fimg"></div>',
                email: '<div class="ftxt">邮箱格式不正确</div><div class="fimg"></div>',
                IsRegistered: '<div class="ftxt">该邮箱没有注册账号</div><div class="fimg"></div>'
            }
        },
        errorClasses: {
            email: {
                required: 'tip err',
                email: 'tip err',
                IsRegistered: 'tip err'
            }
        },
        tips: {
            email: '<div class="ftxt">请填写账户绑定的常用邮箱</div><div class="fimg"></div>'
        },
        tipClasses: {
            email: 'tip'
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            element.closest('.J_validate_group').find('.J_showtip_box').append(error);
        },
        success: function(label) {
            label.append('<div class="ok"></div>');
        }
    });
})(jQuery);