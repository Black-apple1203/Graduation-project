<?php

namespace Common\qscmstag;

defined('THINK_PATH') or exit();

class school_talk_showTag {
    protected $params = array();
    protected $map = array();

    function __construct($options) {
        $array = array(
            '列表名' => 'listname',
            '宣讲会id' => 'id',
            '标题长度' => 'titlelen',
            '填补字符' => 'dot',
        );
        foreach ($options as $key => $value) {
            $this->params[$array[$key]] = $value;
        }
        $this->map['id'] = array('eq', intval($this->params['id']));
        $this->params['listname'] = isset($this->params['listname']) ? $this->params['listname'] : "info";
        $this->params['titlelen'] = isset($this->params['titlelen']) ? $this->params['titlelen'] : 20;
        $this->params['dot'] = isset($this->params['dot']) ? $this->params['dot'] : '...';
    }

    public function run() {
        $item = D('School/SchoolTalk')->where($this->map)->find();
        if (!$item) {
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $item = $this->fill($item);

        return $item;
    }

    private function fill($item) {
        $item['url'] = url_rewrite('QS_school_talk_show', array('id' => $item['id']));
        if ($item['starttime'] > time()) {
            $item['status'] = 1;
            $item['status_cn'] = '未举办';
        } else {
            $item['status'] = 2;
            $item['status_cn'] = '已举办';
        }
        $item['introduction'] = htmlspecialchars_decode($item['introduction'], ENT_QUOTES);
        return $item;
    }
}