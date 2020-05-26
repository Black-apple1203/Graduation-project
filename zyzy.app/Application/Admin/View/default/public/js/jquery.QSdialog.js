/* 
* QSdialog 1.0
* http://www.74cms.com/
* Date: 2011-5-15 
* Requires jQuery
*/ 
(function($) {   
$.fn.QSdialog=function(options){
	var defaults = {
    DialogAddObj:"body",
	DialogClosed:"关闭",
	DialogTitle:"系统提示",
	DialogWidth:"auto",
	DialogHeight:"auto",
	DialogCssName:"",
	DialogContent:"",
	DialogContentType:"text"
   }
    var options = $.extend(defaults,options);
	var AddObj=options.DialogAddObj;
	
function DialogClose()
{
	$(AddObj+" .FloatBg").remove();
	$(AddObj+" .FloatBox").remove();
}
function setPositionQS() {
	$(AddObj + " .FloatBox").css({
		display: "block",
		left: ($(window).width() - $(AddObj + " .FloatBox").outerWidth())/2,
		top: 150
	});
}
	this.die().live('click',function()
	{		
		var temp_float=new String;
		temp_float="<div class=\"FloatBg\"  style=\"height:"+$(document).height()+"px;width:"+$(document).width()+"px;filter:alpha(opacity=0);opacity:0;\"></div>";
		temp_float+="<div class=\"FloatBox\">";
		temp_float+="<div class=\"Box\">";
		temp_float+="<div class=\"title\"><h4></h4><span class=\"DialogClose\" title=\"关闭\"></span></div>";
		temp_float+="<div class=\"content link_lan\"><div class=\"wait\"></div></div>";
		temp_float+="</div>";
		temp_float+="</div>";
		if (AddObj=="body")
		{
		$("body").append(temp_float);	
		}
		else
		{
			$(AddObj).html(temp_float);
		}
		$(".DialogClose,.FloatBg").die().live('click',function(){DialogClose();});	
		$(AddObj+" .FloatBox .title h4").html(options.DialogTitle);
		var content=options.DialogContent;
		switch(options.DialogContentType){
		case "url":
		var url=content;
			url=url+($(this).attr('parameter'));
		$.ajax({
			url:url,
			dataType:"json"
		})
		.done(function(data) {
				if (data.status=='1'){
					 $(AddObj+" .FloatBox .content").html(data.data);
					 setPositionQS();
				}
				else
				{
					 $(AddObj+" .FloatBox .content").html(data.msg);
				}
		})
		.fail(function(data) {
			 $(AddObj+" .FloatBox .content").html('err');
		});
	  	break;
	  	case "text":
		$(AddObj+" .FloatBox .content").html(content);
		break;
		case "id":
		$(AddObj+" .FloatBox .content").html($(content).html());
		break;
		case "iframe":
		$(AddObj+" .FloatBox .content").html("<iframe src=\""+content+"\" width=\"100%\" height=\""+(parseInt(height)-30)+"px"+"\" scrolling=\"auto\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\"></iframe>");
		}
		$(AddObj+" .FloatBg").show().css("opacity", 0.1);
		var width=options.DialogWidth=="auto"?"auto":options.DialogWidth+"px";
		var DEFAULT_VERSION = "8.0";
		var ua = navigator.userAgent.toLowerCase();
		var isIE = ua.indexOf("msie")>-1;
		var safariVersion;
		if(isIE){
		    safariVersion =  ua.match(/msie ([\d.]+)/)[1];
		}
		if(safariVersion < DEFAULT_VERSION ){
		    width = '620px';
		}
		var height=options.DialogHeight=="auto"?"auto":options.DialogHeight+"px";
			$(AddObj + " .FloatBox").css({
				width: width,
				height: height
			});
			setPositionQS();
		$(AddObj+" .FloatBox .DialogClose").hover(function(){$(this).addClass("spanhover")},function(){$(this).removeClass("spanhover")});
		//alert(options.DialogWidth);
	});
}
})(jQuery); 