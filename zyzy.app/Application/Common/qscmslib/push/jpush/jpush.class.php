<?php
/**
 * 极光推送
 */
require_once dirname(__FILE__) . '/Client.php';
require_once dirname(__FILE__) . '/Exceptions/APIConnectionException.php';
require_once dirname(__FILE__) . '/Exceptions/APIRequestException.php';
use JPush\Client as JPush;
use JPush\Exceptions\APIConnectionException;
use JPush\Exceptions\APIRequestException;
class jpush_push{
	protected $_setting = array();
	protected $_error = '';
	protected $platform = array('android','ios','winphone');
	public function __construct($setting) {
		$this->_setting = $setting;
	}
	/**
	 * [sendPush 发送通知]
	 * @param  [option] terminal 		使用终端[1:android|2:ios|3:winphone|all]
	 * @param  [option] type 			目标类型[all|tag|alias|regist]
	 * @param  [option] registration_id APP唯一码
	 * @param  [option] extras 			自定义参数
	 * @param  [option] time_to_live 	自定义参数
	 * @param  [option] apns_production 模式[true:生产模式,false:开发模式]
	 */
	public function sendPush($option){
		import('Common.qscmslib.push.jpush.autoload');
		$data = array(
			'terminal' => '',
			'type' => 'regist',//all,tag,alias,regist
			'registration_id' => '',
			'extras' => array(),
			'time_to_live' => 86400,
			'apns_production' => false
		);
		$data = array_merge($data,$option);
		$client = new JPush($this->_setting['appKey'], $this->_setting['masterSecret']);
		$pusher = $client->push();
        $pusher->setPlatform(in_array($data['terminal'],$this->platform)?$data['terminal']:'all');
        //$pusher->addAllAudience();
        // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
        // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
        // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求
        // ->addAlias('alias')
        //$pusher->addTag(array('tag1', 'tag2'));
        if($data['type'] == 'regist' && $data['registration_id']){
	        $pusher->addRegistrationId($data['registration_id']);
	    }
        $pusher->setNotificationAlert($data['alert']);
        switch($data['terminal']){
			case 'android':
				$pusher->androidNotification($data['alert'], array(
		            'title' => $data['title'],
		            // 'builder_id' => 2,
		            'extras' => $data['extras']
		        ));
				break;
			case 'ios':
				$pusher->iosNotification($data['alert'], array(
		            'sound' => 'sound.caf',
		            'badge' => '+1',
		            'content-available' => true,
		            'mutable-content' => true,
		            //'category' => 'jiguang',
		            'extras' => $data['extras']
		        ));
				break;
			case 'winphone':
				$pusher->addWinPhoneNotification($data['alert'],$data['title'],null,$data['extras']);
				break;
		}
        // $pusher->message($data['alert'], array(
        //     'title' => $data['title'],
        //     // 'content_type' => 'text',
        //     'extras' => array(
        //         'key' => 'value',
        //         'jiguang'
        //     ),
        // ));
        $pusher->options(array(
            // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
            // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
            // 这里设置为 100 仅作为示例

            // 'sendno' => 100,

            // time_to_live: 表示离线消息保留时长(秒)，
            // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
            // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
            // 这里设置为 1 仅作为示例

            'time_to_live' => $data['time_to_live'],

            // apns_production: 表示APNs是否生产环境，
            // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送开发环境

            'apns_production' => $data['apns_production'],

            // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
            // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
            // 这里设置为 1 仅作为示例

            // 'big_push_duration' => 1
            
            'override_msg_id' => isset($data['msgID']) ? $data['msgID'] : null //要覆盖的消息ID
        ));
		try {
			$reg = $pusher->send();
			if($reg['http_code'] == 200){
				return true;
			}else{
				$this->_error = $Rreg['body'];
				return false;
			}
		} catch (APIConnectionException $e) {
		    // try something here
		    $this->_error = $e;
		    return false;
		} catch (APIRequestException $e) {
		    // try something here
		    $this->_error = $e;
		    return false;
		}
	}
	public function getError(){
		return $this->_error;
	}
}
?>