<?php if (!defined('THINK_PATH')) exit();?><!-- public:header 以下内容 -->
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>网站后台管理中心- Powered by 74cms.com</title>
	<link rel="shortcut icon" href="<?php echo C('qscms_site_dir');?>favicon.ico"/>
	<meta name="author" content="骑士CMS" />
	<meta name="copyright" content="74cms.com" />
	<!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">  -->
	<link href="__ADMINPUBLIC__/css/common.css?v=<?php echo strtotime('today');?>" rel="stylesheet" type="text/css">
	<script src="__ADMINPUBLIC__/js/jquery.min.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.highlight-3.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.vtip-min.js"></script>
	<script src="__ADMINPUBLIC__/js/jquery.modal.dialog.js"></script>
	<script type="text/javascript" src="__ADMINPUBLIC__/js/laydate/laydate.js"></script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="__ADMINPUBLIC__/js/PIE.js"></script>
	<script type="text/javascript">
		(function ($) {
			$.pie = function (name, v) {
				// 如果没有加载 PIE 则直接终止
				if (!PIE) return false;
				// 是否 jQuery 对象或者选择器名称
				var obj = typeof name == 'object' ? name : $(name);
				// 指定运行插件的 IE 浏览器版本
				var version = 9;
				// 未指定则默认使用 ie10 以下全兼容模式
				if (typeof v != 'number' && v < 9) {
					version = v;
				}
				// 可对指定的多个 jQuery 对象进行样式兼容
				if ($.browser.msie && obj.size() > 0) {
					if ($.browser.version * 1 <= version * 1) {
						obj.each(function () {
							PIE.attach(this);
						});
					}
				}
			}
		})(jQuery);
		if ($.browser.msie) {
			$.pie('.pie_about');
		};
	</script>
	<![endif]-->
	<script src="__ADMINPUBLIC__/js/jquery.disappear.tooltip.js"></script>
	<script src="__ADMINPUBLIC__/js/common.js"></script>
	<script>
		var URL = '/index.php/Admin/BusinessSetmeal',
			SELF = '/index.php?m=Admin&amp;c=BusinessSetmeal&amp;a=index',
			ROOT_PATH = '/index.php/Admin',
			APP	 =	 '/index.php';

		var qscms = {
			district_level : "<?php echo C('qscms_category_district_level');?>",
			default_district : "<?php echo C('qscms_default_district');?>"
		};
	</script>
	<?php echo ($synsitegroupunbindmobile); ?>
	<?php echo ($synsitegroupregister); ?>
	<?php echo ($synsitegroupedit); ?>
	<?php echo ($synsitegroup); ?>
</head>
<body>

<!-- public:header 以上内容 -->
<?php if(empty($menu_title)): ?><div class="allpagetop">后台管理中心<strong>/</strong>首页</div>
	<?php else: ?>
	<div class="allpagetop"><?php echo ($menu_title); ?><strong>/</strong><?php echo ($sub_menu_title); ?></div><?php endif; ?>
