<?php if (!defined('THINK_PATH')) exit(); if(!empty($company)): ?><div class="si_ava"><a href="<?php echo url_rewrite('QS_companyshow',array('id'=>$company['id']));?>" target="_blank"><?php if($company['logo']): ?><img src="<?php echo attach($company['logo'], 'company_logo');?>" alt=""><?php else: ?><img src="<?php echo attach('no_logo.png', 'resource');?>" alt=""><?php endif; ?></a></div>
	<div class="si_txt"><a class="name" href="<?php echo url_rewrite('QS_companyshow',array('id'=>$company['id']));?>" target="_blank"><?php echo ($company["companyname"]); ?></a></div>
<?php else: ?>
	<div class="si_ava"><a href="<?php echo U('company/index');?>" target="_blank"><img src="<?php echo attach('no_logo.png', 'resource');?>" alt=""></a></div>
	<div class="si_txt"><a class="name" href="<?php echo U('company/index');?>" target="_blank">请完善企业资料！</a></div><?php endif; ?>
<div class="si_txt"><a href="javascript:;" id="J_refresh_jobs">刷新职位</a>获取更多推荐</div>
<div class="clear"></div>
<div class="si_t">
	<div class="t_li"></div><div class="t_tx">为你精选的人才</div>
</div>
<?php if(!empty($info)): if(is_array($info)): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="si_sl res">
		<div class="si_sll substring"><span>【推荐】</span><a href="<?php echo ($list["resume_url"]); ?>" target="_blank"><?php echo ($list["fullname"]); ?></a></div>
		<div class="si_slr"><?php echo ($list["age"]); ?>岁/<?php echo ($list["experience_cn"]); ?>/<?php echo ($list["education_cn"]); ?></div>
		<div class="clear"></div>
	</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>