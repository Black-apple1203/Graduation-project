+function() {

    // 根据关键字搜索位置
    function getAcMaps(map) {
        var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
            {"input" : "mapSearchInput"
            ,"location" : map
        });
        var myValue;
        ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
            var _value = e.item.value;
            myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
            setPlace();
        });
        // 搜索
        $('#key-search-button').die().live('click', function() {
            var searchKey = $.trim($('#mapSearchInput').val());
            if (!searchKey.length) return false;
            myValue = searchKey;
            setPlace();
        });
        function setPlace(){
            function myFun(){
                var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
                var map = new BMap.Map("mapShow");
                map.enableScrollWheelZoom();
                map.addControl(new BMap.NavigationControl());
                var point = new BMap.Point(pp.lng,pp.lat);
                map.centerAndZoom(point, 15);
                setMarkers(map, pp, myValue);// 创建标注
                setRelatePosition(map, myValue);// 设置关联信息
                savePoint(pp.lng, pp.lat);
                addClickListener(map);
            }
            var local = new BMap.LocalSearch(map, { //智能搜索
                onSearchComplete: myFun
            });
            local.search(myValue);
            setMapBounds(map);// 设置可视范围
        }
    }

    // 初始化地图
    function getMaps(lng, lat) {
        var map = new BMap.Map("mapShow");
        map.enableScrollWheelZoom();
        map.addControl(new BMap.NavigationControl());
        var point = new BMap.Point(lng,lat);
        map.centerAndZoom(point, 15);
        var myGeo = new BMap.Geocoder();
        var position;
        function geocodeSearch(pt){
            myGeo.getLocation(pt, function(rs){
                var addComp = rs.addressComponents;
                // 街道、区、市逐层向上找
                if (addComp.street.length) {
                    position = addComp.street;
                } else if (addComp.district.length) {
                    position = addComp.district;
                } else {
                    position = addComp.city;
                }
                setMarkers(map, point, position);// 创建标注
                setRelatePosition(map, position);// 设置关联信息
                getAcMaps(map);// 搜索
            });
        }
        geocodeSearch(point); // 根据经纬度获取位置信息
        addClickListener(map);
        setMapBounds(map);// 设置可视范围
    }

    // 创建标注
    function setMarkers(map, point, position) {
        map.clearOverlays();
        var markerls = new BMap.Marker(point);
        //新建标注
        var infoWindow = new BMap.InfoWindow("<span style=\"font-size:14px;\">当前位置:" + position + "<br><span style=\"font-size:12px; line-height:24px;\">(提示:任意点击地图,选择您的位置)</span></span>");
        map.openInfoWindow(infoWindow, point);
        //默认时，显示窗口信息

        markerls.addEventListener("click", function() {
            map.openInfoWindow(infoWindow, point);
        });
        //点击标注点时显示窗口信息

        markerls.enableDragging(true);
        //启用地图鼠标拖拽
        map.addOverlay(markerls);
        //添加标注点在地图上
    }

    // 设置左侧相关地区
    function setRelatePosition(map, position) {
        var options = {
            onSearchComplete: function(results){
                // 判断状态是否正确
                if (local.getStatus() == BMAP_STATUS_SUCCESS){
                    var sHtml = '';
                    for (var i = 0; i < results.getCurrentNumPois(); i ++){
                        var resultsAdr = '';
                        if (i <= 12) {
                            var ppArr = results.getPoi(i).point;
                            if (results.getPoi(i).province) {
                                resultsAdr += results.getPoi(i).province;
                            } else if (results.getPoi(i).city) {
                                resultsAdr += results.getPoi(i).city;
                            } else {
                                resultsAdr += results.getPoi(i).title;
                            }
                            sHtml += '<li data-position="'+ppArr.lng+','+ppArr.lat+','+results.getPoi(i).title+'"><div class="tit">'+results.getPoi(i).title+'</div><div class="adr">'+resultsAdr+'</div></li><div class="clear"></div>';
                        }
                    }
                    document.getElementById("mapSearchResult").innerHTML = sHtml;
                }
            }
        };
        var local = new BMap.LocalSearch(map, options);
        local.search(position);
    }

    // 保存当前选中点的经纬度
    function savePoint(lng, lat) {
        $('#lng').val(lng);
        $('#lat').val(lat);
    }

    // 添加地图点击监听
    function addClickListener(map) {
        //创建地理编码
        var geoc = new BMap.Geocoder();
        map.addEventListener('click', function(e) {
            var pt = e.point;
            //获取新的经纬度
            geoc.getLocation(pt, function(rs) {
                //根据坐标得到地址描述
                var addComp = rs.addressComponents;
                var address = addComp.district + addComp.street + addComp.streetNumber;
                //获取地址
                setMarkers(map, pt, address);
                savePoint(pt.lng,pt.lat);
                setRelatePosition(map, address);
                setMapBounds(map);// 设置可视范围
            });
        });
    }
    
    // 设置可视范围
    function setMapBounds(map) {
        var bs = map.getBounds();   //获取可视区域
        var bssw = bs.getSouthWest();   //可视区域左下角
        var bsne = bs.getNorthEast();   //可视区域右上角
        $('#ldLng').val(bssw.lng);
        $('#ldLat').val(bssw.lat);
        $('#ruLng').val(bsne.lng);
        $('#ruLat').val(bsne.lat);
        //console.log("当前地图可视范围是：" + bssw.lng + "," + bssw.lat + "到" + bsne.lng + "," + bsne.lat);
    }

    // 处理跳转链接
    function reQsMapUrl(url) {
        url = url.replace('lngVal',$('#lng').val());
        url = url.replace('latVal',$('#lat').val());
        url = url.replace('ldLngVal',$('#ldLng').val());
        url = url.replace('ldLatVal',$('#ldLat').val());
        url = url.replace('ruLngVal',$('#ruLng').val());
        url = url.replace('ruLatVal',$('#ruLat').val());
        setTimeout(function() {
            window.location = url;
        }, 50)
    }

    // 显示地图搜索框
    function showMapDialog() {
        var mDialog = $(this).dialog({
            title: '选择位置',
            header: false,
            footer: false,
            border: false,
            content: ['<div class="map-show">', '<div class="ms-box">', '<div class="done-group pie_about">', '<div class="btn-group">', '<div id="sure-map" class="btn_yellow c-btn gre-btn">确定</div>', '<div id="cancel-map" class="btn_lightgray c-btn gry-btn">取消</div>', '<div class="clear"></div>', '</div>', '</div>', '<div>', '<div class="search-panel">', '<div class="sea-box">', '<div class="sea-container">', '<div class="sea-content">', '<input id="mapSearchInput" class="sole-input" type="text" name="word" autocomplete="off" maxlength="256" placeholder="搜地点" value="">', '</div>', '</div>', '<button id="key-search-button" class="search-button"></button>', '<div class="clear"></div>', '</div>', '</div>', '</div>', '<div class="mb-left">', '<div class="mb-title">请选择附近地标或直接搜索位置</div>', '<div class="mb-li" id="mapSearchResult">', '</div>', '</div>', '<div class="mb-right" id="mapShow">', '</div>', '<div class="clear"></div>', '</div>', '</div>'].join(''),
            loadFun: function() {
                // 设置默认地区
                var mapLng = ''
                    , mapLat = '';
                if ($('#lng').val()) {
                    mapLng = $('#lng').val();
                    mapLat = $('#lat').val();
                } else {
                    mapLng = $('.map-lng').val();
                    mapLat = $('.map-lat').val();
                }
                savePoint(mapLng, mapLat);
                getMaps(mapLng, mapLat);
                $("#mapSearchResult").find('li').die().live('click', function (){
                    // 左侧定位
                    var positionArr = $(this).data('position').split(',');
                    var map = new BMap.Map("mapShow");
                    map.enableScrollWheelZoom();
                    map.addControl(new BMap.NavigationControl());
                    var point = new BMap.Point(positionArr[0],positionArr[1]);
                    map.centerAndZoom(point, 15);
                    setMarkers(map, point, positionArr[2]);// 创建标注
                    savePoint(positionArr[0], positionArr[1]);
                    addClickListener(map);
                    setMapBounds(map);// 设置可视范围
                });
                // 确定
                $('#sure-map').die().live('click', function() {
                    $('.modal_dialog').remove();
                    $('.modal_backdrop').remove();
                    var ldDialog = $(this).dialog({
                        loading: true,
                        footer: false,
                        header: false,
                        border: false
                    });
                    reQsMapUrl(qsMapUrl);
                });
                // 取消
                $('#cancel-map').die().live('click', function () {
                    savePoint(mapLng, mapLat);
                    $('.modal_dialog').remove();
                    $('.modal_backdrop').remove();
                });
            }
        });
    }

    $('#popupBox').click(function() {
        showMapDialog();
    });

}(jQuery)
