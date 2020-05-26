 /* ============================================================
 * jquery.validate.binding.js 第三方注册绑定验证
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

(function($) {
    'use strict';

    // 自定义验证方法，验证是否被注册
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
                    result = true;
                } else {
                    result = false;
                }
            }
        });
        return result;
    }, '已被注册');

    // 自定义验证方法，验证手机号是否唯一
    $.validator.addMethod('IsRegisteredT', function(value, element) {
        var result = false, eletype = 'mobile';
        if (value.length) {
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
        } else {
            result = true;
        }
        return result;
    }, '手机号已被注册');

    // 自定义验证方法，验证区号
    $.validator.addMethod("inputRegValiZone", function(value, element, param) {
        if (this.optional(element))
            return "dependency-mismatch";
        var reg = param;
        if (typeof param == 'string') {
            reg = new RegExp(param);
        }
        return reg.test(value);
    }, '区号格式不正确');

    // 自定义验证方法，固话手机二选一
    $.validator.addMethod("lineMobileAchoice", function(value, element, param) {
        var regularTelphone = qscms.regularMobile;
        var achoice = true;
        var telphoneValue = $.trim($('#telephone').val());
        var landlinefirstValue = $.trim($("#landline_tel_first").val());
        var landlinenextValue = $.trim($("#landline_tel_next").val());
        if (telphoneValue == '' && landlinenextValue == '') {
            achoice = false;
        }
        if (telphoneValue != "" && !regularTelphone.test(telphoneValue) && landlinenextValue == '') {
            achoice = false;
        }
        return achoice;
    }, '固定电话和手机号码请至少填写一项');

    // 手机号输入实时验证二选一
    $('input[name="telephone"]').on('keyup', function(event) {
        var telephoneValue = $(this).val();
        if (telephoneValue.length >= 11) {
            if (!$('#landline_tel_next').closest('.td1').next().find('.ok').length) {
                $('#landline_tel_next').closest('.td1').next().empty();
            }
        }
    });

    // 固定电话输入实时验证二选一
    $('input[name="landline_tel_next"]').on('keyup', function(event) {
        var telValue = $(this).val();
        if (telValue.length >= 6) {
            if (!$('#telephone').closest('.td1').next().find('.ok').length) {
                $('#telephone').closest('.td1').next().empty();
            }
        }
    });

    // 个人邮箱注册验证程序
    $("#regEmailForm").validate({
        submitHandler: function(form) {
            if (!$('#regEmailForm input[name="agreement"]').is(':checked')) {
                disapperTooltip("remind", '请同意注册协议');
                return false;
            }
            regPerByEmailHandler();
        }
    });

    // 企业注册验证程序
    $('#registerForm').validate({
        submitHandler: function(form) {
            if (!$('#registerForm input[name="agreement"]').is(':checked')) {
                disapperTooltip("remind", '请同意注册协议');
                return false;
            }
            var landline_tel_num = $.trim($('#landline_tel_first').val()) + '-' + $.trim($('#landline_tel_next').val());
            if ($.trim($('#landline_tel_last').val()).length) {
                landline_tel_num += '-' + $.trim($('#landline_tel_last').val());
            }
            $('#landline_tel').val(landline_tel_num);
            regCompanyHandler();
        },
        rules: {
            companyname: {
                required: true,
                rangelength: [4, 25],
                IsRegistered: true
            },
            contact: {
                required: true,
                rangelength: [1, 10]
            },
            landline_tel_first: {
                inputRegValiZone: '^[0-9]{3}[0-9]?$'
            },
            landline_tel_next: {
                match: '^[0-9]{6,11}$',
                lineMobileAchoice: true
            },
            landline_tel_last: {
                number: true,
                rangelength: [1, 4]
            },
            telephone: {
                match: qscms.regularMobile,
                lineMobileAchoice: true,
                IsRegisteredT : true
            }
        },
        messages: {
            companyname: {
                required: '<div class="ftxt">请输入企业名称</div><div class="fimg"></div>',
                rangelength: '<div class="ftxt">4-25个字组成</div><div class="fimg"></div>',
                IsRegistered: '<div class="ftxt">该企业名称已被注册</div><div class="fimg"></div>'
            },
            contact: {
                required: '<div class="ftxt">请输入企业联系人</div><div class="fimg"></div>',
                rangelength: '<div class="ftxt">1-10个字组成</div><div class="fimg"></div>'
            },
            landline_tel_first: {
                inputRegValiZone: '<div class="ftxt">请填写正确的区号</div><div class="fimg"></div>'
            },
            landline_tel_next: {
                match: '<div class="ftxt">请输入6-11位的数字</div><div class="fimg"></div>',
                lineMobileAchoice: '<div class="ftxt">固定电话和手机号码至少填写一项</div><div class="fimg"></div>'
            },
            landline_tel_last: {
                number: '<div class="ftxt">分机号码为数字</div><div class="fimg"></div>',
                rangelength: '<div class="ftxt">1-4位数字组成</div><div class="fimg"></div>'
            },
            telephone: {
                match: '<div class="ftxt">手机号格式不正确</div><div class="fimg"></div>',
                lineMobileAchoice: '<div class="ftxt">固定电话和手机号码请至少填写一项</div><div class="fimg"></div>',
                IsRegisteredT : '<div class="ftxt">手机号已被注册</div><div class="fimg"></div>'
            }
        },
        errorClasses: {
            companyname: {
                required: 'tip err',
                rangelength: 'tip err',
                IsRegistered: 'tip err'
            },
            contact: {
                required: 'tip err',
                rangelength: 'tip err'
            },
            landline_tel_first: {
                inputRegValiZone: 'tip err'
            },
            landline_tel_next: {
                match: 'tip err',
                lineMobileAchoice: 'tip err'
            },
            landline_tel_last: {
                number: 'tip err',
                rangelength: 'tip err'
            },
            telephone: {
                match: 'tip err',
                lineMobileAchoice: 'tip err',
                IsRegisteredT:  'tip err'
            }
        },
        tips: {
            companyname: '<div class="ftxt">名称与企业营业执照保持一致</div><div class="fimg"></div>',
            contact: '<div class="ftxt">请填写全名</div><div class="fimg"></div>',
            telephone: '<div class="ftxt">手机号可用于登录网站和找回密码</div><div class="fimg"></div>'
        },
        tipClasses: {
            companyname: 'tip',
            contact: 'tip',
            telephone: 'tip'
        },
        groups: {
            phoneNum: 'landline_tel_first landline_tel_next landline_tel_last'
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            if (element.attr('name') == 'landline_tel_last' || element.attr('name') == 'landline_tel_next' || element.attr('name') == 'landline_tel_first') {
                element.closest('.J_validate_group').find('.J_showtip_box').append(error);
            }  else {
                element.closest('.J_validate_group').find('.J_showtip_box').append(error);
            }
        },
        success: function(label) {
            label.append('<div class="ok"></div>');
        }
    });

    // 个人邮箱注册处理程序
    function regPerByEmailHandler() {
        $('#btnEmailRegister').val('注册中...').addClass('btn_disabled').prop('disabled', !0);
        $.ajax({
            url: qscms.root+'?m=Home&c=Members&a=register',
            type: 'POST',
            dataType: 'json',
            data: $('#regEmailForm').serialize(),
            success: function (data) {
                if(data.status == 1){
                    window.location.href = data.data.url;
                }else{
                    if ($('#regEmailForm input[name="agreement"]').is(':checked')) {
                        $('#btnEmailRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                    }
                    disapperTooltip("remind", data.msg);
                }
            },
            error:function(data){
                if ($('#regEmailForm input[name="agreement"]').is(':checked')) {
                    $('#btnEmailRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                }
                disapperTooltip("remind", data.msg);
            }
        });
    }

    // 注册企业处理程序
    function regCompanyHandler() {
        $('#btnRegister').val('注册中...').addClass('btn_disabled').prop('disabled', !0);
        $.ajax({
            url: qscms.root+'?m=Home&c=Members&a=register',
            type: 'POST',
            dataType: 'json',
            data: $('#registerForm').serialize(),
            success: function (data) {
                if(data.status == 1){
                    window.location.href = data.data.url;
                }else{
                    $('#btnRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                    disapperTooltip("remind", data.msg);
                }
            },
            error:function(data){
                $('#btnRegister').val('注册').removeClass('btn_disabled').prop('disabled', 0);
                disapperTooltip("remind", data.msg);
            }
        });
    }
})(jQuery);