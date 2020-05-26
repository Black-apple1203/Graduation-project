/* eslint-disable require-jsdoc */

class RtcClient {
  constructor(options) {
    this.sdkAppId_ = options.sdkAppId;
    this.userId_ = options.userId;
    this.userSig_ = options.userSig;
    this.roomId_ = options.roomId;

    this.isJoined_ = false;
    this.isPublished_ = false;
    this.localStream_ = null;
    this.remoteStreams_ = [];

    // check if browser is compatible with TRTC
    TRTC.checkSystemRequirements().then(result => {
      if (!result) {
        //alert('Your browser is not compatible with TRTC! Please download Chrome M72+');
        alert('您的浏览器不兼容视频面试！ 请下载最新版谷歌浏览器！');
      }
    });
  }

  async join() {
    if (this.isJoined_) {
      console.warn('duplicate RtcClient.join() observed');
      return;
    }

    // create a client for RtcClient
    this.client_ = TRTC.createClient({
      mode: 'videoCall', // 实时通话模式
      sdkAppId: this.sdkAppId_,
      userId: this.userId_,
      userSig: this.userSig_
    });

    // 处理 client 事件
    this.handleEvents();
    try {
      // join the room
      await this.client_.join({ roomId: this.roomId_ });
      console.log('进入房间成功！');
      this.isJoined_ = true;
    } catch (error) {
      console.error('failed to join room because: ' + error);
      alert(
        '进房失败原因：' +
          error +
          '\r\n\r\n请确保您的网络连接是正常的' +
          '\r\n\r\n另外，请确保您的账号信息是正确的。'
      );
      console.log('进房错误！');
      return;
    }

    try {
      // 采集摄像头和麦克风视频流
      await this.createLocalStream({ audio: true, video: true });
      console.log('摄像头及麦克风采集成功！');
    } catch (error) {
      layer.msg('摄像头及麦克风采集失败！');
      console.error('createLocalStream with audio/video failed: ' + error);
      alert(
        '请确认已连接摄像头和麦克风并授予其访问权限！\r\n\r\n 如果您当前浏览器已允许摄像头和麦克风的权限仍不能正常使用，建议您使用360浏览器！'
      );
      try {
        // fallback to capture camera only
        await this.createLocalStream({ audio: false, video: true });
        console.log('采集摄像头成功！');
      } catch (error) {
        layer.msg('采集摄像头失败！');
        console.error('createLocalStream with video failed: ' + error);
        return;
      }
    }

    this.localStream_.on('player-state-changed', event => {
      console.log(`local stream ${event.type} player is ${event.state}`);
      if (event.type === 'video' && event.state === 'PLAYING') {
        // dismiss the remote user UI placeholder
      } else if (event.type === 'video' && event.state === 'STOPPPED') {
        // show the remote user UI placeholder
      }
    });

    // 在名为 ‘local_stream’ 的 div 容器上播放本地音视频
    this.localStream_.play('local_stream');

    // publish local stream by default after join the room
    if (!isDetectPage) {
      await this.publish();
      console.log('发布本地流成功！');
      this.unAllAudio();
    }
	if (isDetectPage) { // 检测设备页面
		detectVolume(this.localStream_);
	}
  }

  async leave() {
    if (!this.isJoined_) {
      console.warn('leave() - leave without join()d observed');
      layer.msg('请先加入房间！');
      return;
    }

    if (this.isPublished_) {
      // ensure the local stream has been unpublished before leaving.
      await this.unpublish(true);
    }

    try {
      // leave the room
      await this.client_.leave();
      console.log('退房成功！');
      this.isJoined_ = false;
    } catch (error) {
      layer.msg('退出房间失败！');
      console.error('failed to leave the room because ' + error);
      location.reload();
    } finally {
      // 停止本地流，关闭本地流内部的音视频播放器
      this.localStream_.stop();
      // 关闭本地流，释放摄像头和麦克风访问权限
      this.localStream_.close();
      this.localStream_ = null;
    }
  }

  async publish() {
    if (!this.isJoined_) {
      console.log('请先加入房间再点击开始推流！');
      return;
    }
    if (this.isPublished_) {
      console.error('当前正在推流！');
      return;
    }
    try {
      // 发布本地流
      await this.client_.publish(this.localStream_);
      console.log('发布本地流成功！');
      this.isPublished_ = true;
    } catch (error) {
      console.error('failed to publish local stream ' + error);
      layer.msg('发布本地流失败！');
      this.isPublished_ = false;
    }
  }

  async unpublish(isLeaving) {
    if (!this.isJoined_) {
      console.warn('请先加入房间再停止推流！');
      return;
    }
    if (!this.isPublished_) {
      console.warn('当前尚未发布本地流！');
      return;
    }

    try {
      // 停止发布本地流
      await this.client_.unpublish(this.localStream_);
      this.isPublished_ = false;
      console.log('停止发布本地流成功！');
    } catch (error) {
      console.error('failed to unpublish local stream because ' + error);
      console.warn('停止发布本地流失败！');
      if (!isLeaving) {
        console.error('停止发布本地流失败，退出房间！');
        this.leave();
      }
    }
  }

  unAllAudio() {
    setTimeout(function() {
      $('video').each(function(index, value) {
        console.log('video' + $('video')[index].paused);
      });
      console.log($('audio').length);
      $('audio').each(function(index, value) {
        console.log('audio' + $('audio')[index].paused);
        if ($('audio')[index].paused) {
          $('audio')[index].play();
        }
      });
    }, 1000);
  }
  
  muteLocalAudio() {
  	this.localStream_.muteAudio();
  }
  
  unmuteLocalAudio() {
  	this.localStream_.unmuteAudio();
  }
  
