function baidumap(com_companyname,com_address,com_map_x,com_map_y,com_map_zoom,c_map_x,c_map_y,c_map_zoom){
	var map = new BMap.Map("container");
	var infoWindow = '';
	var overlays = [];
	var overlaycomplete = function(e){
		clearAll();
	    overlays.push(e.overlay);
	    var point = new BMap.Point(e.overlay.point.lng, e.overlay.point.lat);
		map.centerAndZoom(point, com_map_zoom);
		map.openInfoWindow(infoWindow,point);
		map.setCenter(point);
	    var geoc = new BMap.Geocoder();
		geoc.getLocation(point, function(rs){
			var addComp = rs.addressComponents;
			G('suggestId').value = addComp.province+addComp.city+addComp.district+addComp.street+addComp.streetNumber;
			infoWindowSet();
		}); 
		G("map_x").value=e.overlay.point.lng;
		G("map_y").value= e.overlay.point.lat;
		G("map_zoom").value=  map.getZoom();
		      
	};
	openDraw();
	function openDraw(){
		//实例化鼠标绘制工具
	    var drawingManager = new BMapLib.DrawingManager(map, {
	        isOpen: true, //是否开启绘制模式
	        enableDrawingTool: false, //是否显示工具栏
	        drawingToolOptions: {
	            anchor: BMAP_ANCHOR_TOP_RIGHT, //位置
	            offset: new BMap.Size(5, 5), //偏离值
		        drawingTypes : [
		            BMAP_DRAWING_MARKER
		        ]
	        },
	    });  
	    drawingManager.setDrawingMode(BMAP_DRAWING_MARKER);
		 //添加鼠标绘制工具监听事件，用于获取绘制结果
	    drawingManager.addEventListener('overlaycomplete', overlaycomplete);
	}
	function clearAll() {
		for(var i = 0; i < overlays.length; i++){
	        map.removeOverlay(overlays[i]);
	    }
	    map.removeOverlay(qs_marker);
	    overlays.length = 0   
	}

	function setPlace(){
		map.clearOverlays();    //清除地图上所有覆盖物
		function myFun(){
			var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
			map.centerAndZoom(pp, 18);
			map.addOverlay(new BMap.Marker(pp));    //添加标注
			G("map_x").value=pp.lng;
			G("map_y").value= pp.lat;
			G("map_zoom").value=  map.getZoom();
		}
		var local = new BMap.LocalSearch(map, { //智能搜索
		  onSearchComplete: myFun
		});
		local.search(G('suggestId').value);
		openDraw();
	}
	$("#search").live('click',function(){
		if(G('suggestId').value==''){
			disapperTooltip("remind", "请输入详细地址");return false;
		}
		setPlace();
	});
	function G(id) {
		return document.getElementById(id);
	}

	var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
	{
		"input" : "suggestId",
		"location" : map
	});
	ac.setInputValue(com_address);
	ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
		// alert(1);
		var _value = e.item.value;
		myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
		G("searchResultPanel").innerHTML ="onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;
		G('suggestId').value = myValue;
	});
	//假如已经设置了标注
	function infoWindowSet(){
		var opts = {
		width : 300,     // 信息窗口宽度
		height: 60,     // 信息窗口高度
		title : com_companyname  // 信息窗口标题
		}
		infoWindow = new BMap.InfoWindow("<span style='font-size:12px;'>（提示：任意点击地图或通过精确搜索选择您的位置）</span>", opts);
	}
	infoWindowSet();
	//假如有设置的，显示参数
	if(com_map_x && com_map_y && com_map_zoom>0){
	var point = new BMap.Point(com_map_x, com_map_y);
	map.centerAndZoom(point, com_map_zoom);
	var qs_marker = new BMap.Marker(point);        // 创建标注
	map.addOverlay(qs_marker);
	map.openInfoWindow(infoWindow,point);
	map.setCenter(point);
	G("map_x").value=com_map_x;
	G("map_y").value= com_map_y;
	G("map_zoom").value=  com_map_zoom;
	}else{
	var point = new BMap.Point(c_map_x,c_map_y);
	map.centerAndZoom(point, c_map_zoom);
	map.setCenter(point);
	}
	map.addControl(new BMap.NavigationControl());//添加鱼骨
	map.enableScrollWheelZoom();//启用滚轮放大缩小，默认禁用。

}