<div class="mains">
	<div class="h1tit">
		<div class="h1">
            <?php if($sub_menu['pageheader']): echo ($sub_menu["pageheader"]); ?>
                <?php else: ?>
                <!--欢迎登录 <?php echo C('qscms_site_name');?> 管理中心！--><?php endif; ?>
        </div>
        <?php if(!empty($sub_menu['menu'])): ?><div class="topnav">
                <?php if(is_array($sub_menu['menu'])): $i = 0; $__LIST__ = $sub_menu['menu'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><a href="<?php echo U($val['module_name'].'/'.$val['controller_name'].'/'.$val['action_name']); echo ($val["data"]); if($isget and $_GET): echo get_first(); endif; if($_GET['_k_v']): ?>&_k_v=<?php echo (_I($_GET['_k_v'])); endif; ?>" class="<?php echo ($val["class"]); ?>"><?php echo ($val["name"]); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
                <div class="clear"></div>
            </div><?php endif; ?>
		<div class="clear"></div>
	</div>
<div class="toptip">
    <div class="toptit">统计：</div>
    <p>
        <?php if(is_array($setmeal_arr)): $i = 0; $__LIST__ = $setmeal_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; echo ($vo['name']); ?>：<?php echo ($vo['count']); ?>&nbsp;&nbsp;&nbsp;<?php endforeach; endif; else: echo "" ;endif; ?>        
    </p>
    <p>已到期：<?php echo ($count1); ?>&nbsp;&nbsp;&nbsp;未到期：<?php echo ($count2); ?></p>
</div>
<div class="seltpye_y">
    <div class="tit">套餐类型</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['setmeal_id']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['setmeal_cn']) != "")?(_I($_GET['setmeal_cn'])):'不限'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('setmeal_id'=>'','setmeal_cn'=>'不限'));?>">不限</li>
        <?php if(is_array($setmeal)): $i = 0; $__LIST__ = $setmeal;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li url="<?php echo P(array('setmeal_id'=>$key,'setmeal_cn'=>$vo));?>"><?php echo ($vo); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">套餐到期</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['overtime']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['overtime_cn']) != "")?(_I($_GET['overtime_cn'])):'不限'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('overtime'=>'','overtime_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('overtime'=>'3','overtime_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('overtime'=>'7','overtime_cn'=>'一周内'));?>">一周内</li>
        <li url="<?php echo P(array('overtime'=>'30','overtime_cn'=>'一月内'));?>">一月内</li>
        <li url="<?php echo P(array('overtime'=>'180','overtime_cn'=>'半年内'));?>">半年内</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">排序</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['sortby']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['sortby_cn']) != "")?(_I($_GET['sortby_cn'])):'添加时间'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('sortby'=>'starttime','sortby_cn'=>'添加时间'));?>">添加时间</li>
        <li url="<?php echo P(array('sortby'=>'endtime','sortby_cn'=>'到期时间'));?>">到期时间</li>
        <li url="<?php echo P(array('sortby'=>'setmeal_id','sortby_cn'=>'套餐类型'));?>">套餐类型</li>
    </div>
</div>
<div class="clear"></div>

    <div class="list_th">
        <div class="td" style=" width:10%;">
            <label id="chkAll" class="left_padding">
                套餐名称
            </label>
        </div>
        <div class="td center" style=" width:25%;">公司名称</div>
        <div class="td center" style=" width:10%;">用户名</div>
        <div class="td center" style=" width:15%;">开始时间</div>
        <div class="td center" style=" width:10%;">结束时间</div>
        <div class="td center" style=" width:10%;">剩余天数</div>
        <div class="td" style=" width:15%;">操作</div>
        <div class="clear"></div>
    </div>
    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
            <div class="td" style=" width:10%;">
                <div class="left_padding striking">
                    <?php if($vo['setmeal_name']): ?><span <?php if($vo['setmeal_id'] != '1'): ?>style="color: #FF6600"<?php endif; ?>><?php echo ($vo['setmeal_name']); ?></span>
                    <?php else: ?>
                    <span style="color:#FF3300">无套餐</span><?php endif; ?>
                <span class="view setmeal_detail" title="套餐详情" parameter="uid=<?php echo ($vo['uid']); ?>">&nbsp;&nbsp;&nbsp;</span>
                </div>
            </div>
            <div class="td center" style=" width:25%;"><a href="<?php echo ($vo['company_url']); ?>" target="_blank"><?php echo ($vo['companyname']); ?></a> </div>
            <div class="td center" style=" width:10%;"><?php echo (($vo['username'] != "")?($vo['username']):'此会员已删除'); ?></div>
            <div class="td center" style=" width:15%;"><?php if($vo['starttime']): echo date('Y-m-d',$vo['starttime']); else: ?>-<?php endif; ?></div>
            <div class="td center" style=" width:10%;"><?php if(($vo['endtime']) == "0"): ?>无限期<?php else: echo date('Y-m-d',$vo['endtime']); endif; ?></div>
            <div class="td center" style=" width:10%;"><?php echo ($vo['leave_days']); ?></div>
            <div class="td edit" style=" width:15%;">
                <a href="javascript:;" class="J_setmeal_log blue" parameter="uid=<?php echo ($vo['uid']); ?>">套餐日志</a>
                <a href="<?php echo U('edit',array('uid'=>$vo['uid'],'_k_v'=>$vo['id']));?>">编辑</a>
                <a href="javascript:;" class="J_message" parameter="uid=<?php echo ($vo['uid']); ?>">发消息</a>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
    </div>

    <div class="footso">
        <form action="?" method="get">
            <div class="sobox">
                <input type="hidden" name="m" value="<?php echo C('admin_alias');?>">
                <input type="hidden" name="c" value="<?php echo CONTROLLER_NAME;?>">
                <input type="hidden" name="a" value="<?php echo ACTION_NAME;?>">
                <input name="key" type="text" class="sinput" value="<?php echo (_I($_GET['key'])); ?>"/>
                <input name="key_type" id="J_key_type_id" type="hidden" value="<?php echo ((_I($_GET['key_type']) != "")?(_I($_GET['key_type'])):'1'); ?>" />
                <input name="key_type_cn" id="J_key_type_cn" type="hidden" value="<?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'公司名'); ?>"/>
                <input name="" type="submit" value="" class="sobtn"/>
                <div class="sotype" id="J_key_click"><?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'公司名'); ?></div>
                <div class="mlist" id="J_mlist">
                    <ul>
                        <li id="1" title="公司名">公司名</li>
                        <li id="2" title="公司ID">公司ID</li>
                        <li id="3" title="会员名">会员名</li>
                        <li id="4" title="会员ID">会员ID</li>
                        <li id="5" title="地址">地址</li>
                        <li id="6" title="电话">电话</li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="pages"><?php echo ($page); ?></div>

