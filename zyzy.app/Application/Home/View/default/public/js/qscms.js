/*
 *上传附件
 *form     flie元素ID容
 *option   上传附件的参数  
 *fun      上传成功回调函数
 *before   上传附件前回调函数
 *going    执行上传附件时显示内容(默认为"请稍后...")
*/
/*(function($){
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
				//if($.isUp) return !1;
				if(before && !1 == before()) return !1;
			},
			'change':function(){
				//if($.isUp || !$.trim($(this).val())) return !1;
				if(!$.trim($(this).val())) return !1;
				upload();
			}
		});
	};
})(jQuery);*/
(function($){
	$.upload = function(form,option,fun,before,going){
		var settings = {
				type:'image'
			};
		if(option) $.extend(settings,option);
		new AjaxUpload(form,{
			action:qscms.root + '?m=Home&c=upload&a=attach',
			name:$(form).attr('name'),
			data:settings,
			responseType:'json',
			/*onChange:function(file,ext){
				alert('vdvdvd');
				if(!$.trim($(file).val())) return !1;
			},*/
			onSubmit:function(file,ext){
				/*if(ext && /^(jpg|jpeg|png|gif)$/.test(ext)){
					var af = this._input.files;
					//var byteSize = af[0].size;
					if ((byteSize / 1024) > (2 * 1024)) {
						alert('图片大小超出范围！');
						return false;
					} else {
						if(before && !1 == before()) return !1;
						//ext是后缀名
						form.disabled = "disabled";
					}
					if(before && !1 == before()) return !1;
					form.disabled = "disabled";
				} else {
					alert('不支持非图片格式！');
					return false;
				}*/
				if(before && !1 == before()) return !1;
				form.disabled = "disabled";
			},
			onComplete:function(file,result){
				form.disabled = "";
				if(result.status==1){
					fun && fun(result);
				}else{
					disapperTooltip('remind', result.msg);
					if(result.dialog) location.reload();
				}
			}
		});
	};
})(jQuery);