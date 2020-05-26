<?php
/**
 * qscms标签库驱动
 */
namespace Think\Template\Taglib;
use Think\Template\TagLib;
class Qscms extends TagLib{
    // 标签定义
    protected $tags   =  array(
        //加载资源
        'load' => array('attr'=>'type,href', 'close'=>0),
        'resume_list' => array('attr'=>'type,开始位置,显示数目,搜索类型,应届生简历,院校名称,更新时间,姓名长度,特长描述长度,意向职位长度,专业长度,填补字符,日期范围,职位分类,职位大类,职位小类,地区分类,地区大类,地区小类,行业,专业,标签,学历,工作经验,工资,等级,性别,照片,关键字,排序,分页显示,页面,列表页,浏览过的简历,列表名','close'=>0),
        'jobs_list' => array('attr'=>'type,搜索类型,显示数目,开始位置,职位名长度,企业名长度,描述长度,填补字符,应届生职位,职位分类,职位大类,职位小类,地区分类,地区大类,地区小类,道路,写字楼,标签,行业,学历,工作经验,工资,职位性质,公司规模,紧急招聘,推荐,关键字,关键字type,日期范围,排序,分页显示,会员UID,公司页面,职位页面,列表页,合并,公司列表名,公司职位页面,单个公司显示职位数,浏览过的职位,风格模板,列表名,去除id,经度,纬度,半径,搜索范围,联系方式,分站','close'=>0),
        'company_jobs_list' => array('attr'=>'type,显示数目,开始位置,企业名长度,填补字符,地区大类,地区小类,紧急招聘,日期范围,推荐,职位名长度,显示职位,职位页面,职位分类,行业,排序,分页显示,公司页面,列表页,统计职位,列表名,分站','close'=>0),
        'news_list' => array('attr'=>'type,列表名,显示数目,图片,属性,资讯大类,资讯小类,标题长度,摘要长度,开始位置,填补字符,日期范围,排序,关键字,分页显示,页面,列表页','close'=>0),
        'news_show' => array('attr'=>'type,列表名,资讯ID','close'=>0),
        'news_category' => array('attr'=>'type,列表名,显示数目,名称长度,开始位置,填补字符,资讯大类,资讯小类,排序,页面','close'=>0),
        'news_property' => array('attr'=>'type,列表名,名称长度,填补字符,排序,分类ID','close'=>0),
        'notice_list' => array('attr'=>'type,列表名,显示数目,标题长度,摘要长度,开始位置,填补字符,分类,排序,分页显示,页面,列表页','close'=>0),
        'notice_show' => array('attr'=>'type,列表名,公告ID','close'=>0),
        'jobfair_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,日期范围,参会企业页,排序,分页显示,页面,列表页','close'=>0),
        'jobfair_show' => array('attr'=>'type,列表名,招聘会id,标题长度,填补字符,参会企业页','close'=>0),
        'jobfair_exhibitors_list' => array('attr'=>'type,列表名,招聘会ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),
        'explain_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,分类ID,排序,页面','close'=>0),
        'explain_show' => array('attr'=>'type,列表名,说明页id','close'=>0),
        'help_list' => array('attr'=>'type,列表名,显示数目,大类,小类,标题长度,摘要长度,开始位置,填补字符,关键字,分页显示,页面,列表页','close'=>0),
        'help_show' => array('attr'=>'type,列表名,ID','close'=>0),
        'help_category' => array('attr'=>'type,列表名,名称长度,填补字符,大类,小类,页面,显示数目','close'=>0),
        'hotword' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符','close'=>0),
        'hrtools_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,分类ID','close'=>0),
        'hrtools_category' => array('attr'=>'type,列表名,标题长度,填补字符,分类ID','close'=>0),
        'link' => array('attr'=>'type,列表名,显示数目,开始位置,文字长度,填补字符,类型,调用名称','close'=>0),
        'nav' => array('attr'=>'type,列表名,显示数目,调用名称','close'=>0),
        'pageinfo' => array('attr'=>'type,列表名,分类ID,调用名称','close'=>0),
        'classify' => array('attr'=>'type,列表名,类型,显示数目,名称长度,填补字符,id','close'=>0),
        'company_list' => array('attr'=>'type,列表名,显示数目,开始位置,企业名长度,描述长度,填补字符,行业,地区分类,地区大类,地区中类,地区小类,企业性质,企业规模,关键字,排序,分页显示,公司页面,列表页,去除id','close'=>0),
        'company_show' => array('attr'=>'type,列表名,企业ID','close'=>0),
        'resume_show' => array('attr'=>'type,列表名,简历ID','close'=>0),
        'jobs_show' => array('attr'=>'type,列表名,职位ID','close'=>0),
        'goods_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,商品大类,商品小类,关键字,推荐,分页显示,排序,积分范围,会员积分','close'=>0),
        'goods_exchange_list' => array('attr'=>'type,列表名,显示数目,开始位置,商品id,分页显示','close'=>0),
        'goods_show' => array('attr'=>'type,列表名,商品id','close'=>0),
        'text' => array('attr'=>'type,列表名,类型','close'=>0),
        'ad' => array('attr'=>'type,列表名,广告位名称,广告数量,职位数量,广告位终端','close'=>0),
        'store_recruit_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,店铺类型,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'store_transfer_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,使用面积,月租金,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'store_tenement_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,经营业态,面积需求,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'store_recruit_show' => array('attr'=>'type,列表名,职位ID','close'=>0),
        'store_transfer_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'store_tenement_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'parttime_jobs_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,兼职类型,结算方式,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'parttime_jobs_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'gworker_jobs_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,薪资待遇,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'gworker_jobs_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'house_rent_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,房屋厅室,月租金,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'house_rent_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'house_seek_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,房屋厅室,月租金,地区分类,关键字,分页显示,排序,描述长度','close'=>0),
        'house_seek_show' => array('attr'=>'type,列表名,信息ID','close'=>0),
        'interview' => array('attr'=>'type,列表名,显示数目,图片,标题长度,摘要长度,开始位置,填补字符,日期范围,排序,关键字,分页显示,页面,列表页','close'=>0),
        'interview_show' => array('attr'=>'type,列表名,专访ID','close'=>0),
        'career' => array('attr'=>'type,列表名,显示数目,图片,标题长度,摘要长度,开始位置,填补字符,日期范围,排序,关键字,分页显示,页面,列表页','close'=>0),
        'career_show' => array('attr'=>'type,列表名,招考ID','close'=>0),
        'subject' => array('attr'=>'type,列表名,显示数目,图片,标题长度,摘要长度,开始位置,填补字符,日期范围,排序,关键字,分页显示,页面,列表页','close'=>0),
        'subject_show' => array('attr'=>'type,列表名,专题ID','close'=>0),
        'subject_company' => array('attr'=>'ype,列表名,显示数目,专题公司ID,关键字,关键字类型,分页显示','close'=>0),
        'subject_personal' => array('attr'=>'type,列表名,显示数目,专题公司ID,关键字,关键字类型,分页显示','close'=>0),
		'subsite' => array('attr'=>'type,列表名','close'=>0),
        'senior_jobfair_list' => array('attr'=>'type,列表名,显示数目,标题长度,开始位置,填补字符,日期范围,参会企业页,排序,分页显示,页面,列表页','close'=>0),
        'senior_jobfair_show' => array('attr'=>'type,列表名,招聘会id,标题长度,填补字符,参会企业页','close'=>0),
        'senior_jobfair_exhibitors_list' => array('attr'=>'type,列表名,招聘会ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),
        'school_list' => array('attr'=>'type,列表名,招聘会ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
        'school_show' => array('attr'=>'type,列表名,招聘会ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
        'school_talk_list' => array('attr'=>'type,列表名,院校ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
        'school_talk_show' => array('attr'=>'type,列表名,,院校ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
        'school_election_list' => array('attr'=>'type,列表名,院校ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
        'school_election_show' => array('attr'=>'type,列表名,,院校ID,显示数目,公司名称长度,开始位置,填补字符,日期范围,排序,分页显示,页面,列表页','close'=>0),  // todo 待完善
    );
    public function __call($method, $args) {
        $tag = substr($method, 1);
        if (!isset($this->tags[$tag])) return false;
        $_tag = $args[0];
        $_tag['cache'] = isset($_tag['cache']) ? intval($_tag['cache']) : 0;
        $_tag['列表名'] = isset($_tag['列表名']) ? trim($_tag['列表名']) : 'list';
        $_tag['type'] = isset($_tag['type']) ? trim($_tag['type']) : 'run';
        if (!$_tag['type']) return false;
        $parse_str  = '<?php ';
        if ($_tag['cache']) {
            //标签名-属性-属性值 组合标识
            ksort($_tag);
            $tag_id = md5($tag . '&' . implode('&', array_keys($_tag)) . '&' . implode('&', array_values($_tag)));
            //缓存代码开始
            $parse_str .= '$' . $_tag['列表名'] . ' = S(\'' . $tag_id . '\');';
            $parse_str .=  'if (false === $' . $_tag['列表名'] . ') { ';
        }
        $action = $_tag['type'];
        $class = '$tag_' . $tag . '_class';
        $parse_str .= $class . ' = new \\Common\\qscmstag\\' . $tag . 'Tag('.self::arr_to_html($_tag).');';
        $parse_str .= '$' . $_tag['列表名'] . ' = ' . $class . '->' . $action . '();';
        if($method != '_load'){
            $parse_str .= '$frontend = new \\Common\\Controller\\FrontendController;';
            $parse_str .= '$page_seo = $frontend->_config_seo('.self::config_seo().',$'.$_tag['列表名'].');';
        }
        if ($_tag['cache']) {
            //缓存代码结束
            $parse_str .= 'S(\'' . $tag_id . '\', $' . $_tag['列表名'] . ', ' . $_tag['cache'] . ');';
            $parse_str .= ' }';
        }
        $parse_str .= '?>';
        $parse_str .= $args[1];
        return $parse_str;
    }
    private static function config_seo() {
        $page_seo = D('Page')->get_page();
        $page = $page_seo[strtolower(MODULE_NAME).'_'.strtolower(CONTROLLER_NAME).'_'.strtolower(ACTION_NAME)];
        return 'array("pname"=>"'.$page['pname'].'","title"=>"'.$page['title'].'","keywords"=>"'.$page['keywords'].'","description"=>"'.$page['description'].'","header_title"=>"'.$page['header_title'].'")';
    }
    /**
     * 转换数据为HTML代码
     * @param array $data
     */
    private static function arr_to_html($data) {
        if (is_array($data)) {
            $str = 'array(';
                foreach ($data as $key=>$val) {
                    if (is_array($val)) {
                        $str .= "'$key'=>".self::arr_to_html($val).",";
                    } else {
                        if (strpos($val, '$')===0) {
                            $str .= "'$key'=>_I($val),";
                        } else {
                            $str .= "'$key'=>'".addslashes_deep($val)."',";
                        }
                    }
                }
                return $str.')';
            }
            return false;
        }
    }
