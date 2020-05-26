<?php if (!defined('THINK_PATH')) exit();?><!-- <div class="si_ava"><a href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>" target="_blank"><?php if($resume['photo_img']): ?><img src="<?php echo attach($resume['photo_img'],'avatar');?>" alt=""><?php else: if($resume['sex'] == 1): ?><img src="<?php echo attach('no_photo_male.png','resource');?>" alt=""><?php else: ?><img src="<?php echo attach('no_photo_female.png','resource');?>" alt=""><?php endif; endif; ?></a></div>
<div class="si_txt"><a class="name" href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>" target="_blank"><?php echo ($resume["fullname"]); ?></a>，<?php echo ($am_pm); ?></div>
<div class="si_txt"><a href="<?php echo U('personal/index');?>" target="_blank">更新简历</a>修改推荐职位</div> -->
<?php if(!empty($resume)): ?><div class="si_ava"><a href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>" target="_blank"><?php if($resume['photo_img']): ?><img src="<?php echo attach($resume['photo_img'],'avatar');?>" alt=""><?php else: if($resume['sex'] == 1): ?><img src="<?php echo attach('no_photo_male.png','resource');?>" alt=""><?php else: ?><img src="<?php echo attach('no_photo_female.png','resource');?>" alt=""><?php endif; endif; ?></a></div>
	<div class="si_txt"><a class="name" href="<?php echo url_rewrite('QS_resumeshow',array('id'=>$resume['id']));?>" target="_blank"><?php echo ($resume["fullname"]); ?></a>，<?php echo ($am_pm); ?></div>
<?php else: ?>
	<div class="si_ava"><a href="<?php echo U('personal/index');?>" target="_blank"><img src="<?php echo C('TPL_PUBLIC_DIR');?>/images/index/no_avatar.png" alt=""></a></div>
	<div class="si_txt"><a class="name" href="<?php echo U('personal/index');?>" target="_blank">hi</a>，<?php echo ($am_pm); ?></div><?php endif; ?>
<div class="si_txt"><a href="<?php echo U('personal/index');?>" target="_blank">更新简历</a>修改推荐职位</div>
<div class="clear"></div>
<div class="si_t">
	<div class="t_li"></div><div class="t_tx">为你精选的职位</div>
</div>
<?php if(!empty($info)): if(is_array($info)): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="si_sl">
	<div class="si_sll substring"><span>【推荐】</span><a href="<?php echo ($list["jobs_url"]); ?>" target="_blank"><?php echo ($list["jobs_name"]); ?></a></div>
	<div class="si_slr"><?php echo ($list["wage_cn"]); ?></div>
	<div class="clear"></div>
</div><?php endforeach; endif; else: echo "" ;endif; endif; ?>