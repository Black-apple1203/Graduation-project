/* ============================================================
 * 会员中心选择地区
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {
    var backdropLayerTpl = '<div class="modal_backdrop fade"></div>';
    var htmlLayerTpl = ['<div class="modal qs-category-unlimited">', '<div class="modal_dialog">', '<div class="modal_content pie_about">', '<div class="modal_header">', '<span class="title J_modal_title"></span>', '<span class="max_remind J_modal_max"></span>', '<a href="javascript:;" class="close J_dismiss_modal"></a>', '</div>', '<div class="modal_body pd0">', '<div class="listed_group" id="J_listed_group">', '<div class="left_text">已选择：</div>', '<div class="center_text" id="J_listed_content"></div>', '<a href="javascript:;" class="right_text" id="J_listed_clear">清空</a>', '<div class="clear"></div>', '</div>', '<div class="J_modal_content"></div>', '</div>', '<div class="modal_footer">', '<div class="res_add_but">', '<div class="butlist">', '<div class="btn_blue J_hoverbut btn_100_38 J_btn_yes">确 定</div>', '</div>', '<div class="butlist">', '<div class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btn_cancel">取 消</div>', '</div>', '<div class="clear"></div>', '</div>', '</div>', '<input type="hidden" class="J_btnload" />', '</div>', '</div>', '</div>'].join('');
    // 处理需要复原的数据
    if ($('.J_resultcode_city').val().length) {
        var codeArr = $('.J_resultcode_city').val().split(',');
        var ciSub = qscms.district_level-1;
        var newCodeArr = new Array();
        for (var i = 0; i < codeArr.length; i++) {
            var sArr = codeArr[i].split('.');
            if (eval(sArr[sArr.length-1])) {
                newCodeArr.push(sArr[sArr.length-1]);
            } else {
                if (eval(sArr[sArr.length-2])) {
                    newCodeArr.push(sArr[sArr.length-2]);
                } else {
                    newCodeArr.push(sArr[sArr.length-3]);
                }
            }
        }
        $('.J_resultcode_city').val(newCodeArr);
    }
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
        var htmlCategory = '';
        var cateLevel = qscms.district_level;
        var checkedPool = new Array();
        var defaultKeyArr = '';
        var recoverVal = $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val();
        var recoverKeepVal = $('[data-toggle="funCityModal"]').find('.J_resultcode_city').attr('keep');
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
        if (cateLevel > 1) {
            // 地区级数大于一级
            var cateParentHtml = '<select class="categorySelect">';
            for (var i = 0; i < QS_city_parent.length; i++) {
                cateParentHtml += '<option value="' + QS_city_parent[i].split(',')[0] + '">' + QS_city_parent[i].split(',')[1] + '</option>';
            }
            cateParentHtml += '</select>';
            $('#selectCategoryBox').html(cateParentHtml);
            // 默认值和需要恢复的处理
            if (recoverVal.length) {
                var recoverKeepArr = recoverKeepVal.split(',');
                var firstKeppArr = recoverKeepArr[0].split('.');
                var subCateLevelArr = new Array();
                if (firstKeppArr.length > 1) {
                    subCateLevelArr = firstKeppArr.slice(0, firstKeppArr.length-1);
                    getSubCateHtml(firstKeppArr[0],subCateLevelArr);
                    for (var i = 0; i < $('.categorySelect').length; i++) {
                         for (var j = 0; j < subCateLevelArr.length; j++) {
                            $('.categorySelect').eq(i).find('option').each(function(index, el) {
                               if ($(this).val() == subCateLevelArr[j]) {
                                    $(this).prop('selected', !0);
                               }
                            })
                        }
                    }
                    setCheckCode();
                    setPool(recoverKeepVal.split(','), 0);
                } else {
                    $('.categorySelect').eq(0).find('option').each(function(index, el) {
                       if ($(this).val() == firstKeppArr) {
                            $(this).prop('selected', !0);
                       }
                    })
                    getSubCateHtml(firstKeppArr[0],firstKeppArr);
                    setCheckCode();
                    setPool(recoverKeepVal.split(','), 1);
                }
            } else {
                if (defaultCity) {
                    // 默认地区
                    var firstKeppArr = defaultCity.toString().split('.');
                    if (firstKeppArr.length > 1) {
                        getSubCateHtml(firstKeppArr[0],firstKeppArr);
                        for (var i = 0; i < $('.categorySelect').length; i++) {
                             for (var j = 0; j < firstKeppArr.length; j++) {
                                $('.categorySelect').eq(i).find('option').each(function(index, el) {
                                   if ($(this).val() == firstKeppArr[j]) {
                                        $(this).prop('selected', !0);
                                   }
                                })
                            }
                        }
                        setCheckCode();
                    } else {
                        $('.categorySelect').eq(0).find('option').each(function(index, el) {
                           if ($(this).val() == firstKeppArr) {
                                $(this).prop('selected', !0);
                           }
                        })
                        getSubCateHtml(firstKeppArr[0],firstKeppArr);
                        setCheckCode();
                    }
                } else {
                    getSubCateHtml($('.categorySelect').eq(0).find('option:selected').val(),'');
                    setCheckCode();
                }
            }
        } else {
            // 只有一级
            $('.category-select').remove();
            var checkboxHtml = '<div class="dl">';
            checkboxHtml += '<div class="dt"><span>选择地区</span></div>';
            checkboxHtml += '<div class="dd"><ul>';
            for (var i = 0; i < QS_city_parent.length; i++) {
                var arrP = QS_city_parent[i].split(',');
                checkboxHtml += '<li><div class="one-select"><label><input class="check-box checkOption" type="checkbox" data-code="' + arrP[0] + '" data-text="' + arrP[1] + '">' + arrP[1] + '</label></div></li>';
            }
            checkboxHtml += '</ul></div>';
            checkboxHtml += '<div class="clear"></div></div>';
            $('#checkboxCategoryBox').html(checkboxHtml);
            if (recoverVal.length) {
                setPool(recoverVal.split(','), 1);
            }
        }
        // 复原选中和同步
        recoverCheckbox();
        if (multipleValue) {
            syncOptionSelected();
        }
        // 父级选择
        $('.categorySelect').die().live('change', function() {
            if ($.browser.msie) {
                
            }
            var currentVal = $(this).val();
            $(this).nextAll().remove();
            $('#checkboxCategoryBox').empty();
            getSubCateHtml(currentVal, '');
            setCheckCode();
            recoverCheckbox();
        })
        // 动态替换code
        function setCheckCode() {
            var checkId = '';
            $('.categorySelect').each(function(index, el) {
                checkId += $('.categorySelect').eq(index).find('option:selected').val() + '.';
            })
            $('.checkOption').each(function(index, el) {
                if (!$(this).hasClass('noLimit')) {
                    $(this).data('code', checkId + $(this).data('code'));
                } else {
                    if ($('.categorySelect').length > 1) {
                        $(this).data('code', checkId + $(this).data('code'));
                    }
                }
            })
        }
        // 设置还原数据
        function setPool(poolArr, cateType) {
            for (var i = 0; i < poolArr.length; i++) {
                var itemName = '';
                if (cateType) {
                    itemName = getName(poolArr[i].split('.')[poolArr[i].split('.').length-1], 1);
                } else {
                    itemName = getName(poolArr[i].split('.')[poolArr[i].split('.').length-1], 0);
                }
                checkedPool.push(poolArr[i] + '`' + itemName);
            }
        }
        // 设置第一级的选中状态
        function setSelected(comparId) {
            $('.categorySelect').eq(0).find('option').each(function(index, el) {
                if ($(this).val() == comparId) {
                    $(this).prop('selected', !0);
                }
            })
        }
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
                for(j = 0;j < citySubArr.length; j++){
                    cateSubHtml += '<option value="'+citySubArr[j].split(',')[0]+'">'+citySubArr[j].split(',')[1]+'</option>';
                }
                cateSubHtml += '</select>';
            }
            $('#selectCategoryBox').append(cateSubHtml);
            var cateChekcId = subCateLevelArr[subCateLevelArr.length-1];
            var checkName = $('.categorySelect').eq($('.categorySelect').length-1).find('option:selected').text();
            if (getSubCateLevel(currendId,'')) {
                $('#checkboxCategoryBox').html(checkboxFactory(cateChekcId, getNameNew(cateChekcId)));
            } else {
                $('#checkboxCategoryBox').html(checkboxFactory(currendId, getNameNew(currendId)));
            }
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
        // 最后一级点击
        $('.checkOption').die().live('click', function() {
            // 多选单选
            if (multipleValue) {
                var poolSub = $(this).data('code') + '`' + $(this).data('text');
                // 是否选中
                if ($(this).is(':checked')) {
                    if ((checkedPool.length + 1) > maximumValue) {
                        $(this).prop('checked', 0);
                        disapperTooltip('remind', '最多选择' + maximumValue + '个');
                        return false;
                    } else {
                        checkedPool.push(poolSub);
                        $(this).closest('label').addClass('selected');
                    }
                } else {
                    checkedPool.splice($.inArray(poolSub, checkedPool), 1);
                    $(this).closest('label').removeClass('selected');
                }
                syncOptionSelected();
            } else {
                var thisCode = $(this).data('code');
                var thisText = $(this).data('text');
                if (!$(this).hasClass('noLimit')) {
					
				if ($('.categorySelect').length > 1 || $('.categorySelect').length==1) {
                        $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val(thisCode.split('.')[thisCode.split('.').length-1]);
                    } else {
                        $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val(thisCode);
                    }
                } else {
                    if ($('.categorySelect').length > 1) {
                        $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val(thisCode.split('.')[thisCode.split('.').length-1]);
                    } else {
                        $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val(thisCode);
                    }
                }
                $('[data-toggle="funCityModal"]').find('.J_resultcode_city').attr('keep', thisCode);
                $('[data-toggle="funCityModal"]').find('.J_resuletitle_city').text(thisText);
                $('[data-toggle="funCityModal"]').find('.J_resuletitle_city').attr('title', thisText);
                removeModal();
            }
        })
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
        // 根据id获取对应文字
        function getName(id, pid) {
            var name = '';
            if (pid) {
                // 只有一级
                for (var i = 0; i < QS_city_parent.length; i++) {
                    if (id == QS_city_parent[i].split(',')[0]) {
                        name = QS_city_parent[i].split(',')[1];
                    }
                }
            } else {
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
            }
            return name;
        }
        // 复原checkbox选中
        function recoverCheckbox() {
            for (var i = 0; i < checkedPool.length; i++) {
                $('.checkOption').each(function(index, el) {
                    if ($(this).data('code') == checkedPool[i].split('`')[0]) {
                        $(this).closest('label').addClass('selected');
                        $(this).prop('checked', !0);
                    }
                })
            }
        }
        // 清空
        $('#J_clear_all').die().live('click', function() {
            checkedPool.splice(0, checkedPool.length);
            $('.checkOption:checked').each(function() {
                $(this).prop('checked', 0);
                $(this).closest('label').removeClass('selected');
            })
            syncOptionSelected();
        })
        // 确定
        $('#J_btn_yes_city').die().live('click', function() {
            var checkedArray = $('.checkOption:checked');
            var codeArray = new Array();
            var textArray = new Array();
            var keepArray = new Array();
            for (var i = 0; i < checkedPool.length; i++) {
                keepArray[i] = checkedPool[i].split('`')[0];
                codeArray[i] = checkedPool[i].split('`')[0].split('.')[checkedPool[i].split('`')[0].split('.').length-1];
                textArray[i] = checkedPool[i].split('`')[1];
            }
            $('[data-toggle="funCityModal"]').find('.J_resultcode_city').val(codeArray.join(','));
            $('[data-toggle="funCityModal"]').find('.J_resultcode_city').attr('keep', keepArray.join(','));
            $('[data-toggle="funCityModal"]').find('.J_resuletitle_city').text(textArray.length ? textArray.join('+') : '请选择');
            $('[data-toggle="funCityModal"]').find('.J_resuletitle_city').attr('title', textArray.length ? textArray.join('+') : '请选择');
            removeModal();
        })
        // 同步
        function syncOptionSelected() {
            if (checkedPool.length) {
                var checkedHtml = '';
                for (var i = 0; i < checkedPool.length; i++) {
                    var pollArr = checkedPool[i].split('`');
                    checkedHtml += '<div class="s-cell" data-code="' + pollArr[0] + '" data-text="' + pollArr[1] + '"><span>' + pollArr[1] + '</span><i class="J_s_i"></i></div>';
                }
                $('.J_cate_box').html(checkedHtml);
                $('.J_selected_group').addClass('open');
            } else {
                $('.J_selected_group').removeClass('open');
            }
            $('.J_s_i').die().live('click', function() {
                var sCode = $(this).closest('.s-cell').data('code');
                var sSub = $(this).closest('.s-cell').data('code') + '`' + $(this).closest('.s-cell').data('text');
                checkedPool.splice($.inArray(sSub, checkedPool), 1);
                $('.checkOption:checked').each(function() {
                    if ($(this).data('code') == sCode) {
                        $(this).prop('checked', 0);
                        $(this).closest('label').removeClass('selected');
                    }
                })
                syncOptionSelected();
            })
        }
        // 生成checkbox
        function checkboxFactory(id, title) {
            var checkboxHtml = '<div class="dl">';
            checkboxHtml += '<div class="dt"><span>' + getNameNew(id) + '</span></div>';
            checkboxHtml += '<div class="dd"><ul>';
            if (QS_city[id]) {
                var checkArr = QS_city[id].split('`');
                for (var i = 0; i < checkArr.length; i++) {
                    var arrP = checkArr[i].split(',');
                    checkboxHtml += '<li><div class="one-select"><label><input class="check-box checkOption" type="checkbox" data-code="' + arrP[0] + '" data-text="' + getNameNew(arrP[0]) + '">' + getNameNew(arrP[0]) + '</label></div></li>';
                }
            } else {
                id = $('.categorySelect').eq($('.categorySelect').length-1).find('option:selected').val();
                checkboxHtml += '<li><div class="one-select"><label><input class="check-box checkOption noLimit" type="checkbox" data-code="' + id + '" data-text="' + getNameNew(id) + '">不限</label></div></li>';
            }
            checkboxHtml += '</ul></div>';
            checkboxHtml += '<div class="clear"></div></div>';
            return checkboxHtml;
        }
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
        if (multipleValue) {
            $('.J_modal_max').text('（最多选择' + maximumValue + '个）');
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
    //关闭弹窗的公共方法
    function removeModal() {
        setTimeout(function() {
            $('.modal_backdrop').remove();
            $('.modal').remove();
        }, 50)
    }
}(window.jQuery);
