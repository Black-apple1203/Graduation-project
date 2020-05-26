<?php

namespace Common\qscmstag;

use School\Model\SchoolModel;

defined('THINK_PATH') or exit();

class school_talk_listTag {
    protected $params = array();
    protected $limit;
    protected $order;
    protected $map = array();

    public function __construct(array $params) {
        $array = array(
            '列表名' => 'listname',
            '显示数目' => 'row',
            '标题长度' => 'titlelen',
            '填补字符' => 'dot',
            '排序' => 'displayorder',
            '分页显示' => 'paged',
            '开始位置' => 'start',
            '举办时间' => 'timecase',
            '院校id' => 'school_id',
            '企业id' => 'company_id',
            '列表页' => 'listpage',
            '审核' => 'audit',
            '列表类型' => 'listtype',
        );
        foreach ($params as $key => $value) {
            $this->params[$array[$key]] = $value;
        }

        // 初始化参数/属性
        $this->params['listname'] = isset($this->params['listname']) ? $this->params['listname'] : "list";
        $this->params['titlelen'] = isset($this->params['titlelen']) ? intval($this->params['titlelen']) : 15;
        $this->params['dot'] = isset($this->params['dot']) ? $this->params['dot'] : '...';
        $this->params['start'] = isset($this->params['start']) ? intval($this->params['start']) : 0;
        $this->params['timecase'] = isset($this->params['timecase']) ? intval($this->params['timecase']) : 0;
        $this->limit = isset($this->params['row']) ? intval($this->params['row']) : 10;
        $this->order = isset($this->params['displayorder']) ? explode(':', $this->params['displayorder']) : array('addtime', 'desc');

        // 查询条件
        $this->map['display'] = array('eq', 1);
        if ($this->params['timecase'] > 0) {
            $this->set_timecase_map($this->params['timecase']);
        }
        if (isset($this->params['school_id']) && intval($this->params['school_id']) > 0) {
            $school_id = intval($this->params['school_id']);
            $this->map['school_id'] = $school_id;
        }
        if (isset($this->params['company_id']) && intval($this->params['company_id']) > 0) {
            $company_id = intval($this->params['company_id']);
            $this->map['company_id'] = $company_id;
        }
        if (isset($this->params['audit'])) {
            $audit = (int)$this->params['audit'];
            $this->map['audit'] = array('elt', $audit);
        } else {
            $this->map['audit'] = array('elt', 1);
        }
    }

    public function run() {
        if (isset($this->params['listtype']) && $this->params['listtype'] == 'time_group') {
            /*
             * 最近举办(15日内)、月份分组、已举办
             */
            $_list = D('School/SchoolTalk')->field('introduction', true)->where($this->map)->select();
            $_list = $this->list_fill($_list);
            $list = array();
            if (!empty($_list)) {
                $today = time();
                $latestday = strtotime('+15 day', $today);
                foreach ($_list as $index => $item) {
                    if ($item['starttime'] >= $today && $item['starttime'] <= $latestday) {
                        $list['即将举办'][] = $item;
                    } elseif ($item['starttime'] > $latestday) {
                        $list[date('Y-m', $item['starttime'])][] = $item;
                    } else {
                        $list['已举办'][] = $item;
                    }
                }
            }
            uksort($list, function ($key1, $key2) {
                $key1 = $key1 == '即将举办' ? '0' : ($key1 == '已举办' ? '9999' : $key1);
                $key2 = $key2 == '即将举办' ? '0' : ($key2 == '已举办' ? '9999' : $key2);
                return strcmp($key1, $key2);
            });
            unset($_list);
        } else {
            if ($this->params['paged']) {
                /*
                 * 有分页时按固定规则排序，忽略标签中指定的排序规则
                 * 大于当前时间按升序排序，小于当前时间按倒序排序，优先查询大于当前时间的记录
                 */
                // 查询全部满足条件的记录总数
                // 查询全部满足条件的时间大于当前时间的总数
                // 获取当前分页数和每页获取记录数
                $currtime = time();
                $total = D('School/SchoolTalk')->where($this->map)->count();
                $unstart_total = D('School/SchoolTalk')->where($this->map)->where(array('starttime' => array('gt', $currtime)))->count();
                $pager = pager($total, $this->limit);
                if (isset($this->params['listpage'])) {
                    $pager->path = $this->params['listpage'];
                } else {
                    $pager->showname = 'Qs_school_list';
                }
                $page = $pager->fshow();
                $p = I('get.page', 1, 'intval');
                $prange_min = abs($p - 1) * $this->limit + 1;
                if ($unstart_total < $prange_min) {
                    $list = D('School/SchoolTalk')->field('introduction', true)
                        ->where($this->map)
                        ->page($p, $this->limit)
                        ->order('starttime desc')
                        ->select();
                } else {
                    $list = D('School/SchoolTalk')->field('introduction', true)
                        ->where($this->map)
                        ->where(array('starttime' => array('gt', $currtime)))
                        ->page($p, $this->limit)
                        ->order('starttime asc')
                        ->select();
                    if ((count($list) < $this->limit) && ($total > $unstart_total)) {
                        $_list = D('School/SchoolTalk')->field('introduction', true)
                            ->where($this->map)
                            ->where(array('starttime' => array('elt', $currtime)))
                            ->limit($this->limit - count($list))
                            ->order('starttime desc')
                            ->select();
                        $list = array_merge($list, $_list);
                    }
                }
                $list = $this->list_fill($list);
            } else {
                $this->firstRow = $this->params['start'];
                $total = 0;
                $page = '';

                $list = D('School/SchoolTalk')->field('introduction', true)
                    ->where($this->map)
                    ->limit($this->firstRow, $this->limit)
                    ->order(implode(' ', $this->order))
                    ->select();

                $list = $this->list_fill($list);
            }
        }
        $return['list'] = $list;
        $return['page'] = $page;
        $return['total'] = $total;

        return $return;
    }

