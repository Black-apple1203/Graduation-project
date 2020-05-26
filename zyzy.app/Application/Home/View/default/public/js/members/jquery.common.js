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

	
		//会员登录手机登录和二维码的路切换
	$(".J_mob,.J_qr_code").click( function () {
		$(".j_mob_show").toggle();
		$(".J_qr_code_show").toggle();
	});
	//个人注册方式选项卡切换
	$(".regtab .tabli").click( function () {
		$(this).addClass("select").siblings(".tabli").removeClass("select");
		var index = $(".regtab .tabli").index(this);
		$('.tabshow').eq(index).show().siblings(".tabshow").hide();
		$('.tabshow').eq(index).find('input').eq(0).focus().addClass('input_focus');
	});
	//
	

//给符合条件的的文本框增加获取焦点的边框和背景变化	
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus'],.J_focus input[type='password']").focus(function(){
	$(this).addClass("input_focus")											
	});
	$(".J_focus input[type='text'][dir!='no_focus'],.J_focus textarea[dir!='no_focus'],.J_focus input[type='password']").blur(function(){
	$(this).removeClass("input_focus")	
	});