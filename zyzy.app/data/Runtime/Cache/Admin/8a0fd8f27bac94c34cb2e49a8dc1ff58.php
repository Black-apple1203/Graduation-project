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
		var URL = '/index.php/Admin/Order',
			SELF = '/index.php?m=Admin&amp;c=Order&amp;a=index_per',
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
    <div class="tit">完成状态</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['is_paid']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['is_paid_cn']) != "")?(_I($_GET['is_paid_cn'])):'不限'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('is_paid'=>'','is_paid_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('is_paid'=>'2','is_paid_cn'=>'已完成'));?>">已完成</li>
        <li url="<?php echo P(array('is_paid'=>'1','is_paid_cn'=>'待付款'));?>">待付款</li>
        <li url="<?php echo P(array('is_paid'=>'3','is_paid_cn'=>'已取消'));?>">已取消</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">付款方式</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['payment']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['payment_cn']) != "")?(_I($_GET['payment_cn'])):'不限'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('payment'=>'','payment_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('payment'=>'points','payment_cn'=>C('qscms_points_byname').'支付'));?>"><?php echo C('qscms_points_byname');?>支付</li>
        <?php if(is_array($payment_list)): $i = 0; $__LIST__ = $payment_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li url="<?php echo P(array('payment'=>$vo['typename'],'payment_cn'=>$vo['byname']));?>"><?php echo ($vo['byname']); if(($vo['typename'] == 'remittance') and ($count > 0)): ?><span>(<?php echo ($count); ?>)</span><?php endif; ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">订单类型</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['type']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['type_cn']) != "")?(_I($_GET['type_cn'])):'不限'); ?></div>
    </div>
    <div class="downlist">
        <li url="<?php echo P(array('type'=>'','type_cn'=>'不限'));?>">不限</li>
        <li url="<?php echo P(array('type'=>'3','type_cn'=>'简历置顶'));?>">简历置顶</li>
        <li url="<?php echo P(array('type'=>'4','type_cn'=>'醒目标签'));?>">醒目标签</li>
        <li url="<?php echo P(array('type'=>'5','type_cn'=>'简历模板'));?>">简历模板</li>
    </div>
</div>
<div class="seltpye_y">
    <div class="tit">添加时间</div>
    <div class="ct">
        <div class="txt <?php if(($_GET['settr']) != ""): ?>select<?php endif; ?>"><?php echo ((_I($_GET['settr_cn']) != "")?(_I($_GET['settr_cn'])):'不限'); ?></div>
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

<form id="form1" name="form1" method="post" action="<?php echo U('order_cancel_per');?>">
    <input type="hidden" name="_k_v" value="<?php echo (_I($_GET['_k_v'])); ?>">
    <div class="list_th">
        <div class="td" style=" width:10%;">
            <label id="chkAll" class="left_padding">
                <input type="checkbox" name="chkAll" id="chk" title="全选/反选"/>全选
            </label>
        </div>
        <div class="td" style=" width:10%;">金额</div>
        <div class="td" style=" width:27%;">说明</div>
        <div class="td center" style=" width:12%;">单号</div>
        <div class="td center" style=" width:10%;">申请会员</div>
        <div class="td center" style=" width:9%;">申请时间</div>
        <div class="td center" style=" width:9%;">支付方式</div>
        <div class="td" style=" width:13%;">操作</div>
        <div class="clear"></div>
    </div>

    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="list_tr link_black">
            <div class="td" style=" width:10%;">
                <div class="left_padding striking">
                    <?php if($vo['is_paid'] == 1): ?><input name="id[]" type="checkbox" id="y_id" value="<?php echo ($vo['id']); ?>"  />
                        <span style="color: #FF6600">待付款</span>
                        <?php elseif($vo['is_paid'] == 2): ?>
                        <input name="id[]" type="checkbox" id="y_id" value="<?php echo ($vo['id']); ?>" disabled="disabled"/>
                        <span style="color: #009900;">已完成</span>
                        <?php else: ?>
                        <input name="id[]" type="checkbox" id="y_id" value="<?php echo ($vo['id']); ?>" disabled="disabled"/>
                        <span style="color: #999;">已取消</span><?php endif; ?>
                </div>
            </div>
            <div class="td" style=" width:10%;">￥<strong><?php echo ($vo['amount']); ?></strong>元</div>
            <div class="td" style=" width: 27%;"><?php echo ($vo['description']); ?></div>
            <div class="td center" style=" width:12%;"><?php echo ($vo['oid']); ?></div>
            <div class="td center" style=" width:10%;"><?php echo ($vo['username']); ?></div>
            <div class="td center" style=" width:9%;"><?php echo admin_date($vo['addtime']);?></div>
            <div class="td center" style=" width:9%;"><?php echo ($vo['payment_cn']); ?></div>
            <div class="td edit" style=" width:13%;">
                <?php if($vo['is_paid'] == 2 || $vo['is_paid'] == 3): ?><a href="<?php echo U('order_show_per',array('id'=>$vo['id'],'_k_v'=>$_GET['_k_v']));?>">查看</a><?php endif; ?>
                <?php if($vo['is_paid'] == 1): ?><a href="<?php echo U('order_set_per',array('id'=>$vo['id'],'_k_v'=>$_GET['_k_v']));?>">设置</a>
                    <a href="<?php echo U('order_cancel_per',array('id'=>$vo['id'],'_k_v'=>$_GET['_k_v']));?>" onclick="return confirm('你确定要取消吗？')" class="gray">取消</a><?php endif; ?>
            </div>
            <div class="clear"></div>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>
</form>

<?php if(empty($list)): ?><div class="list_empty">没有任何信息！</div><?php endif; ?>

<div class="list_foot">
    <div class="btnbox">
        <input type="button" class="admin_submit" id="ButDelOrder" value="取消订单"/>
    </div>
    <div class="footso">
        <?php if($_GET['_k_v']== ''): ?><form action="?" method="get">
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
                            <li id="2" title="订单号">订单号</li>
                        </ul>
                    </div>
                </div>
            </form><?php endif; ?>
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
        //点击批量取消
        $("#ButDelOrder").click(function () {
            if (confirm('你确定要取消吗？')) {
                $("form[name=form1]").submit();
            }
        });
    });
</script>
</html>