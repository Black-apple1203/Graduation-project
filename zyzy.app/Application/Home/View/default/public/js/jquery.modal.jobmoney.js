
/* ============================================================
 * jquery.modal.jobmoney.js  红包职位弹出框
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	var htmlLayerTplMoney = [
			'<div class="modalfast">',
	            '<div class="modal_dialog">',
	                '<div class="modal_content pie_about">',
	                    '<div class="modal_header">',
							'<span class="title J_modal_title"></span>',
	                        '<span class="max_remind J_modal_max"></span>',
	                        '<a href="javascript:;" class="close J_dismiss_modal_fast"></a>',
						'</div>',
	                    '<div class="modal_body">',
	                    	'<div class="listed_group" id="J_listed_group">',
	                    		'<div class="left_text">已选择：</div>',
	                    		'<div class="center_text" id="J_listed_content"></div>',
	                    		'<a href="javascript:;" class="right_text" id="J_listed_clear">清空</a>',
	                    		'<div class="clear"></div>',
	                    	'</div>',
	                    	'<div class="J_modal_content_modalfast"></div>',
	                    '</div>',
	                    '<div class="modal_footer">',
	                        '<div class="res_add_but">',
	                        	'<div class="butlist">',
	                            	'<div class="btn_blue J_hoverbut btn_100_38 J_btnyes">确 定</div>',
	                            '</div>',
	                            '<div class="butlist">',
	                            	'<div class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal_fast J_btncancel">取 消</div>',
	                            '</div>',
	                            '<div class="clear"></div>',
	                        '</div>',
	                    '</div>',
	                    '<input type="hidden" class="J_btnload" />',
	                '</div>',
	            '</div>',
	        '</div>'
		].join('');

	// 点击显示职位分类
	$('#J_showmodal_jobs_money').live('click', function() {
		var titleValue = $(this).data('title');
		var multipleValue = eval($(this).data('multiple'));
		var maxNumValue = eval($(this).data('maxnum'));
		var widthValue = eval($(this).data('width'));
		var htmlJobs = '';
		var categoryValue = eval($(this).data('category'));
		var classifyModel = 0; // 标记二级还是三级分类
		categoryValue > 2 ? classifyModel = 0 : classifyModel = 1;

		if (classifyModel) { // 二级分类
			if (QS_jobs_parent) {
				htmlJobs += '<div class="modal_body_box modal_body_box3">';
				htmlJobs += '<div class="left_box">';
				htmlJobs += '<ul class="list_nav">';
				for (var i = 0; i < QS_jobs_parent.length; i++) {
					if (QS_jobs_parent[i].split(',')) {
						var iArray = QS_jobs_parent[i].split(',');
						htmlJobs += [
							'<li class="J_list_jobs_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">',
								'<label>' + iArray[1] + '</label>',
							'</li>'
						].join('');
					};
				};
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
									htmlJobs += [
										'<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
											'<label>不限</label>',
										'</li>'
									].join('');
									for (var j = 0; j < jobs11Array.length; j++) {
										if (jobs11Array[j].split(',')) {
											var jArray = jobs11Array[j].split(',');
											htmlJobs += [
												'<li class="J_list_jobs" data-code="' + jobs1Array[0] + '.' + jArray[0] + '.0" data-title="' + jArray[1] + '">',
													'<label>' + jArray[1] + '</label>',
												'</li>'
											].join('');
										};
									};
									htmlJobs += '</ul>';
								}
							} else {
				                var jobs11Array = QS_jobs[jobs1Array[0]].split('`');
				                htmlJobs += '<ul class="list_nav J_list_jobs_group">';
				                htmlJobs += [
				                  '<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
				                  '<label>不限</label>',
				                  '</li>'
				                ].join('');
				                htmlJobs += '</ul>';
				            }
						}
					}
				};
				htmlJobs += '</div>';
				htmlJobs += '<div class="clear"></div>';
				htmlJobs += '</div>';
			}

            prepareModalMoney(titleValue, multipleValue, maxNumValue);

			$('.J_modal_content_modalfast').html(htmlJobs);
		    $('.J_btnyes').attr('id', 'J_btnyes_jobs');
		    $('.J_modal_content_modalfast .right_box .list_nav').eq(0).show();
		    $('.J_list_jobs_parent').eq(0).addClass('current');

			$('.modalfast .modal_dialog').css({
				width: widthValue + 'px',
		    	left: ($('.J_modal_job_money').width() - widthValue)/2,
		    	top: ($('.J_modal_job_money').height() - $('.modalfast .modal_dialog').outerHeight())/2
		    });

		    $('.J_list_jobs_parent').live('click', function() {
		    	$(this).addClass('current').siblings('.J_list_jobs_parent').removeClass('current');
		    	var subscriptValue = $('.J_list_jobs_parent').index(this);
		    	$('.J_list_jobs_group').eq(subscriptValue).show().siblings('.J_list_jobs_group').hide();
		    });

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
		    		};
		    		var thisGroup = $(this).closest('.J_list_jobs_group');
		    		var subscriptValue = $('.J_list_jobs_group').index(thisGroup);
		    		if (!thisGroup.find('.seledted').length) {
		    			$('.J_list_jobs_parent').eq(subscriptValue).removeClass('seledted').addClass('current');
		    		};
		    	} else {
		    		$(this).addClass('seledted');
		    		if (multipleValue) {
		    			if (!$(this).is('.J_list_jobs_nolimit')) {
	    					var thisGroup = $(this).closest('.J_list_jobs_group');
	    					thisGroup.find('.J_list_jobs_nolimit').removeClass('seledted');
	    				};
		    			if ($('.J_list_jobs.seledted').length > maxNumValue) {
		    				$(this).removeClass('seledted');
		    				disapperTooltip("remind", '最多选择'+ maxNumValue +'个');
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
						$('#J_showmodal_jobs_money .J_resultcode_jobs').val(code);
						$('#J_showmodal_jobs_money .J_resuletitle_jobs').text(title);
						$('#J_showmodal_jobs_money .J_resuletitle_jobs').attr('title', title);
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
		    		htmlListed += [
						'<div class="listed_item_parent J_listed_jobs" data-code="' + listedCode + '" data-title="' + listedTitle + '">',
							'<a href="javascript:;" class="listed_item">',
								'<span>' + listedTitle + '</span><div class="del"></div>',
							'</a>',
						'</div>'
					].join('');
		    	});
		    	$('#J_listed_content').html(htmlListed);
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
			    		};
					};
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
				$('#J_showmodal_jobs_money .J_resultcode_jobs').val(codeArray.join(','));
				;
				$('#J_showmodal_jobs_money .J_resuletitle_jobs').text(titleArray.length ? titleArray.join('+') : '请选择');
				$('#J_showmodal_jobs_money .J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModalMoney();
		    });
		} else { // 三级分类
			if (QS_jobs_parent) {
				var job2Array = new Array();
				var htmlJobs = '<div class="modal_body_box modal_body_box2">';
				htmlJobs += '<div class="item">';
				htmlJobs += '<ul class="list_nav">';
				for (var i = 0; i < QS_jobs_parent.length; i++) {
					if (QS_jobs_parent[i].split(',')) {
						var iArray = QS_jobs_parent[i].split(',');
						htmlJobs += [
							'<li class="J_list_jobs_parent">',
								'<label>' + iArray[1] + '</label>',
							'</li>'
						].join('');
					};
				};
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
											htmlJobs += [
												'<li class="J_list_jobs_parent1">',
													'<label>' + jArray[1] + '</label>',
												'</li>'
											].join('');
											job2Array.push(jobs1Array[0]+'.'+jArray[0]+'.'+jArray[1]);
										};
									};
									htmlJobs += '</ul>';
								};
							} else {
								// 无二级分类
                                job2Array.push(jobs1Array[0] + '.0.此分类下无子分类!');
                                htmlJobs += '<ul class="list_nav J_list_jobs_group1">';
                                htmlJobs += ['<li class="J_list_jobs_parent1">', '<label>此分类下无子分类!</label>', '</li>'].join('');
                                htmlJobs += '</ul>';
							}
						}
					};
				};
				htmlJobs += '</div>';
				htmlJobs += '<div class="item">';
				if (job2Array) {
					for (var i = 0; i < job2Array.length; i++) {
						if (job2Array[i].split('.')) {
							var combinationArray = job2Array[i].split('.')
							if (QS_jobs[combinationArray[1]]) {
								if (QS_jobs[combinationArray[1]].split('`')) {
									var job22Array = QS_jobs[combinationArray[1]].split('`');
									htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
									htmlJobs += [
										'<li>',
											'<label>',
												'<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ',
											'不限</label>',
										'</li>'
									].join('');
									for (var j = 0; j < job22Array.length; j++) {
										if (job22Array[j].split(',')) {
											var jArray = job22Array[j].split(',');
											htmlJobs += [
												'<li>',
													'<label>',
														'<input class="J_list_jobs" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '"> ',
													'' + jArray[1] + '</label>',
												'</li>'
											].join('');
										};
									};
									htmlJobs += '</ul>';
								};
							} else {
								htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
								if (eval(combinationArray[1]) == 0) {
									htmlJobs += [
										'<li>',
											'<label>',
											'此分类下无子分类!</label>',
										'</li>'
									].join('');
								} else {
									htmlJobs += [
										'<li>',
											'<label>',
												'<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ',
											'不限</label>',
										'</li>'
									].join('');
								}
								htmlJobs += '</ul>';
							}
						};
					};
				};
				htmlJobs += '</div>';
				htmlJobs += '<div class="clear"></div>';
				htmlJobs += '</div>';
			};

            prepareModalMoney(titleValue, multipleValue, maxNumValue);
			
			$('.J_modal_content_modalfast').html(htmlJobs);
		    $('.J_btnyes').attr('id', 'J_btnyes_jobs');
		    $('.J_modal_content_modalfast .item').eq(0).find('.list_nav').show();
		    $('.J_list_jobs_parent').eq(0).addClass('current');
		    $('.J_list_jobs_parent1').eq(0).addClass('current');
		    $('.J_list_jobs_group1').eq(0).show();
		    $('.J_list_jobs_group2').eq(0).show();

			$('.modalfast .modal_dialog').css({
				width: widthValue + 'px',
		    	left: ($('.J_modal_job_money').width() - widthValue)/2,
		    	top: ($('.J_modal_job_money').height() - $('.modalfast .modal_dialog').outerHeight())/2
		    });

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
		    	if ($(this).closest('li').hasClass('seledted')) {
		    		$(this).closest('li').removeClass('seledted');
		    		if (multipleValue) {
		    			copyJobsSelected();
		    		};
		    		var thisGroup = $(this).closest('.J_list_jobs_group2');
		    		var subscriptValue = $('.J_list_jobs_group2').index(thisGroup);
		    		if (!thisGroup.find('.seledted').length) {
		    			$('.J_list_jobs_parent1').eq(subscriptValue).removeClass('seledted').addClass('current');
		    			var thisGroup2 = $('.J_list_jobs_parent1').eq(subscriptValue).closest('.J_list_jobs_group1');
			    		var subscriptValue1 = $('.J_list_jobs_group1').index(thisGroup2);
			    		if (!thisGroup2.find('.seledted').length) {
			    			$('.J_list_jobs_parent').eq(subscriptValue1).removeClass('seledted').addClass('current');
			    		};
		    		};

		    	} else {
		    		$(this).closest('li').addClass('seledted');
		    		if (multipleValue) {
		    			if (!$(this).is('.J_list_jobs_nolimit')) {
	    					var thisGroup = $(this).closest('.J_list_jobs_group2');
	    					thisGroup.find('.J_list_jobs_nolimit').prop('checked', 0);
	    					thisGroup.find('.J_list_jobs_nolimit').closest('li').removeClass('seledted');
	    				};
		    			if ($('.J_list_jobs:checked').length > maxNumValue) {
		    				$(this).closest('li').removeClass('seledted');
		    				$(this).prop('checked', 0);
		    				disapperTooltip("remind", '最多选择'+ maxNumValue +'个');
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
		    		} else {
		    			var code = $(this).data('code');
						var title = $(this).data('title');
						$('#J_showmodal_jobs_money .J_resultcode_jobs').val(code);
						$('#J_showmodal_jobs_money .J_resuletitle_jobs').text(title);
						$('#J_showmodal_jobs_money .J_resuletitle_jobs').attr('title', title);
						$('.modal_backdrop').remove();
		 				$('.modal').remove();
		    		}
		    	}
		    });

			function copyJobsSelected() {
		    	var htmlListed = '';
		    	$('.J_list_jobs:checked').each(function(index, el) {
		    		var listedCode = $(this).data('code');
		    		var listedTitle = $(this).data('title');
		    		htmlListed += [
						'<div class="listed_item_parent J_listed_jobs" data-code="' + listedCode + '" data-title="' + listedTitle + '">',
							'<a href="javascript:;" class="listed_item">',
								'<span>' + listedTitle + '</span><div class="del"></div>',
							'</a>',
						'</div>'
					].join('');
		    	});
		    	$('#J_listed_content').html(htmlListed);
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
				    		};
			    		};
					};
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
				$('#J_showmodal_jobs_money .J_resultcode_jobs').val(codeArray.join(','));
				;
				$('#J_showmodal_jobs_money .J_resuletitle_jobs').text(titleArray.length ? titleArray.join('+') : '请选择');
				$('#J_showmodal_jobs_money .J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
                removeModalMoney();
		    });
		}
	});

	function prepareModalMoney(titleValue, multipleValue, maxNumValue) {
		var ie = !-[1,];
		var ie6 = !-[1,]&&!window.XMLHttpRequest;
		$('.modalfast').remove();
		$(htmlLayerTplMoney).appendTo($('.J_modal_job_money'));
		
		$('.J_modal_title').text(titleValue);
		if (multipleValue) {
	    	$('.J_modal_max').text('（最多选择'+ maxNumValue +'个）');
	    };

		$(".J_hoverbut").hover(
			function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			}
		);

		if (ie) {
			if (window.PIE) { 
	            $('.pie_about').each(function() {
	                PIE.attach(this);
	            });
	        }
		};
	}

	$('.J_dismiss_modal_fast').live('click', function() {
        removeModalMoney();
    });

    $(document).on('keydown', function(event) {
 		if (event.keyCode == 27) {
            removeModalMoney();
		}
 	});

	function removeModalMoney() {
		setTimeout(function() {
 			$('.modalfast').remove();
		},50)
	}
	
}(window.jQuery);