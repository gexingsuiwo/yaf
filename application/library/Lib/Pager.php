<?php
/**
 * 分页类
 * @author wangliuyang
 */
namespace Lib;

class Pager
{
    //总记录数
    private $_totalCount;
    //总页数
    private $_totalPage;
    //每页显示数
    private $_pageNum;
    //单前页
    private $_curPage;
    //上一页
    private $_prevPage;
    //下一页
    private $_nextPage;
    //链接
    private $_url;
    //分页间隔
    private $_interval;
        
    public function __construct($total_count = 0, $page_num = 20, $interval = 5)
    {
        $this->_totalCount = $total_count;
        $this->_pageNum = $page_num;
        $this->_interval = $interval;
        $this->_totalPage = ($this->_totalCount > 0) ? ceil($this->_totalCount / $this->_pageNum) : 1;
        $this->_curPage = \Lib\Context::get('p', \Lib\Context::T_INT, 1);
        $this->_curPage = ($this->_curPage < 1) ? 1 : $this->_curPage;
        $this->_curPage = ($this->_curPage > $this->_totalPage) ? $this->_totalPage : $this->_curPage;
        $this->_prevPage = ($this->_curPage <= 1) ? 1 : ($this->_curPage - 1);
        $this->_nextPage = ($this->_curPage >= $this->_totalPage) ? $this->_totalPage : ($this->_curPage + 1);
        $this->setUrl(); 
    }
    
    /**
     * 设置url
     * @param string $url
     */
    public function setUrl($url = '')
    {
        if(!empty($url))
        {
            $this->_url = $this->_url;            
        }
        else
        {
            $uri = $_SERVER['REQUEST_URI'];
            $uri_arr = explode('?', $uri);
            $parameters = $_GET;
            if(isset($parameters['p']))unset($parameters['p']);
            $parameters = http_build_query($parameters);
            $this->_url = empty($parameters) ? $uri_arr[0] : $uri_arr[0] . '?' . $parameters;
        }
        $this->_url .= (strpos($this->_url,"?") === false) ? '?' : '&';
    }
    
    /**
     * 返回总页数
     * @return number
     */
    public function getTotalPage()
    {
        return $this->_totalPage;
    }
    
    /**
     * 返回当前页
     */
    public function getCurrentPage()
    {
        return $this->_curPage;
    }
    
    /**
     * 返回上一页
     */
    public function getPrevPage()
    {
        return $this->_prevPage;
    }
    
    /**
     * 返回下一页
     */
    public function getNextPage()
    {
        return $this->_nextPage;
    }
    
    /**
     * 返回分页条件
     * @return string
     */
    public function getLimit()
    {
        $start = ($this->_curPage - 1) * $this->_pageNum;
        return $start . ',' . $this->_pageNum;
    }
    
    /**
     * 返回偏移量
     * @return number
     */
    public function getOffset(){
        $start = ($this->_curPage - 1) * $this->_pageNum;
        return $start;
    }
    
    /**
     * 返回URL
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * 获取分页HTML
     * @return string
     */
    public function getHtml()
    {
        $start = $this->_curPage > $this->_interval ? $this->_curPage - $this->_interval : 1;
        $end = $this->_curPage + $this->_interval;
        $end = $end > $this->_totalPage ? $this->_totalPage : $end;
        
        $html = '';
        if($this->_curPage > $this->_interval)
        {
            $html .= '<li><a href=' . $this->_url . 'p=1>首页</a></li>';
        }
        if($this->_curPage != $this->_prevPage)
        {
            $html .= '<li><a href=' . $this->_url . 'p=' . $this->_prevPage . '>上一页</a></li>';
        }
        
        for($i = $start; $i <= $end; $i++)
        {
            if($i == $this->_curPage)
            {
                $html .= '<li><a class="cur" href="javascript:void(0);"><strong>' . $i . '</strong></a></li>';
            }
            else
            {
                $html .= '<li><a href=' . $this->_url . 'p=' . $i . '>' . $i . '</a></li>';
            }
        }
        
        if($this->_curPage != $this->_totalPage)
        {
            $html .= '<li><a href=' . $this->_url . 'p=' . $this->_nextPage . '>下一页</a></li>';
        }
        if(($this->_curPage + $this->_interval) < $this->_totalPage)
        {
            $html .= '<li><a href=' . $this->_url . 'p=' . $this->_totalPage . '>末页</a></li>';
        }       

        $html .= '<li><a>共' . $this->_totalCount . '条记录&nbsp;' . $this->_totalPage . '页</a></li>';
        
        return $html;
    }
    
    /**
     * 获取AJAX HTML
     * @return string
     */
    public function getAjaxHtml()
    {
        $start = $this->_curPage > $this->_interval ? $this->_curPage - $this->_interval : 1;
        $end = $this->_curPage + $this->_interval;
        $end = $end > $this->_totalPage ? $this->_totalPage : $end;
    
        $html = '';
        if($this->_curPage > $this->_interval)
        {
            $html .= '<li><a href="javascript:void(0);" data_page=1>首页</a></li>';
        }
        if($this->_curPage != $this->_prevPage)
        {
            $html .= '<li><a href="javascript:void(0);" data_page=' . $this->_prevPage . '>上一页</a></li>';
        }
    
        for($i = $start; $i <= $end; $i++)
        {
            if($i == $this->_curPage)
            {
                $html .= '<li><a class="cur" href="javascript:void(0);" data_page=' . $i . '><strong>' . $i . '</strong></a></li>';
            }
            else
            {
                $html .= '<li><a href="javascript:void(0);" data_page=' . $i . '>' . $i . '</a></li>';
            }
        }
    
        if($this->_curPage != $this->_totalPage)
        {
            $html .= '<li><a href="javascript:void(0);" data_page=' . $this->_nextPage . '>下一页</a></li>';
        }
        if(($this->_curPage + $this->_interval) < $this->_totalPage)
        {
            $html .= '<li><a href="javascript:void(0);" data_page=' . $this->_totalPage . '>末页</a></li>';
        }
    
        $html .= '共' . $this->_totalCount . '条记录&nbsp;' . $this->_totalPage . '页';
    
        return $html;
    }
}