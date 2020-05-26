/* ============================================================
 * jquery.allselected.js  全选
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	$('.J_allSelected').click(function() {
		var isChecked = $(this).is(':checked');
		var listArray = $('.J_allListBox .J_allList');
		$.each(listArray, function(index, val) {
			$(this).prop('checked', isChecked);
		});
	});
	$('.J_allList').click(function() {
		var isChecked = $(this).is(':checked');
		if (isChecked) {
			var listArray = $('.J_allListBox .J_allList');
			var listCheckedArray = $('.J_allListBox .J_allList:checked');
			if (listArray.length == listCheckedArray.length) {
				$('.J_allSelected').prop('checked', isChecked);
			};
		} else {
			$('.J_allSelected').prop('checked', isChecked);
		}
	});
	
}(window.jQuery);