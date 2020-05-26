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
		var URL = '/index.php/Admin/Report',
			SELF = '/index.php?m=Admin&amp;c=Report&amp;a=index',
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
<div class="seltpye_y">
    <div class="tit">举报类型</div>
    <div class="ct">
        <div class="txt select"><?php echo ((_I($_GET['type_cn']) != "")?(_I($_GET['type_cn'])):"职位"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('type'=>'1','type_cn'=>'职位'));?>">职位<?php if($count[0]): ?><span style="color:#ff0000;">(<?php echo ($count[0]); ?>)</span><?php endif; ?></li>
        <li url="<?php echo P(array('type'=>'2','type_cn'=>'简历'));?>">简历<?php if($count[1]): ?><span style="color:#ff0000;">(<?php echo ($count[1]); ?>)</span><?php endif; ?></li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">举报原因</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['report_type'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['report_type_cn']) != "")?(_I($_GET['report_type_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('report_type'=>'','report_type_cn'=>'不限'));?>">不限</li>
        <?php if(is_array($type_arr)): $i = 0; $__LIST__ = $type_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li url="<?php echo P(array('report_type'=>$key,'report_type_cn'=>$vo));?>" title="<?php echo ($vo); ?>"><?php echo ($vo); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">核实情况</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['audit'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['audit_cn']) != "")?(_I($_GET['audit_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('audit'=>'','audit_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('audit'=>'1','audit_cn'=>'未审核'));?>">未核实</li>
        <li url="<?php echo P(array('audit'=>'2','audit_cn'=>'属实'));?>">属实</li>
        <li url="<?php echo P(array('audit'=>'3','audit_cn'=>'不属实'));?>">不属实</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">举报时间</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['settr'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['settr_cn']) != "")?(_I($_GET['settr_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('settr'=>'','settr_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('settr'=>'3','settr_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('settr'=>'7','settr_cn'=>'一周内'));?>">一周内</li>
        <li url="<?php echo P(array('settr'=>'30','settr_cn'=>'一月内'));?>">一月内</li>
        <li url="<?php echo P(array('settr'=>'180','settr_cn'=>'半年内'));?>">半年内</li>
        <li url="<?php echo P(array('settr'=>'360','settr_cn'=>'一年内'));?>">一年内</li>
    </div>
</div>
<div class="clear"></div>

<form id="form1" name="form1" method="post" action="<?php echo U('report_audit');?>">
    <input type="hidden" name="type" value="<?php echo ((_I($_GET['type']) != "")?(_I($_GET['type'])):1); ?>">
    <div class="list_th">
        <div class="td" style=" width:15%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>
                <?php if($_GET['type']== 2): ?>投诉简历<?php else: ?>投诉职位<?php endif; ?>
            </label>
        </div>
        <div class="td" style=" width:5%;">核实情况</div>
        <div class="td" style=" width:15%;">举报原因</div>
        <div class="td" style=" width:30%;">举报内容</div>
        <div class="td" style=" width:10%;">举报者</div>
        <div class="td" style=" width:10%;">联系电话</div>
        <div class="td center" style=" width:10%;">举报时间</div>
        <div class="td" style=" width:5%;">操作</div>
        <div class="clear"></div>
    </div>

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_blue">
            <div class="td" style=" width:15%;">
                <div class="left_padding striking">
                    <input name="id[]" type="checkbox" id="id" value="<?php echo ($vo['id']); ?>"/>
                    <?php if($_GET['type']== 2): ?><a href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$vo['resume_id']));?>" target="_blank"><?php echo ($vo["resume_realname"]); ?></a>
                        <?php else: ?>
                        <a href="<?php echo url_rewrite('QS_jobsshow',array('id'=>$vo['jobs_id']));?>" target="_blank"><?php echo ($vo["jobs_name"]); ?></a><?php endif; ?>
                </div>
            </div>
            <div class="td" style=" width:5%;">
                <?php if($vo['audit'] == 2): ?>属实
                    <?php elseif($vo['audit'] == 3): ?>不属实
                    <?php else: ?><span style="color: #ff0000;">未核实</span><?php endif; ?>
            </div>
            <div class="td" style=" width:15%;"><?php echo (($type_arr[$vo['report_type']] != "")?($type_arr[$vo['report_type']]):'-'); ?></div>
            <div class="td vtip" style=" width:30%;" title="<?php echo str_replace(array('&lt;','&gt;','/','<','>','script'),'',nl2br($vo['content']));?>"><?php echo (($vo["content"] != "")?($vo["content"]):'-'); ?></div>
            <div class="td" style=" width:10%;"><?php echo (($vo["username"] != "")?($vo["username"]):'-'); ?></div>
            <div class="td" style=" width:10%;"><?php echo (($vo["telephone"] != "")?($vo["telephone"]):'-'); ?></div>
            <div class="td center" style=" width:10%;"><?php echo admin_date($vo['addtime']);?></div>
            <div class="td edit" style=" width:5%;">
                <a href="<?php echo U('delete',array('id'=>$vo['id'],'type'=>I('get.type',1)));?>" class="gray" onclick="return confirm('你确定要删除吗？')">删除</a>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
</form>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
        <input type="button" class="admin_submit" id="ButAudit" value="审核"/>
    </div>
    <div class="footso"></div>
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
        //审核
        $("#ButAudit").click(function () {
            var data = $("form[name=form1]").serialize();
            if(data.length == 0){
                disapperTooltip('remind','请选择举报记录！');
            } else {
                var qsDialog = $(this).dialog({
                    title: '审核举报',
                    loading: true,
                    footer : false
                });
                var url = "<?php echo U('Ajax/report_audit');?>";
                $.post(url, data, function (result) {
                    if(result.status == 1){
                        qsDialog.setContent(result.data);
                    } else {
                        qsDialog.hide();
                        disapperTooltip('remind',result.msg);
                    }
                });
            }
        });
    });
</script>
</html>