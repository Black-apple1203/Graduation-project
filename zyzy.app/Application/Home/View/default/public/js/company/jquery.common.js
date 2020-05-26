// JavaScript Document
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
});
