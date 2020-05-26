// JavaScript Document
$(document).ready(function()
{
	//给所有J_hoverbut的元素增加hover样式
	$(".J_hoverbut").hover(
		function()
		{
		$(this).addClass("hover");
		},
		function()
		{
		$(this).removeClass("hover");
		}
		);

	//首页BANER右侧选项卡切换
	$(".noticestab .tli").click( function () {
		$(this).addClass("select").siblings(".tli").removeClass("select");
		var index = $(".noticestab .tli").index(this);
		$('.notice_showtabs').eq(index).show().siblings(".notice_showtabs").hide();
	});
	//首页底部资讯选项卡切换
	$(".newstab .newstli").click( function () {
		$(this).addClass("select").siblings(".newstli").removeClass("select");
		var index = $(".newstab .newstli").index(this);
		$('.news_showtabs').eq(index).show().siblings(".news_showtabs").hide();
	});
	//首页登录二维码和文本登录切换
	$(".code_login,.txt_login").click( function () {
		$(".j_mob_show").toggle();
		$(".J_qr_code_show").toggle();
		$(".code_login").toggle();
		$(".txt_login").toggle();
	});
//给符合条件的的文本框增加获取焦点的边框和背景变化	
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus'],.J_focus input[type='password']").focus(function(){
	$(this).addClass("input_focus")											
	});
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus'],.J_focus input[type='password']").blur(function(){
	$(this).removeClass("input_focus")	
	});

});
