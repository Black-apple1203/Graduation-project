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
		var URL = '/index.php/Admin/Personal',
			SELF = '/index.php?m=Admin&amp;c=Personal&amp;a=index&amp;menu_id=171&amp;sub_menu_id=172',
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
    <div class="toptit">提示：</div>
    <p>可见简历是指：审核通过、审核中等能正常显示的简历。</p>
    <p>不可见简历指：审核未通过等网站前台不显示的简历。</p>
</div>
<div class="seltpye_y">
    <div class="tit">可见状态</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['tabletype'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['tabletype_cn']) != "")?(_I($_GET['tabletype_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('tabletype'=>'0','tabletype_cn'=>'不限'));?>">不限<span>(<?php echo ($count[0]); ?>)</span></li>
        <li url="<?php echo P(array('tabletype'=>'1','tabletype_cn'=>'可见简历'));?>">可见简历<span>(<?php echo ($count[1]); ?>)</span></li>
        <li url="<?php echo P(array('tabletype'=>'2','tabletype_cn'=>'非可见简历'));?>">非可见简历<span>(<?php echo ($count[2]); ?>)</span></li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">审核状态</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['audit'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['audit_cn']) != "")?(_I($_GET['audit_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('audit'=>'','audit_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('audit'=>'2','audit_cn'=>'等待审核'));?>">等待审核<?php if($count[4] > 0): ?><span style="color:#FF0000">(<?php echo ($count[4]); ?>)</span><?php endif; ?></li>
        <li url="<?php echo P(array('audit'=>'1','audit_cn'=>'审核通过'));?>">审核通过<span>(<?php echo ($count[3]); ?>)</span></li>
        <?php if($_GET['tabletype']!= 1): ?><li url="<?php echo P(array('audit'=>'3','audit_cn'=>'审核未通过'));?>" title="审核未通过">审核未通过<span>(<?php echo ($count[5]); ?>)</span></li><?php endif; ?>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">简历照片</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['photos'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['photos_cn']) != "")?(_I($_GET['photos_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('photos'=>'','photos_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('photos'=>'1','photos_cn'=>'有'));?>">有</li>
        <li url="<?php echo P(array('photos'=>'2','photos_cn'=>'无'));?>">无</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">创建时间</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['addtimesettr'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['addtimesettr_cn']) != "")?(_I($_GET['addtimesettr_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('addtimesettr'=>'','addtimesettr_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('addtimesettr'=>'3','addtimesettr_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('addtimesettr'=>'7','addtimesettr_cn'=>'一周内'));?>">一周内</li>
        <li url="<?php echo P(array('addtimesettr'=>'30','addtimesettr_cn'=>'一月内'));?>">一月内</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">刷新时间</div>
    <div class="ct">
        <div class="txt <?php if(!empty($_GET['settr'])): ?>select<?php endif; ?>"><?php echo ((_I($_GET['settr_cn']) != "")?(_I($_GET['settr_cn'])):"不限"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('settr'=>'','settr_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('settr'=>'3','settr_cn'=>'三天内'));?>">三天内</li>
        <li url="<?php echo P(array('settr'=>'7','settr_cn'=>'一周内'));?>">一周内</li>
        <li url="<?php echo P(array('settr'=>'30','settr_cn'=>'一月内'));?>">一月内</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">排序方式</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['orderby']!= '') AND ($_GET['orderby']!= 'addtime')): ?>select<?php endif; ?>"><?php echo ((_I($_GET['orderby_cn']) != "")?(_I($_GET['orderby_cn'])):"发布时间"); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('orderby'=>'addtime','orderby_cn'=>'创建时间'));?>">创建时间</li>
        <li url="<?php echo P(array('orderby'=>'refreshtime','orderby_cn'=>'刷新时间'));?>">刷新时间</li>
    </div>
</div>
<div class="clear"></div>

<form id="form1" name="form1" method="post" action="<?php echo U('set_audit');?>">
    <div class="list_th">
        <div class="td" style=" width:17%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>姓名
            </label>
        </div>
        <div class="td" style=" width:18%;">基本信息</div>
        <div class="td" style=" width:8%;">简历完整度</div>
        <div class="td center" style=" width:8%;">审核状态&nbsp;&nbsp;&nbsp;</div>
        <div class="td center" style=" width:5%;">公开</div>
        <div class="td center" style=" width:8%;">创建时间</div>
        <div class="td center" style=" width:8%;">刷新时间</div>
        <div class="td center" style=" width:5%;">高级人才</div>
        <div class="td" style=" width:20%;">操作</div>
        <div class="clear"></div>
    </div>

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
            <div class="td" style=" width:17%;">
                <div class="left_padding striking">
                    <input name="id[]" type="checkbox" id="id" value="<?php echo ($vo['id']); ?>"/>
                    <a href="<?php echo ($vo['resume_url']); ?>" target="_blank"><?php echo ($vo['fullname']); ?></a>
                    <?php if($vo['photo_img'] != ''): ?>&nbsp;<span class="vtip" title="<img src='<?php echo attach($vo['photo_img'],'avatar');?>' border=0 align=absmiddle width=120 height=120>">
                            <img class="avatar small" src="<?php echo attach($vo['photo_img'],'avatar');?>" align="absmiddle">
                        </span><?php endif; ?>
                </div>
            </div>
            <div class="td" style=" width:18%;">
                <?php if(!empty($vo["birthdate"])): echo date('Y')-$vo['birthdate'];?>岁 <?php else: ?>未填写<?php endif; ?>
                <?php if(!empty($vo["sex_cn"])): ?>/ <?php echo ($vo['sex_cn']); endif; ?>
                <?php if(!empty($vo["education_cn"])): ?>/ <?php echo ($vo['education_cn']); endif; ?>
                <?php if(!empty($vo["experience_cn"])): ?>/ <?php echo ($vo['experience_cn']); endif; ?>
            </div>
            <div class="td" style=" width:8%;">
                <div style="width:100px; background-color:#CCCCCC; position:relative; margin-top: 20px;" title="完整度:<?php echo ($vo['complete_percent']); ?>%">
                    <div style="background-color: #99CC00; height:16px; width:<?php echo ($vo['complete_percent']); ?>%;"></div>
                    <div style="position:absolute; top:0; left: 40%; font-size:10px; width: 100px; height: 16px; line-height: 16px;"><?php echo ($vo['complete_percent']); ?>%</div>
                </div>
            </div>
            <div class="td center" style=" width:8%;">
                <?php if($vo['audit'] == 1): ?><span style="color: #009900">审核通过</span>
                    <?php elseif($vo['audit'] == 2): ?>
                    <span style="color:#FF6600">等待审核</span>
                    <?php elseif($vo['audit'] == 3): ?>
                    <span style="color:#666666">审核未通过</span><?php endif; ?>
                <span class="view resume_audit_log" title="查看日志" parameter="id=<?php echo ($vo['id']); ?>">&nbsp;&nbsp;&nbsp;</span>
            </div>
            <div class="td center" style=" width:5%;">
                <?php if($vo['display'] == '1'): ?>公开<?php else: ?>保密<?php endif; ?>
            </div>
            <div class="td center" style=" width:8%;"><?php echo admin_date($vo['addtime']);?></div>
            <div class="td center" style=" width:8%;"><?php echo admin_date($vo['refreshtime']);?></div>
            <div class="td center" style=" width:5%;"><?php if(($vo['talent']) == "1"): ?><span class="font_green">高级</span><?php else: ?>普通<?php endif; ?></div>
            <div class="td edit" style=" width:20%;">
                <a href="javascript:void(0);" class="business" parameter="uid=<?php echo ($vo['uid']); ?>" hideFocus="true">业务</a>
                <a href="javascript:void(0);" class="blue resume_log" parameter="uid=<?php echo ($vo['uid']); ?>">日志</a>
                <?php if($apply['Analyze']): ?><a href="<?php echo U('Analyze/Admin/analyze_list_per',array('uid'=>$vo['uid'],'utype'=>2,'_k_v'=>$vo['id']));?>">统计</a><?php endif; ?>
                <a href="javascript:void(0);" class="comment <?php if($vo['comment_content']): ?>vtip<?php endif; ?>" <?php if($vo['comment_content']): ?>title="<?php echo ($vo['comment_content']); ?>"<?php endif; ?> parameter="id=<?php echo ($vo['id']); ?>">点评</a>
                <a href="javascript:;" class="J_message" parameter="uid=<?php echo ($vo['uid']); ?>">发消息</a>
                <a href="<?php echo U('resume_delete',array('id'=>$vo['id']));?>" onClick="return confirm('你确定要删除该简历吗？')" class="gray">删除</a>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
</form>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
        <input type="button" class="admin_submit" id="ButAudit" value="审核简历"/>
        <input type="button" class="admin_submit" id="ButRefresh" value="刷新"/>
        <input type="button" class="admin_submit" id="ButDel" value="删除"/>
        <?php if($apply['Export']): ?><input type="button" class="admin_submit" id="ExPort" value="导出"/>
            <input type="button" class="admin_submit" id="ExPort_s" value="批量导出"/><?php endif; ?>
        <?php if(!empty($apply['Resumeimport'])): ?><input type="button" class="admin_submit xianshi" id="ButImportresume" value="导入简历"/>
            <input type="button" class="admin_submit xianshi" id="excelmodel" value="下载简历模板" onclick="javascript:location.href='<?php echo attach('excelmodel.xls','resumeimport');?>'"/><?php endif; ?>
    </div>

    <div class="footso">
        <form action="?" method="get">
            <div class="sobox">
                <input type="hidden" name="m" value="<?php echo C('admin_alias');?>">
                <input type="hidden" name="c" value="<?php echo CONTROLLER_NAME;?>">
                <input type="hidden" name="a" value="<?php echo ACTION_NAME;?>">
                <input name="key" type="text" class="sinput" value="<?php echo (_I($_GET['key'])); ?>"/>
                <input name="key_type" id="J_key_type_id" type="hidden" value="<?php echo ((_I($_GET['key_type']) != "")?(_I($_GET['key_type'])):'1'); ?>" />
                <input name="key_type_cn" id="J_key_type_cn" type="hidden" value="<?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'姓名'); ?>"/>
                <input name="" type="submit" value="" class="sobtn"/>
                <div class="sotype" id="J_key_click"><?php echo ((_I($_GET['key_type_cn']) != "")?(_I($_GET['key_type_cn'])):'姓名'); ?></div>
                <div class="mlist" id="J_mlist">
                    <ul>
                        <li id="1" title="姓名">姓名</li>
                        <li id="2" title="简历ID">简历ID</li>
                        <li id="3" title="会员ID">会员ID</li>
                        <li id="4" title="电话">电话</li>
                        <!--<li id="5" title="QQ">QQ</li>-->
                        <li id="6" title="地址">地址</li>
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
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.listitem.js"></script>
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.dropdown.js"></script>
<script type="text/javascript" src="__ADMINPUBLIC__/js/jquery.entrustinfotip-min.js"></script>
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
        $(".resume_log").click(function () {
            var qsDialog = $(this).dialog({
                title: '简历日志',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/resume_log');?>&" + param;
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
        <?php if(!empty($apply['Resumeimport'])): ?>//导入简历
		$("#ButImportresume").click(function () {
			var qsDialog = $(this).dialog({
				title: '导入简历',
				loading: true,
				footer : false
			});
			var url = "<?php echo U('Ajax/resume_import');?>";
			$.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
		});<?php endif; ?>
        //简历审核日志
        $(".resume_audit_log").click(function () {
            var qsDialog = $(this).dialog({
                title: '审核日志',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/resume_audit_log');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //点评
        $(".comment").click(function () {
            var qsDialog = $(this).dialog({
                title: '人才点评',
                loading: true,
                footer : false
            });
            var param = $(this).attr('parameter');
            var url = "<?php echo U('Ajax/comment_resume');?>&" + param;
            $.getJSON(url, function (result) {
                qsDialog.setContent(result.data);
            });
        });
        //审核简历
        $("#ButAudit").click(function () {
            var data = $("form[name=form1]").serialize();
            if(data.length == 0){
                disapperTooltip('remind','请选择简历！');
            } else {
                var qsDialog = $(this).dialog({
                    title: '审核简历',
                    loading: true,
                    footer : false
                });
                var url = "<?php echo U('Ajax/resumes_audit');?>";
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
        //点击批量删除
        $("#ButDel").click(function () {
            if (confirm('你确定要删除吗？')) {
                $("form[name=form1]").attr("action", "<?php echo U('resume_delete');?>");
                $("form[name=form1]").submit();
            }
        });
        //点击批量刷新
        $("#ButRefresh").click(function () {
            $("form[name=form1]").attr("action", "<?php echo U('refresh');?>");
            $("form[name=form1]").submit();
        });
        <?php if($apply['Export']): ?>//点击批量导出
            $("#ExPort").click(function () {
                var data = $("form[name=form1]").serialize();
                if(data.length == 0){
                    disapperTooltip('remind','请选择简历！');
                    qsDialog.hide();
                }
                $("form[name=form1]").attr("action", "<?php echo U('Export/Admin/export_personal');?>");
                $("form[name=form1]").submit();
            });
            $("#ExPort_s").click(function () {
                var qsDialog = $(this).dialog({
                    title: '批量导出',
                    loading: true,
                    footer : false
                });
                var url = "<?php echo U('Export/Admin/ajax_export_personal');?>";
                $.getJSON(url, function (result) {
                    qsDialog.setContent(result.data);
                });
            });<?php endif; ?>
    });
</script>
</html>