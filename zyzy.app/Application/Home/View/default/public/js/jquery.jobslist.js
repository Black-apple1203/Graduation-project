/* ============================================================
 * jquery.jobslist.js  职位搜索列表页面js集合
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
			$('.J_allListBox').find('.J_jobsStatus').addClass('show');
		} else {
			$('.J_allListBox').find('.detail').show();
			$('.J_allListBox').find('.J_jobsStatus').removeClass('show');
		}
		$.getJSON(qscms.root + '?m=Home&c=AjaxCommon&a=list_show_type',{action:'jobs',type:type});
	});

	// 周边职位和热门职位切换
	$('.J_job_hotnear').click(function() {
		$(this).addClass('select').siblings('.J_job_hotnear').removeClass('select');
		var indexValue = $('.J_job_hotnear').index(this);
		$('.J_job_hotnear_show').removeClass('show');
		$('.J_job_hotnear_show').eq(indexValue).addClass('show');
	});

	// 列表详细展开收起
	$('.J_jobsStatus').click(function(){
		if($(this).hasClass('show')){
			$(this).removeClass('show');
			$(this).closest('.J_jobsList').find('.detail').show();
		}else{
			$(this).addClass('show');
			$(this).closest('.J_jobsList').find('.detail').hide();
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
			$('.J_jobsList').removeClass('select');
		} else {
			$(this).addClass('select');
			$.each(listArray, function(index, val) {
				$(this).addClass('select');
			});
			$('.J_jobsList').addClass('select');
		}

	});
	$('.J_allList').click(function(){
		var isChecked = $(this).hasClass('select');
		if (isChecked) {
			$(this).removeClass('select');
			$(this).closest('.J_jobsList').removeClass('select');
			$('.J_allSelected').removeClass('select');
		} else {
			$(this).addClass('select');
			$(this).closest('.J_jobsList').addClass('select');
			var listArray = $('.J_allListBox .J_allList');
			var listCheckedArray = $('.J_allListBox .J_allList.select');
			if (listArray.length == listCheckedArray.length) {
				$('.J_allSelected').addClass('select');
			}
		}
	});

	var qrcode_bind_time,
		waiting_weixin_bind = function(){
			$.getJSON(qscms.root+"?m=Home&c=Members&a=waiting_weixin_bind");
		};
    apply_job_allowance('.J_applyForJobAllowance',qscms.is_login);
	function apply_job_allowance(trigger,is_login){
		$(trigger).click(function(){
			var jid = $(this).data('jid');
			var qsDialog = $(this).dialog({
	    		loading: true,
				footer: false,
				header: false,
				border: false,
				backdrop: false
			});
			if(is_login!='0'){
	            var url = qscms.root+"?m=Allowance&c=Ajax&a=apply_allowance";
	            $.getJSON(url,{jid:jid},function(data){
	                if(data.status==1) {
	                    qsDialog.hide();
	                    var qsDialogSon = $(this).dialog({
	                        title: '领取红包',
	                        content: data.data.html,
	                        yes:function(){
	                        	if(data.data.tip_status==1){
									$.getJSON(qscms.root+'?m=Allowance&c=Ajax&a=apply_allowance_save',{jid:jid,need_check_bind:1},function(r){
										if(r.status==1){
											qsDialogSon.hide();
					                        if(r.data.type_alias=='apply'){
					                        	$('body').append('<div class="modal_backdrop"></div>');
					                        	$('.get-money-fail-suc').show();
					                        	$('.get-money-fail-suc .cash-line .cl-big').html(r.data.per_amount);
					                        }else{
					                        	$.getJSON(qscms.root+'?m=Allowance&c=Ajax&a=apply_allowance_okremind',{type:r.data.type_alias},function(re){
														if(re.status==1){
															var remindDialog = $(this).dialog({
																title: '系统提示',
	                        									content: re.data,
															});
														}
					                        	});
					                        }
										}
										else if(r.status==2){
											qsDialogSon.hide();
											var qsDialogBind = $('.J_applyForJobAllowance').dialog({
						                        title: '绑定微信',
						                        content: r.data,
						                        yes:function(){
						                        	clearInterval(qrcode_bind_time);
						                        	$.getJSON(qscms.root+'?m=Allowance&c=Ajax&a=apply_allowance_save',{jid:jid},function(t_r){
						                        		if(t_r.status==1){
						                        			qsDialogBind.hide();
									                        if(t_r.data.type_alias=='apply'){
									                        	$('body').append('<div class="modal_backdrop"></div>');
									                        	$('.get-money-fail-suc').show();
									                        	$('.get-money-fail-suc .cash-line .cl-big').html(t_r.data.per_amount);
									                        }else{
									                        	$.getJSON(qscms.root+'?m=Allowance&c=Ajax&a=apply_allowance_okremind',{type:t_r.data.type_alias},function(t_re){
																		if(t_re.status==1){
																			var remindDialog = $(this).dialog({
																				title: '系统提示',
					                        									content: t_re.data,
																			});
																		}
									                        	});
									                        }
						                        		}else{
						                        			disapperTooltip('remind',t_r.msg);
															return false;
						                        		}
						                        	});
						                        },
						                        cancel:function(){
						                        	clearInterval(qrcode_bind_time);
						                        }
						                    });
	                						qsDialogBind.setCloseDialog(false);
						                    qsDialogBind.setBtns(['我已绑定', '取消']);
						                    clearInterval(qrcode_bind_time);
						                    qrcode_bind_time=setInterval(waiting_weixin_bind,5000);
										}
										else if(r.status==3){
											qsDialogSon.hide();
											var authMobileDialog = $(this).dialog({
								        		title: "验证手机号",
								        		content:r.data,
												loading: false,
												showFooter: true,
												yes: function() {
													var verifycode  = $.trim($('#J_mobileWrap input[name="verifycode"]').val());
													if(!verifycode){
														disapperTooltip("remind", "请填写验证码！");
														return !1;
													}
													$.post(qscms.root+"?m=Home&c=Members&a=verify_mobile_code",{verifycode:verifycode},function(result){
														if(result.status == 1){
															disapperTooltip('success','手机号验证成功，请重新领取红包');
															authMobileDialog.hide();
														}else{
															disapperTooltip('remind',result.msg);
														}
													},'json');
												}
											});
											authMobileDialog.setCloseDialog(false);
										}
										else{
											disapperTooltip('remind',r.msg);
											return false;
										}
									});
	                        	}else{
	                        		disapperTooltip('remind','你的简历不满足条件，无法领取红包，你可以直接投递');
	                        		return false;
	                        	}
	                        },
	                        cancel:function(){
	                        	var url = qscms.root+"?m=Home&c=AjaxPersonal&a=resume_apply";
			                    $.getJSON(url,{jid:jid},function(result){
				                    if(result.status==1) {
				                        if(result.data.html){
				                            var qsDialogSon = $(this).dialog({
				                                title: '申请职位',
				                                content: result.data.html
				                            });
				                        }
				                        else {
				                            qsDialog.hide();
				                            disapperTooltip("remind", result.msg);
				                        }
				                    }
				                    else if(result.data==1){
				                        qsDialog.hide();
				                        disapperTooltip('remind',result.msg);
				                        setTimeout(function() {
				                            location.href=qscms.root+"?m=Home&c=Personal&a=resume_add";
				                        },1000);
				                    }
				                    else
				                    {
				                        if (eval(result.dialog)) {
				                            var creatsUrl = qscms.root+"?m=Home&c=AjaxPersonal&a=resume_add_dig";
				                            $.getJSON(creatsUrl,{jid:jid}, function(result){
				                                if(result.status==1){
				                                    qsDialog.hide();
				                                    var qsDialogSon = $(this).dialog({
				                                        content: result.data.html,
				                                        footer: false,
				                                        header: false,
				                                        border: false
				                                    });
				                                    qsDialogSon.setInnerPadding(false);
				                                } else {
				                                    qsDialog.hide();
				                                    disapperTooltip('remind',result.msg);
				                                }
				                            });
				                        } else {
				                            qsDialog.hide();
				                            disapperTooltip('remind',result.msg);
				                        }
				                    }
				                });
	                        }
	                    });
	                	qsDialogSon.setBtnClass(['w130', 'w130']);
	                    qsDialogSon.setCloseDialog(false);
	                    qsDialogSon.setBtns(['领取红包并投递', '不领红包直接投递']);
	                }
	                else
	                {
	                    qsDialog.hide();
	                    disapperTooltip('remind',data.msg);
	                    if(data.status==2){
                        	setTimeout(function() {
	                            location.href=qscms.root+"?m=Home&c=Personal&a=resume_add";
	                        },2000);
                        }
	                }
	            });
	        }else{
				var loginUrl = qscms.root+"?m=Home&c=AjaxCommon&a=ajax_login";
	            $.getJSON(loginUrl, function(result){
	                if(result.status==1){
	                    qsDialog.hide();
	                    var qsDialogSon = $(this).dialog({
	                        header: false,
	                        content: result.data.html,
	                        footer: false,
	                        border: false
	                    });
	                    qsDialogSon.setInnerPadding(false);
	                } else {
	                    qsDialog.hide();
	                    disapperTooltip('remind',result.msg);
	                }
	            });
	        }
		});
	}

	// 申请、收藏职位
	jobSomething('.J_applyForJob', '申请成功！', true);
	jobSomething('.J_collectForJobBatch', '收藏成功！', false);

	function jobSomething (trigger, successMsg, iscreate) {
		$(trigger).click(function() {
			var batch = eval($(this).data('batch'));
			var url = $(this).data('url');
			var hasAllowance = false;
			var jidValue = '';
			if (batch) { // 是否是批量
				if (listCheckEmpty()) {
					disapperTooltip('remind','您还没有选择职位！');
					return false;
				} else {
					var listCheckedObjs = $('.J_allListBox .J_allList.select');
					var jidArray = new Array();
					$.each(listCheckedObjs, function(index, val) {
						jidArray[index] = $(this).closest('.J_jobsList').data('jid');
						if ($(this).closest('.J_jobsList').find('.i-m').length) {
							hasAllowance = true;
						}
					});
					jidValue = jidArray.join(',');
				}
			} else {
				jidValue = $(this).closest('.J_jobsList').data('jid');
			}
			// 是否含有红包职位
			if (hasAllowance) {
				var conAloDia = $(this).dialog({
					title: "提示",
					content: "当前所选职位中包含红包职位，无法批量投递。<br />你可以点击“取消”逐个申请职位，也可以点击“直接投递”但不领取红包。",
					yes: function() {
						ajaxFroJob();
					}
				})
				conAloDia.setBtns(['直接投递','取消']);
			} else {
				ajaxFroJob();
			}
			function ajaxFroJob() {
				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: {jid: jidValue}
				})
				.done(function(data) {
					if (parseInt(data.status)) {
						if(data.data.html){
							if(data.data.rid){
								var qsDialogSon = $(this).dialog({
									title: '申请职位',
									content: data.data.html,
									yes: function () {
										var url = qscms.root+"?m=Home&c=Personal&a=index";
										document.location.href = url;
									},
									btns: ['完善简历', '放弃申请']
								});
							}else{
								var qsDialogSon = $(this).dialog({
									title: '申请职位',
									content: data.data.html,
									footer: false
								});
								if ((isVisitor > 0)) {
									setTimeout(function(){
										window.location.reload();
									},2000);
								}
							}
						} else {
							disapperTooltip('success', successMsg);
							setTimeout(function(){
								window.location.reload();
							},2000);
						}
					}
					else if(data.data==1){
						location.href=qscms.root+"?m=Home&c=Personal&a=resume_add";
					}
					else {
						if (eval(data.dialog)) {
							var qsDialog = $(this).dialog({
				        		loading: true,
								footer: false,
								header: false,
								border: false,
								backdrop: false
							});
							if (iscreate) { // 申请职位
	                            if (eval(qscms.smsTatus)) {// 是否开启短信
	                                var creatsUrl = qscms.root + '?m=Home&c=AjaxPersonal&a=resume_add_dig';
	                                $.getJSON(creatsUrl,{jid:jidValue}, function(result){
	                                    if(result.status==1){
	                                        qsDialog.hide();
	                                        var qsDialogSon = $(this).dialog({
	                                            content: result.data.html,
	                                            footer: false,
	                                            header: false,
	                                            border: false
	                                        });
	                                        qsDialogSon.setInnerPadding(false);
	                                    } else {
	                                        qsDialog.hide();
	                                        disapperTooltip("remind", result.msg);
	                                    }
	                                });
	                            } else {
	                                var loginUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_login';
	                                $.getJSON(loginUrl, function(result){
	                                    if(result.status==1){
	                                        qsDialog.hide();
	                                        var qsDialogSon = $(this).dialog({
	                                            header: false,
	                                            content: result.data.html,
	                                            footer: false,
	                                            border: false,
	                                            yes: function () {
													window.location.reload();
												}
	                                        });
	                                        qsDialogSon.setInnerPadding(false);
	                                    } else {
	                                        qsDialog.hide();
	                                        disapperTooltip("remind", result.msg);
	                                    }
	                                });
	                            }
							} else {
								var loginUrl = qscms.root + '?m=Home&c=AjaxCommon&a=ajax_login';
								$.getJSON(loginUrl, function(result){
						            if(result.status==1){
										qsDialog.hide();
										var qsDialogSon = $(this).dialog({
											header: false,
											content: result.data.html,
											footer: false,
											border: false
										});
						    			qsDialogSon.setInnerPadding(false);
						            } else {
						            	qsDialog.hide();
						                disapperTooltip("remind", result.msg);
						            }
						        });
							}
						} else {
							disapperTooltip("remind", data.msg);
						}
					}
				})
			}
		});
	}

	// 收藏职位
	$(".J_collectForJob").die().live('click',function(){
		var url = $(this).data('url');
		var jid = $(this).closest('.J_jobsList').data('jid');
		var $this = $(this);
		if ((isVisitor > 0)) {
			$.getJSON(url,{jid:jid},function(result){
				if(result.status==1){
					$this.addClass('has-favor').html('已收藏');
					disapperTooltip('success',result.msg);
				} else {
					disapperTooltip('remind',result.msg);
				}
			});
		} else {
			var qsDialog = $(this).dialog({
        		loading: true,
				footer: false,
				header: false,
				border: false,
				backdrop: false
			});
			// var loginUrl = "{:U('AjaxCommon/ajax_login')}";
			$.getJSON(ajaxLoginDiaUrl, function(result){
	            if(result.status==1){
					qsDialog.hide();
					var qsDialogSon = $(this).dialog({
						header: false,
						content: result.data.html,
						footer: false,
						border: false
					});
	    			qsDialogSon.setInnerPadding(false);
	            } else {
	                qsDialog.hide();
					disapperTooltip('remind',result.msg);
	            }
	        });
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