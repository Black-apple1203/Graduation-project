<?php if (!defined('THINK_PATH')) exit(); if(empty($list)): ?><div class="empty font_gray9">工作经历最能体现您丰富的阅历和出众的工作能力，是你薪酬翻倍的筹码哦！</div>
<?php else: ?>
	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="course J_listhover_edit" data-id="<?php echo ($list["id"]); ?>">
			<div class="td1">
				<?php echo ($list["startyear"]); ?>-<?php echo ($list["startmonth"]); ?> 至<?php if($list['todate'] == 1): ?>今<br/>[<?php echo ddate($list['startyear'].'-'.$list['startmonth'],date('Y-m',time()));?>]<?php else: ?> <?php echo ($list["endyear"]); ?>-<?php echo ($list["endmonth"]); ?><br/>[<?php echo ddate($list['startyear'].'-'.$list['startmonth'],$list['endyear'].'-'.$list['endmonth']);?>]<?php endif; ?>
			</div>
			<div class="td2"></div>
			<div class="td3">
				<div class="ltxt font_blue"><?php echo ($list["jobs"]); ?><span>|</span><?php echo ($list["companyname"]); ?></div>
				<div class="editbox link_yellow"><a class="J_editexp" href="javascript:;">修改</a>&nbsp;&nbsp;&nbsp;<a class="J_delexp" href="javascript:;">删除</a></div>
				<div class="clear"></div>
				<div class="txt"><?php echo ($list["achievements"]); ?></div>
			</div>
			<div class="clear"></div>
		</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>