<?php
/*
* 74cms 计划任务 限时推广清理
* ============================================================================
* 版权所有: 骑士网络，并保留所有权利。
* 网站地址: http://www.74cms.com；
* ----------------------------------------------------------------------------
* 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
* 使用；不允许对程序代码以任何形式任何目的的再发布。
* ============================================================================
*/
defined('THINK_PATH') or exit();
ignore_user_abort(true);
class ClearPromotion{
	public function run(){
		//职位推广
		$model = D('Promotion');
		$where['cp_endtime'] = array('lt',time());
		$result = $model->where($where)->select();
		foreach ($result as $key => $value) {
			$model->cancel_promotion($value['cp_jobid'],$value['cp_ptype']);
			$proid[] = $value['cp_id'];
		}
		if (is_array($proid) && !empty($proid))
		{
			$sqlin=implode(",",$proid);
			$model->where(array('cp_id'=>array('in',$sqlin)))->delete();
		}


		//简历置顶推广
		$model = D('PersonalServiceStickLog');
		$where['endtime'] = array('lt',time());
		$result = $model->where($where)->select();
		foreach ($result as $key => $value) {
			$model->cancel_stick($value['resume_id']);
			$proid[] = $value['id'];
		}
		if (is_array($proid) && !empty($proid))
		{
			$sqlin=implode(",",$proid);
			$model->where(array('id'=>array('in',$sqlin)))->delete();
		}


		//简历醒目标签推广
		$model = D('PersonalServiceTagLog');
		$where['endtime'] = array('lt',time());
		$result = $model->where($where)->select();
		foreach ($result as $key => $value) {
			$model->cancel_tag($value['resume_id']);
			$proid[] = $value['id'];
		}
		if (is_array($proid) && !empty($proid))
		{
			$sqlin=implode(",",$proid);
			$model->where(array('id'=>array('in',$sqlin)))->delete();
		}
	}
}
?>