    private function fill($item) {
        $item['school']['name'] = cut_str($item['school']['name'], $this->params['titlelen'], 0, $this->params['dot']);
        $item['subject'] = cut_str($item['subject'], $this->params['titlelen'], 0, $this->params['dot']);
        $item['url'] = url_rewrite('QS_school_talk_info', array('id' => $item['id']));
        if ($item['starttime'] > time()) {
            $item['status'] = 1;
            $item['status_cn'] = '未举办';
        } else {
            $item['status'] = 2;
            $item['status_cn'] = '已举办';
        }

        return $item;
    }

    private function set_timecase_map($timecase) {
        // 今天 明天 一周内 一月内 三月内 已举办 即将举办
        switch ($timecase) {
            case 1:  // 今天
                $s = strtotime(date('Y-m-d 00:00:00'));
                $e = strtotime(date('Y-m-d 23:59:59'));
                $this->map['starttime'] = array('between', array($s, $e));
                break;
            case 2:  // 明天
                $time = strtotime('+1 day');
                $s = strtotime(date('Y-m-d 00:00:00', $time));
                $e = strtotime(date('Y-m-d 23:59:59', $time));
                $this->map['starttime'] = array('between', array($s, $e));
                break;
            case 3:  // 一周内
                $this->map['starttime'] = array('between', array(time(), strtotime('+7 day')));
                break;
            case 4:  // 一月内
                $this->map['starttime'] = array('between', array(time(), strtotime('+30 day')));
                break;
            case 5:  // 三月内
                $this->map['starttime'] = array('between', array(time(), strtotime('+90 day')));
                break;
            case 6:  // 已举办
                $this->map['starttime'] = array('lt', time());
                break;
            case 7:  // 即将举办
                $this->map['starttime'] = array('gt', time());
                break;
        }
    }

    /**
     * @param array $list
     * @return array
     */
    private function list_fill(array $list) {
        if (!empty($list)) {
            $school_ids = array();
            foreach ($list as $item) {
                $school_ids[] = $item['school_id'];
            }
            $school_mod = new SchoolModel();
            $school_list = $school_mod->get_list(array('introduction', true), array('id' => array('in', $school_ids)));
            foreach ($list as $index => $item) {
                $list[$index]['school'] = isset($school_list[$item['school_id']]) ? $school_list[$item['school_id']] : array();
            }

            $list = array_map(array($this, 'fill'), $list);
        }
        return $list;
    }
}