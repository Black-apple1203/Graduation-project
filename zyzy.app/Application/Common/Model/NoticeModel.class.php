<?php
namespace Common\Model;
use Think\Model\RelationModel;
class NoticeModel extends RelationModel{
	protected $_validate = array(
		array('type_id,title,content','identicalNull','',1,'callback'),
	);
	protected $_auto = array (
		array('is_display',1),
		array('click',1),
		array('addtime','time',1,'function'),
		array('sort',0),
	);
	protected $_link = array(
        'category' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'NoticeCategory',
            'foreign_key' => 'type_id',
        ),
    );
	/*
		获取公告列表 get_notice_list
		@data array 查询条件 
		@page 是否开启分页 (1=>开启 0=>不开启)
		@pagesize 若开启分页 则表示 一页显示条数 ; 若没有开启分页 则表示 要显示条数
		@type 公告类型 (1=>首页公告  2=>培训公告)
	*/
	public function get_notice_list($data,$page=1,$pagesize=10,$type=1)
	{
		$data['type_id'] = intval($type);
		// 开启分页
		if($page)
		{
			$count = $this->where($data)->count();
			$pager =  pager($count,$pagesize);
			$notice_list = $this->where($data)->limit($pager->firstRow . ',' . $pager->listRows)->select();
			$result['list']=$notice_list;
			$result['page']=$pager->fshow();
		}
		else
		{
			$notice_list = $this->where($data)->limit($pagesize)->select();
			$result = $notice_list;
		}
		return $result;
	}
}
?>