/* ============================================================
 * jquery.modal.userselectlayer.js  职位、地区、行业、专业
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
!function($) {

	var backdropLayerTpl = '<div class="modal_backdrop fade"></div>';
	var htmlLayerTpl = [
			'<div class="modal">',
	            '<div class="modal_dialog">',
	                '<div class="modal_content pie_about">',
	                    '<div class="modal_header">',
							'<span class="title J_modal_title"></span>',
	                        '<span class="max_remind J_modal_max"></span>',
	                        '<a href="javascript:;" class="close J_dismiss_modal"></a>',
						'</div>',
	                    '<div class="modal_body">',
	                    	'<div class="listed_group" id="J_listed_group">',
	                    		'<div class="left_text">已选择：</div>',
	                    		'<div class="center_text" id="J_listed_content"></div>',
	                    		'<a href="javascript:;" class="right_text" id="J_listed_clear">清空</a>',
	                    		'<div class="clear"></div>',
	                    	'</div>',
	                    	'<div class="J_modal_content"></div>',
	                    '</div>',
	                    '<div class="modal_footer">',
	                        '<div class="res_add_but">',
	                        	'<div class="butlist">',
	                            	'<div class="btn_blue J_hoverbut btn_100_38 J_btnyes">确 定</div>',
	                            '</div>',
	                            '<div class="butlist">',
	                            	'<div class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btncancel">取 消</div>',
	                            '</div>',
	                            '<div class="clear"></div>',
	                        '</div>',
	                    '</div>',
	                    '<input type="hidden" class="J_btnload" />',
	                '</div>',
	            '</div>',
	        '</div>'
		].join('');

  // 点击显示行业分类
  $('.J_resuletitle_trade').live('click', function() {
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
    var recoverValue = $('.J_resultcode_trade').val();
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
        $('.J_resultcode_trade').val(code);
        $('.J_resuletitle_trade').val(title);
        $('.J_resuletitle_trade').attr('title', title);
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
      $('.J_resultcode_trade').val(codeArray.join(','));
      ;$('.J_resuletitle_trade').val(titleArray.length ? titleArray.join('+') : '不限');
      $('.J_resuletitle_trade').attr('title', titleArray.length ? titleArray.join('+') : '不限');
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
					htmlMajor += [
						'<li class="J_list_major_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">',
							'<label>' + iArray[1] + '</label>',
						'</li>'
					].join('');
					major1Array[i] = iArray[0];
				};
			};
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
									htmlMajor += [
										'<li class="J_list_major" data-code="' + jArray[0] + '" data-title="' + jArray[1] + '">',
											'<label>' + jArray[1] + '</label>',
										'</li>'
									].join('');
								};
							};
							htmlMajor += '</ul>';
						};
					};
				};
			};
			htmlMajor += '</div>';
			htmlMajor += '<div class="clear"></div>';
			htmlMajor += '</div>';
		};

		prepareModal(titleValue, multipleValue, maxNumValue);

		$('.J_modal_content').html(htmlMajor);
	    $('.J_btnyes').attr('id', 'J_btnyes_major');
	    $('.J_modal_content .right_box .list_nav').eq(0).show();
	    $('.J_list_major_parent').eq(0).addClass('current');

		$('.modal_dialog').css({
			width: widthValue + 'px',
	    	left: ($(window).width() - widthValue)/2,
	    	top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
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
						};
					});
				};
				copyMajorSelected();
		    } else {
	    		$('.J_list_major').each(function(index, el) {
					if ($(this).data('code') == recoverValue) {
						$(this).addClass('seledted');
					};
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
	    		};
	    		var thisGroup = $(this).closest('.J_list_major_group');
	    		var subscriptValue = $('.J_list_major_group').index(thisGroup);
	    		if (!thisGroup.find('.seledted').length) {
	    			$('.J_list_major_parent').eq(subscriptValue).removeClass('seledted').addClass('current');
	    		};
	    	} else {
	    		$(this).addClass('seledted');
	    		if (multipleValue) {
	    			if ($('.J_list_major.seledted').length > maxNumValue) {
	    				$(this).removeClass('seledted');
	    				alert('最多选择'+ maxNumValue +'个');
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
	    		htmlListed += [
					'<div class="listed_item_parent J_listed_major" data-code="' + listedCode + '" data-title="' + listedTitle + '">',
						'<a href="javascript:;" class="listed_item">',
							'<span>' + listedTitle + '</span><div class="del"></div>',
						'</a>',
					'</div>'
				].join('');
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
		    		};
				};
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
			;
			$('#J_showmodal_major .J_resuletitle_major').text(titleArray.length ? titleArray.join('+') : '请选择');
			$('#J_showmodal_major .J_resuletitle_major').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
			removeModal();
	    });
	});

	// 点击显示职位分类
	$('#category_cn').live('click', function() {
		var titleValue = $(this).data('title');
		var multipleValue = eval($(this).data('multiple'));
		var maxNumValue = eval($(this).data('maxnum'));
		var widthValue = eval($(this).data('width'));
		var htmlJobs = '';
		var categoryValue = eval($(this).data('category'));
    	var addJob = eval($(this).data('addjob')); // 发职位标识
		var classifyModel = 0; // 标记二级还是三级分类
		categoryValue > 2 ? classifyModel = 0 : classifyModel = 1;

		if (classifyModel) { // 二级分类
			if (QS_jobs_parent) {
				htmlJobs += '<div class="modal_body_box modal_body_box_jl2">';
				htmlJobs += '<div class="left_box">';
				htmlJobs += '<ul class="list_nav">';
				for (var i = 0; i < QS_jobs_parent.length; i++) {
					if (QS_jobs_parent[i].split(',')) {
						var iArray = QS_jobs_parent[i].split(',');
						htmlJobs += [
							'<li class="J_list_jobs_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">',
								'<label title="' + iArray[1] + '">' + iArray[1] + '</label>',
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
									if (!addJob) {
										htmlJobs += [
										'<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
											'<label>不限</label>',
										'</li>'
										].join('');
									}
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
								htmlJobs += '<ul class="list_nav J_list_jobs_group">';
								if (addJob) {
									htmlJobs += [
										'<li class="J_list_jobs_nolimit">',
											'<label>此分类下无子分类!</label>',
										'</li>'
									].join('');
								} else {
									htmlJobs += [
										'<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
											'<label>不限</label>',
										'</li>'
									].join('');
								}
								htmlJobs += '</ul>';
							}
						}
					}
				};
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
		    	left: ($(window).width() - widthValue)/2,
		    	top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
		    });

		    $('.modal_backdrop').addClass('in');

            $('.J_list_jobs_parent').live('click', function() {
                $(this).addClass('current').siblings('.J_list_jobs_parent').removeClass('current');
                var subscriptValue = $('.J_list_jobs_parent').index(this);
                $('.J_list_jobs_group').eq(subscriptValue).show().siblings('.J_list_jobs_group').hide();
            });

		    // 恢复选中
	    	var recoverValue = $('.J_resultcode_jobs').val();
	    	if (recoverValue.length) {
			    if (multipleValue) {
			    	var recoverValueArray = recoverValue.split(',');
					for (var i = 0; i < recoverValueArray.length; i++) {
						$('.J_list_jobs').each(function(index, el) {
							if ($(this).data('code') == recoverValueArray[i]) {
								$(this).addClass('seledted');
							};
						});
					};
					copyJobsSelectedSecond();
			    } else {
		    		$('.J_list_jobs').each(function(index, el) {
						if ($(this).data('code') == recoverValue) {
							$(this).addClass('seledted');
						};
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
		    				alert('最多选择'+ maxNumValue +'个');
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
						$('.J_resultcode_jobs').val(code);
						$('.J_resuletitle_jobs').val(title);
						$('.J_resuletitle_jobs').attr('title', title);
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
				$('.J_resultcode_jobs').val(codeArray.join(','));
				;
				$('.J_resuletitle_jobs').val(titleArray.length ? titleArray.join('+') : '请选择');
				$('.J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
				removeModal();
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
								'<label title="' + iArray[1] + '">' + iArray[1] + '</label>',
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
					if (multipleValue) {
						for (var i = 0; i < job2Array.length; i++) {
							if (job2Array[i].split('.')) {
								var combinationArray = job2Array[i].split('.')
								if (QS_jobs[combinationArray[1]]) {
									if (QS_jobs[combinationArray[1]].split('`')) {
										var job22Array = QS_jobs[combinationArray[1]].split('`');
										htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
										if (!addJob) {
											htmlJobs += [
											'<li>',
												'<label>',
													'<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ',
												'不限</label>',
											'</li>'
											].join('');
										}
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
									if (addJob) {
										htmlJobs += [
											'<li>',
												'<label>',
												'此分类下无子分类!</label>',
											'</li>'
										].join('');
									} else {
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
									}
									htmlJobs += '</ul>';
								}
							};
						};
					} else {
						for (var i = 0; i < job2Array.length; i++) {
							if (job2Array[i].split('.')) {
								var combinationArray = job2Array[i].split('.')
								if (QS_jobs[combinationArray[1]]) {
									if (QS_jobs[combinationArray[1]].split('`')) {
										var job22Array = QS_jobs[combinationArray[1]].split('`');
										htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
										if (!addJob) {
											htmlJobs += [
											'<li>',
												'<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">',
												'不限</label>',
											'</li>'
											].join('');
										}
										for (var j = 0; j < job22Array.length; j++) {
											if (job22Array[j].split(',')) {
												var jArray = job22Array[j].split(',');
												htmlJobs += [
													'<li>',
														'<label class="J_list_jobs" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '">',
														'' + jArray[1] + '</label>',
													'</li>'
												].join('');
											};
										};
										htmlJobs += '</ul>';
									};
								} else {
									htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
									if (addJob) {
										htmlJobs += [
											'<li>',
												'<label>',
												'此分类下无子分类!</label>',
											'</li>'
										].join('');
									} else {
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
													'<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">',
													'不限</label>',
												'</li>'
											].join('');
										}
									}
									htmlJobs += '</ul>';
								}
							};
						};
					}
				};
				htmlJobs += '</div>';
				htmlJobs += '<div class="clear"></div>';
				htmlJobs += '</div>';
			};

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
		    	left: ($(window).width() - widthValue)/2,
		    	top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
		    });

		    $('.modal_backdrop').addClass('in');

		    // 恢复选中
	    	var recoverValue = $('.J_resultcode_jobs').val();
	    	if (recoverValue.length) {
			    if (multipleValue) {
			    	var recoverValueArray = recoverValue.split(',');
					for (var i = 0; i < recoverValueArray.length; i++) {
						$('.J_list_jobs').each(function(index, el) {
							if ($(this).data('code') == recoverValueArray[i]) {
								$(this).closest('li').addClass('seledted');
								$(this).prop('checked', !0);
							};
						});
					};
					copyJobsSelected();
			    } else {
		    		$('.J_list_jobs').each(function(index, el) {
						if ($(this).data('code') == recoverValue) {
							$(this).closest('li').addClass('seledted');
							$(this).prop('checked', !0);
						};
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
				    		};
			    		};
		    		} else {
		    			$(this).closest('li').addClass('seledted');
		    			if (!$(this).is('.J_list_jobs_nolimit')) {
	    					var thisGroup = $(this).closest('.J_list_jobs_group2');
	    					thisGroup.find('.J_list_jobs_nolimit').prop('checked', 0);
	    					thisGroup.find('.J_list_jobs_nolimit').closest('li').removeClass('seledted');
	    				};
		    			if ($('.J_list_jobs:checked').length > maxNumValue) {
		    				$(this).closest('li').removeClass('seledted');
		    				$(this).prop('checked', 0);
		    				alert('最多选择'+ maxNumValue +'个');
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
					$('.J_resultcode_jobs').val(code);
					$('.J_resuletitle_jobs').val(title);
					$('.J_resuletitle_jobs').attr('title', title);
					$('.modal_backdrop').remove();
	 				$('.modal').remove();
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
				$('.J_resultcode_jobs').val(codeArray.join(','));
				;
				$('.J_resuletitle_jobs').val(titleArray.length ? titleArray.join('+') : '请选择');
				$('.J_resuletitle_jobs').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
				removeModal();
		    });
		}
	});

	// 点击显示职位分类_t
	$('#category_cn_t').live('click', function() {
		var titleValue = $(this).data('title');
		var multipleValue = eval($(this).data('multiple'));
		var maxNumValue = eval($(this).data('maxnum'));
		var widthValue = eval($(this).data('width'));
		var htmlJobs = '';
		var categoryValue = eval($(this).data('category'));
    	var addJob = eval($(this).data('addjob')); // 发职位标识
		var classifyModel = 0; // 标记二级还是三级分类
		categoryValue > 2 ? classifyModel = 0 : classifyModel = 1;

		if (classifyModel) { // 二级分类
			if (QS_jobs_parent) {
				htmlJobs += '<div class="modal_body_box modal_body_box_jl2">';
				htmlJobs += '<div class="left_box">';
				htmlJobs += '<ul class="list_nav">';
				for (var i = 0; i < QS_jobs_parent.length; i++) {
					if (QS_jobs_parent[i].split(',')) {
						var iArray = QS_jobs_parent[i].split(',');
						htmlJobs += [
							'<li class="J_list_jobs_parent" data-code="' + iArray[0] + '" data-title="' + iArray[1] + '">',
								'<label title="' + iArray[1] + '">' + iArray[1] + '</label>',
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
									if (!addJob) {
										htmlJobs += [
											'<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
												'<label>不限</label>',
											'</li>'
										].join('');
									}
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
								htmlJobs += '<ul class="list_nav J_list_jobs_group">';
								if (addJob) {
									htmlJobs += [
										'<li class="J_list_jobs_nolimit">',
											'<label>此分类下无子分类!</label>',
										'</li>'
									].join('');
								} else {
									htmlJobs += [
										'<li class="J_list_jobs J_list_jobs_nolimit" data-code="' + jobs1Array[0] + '.0.0" data-title="' + jobs1Array[1] + '">',
											'<label>不限</label>',
										'</li>'
									].join('');
								}

								htmlJobs += '</ul>';
							}
						}
					}
				};
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
		    	left: ($(window).width() - widthValue)/2,
		    	top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
		    });

		    $('.modal_backdrop').addClass('in');

		    // 恢复选中
	    	var recoverValue = $('.J_resultcode_jobs_t').val();
	    	if (recoverValue.length) {
			    if (multipleValue) {
			    	var recoverValueArray = recoverValue.split(',');
					for (var i = 0; i < recoverValueArray.length; i++) {
						$('.J_list_jobs').each(function(index, el) {
							if ($(this).data('code') == recoverValueArray[i]) {
								$(this).addClass('seledted');
							};
						});
					};
					copyJobsSelectedSecond();
			    } else {
		    		$('.J_list_jobs').each(function(index, el) {
						if ($(this).data('code') == recoverValue) {
							$(this).addClass('seledted');
						};
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
		    				alert('最多选择'+ maxNumValue +'个');
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
						$('.J_resultcode_jobs_t').val(code);
						$('.J_resuletitle_jobs_t').val(title);
						$('.J_resuletitle_jobs_t').attr('title', title);
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
				$('.J_resultcode_jobs_t').val(codeArray.join(','));
				;
				$('.J_resuletitle_jobs_t').val(titleArray.length ? titleArray.join('+') : '请选择');
				$('.J_resuletitle_jobs_t').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
				removeModal();
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
								'<label title="' + iArray[1] + '">' + iArray[1] + '</label>',
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
					if (multipleValue) {
						for (var i = 0; i < job2Array.length; i++) {
							if (job2Array[i].split('.')) {
								var combinationArray = job2Array[i].split('.')
								if (QS_jobs[combinationArray[1]]) {
									if (QS_jobs[combinationArray[1]].split('`')) {
										var job22Array = QS_jobs[combinationArray[1]].split('`');
										htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
										if (!addJob) {
											htmlJobs += [
												'<li>',
													'<label>',
														'<input class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '"> ',
													'不限</label>',
												'</li>'
											].join('');
										}
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
									if (addJob) {
										htmlJobs += [
											'<li>',
												'<label>',
												'此分类下无子分类!</label>',
											'</li>'
										].join('');
									} else {
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
									}

									htmlJobs += '</ul>';
								}
							};
						};
					} else {
						for (var i = 0; i < job2Array.length; i++) {
							if (job2Array[i].split('.')) {
								var combinationArray = job2Array[i].split('.')
								if (QS_jobs[combinationArray[1]]) {
									if (QS_jobs[combinationArray[1]].split('`')) {
										var job22Array = QS_jobs[combinationArray[1]].split('`');
										htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
										if (!addJob) {
											htmlJobs += [
												'<li>',
													'<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">',
													'不限</label>',
												'</li>'
											].join('');
										}
										for (var j = 0; j < job22Array.length; j++) {
											if (job22Array[j].split(',')) {
												var jArray = job22Array[j].split(',');
												htmlJobs += [
													'<li>',
														'<label class="J_list_jobs" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.' + jArray[0] + '" data-title="' + jArray[1] + '">',
														'' + jArray[1] + '</label>',
													'</li>'
												].join('');
											};
										};
										htmlJobs += '</ul>';
									};
								} else {
									htmlJobs += '<ul class="list_nav J_list_jobs_group2">';
									if (addJob) {
										htmlJobs += [
											'<li>',
												'<label>',
												'此分类下无子分类!</label>',
											'</li>'
										].join('');
									} else {
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
													'<label class="J_list_jobs J_list_jobs_nolimit" type="checkbox" data-code="' + combinationArray[0] + '.' + combinationArray[1] + '.0" data-title="' + combinationArray[2] + '">',
													'不限</label>',
												'</li>'
											].join('');
										}
									}

									htmlJobs += '</ul>';
								}
							};
						};
					}

				};
				htmlJobs += '</div>';
				htmlJobs += '<div class="clear"></div>';
				htmlJobs += '</div>';
			};

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
		    	left: ($(window).width() - widthValue)/2,
		    	top: ($(window).height() - $('.modal_dialog').outerHeight())/2 + $(document).scrollTop()
		    });

		    $('.modal_backdrop').addClass('in');

		    // 恢复选中
	    	var recoverValue = $('.J_resultcode_jobs_t').val();
	    	if (recoverValue.length) {
			    if (multipleValue) {
			    	var recoverValueArray = recoverValue.split(',');
					for (var i = 0; i < recoverValueArray.length; i++) {
						$('.J_list_jobs').each(function(index, el) {
							if ($(this).data('code') == recoverValueArray[i]) {
								$(this).closest('li').addClass('seledted');
								$(this).prop('checked', !0);
							};
						});
					};
					copyJobsSelected();
			    } else {
		    		$('.J_list_jobs').each(function(index, el) {
						if ($(this).data('code') == recoverValue) {
							$(this).closest('li').addClass('seledted');
							$(this).prop('checked', !0);
						};
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
				    		};
			    		};
		    		} else {
		    			$(this).closest('li').addClass('seledted');
		    			if (!$(this).is('.J_list_jobs_nolimit')) {
	    					var thisGroup = $(this).closest('.J_list_jobs_group2');
	    					thisGroup.find('.J_list_jobs_nolimit').prop('checked', 0);
	    					thisGroup.find('.J_list_jobs_nolimit').closest('li').removeClass('seledted');
	    				};
		    			if ($('.J_list_jobs:checked').length > maxNumValue) {
		    				$(this).closest('li').removeClass('seledted');
		    				$(this).prop('checked', 0);
		    				alert('最多选择'+ maxNumValue +'个');
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
					$('.J_resultcode_jobs_t').val(code);
					$('.J_resuletitle_jobs_t').val(title);
					$('.J_resuletitle_jobs_t').attr('title', title);
					$('.modal_backdrop').remove();
	 				$('.modal').remove();
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
				$('.J_resultcode_jobs_t').val(codeArray.join(','));
				;
				$('.J_resuletitle_jobs_t').val(titleArray.length ? titleArray.join('+') : '请选择');
				$('.J_resuletitle_jobs_t').attr('title', titleArray.length ? titleArray.join('+') : '请选择');
				removeModal();
		    });
		}
	});

	// 点击显示职位亮点
	$('#tag_cn').live('click', function() {
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

	function prepareModal(titleValue, multipleValue, maxNumValue) {
		var ie = !-[1,];
		var ie6 = !-[1,]&&!window.XMLHttpRequest;
		$(backdropLayerTpl).appendTo($(document.body));
		if (ie6) {
			$('.modal_backdrop').css("height", $(document).height());
		}
		$(htmlLayerTpl).appendTo($(document.body));

		$('.J_modal_title').text(titleValue);
		if (multipleValue) {
	    	$('.J_modal_max').text('（最多选择'+ maxNumValue +'个）');
	    };
	    if (!multipleValue) {
	    	$('.modal_footer').hide();
	    };

		$(".J_hoverbut").hover(
			function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			}
		);

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
		};
	}

	$('.J_dismiss_modal').live('click', function() {
        removeModal();
    });

    $(document).on('keydown', function(event) {
 		if (event.keyCode == 27) {
			removeModal();
		}
 	});

	function removeModal() {
		setTimeout(function() {
	    	$('.modal_backdrop').remove();
 			$('.modal').remove();
		},50)
	}

}(window.jQuery);