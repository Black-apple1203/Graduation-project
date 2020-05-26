<?php if (!defined('THINK_PATH')) exit(); if(empty($list)): ?><div class="empty font_gray9">语言能力是提升求职竞争力的法宝，千万别谦虚啊！</div>
<?php else: ?>
	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="label"><strong class="font_blue"><?php echo ($list["language_cn"]); ?></strong><?php echo ($list["level_cn"]); ?></div><?php endforeach; endif; else: echo "" ;endif; ?>
	<div class="clear"></div><?php endif; ?>