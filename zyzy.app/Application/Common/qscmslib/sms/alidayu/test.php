<?php
	include "TopSdk.php";
	date_default_timezone_set('Asia/Shanghai'); 

	// $httpdns = new HttpdnsGetRequest;
	// $client = new ClusterTopClient("23316223","c31c98200e560f85589584e674a6d28a");
	// $client->gatewayUrl = "http://api.daily.taobao.net/router/rest";
	// var_dump($client->execute($httpdns,"6100e23657fb0b2d0c78568e55a3031134be9a3a5d4b3a365753805"));

$c = new TopClient;
$c->appkey = '23316223';
$c->secretKey = 'c31c98200e560f85589584e674a6d28a';
$req = new AlibabaAliqinFcSmsNumSendRequest;
$req->setExtend("123456");
$req->setSmsType("normal");
$req->setSmsFreeSignName("阿里大鱼");
$req->setSmsParam("{\"code\":\"1234\",\"product\":\"阿里大鱼\"}");
$req->setRecNum("13835466483");
$req->setSmsTemplateCode("SMS_5059650");
$resp = $c->execute($req);

?>