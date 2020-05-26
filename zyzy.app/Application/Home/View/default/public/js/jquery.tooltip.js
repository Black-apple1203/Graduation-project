/* ============================================================
 * jquery.tooltip.js
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */

!function($) {

	// 定义开关
	var tooltipToggle = '.J_tooltip';
	
	$(tooltipToggle).hover(function() {
		var $this = $(this), isActive;
		if ($this.is('.disabled, :disabled')) return;
		isActive = $this.hasClass('open');
		if (!isActive) {
			$this.css('position', 'relative');
			$this.toggleClass('open');
		};
	}, function() {
		clearMenus();
	});

	function clearMenus() {
        $(tooltipToggle).each(function() {
            $(this).removeClass('open');
        })
    }

}(window.jQuery);