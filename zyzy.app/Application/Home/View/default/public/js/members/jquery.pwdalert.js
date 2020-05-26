/* ============================================================
 * jquery.pwdalert.js 下拉js
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

!function($) {
	
	$('.J_passwordalert').keyup(function () { 
		var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g"); 
		var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g"); 
		var enoughRegex = new RegExp("(?=.{6,}).*", "g"); 
		var thisGroup = $(this).closest('.J_passwordalert_group');
	
		if (false == enoughRegex.test($(this).val())) { 
			thisGroup.find('.slist').removeClass('select'); 
			 //密码小于六位的时候，密码强度图片都为灰色 
		} 
		else if (strongRegex.test($(this).val())) { 
			thisGroup.find('.slist.t1').removeClass('select');
			thisGroup.find('.slist.t2').removeClass('select');
			thisGroup.find('.slist.t3').addClass('select');
			 //密码为八位及以上并且字母数字特殊字符三项都包括,强度最强 
		} 
		else if (mediumRegex.test($(this).val())) { 
			thisGroup.find('.slist.t1').removeClass('select');
			thisGroup.find('.slist.t2').addClass('select');
			thisGroup.find('.slist.t3').removeClass('select');
			 //密码为七位及以上并且字母、数字、特殊字符三项中有两项，强度是中等 
		} 
		else { 
			thisGroup.find('.slist.t1').addClass('select');
			thisGroup.find('.slist.t2').removeClass('select');
			thisGroup.find('.slist.t3').removeClass('select');
			 //如果密码为6为及以下，就算字母、数字、特殊字符三项都包括，强度也是弱的 
		} 
		return true; 
	});

}(window.jQuery);