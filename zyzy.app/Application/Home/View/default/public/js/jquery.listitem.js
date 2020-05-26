/* ============================================================
 * jquery.listitem.js 下拉列表选择
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

!function($) {

  // 个人简历年份 相关
  var birthdayDate = new Date();
  var byear = birthdayDate.getFullYear();
  byear = byear - 16;
  var byearMin = byear - 49;
  var htmlYear = '', htmlYear1 = '', htmlYear2 = '';
	for (var i = byear; i >= byearMin; i--) {
		if (i > (byear-20)) {
			htmlYear += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		} else if (i > (byear-40)) {
			htmlYear1 += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		} else {
			htmlYear2 += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		}
  }

  $('.J_birthdy').each(function(index, el) {
    var $thisBirthdys = $(this).find('.J_birthday_box');
    $thisBirthdys.eq(0).html(htmlYear);
    $thisBirthdys.eq(1).html(htmlYear1);
    $thisBirthdys.eq(2).html(htmlYear2);
    $(this).closest('.J_listitme_parent').find('.J_birthday_prev').hide();
  })

	// 点击翻页
	$('.J_birthday_next').die().live('click', function(event) {
		var $thisParent = $(this).closest('.J_listitme_parent');
		var $thisBox = $thisParent.find('.J_birthday_box');
		var sub = 0; // 获取选中tab的下标
    	$thisBox.each(function(index, el) {
    		if ($(this).hasClass('active')) {
    			sub = index;
    		}
    	});
    	$thisBox.eq(sub+1).addClass("active").siblings(".J_birthday_box").removeClass("active");
    	if (sub == 1) {
    		$thisParent.find('.J_birthday_next').hide();
    	}
    	$thisParent.find('.J_birthday_prev').show();
	})

	$('.J_birthday_prev').die().live('click', function(event) {
		var $thisParent = $(this).closest('.J_listitme_parent');
		var $thisBox = $thisParent.find('.J_birthday_box');
		var sub = 0; // 获取选中tab的下标
    	$thisBox.each(function(index, el) {
    		if ($(this).hasClass('active')) {
    			sub = index;
    		}
    	});
    	$thisBox.eq(sub-1).addClass("active").siblings(".J_birthday_box").removeClass("active");
    	if (sub == 1) {
    		$thisParent.find('.J_birthday_prev').hide();
    	}
    	$thisParent.find('.J_birthday_next').show();
	})

	// 教育经历等相关年份
	var experienceDate = new Date();
  var eyear = experienceDate.getFullYear();
  var eyearMin = eyear - 59;
  var htmlYearexp = '', htmlYear1exp = '', htmlYear2exp = '';
    for (var i = eyear; i >= eyearMin; i--) {
		if (i > (eyear-20)) {
			htmlYearexp += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		} else if (i > (eyear-40)) {
			htmlYear1exp += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		} else {
			htmlYear2exp += [
				'<li>',
				'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '</a>',
				'</li>'
			].join('');
		}
  }

  $('.J_birthdy_exp').each(function(index, el) {
    var $thisExps = $(this).find('.J_birthday_box_exp');
    $thisExps.eq(0).html(htmlYearexp);
    $thisExps.eq(1).html(htmlYear1exp);
    $thisExps.eq(2).html(htmlYear2exp);
    $(this).closest('.J_listitme_parent').find('.J_birthday_prev_exp').hide();
  })

  // 点击翻页
	$('.J_birthday_next_exp').die().live('click', function(event) {
		var $thisParent = $(this).closest('.J_listitme_parent');
		var $thisBox = $thisParent.find('.J_birthday_box_exp');
		var sub = 0; // 获取选中tab的下标
    	$thisBox.each(function(index, el) {
    		if ($(this).hasClass('active')) {
    			sub = index;
    		}
    	});
    	$thisBox.eq(sub+1).addClass("active").siblings(".J_birthday_box_exp").removeClass("active");
    	if (sub == 1) {
    		$thisParent.find('.J_birthday_next_exp').hide();
    	}
    	$thisParent.find('.J_birthday_prev_exp').show();
	})

	$('.J_birthday_prev_exp').die().live('click', function(event) {
		var $thisParent = $(this).closest('.J_listitme_parent');
		var $thisBox = $thisParent.find('.J_birthday_box_exp');
		var sub = 0; // 获取选中tab的下标
    $thisBox.each(function(index, el) {
      if ($(this).hasClass('active')) {
        sub = index;
      }
    })
    $thisBox.eq(sub-1).addClass("active").siblings(".J_birthday_box_exp").removeClass("active");
    if (sub == 1) {
      $thisParent.find('.J_birthday_prev_exp').hide();
    }
    $thisParent.find('.J_birthday_next_exp').show();
	})

	// 个人简历月份 相关
	var htmlMonth = '';
	for (var i = 1; i <= 12; i++) {
		htmlMonth += [
			'<li>',
			'<a class="J_listitme" href="javascript:;" data-code="' + i + '">' + i + '月</a>',
			'</li>'
		].join('');
	}
	$('.J_month').each(function(index, el) {
    $(this).find('.J_month_box').html(htmlMonth);
  })

	var $obj = $('.J_listitme');
	$obj.live('click', function() {
		var $thisParent = $(this).closest('.J_listitme_parent');
		var thisText = $(this).text();
		var thisCode = $(this).data('code');
		$thisParent.find('.J_listitme_text').text(thisText);
		$thisParent.find('.J_listitme_code').val(thisCode); // 隐藏input赋值
		hideMenus();
	})

	// 下拉多选
  var $objM = $('.J_listitme_m');
  $objM.die().live('click', function() {
    var $thisParent = $(this).closest('.J_listitme_parent');
    var thisText = $(this).data('title');
    var thisCode = $(this).data('code');
    function syncListM() {
      var checkedArray = $thisParent.find('.J_listitme_m:checked');
      var codeArray = new Array();
      var titleArray = new Array();
      $.each(checkedArray, function(index, val) {
        codeArray[index] = $(this).data('code');
        titleArray[index] = $(this).data('title');
      });
      $thisParent.find('.J_listitme_text').text(titleArray.length ? titleArray.join('+') : $thisParent.find('.J_listitme_text').data('title'));
      $thisParent.find('.J_listitme_text').attr('title', titleArray.length ? titleArray.join('+') : $thisParent.find('.J_listitme_text').data('title'));
      $thisParent.find('.J_listitme_code').val(codeArray.join(','));
    }
    if ($(this).is(':checked')) {
      // 已选中
      if (!eval(thisCode)) {
        // 不限
        $thisParent.find('.J_listitme_m').each(function(index, el) {
          if (index) {
            $(this).prop('checked', !0);
            $(this).prop('disabled', !0);
          }
        });
        $thisParent.find('.J_listitme_text').text(thisText);
        $thisParent.find('.J_listitme_text').attr('title', thisText);
        $thisParent.find('.J_listitme_code').val(thisCode);
        hideMenus();
      } else {
        var othersArray = $thisParent.find('.J_listitme_m');
        var thisIndex = $thisParent.find('.J_listitme_m').index($(this));
        var checkedNum = $thisParent.find('.J_listitme_m:checked').length;
        if (checkedNum <= 1) {
          $.each(othersArray, function (index, value) {
            if (index > thisIndex) {
              $(this).prop('checked', !0);
            }
          })
        }
        syncListM();
      }
    } else {
      // 未选中
      if (!eval(thisCode)) {
        // 不限
        $thisParent.find('.J_listitme_m').each(function(index, el) {
          if (index) {
            $(this).prop('checked', 0);
            $(this).prop('disabled', 0);
          }
        });
      }
      syncListM();
    }
  });

	function hideMenus() {
    $('.J_tooltip').each(function() {
      $(this).removeClass('open');
    })
    $('.J_dropdown').each(function() {
      $(this).removeClass('open');
    })
  }

  // 单选按钮点击
  var $radiobj = $('.J_radioitme');
  $radiobj.live('click', function(event) {
    $(this).addClass("checked").siblings(".J_radioitme").removeClass("checked");
    var $thisParent = $(this).closest('.J_radioitme_parent');
    var thisCode = $(this).data('code');
    $thisParent.find('.J_radioitme_code').val(thisCode); // 隐藏input赋值
  })

}(window.jQuery);