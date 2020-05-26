/* ============================================================
 * jquery.modal.userselectlayer.js  职位、地区、行业、专业
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

    var backdropLayerTpl = '<div class="modal_backdrop fade"></div>';
    var htmlLayerTpl = ['<div class="modal">', '<div class="modal_dialog">', '<div class="modal_content pie_about">', '<div class="modal_header">', '<span class="title J_modal_title"></span>', '<span class="max_remind J_modal_max"></span>', '<a href="javascript:;" class="close J_dismiss_modal"></a>', '</div>', '<div class="modal_body">', '<div class="listed_group" id="J_listed_group">', '<div class="left_text">已选择：</div>', '<div class="center_text" id="J_listed_content"></div>', '<a href="javascript:;" class="right_text" id="J_listed_clear">清空</a>', '<div class="clear"></div>', '</div>', '<div class="J_modal_content"></div>', '</div>', '<div class="modal_footer">', '<div class="res_add_but">', '<div class="butlist">', '<div class="btn_blue J_hoverbut btn_100_38 J_btnyes">确 定</div>', '</div>', '<div class="butlist">', '<div class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btncancel">取 消</div>', '</div>', '<div class="clear"></div>', '</div>', '</div>', '<input type="hidden" class="J_btnload" />', '</div>', '</div>', '</div>'].join('');

    // 点击显示行业分类
    $('#J_showmodal_trade').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlTrade = '';

        if (QS_trade) {
            htmlTrade += '<div class="modal_body_box modal_body_box1">';
            htmlTrade += '<ul class="list_nav1">';
            if (multipleValue) {
                for (var i = 0; i < QS_trade.length; i++) {
                    if (QS_trade[i].split(',')) {
                        var iArray = QS_trade[i].split(',');
                        htmlTrade += ['<li>', '<label>', '<input class="J_list_trade" type="checkbox" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '"> ', '' + iArray[1] + '</label>', '</li>', ].join('');
                    }
                }
            } else {
                for (var i = 0; i < QS_trade.length; i++) {
                    if (QS_trade[i].split(',')) {
                        var iArray = QS_trade[i].split(',');
                        htmlTrade += ['<li>', '<label class="J_list_trade" type="checkbox" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">', '' + iArray[1] + '</label>', '</li>', ].join('');
                    }
                }
            }
            htmlTrade += '<div class="clear"></div>';
            htmlTrade += '</ul>';
            htmlTrade += '</div>';
        }
        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlTrade);
        $('.J_btnyes').attr('id', 'J_btnyes_trade');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        // 恢复选中
        var recoverValue = $('#J_showmodal_trade .J_resultcode_trade').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_trade').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                        $(this).closest('li').addClass('current');
                    }
                });
            }
            if (multipleValue) {
                copyTradeSelected();
            }
        }
        // 行业列表点击
        $('.J_list_trade').die().live('click', function() {
            if (multipleValue) {
                if ($(this).is(':checked')) {
                    $(this).closest('li').addClass('current');
                    var checkedArray = $('.J_list_trade:checked');
                    if (checkedArray.length > maxNumValue) {
                        disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    } else {
                        copyTradeSelected();
                    }
                } else {
                    $(this).closest('li').removeClass('current');
                    copyTradeSelected();
                }
            } else {
                $(this).closest('li').addClass('current');
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('#J_showmodal_trade .J_resultcode_trade').val(code);
                $('#J_showmodal_trade .J_resuletitle_trade').text(title);
                $('#J_showmodal_trade .J_resuletitle_trade').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        function copyTradeSelected() {
            var htmlListed = '';
            $('.J_list_trade:checked').each(function(index, el) {
                var listedCode = $(this).data('code');
                var listedTitle = $(this).data('title');
                htmlListed += ['<div class="listed_item_parent J_listed_trade" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
            });
            $('#J_listed_content').html(htmlListed);
            if ($('.J_listed_trade').length) {
                $('#J_listed_group').addClass('nmb');
            } else {
                $('#J_listed_group').removeClass('nmb');
            }
            $('#J_listed_group').show();
        }

        $('.J_listed_trade').die().live('click', function() {
            var listedValue = $(this).data('code');
            $('.J_list_trade').each(function(index, el) {
                if ($(this).data('code') == listedValue) {
                    $(this).prop('checked', 0);
                    $(this).closest('li').removeClass('current');
                }
            });
            copyTradeSelected();
        });

        // 清空
        $('#J_listed_clear').live('click', function() {
            $('.J_list_trade:checked').each(function(index, el) {
                $(this).prop('checked', 0);
                $(this).closest('li').removeClass('current');
            });
            copyTradeSelected();
        });

        // 确定
        $('#J_btnyes_trade').live('click', function(event) {
            var checkedArray = $('.J_list_trade:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('#J_showmodal_trade .J_resultcode_trade').val(codeArray.join(','));
            ;$('#J_showmodal_trade .J_resuletitle_trade').text(titleArray.length ? titleArray.join('+') : '不限');
            $('#J_showmodal_trade .J_resuletitle_trade').attr('title', titleArray.length ? titleArray.join('+') : '不限');
            removeModal();
        });
    });

    // 点击显示专业分类
    $('#J_showmodal_major').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlMajor = '';

        if (QS_major_parent) {
            var major1Array = new Array();
            htmlMajor += '<div class="modal_body_box modal_body_box3">';
            htmlMajor += '<div class="left_box">';
            htmlMajor += '<ul class="list_nav">';
            for (var i = 0; i < QS_major_parent.length; i++) {
                if (QS_major_parent[i].split(',')) {
                    var iArray = QS_major_parent[i].split(',');
                    htmlMajor += ['<li class="J_list_major_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">', '<label>' + iArray[1] + '</label>', '</li>'].join('');
                    major1Array[i] = iArray[0];
                }
            }
            htmlMajor += '</ul>';
            htmlMajor += '</div>';
            htmlMajor += '<div class="right_box">';
            if (major1Array) {
                for (var i = 0; i < major1Array.length; i++) {
                    if (QS_major[major1Array[i]]) {
                        if (QS_major[major1Array[i]].split('`')) {
                            var major11Array = QS_major[major1Array[i]].split('`');
                            htmlMajor += '<ul class="list_nav J_list_major_group">';
                            for (var j = 0; j < major11Array.length; j++) {
                                if (major11Array[j].split(',')) {
                                    var jArray = major11Array[j].split(',');
                                    htmlMajor += ['<li class="J_list_major" data-code="' + jArray[0] + '" data-title="' + jArray[1] + '">', '<label>' + jArray[1] + '</label>', '</li>'].join('');
                                }
                            }
                            htmlMajor += '</ul>';
                        }
                    }
                }
            }
            htmlMajor += '</div>';
            htmlMajor += '<div class="clear"></div>';
            htmlMajor += '</div>';
        }
        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlMajor);
        $('.J_btnyes').attr('id', 'J_btnyes_major');
        $('.J_modal_content .right_box .list_nav').eq(0).show();
        $('.J_list_major_parent').eq(0).addClass('current');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        // 恢复选中
        var recoverValue = $('#J_showmodal_major .J_resultcode_major').val();
        if (recoverValue.length) {
            if (multipleValue) {
                var recoverValueArray = recoverValue.split(',');
                for (var i = 0; i < recoverValueArray.length; i++) {
                    $('.J_list_major').each(function(index, el) {
                        if ($(this).data('code') == recoverValueArray[i]) {
                            $(this).addClass('seledted');
                        }
                    });
                }
                copyMajorSelected();
            } else {
                $('.J_list_major').each(function(index, el) {
                    if ($(this).data('code') == recoverValue) {
                        $(this).addClass('seledted');
                    }
                });
            }
            $('.J_list_major_parent').removeClass('seledted current');
            $('.J_list_major.seledted').each(function(index, el) {
                var thisGroup = $(this).closest('.J_list_major_group');
                var subscriptValue = $('.J_list_major_group').index(thisGroup);
                $('.J_list_major_parent').eq(subscriptValue).addClass('seledted');
            });
            $('.J_list_major_parent.seledted').first().click();
        }

        $('.J_list_major_parent').live('click', function() {
            $(this).addClass('current').siblings('.J_list_major_parent').removeClass('current');
            var subscriptValue = $('.J_list_major_parent').index(this);
            $('.J_list_major_group').eq(subscriptValue).show().siblings('.J_list_major_group').hide();
        });

        $('.J_list_major').die().live('click', function() {
            if ($(this).hasClass('seledted')) {
                $(this).removeClass('seledted');
                if (multipleValue) {
                    copyMajorSelected();
                }
                var thisGroup = $(this).closest('.J_list_major_group');
                var subscriptValue = $('.J_list_major_group').index(thisGroup);
                if (!thisGroup.find('.seledted').length) {
                    $('.J_list_major_parent').eq(subscriptValue).removeClass('seledted').addClass('current');
                }
            } else {
                $(this).addClass('seledted');
                if (multipleValue) {
                    if ($('.J_list_major.seledted').length > maxNumValue) {
                        $(this).removeClass('seledted');
                        disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                        return false;
                    } else {
                        copyMajorSelected();
                    }
                    var thisGroup = $(this).closest('.J_list_major_group');
                    var subscriptValue = $('.J_list_major_group').index(thisGroup);
                    $('.J_list_major_parent').eq(subscriptValue).addClass('seledted');
                } else {
                    var code = $(this).data('code');
                    var title = $(this).data('title');
                    $('#J_showmodal_major .J_resultcode_major').val(code);
                    $('#J_showmodal_major .J_resuletitle_major').text(title);
                    $('#J_showmodal_major .J_resuletitle_major').attr('title', title);
                    $('.modal_backdrop').remove();
                    $('.modal').remove();
                }
            }
        });

        // 同步(关键)
        function copyMajorSelected() {
            var htmlListed = '';
            $('.J_list_major.seledted').each(function(index, el) {
                var listedCode = $(this).data('code');
                var listedTitle = $(this).data('title');
                htmlListed += ['<div class="listed_item_parent J_listed_major" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
            });
            $('#J_listed_content').html(htmlListed);
            if ($('.J_listed_major').length) {
                $('#J_listed_group').addClass('nmb');
            } else {
                $('#J_listed_group').removeClass('nmb');
            }
            $('#J_listed_group').show();
        }

        // 已选分类点击
        $('.J_listed_major').die().live('click', function() {
            var listedValue = $(this).data('code');
            $('.J_list_major').each(function(index, el) {
                if ($(this).data('code') == listedValue) {
                    $(this).removeClass('seledted');
                    var thisGroup = $(this).closest('.J_list_major_group');
                    var subscriptValue = $('.J_list_major_group').index(thisGroup);
                    if (!thisGroup.find('.seledted').length) {
                        $('.J_list_major_parent').eq(subscriptValue).removeClass('seledted');
                    }
                }
            });
            copyMajorSelected();
        });

        // 清空
        $('#J_listed_clear').live('click', function() {
            $('.J_list_major.seledted').each(function(index, el) {
                $(this).removeClass('seledted');
            });
            $('.J_list_major_parent').removeClass('seledted');
            copyMajorSelected();
        });

        // 确定
        $('#J_btnyes_major').die().live('click', function() {
            var checkedArray = $('.J_list_major.seledted');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('#J_showmodal_major .J_resultcode_major').val(codeArray.join(','));
            ;$('#J_showmodal_major .J_resuletitle_major').text(titleArray.length ? titleArray.join('+') : '请选择');
            $('#J_showmodal_major .J_resuletitle_major').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });

    // 点击显示地区分类
    $('#J_showmodal_city').live('click', function() {
        var titleValue = $(this).data('title');// 标题
        var multipleValue = eval($(this).data('multiple')); // 是否多选
        var maxNumValue = eval($(this).data('maxnum')); // 最多选择的数量
        var widthValue = eval($(this).data('width')); // 内容的宽度
        var htmlCity = '';
        var categoryValue = eval($(this).data('category')); // 几级分类的标识
        var addJob = eval($(this).data('addjob')); // 是否是发布职位
        // 发职位标识
        var classifyModel = 0;
        // 标记二级还是三级分类
        categoryValue > 2 ? classifyModel = 0 : classifyModel = 1;

        if (classifyModel) {
            // 二级分类
            if (QS_city_parent) {
                htmlCity += '<div class="modal_body_box modal_body_box3">';
                htmlCity += '<div class="left_box">';
                htmlCity += '<ul class="list_nav">';
                // 生成一级分类
                for (var i = 0; i < QS_city_parent.length; i++) {
                    if (QS_city_parent[i].split(',')) {
                        var iArray = QS_city_parent[i].split(',');
                        htmlCity += ['<li class="J_list_city_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">', '<label title="' + iArray[1] + '">' + iArray[1] + '</label>', '</li>'].join('');
                    }
                }
                htmlCity += '</ul>';
                htmlCity += '</div>';
                htmlCity += '<div class="right_box">';
                if (QS_city_parent) {// 生成二级分类
                    for (var i = 0; i < QS_city_parent.length; i++) {
                        if (QS_city_parent[i].split(',')) {// 一级分类是否存在
                            var city1Array = QS_city_parent[i].split(',');
                            if (QS_city[city1Array[0]]) {// 是否存在该一级分类对应的子分类
                                if (QS_city[city1Array[0]].split('`')) {// 子分类是否为空
                                    var city11Array = QS_city[city1Array[0]].split('`');
                                    htmlCity += '<ul class="list_nav J_list_city_group">';
                                    if (!addJob) {// 发布职位不出现不限
                                        htmlCity += ['<li class="J_list_city J_list_city_nolimit" data-code="' + city1Array[0] + '.0.0" data-title="' + city1Array[1] + '">', '<label>不限</label>', '</li>'].join('');
                                    }
                                    for (var j = 0; j < city11Array.length; j++) {
                                        if (city11Array[j].split(',')) {
                                            var jArray = city11Array[j].split(',');
                                            htmlCity += ['<li class="J_list_city" data-code="' + city1Array[0] + '.' + jArray[0] + '.0" data-title="' + jArray[1] + '">', '<label>' + jArray[1] + '</label>', '</li>'].join('');
                                        }
                                    }
                                    htmlCity += '</ul>';
                                }
                            } else {// 子分类为空
                                htmlCity += '<ul class="list_nav J_list_city_group">';
                                if (addJob) {// 发布职位时给出提示，不出现不限
                                    // 发布职位
                                    htmlCity += ['<li class="J_list_city_nolimit">', '<label>此分类下无子分类!</label>', '</li>'].join('');
                                } else {// 无子分类显示不限
                                    htmlCity += ['<li class="J_list_city J_list_city_nolimit" data-code="' + city1Array[0] + '.0.0" data-title="' + city1Array[1] + '">', '<label>不限</label>', '</li>'].join('');
                                }
                                htmlCity += '</ul>';
                            }
                        }
                    }
                }
                htmlCity += '</div>';
                htmlCity += '<div class="clear"></div>';
                htmlCity += '</div>';
            }

            prepareModal(titleValue, multipleValue, maxNumValue); // 初始化二级地区弹出框

            $('.J_modal_content').html(htmlCity);// 弹出框写入内容（根据缓存转换来的html）
            $('.J_btnyes').attr('id', 'J_btnyes_city');// 确定按钮绑定ID
            $('.J_modal_content .right_box .list_nav').eq(0).show();
            // 二级分类列表的第一个为显示状态，后面会根据是否需要恢复来重置
            $('.J_list_city_parent').eq(0).addClass('current');
            // 一级分类的第一个添加选中状态，后面会根据是否需要恢复来重置

            $('.modal_dialog').css({// 弹出框位置在屏幕正中
                width: widthValue + 'px',
                left: ($(window).width() - widthValue) / 2,
                top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
            });

            $('.modal_backdrop').addClass('in');// 半透明遮罩层添加动画的效果

            // 一级分类绑定事件
            $('.J_list_city_parent').live('click', function() {
                $(this).addClass('current').siblings('.J_list_city_parent').removeClass('current');
                var subscriptValue = $('.J_list_city_parent').index(this);
                $('.J_list_city_group').eq(subscriptValue).show().siblings('.J_list_city_group').hide();
            });

            // 恢复选中
            var recoverValue = $('#J_showmodal_city .J_resultcode_city').val();
            if (recoverValue.length) {// 隐藏表单中有值就进行复原
                // 原理->将隐藏表单中已选的值分割分别与弹出框二级列表的data-code比对，如果相同就添加选中（这里如果是三级数据会导致匹配不上，出现弹出框中复原不了选中，需要重新选择保存）。
                if (multipleValue) {// 多选
                    var recoverValueArray = recoverValue.split(',');
                    for (var i = 0; i < recoverValueArray.length; i++) {
                        $('.J_list_city').each(function(index, el) {
                            if ($(this).data('code') == recoverValueArray[i]) {
                                $(this).addClass('seledted');
                            }
                        });
                    }
                    copyCitySelectedSecond();// 调用选择同步的方法给复原选中的项目绑定相应的事件。
                } else {// 单选
                    $('.J_list_city').each(function(index, el) {
                        if ($(this).data('code') == recoverValue) {
                            $(this).addClass('seledted');
                        }
                    });
                }
                // 重置一级分类的选中情况
                $('.J_list_city_parent').removeClass('seledted current');
                $('.J_list_city.seledted').each(function(index, el) {
                    var thisGroup = $(this).closest('.J_list_city_group');
                    var subscriptValue = $('.J_list_city_group').index(thisGroup);
                    $('.J_list_city_parent').eq(subscriptValue).addClass('seledted');
                });
                $('.J_list_city_parent.seledted').first().click();// 选中列表的第一个显示
            }

            // 不限绑定事件
            $('.J_list_city_nolimit').die().live('click', function() {
                var thisGroup = $(this).closest('.J_list_city_group');
                thisGroup.find('.J_list_city').not('.J_list_city_nolimit').removeClass('seledted');
            });

            // 二级分类绑定事件
            $('.J_list_city').die().live('click', function() {
                if ($(this).hasClass('seledted')) { // 已经是选中状态
                    $(this).removeClass('seledted');
                    if (multipleValue) { // 多选情况下需要同步已选列表
                        copyCitySelectedSecond();
                    }
                    // 判断当前分类下是否还有选中的项目，如果没有其他选中项目则将对应的一级分类已选状态去掉
                    var thisGroup = $(this).closest('.J_list_city_group');
                    var subscriptValue = $('.J_list_city_group').index(thisGroup);
                    if (!thisGroup.find('.seledted').length) {
                        $('.J_list_city_parent').eq(subscriptValue).removeClass('seledted').addClass('current');
                    }
                } else { // 未选中状态
                    $(this).addClass('seledted');
                    if (multipleValue) {
                        if (!$(this).is('.J_list_city_nolimit')) {// 判断是否点击的是不限
                            var thisGroup = $(this).closest('.J_list_city_group');
                            thisGroup.find('.J_list_city_nolimit').removeClass('seledted');
                        }
                        // 是否超出最大选择数量
                        if ($('.J_list_city.seledted').length > maxNumValue) {
                            $(this).removeClass('seledted');
                            disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                            return false;
                        } else {
                            copyCitySelectedSecond();
                        }
                        // 将对应的一级分类添加已选状态
                        var thisGroup = $(this).closest('.J_list_city_group');
                        var subscriptValue = $('.J_list_city_group').index(thisGroup);
                        $('.J_list_city_parent').eq(subscriptValue).addClass('seledted');
                    } else {// 单选直接赋值然后关闭弹出框
                        var code = $(this).data('code');
                        var title = $(this).data('title');
                        $('#J_showmodal_city .J_resultcode_city').val(code);
                        $('#J_showmodal_city .J_resuletitle_city').text(title);
                        $('#J_showmodal_city .J_resuletitle_city').attr('title', title);
                        $('.modal_backdrop').remove();
                        $('.modal').remove();
                    }
                }
            });

            // 同步选择与已选列表
            function copyCitySelectedSecond() {
                var htmlListed = '';
                $('.J_list_city.seledted').each(function(index, el) {
                    var listedCode = $(this).data('code');
                    var listedTitle = $(this).data('title');
                    htmlListed += ['<div class="listed_item_parent J_listed_city" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
                });
                $('#J_listed_content').html(htmlListed);
                if ($('.J_listed_city').length) {
                    $('#J_listed_group').addClass('nmb');
                } else {
                    $('#J_listed_group').removeClass('nmb');
                }
                $('#J_listed_group').show();
            }

            // 已选列表绑定时间
            $('.J_listed_city').die().live('click', function() {
                var listedValue = $(this).data('code');
                $('.J_list_city').each(function(index, el) {
                    if ($(this).data('code') == listedValue) {
                        $(this).removeClass('seledted');
                        var thisGroup = $(this).closest('.J_list_city_group');
                        var subscriptValue = $('.J_list_city_group').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            $('.J_list_city_parent').eq(subscriptValue).removeClass('seledted');
                        }
                    }
                });
                copyCitySelectedSecond();
            });

            // 清空已选
            $('#J_listed_clear').live('click', function() {
                $('.J_list_city.seledted').each(function(index, el) {
                    $(this).removeClass('seledted');
                });
                $('.J_list_city_parent').removeClass('seledted');
                copyCitySelectedSecond();
            });

            // 二级地区确定按钮绑定事件
            $('#J_btnyes_city').die().live('click', function() {
                var checkedArray = $('.J_list_city.seledted');
                var codeArray = new Array();
                var titleArray = new Array();
                $.each(checkedArray, function(index, val) {
                    codeArray[index] = $(this).data('code');
                    titleArray[index] = $(this).data('title');
                });
                $('#J_showmodal_city .J_resultcode_city').val(codeArray.join(','));
                $('#J_showmodal_city .J_resuletitle_city').text(titleArray.length ? titleArray.join('+') : '请选择');
                $('#J_showmodal_city .J_resuletitle_city').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModal();
            });
        } else {
            // 三级分类
            if (QS_city_parent) {
                var city2Array = new Array();// 创建数组用来存放生成三级分类的依照
                var htmlCity = '<div class="modal_body_box modal_body_box2">';
                htmlCity += '<div class="item">';
                htmlCity += '<ul class="list_nav">';
                // 生成一级分类
                for (var i = 0; i < QS_city_parent.length; i++) {
                    if (QS_city_parent[i].split(',')) {
                        var iArray = QS_city_parent[i].split(',');
                        htmlCity += ['<li class="J_list_city_parent">', '<label title="' + iArray[1] + '">' + iArray[1] + '</label>', '</li>'].join('');
                    }
                }
                htmlCity += '</ul>';
                htmlCity += '</div>';
                htmlCity += '<div class="item">';
                if (QS_city_parent) {
                    // 生成二级分类
                    for (var i = 0; i < QS_city_parent.length; i++) {
                        if (QS_city_parent[i].split(',')) {// 是否存在一级分类
                            var city1Array = QS_city_parent[i].split(',');
                            if (QS_city[city1Array[0]]) {// 该分类下对应的子分类是否存在
                                if (QS_city[city1Array[0]].split('`')) {// 子分类是否为空
                                    var city11Array = QS_city[city1Array[0]].split('`');
                                    htmlCity += '<ul class="list_nav J_list_city_group1">';
                                    for (var j = 0; j < city11Array.length; j++) {
                                        if (city11Array[j].split(',')) {
                                            var jArray = city11Array[j].split(',');
                                            htmlCity += ['<li class="J_list_city_parent1">', '<label>' + jArray[1] + '</label>', '</li>'].join('');
                                            city2Array.push(city1Array[0] + '.' + jArray[0] + '.' + jArray[1]);
                                        }
                                    }
                                    htmlCity += '</ul>';
                                }
                            } else {
                                // 无二级分类
                                city2Array.push(city1Array[0] + '.0.此分类下无子分类!');
                                htmlCity += '<ul class="list_nav J_list_city_group1">';
                                htmlCity += ['<li class="J_list_city_parent1">', '<label>此分类下无子分类!</label>', '</li>'].join('');
                                htmlCity += '</ul>';
                            }
                        }
                    }
                }
                htmlCity += '</div>';
                htmlCity += '<div class="item">';
                if (city2Array) {
                    if (multipleValue) { // 多选
                        for (var i = 0; i < city2Array.length; i++) {
                            if (city2Array[i].split('.')) { // 二级分类是否存在
                                var combinationArray = city2Array[i].split('.');
                                if (QS_city[combinationArray[1]]) { // 三级分类是否存在
                                    if (QS_city[combinationArray[1]].split('`')) {// 三级分类是否为空
                                        var city22Array = QS_city[combinationArray[1]].split('`');
                                        htmlCity += '<ul class="list_nav J_list_city_group2">';
                                        if (!addJob) {// 发职位时需要选择最后一级，不出现不限。
                                            htmlCity += ['<li>', '<label>', '<input class="J_list_city J_list_city_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ', '不限</label>', '</li>'].join('');
                                        }
                                        for (var j = 0; j < city22Array.length; j++) {
                                            if (city22Array[j].split(',')) {
                                                var jArray = city22Array[j].split(',');
                                                htmlCity += ['<li>', '<label>', '<input class="J_list_city" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '"> ', '' + jArray[1] + '</label>', '</li>'].join('');
                                            }
                                        }
                                        htmlCity += '</ul>';
                                    }
                                } else {// 三级分类不存在
                                    htmlCity += '<ul class="list_nav J_list_city_group2">';
                                    if (addJob) {
                                        // 发布职位时最后一级不存在则给出提示
                                        htmlCity += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                    } else {
                                        if (eval(combinationArray[1]) == 0) {// 二级分类为空
                                            htmlCity += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                        } else {// 发布职位之外最后一级不存在的情况下显示不限
                                            htmlCity += ['<li>', '<label>', '<input class="J_list_city J_list_city_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ', '不限</label>', '</li>'].join('');
                                        }

                                    }
                                    htmlCity += '</ul>';
                                }
                            }
                        }
                    } else {
                        // 单选
                        for (var i = 0; i < city2Array.length; i++) {
                            if (city2Array[i].split('.')) {// 二级分类是否存在
                                var combinationArray = city2Array[i].split('.');
                                if (QS_city[combinationArray[1]]) {// 改分类下的三级分类是否存在
                                    if (QS_city[combinationArray[1]].split('`')) {// 三级分类是否为空
                                        var city22Array = QS_city[combinationArray[1]].split('`');
                                        htmlCity += '<ul class="list_nav J_list_city_group2">';
                                        if (!addJob) {// 发布职位时最后一级不能显示不限
                                            htmlCity += ['<li>', '<label class="J_list_city J_list_city_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">', '不限</label>', '</li>'].join('');
                                        }
                                        for (var j = 0; j < city22Array.length; j++) {
                                            if (city22Array[j].split(',')) {
                                                var jArray = city22Array[j].split(',');
                                                htmlCity += ['<li>', '<label class="J_list_city" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '">', '' + jArray[1] + '</label>', '</li>'].join('');
                                            }
                                        }
                                        htmlCity += '</ul>';
                                    }
                                } else {
                                    // 三级分类不存在
                                    htmlCity += '<ul class="list_nav J_list_city_group2">';
                                    if (addJob) {
                                        // 发布职位时无三级分类则给出提示
                                        htmlCity += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                    } else {
                                        if (eval(combinationArray[1]) == 0) {// 二级分类为空
                                            htmlCity += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                        } else {// 除发布职位外无三级分类时显示不限
                                            htmlCity += ['<li>', '<label class="J_list_city J_list_city_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">', '不限</label>', '</li>'].join('');
                                        }
                                    }
                                    htmlCity += '</ul>';
                                }
                            }
                        }
                    }
                }
                htmlCity += '</div>';
                htmlCity += '<div class="clear"></div>';
                htmlCity += '</div>';
            }
            prepareModal(titleValue, multipleValue, maxNumValue);// 初始化三级地区弹出框

            $('.J_modal_content').html(htmlCity);// 三级地区弹出框填充内容
            $('.J_btnyes').attr('id', 'J_btnyes_city');// 确定按钮绑定ID
            // 设置默认项目，之后会根据是否需要复原来重置
            $('.J_modal_content .item').eq(0).find('.list_nav').show(); // 一级分类列表显示
            $('.J_list_city_parent').eq(0).addClass('current');// 默认一级分类的第一个列表项为选中状态
            $('.J_list_city_parent1').eq(0).addClass('current');// 二级分类的第一个列表项为选中状态
            $('.J_list_city_group1').eq(0).show();// 二级分类的第一个列表显示
            $('.J_list_city_group2').eq(0).show();// 三级分类的第一个列表显示

            // 弹出框居中显示
            $('.modal_dialog').css({
                width: widthValue + 'px',
                left: ($(window).width() - widthValue) / 2,
                top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
            });

            $('.modal_backdrop').addClass('in');// 遮罩层添加动画效果

            // 恢复选中
            var recoverValue = $('#J_showmodal_city .J_resultcode_city').val();
            if (recoverValue.length) {// 隐藏表单中有值就进行复原
                // 原理->将隐藏表单中已选的值分割分别与弹出框三级列表的data-code比对，如果相同就添加选中（这里如果是二级数据会导致匹配不上，出现弹出框中复原不了选中，需要重新选择保存）。
                if (multipleValue) {// 多选
                    var recoverValueArray = recoverValue.split(',');
                    for (var i = 0; i < recoverValueArray.length; i++) {
                        $('.J_list_city').each(function(index, el) {
                            if ($(this).data('code') == recoverValueArray[i]) {
                                $(this).closest('li').addClass('seledted');
                                $(this).prop('checked', !0);
                            }
                        });
                    }
                    copyCitySelected();// 同步选中，已选列表绑定事件
                } else {// 单选
                    $('.J_list_city').each(function(index, el) {
                        if ($(this).data('code') == recoverValue) {
                            $(this).closest('li').addClass('seledted');
                        }
                    });
                }
                // 将默认的选中状态去掉
                $('.J_list_city_parent').removeClass('seledted current');
                $('.J_list_city_parent1').removeClass('seledted current');
                // 复原选中状态的原理->首先获取选中项当前三级分类的下标，根据下标为对应的二级分类添加选中状态，然后获取二级分类选中项的下标，为一级分类添加选中状态。
                if (multipleValue) {
                    $('.J_list_city:checked').each(function(index, el) {
                        var thisGroup = $(this).closest('.J_list_city_group2');
                        var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                        $('.J_list_city_parent1').eq(subscriptValue).addClass('seledted');
                        var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                        var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                        $('.J_list_city_parent').eq(subscriptValue1).addClass('seledted');
                    });
                } else {
                    $('.J_list_city').each(function(index, el) {
                        if ($(this).closest('li').hasClass('seledted')) {
                            var thisGroup = $(this).closest('.J_list_city_group2');
                            var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                            $('.J_list_city_parent1').eq(subscriptValue).addClass('seledted');
                            var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                            var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                            $('.J_list_city_parent').eq(subscriptValue1).addClass('seledted');
                        }
                    });
                }

                // 一级分类选中项的第一个选项添加当前状态class，对应的第一个二级分类显示，触发二级分类的点击事件，显示对应的三级分类。
                $($('.J_list_city_parent.seledted').first()).addClass('current').siblings('.J_list_city_parent').removeClass('current');
                var subscriptValue = $('.J_list_city_parent').index($('.J_list_city_parent.seledted').first());
                $('.J_list_city_group1').eq(subscriptValue).show().siblings('.J_list_city_group1').hide();
                $('.J_list_city_group1').eq(subscriptValue).find('.J_list_city_parent1').eq(0).click();

                // 二级分类的选中的第一个添加当前状态class，对应的三级分类显示。
                $($('.J_list_city_parent1.seledted').first()).addClass('current').siblings('.J_list_city_parent1').removeClass('current');
                var subscriptValueS = $('.J_list_city_parent1').index($('.J_list_city_parent1.seledted').first());
                $('.J_list_city_group2').eq(subscriptValueS).show().siblings('.J_list_city_group2').hide();
            }

            // 一级分类绑定事件
            $('.J_list_city_parent').live('click', function() {
                // 根据当前的下标显示对应的二级分类列表
                $(this).addClass('current').siblings('.J_list_city_parent').removeClass('current');
                var subscriptValue = $('.J_list_city_parent').index(this);
                $('.J_list_city_group1').eq(subscriptValue).show().siblings('.J_list_city_group1').hide();
                // 如果对应的二级分类有选中的，则显示选中的列表，反之显示第一个列表。
                var seledtedLength = $('.J_list_city_group1').eq(subscriptValue).find('.J_list_city_parent1.seledted').length;
                if (seledtedLength) {
                    $('.J_list_city_group1').eq(subscriptValue).find('.J_list_city_parent1.seledted').eq(0).click();
                } else {
                    $('.J_list_city_group1').eq(subscriptValue).find('.J_list_city_parent1').eq(0).click();
                }
            });

            // 二级分类绑定事件
            $('.J_list_city_parent1').live('click', function() {
                // 根据当前的下标显示对应的三级级分类列表
                $(this).addClass('current').siblings('.J_list_city_parent1').removeClass('current');
                var subscriptValue = $('.J_list_city_parent1').index(this);
                $('.J_list_city_group2').eq(subscriptValue).show().siblings('.J_list_city_group2').hide();
            });

            // 不限选项绑定事件
            $('.J_list_city_nolimit').die().live('click', function() {
                var thisGroup = $(this).closest('.J_list_city_group2');
                thisGroup.find('.J_list_city').not('.J_list_city_nolimit').prop('checked', 0);
                thisGroup.find('.J_list_city').not('.J_list_city_nolimit').closest('li').removeClass('seledted');
            });

            // 三级分类绑定事件
            $('.J_list_city').die().live('click', function() {
                if (multipleValue) {// 多选
                    if ($(this).closest('li').hasClass('seledted')) {// 已经为选中状态
                        $(this).closest('li').removeClass('seledted');
                        copyCitySelected();// 同步选择
                        var thisGroup = $(this).closest('.J_list_city_group2');
                        var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            // 判断改三级分类列表下选中项的数量，如果为0则将对应的二级分类选中状态取消
                            $('.J_list_city_parent1').eq(subscriptValue).removeClass('seledted').addClass('current');
                            var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                            var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                            if (!thisGroup2.find('.seledted').length) {
                                // 如果对应的二级分类列表选中项数量为0则将对应的一级分类的选中状态取消
                                $('.J_list_city_parent').eq(subscriptValue1).removeClass('seledted').addClass('current');
                            }
                        }
                    } else {// 未选中状态
                        $(this).closest('li').addClass('seledted');
                        if (!$(this).is('.J_list_city_nolimit')) {
                            // 如果点击的是不限之外的选项，则取消掉不限的选中状态
                            var thisGroup = $(this).closest('.J_list_city_group2');
                            thisGroup.find('.J_list_city_nolimit').prop('checked', 0);
                            thisGroup.find('.J_list_city_nolimit').closest('li').removeClass('seledted');
                        }
                        // 判断是否超出最大选择数量
                        if ($('.J_list_city:checked').length > maxNumValue) {
                            $(this).closest('li').removeClass('seledted');
                            $(this).prop('checked', 0);
                            disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                            return false;
                        } else {
                            copyCitySelected();// 同步选择
                        }
                        // 根据下标将对应的一级、二级分类添加选中状态
                        var thisGroup = $(this).closest('.J_list_city_group2');
                        var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                        $('.J_list_city_parent1').eq(subscriptValue).addClass('seledted');
                        var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                        var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                        $('.J_list_city_parent').eq(subscriptValue1).addClass('seledted');
                    }
                } else {// 单选
                    // 先根据当前选中项的下标将对应的一级、二级分类添加选中状态，然后赋值
                    $(this).closest('li').addClass('seledted');
                    var thisGroup = $(this).closest('.J_list_city_group2');
                    var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                    $('.J_list_city_parent1').removeClass('seledted');
                    $('.J_list_city_parent1').eq(subscriptValue).addClass('seledted');
                    var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                    var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                    $('.J_list_city_parent').removeClass('seledted');
                    $('.J_list_city_parent').eq(subscriptValue1).addClass('seledted');
                    var code = $(this).data('code');
                    var title = $('.J_list_city_parent.seledted').find('label').attr('title');
                    var title1 = $('.J_list_city_parent1.seledted').find('label').text();
                    var title2 = $(this).data('title');
                    $('#J_showmodal_city .J_resultcode_city').val(code);
                    $('#J_showmodal_city .J_resuletitle_city').text(title + '/' + title1 + '/' + title2);
                    $('#J_showmodal_city .J_resuletitle_city').attr('title', title);
                    $('.modal_backdrop').remove();
                    $('.modal').remove();
                }
            });

            // 同步列表和已选项目
            function copyCitySelected() {
                var htmlListed = '';
                $('.J_list_city:checked').each(function(index, el) {
                    var listedCode = $(this).data('code');
                    var listedTitle = $(this).data('title');
                    htmlListed += ['<div class="listed_item_parent J_listed_city" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
                });
                $('#J_listed_content').html(htmlListed);
                if ($('.J_listed_city').length) {
                    $('#J_listed_group').addClass('nmb');
                } else {
                    $('#J_listed_group').removeClass('nmb');
                }
                $('#J_listed_group').show();
            }

            // 已选项目绑定事件
            $('.J_listed_city').die().live('click', function() {
                var listedValue = $(this).data('code');
                $('.J_list_city').each(function(index, el) {
                    if ($(this).data('code') == listedValue) {
                        $(this).closest('li').removeClass('seledted');
                        $(this).prop('checked', 0);
                        var thisGroup = $(this).closest('.J_list_city_group2');
                        var subscriptValue = $('.J_list_city_group2').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            $('.J_list_city_parent1').eq(subscriptValue).removeClass('seledted current');
                            var thisGroup2 = $('.J_list_city_parent1').eq(subscriptValue).closest('.J_list_city_group1');
                            var subscriptValue1 = $('.J_list_city_group1').index(thisGroup2);
                            if (!thisGroup2.find('.seledted').length) {
                                $('.J_list_city_parent').eq(subscriptValue1).removeClass('seledted');
                            }
                        }
                    }
                });
                copyCitySelected();
            });

            // 清空选择
            $('#J_listed_clear').live('click', function() {
                $('.J_list_city:checked').each(function(index, el) {
                    $(this).closest('li').removeClass('seledted');
                    $(this).prop('checked', 0);
                });
                $('.J_list_city_parent1').removeClass('seledted');
                $('.J_list_city_parent').removeClass('seledted');
                copyCitySelected();
            });

            // 三级地区确定按钮绑定事件
            $('#J_btnyes_city').die().live('click', function() {
                var checkedArray = $('.J_list_city:checked');
                var codeArray = new Array();
                var titleArray = new Array();
                $.each(checkedArray, function(index, val) {
                    codeArray[index] = $(this).data('code');
                    titleArray[index] = $(this).data('title');
                });
                $('#J_showmodal_city .J_resultcode_city').val(codeArray.join(','));
                $('#J_showmodal_city .J_resuletitle_city').text(titleArray.length ? titleArray.join('+') : '请选择');
                $('#J_showmodal_city .J_resuletitle_city').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModal();
            });
        }
    });

    // 点击显示职位分类
    $('#J_showmodal_jobs').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlJobs = '';
        var categoryValue = eval($(this).data('category'));
        var addJob = eval($(this).data('addjob'));
        // 发职位标识
        var classifyModel = 0;
        // 标记二级还是三级分类
        categoryValue > 2 ? classifyModel = 0 : classifyModel = 1;

        if (classifyModel) {
            // 二级分类
            if (QS_jobs_parent) {
                htmlJobs += '<div class="modal_body_box modal_body_box_jl2">';
                htmlJobs += '<div class="left_box">';
                htmlJobs += '<ul class="list_nav">';
                for (var i = 0; i < QS_jobs_parent.length; i++) {
                    if (QS_jobs_parent[i].split(',')) {
                        var iArray = QS_jobs_parent[i].split(',');
                        htmlJobs += ['<li class="J_list_jobs_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">', '<label title="' + iArray[1] + '">' + iArray[1] + '</label>', '</li>'].join('');
                    }
                }
                htmlJobs += '</ul>';
                htmlJobs += '</div>';
                htmlJobs += '<div class="right_box">';
                if (QS_jobs_parent) {
                    for (var i = 0; i < QS_jobs_parent.length; i++) {
                        if (QS_jobs_parent[i].split(',')) {
                            var jobs1Array = QS_jobs_parent[i].split(',');
                            if (QS_jobs[jobs1Array[0]]) {
                                if (QS_jobs[jobs1Array[0]].split('`')) {
                                    var jobs11Array = QS_jobs[jobs1Array[0]].split('`');
                                    htmlJobs += '<ul class="list_nav J_list_jobs_group">';
                                    if (!addJob) {
                                        htmlJobs += ['<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">', '<label>不限</label>', '</li>'].join('');
                                    }
                                    for (var j = 0; j < jobs11Array.length; j++) {
                                        if (jobs11Array[j].split(',')) {
                                            var jArray = jobs11Array[j].split(',');
                                            htmlJobs += ['<li class="J_list_jobs" data-code="' + jobs1Array[0] + '.' + jArray[0] + '.0" data-title="' + jArray[1] + '">', '<label>' + jArray[1] + '</label>', '</li>'].join('');
                                        }
                                    }
                                    htmlJobs += '</ul>';
                                }
                            } else {
                                htmlJobs += '<ul class="list_nav J_list_jobs_group">';
                                if (addJob) {
                                    htmlJobs += ['<li class="J_list_jobs_nolimit">', '<label>此分类下无子分类!</label>', '</li>'].join('');
                                } else {
                                    htmlJobs += ['<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">', '<label>不限</label>', '</li>'].join('');
                                }
                                htmlJobs += '</ul>';
                            }
                        }
                    }
                }
                htmlJobs += '</div>';
                htmlJobs += '<div class="clear"></div>';
                htmlJobs += '</div>';
            }

            prepareModal(titleValue, multipleValue, maxNumValue);

            $('.J_modal_content').html(htmlJobs);
            $('.J_btnyes').attr('id', 'J_btnyes_jobs');
            $('.J_modal_content .right_box .list_nav').eq(0).show();
            $('.J_list_jobs_parent').eq(0).addClass('current');

            $('.modal_dialog').css({
                width: widthValue + 'px',
                left: ($(window).width() - widthValue) / 2,
                top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
            });

            $('.modal_backdrop').addClass('in');

            $('.J_list_jobs_parent').live('click', function() {
                $(this).addClass('current').siblings('.J_list_jobs_parent').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent').index(this);
                $('.J_list_jobs_group').eq(subscriptValue).show().siblings('.J_list_jobs_group').hide();
            });

            // 恢复选中
            var recoverValue = $('#J_showmodal_jobs .J_resultcode_jobs').val();
            if (recoverValue.length) {
                if (multipleValue) {
                    var recoverValueArray = recoverValue.split(',');
                    for (var i = 0; i < recoverValueArray.length; i++) {
                        $('.J_list_jobs').each(function(index, el) {
                            if ($(this).data('code') == recoverValueArray[i]) {
                                $(this).addClass('seledted');
                            }
                        });
                    }
                    copyJobsSelectedSecond();
                } else {
                    $('.J_list_jobs').each(function(index, el) {
                        if ($(this).data('code') == recoverValue) {
                            $(this).addClass('seledted');
                        }
                    });
                }
                $('.J_list_jobs_parent').removeClass('seledted current');
                $('.J_list_jobs.seledted').each(function(index, el) {
                    var thisGroup = $(this).closest('.J_list_jobs_group');
                    var subscriptValue = $('.J_list_jobs_group').index(thisGroup);
                    $('.J_list_jobs_parent').eq(subscriptValue).addClass('seledted');
                });
                $('.J_list_jobs_parent.seledted').first().click();
            }

            // 不限
            $('.J_list_jobs_nolimit').die().live('click', function() {
                var thisGroup = $(this).closest('.J_list_jobs_group');
                thisGroup.find('.J_list_jobs').not('.J_list_jobs_nolimit').removeClass('seledted');
            });

            $('.J_list_jobs').die().live('click', function() {
                if ($(this).hasClass('seledted')) {
                    $(this).removeClass('seledted');
                    if (multipleValue) {
                        copyJobsSelectedSecond();
                    }
                    var thisGroup = $(this).closest('.J_list_jobs_group');
                    var subscriptValue = $('.J_list_jobs_group').index(thisGroup);
                    if (!thisGroup.find('.seledted').length) {
                        $('.J_list_jobs_parent').eq(subscriptValue).removeClass('seledted').addClass('current');
                    }
                } else {
                    $(this).addClass('seledted');
                    if (multipleValue) {
                        if (!$(this).is('.J_list_jobs_nolimit')) {
                            var thisGroup = $(this).closest('.J_list_jobs_group');
                            thisGroup.find('.J_list_jobs_nolimit').removeClass('seledted');
                        }
                        if ($('.J_list_jobs.seledted').length > maxNumValue) {
                            $(this).removeClass('seledted');
                            disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                            return false;
                        } else {
                            copyJobsSelectedSecond();
                        }
                        var thisGroup = $(this).closest('.J_list_jobs_group');
                        var subscriptValue = $('.J_list_jobs_group').index(thisGroup);
                        $('.J_list_jobs_parent').eq(subscriptValue).addClass('seledted');
                    } else {
                        var code = $(this).data('code');
                        var title = $(this).data('title');
                        $('#J_showmodal_jobs .J_resultcode_jobs').val(code);
                        $('#J_showmodal_jobs .J_resuletitle_jobs').text(title);
                        $('#J_showmodal_jobs .J_resuletitle_jobs').attr('title', title);
                        // 智能填充职位描述
                        if (addJob) {
                            var cId = code.split('.')[1];
                            $.ajax({
                                url: qscms.root + '?m=Home&c=AjaxCompany&a=ajax_get_category_content',
                                type: 'GET',
                                dataType: 'json',
                                data: {
                                    id: cId
                                }
                            }).done(function(data) {
                                if (parseInt(data.status)) {
                                    if (parseInt(data.data.show_relation)) {
                                        var ddh = '';
                                        for (var dr = 0; dr < data.data.relation_data.length; dr++) {
                                            ddh += '<a class="des-item';
                                            if (parseInt(data.data.relation_data[dr].current)) {
                                                ddh += ' cur';
                                            } else {
                                                ddh += ' J-rea-desc';
                                            }
                                            ddh += '" href="javascript:;" data-id="' + data.data.relation_data[dr].id + '" data-desc="' + data.data.relation_data[dr].desc + '">' + data.data.relation_data[dr].name + '</a>';
                                        }
                                        $('#des-item-group').html(ddh);
                                        $('#des-cell-box').show();
                                    } else {
                                        $('#des-item-group').html('');
                                        $('#des-cell-box').hide();
                                    }
                                    // 默认填写选中分类的描述
                                    $('#contents').val(data.data.relation_data[0].desc);
                                    // 相关分类绑定事件
                                    $('.J-rea-desc').live('click', function() {
                                        $('#des-item-group .des-item').removeClass('cur').addClass('J-rea-desc');
                                        $(this).addClass('cur').removeClass('J-rea-desc');
                                        $('#contents').val($(this).data('desc'));
                                    })
                                } else {
                                    console.log(data.msg);
                                }
                            }).fail(function(result) {
                                console.log(result.msg);
                            });
                        }
                        $('.modal_backdrop').remove();
                        $('.modal').remove();
                    }
                }
            });

            function copyJobsSelectedSecond() {
                var htmlListed = '';
                $('.J_list_jobs.seledted').each(function(index, el) {
                    var listedCode = $(this).data('code');
                    var listedTitle = $(this).data('title');
                    htmlListed += ['<div class="listed_item_parent J_listed_jobs" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
                });
                $('#J_listed_content').html(htmlListed);
                if ($('.J_listed_jobs').length) {
                    $('#J_listed_group').addClass('nmb');
                } else {
                    $('#J_listed_group').removeClass('nmb');
                }
                $('#J_listed_group').show();
            }

            $('.J_listed_jobs').die().live('click', function() {
                var listedValue = $(this).data('code');
                $('.J_list_jobs').each(function(index, el) {
                    if ($(this).data('code') == listedValue) {
                        $(this).removeClass('seledted');
                        var thisGroup = $(this).closest('.J_list_jobs_group');
                        var subscriptValue = $('.J_list_jobs_group').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            $('.J_list_jobs_parent').eq(subscriptValue).removeClass('seledted');
                        }
                    }
                });
                copyJobsSelectedSecond();
            });

            $('#J_listed_clear').live('click', function() {
                $('.J_list_jobs.seledted').each(function(index, el) {
                    $(this).removeClass('seledted');
                });
                $('.J_list_jobs_parent').removeClass('seledted');
                copyJobsSelectedSecond();
            });

            $('#J_btnyes_jobs').die().live('click', function() {
                var checkedArray = $('.J_list_jobs.seledted');
                var codeArray = new Array();
                var titleArray = new Array();
                $.each(checkedArray, function(index, val) {
                    codeArray[index] = $(this).data('code');
                    titleArray[index] = $(this).data('title');
                });
                $('#J_showmodal_jobs .J_resultcode_jobs').val(codeArray.join(','));
                ;$('#J_showmodal_jobs .J_resuletitle_jobs').text(titleArray.length ? titleArray.join('+') : '请选择');
                $('#J_showmodal_jobs .J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModal();
            });
        } else {
            // 三级分类
            if (QS_jobs_parent) {
                var job2Array = new Array();
                var htmlJobs = '<div class="modal_body_box modal_body_box2">';
                htmlJobs += '<div class="item">';
                htmlJobs += '<ul class="list_nav">';
                for (var i = 0; i < QS_jobs_parent.length; i++) {
                    if (QS_jobs_parent[i].split(',')) {
                        var iArray = QS_jobs_parent[i].split(',');
                        htmlJobs += ['<li class="J_list_jobs_parent">', '<label title="' + iArray[1] + '">' + iArray[1] + '</label>', '</li>'].join('');
                    }
                }
                htmlJobs += '</ul>';
                htmlJobs += '</div>';
                htmlJobs += '<div class="item">';
                if (QS_jobs_parent) {
                    for (var i = 0; i < QS_jobs_parent.length; i++) {
                        if (QS_jobs_parent[i].split(',')) {
                            var jobs1Array = QS_jobs_parent[i].split(',');
                            if (QS_jobs[jobs1Array[0]]) {
                                if (QS_jobs[jobs1Array[0]].split('`')) {
                                    var job11Array = QS_jobs[jobs1Array[0]].split('`');
                                    htmlJobs += '<ul class="list_nav J_list_jobs_group1">';
                                    for (var j = 0; j < job11Array.length; j++) {
                                        if (job11Array[j].split(',')) {
                                            var jArray = job11Array[j].split(',');
                                            htmlJobs += ['<li class="J_list_jobs_parent1">', '<label>' + jArray[1] + '</label>', '</li>'].join('');
                                            job2Array.push(jobs1Array[0] + '.' + jArray[0] + '.' + jArray[1]);
                                        }
                                    }
                                    htmlJobs += '</ul>';
                                }
                            } else {
                                // 无二级分类
                                job2Array.push(jobs1Array[0] + '.0.此分类下无子分类!');
                                htmlJobs += '<ul class="list_nav J_list_jobs_group1">';
                                htmlJobs += ['<li class="J_list_jobs_parent1">', '<label>此分类下无子分类!</label>', '</li>'].join('');
                                htmlJobs += '</ul>';
                            }
                        }
                    }
                }
                htmlJobs += '</div>';
                htmlJobs += '<div class="item">';
                if (job2Array) {
                    if (multipleValue) {
                        for (var i = 0; i < job2Array.length; i++) {
                            if (job2Array[i].split('.')) {
                                var combinationArray = job2Array[i].split('.')
                                if (QS_jobs[combinationArray[1]]) {
                                    if (QS_jobs[combinationArray[1]].split('`')) {
                                        var job22Array = QS_jobs[combinationArray[1]].split('`');
                                        htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
                                        if (!addJob) {
                                            htmlJobs += ['<li>', '<label>', '<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ', '不限</label>', '</li>'].join('');
                                        }
                                        for (var j = 0; j < job22Array.length; j++) {
                                            if (job22Array[j].split(',')) {
                                                var jArray = job22Array[j].split(',');
                                                htmlJobs += ['<li>', '<label>', '<input class="J_list_jobs" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '"> ', '' + jArray[1] + '</label>', '</li>'].join('');
                                            }
                                        }
                                        htmlJobs += '</ul>';
                                    }
                                } else {
                                    htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
                                    if (addJob) {
                                        htmlJobs += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                    } else {
                                        if (eval(combinationArray[1]) == 0) {
                                            htmlJobs += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                        } else {
                                            htmlJobs += ['<li>', '<label>', '<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ', '不限</label>', '</li>'].join('');
                                        }
                                    }
                                    htmlJobs += '</ul>';
                                }
                            }
                        }
                    } else {
                        for (var i = 0; i < job2Array.length; i++) {
                            if (job2Array[i].split('.')) {
                                var combinationArray = job2Array[i].split('.')
                                if (QS_jobs[combinationArray[1]]) {
                                    if (QS_jobs[combinationArray[1]].split('`')) {
                                        var job22Array = QS_jobs[combinationArray[1]].split('`');
                                        htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
                                        if (!addJob) {
                                            htmlJobs += ['<li>', '<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">', '不限</label>', '</li>'].join('');
                                        }
                                        for (var j = 0; j < job22Array.length; j++) {
                                            if (job22Array[j].split(',')) {
                                                var jArray = job22Array[j].split(',');
                                                htmlJobs += ['<li>', '<label class="J_list_jobs" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '">', '' + jArray[1] + '</label>', '</li>'].join('');
                                            }
                                        }
                                        htmlJobs += '</ul>';
                                    }
                                } else {
                                    htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
                                    if (addJob) {
                                        htmlJobs += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                    } else {
                                        if (eval(combinationArray[1]) == 0) {
                                            htmlJobs += ['<li>', '<label>', '此分类下无子分类!</label>', '</li>'].join('');
                                        } else {
                                            htmlJobs += ['<li>', '<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">', '不限</label>', '</li>'].join('');
                                        }
                                    }
                                    htmlJobs += '</ul>';
                                }
                            }
                        }
                    }
                }
                htmlJobs += '</div>';
                htmlJobs += '<div class="clear"></div>';
                htmlJobs += '</div>';
            }
            prepareModal(titleValue, multipleValue, maxNumValue);

            $('.J_modal_content').html(htmlJobs);
            $('.J_btnyes').attr('id', 'J_btnyes_jobs');
            $('.J_modal_content .item').eq(0).find('.list_nav').show();
            $('.J_list_jobs_parent').eq(0).addClass('current');
            $('.J_list_jobs_parent1').eq(0).addClass('current');
            $('.J_list_jobs_group1').eq(0).show();
            $('.J_list_jobs_group2').eq(0).show();

            $('.modal_dialog').css({
                width: widthValue + 'px',
                left: ($(window).width() - widthValue) / 2,
                top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
            });

            $('.modal_backdrop').addClass('in');

            // 恢复选中
            var recoverValue = $('#J_showmodal_jobs .J_resultcode_jobs').val();
            if (recoverValue.length) {
                if (multipleValue) {
                    var recoverValueArray = recoverValue.split(',');
                    for (var i = 0; i < recoverValueArray.length; i++) {
                        $('.J_list_jobs').each(function(index, el) {
                            if ($(this).data('code') == recoverValueArray[i]) {
                                $(this).closest('li').addClass('seledted');
                                $(this).prop('checked', !0);
                            }
                        });
                    }
                    copyJobsSelected();
                } else {
                    $('.J_list_jobs').each(function(index, el) {
                        if ($(this).data('code') == recoverValue) {
                            $(this).closest('li').addClass('seledted');
                            $(this).prop('checked', !0);
                        }
                    });
                }
                $('.J_list_jobs_parent').removeClass('seledted current');
                $('.J_list_jobs_parent1').removeClass('seledted current');
                if (multipleValue) {
                    $('.J_list_jobs:checked').each(function(index, el) {
                        var thisGroup = $(this).closest('.J_list_jobs_group2');
                        var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
                        $('.J_list_jobs_parent1').eq(subscriptValue).addClass('seledted');
                        var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
                        var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
                        $('.J_list_jobs_parent').eq(subscriptValue1).addClass('seledted');
                    });
                } else {
                    $('.J_list_jobs').each(function(index, el) {
                        if ($(this).closest('li').hasClass('seledted')) {
                            var thisGroup = $(this).closest('.J_list_jobs_group2');
                            var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
                            $('.J_list_jobs_parent1').eq(subscriptValue).addClass('seledted');
                            var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
                            var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
                            $('.J_list_jobs_parent').eq(subscriptValue1).addClass('seledted');
                        }
                    });
                }
                $('.J_list_jobs_parent.seledted').first().addClass('current').siblings('.J_list_jobs_parent').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent').index($('.J_list_jobs_parent.seledted').first());
                $('.J_list_jobs_group1').eq(subscriptValue).show().siblings('.J_list_jobs_group1').hide();
                $('.J_list_jobs_group1').eq(subscriptValue).find('.J_list_jobs_parent1').eq(0).click();

                $($('.J_list_jobs_parent1.seledted').first()).addClass('current').siblings('.J_list_jobs_parent1').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent1').index($('.J_list_jobs_parent1.seledted').first());
                $('.J_list_jobs_group2').eq(subscriptValue).show().siblings('.J_list_jobs_group2').hide();
            }

            $('.J_list_jobs_parent').live('click', function() {
                $(this).addClass('current').siblings('.J_list_jobs_parent').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent').index(this);
                $('.J_list_jobs_group1').eq(subscriptValue).show().siblings('.J_list_jobs_group1').hide();
                var seledtedLength = $('.J_list_jobs_group1').eq(subscriptValue).find('.J_list_jobs_parent1.seledted').length;
                if (seledtedLength) {
                    $('.J_list_jobs_group1').eq(subscriptValue).find('.J_list_jobs_parent1.seledted').eq(0).click();
                } else {
                    $('.J_list_jobs_group1').eq(subscriptValue).find('.J_list_jobs_parent1').eq(0).click();
                }
            });

            $('.J_list_jobs_parent1').live('click', function() {
                $(this).addClass('current').siblings('.J_list_jobs_parent1').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent1').index(this);
                $('.J_list_jobs_group2').eq(subscriptValue).show().siblings('.J_list_jobs_group2').hide();
            });

            // 不限
            $('.J_list_jobs_nolimit').die().live('click', function() {
                var thisGroup = $(this).closest('.J_list_jobs_group2');
                thisGroup.find('.J_list_jobs').not('.J_list_jobs_nolimit').prop('checked', 0);
                thisGroup.find('.J_list_jobs').not('.J_list_jobs_nolimit').closest('li').removeClass('seledted');
            });

            $('.J_list_jobs').die().live('click', function() {
                if (multipleValue) {
                    if ($(this).closest('li').hasClass('seledted')) {
                        $(this).closest('li').removeClass('seledted');
                        copyJobsSelected();
                        var thisGroup = $(this).closest('.J_list_jobs_group2');
                        var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            $('.J_list_jobs_parent1').eq(subscriptValue).removeClass('seledted').addClass('current');
                            var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
                            var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
                            if (!thisGroup2.find('.seledted').length) {
                                $('.J_list_jobs_parent').eq(subscriptValue1).removeClass('seledted').addClass('current');
                            }
                        }
                    } else {
                        $(this).closest('li').addClass('seledted');
                        if (!$(this).is('.J_list_jobs_nolimit')) {
                            var thisGroup = $(this).closest('.J_list_jobs_group2');
                            thisGroup.find('.J_list_jobs_nolimit').prop('checked', 0);
                            thisGroup.find('.J_list_jobs_nolimit').closest('li').removeClass('seledted');
                        }
                        if ($('.J_list_jobs:checked').length > maxNumValue) {
                            $(this).closest('li').removeClass('seledted');
                            $(this).prop('checked', 0);
                            disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                            return false;
                        } else {
                            copyJobsSelected();
                        }
                        var thisGroup = $(this).closest('.J_list_jobs_group2');
                        var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
                        $('.J_list_jobs_parent1').eq(subscriptValue).addClass('seledted');
                        var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
                        var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
                        $('.J_list_jobs_parent').eq(subscriptValue1).addClass('seledted');
                    }
                } else {
                    var code = $(this).data('code');
                    var title = $(this).data('title');
                    $('#J_showmodal_jobs .J_resultcode_jobs').val(code);
                    $('#J_showmodal_jobs .J_resuletitle_jobs').text(title);
                    $('#J_showmodal_jobs .J_resuletitle_jobs').attr('title', title);
                    // 智能填充职位描述
                    if (addJob) {
                        var cId = code.split('.')[2];
                        $.ajax({
                            url: qscms.root + '?m=Home&c=AjaxCompany&a=ajax_get_category_content',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                id: cId
                            }
                        }).done(function(data) {
                            if (parseInt(data.status)) {
                                if (parseInt(data.data.show_relation)) {
                                    var ddh = '';
                                    for (var dr = 0; dr < data.data.relation_data.length; dr++) {
                                        ddh += '<a class="des-item';
                                        if (parseInt(data.data.relation_data[dr].current)) {
                                            ddh += ' cur';
                                        } else {
                                            ddh += ' J-rea-desc';
                                        }
                                        ddh += '" href="javascript:;" data-id="' + data.data.relation_data[dr].id + '" data-desc="' + data.data.relation_data[dr].desc + '">' + data.data.relation_data[dr].name + '</a>';
                                    }
                                    $('#des-item-group').html(ddh);
                                    $('#des-cell-box').show();
                                } else {
                                    $('#des-item-group').html('');
                                    $('#des-cell-box').hide();
                                }
                                // 默认填写选中分类的描述
                                $('#contents').val(data.data.relation_data[0].desc);
                                // 相关分类绑定事件
                                $('.J-rea-desc').live('click', function() {
                                    $('#des-item-group .des-item').removeClass('cur').addClass('J-rea-desc');
                                    $(this).addClass('cur').removeClass('J-rea-desc');
                                    $('#contents').val($(this).data('desc'));
                                })
                            } else {
                                console.log(data.msg);
                            }
                        }).fail(function(result) {
                            console.log(result.msg);
                        });
                    }
                    $('.modal_backdrop').remove();
                    $('.modal').remove();
                }
            });

            function copyJobsSelected() {
                var htmlListed = '';
                $('.J_list_jobs:checked').each(function(index, el) {
                    var listedCode = $(this).data('code');
                    var listedTitle = $(this).data('title');
                    htmlListed += ['<div class="listed_item_parent J_listed_jobs" data-code="' + listedCode + '" data-title="' + listedTitle + '">', '<a href="javascript:;" class="listed_item">', '<span>' + listedTitle + '</span><div class="del"></div>', '</a>', '</div>'].join('');
                });
                $('#J_listed_content').html(htmlListed);
                if ($('.J_listed_jobs').length) {
                    $('#J_listed_group').addClass('nmb');
                } else {
                    $('#J_listed_group').removeClass('nmb');
                }
                $('#J_listed_group').show();
            }

            $('.J_listed_jobs').die().live('click', function() {
                var listedValue = $(this).data('code');
                $('.J_list_jobs').each(function(index, el) {
                    if ($(this).data('code') == listedValue) {
                        $(this).closest('li').removeClass('seledted');
                        $(this).prop('checked', 0);
                        var thisGroup = $(this).closest('.J_list_jobs_group2');
                        var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
                        if (!thisGroup.find('.seledted').length) {
                            $('.J_list_jobs_parent1').eq(subscriptValue).removeClass('seledted current');
                            var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
                            var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
                            if (!thisGroup2.find('.seledted').length) {
                                $('.J_list_jobs_parent').eq(subscriptValue1).removeClass('seledted');
                            }
                        }
                    }
                });
                copyJobsSelected();
            });

            $('#J_listed_clear').live('click', function() {
                $('.J_list_jobs:checked').each(function(index, el) {
                    $(this).closest('li').removeClass('seledted');
                    $(this).prop('checked', 0);
                });
                $('.J_list_jobs_parent1').removeClass('seledted');
                $('.J_list_jobs_parent').removeClass('seledted');
                copyJobsSelected();
            });

            $('#J_btnyes_jobs').die().live('click', function() {
                var checkedArray = $('.J_list_jobs:checked');
                var codeArray = new Array();
                var titleArray = new Array();
                $.each(checkedArray, function(index, val) {
                    codeArray[index] = $(this).data('code');
                    titleArray[index] = $(this).data('title');
                });
                $('#J_showmodal_jobs .J_resultcode_jobs').val(codeArray.join(','));
                ;$('#J_showmodal_jobs .J_resuletitle_jobs').text(titleArray.length ? titleArray.join('+') : '请选择');
                $('#J_showmodal_jobs .J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModal();
            });
        }
    });

    // 点击显示职位亮点
    $('#J_showmodal_jobtag').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlJobtag = '';

        if (QS_jobtag) {
            htmlJobtag += '<div class="modal_body_box modal_body_box4">';
            for (var i = 0; i < QS_jobtag.length; i++) {
                if (QS_jobtag[i].split(',')) {
                    var iArray = QS_jobtag[i].split(',');
                    htmlJobtag += ['<ul class="list_nav">', '<li>', '<label>', '<input class="J_list_jobtag" type="checkbox" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '"> ', '' + iArray[1] + '</label>', '</li>', '</ul>'].join('');
                }
            }
            htmlJobtag += '<div class="clear"></div>';
            htmlJobtag += '</div>';
        }
        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlJobtag);
        $('.J_btnyes').attr('id', 'J_btnyes_jobtag');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        var recoverValue = $('#J_showmodal_jobtag .J_resultcode_jobtag').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_jobtag').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                    }
                });
            }
        }
        $('.J_list_jobtag').live('click', function() {
            if (multipleValue) {
                var checkedArray = $('.J_list_jobtag:checked');
                if ($(this).is(':checked')) {
                    if (checkedArray.length > maxNumValue) {
                        disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    }
                } else {
                    $('.J_list_jobtag').not(':checked').prop('disabled', 0);
                }
            } else {
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('#J_showmodal_jobtag .J_resultcode_jobtag').val(code);
                $('#J_showmodal_jobtag .J_resuletitle_jobtag').text(title);
                $('#J_showmodal_jobtag .J_resuletitle_jobtag').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        $('#J_btnyes_jobtag').live('click', function(event) {
            var checkedArray = $('.J_list_jobtag:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('#J_showmodal_jobtag .J_resultcode_jobtag').val(codeArray.join(','));
            $('#J_showmodal_jobtag .J_resuletitle_jobtag').text(titleArray.length ? titleArray.join('+') : '请选择');
            $('#J_showmodal_jobtag .J_resuletitle_jobtag').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });
    // 点击显示职位亮点
    $('#tag_cn').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlJobtag = '';

        if (QS_resumetag) {
            htmlJobtag += '<div class="modal_body_box modal_body_box4">';
            for (var i = 0; i < QS_jobtag.length; i++) {
                if (QS_resumetag[i].split(',')) {
                    var iArray = QS_resumetag[i].split(',');
                    htmlJobtag += [
                        '<ul class="list_nav">',
                            '<li>',
                                '<label>',
                                    '<input class="J_list_jobtag" type="checkbox" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '"> ',
                                '' + iArray[1] + '</label>',
                            '</li>',
                        '</ul>'
                    ].join('');
                };
            };
            htmlJobtag += '<div class="clear"></div>';
            htmlJobtag += '</div>';
        };

        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlJobtag);
        $('.J_btnyes').attr('id', 'J_btnyes_jobtag');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue)/2,
            top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        var recoverValue = $('.J_resultcode_jobtag').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_jobtag').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                    };
                });
            };
        };

        $('.J_list_jobtag').live('click', function() {
            if (multipleValue) {
                var checkedArray = $('.J_list_jobtag:checked');
                if ($(this).is(':checked')) {
                    if (checkedArray.length > maxNumValue) {
                        alert('最多选择'+ maxNumValue +'个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    };
                } else {
                    $('.J_list_jobtag').not(':checked').prop('disabled', 0);
                }
            } else {
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('.J_resultcode_jobtag').val(code);
                $('.J_resuletitle_jobtag').val(title);
                $('.J_resuletitle_jobtag').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        $('#J_btnyes_jobtag').live('click', function(event) {
            var checkedArray = $('.J_list_jobtag:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('.J_resultcode_jobtag').val(codeArray.join(','));
            ;
            $('.J_resuletitle_jobtag').val(titleArray.length ? titleArray.join('+') : '请选择');
            $('.J_resuletitle_jobtag').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });

    // 点击显示设施
    $('#J_store_tag').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlJobtag = '';
        var tagArray = categoryTagArray;
        if (tagArray) {
            htmlJobtag += '<div class="modal_body_box modal_body_box4">';
            $.each(tagArray, function (index, value) {
                htmlJobtag += [
                    '<ul class="list_nav">',
                    '<li>',
                    '<label>',
                    '<input class="J_list_jobtag" type="checkbox" data-code="' + index + '" data-title="' + value + '"> ',
                    '' + value + '</label>',
                    '</li>',
                    '</ul>'
                ].join('');
            })
            htmlJobtag += '<div class="clear"></div>';
            htmlJobtag += '</div>';
        }

        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlJobtag);
        $('.J_btnyes').attr('id', 'J_btnyes_jobtag');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue)/2,
            top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        var recoverValue = $('.J_resultcode_jobtag').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_jobtag').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                    };
                });
            };
        };

        $('.J_list_jobtag').live('click', function() {
            if (multipleValue) {
                var checkedArray = $('.J_list_jobtag:checked');
                if ($(this).is(':checked')) {
                    if (checkedArray.length > maxNumValue) {
                        alert('最多选择'+ maxNumValue +'个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    };
                } else {
                    $('.J_list_jobtag').not(':checked').prop('disabled', 0);
                }
            } else {
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('.J_resultcode_jobtag').val(code);
                $('.J_resuletitle_jobtag').val(title);
                $('.J_resuletitle_jobtag').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        $('#J_btnyes_jobtag').live('click', function(event) {
            var checkedArray = $('.J_list_jobtag:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('.J_resultcode_jobtag').val(codeArray.join(','));
            ;
            $('.J_resuletitle_jobtag').val(titleArray.length ? titleArray.join('+') : '请选择');
            $('.J_resuletitle_jobtag').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });

    // 点击显示设施
    $('#J_store_tag').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlJobtag = '';
        var tagArray = categoryTagArray;
        if (tagArray) {
            htmlJobtag += '<div class="modal_body_box modal_body_box4">';
            $.each(tagArray, function (index, value) {
                htmlJobtag += [
                    '<ul class="list_nav">',
                    '<li>',
                    '<label>',
                    '<input class="J_list_jobtag" type="checkbox" data-code="' + index + '" data-title="' + value + '"> ',
                    '' + value + '</label>',
                    '</li>',
                    '</ul>'
                ].join('');
            })
            htmlJobtag += '<div class="clear"></div>';
            htmlJobtag += '</div>';
        }

        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlJobtag);
        $('.J_btnyes').attr('id', 'J_btnyes_jobtag');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue)/2,
            top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        var recoverValue = $('.J_resultcode_jobtag').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_jobtag').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                    };
                });
            };
        };

        $('.J_list_jobtag').live('click', function() {
            if (multipleValue) {
                var checkedArray = $('.J_list_jobtag:checked');
                if ($(this).is(':checked')) {
                    if (checkedArray.length > maxNumValue) {
                        alert('最多选择'+ maxNumValue +'个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    };
                } else {
                    $('.J_list_jobtag').not(':checked').prop('disabled', 0);
                }
            } else {
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('.J_resultcode_jobtag').val(code);
                $('.J_resuletitle_jobtag').html(title);
                $('.J_resuletitle_jobtag').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        $('#J_btnyes_jobtag').live('click', function(event) {
            var checkedArray = $('.J_list_jobtag:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('.J_resultcode_jobtag').val(codeArray.join(','));
            ;
            $('.J_resuletitle_jobtag').html(titleArray.length ? titleArray.join('+') : '请选择');
            $('.J_resuletitle_jobtag').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });

    // 点击显示简历标签
    $('#J_showmodal_resumetag').live('click', function() {
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maxNumValue = eval($(this).data('maxnum'));
        var widthValue = eval($(this).data('width'));
        var htmlResumetag = '';

        if (QS_resumetag) {
            htmlResumetag += '<div class="modal_body_box modal_body_box1">';
            for (var i = 0; i < QS_resumetag.length; i++) {
                if (QS_resumetag[i].split(',')) {
                    var iArray = QS_resumetag[i].split(',');
                    htmlResumetag += ['<ul class="list_nav">', '<li>', '<label>', '<input class="J_list_resumetag" type="checkbox" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '"> ', '' + iArray[1] + '</label>', '</li>', '</ul>'].join('');
                }
            }
            htmlResumetag += '<div class="clear"></div>';
            htmlResumetag += '</div>';
        }
        prepareModal(titleValue, multipleValue, maxNumValue);

        $('.J_modal_content').html(htmlJobtag);
        $('.J_btnyes').attr('id', 'J_btnyes_resumetag');

        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        });

        $('.modal_backdrop').addClass('in');

        var recoverValue = $('#J_showmodal_resumetag .J_resultcode_resumetag').val();
        if (recoverValue.length) {
            var recoverValueArray = recoverValue.split(',');
            for (var i = 0; i < recoverValueArray.length; i++) {
                $('.J_list_resumetag').each(function(index, el) {
                    if ($(this).data('code') == recoverValueArray[i]) {
                        $(this).prop('checked', !0);
                    }
                });
            }
        }
        $('.J_list_resumetag').live('click', function() {
            if (multipleValue) {
                var checkedArray = $('.J_list_resumetag:checked');
                if ($(this).is(':checked')) {
                    if (checkedArray.length > maxNumValue) {
                        disapperTooltip("remind", '最多选择' + maxNumValue + '个');
                        $(this).prop('checked', 0);
                        $(this).closest('li').removeClass('current');
                        return false;
                    }
                } else {
                    $('.J_list_resumetag').not(':checked').prop('disabled', 0);
                }
            } else {
                var code = $(this).data('code');
                var title = $(this).data('title');
                $('#J_showmodal_resumetag .J_resultcode_resumetag').val(code);
                $('#J_showmodal_resumetag .J_resuletitle_resumetag').text(title);
                $('#J_showmodal_resumetag .J_resuletitle_resumetag').attr('title', title);
                $('.modal_backdrop').remove();
                $('.modal').remove();
            }
        });

        $('#J_btnyes_resumetag').live('click', function(event) {
            var checkedArray = $('.J_list_resumetag:checked');
            var codeArray = new Array();
            var titleArray = new Array();
            $.each(checkedArray, function(index, val) {
                codeArray[index] = $(this).data('code');
                titleArray[index] = $(this).data('title');
            });
            $('#J_showmodal_resumetag .J_resultcode_resumetag').val(codeArray.join(','));
            ;$('#J_showmodal_resumetag .J_resuletitle_resumetag').text(titleArray.length ? titleArray.join('+') : '请选择');
            $('#J_showmodal_resumetag .J_resuletitle_resumetag').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
            removeModal();
        });
    });

    function prepareModal(titleValue, multipleValue, maxNumValue) {
        var ie = !-[1, ];
        var ie6 = !-[1, ] && !window.XMLHttpRequest;
        $(backdropLayerTpl).appendTo($(document.body));
        if (ie6) {
            $('.modal_backdrop').css("height", $(document).height());
        }
        $(htmlLayerTpl).appendTo($(document.body));

        $('.J_modal_title').text(titleValue);
        if (multipleValue) {
            $('.J_modal_max').text('（最多选择' + maxNumValue + '个）');
        }
        if (!multipleValue) {
            $('.modal_footer').hide();
        }
        $(".J_hoverbut").hover(function() {
            $(this).addClass("hover");
        }, function() {
            $(this).removeClass("hover");
        });

        // 可拖动
        var newObj = $('.modal_dialog');
        var newTit = newObj.find(".modal_header");

        newTit.mousedown(function(e) {
            var offset = newObj.offset();
            var x = e.pageX - offset.left;
            var y = e.pageY - offset.top;
            $(document).bind('mousemove', function(ev) {
                newObj.bind('selectstart', function() {
                    return false;
                });
                var newx = ev.pageX - x;
                var newy = ev.pageY - y;
                newObj.css({
                    'left': newx + "px",
                    'top': newy + "px"
                });
            });
        });

        $(document).mouseup(function() {
            $(this).unbind("mousemove");
        })

        if (ie) {
            if (window.PIE) {
                $('.pie_about').each(function() {
                    PIE.attach(this);
                });
            }
        }
    }

    $('.J_dismiss_modal').live('click', function() {
        removeModal();
    });

    function removeModal() {
        setTimeout(function() {
            $('.modal_backdrop').remove();
            $('.modal').remove();
        }, 50)
    }

}(window.jQuery);
