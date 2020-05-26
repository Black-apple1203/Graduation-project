<?php

namespace Common\qscmstag;

defined('THINK_PATH') or exit();

class school_showTag {
    protected $params = array();
    protected $map = array();

    function __construct($options) {
        $array = array(
            '列表名' => 'listname',
            '院校id' => 'id',
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
        $item = D('School/School')->where($this->map)->find();
        if (!$item) {
            $controller = new \Common\Controller\BaseController;
            $controller->_empty();
        }
        $item = $this->fill($item);

        return $item;
    }

    private function fill($item) {
        $sqlin['school_id'] = array('in', $item['id']);
        $sqlin['display'] = 1;
        $item['election_count'] = D('School/SchoolElection')->where($sqlin)->count();
        $sqlin['audit'] = array('elt', 1);
        $item['talk_count'] = D('School/SchoolTalk')->where($sqlin)->count();
        $item['introduction'] = htmlspecialchars_decode($item['introduction'], ENT_QUOTES);
        $item['thumb'] = $item['thumb'] ? attach($item['thumb'], 'school_img') : attach('no_logo.png', 'resource');
        $item['url'] = url_rewrite('QS_school_school_show', array('id' => $item['id']));
        $item['election_url'] = url_rewrite('QS_school_school_election', array('school_id' => $item['id']));
        $item['talk_url'] = url_rewrite('QS_school_school_talk', array('school_id' => $item['id']));
        $img_list_map = array('school_id' => intval($this->params['id']), 'display' => 1);
        $imgs = array();
        if (!empty($item['img'])) {
            $item['img'] = htmlspecialchars_decode($item['img']);
            $imgs = json_decode($item['img']);
            foreach ($imgs as $index => $img) {
                $imgs[$index] = attach($img, 'school_img');
            }
        }
        $item['img_list'] = $imgs;
        return $item;
    }
}