/* eslint-disable require-jsdoc */

let rtc = null;

$("#join").on("click", function(e) {
	e.preventDefault();
	console.log("join");
	if (rtc) return;
	const userId = $("#userId").val();
	const roomId = $("#roomId").val();
	const config = globalConfig;
	rtc = new RtcClient({
		userId,
		roomId,
		sdkAppId: config.sdkAppId,
		userSig: config.userSig
	});
	rtc.join();
});
// 加入房间
if (isDetectPage || $(".vi_media_room").length > 0) {
	joinRoom();
}
function joinRoom() {
	if (rtc) return;
	const userId = $("#userId").val();
	const roomId = $("#roomId").val();
	const config = globalConfig;
	rtc = new RtcClient({
		userId,
		roomId,
		sdkAppId: config.sdkAppId,
		userSig: config.userSig
	});
	rtc.join();
}

// 关闭音频
$(".J_mute").on("click", function(e) {
	e.preventDefault();
	if (rtc) {
		if ($(this).hasClass("disable")) {
			$(this).removeClass("disable");
			rtc.unmuteLocalAudio();
			layer.msg("解除静音");
		} else {
			$(this).addClass("disable");
			rtc.muteLocalAudio();
			layer.msg("静音成功");
		}
	}
});

$("#publish").on("click", function(e) {
	e.preventDefault();
	console.log("publish");
	if (!rtc) {
		layer.msg("请先加入房间！");
		return;
	}
	rtc.publish();
});

$("#unpublish").on("click", function(e) {
	e.preventDefault();
	console.log("unpublish");
	if (!rtc) {
		layer.msg("请先加入房间！");
		return;
	}
	rtc.unpublish();
});

$("#leave").on("click", function(e) {
	e.preventDefault();
	if (!rtc) {
		layer.msg("请先加入房间！");
		return;
	}
	var leaveIndex = layer.confirm(
		"提示",
		{
			title: "提示",
			content:
				"挂断后将断开本次视频面试，再次开启请刷新当前页面或视频面试列表进入房间",
			btn: ["立即挂断", "取消"] //按钮
		},
		function() {
			console.log("leave");
			rtc.leave();
			rtc = null;
			layer.close(leaveIndex);
		},
		function() {}
	);
});

// 切换设备
$(".J_device_choose").click(function() {
	if ($(this).hasClass("disable")) {
		return false;
	}
	//$(this).toggleClass('pull');
	var $that = $(this),
		thisType = $(this).attr("type"),
		menuArr = [];
	if (eval(thisType) === 0) {
		// 0代表摄像头
		menuArr = camerasList;
	} else if (eval(thisType) === 1) {
		// 1代表麦克风
		menuArr = microphonesList;
	} else if (eval(thisType) === 2) {
		// 2代表扬声器
		menuArr = speakersList;
	}
	$(this).selectMenu({
		regular: true,
		data: function() {
			var d = new Array();
			$.each(menuArr, function(index, value) {
				var rd = {
					content: menuArr[index].label,
					callback: function() {
						$that.text(menuArr[index].label);
						let dId = menuArr[index].deviceId;
						rtc.switchLocalDevice(thisType, dId);
					}
				};
				d.push(rd);
			});
			return d;
		}
	});
});
