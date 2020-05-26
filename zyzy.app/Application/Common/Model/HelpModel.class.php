<?php 
namespace Common\Model;
use Think\Model\RelationModel;
class HelpModel extends RelationModel
{
	protected $_validate = array(
		array('parentid,title,content','identicalNull','',1,'callback'),
	);
	protected $_auto = array (
		array('click',1),
		array('addtime','time',1,'function'),
	);
	protected $_link = array(
        'category' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'HelpCategory',
            'foreign_key' => 'type_id',
        ),
        'parent' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'HelpCategory',
            'foreign_key' => 'parentid',
        )
    );
}	
?>