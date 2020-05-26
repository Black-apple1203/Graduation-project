/* ============================================================
 * jquery.resumelist.js  简历搜索列表页面js集合
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	// 搜索类型切换
	$('.J_sli').click(function() {
		$(this).addClass('select').siblings().removeClass('select');
		var indexValue = $('.J_sli').index(this);
		var typeValue = $.trim($(this).data('type'));
		$('input[name="search_type"]').val(typeValue);
	});

	// 收起、展开筛选条件
	foldAction('.J_showbtn', '.J_so_condition');
	function foldAction(trigger, performer) {
		$(trigger).click(function() {
			$(this).addClass('none').siblings().removeClass('none');
			var indexValue = $(trigger).index(this);
			if (indexValue) {
				$(performer).slideUp();
			} else {
				$(performer).slideDown();
			}
		})
	}
	$('.J_showJobConditions').die().live('click', function(event) {
		$(this).addClass('none').siblings().removeClass('none');
		var indexValue = $('.J_showJobConditions').index(this);
		if (indexValue) {
			$('.for_up').slideDown();
		} else {
			$('.for_up').slideUp();
		}
	});

	// 列表详细和简易切换
	$('.J_detailList').click(function() {
		$(this).addClass('select').siblings('.J_detailList').removeClass('select');
		var indexValue = $('.J_detailList').index(this),
			type = $(this).attr('show_type');
		if (indexValue) {
			$('.J_allListBox').find('.detail').hide();
			$('.J_allListBox').find('.J_resumeStatus').addClass('show');
		} else {
			$('.J_allListBox').find('.detail').show();
			$('.J_allListBox').find('.J_resumeStatus').removeClass('show');
		}
		$.getJSON(qscms.root + '?m=Home&c=AjaxCommon&a=list_show_type',{action:'resume',type:type});
	});

	// 周边人才和热门人才切换
	$('.J_resume_hotnear').click(function() {
		$(this).addClass('select').siblings('.J_resume_hotnear').removeClass('select');
		var indexValue = $('.J_resume_hotnear').index(this);
		$('.J_resume_hotnear_show').removeClass('show');
		$('.J_resume_hotnear_show').eq(indexValue).addClass('show');
	});

	// 列表详细展开收起
	$('.J_resumeStatus').click(function(){
		if($(this).hasClass('show')){
			$(this).removeClass('show');
			$(this).closest('.J_resumeList').find('.detail').show();
		}else{
			$(this).addClass('show');
			$(this).closest('.J_resumeList').find('.detail').hide();
		}
	});

	// 全选、反选
	$('.J_allSelected').click(function() {
		var isChecked = $(this).hasClass('select');
		var listArray = $('.J_allListBox .J_allList');
		if (isChecked) {
			$(this).removeClass('select');
			$.each(listArray, function(index, val) {
				$(this).removeClass('select');
			});
			$('.J_resumeList').removeClass('select');
		} else {
			$(this).addClass('select');
			$.each(listArray, function(index, val) {
				$(this).addClass('select');
			});
			$('.J_resumeList').addClass('select');
		}
		
	});
	$('.J_allList').click(function(){
		var isChecked = $(this).hasClass('select');
		if (isChecked) {
			$(this).removeClass('select');
			$(this).closest('.J_resumeList').removeClass('select');
			$('.J_allSelected').removeClass('select');
		} else {
			$(this).addClass('select');
			$(this).closest('.J_resumeList').addClass('select');
			var listArray = $('.J_allListBox .J_allList');
			var listCheckedArray = $('.J_allListBox .J_allList.select');
			if (listArray.length == listCheckedArray.length) {
				$('.J_allSelected').addClass('select');
			}
		}
	});

	

	// 判断列表中是否有选中的项目
	function listCheckEmpty() {
		var listCheckedArray = $('.J_allListBox .J_allList.select');
		if (listCheckedArray.length) {
			return false;
		} else {
			return true;
		}
	}

	// 专业类别相关js
	var majorValue = $('input[name="major"]').val();
	if (majorValue) {
		if(!majorValue.length) {
			$('.tab_list').eq(0).addClass('select');
			$('.tab_content').eq(0).addClass('select');
		} else {
			var recoverIndex = $('.tab_list').index($('.tab_list.select'));
			$('.tab_content').eq(recoverIndex).addClass('select');
		}
	} else {
		$('.tab_list').eq(0).addClass('select');
		$('.tab_content').eq(0).addClass('select');
	}
	$('.tab_list').click(function () {
		$(this).addClass('select').siblings().removeClass('select');
		var thisIndex = $('.tab_list').index(this);
		$('.tab_content').eq(thisIndex).addClass('select').siblings('.tab_content').removeClass('select');
	});

	// 关键字改变，搜索条件清空
	$('#ajax_search_location').submit(function(){
		var nowKeyValue = $.trim($('input[name="key"]').val());
		var orgKeyValue = $.trim($('input[name="key"]').data('original'));
		if(nowKeyValue.length && nowKeyValue.length<2){
			disapperTooltip("remind",'关健字长度需大于2个字！');
			return !1;
		}
		if (!(nowKeyValue == orgKeyValue)) {
			$('.J_forclear').val('');
		}
		$('input[name="key"]').val(htmlspecialchars($('input[name="key"]').val()));
		var post_data = $('#ajax_search_location').serialize();
		if(qscms.keyUrlencode==1){
			post_data = encodeURI(post_data);
		}
		$.post($('#ajax_search_location').attr('action'),post_data,function(result){
			window.location=result.data;
		},'json');
		return false;
	});
}(window.jQuery);