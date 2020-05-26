<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>网站后台管理中心- zy拉钩人才系统</title>
    <link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
    <meta name="author" content="骑士CMS" />
    <meta name="copyright" content="74cms.com" />
    <link href="__ADMINPUBLIC__/css/common.css" rel="stylesheet" type="text/css">
    <script src="__ADMINPUBLIC__/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".frame_top .navmenu a").click(function () {
                $(".frame_top .navmenu a").removeClass("select");
                $(this).addClass("select");
                $(this).blur();
                if (!$(this).hasClass('for_more')) {
                    window.parent.frames["leftFrame"].location.href = $(this).attr('frame-url');
                }
            });
        });
    </script>
</head>
<body>

<div class="frame_top">
    <div class="logo">
        <img src="__ADMINPUBLIC__/images/admin_logo_in.png" border="0"/>
    </div>
    <div class="navmenu">
        <a href="<?php echo U('index/panel');?>" class="select" target="mainFrame" id="index" data-id="0" frame-url="<?php echo U('Index/left_menu');?>&menuid=0">首页</a>
        <?php if(is_array($menus)): $i = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><a href="<?php echo U($menu['module_name'].'/'.$menu['controller_name'].'/'.$menu['action_name'],array('menuid'=>$menu['id'],'child'=>1)); echo ($menu["data"]); ?>" target="mainFrame" frame-url="<?php echo U('Index/left_menu');?>&menuid=<?php echo ($menu["id"]); ?>" data-id="<?php echo ($menu["id"]); ?>" title="<?php echo ($menu["name"]); ?>" <?php if($menu['stat']): ?>stat="<?php echo ($menu["stat"]); ?>"<?php endif; ?>><?php echo ($menu["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
        <div class="clear"></div>
    </div>
    <div class="links">
        <a href="<?php echo C('qscms_site_domain'); echo C('qscms_site_dir');?>" target="_blank" class="home" title="网站首页"></a>
        <a href="<?php echo U('index/logout');?>" target="_top" class="logout" title="退出登录"></a>
        <a href="http://ask.74cms.com/" target="_blank" class="qscms" title="官方问答"></a>
        <div class="clear"></div>
    </div>
    <div class="adminname">
        <div class="unamestr"><?php echo ($visitor["username"]); ?><span style=" padding-left:10px; color:#009900">(<?php echo ($visitor["role_cn"]); ?>)</span></div>
        <div class="useravatar"><img src="__ADMINPUBLIC__/images/avatar.jpg" border="0"/></div>
        <div class="clear"></div>
    </div>
</div>
</body>
<script type="text/javascript">
    function refresh_affair(){
        var affair = $('.navmenu a[stat]').map(function(){
            return $(this).attr('stat');
        }).get();
        $.post("<?php echo U('Ajax/affair');?>",{affair:affair},function(result){
            if(result.status == 1){
                result.data = result.data || {};
                $('.navmenu a[stat]').each(function(){
                    var h = result.data[$(this).attr('stat')];
                    h = h ? '<div class="count"></div>' : '';
                    $(this).html($(this).attr('title')+h);
                });
            }
        },'json');
    }
    refresh_affair();
</script>
</html>