<?php if (!defined('THINK_PATH')) exit(); if(empty($list)): ?><div class="empty font_gray9">项目经历让你的简历超越99%的竞争者，快来说说令您难忘的项目经历吧！</div>
<?php else: ?>
	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="course J_listhover_edit" data-id="<?php echo ($list["id"]); ?>">
			<div class="td1">
				<?php echo ($list["startyear"]); ?>-<?php echo ($list["startmonth"]); ?> 至<?php if($list['todate'] == 1): ?>今<br/>[<?php echo ddate($list['startyear'].'-'.$list['startmonth'],date('Y-m',time()));?>]<?php else: ?> <?php echo ($list["endyear"]); ?>-<?php echo ($list["endmonth"]); ?><br/>[<?php echo ddate($list['startyear'].'-'.$list['startmonth'],$list['endyear'].'-'.$list['endmonth']);?>]<?php endif; ?>
			</div>
			<div class="td2"></div>
			<div class="td3">
				<div class="ltxt font_blue"><?php echo ($list["projectname"]); ?><span>|</span><?php echo ($list["role"]); ?></div>
				<div class="editbox link_yellow"><a class="J_editpro" href="javascript:;">修改</a>&nbsp;&nbsp;&nbsp;<a class="J_delpro" href="javascript:;">删除</a></div>
				<div class="clear"></div>
				<div class="txt"><?php echo ($list["description"]); ?></div>
			</div>
			<div class="clear"></div>
		</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>