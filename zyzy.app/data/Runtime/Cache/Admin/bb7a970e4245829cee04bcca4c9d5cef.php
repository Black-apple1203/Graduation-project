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
		var URL = '/index.php/Admin/CompanyMembers',
			SELF = '/index.php?m=Admin&amp;c=CompanyMembers&amp;a=index',
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
<div class="seltpye_x">
    <div class="left">注册时间</div>
    <div class="right">
        <a href="<?php echo P(array('settr'=>''));?>" <?php if(($_GET['settr']) == ""): ?>class="select"<?php endif; ?>>不限</a>
        <a href="<?php echo P(array('settr'=>'3'));?>" <?php if(($_GET['settr']) == "3"): ?>class="select"<?php endif; ?>>三天内</a>
        <a href="<?php echo P(array('settr'=>'7'));?>" <?php if(($_GET['settr']) == "7"): ?>class="select"<?php endif; ?>>一周内</a>
        <a href="<?php echo P(array('settr'=>'30'));?>" <?php if(($_GET['settr']) == "30"): ?>class="select"<?php endif; ?>>一月内</a>
        <a href="<?php echo P(array('settr'=>'180'));?>" <?php if(($_GET['settr']) == "180"): ?>class="select"<?php endif; ?>>半年内</a>
        <a href="<?php echo P(array('settr'=>'360'));?>" <?php if(($_GET['settr']) == "360"): ?>class="select"<?php endif; ?>>一年内</a>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="seltpye_x">
    <div class="left">微信绑定状态</div>
    <div class="right">
        <a href="<?php echo P(array('is_bind'=>''));?>" <?php if(($_GET['is_bind']) == ""): ?>class="select"<?php endif; ?>>不限</a>
        <a href="<?php echo P(array('is_bind'=>'1'));?>" <?php if(($_GET['is_bind']) == "1"): ?>class="select"<?php endif; ?>>绑定</a>
        <a href="<?php echo P(array('is_bind'=>'0'));?>" <?php if(($_GET['is_bind']) == "0"): ?>class="select"<?php endif; ?>>未绑定</a>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<div class="seltpye_x">
    <div class="left">数据来源</div>
    <div class="right">
        <a href="<?php echo P(array('source'=>''));?>" <?php if(($_GET['source']) == ""): ?>class="select"<?php endif; ?>>不限</a>
        <a href="<?php echo P(array('source'=>'1'));?>" <?php if(($_GET['source']) == "1"): ?>class="select"<?php endif; ?>>网页端</a>
        <a href="<?php echo P(array('source'=>'2'));?>" <?php if(($_GET['source']) == "2"): ?>class="select"<?php endif; ?>>手机端</a>
        <a href="<?php echo P(array('source'=>'3'));?>" <?php if(($_GET['source']) == "3"): ?>class="select"<?php endif; ?>>小程序</a>
        <a href="<?php echo P(array('source'=>'4'));?>" <?php if(($_GET['source']) == "4"): ?>class="select"<?php endif; ?>>App端</a>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<form id="form1" name="form1" method="post" action="<?php echo U('delete');?>">
    <div class="list_th">
        <div class="td" style=" width:27%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>用户名
            </label>
        </div>
        <div class="td" style=" width:15%;">企业信息</div>
        <div class="td center" style=" width:10%;">手机</div>
        <div class="td center" style=" width:10%;">email</div>
        <div class="td center" style=" width:8%;">注册时间</div>
        <div class="td center" style=" width:10%;">最后登录时间</div>
        <div class="td" style=" width:20%;">操作</div>
        <div class="clear"></div>
    </div>

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
            <div class="td" style=" width:27%;">
                <div class="left_padding striking">
                    <input name="tuid[]" type="checkbox" id="id" value="<?php echo ($vo['uid']); ?>"/><?php echo ($vo['username']); ?><span style="color: #999999">(uid:<?php echo ($vo['uid']); ?>)</span>
                    <?php if($vo['is_bind']): ?><span class="weixin_bind">&nbsp;&nbsp;&nbsp;&nbsp;</span><?php endif; ?>
                </div>
            </div>
            <div class="td" style=" width:15%;">
                <?php if($vo['companyname']): ?><a href="<?php echo url_rewrite('QS_companyshow',array('id'=>$vo['company_id']));?>" target="_blank"><?php echo cut_str($vo['companyname'],15,0,'..');?></a>
                <?php else: ?>
                    <span style="color: #999999">未完善企业资料</span><?php endif; ?>
            </div>
            <div class="td center" style=" width:10%;">
                <span><?php echo (($vo['mobile'] != "")?($vo['mobile']):"未填写"); ?></span>
            </div>
            <div class="td center" style=" width: 10%;">
                <span><?php echo (($vo['email'] != "")?($vo['email']):"未填写"); ?></span>
            </div>
            <div class="td center" style=" width:8%;"><?php echo admin_date($vo['reg_time']);?></div>
            <div class="td center" style=" width:10%;">
                <?php if($vo['last_login_time']): echo admin_date($vo['last_login_time']); else: ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;从未登录<?php endif; ?>
                <span class="view login_log" title="最新5次登录记录" parameter="id=<?php echo ($vo['uid']); ?>">&nbsp;&nbsp;&nbsp;</span>
            </div>
            <div class="td edit" style=" width:20%;">
                <a href="javascript:void(0);" class="business" parameter="uid=<?php echo ($vo['uid']); ?>&utype=1" hideFocus="true">业务</a>
                <a href="javascript:void(0);" class="blue company_log" parameter="uid=<?php echo ($vo['uid']); ?>">日志</a>
                <?php if($apply['Analyze']): ?><a href="<?php echo U('Analyze/Admin/analyze_list_com',array('uid'=>$vo['uid'],'_k_v'=>$vo['id']));?>">统计</a><?php endif; ?>
                <a href="<?php echo U('Company/edit_company',array('uid'=>$vo['uid'],'_k_v'=>$vo['uid']));?>">编辑</a>
                <a href="javascript:;" class="J_message" parameter="uid=<?php echo ($vo['uid']); ?>">发消息</a>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
