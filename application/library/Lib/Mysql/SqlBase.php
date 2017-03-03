<?php

/**
 * sql语句操作数据库
 * @author liuyang
 */

namespace Lib\Mysql;
class SqlBase extends \Lib\Mysql\Base
{
    public $_table = null;
    public $_configTarget = 'default';

    public function __construct($table = '')
    {
        if(!empty($table)) $this->_table = $table;
        parent::__construct();
    }

    /**
     * 根据sql语句查询
     * @param unknown $sql
     * @return boolean|multitype:
     */
    public function fetchAll($sql)
    {
        if(empty($sql)) return false;
        return $this->_db->fetchAll($sql);
    }
    
    /**
     * 根据sql语句查询一条记录
     * @param unknown $sql
     * @param unknown $fieldname
     * @return boolean|string
     */
    public function fetchOne($sql, $fieldname)
    {
        if(empty($sql) || empty($fieldname)) return false;
        $res = $this->_db->fetchOne($sql, $fieldname);
        return $res;
    }
    
    /**
     * 根据sql语句查询一条记录
     * @param unknown $sql
     * @param unknown $fieldname
     * @return boolean|string
     */
    public function fetchRow($sql)
    {
        if(empty($sql)) return false;
        $res = $this->_db->fetchRow($sql);
        return $res;
    }
    /**
     * 执行sql语句
     * @param unknown $sql
     * @return string
     */
    public function exec($sql)
    {
        return $this->_db->exec($sql);
    }    

}
