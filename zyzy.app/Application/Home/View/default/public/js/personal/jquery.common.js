$(document).ready(function()
{
	//给所有J_hoverbut的元素增加hover样式
	$(".J_hoverbut").hover(
		function() {
			$(this).addClass("hover");
		},
		function() {
			$(this).removeClass("hover");
		}
	);

	//个人会员中心小标题切换卡
	$(".thtab .li").click(function() {
		$(this).addClass("select").siblings(".li").removeClass("select");
		var index = $(".thtab .li").index(this);
		$('.tabshow').eq(index).show().siblings(".tabshow").hide();
	});
	
	//新增简历页面 点击重命名简历标题，切换出文本框
	$("#J_edit_title").click(function() {
		$("#J_edit_title_input").show();
		$("#J_edit_title_txt").hide();
	});
	$("#J_edit_title").click(function() {
		$("#J_edit_title_input").show();
		var oval = $("#J_edit_title_input").find('input[name="title"]').val();
		$("#J_edit_title_input").find('input[name="title"]').val('').focus().val(oval).addClass('input_focus');
		$("#J_edit_title_txt").hide();
	});
	$('input[name="title"]').blur(function() {
		$("#J_edit_title_input").hide();
		$("#J_edit_title_txt").show();
	});
	$('input[name="title"]').keyup(function() {
		$("#J_edit_title_txt span").html($(this).val());
	});
	
	//给符合条件的的文本框增加获取焦点的边框和背景变化	
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus']").focus(function() {
		$(this).addClass("input_focus")
	});
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus']").blur(function() {
		$(this).removeClass("input_focus")
	});

	//给符合条件的的DIV文本框增加获取焦点的边框和背景变化
	$(".J_hoverinput").hover(
		function() {
			$(this).addClass("input_focus");
		},
		function() {
			$(this).removeClass("input_focus");
		}
	);

	//新增简历页面打开关闭更多选项
	$("#J_addmore").click(function() {
		$("#J_addmore_show").toggle();
		if ($("#J_addmore_show").is(':hidden')) {
			$(this).removeClass('show');
		} else {
			$(this).addClass('show');
		}
	});

	//编辑简历页滑过项目显示编辑和删除
	$(".J_course_edit").hover(
		function() {
			$(this).find(".editbox").show();
		},
		function() {
			$(this).find(".editbox").hide();
		}
	);
	
	//编辑简历页点击添加和修改显示对应表单
	$(".J_show_itemsform").click(function() {
		$(this).parent().parent().hide();
		$(this).parent().parent().next().show();
	});
	$(".J_hide_itemsform").click(function() {
		$(this).parent().parent().parent().parent().hide();
		$(this).parent().parent().parent().parent().prev().show();
	});
	$(".J_close_itemsform").click(function() {
		$(this).parent().parent().hide();
		$(this).parent().parent().prev().show();
	});
});
