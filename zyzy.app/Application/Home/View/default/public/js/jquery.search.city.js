/* ============================================================
 * 搜索页面地区
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {
    var backdropLayerTpl = '<div class="modal_backdrop fade"></div>';
    var htmlLayerTpl = ['<div class="modal qs-category-unlimited def">', '<div class="modal_dialog">', '<div class="modal_content pie_about">', '<div class="modal_header">', '<span class="title J_modal_title"></span>', '<span class="max_remind J_modal_max"></span>', '<a href="javascript:;" class="close J_dismiss_modal"></a>', '</div>', '<div class="modal_body pd0">', '<div class="listed_group" id="J_listed_group">', '<div class="left_text">已选择：</div>', '<div class="center_text" id="J_listed_content"></div>', '<a href="javascript:;" class="right_text" id="J_listed_clear">清空</a>', '<div class="clear"></div>', '</div>', '<div class="J_modal_content"></div>', '</div>', '<div class="modal_footer">', '<div class="res_add_but">', '<div class="butlist">', '<div class="btn_blue J_hoverbut btn_100_38 J_btn_yes">确 定</div>', '</div>', '<div class="butlist">', '<div class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btn_cancel">取 消</div>', '</div>', '<div class="clear"></div>', '</div>', '</div>', '<input type="hidden" class="J_btnload" />', '</div>', '</div>', '</div>'].join('');
    // 点击
    $('[data-toggle="funCityModal"]').live('click', function() {
        var that = $(this);
        if (QS_city_parent.length <= 0) {
            console.log('地区分类出错！！！');
            return false;
        }
        var titleValue = $(this).data('title');
        var multipleValue = eval($(this).data('multiple'));
        var maximumValue = eval($(this).data('maximum'));
        var widthValue = eval($(this).data('width'));
        var defaultCity = qscms.default_district;
        var defaultCitySpell = qscms.default_district_spell;
        var htmlCategory = '';
        var isSpell = app_spell;
        var cateLevel = qscms.district_level;
        var checkedPool = new Array();
        var defaultKeyArr = '';
        var recoverVal = $('#recoverSearchCityModalCode').val();
        var ids = '';
        htmlCategory += '<div class="selected-group J_selected_group"><div class="selected-box"><div class="s-l-txt">已选择：</div><div class="p-cate J_cate_box"></div><div class="btn-some" id="J_clear_all"><a href="javascript:;">清空</a></div></div></div>';
        htmlCategory += '<div class="category-select"><div class="cs-left"><span>选择地区</span></div><div class="cs-right" id="selectCategoryBox">';
        htmlCategory += '<div class="clear"></div></div><div class="clear"></div></div>';
        htmlCategory += '<div class="cate-type" id="checkboxCategoryBox">';
        htmlCategory += '<div class="clear"></div></div>';
        htmlCategory += '</div>';
        // 初始化
        prepareModal(titleValue, multipleValue, maximumValue);
        // 模板写入
        $('.J_modal_content').html(htmlCategory);
        $('.J_btn_yes').attr('id', 'J_btn_yes_city');
        // 显示并调整位置
        $('.modal_dialog').css({
            width: widthValue + 'px',
            left: ($(window).width() - widthValue) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        })
        $('.modal_backdrop').addClass('in');
        if (isSpell) {
            var cateParentHtml = '<select class="categorySelect">';
            for (var i = 0; i < QS_city_spell_parent.length; i++) {
                cateParentHtml += '<option value="' + QS_city_spell_parent[i].split(',')[0] + '">' + QS_city_spell_parent[i].split(',')[1] + '</option>';
            }
            cateParentHtml += '</select>';
            $('#selectCategoryBox').html(cateParentHtml);
            function getParentId(id) {
                $.each(QS_city_spell_parent, function (index, val) {
                    var parentValArr = val.split(',');
                    if (QS_city_spell[parentValArr[0]]) {
                        var subArr = QS_city_spell[parentValArr[0]].split('`');
                        getPid(id, subArr, parentValArr[0]);
                    }
                })
                return ids;
            }
            function getPid(id, subArr, parentVal) {
                for (var j = 0; j < subArr.length; j++) {
                    if (id == subArr[j].split(',')[0]) {
                        if (ids) {
                            ids = parentVal + '.' + ids;
                        } else {
                            ids = parentVal;
                        }
                    } else {
                        if (QS_city_spell[subArr[j].split(',')[0]]) {
                            var ssubArr = QS_city_spell[subArr[j].split(',')[0]].split('`');
                            getPid(id, ssubArr, parentVal + '.' + subArr[j].split(',')[0]);
                        }
                    }
                }
            }
            // 默认值和需要恢复的处理
            if (recoverVal.length) {
                ids = '';
                var firstKeepHtml = getParentId(recoverVal);
                if (firstKeepHtml) {
                    var firstHtml = firstKeepHtml + '.' + recoverVal;
                    var firstKeepArr = firstHtml.toString().split('.');
                    getSubCateHtml(firstKeepArr[0],firstKeepArr);
                    for (var i = 0; i < $('.categorySelect').length; i++) {
                         for (var j = 0; j < firstKeepArr.length; j++) {
                            $('.categorySelect').eq(i).find('option').each(function(index, el) {
                               if ($(this).val() == firstKeepArr[j]) {
                                    $(this).prop('selected', !0);
                               }
                            })
                        }
                    }
                } else {
                    $('.categorySelect').eq(0).find('option').each(function(index, el) {
                       if ($(this).val() == recoverVal) {
                            $(this).prop('selected', !0);
                       }
                    })
                    if (getSubCateLevel(recoverVal,'')) {
                        getSubCateHtml(recoverVal,'');
                    }
                }
            } else {
                if (defaultCitySpell) {
                    var firstKeepArr = defaultCitySpell.split('.');
                    getSubCateHtml(firstKeepArr[0], '');
                    for (var i = 0; i < $('.categorySelect').length; i++) {
                         for (var j = 0; j < firstKeepArr.length; j++) {
                            $('.categorySelect').eq(i).find('option').each(function(index, el) {
                               if ($(this).val() == firstKeepArr[j]) {
                                    $(this).prop('selected', !0);
                               }
                            })
                        }
                    }
                } else {
                    getSubCateHtml($('.categorySelect').eq(0).find('option:selected').val(),'');
                    $('.categorySelect').eq(0).find('option').each(function(index, el) {
                       if ($(this).val() == recoverVal) {
                            $(this).prop('selected', !0);
                       }
                    })
                }
            }
            // 父级选择
            $('.categorySelect').die().live('change', function() {
                if ($.browser.msie) {
                    
                }
                if (!($(this).find('option:selected').hasClass('nolimit'))) {
                    var currentVal = $(this).val();
                    var currentText = $(this).find('option:selected').text();
                    $(this).nextAll().remove();
                    getSubCateHtml(currentVal, '');
                }
            })
            // 生成列表
            function getSubCateHtml(currendId, dataArr) {
                var subCateLevelArr = new Array();
                if (dataArr.length) {
                    subCateLevelArr = dataArr;
                } else {
                    subCateLevelArr = getSubCateLevel(currendId,'').split('.');
                }
                var cateSubHtml = '';
                for(i = 0;i < subCateLevelArr.length-1; i++) {
                    cateSubHtml += '<select class="categorySelect">';
                    cateSubHtml += '<option class="nolimit" value="'+subCateLevelArr[i]+'" title="'+getNameNew(subCateLevelArr[i])+'">不限</option>';
                    var citySubArr = QS_city_spell[subCateLevelArr[i]].split('`');
                    for(j = 0;j < citySubArr.length; j++){
                        cateSubHtml += '<option value="'+citySubArr[j].split(',')[0]+'">'+citySubArr[j].split(',')[1]+'</option>';
                    }
                    cateSubHtml += '</select>';
                }
                $('#selectCategoryBox').append(cateSubHtml);
            }
            // 获得级数
            function getSubCateLevel(id, arr) {
                if (QS_city_spell[id]) {
                    var levelIdArr = QS_city_spell[id].split('`');
                    if (arr.length) {
                        arr = arr + '.' + id;
                    } else {
                        arr = id;
                    }
                    return getSubCateLevel(levelIdArr[0].split(',')[0],arr);
                } else {
                    return arr;
                }
            }
            // 根据id获取对应文字
            function getNameNew(id) {
                var name = '';
                $.each(QS_city_spell_parent, function (index, val) {
                    var parentValArr = val.split(',');
                    if (QS_city_spell[parentValArr[0]]) {
                        var subArr = QS_city_spell[parentValArr[0]].split('`');
                        for (var j = 0; j < subArr.length; j++) {
                            if (id == subArr[j].split(',')[0]) {
                                name = subArr[j].split(',')[1];
                            }
                        }
                    }
                })
                if (!name) {
                    $.each(QS_city_spell_parent, function (index, val) {
                        var parentValArr = val.split(',');
                        if (id == parentValArr[0]) {
                            name = parentValArr[1];
                        }
                    })
                }
                return name;
            }
        } else {
            // id
            var cateParentHtml = '<select class="categorySelect">';
            for (var i = 0; i < QS_city_parent.length; i++) {
                cateParentHtml += '<option value="' + QS_city_parent[i].split(',')[0] + '">' + QS_city_parent[i].split(',')[1] + '</option>';
            }
            cateParentHtml += '</select>';
            $('#selectCategoryBox').html(cateParentHtml);
            function getParentId(id) {
                for (var i = 0; i < QS_city.length; i++) {
                    if (QS_city[i]) {
                        var subArr = QS_city[i].split('`');
                        for (var j = 0; j < subArr.length; j++) {
                            if (id == subArr[j].split(',')[0]) {
                                if (ids) {
                                    ids =  i + '.' + ids;
                                } else {
                                    ids =  i;
                                }
                                getParentId(i);
                            }
                        }
                    }
                }
                return ids;
            }
            // 默认值和需要恢复的处理
            if (recoverVal.length) {
                ids = '';
                var firstKeepHtml = getParentId(recoverVal);
                if (firstKeepHtml) {
                    var firstHtml = firstKeepHtml + '.' + recoverVal;
                    var firstKeepArr = firstHtml.toString().split('.');
                    getSubCateHtml(firstKeepArr[0],firstKeepArr);
                    for (var i = 0; i < $('.categorySelect').length; i++) {
                         for (var j = 0; j < firstKeepArr.length; j++) {
                            $('.categorySelect').eq(i).find('option').each(function(index, el) {
                               if ($(this).val() == firstKeepArr[j]) {
                                    $(this).prop('selected', !0);
                               }
                            })
                        }
                    }
                } else {
                    $('.categorySelect').eq(0).find('option').each(function(index, el) {
                       if ($(this).val() == recoverVal) {
                            $(this).prop('selected', !0);
                       }
                    })
                    if (getSubCateLevel(recoverVal,'')) {
                        getSubCateHtml(recoverVal,'');
                    }
                }
            } else {
                if (defaultCity) {
                    var firstKeepArr = defaultCity.split('.');
                    getSubCateHtml(firstKeepArr[0], '');
                    for (var i = 0; i < $('.categorySelect').length; i++) {
                         for (var j = 0; j < firstKeepArr.length; j++) {
                            $('.categorySelect').eq(i).find('option').each(function(index, el) {
                               if ($(this).val() == firstKeepArr[j]) {
                                    $(this).prop('selected', !0);
                               }
                            })
                        }
                    }
                } else {
                    getSubCateHtml($('.categorySelect').eq(0).find('option:selected').val(),'');
                    $('.categorySelect').eq(0).find('option').each(function(index, el) {
                       if ($(this).val() == recoverVal) {
                            $(this).prop('selected', !0);
                       }
                    })
                }
            }
            // 父级选择
            $('.categorySelect').die().live('change', function() {
                if ($.browser.msie) {
                    
                }
                if (!($(this).find('option:selected').hasClass('nolimit'))) {
                    var currentVal = $(this).val();
                    var currentText = $(this).find('option:selected').text();
                    $(this).nextAll().remove();
                    getSubCateHtml(currentVal, '');
                }
            })
            // 生成列表
            function getSubCateHtml(currendId, dataArr) {
                var subCateLevelArr = new Array();
                if (dataArr.length) {
                    subCateLevelArr = dataArr;
                } else {
                    subCateLevelArr = getSubCateLevel(currendId,'').split('.');
                }
                var cateSubHtml = '';
                for(i = 0;i < subCateLevelArr.length-1; i++) {
                    cateSubHtml += '<select class="categorySelect">';
                    var citySubArr = QS_city[subCateLevelArr[i]].split('`');
                    cateSubHtml += '<option class="nolimit" value="'+subCateLevelArr[i]+'" title="'+getNameNew(subCateLevelArr[i])+'">不限</option>';
                    for(j = 0;j < citySubArr.length; j++){
                        cateSubHtml += '<option value="'+citySubArr[j].split(',')[0]+'">'+citySubArr[j].split(',')[1]+'</option>';
                    }
                    cateSubHtml += '</select>';
                }
                $('#selectCategoryBox').append(cateSubHtml);
            }
            // 根据id获取对应文字
            function getNameNew(id) {
                var name = '';
                for (var i = 0; i < QS_city.length; i++) {
                    if (QS_city[i]) {
                        var iArr = QS_city[i].split('`');
                        for (var j = 0; j < iArr.length; j++) {
                            if (id == iArr[j].split(',')[0]) {
                                name = iArr[j].split(',')[1];
                            }
                        }
                    }
                }
                if (!name) {
                    // 只有一级
                    for (var i = 0; i < QS_city_parent.length; i++) {
                        if (id == QS_city_parent[i].split(',')[0]) {
                            name = QS_city_parent[i].split(',')[1];
                        }
                    }
                }
                return name;
            }
            // 获得级数
            function getSubCateLevel(id, arr) {
                if (QS_city[id]) {
                    var levelIdArr = QS_city[id].split('`');
                    if (arr.length) {
                        arr = arr + '.' + id;
                    } else {
                        arr = id;
                    }
                    return getSubCateLevel(levelIdArr[0].split(',')[0],arr);
                } else {
                    return arr;
                }
            }
        }
    })
    
    // 确定
    $('#J_btn_yes_city').die().live('click', function() {
        var currentVal = $('.categorySelect:last').find('option:selected').val();
        var currentText = $('.categorySelect:last').find('option:selected').text();
        if ($('.categorySelect:last').find('option:selected').hasClass('nolimit')) {
            currentText = $('.categorySelect:last').find('option:selected').attr('title');
        }
        $('#searchCityModalCode').val(currentVal);
        $('#recoverSearchCityModalCode').val(currentVal);
        $('[data-toggle="funCityModal"]').text(currentText);
        $('[data-toggle="funCityModal"]').attr('title', currentText);
        removeModal();
    })

    //  初始化程序
    function prepareModal(titleValue, multipleValue, maximumValue) {
        var ie = !-[1, ];
        var ie6 = !-[1, ] && !window.XMLHttpRequest;
        $(backdropLayerTpl).appendTo($(document.body));
        if (ie6) {
            $('.modal_backdrop').css("height", $(document).height());
        }
        $(htmlLayerTpl).appendTo($(document.body));

        $('.J_modal_title').text(titleValue);
        multipleValue = true;
        if (multipleValue) {
            //$('.J_modal_max').text('（最多选择' + maximumValue + '个）');
        }
        if (!multipleValue) {
            $('.modal_footer').hide();
        }
        $(".J_hoverbut").hover(function() {
            $(this).addClass("hover");
        }, function() {
            $(this).removeClass("hover");
        })
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
                })
                var newx = ev.pageX - x;
                var newy = ev.pageY - y;
                newObj.css({
                    'left': newx + "px",
                    'top': newy + "px"
                })
            })
        })
        $(document).mouseup(function() {
            $(this).unbind("mousemove");
        })
        if (ie) {
            if (window.PIE) {
                $('.pie_about').each(function() {
                    PIE.attach(this);
                })
            }
        }
    }

    // 关闭
    $('.J_dismiss_modal').live('click', function() {
        removeModal();
    })
    // esc关闭
    $(document).on('keydown', function(event) {
        if (event.keyCode == 27) {
            removeModal();
        }
    })
    //关闭弹窗的公共方法
    function removeModal() {
        setTimeout(function() {
            $('.modal_backdrop').remove();
            $('.modal').remove();
        }, 50)
    }
}(window.jQuery);