</form>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
        <input type="button" class="admin_submit" id="ButAdd" value="添加会员" onclick="window.location.href='<?php echo U('Company/add');?>'"/>
        <input type="button" class="admin_submit" id="ButDel" value="删除会员"/>
    </div>

    <div class="footso">
        <form action="?" method="get">
            <div class="sobox">
                <input type="hidden" name="m" value="<?php echo C('admin_alias');?>">
                <input type="hidden" name="c" value="<?php echo CONTROLLER_NAME;?>">
                <input type="hidden" name="a" value="<?php echo ACTION_NAME;?>">
                <input name="key" type="text" class="sinput" value="<?php echo (_I($_GET['key'])); ?>"/>
                <input name="key_type" id="J_key_type_id" type="hidden" value="<?php echo ((_I($_GET['key_type']) != "")?(_I($_GET['key_type'])):'1'); ?>" />
                <input name="key_type_cn" id="J_key_type_cn" type="hidden" value="<?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'用户名'); ?>"/>
                <input name="" type="submit" value="" class="sobtn"/>
                <div class="sotype" id="J_key_click"><?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'用户名'); ?></div>
                <div class="mlist" id="J_mlist">
                    <ul>
                        <li id="1" title="用户名">用户名</li>
                        <li id="2" title="UID">UID</li>
                        <li id="3" title="email">email</li>
                        <li id="4" title="手机号">手机号</li>
						<li id="5" title="公司名">公司名</li><!--  新增企业名称搜素-->
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
        //业务
        $(".business").click(function () {
            var qsDialog = $(this).dialog({
                title: '业务',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/business');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //审核日志
        $(".login_log").click(function () {
            var qsDialog = $(this).dialog({
                title: '登录日志',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/login_log');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //会员日志
        $(".company_log").click(function () {
            var qsDialog = $(this).dialog({
                title: '会员日志',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/company_log');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //点击批量删除
        $("#ButDel").click(function () {
            if (confirm('删除会员，该会员下企业、职位、简历信息将同步删除')) {
                $("form[name=form1]").attr("action", "<?php echo U('delete');?>");
                $("form[name=form1]").submit();
            }
        });
        /*//批量删除
        $("#ButDel").click(function () {
            var data = $("form[name=form1]").serialize();
            if(data.length == 0){
                disapperTooltip('remind','请选择会员！');
            } else {
                var qsDialog = $(this).dialog({
                    title: '删除会员',
                    loading: true,
                    footer : false
                });
                var url = "<?php echo U('Ajax/delete_company_members');?>";
                $.post(url, data, function (result) {
                    if(result.status == 1){
                        qsDialog.setContent(result.data);
                    } else {
                        qsDialog.hide();
                        disapperTooltip('remind',result.msg);
                    }
                });
            }
        })*/;
    });
</script>
</html>