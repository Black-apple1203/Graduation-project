 /* ============================================================
 * jquery.validate.appeal.js 账号申诉验证
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

(function($) {
    'use strict';

    var frmAppealValid = $("#appealForm").validate({
        rules: {
            realname: {
                required: true
            },
            mobile: {
                required: true, 
                match: qscms.regularMobile
            },
            email: {
                required: true,
                email: true
            },
            description: {
                required: true
            }
        },
        messages: {
            realname: {
                required: '<div class="ftxt">请输入您的真实姓名</div><div class="fimg"></div>'
            },
            mobile: {
                required: '<div class="ftxt">请输入您的手机号码</div><div class="fimg"></div>',
                match: '<div class="ftxt">手机号码格式不正确</div><div class="fimg"></div>'
            },
            email: {
                required: '<div class="ftxt">请输入您的常用邮箱</div><div class="fimg"></div>',
                email: '<div class="ftxt">邮箱格式不正确</div><div class="fimg"></div>'
            },
            description: {
                required: '<div class="ftxt">请输入账号申诉描述</div><div class="fimg"></div>'
            }
        },
        errorClasses: {
            realname: {
                required: 'tip err'
            },
            mobile: {
                required: 'tip err',
                match: 'tip err'
            },
            email: {
                required: 'tip err',
                email: 'tip err'
            },
            description: {
                required: 'tip err'
            }
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            element.closest('.J_validate_group').find('.J_showtip_box').append(error);
        },
        success: function(label) {
            label.append('<div class="ok"></div>');
        }
    });

    $('#btnAppealRegister').click(function() {
        $(this).submitForm({
            beforeSubmit: $.proxy(frmAppealValid.form, frmAppealValid),
            success: function(data) {
                if (data.status) {
                    $("#appealForm").remove();
                    $(".appeal_ok").show();
                } else {
                    disapperTooltip("remind", data.msg);
                    return false;
                }
            },
            clearForm: false
        });
        return false;
    });

})(jQuery);