<?php

namespace Our\Model;

/**
 * 业务层的抽象类
 */
class Base {
    protected $_succ	= 'succ';
    protected $_fail	= 'fail';

    /**
     * 不允许克隆对象
     */
    public function __clone() {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }
    
    public function __construct() {
    }

    /**
     * 将要输出的数据格式成需要的形式返回
     * @param boolean $isSucc
     * @param mixed   $info
     * @return array
     */
    protected function _formatReturnData($isSucc, $info = null)
    {
        $res = array();
        if($isSucc) {
            $res['result'] = $this->_succ;
            $res['info'] = $info;
        } else {
            $res['result'] = $this->_fail;
            $res['reason'] = $info;
        }
        return $res;
    }

    protected function _formatReturnlist($isSucc, $info = null)
    {
        $res = array();
        if($isSucc) {
            $res['result'] = $this->_succ;
            $res['num'] = count($info);
            $res['list'] = $info;
        } else {
            $res['result'] = $this->_fail;
            $res['reason'] = $info;
        }
        return $res;
    }
    
    
    /**
    * 组装sql条件语句
    * @return string
    */
    public function formatCondition($data,$extraSql = '')
    {
        $condition = '';
        if(is_array($data) && count($data) > 0)
        {
            foreach($data as $key=>$value)
            {
                switch ($key)
                {
                    default:
                        $condition .= "AND `{$key}`='{$value}' ";
                        break;
                }

            }

            $condition = ltrim($condition, 'AND');
        }
        else
        {
            $condition = $data;
        }
        $condition.=$extraSql;
        return $condition;
    }        

}