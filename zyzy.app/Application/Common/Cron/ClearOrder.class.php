<?php
/*
* 74cms 计划任务 每天处理未支付订单
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
class ClearOrder{
	public function run(){
		$model = D('Order');
		$table_name = C('DB_PREFIX').'order';
		$join = C('DB_PREFIX').'members as m on '.$table_name.'.uid=m.uid';
		$cuttime = strtotime('-15 day');
		$where[$table_name.'.is_paid'] = array('eq',1);
		$where[$table_name.'.addtime'] = array('lt',$cuttime);
		$result = $model->where($where)->field($table_name.'.*,m.username,m.utype')->join($join)->select();
		foreach ($result as $key => $value) {
			$proid[] = $value['id'];
		}
		if (is_array($proid) && !empty($proid))
		{
			$sqlin=implode(",",$proid);
			$model->where(array('id'=>array('in',$sqlin)))->setField('is_paid',3);
		}
	}
}
?>