</div>
<!-- public:footer 以下内容 -->
<div class="footer link_blue">
    Powered by <a href="https://www.74cms.com" target="_blank"><span style="color:#009900">74</span><span
        style="color: #FF3300">cms</span></a> v<?php echo C('QSCMS_VERSION');?>
</div>
<div class="layui-floatmenu">
    <div class="J_suggest item" data-url="<?php echo U('Suggest/add');?>"></div>
    <div class="J_suggestList list item" data-url="<?php echo U('Suggest/index');?>">
      <?php if($suggest): ?><span></span><?php endif; ?>
    </div>
  </div>
<script src="__ADMINPUBLIC__/js/layui.js"></script>
<!-- public:footer 以上内容 -->
<script>
  layui.use(['form', 'element'], function(){
  var element = layui.element;
  var form = layui.form;
  var layer = layui.layer;
  var $ = layui.jquery;
  var loading = layer.load(0, {shade: [0.3,'#fff'],time:500});
  $('.J_suggest').click(function(){
    var url = $(this).data('url');
    $.getJSON(url,function(result){
      layer.open({
          title:'反馈',
          type: 1,
          area: ['800px', 'auto'], //宽高
          content: result.data
      });
    });
  });
  $('.J_suggestList').click(function(){
    var url = $(this).data('url');
    var f = $(this).find('span');
    $.getJSON(url,function(result){
      layer.open({
          title:'历史反馈记录',
          type: 1,
          area: ['800px', '690px'], //宽高
          content: result.data
      });
      f.remove();
    });
  });
});
</script>

</body>
<script type="text/javascript">
    $(document).ready(function () {
        $(".J_message").click(function () {
            $('.modal_backdrop').remove();
            $('.modal').remove();
            var qsDialog = $(this).dialog({
                title: '发消息',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/ajax_message');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //审核日志
        $(".J_setmeal_log").click(function () {
            var qsDialog = $(this).dialog({
                title: '套餐日志',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/setmeal_log');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //套餐详情
        $(".setmeal_detail").click(function () {
            var qsDialog = $(this).dialog({
                title: '套餐详情',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/setmeal_detail');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
    });
</script>
</html>