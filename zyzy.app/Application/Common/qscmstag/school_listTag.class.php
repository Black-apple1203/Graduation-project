<?php

namespace Common\qscmstag;

defined('THINK_PATH') or exit();

class school_listTag {
    protected $params = array();
    protected $limit;
    protected $map = array();

    public function __construct(array $params) {
        $array = array(
            '列表名' => 'listname',
            '显示数目' => 'row',
            '标题长度' => 'titlelen',
            '填补字符' => 'dot',
            '排序' => 'displayorder',
            '关键字' => 'key',
            '分页显示' => 'paged',
            '开始位置' => 'start',
        );
        foreach ($params as $key => $value) {
            $this->params[$array[$key]] = $value;
        }

        // 初始化参数/属性
        $this->params['listname'] = isset($this->params['listname']) ? $this->params['listname'] : "list";
        $this->params['titlelen'] = isset($this->params['titlelen']) ? intval($this->params['titlelen']) : 15;
        $this->params['dot'] = isset($this->params['dot']) ? $this->params['dot'] : '...';
        $this->params['start'] = isset($this->params['start']) ? intval($this->params['start']) : 0;
        $this->limit = isset($this->params['row']) ? intval($this->params['row']) : 10;

        // 查询条件
        $this->map['display'] = array('eq', 1);
        if (isset($this->params['key']) && !empty($this->params['key'])) {
            $this->map['name'] = array('like', '%' . trim($this->params['key']) . '%');
        }
    }

    public function run() {
        if ($this->params['paged']) {
            $total = D('School/School')->where($this->map)->count();
            $pager = pager($total, $this->limit);
            $pager->showname = 'Qs_school_list';
            $page = $pager->fshow();
            $p = I('get.page', 1, 'intval');
            $this->firstRow = abs($p - 1) * $this->limit;
        } else {
            $this->firstRow = $this->params['start'];
            $total = 0;
            $page = '';
        }

        $list = D('School/School')->get_list(array('introduction', true), $this->map, array($this->firstRow, $this->limit));
        $list = array_map(array($this, 'fill'), $list);

        $return['list'] = $list;
        $return['page'] = $page;
        $return['total'] = $total;

        return $return;
    }

    private function fill($item) {
        $item['name'] = cut_str($item['name'], $this->params['titlelen'], 0, $this->params['dot']);
        return $item;
    }
}