<?php 
namespace Common\Model;
use Think\Model\RelationModel;
class HrtoolsModel extends RelationModel{
	protected $_validate = array(
		array('h_typeid,h_filename,h_fileurl','identicalNull','',1,'callback'),
		array('h_typeid,h_order,h_strong','identicalEnum','',1,'callback'),
		array('h_filename','1,200','{%h_filename_length}',1,'length'),
	);
	protected $_link = array(
        'category' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'HrtoolsCategory',
            'foreign_key' => 'h_typeid',
        ),
    );
}	
?>