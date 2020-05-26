<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------
namespace Common\ORG;
class Page {
    
    // 分页栏每页显示的页数
    public $rollPage = 10;
    // 分页地址
    public $path = '';
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    protected $actualPage;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'<上一页','next'=>'下一页>','first'=>'第一页','last'=>'最后一页','theme'=>'%totalRow% %header% %nowPage%/%totalPage% 页 %first% %upPage% %linkPage% %downPage% %end%');
    // 默认分页变量名
    public $varPage;
    public $showname;
    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!isset($_GET[$this->varPage])) $this->varPage = 'page';
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        $this->actualPage   =   $this->nowPage;
        if(!empty($this->totalPages) && $this->nowPage>=$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $middle = ceil($this->rollPage/2); //中间位置

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                if(empty($_GET)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $_GET;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U($this->path,$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }

        // << < > >>
        $theFirst = $theEnd = '';
        if ($this->totalPages > $this->rollPage) {
            if($this->nowPage - $middle < 1){
                $theFirst   =   '';
            }else{
                $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
            }
            if($this->nowPage + $middle > $this->totalPages){
                $theEnd     =   '';
            }else{
                $theEndRow  =   $this->totalPages;
                $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
            }
        }

        // 1 2 3 4 5
        $linkPage = "";
        if ($this->totalPages != 1) {
            if ($this->nowPage < $middle) { //刚开始
                $start = 1;
                $end = $this->rollPage;
            } elseif ($this->totalPages < $this->nowPage + $middle - 1) {
                $start = $this->totalPages - $this->rollPage + 1;
                $end = $this->totalPages;
            } else {
                $start = $this->nowPage - $middle + 1;
                $end = $this->nowPage + $middle - 1;
            }
            $start < 1 && $start = 1;
            $end > $this->totalPages && $end = $this->totalPages;
            for ($page = $start; $page <= $end; $page++) {
                if ($page != $this->nowPage) {
                    $linkPage .= " <a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a>";
                } else {
                    $linkPage .= " <span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%linkPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$linkPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function fshow($jump=false) {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $middle         =   ceil($this->rollPage/2); //中间位置

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                if(empty($_GET)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $_GET;
                }
            }
            if($parameter['key'] && C('qscms_key_urlencode')==1 && !C('backend')){
                $parameter['key'] = urlencode(urldecode(urldecode($parameter['key'])));
            }
            $parameter[$p]  =   '__PAGE__';
            $this->showname ? $parameter['page']  =   '__PAGE__' : $parameter[$p]  =   '__PAGE__';
            $url            =   $this->showname ? url_rewrite($this->showname,$parameter) : U($this->path,$parameter);
            //$url            =   P($parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   "<a class='unable'>".$this->config['prev']."</a>";
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   "<a class='unable'>".$this->config['next']."</a>";
        }

        // << < > >>
        $theFirst = $theEnd = '';
        if ($this->totalPages > $this->rollPage) {
            if($this->nowPage - $middle < 1){
                $theFirst   =   '';
            }else{
                $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
            }
            if($this->nowPage + $middle > $this->totalPages){
                $theEnd     =   '';
            }else{
                $theEndRow  =   $this->totalPages;
                $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
            }
        }

        // 1 2 3 4 5
        $linkPage = "";
        if ($this->totalPages != 0) {
            if ($this->nowPage < $middle) { //刚开始
                $start = 1;
                $end = $this->rollPage;
            } elseif ($this->totalPages < $this->nowPage + $middle - 1) {
                $start = $this->totalPages - $this->rollPage + 1;
                $end = $this->totalPages;
            } else {
                $start = $this->nowPage - $middle + 1;
                $end = $this->nowPage + $middle - 1;
            }
            $start < 1 && $start = 1;
            $end > $this->totalPages && $end = $this->totalPages;
            for ($page = $start; $page <= $end; $page++) {
                if ($page != $this->nowPage) {
                    $linkPage .= " <a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a>";
                } else {
                    $linkPage .= " <span class='current'>".$page."</span>";
                }
            }
        }
        if($jump){
            $script = '<script>$(".J_page_jump").click(function(){var url=$(this).data("url");url = url.replace("__PAGE__",$("#page_jump").val());location.href=url;});</script>';
            $jump_page = '<input min="1" type="text" name="page" class="page_num" id="page_jump" value="'.$this->nowPage.'" placeholder="请输入页码" onkeyup="this.value=this.value.replace(/\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\D/g,\'\')" /><a class="J_page_jump" href="javascript:;" data-url="'.$url.'">跳转</a>'.$script;
        }else{
            $jump_page = '';
        }
        
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%linkPage%','%end%','%jump_page%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$linkPage,$theEnd,$jump_page),$this->config['theme']);
        return $pageStr;
    }
    public function get_page_params(){
        return array(
            'actualPage' => $this->actualPage,
            'nowPage' => $this->nowPage,
            'totalRows' => $this->totalRows,
            'totalPages' => $this->totalPages,
            'isfull' => $this->nowPage>=$this->totalPages
        );
    }
    /*
        ajax 刷新获取下一页数据
        返回 下一页 页数
    */
    public function ajax_show($type=1)
    {
        if(0 == $this->totalRows) return '';
        if($type && ($this->nowPage > $this->totalRows)) return 1;
        return $this->nowPage+1;
    }
}