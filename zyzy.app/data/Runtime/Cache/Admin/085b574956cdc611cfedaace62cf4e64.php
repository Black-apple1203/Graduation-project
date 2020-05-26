<?php if (!defined('THINK_PATH')) exit();?>﻿<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>网站后台管理中心- zy拉钩人才系统</title>
    <link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
</head>
<frameset rows="60,*" frameborder="no" border="0" framespacing="0">
    <frame src="<?php echo U('index/top_menu');?>" name="topFrame" id="topFrame" scrolling="no" frameborder="NO" border="0"
           framespacing="0">
    <frameset cols="200,*" name="bodyFrame" id="bodyFrame" frameborder="no" border="0" framespacing="0">
        <frame src="<?php echo U('index/left_menu');?>" name="leftFrame" frameborder="no" scrolling="auto" noresize id="leftFrame">
        <frame src="<?php echo U('index/panel');?>" name="mainFrame" frameborder="no" scrolling="auto" noresize id="mainFrame">
    </frameset>
</frameset>
<noframes>
    <body>你的浏览器不支持框架</body>
</noframes>
</html>