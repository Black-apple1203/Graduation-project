<?php
/**
 * 商品列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class goods_listTag {
	protected $params = array();
	protected $map = array();
	protected $order;
	protected $limit;
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'显示数目'			=>	'row',
    		'标题长度'			=>	'titlelen',
    		'开始位置'			=>	'start',
    		'填补字符'			=>	'dot',
            '商品大类'          =>  'category',
            '商品小类'          =>  'scategory',
            '关键字'            =>  'key',
            '推荐'              =>  'recommend',
            '分页显示'          =>  'paged',
    		'排序'				=>	'displayorder',
    		'积分范围'			=>	'points_interval',
    		'会员积分'			=>	'user_points'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        if(isset($this->params['category']) && intval($this->params['category'])>0){
            $this->map['category'] = array('eq',intval($this->params['category']));
        }
        if(isset($this->params['scategory']) && intval($this->params['scategory'])>0){
            $this->map['scategory'] = array('eq',intval($this->params['scategory']));
        }
        if(isset($this->params['key']) && trim($this->params['key'])<>''){
            $this->map['goods_title'] = array('like','%'.trim($this->params['key']).'%');
        }
        if(isset($this->params['points_interval']) && !empty($this->params['points_interval'])){
            $points_arr=explode('-',$this->params['points_interval']);
            $points_min=$points_arr[0];
            $points_max=$points_arr[1];
            $this->map['goods_points'] = array(array('gt',$points_min),array('lt',$points_max),'and');
        }
        if(isset($this->params['user_points']) && !empty($this->params['user_points']))
        {
            $this->map['goods_points'] = array('lt',intval($this->params['user_points']));
        }
        $this->params['recommend']=isset($this->params['recommend'])?intval($this->params['recommend']):0;
        if(isset($this->params['displayorder'])){
            $displayorder = explode(':',$this->params['displayorder']);
            $sort_arr = array('addtime','goods_points','ex_time');
            $order_arr = array('asc','desc');
            if(in_array($displayorder[0], $sort_arr) && in_array($displayorder[1], $order_arr)){
                $this->order = $displayorder[0].' '.$displayorder[1];
            }else{
                $this->order = 'id desc';
            }
        }else{
            if($this->params['recommend']>0){
                $this->order = 'recommend desc,addtime desc,id desc';
            }else{
                $this->order = 'addtime desc,id desc';
            }
        }
    	$this->limit = isset($this->params['row'])?intval($this->params['row']):10;
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
		$this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
		$this->params['titlelen']=isset($this->params['titlelen'])?intval($this->params['titlelen']):15;
		$this->params['dot']=isset($this->params['dot'])?$this->params['dot']:'';
    }
    public function run(){
    	if($this->params['paged']){
    		$count = M('MallGoods')->where($this->map);
	    	$total = $count->count();
	        $pager = pager($total, $this->limit);
            $pager->showname = 'QS_goods_list';
	        $page = $pager->fshow();
	        $this->params['start']>0 && $pager->firstRow = $this->params['start'];
	        $this->limit = $pager->firstRow.','.$pager->listRows;
    	}else{
            $this->limit = $this->params['start'].','.$this->limit;
    		$total = 0;
    		$page = '';
    	}
        $select = M('MallGoods');
		if(isset($this->map) && !empty($this->map)){
            $select = $select->where($this->map);
        }
        $result = $select->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
        	$row = $value;
        	$row['goods_title_']=cut_str($row['goods_title'],$this->params['titlelen'],0,$this->params['dot']);
            $row['goods_url']=url_rewrite('QS_goods_show',array('id'=>$row['id']));
            $row['goods_img'] = $row['goods_img']?attach($row['goods_img'],'mall'):attach($row['goods_img'],'resource');
			$list[] = $row;
        }
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
}