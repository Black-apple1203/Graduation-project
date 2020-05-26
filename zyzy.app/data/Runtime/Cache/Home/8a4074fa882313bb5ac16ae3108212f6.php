<?php if (!defined('THINK_PATH')) exit(); if(empty($list)): ?><div class="empty font_gray9">证书是您驰骋职场的敲门砖。 您有哪些证书呢？</div>
<?php else: ?>
	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="course J_listhover_edit" data-id="<?php echo ($list["id"]); ?>">
			<div class="td1"><?php echo ($list["year"]); ?>-<?php echo ($list["month"]); ?></div>
			<div class="td2"></div>
			<div class="td3">
				<div class="ltxt font_blue"><?php echo ($list["name"]); ?></div>
				<div class="editbox link_yellow"><a class="J_editcre" href="javascript:;">修改</a>&nbsp;&nbsp;&nbsp;<a class="J_delcre" href="javascript:;">删除</a></div>
				<div class="clear"></div>
				<div class="txt"> </div>
			</div>			 
			<div class="clear"></div>
		</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>