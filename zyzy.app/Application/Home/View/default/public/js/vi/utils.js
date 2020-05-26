/* eslint-disable require-jsdoc */

function addView(id) {
  if (!$('#' + id)[0]) {
    $('<div/>', {
      id,
      class: 'player_box'
    }).appendTo('#video_grid');
  }
}

function removeView(id) {
  if ($('#' + id)[0]) {
    $('#' + id).remove();
  }
}

// 摄像头列表
TRTC.getCameras().then(devices => {
	camerasList = devices;
	if (isDetectPage) {
		$('.J_device_choose').eq(0).text(devices[0].label);
	}
});

// 麦克风列表
TRTC.getMicrophones().then(devices => {
	microphonesList = devices;
	if (isDetectPage) {
		$('.J_device_choose').eq(1).text(devices[0].label);
	}
});

// 扬声器列表
TRTC.getSpeakers().then(devices => {
	speakersList = devices;
	if (isDetectPage) {
		$('.J_device_choose').eq(2).text(devices[0].label);
	}
});


function getCameraId() {
  const selector = document.getElementById('cameraId');
  const cameraId = selector[selector.selectedIndex].value;
  console.log('selected cameraId: ' + cameraId);
  return cameraId;
}

function getMicrophoneId() {
  const selector = document.getElementById('microphoneId');
  const microphoneId = selector[selector.selectedIndex].value;
  console.log('selected microphoneId: ' + microphoneId);
  return microphoneId;
}

// fix jquery touchstart event warn in chrome M76
jQuery.event.special.touchstart = {
  setup: function(_, ns, handle) {
    if (ns.includes('noPreventDefault')) {
      this.addEventListener('touchstart', handle, { passive: false });
    } else {
      this.addEventListener('touchstart', handle, { passive: true });
    }
  }
};
jQuery.event.special.touchmove = {
  setup: function(_, ns, handle) {
    if (ns.includes('noPreventDefault')) {
      this.addEventListener('touchmove', handle, { passive: false });
    } else {
      this.addEventListener('touchmove', handle, { passive: true });
    }
  }
};
