<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>网站后台管理中心- zy拉钩人才系统</title>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <meta name="author" content="zy拉钩人才系统" />
    <meta name="copyright" content="zy拉钩人才系统" />
    <link href="__ADMINPUBLIC__/css/common.css" rel="stylesheet" type="text/css">
    <script src="__ADMINPUBLIC__/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".li").first().addClass("select");
            $(".li").click(function () {
                $(".li").removeClass("select");
                $(this).addClass("select");
                $(this).blur();
            });
        });
    </script>
</head>
<body style="background-color:#2F4050">
<div class="frame_left">
    <div class="tops"></div>
    <?php if(empty($menuid)): ?><a href="<?php echo U('index/panel');?>" target="mainFrame" class="li">
            管理中心
            <div class="linkimg"><img src="__ADMINPUBLIC__/images/menu/home.png" border="0"/></div>
        </a><?php endif; ?>
    <?php if(is_array($menus)): $i = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><a href="<?php echo U($menu['module_name'].'/'.$menu['controller_name'].'/'.$menu['action_name']); echo ($menu["data"]); ?>" target="mainFrame" class="li <?php if($menuid && $key == 0): ?>select<?php endif; ?>" title="<?php echo ($menu["name"]); ?>" <?php if($menu['stat']): ?>stat="<?php echo ($menu["stat"]); ?>"<?php endif; ?>>
            <span class="J_menu_name"><?php echo ($menu["name"]); ?></span>
            <div class="linkimg"><img src="__ADMINPUBLIC__/images/menu/<?php if($menu['img'] == ''): ?>empty.png<?php else: echo ($menu['img']); endif; ?>" border="0"/></div>
        </a><?php endforeach; endif; else: echo "" ;endif; ?>
    <?php if(empty($menuid)): ?><a href="<?php echo U('index/logout');?>" target="_top" class="li">
            退出登录
            <div class="linkimg"><img src="__ADMINPUBLIC__/images/menu/exit.png" border="0"/></div>
        </a><?php endif; ?>
</div>
</body>
<script type="text/javascript">
    function refresh_affair(){
        var affair = $('.frame_left a[stat]').map(function(){
            return $(this).attr('stat');
        }).get();
        $.post("<?php echo U('Ajax/affair_stat');?>",{affair:affair},function(result){
            if(result.status == 1){
                result.data = result.data || {};
                $('.frame_left a[stat]').each(function(){
                    var h = result.data[$(this).attr('stat')];
                    $(this).find('.J_menu_name').find("span").remove();
                    //h = h ? '<span style="color: #FF0000;">('+h+')</span>' : '';
                    //$(this).find('.J_menu_name').append(h);
                    //$(this).html($(this).attr('title')+h);
                    h = h ? '<div class="count"></div>' : '';
                    $(this).append(h);
                });
            }
        },'json');
    }
    refresh_affair();
</script>
</html>