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
		var URL = '/index.php/Admin/Config',
			SELF = '/index.php?m=Admin&amp;c=Config&amp;a=index&amp;menu_id=1&amp;sub_menu_id=6',
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
        <p>页面标题设置以及关键字设置等请在页面管理中设置。</p>
        <p>网站域名和网站安装目录填写错误可导致网站部分功能不能使用。</p>
    </div>
<form id="form1">
    <div class="toptit">网站配置</div>
    <div class="form_main width200">
        <div class="fl">网站名称：</div>
        <div class="fr">
            <input name="site_name" type="text" class="input_text_default" maxlength="30" value="<?php echo C('qscms_site_name');?>"/>
        </div>
        <div class="fl">网站域名：</div>
        <div class="fr">
            <input name="site_domain" type="text" class="input_text_default" maxlength="100" value="<?php echo C('qscms_site_domain');?>" placeholder="结尾不要加 &quot; / &quot;" />
        </div>
        <div class="fl">安装目录：</div>
        <div class="fr">
            <input name="site_dir" type="text" class="input_text_default" maxlength="40" value="<?php echo C('qscms_site_dir');?>" placeholder='以 " / " 开头和结尾, 如果安装在根目录，则为" / "' />
        </div>
        <div class="fl">联系电话(顶部)：</div>
        <div class="fr">
            <input name="top_tel" type="text" class="input_text_default" maxlength="80" value="<?php echo C('qscms_top_tel');?>"/>
        </div>
        <div class="fl">联系电话(底部)：</div>
        <div class="fr">
            <input name="bootom_tel" type="text" class="input_text_default" maxlength="80" value="<?php echo C('qscms_bootom_tel');?>"/>
        </div>
        <div class="fl">联系邮箱：</div>
        <div class="fr">
            <input name="contact_email" type="text" class="input_text_default" maxlength="80" value="<?php echo C('qscms_contact_email');?>"/>
        </div>
        <div class="fl">网站底部联系地址：</div>
        <div class="fr">
            <input name="address" type="text" class="input_text_default" maxlength="120" value="<?php echo C('qscms_address');?>"/>
        </div>
        <div class="fl">网站底部其他说明：</div>
        <div class="fr">
            <input name="bottom_other" type="text" class="input_text_default" maxlength="200" value="<?php echo C('qscms_bottom_other');?>"/>
        </div>
        <div class="fl">网站备案号(ICP)：</div>
        <div class="fr">
            <input name="icp" type="text" class="input_text_default" maxlength="30" value="<?php echo C('qscms_icp');?>"/>
        </div>
        <div class="fl">暂时关闭网站：</div>
        <div class="fr">
            <div data-code="0,1" class="imgchecked_small <?php if(C('qscms_isclose') == 1): ?>select<?php endif; ?>"><input name="isclose" type="hidden" value="<?php echo C('qscms_isclose');?>" /></div>
            <div class="clear"></div>
        </div>
        <div class="fl">暂时关闭原因：</div>
        <div class="fr">
            <input name="close_reason" type="text" class="input_text_default" value="<?php echo C('qscms_close_reason');?>"/>
        </div>
        <div class="fl">第三方流量统计代码：</div>
        <div class="fr">
            <textarea name="statistics" class="input_text_default" style="height: 100px; line-height: 180%;"><?php echo C('qscms_statistics');?></textarea>
        </div>
        <div class="fl">网站首页Logo：</div>
        <div class="fr J-file-input-box">
            <?php if(C('qscms_logo_home')): ?><div class="file-input-src">
                    <div class="img"><img src="<?php echo attach(C('qscms_logo_home'),'resource');?>?_t=<?php echo time();?>" align=absmiddle></div>
                    <div class="del file-input-del" id="J_upload_logo_home_btn" name="logo_home">点击更换</div>
                    <div class="r-note">(建议尺寸240*70)</div>
                </div>
            <?php else: ?>
                <div class="file-input-src hid">
                    <div class="img"></div>
                    <div class="del file-input-del" id="" name="logo_home">点击更换</div>
                    <div class="r-note">(建议尺寸240*70)</div>
                </div>
                <div class="file-input-block" id="J_upload_logo_home_btn" name="logo_home"><span class="o-txt">上传</span>网站首页Logo<span class="re-txt">(建议尺寸240*70)</span></div><?php endif; ?>
            <input type="hidden" class="file-input-save-name" name="logo_home" value="<?php echo C('qscms_logo_home');?>">
        </div>
        <div class="fl">网站其它页Logo：</div>
        <div class="fr J-file-input-box">
            <?php if(C('qscms_logo_other')): ?><div class="file-input-src">
                    <div class="img"><img src="<?php echo attach(C('qscms_logo_other'),'resource');?>?_t=<?php echo time();?>" align=absmiddle></div>
                    <div class="del file-input-del" id="J_upload_logo_other_btn" name="logo_other">点击更换</div>
                    <div class="r-note">(建议尺寸210*55)</div>
                </div>
            <?php else: ?>
                <div class="file-input-src hid">
                    <div class="img"></div>
                    <div class="del file-input-del" id="" name="logo_other">点击更换</div>
                    <div class="r-note">(建议尺寸210*55)</div>
                </div>
                <div class="file-input-block" id="J_upload_logo_other_btn" name="logo_other" ><span class="o-txt">上传</span>网站其它页Logo<span class="re-txt">(建议尺寸210*55)</span></div><?php endif; ?>
            <input type="hidden" class="file-input-save-name" name="logo_other" value="<?php echo C('qscms_logo_other');?>">
        </div>
        <div class="fl"></div>
        <div class="fr">
            <input type="button" class="admin_submit" id="J_submit" value="保存修改"/>
        </div>
        <div class="clear"></div>
    </div>
