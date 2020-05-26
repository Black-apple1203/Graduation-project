/* ============================================================
 * jquery.disappear.tooltip.js
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

!function($) {

	// 圆角
	if (window.PIE) { 
        $('.pie_about').each(function() {
            PIE.attach(this);
        });
    }

    // 创建提示示例
	$('<div class="disappear_tooltip pie_about"><div class="icon"></div><div class="content"></div></div>').appendTo(document.body);
	$('.disappear_tooltip').css({
		left: ($(window).width() - $('.disappear_tooltip').outerWidth())/2,
		top: ($(window).height() - $('.disappear_tooltip').outerHeight())/2 + $(document).scrollTop()
	});

}(window.jQuery);

function disapperTooltip(className, remindContent,callback) {
	$('.disappear_tooltip').addClass('tip_anim_close');
	$('.disappear_tooltip').find('.content').html(remindContent);
	$('.disappear_tooltip').css({
		left: ($(window).width() - $('.disappear_tooltip').outerWidth())/2,
		top: ($(window).height() - $('.disappear_tooltip').outerHeight())/2 + $(document).scrollTop()
	});
	$('.disappear_tooltip').removeClass('goldremind');
	$('.disappear_tooltip').addClass(className);
	$('.disappear_tooltip').removeClass('tip_anim_close');
	$('.disappear_tooltip').addClass('tip_anim');
	if (window.PIE) { 
        $('.pie_about').each(function() {
            PIE.attach(this);
        });
    }
	setTimeout(function () {
		$('.disappear_tooltip').addClass('tip_anim_close');
		if (callback && typeof(callback) === "function") {
			callback();
	    }
	}, 2000);
}