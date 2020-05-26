<?php
/**
 * 商品兑换列表
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class goods_exchange_listTag {
	protected $params = array();
	protected $map = array();
	protected $order;
	protected $limit;
    function __construct($options) {
    	$array = array(
    		'列表名'			=>	'listname',
    		'显示数目'			=>	'row',
            '开始位置'          =>  'start',
            '商品id'            =>  'goods_id',
            '分页显示'          =>  'paged'
    	);
    	foreach ($options as $key => $value) {
    		$this->params[$array[$key]] = $value;
    	}
        if(isset($this->params['goods_id']) && intval($this->params['goods_id'])>0){
            $this->map['goods_id'] = intval($this->params['goods_id']);
        }
        $this->order = 'id desc';
    	$this->limit = isset($this->params['row'])?intval($this->params['row']):10;
        $this->params['start']=isset($this->params['start'])?intval($this->params['start']):0;
    	$this->params['listname']=isset($this->params['listname'])?$this->params['listname']:"list";
    }
    public function run(){
        if($this->params['paged']){
            $count = M('MallExchange');
            if($this->map){
               $count->where($this->map); 
            }
            $total = $count->count();
            $pager = pager($total, $this->limit);
            $page = $pager->fshow();
            $this->params['start']>0 && $pager->firstRow = $this->params['start'];
            $this->limit = $pager->firstRow.','.$pager->listRows;
        }else{
            $total = 0;
            $page = '';
        }
        $select = M('MallExchange');
        if($this->map){
            $select = $select->where($this->map); 
        }
        $result = $select->order($this->order)->limit($this->limit)->select();
        $list = array();
        foreach ($result as $key => $value) {
        	$row = $value;
            $row['goods_url']=url_rewrite('QS_goods_show',array('id'=>$row['goods_id']));
        	$row['addtime_cn'] = daterange(time(),$row['addtime']);
            $row['username'] = contact_hide($value['username'],4);
			$list[] = $row;
        }
        $return['page'] = $page;
        $return['total'] = $total;
        $return['list'] = $list;
        return $return;
    }
}