  switchLocalDevice(type, dId) {
		if (eval(type) === 0) { // 0代表摄像头
				this.localStream_.switchDevice('video', dId).then(() => {
				console.log('switch camera success');
				layer.msg('设备切换成功');
			});
		} else if (eval(type) === 1) { // 1代表麦克风
				this.localStream_.switchDevice('audio', dId).then(() => {
				console.log('switch microphone success');
				layer.msg('设备切换成功');
			});
		}
  }
  

  async createLocalStream(options) {
    this.localStream_ = TRTC.createStream({
      audio: options.audio, // 采集麦克风
      video: options.video, // 采集摄像头
      userId: this.userId_
      // cameraId: getCameraId(),
      // microphoneId: getMicrophoneId()
    });
    // 设置视频分辨率帧率和码率
    this.localStream_.setVideoProfile('480p');

    await this.localStream_.initialize();
  }

  handleEvents() {
    // 处理 client 错误事件，错误均为不可恢复错误，建议提示用户后刷新页面
    this.client_.on('error', err => {
      console.error(err);
      layer.msg('客户端错误：' + err);
      // location.reload();
    });

    // 处理用户被踢事件，通常是因为房间内有同名用户引起，这种问题一般是应用层逻辑错误引起的
    // 应用层请尽量使用不同用户ID进房
    this.client_.on('client-banned', err => {
      console.error('client has been banned for ' + err);
      layer.msg('用户被踢出房间！');
      // location.reload();
    });

    // 远端用户进房通知 - 仅限主动推流用户
    this.client_.on('peer-join', evt => {
      const userId = evt.userId;
      console.log('远端用户进房 - ' + userId);
    });
    // 远端用户退房通知 - 仅限主动推流用户
    this.client_.on('peer-leave', evt => {
      const userId = evt.userId;
      console.log('远端用户退房 - ' + userId);
    });

    // 处理远端流增加事件
    this.client_.on('stream-added', evt => {
      const remoteStream = evt.stream;
      const id = remoteStream.getId();
      const userId = remoteStream.getUserId();
      console.log(`remote stream added: [${userId}] ID: ${id} type: ${remoteStream.getType()}`);
      console.log('远端流增加 - ' + userId);
      console.log('subscribe to this remote stream');
      // 远端流默认已订阅所有音视频，此处可指定只订阅音频或者音视频，不能仅订阅视频。
      // 如果不想观看该路远端流，可调用 this.client_.unsubscribe(remoteStream) 取消订阅
      this.client_.subscribe(remoteStream);
    });
	
	var intDiff = parseInt(0),
		callTimer;
    // 远端流订阅成功事件
    this.client_.on('stream-subscribed', evt => {
      const remoteStream = evt.stream;
      const id = remoteStream.getId();
      this.remoteStreams_.push(remoteStream);
      addView(id);
      // 在指定的 div 容器上播放音视频
      remoteStream.play(id);
      const userId = remoteStream.getUserId();
      console.log('stream-subscribed ID: ', id);
      console.log('远端流订阅成功 - ' + remoteStream.getUserId());
      // 处理来自触屏的视频比例
      if (userId.split('spl')[1] == 'mobile') {
        $('#video_grid').addClass('mobile');
      } else {
        $('#video_grid').removeClass('mobile');
      }
	  $('#video_grid .wait_join').hide();
	  timerJoin(intDiff);
    });

    // 处理远端流被删除事件
    this.client_.on('stream-removed', evt => {
      const remoteStream = evt.stream;
      const id = remoteStream.getId();
      // 关闭远端流内部的音视频播放器
      remoteStream.stop();
      this.remoteStreams_ = this.remoteStreams_.filter(stream => {
        return stream.getId() !== id;
      });
      removeView(id);
      console.log(`stream-removed ID: ${id}  type: ${remoteStream.getType()}`);
      console.log('远端流删除 - ' + remoteStream.getUserId());
	  $('#video_grid .wait_join').show();
	  layer.msg('对方已退出房间');
	  clearInterval(callTimer);
	  intDiff = parseInt(0);
    });
	
	// 计时
	function timerJoin(intDiff) {
		callTimer = window.setInterval(function() {
			var minute = 0,
				second = 0; //时间默认值
			if (intDiff > 0) {
				minute = Math.floor(intDiff / 60);
				second = Math.floor(intDiff) - minute * 60;
			}
			if (minute <= 9) minute = "0" + minute;
			if (second <= 9) second = "0" + second;
			$("#J_view_time").html(minute + ":" + second);
			intDiff++;
		}, 1000);
	}

    // 处理远端流更新事件，在音视频通话过程中，远端流音频或视频可能会有更新
    this.client_.on('stream-updated', evt => {
      const remoteStream = evt.stream;
      console.log(
        'type: ' +
          remoteStream.getType() +
          ' stream-updated hasAudio: ' +
          remoteStream.hasAudio() +
          ' hasVideo: ' +
          remoteStream.hasVideo()
      );
      console.log('远端流更新！');
      $('#video_grid .wait_join').hide();
      this.unAllAudio();
    });

    // 远端流音频或视频mute状态通知
    this.client_.on('mute-audio', evt => {
      console.log(evt.userId + ' mute audio');
    });
    this.client_.on('unmute-audio', evt => {
      console.log(evt.userId + ' unmute audio');
    });
    this.client_.on('mute-video', evt => {
      console.log(evt.userId + ' mute video');
    });
    this.client_.on('unmute-video', evt => {
      console.log(evt.userId + ' unmute video');
    });

    // 信令通道连接状态通知
    this.client_.on('connection-state-changed', evt => {
      console.log(`RtcClient state changed to ${evt.state} from ${evt.prevState}`);
    });
  }
}
