/*
 *上传附件
 *form     flie元素ID容
 *option   上传附件的参数  
 *fun      上传成功回调函数
 *before   上传附件前回调函数
 *going    执行上传附件时显示内容(默认为"请稍后...")
*/
(function($){
	$.isUp = 0;
	$.upload = function(form,option,fun,before,going){
		var settings = {
				type:'image'
			},
			upload = function(){
				$.isUp=1;
				$.ajaxFileUpload({
					url: qscms.root + '/?c=upload&a=attach',
					type: 'POST',
					data:settings,
					secureuri:false,
					fileElementId:form,
					dataType:'json',
					success:function(result){
						if(result.status==1){
							fun && fun(result);
						}else{
							alert(result.msg);
							if(result.dialog) location.reload();
						}
						$.isUp = 0;
					}
				});
			};
		if(option) $.extend(settings,option);
		$(form).die().live({
			'click':function(){
				if($.isUp) return !1;
				if(before && !1 == before()) return !1;
			},
			'change':function(){
				if($.isUp || !$.trim($(this).val())) return !1;
				upload();
			}
		});
	};
})(jQuery);