</form>
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
<script type="text/javascript">
  var uploadUrl = "<?php echo U('Upload/form_upload');?>";
</script>
<script src="__ADMINPUBLIC__/js/ajaxfileupload.js"></script>
<script src="__ADMINPUBLIC__/js/fileupload.js"></script>
<script>
    $('#J_submit').click(function(){
        var that = $(this);
        if(that.hasClass('disabled')){
            return false;
        }
        that.val('正在保存...').addClass('disabled');
        $.post("<?php echo U('config/edit');?>",$('#form1').serialize(),function(result){
            if(result.status==1){
                disapperTooltip("success", result.msg,function(){
                    that.val('保存修改').removeClass('disabled');
                });
            }else{
                disapperTooltip("remind", result.msg,function(){
                    that.val('保存修改').removeClass('disabled');
                });
                return false;
            }
        },'json');
    });
    // 上传网站首页Logo
    $.upload('#J_upload_logo_home_btn',{name:'logo_home',dir:'resource'},function(result){
        if(result.error == 1){
            var htmlResult = '<img src="'+ result.url.src +'" align=absmiddle>';
            $('#J_upload_logo_home_btn').closest('.J-file-input-box').find('.file-input-src .img').html(htmlResult);
            $('#J_upload_logo_home_btn').closest('.J-file-input-box').find('.file-input-save-name').val(result.url.savename);
            if ($('#J_upload_logo_home_btn').hasClass('file-input-block')) {
              $('#J_upload_logo_home_btn').closest('.J-file-input-box').find('.file-input-src').removeClass('hid');
              var $delObj = $('#J_upload_logo_home_btn').closest('.J-file-input-box').find('.file-input-del');
              $('#J_upload_logo_home_btn').remove();
              $delObj.attr('id', "J_upload_logo_home_change_btn");
              $.upload('#J_upload_logo_home_change_btn',{name:'logo_home',dir:'resource'},function(result){
                if(result.error == 1){
                  var htmlResult = '<img src="'+ result.url.src +'" align=absmiddle>';
                  $('#J_upload_logo_home_change_btn').closest('.J-file-input-box').find('.file-input-src .img').html(htmlResult);
                  $('#J_upload_logo_home_change_btn').closest('.J-file-input-box').find('.file-input-save-name').val(result.url.savename);
                } else {
                  disapperTooltip("remind", "上传失败："+result.message);
                }
              })
            }
        } else {
            disapperTooltip("remind", "上传失败："+result.message);
        }
    })

    // 上传网站其它页Logo
    $.upload('#J_upload_logo_other_btn',{name:'logo_other',dir:'resource'},function(result){
      if(result.error == 1){
        var htmlResult = '<img src="'+ result.url.src +'" align=absmiddle>';
        $('#J_upload_logo_other_btn').closest('.J-file-input-box').find('.file-input-src .img').html(htmlResult);
        $('#J_upload_logo_other_btn').closest('.J-file-input-box').find('.file-input-save-name').val(result.url.savename);
        if ($('#J_upload_logo_other_btn').hasClass('file-input-block')) {
          $('#J_upload_logo_other_btn').closest('.J-file-input-box').find('.file-input-src').removeClass('hid');
          var $delObj = $('#J_upload_logo_other_btn').closest('.J-file-input-box').find('.file-input-del');
          $('#J_upload_logo_other_btn').remove();
          $delObj.attr('id', "J_upload_logo_other_change_btn");
          $.upload('#J_upload_logo_other_change_btn',{name:'logo_other',dir:'resource'},function(result){
            if(result.error == 1){
              var htmlResult = '<img src="'+ result.url.src +'" align=absmiddle>';
              $('#J_upload_logo_other_change_btn').closest('.J-file-input-box').find('.file-input-src .img').html(htmlResult);
              $('#J_upload_logo_other_change_btn').closest('.J-file-input-box').find('.file-input-save-name').val(result.url.savename);
            } else {
              disapperTooltip("remind", "上传失败："+result.message);
            }
          })
        }
      } else {
        disapperTooltip("remind", "上传失败："+result.message);
      }
    })
</script>
</body>
</html>