/*!
 * cxScroll 1.0.0
 * DownLoad by www.juheweb.com
 */
(function($){
	$.fn.cxScroll=function(settings){
		if(!this.length){return;};
		settings=$.extend({},$.cxScroll.defaults,settings);

		var obj=this;
		var scroller={};
		scroller.fn={};

		scroller.box=obj.find(".box");
		scroller.list=scroller.box.find(".list");
		scroller.items=scroller.list.find("li");
		scroller.itemSum=scroller.items.length;

		if(scroller.itemSum<=1){return;};

		scroller.plusBtn=obj.find(".plus");
		scroller.minusBtn=obj.find(".minus");
		scroller.itemWidth=scroller.items.outerWidth();
		scroller.itemHeight=scroller.items.outerHeight();

		if(settings.direction=="left"||settings.direction=="right"){
			if(scroller.itemWidth*scroller.itemSum<=scroller.box.outerWidth()){return;};
			scroller.plusVal="left";
			scroller.minusVal="right";
			scroller.moveVal=scroller.itemWidth;
		}else{
			if(scroller.itemHeight*scroller.itemSum<=scroller.box.outerHeight()){return;};
			scroller.plusVal="top";
			scroller.minusVal="bottom";
			scroller.moveVal=scroller.itemHeight;
		};

		// 元素：左右操作按钮
		if(settings.plus&&!scroller.plusBtn.length){
			scroller.plusBtn=$("<a></a>",{"class":"plus"}).appendTo(obj);
		};
		if(settings.minus&&!scroller.minusBtn.length){
			scroller.minusBtn=$("<a></a>",{"class":"minus"}).appendTo(obj);
		};

		// 元素：后补
		scroller.list.append(scroller.list.html());

		// 方法：开始
		scroller.fn.on=function(){
			if(!settings.auto){return;};
			scroller.fn.off();

			scroller.run=setTimeout(function(){
				scroller.fn.goto(settings.direction);
			},settings.time);
		};

		// 方法：停止
		scroller.fn.off=function(){
			if(typeof(scroller.run)!=="undefined"){clearTimeout(scroller.run);};
		};

		// 方法：增加控制
		scroller.fn.addControl=function(){
			if(settings.plus&&scroller.minusBtn.length){
				scroller.plusBtn.bind("click",function(){
					scroller.fn.goto(scroller.plusVal);
				});
			};
			if(settings.minus&&scroller.minusBtn.length){
				scroller.minusBtn.bind("click",function(){
					scroller.fn.goto(scroller.minusVal);
				});
			};
		};

		// 方法：解除控制
		scroller.fn.removeControl=function(){
			if(scroller.plusBtn.length){scroller.plusBtn.unbind("click");};
			if(scroller.minusBtn.length){scroller.minusBtn.unbind("click");};
		};

		// 方法：滚动
		scroller.fn.goto=function(d){
			scroller.fn.off();
			scroller.fn.removeControl();
			scroller.box.stop(true);

			var _max;	// _max	滚动的最大限度
			var _dis;	// _dis	滚动的距离

			switch(d){
			case "left":
			case "top":
				_max=0;
				if(d=="left"){
					if(parseInt(scroller.box.scrollLeft(),10)==0){
						scroller.box.scrollLeft(scroller.itemSum*scroller.moveVal);
					};
					_dis=scroller.box.scrollLeft()-(scroller.moveVal*settings.step);
					if(_dis<_max){_dis=_max};
					scroller.box.animate({"scrollLeft":_dis},settings.speed,function(){
						if(parseInt(scroller.box.scrollLeft(),10)<=_max){
							scroller.box.scrollLeft(0);
						};
						scroller.fn.addControl();
					});
				}else{
					if(parseInt(scroller.box.scrollTop(),10)==0){
						scroller.box.scrollTop(scroller.itemSum*scroller.moveVal);
					};
					_dis=scroller.box.scrollTop()-(scroller.moveVal*settings.step);
					if(_dis<_max){_dis=_max};
					scroller.box.animate({"scrollTop":_dis},settings.speed,function(){
						if(parseInt(scroller.box.scrollTop(),10)<=_max){
							scroller.box.scrollTop(0);
						};
						scroller.fn.addControl();
					});
				};
				break;
			case "right":
			case "bottom":
				_max=scroller.itemSum*scroller.moveVal;
				if(d=="right"){
					_dis=scroller.box.scrollLeft()+(scroller.moveVal*settings.step);
					if(_dis>_max){_dis=_max};
					scroller.box.animate({"scrollLeft":_dis},settings.speed,function(){
						if(parseInt(scroller.box.scrollLeft(),10)>=_max){
							scroller.box.scrollLeft(0);
						};
					});
				}else{
					_dis=scroller.box.scrollTop()+(scroller.moveVal*settings.step);
					if(_dis>_max){_dis=_max};
					scroller.box.animate({"scrollTop":_dis},settings.speed,function(){
						if(parseInt(scroller.box.scrollTop(),10)>=_max){
							scroller.box.scrollTop(0);
						};
					});
				};
				break;
			};
			scroller.box.queue(function(){
				scroller.fn.addControl();
				scroller.fn.on();
				$(this).dequeue();
			});
		};

		// 事件：鼠标移入停止，移出开始
		scroller.box.hover(function(){
			scroller.fn.off();
		},function(){
			scroller.fn.on();
		});

		scroller.fn.addControl();
		scroller.fn.on();
	};

	// 默认值
	$.cxScroll={defaults:{
		direction:"right",	// 滚动方向
		step:1,				// 滚动步长
		speed:800,			// 滚动速度
		time:4000,			// 自动滚动间隔时间
		auto:true,			// 是否自动滚动
		plus:true,			// 是否使用 plus 按钮
		minus:true			// 是否使用 minus 按钮
	}};
})(jQuery);