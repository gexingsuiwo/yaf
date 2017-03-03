<?php

/**
 * data基类
 * @author liuyang
 */

namespace Lib\Mysql;

class Base {

    /**
     * 数据库操作对象
     * @var lib_base_db
     */
    protected $_db = null;

    /**
     * 对应数据库配置，默认为default
     * @var string
     */
    protected $_config_target = 'default';

    /**
     * 数据表前缀
     * @var string
     */
    public $_tablePrefix;

    /**
     * 数据表名
     * @var string
     */
    public $_table = '';

    /**
     * 主键
     * @var string
     */
    public $pk = 'id';

    /**
     * 查询参数
     * @var array
     */
    public $options = array();

    /**
     * 事务开始
     * @var bool
     */
    private $_beginTransaction = false;

    /**
     * 锁表语句
     * @var string
     */
    protected $_lock = '';

    /**
     * 构造函数，获取并设置数据库配置信息
     * @throws lib_base_exception
     */
    public function __construct($table = '') {

        $dbConfig = \Yaf\Registry::get('config_database');

        if (!$dbConfig || !isset($dbConfig[$this->_config_target])) {
            throw new \Exception('数据库配置错误！');
        }
        if (!empty($table)) {
            $this->_table = $table;
        }
        $this->_tablePrefix = empty($this->_tablePrefix) ? $dbConfig[$this->_config_target]['table_prefix'] : $this->_tablePrefix;
        $this->_db = new \Lib\Base\Mysql($dbConfig[$this->_config_target]);
        $this->setTableName();
    }

    /**
     * 特殊方法实现
     * @param string $pMethod
     * @param array $pArgs
     * @return mixed
     */
    function __call($pMethod, $pArgs) {
# 连贯操作的实现
        if (in_array($pMethod, array('field', 'table', 'where', 'order', 'limit', 'page', 'having', 'group', 'distinct'), true)) {
            if ($pMethod == 'where') {
                $pArgs[0] = $this->formatCondition($pArgs[0]);
            }
            $this->options[$pMethod] = $pArgs[0];
            return $this;
        }
# 统计查询的实现
        if (in_array($pMethod, array('count', 'sum', 'min', 'max', 'avg'))) {
            $field = isset($pArgs[0]) ? $pArgs[0] : '*';
            return $this->fetchOne("$pMethod($field)");
        }
# 根据某个字段获取记录
        if ('ff' == substr($pMethod, 0, 2)) {
            return $this->where(strtolower(substr($pMethod, 2)) . "='{$pArgs[0]}'")->fRow();
        }
    }

    /**
     * 格式化条件
     * @param array|string $data
     */
    public function formatCondition($data) {
        $condition = '';
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $condition .= "AND `{$key}`='{$value}' ";
            }

            $condition = ltrim($condition, 'AND');
        } else {
            $condition = $data;
        }

        return $condition;
    }

    /**
     * 过滤数据
     * @param array $datas 过滤数据
     * @return bool
     */
    private function _filter(&$datas) {
        $tFields = $this->getFields();
        foreach ($datas as $k1 => &$v1) {
            if (isset($tFields[$k1])) {
                $v1 = strtr($v1, array('\\' => '', "'" => "'"));
            } else {
                unset($datas[$k1]);
            }
        }
        return $datas ? true : false;
    }

    /**
     * 查询条件
     * @param array $pOpt 条件
     * @return array
     */
    private function _options($pOpt = array()) {
        # 合并查询条件
        $tOpt = $pOpt ? array_merge($this->options, $pOpt) : $this->options;
        $this->options = array();
        # 数据表
        empty($tOpt['table']) && $tOpt['table'] = $this->_table;
        empty($tOpt['field']) && $tOpt['field'] = '*';
        return $tOpt;
    }

    /**
     * 添加记录
     */
    function insert($datas, $pReplace = false) {
        if ($this->_filter($datas)) {
            if ($this->_db->exec(($pReplace ? "REPLACE" : "INSERT") . " INTO `$this->_table`(`" . join('`,`', array_keys($datas)) . "`) VALUES ('" . join("','", $datas) . "')")) {
                return $this->_db->lastInsertId();
            }
        }
        return 0;
    }

    /**
     * 更新记录
     */
    function update($datas) {
        # 过滤
        if (!$this->_filter($datas))
            return false;
        # 条件
        $tOpt = array();
        if (isset($datas[$this->pk])) {
            $tOpt = array('where' => "$this->pk=' {$datas[$this->pk]}'");
        }
        $tOpt = $this->_options($tOpt);
        # 更新
        if ($datas && !empty($tOpt['where'])) {
            foreach ($datas as $k1 => $v1)
                $tSet[] = "`$k1`='$v1'";
            return $this->_db->exec("UPDATE `" . $tOpt['table'] . "` SET " . join(', ', $tSet) . " WHERE " . $tOpt['where']);
        }
        return false;
    }

    /**
     * 删除记录
     */
    function del() {
        if ($tArgs = func_get_args()) {
            # 主键删除
            $tSql = "DELETE FROM `$this->_table` WHERE ";
            if (intval($tArgs[0]) || count($tArgs) > 1) {
                return $this->_db->exec($tSql . $this->pk . ' IN(' . join(', ', array_map("intval", $tArgs)) . ')');
            }
            # 传入删除条件
            return $this->_db->exec($tSql . $tArgs[0]);
        }
        # 连贯删除
        $tOpt = $this->_options();
        if (empty($tOpt['where']) || !stripos($tOpt['where'], '='))
            return false;
        return $this->_db->exec("DELETE FROM `" . $tOpt['table'] . "` WHERE " . $tOpt['where']);
    }

    /**
     * 查找一条
     */
    function fRow($pId = 0) {
        if (false === stripos($pId, 'SELECT')) {
            $tOpt = $pId ? $this->_options(array('where' => $this->pk . ' = ' . abs($pId))) : $this->_options();
            $tOpt['where'] = empty($tOpt['where']) ? '' : ' WHERE ' . $tOpt['where'];
            $tOpt['order'] = empty($tOpt['order']) ? '' : ' ORDER BY ' . $tOpt['order'];
            $tSql = "SELECT {$tOpt['field']} FROM `{$tOpt['table']}` {$tOpt['where']} {$tOpt['order']}  LIMIT 0,1";
        } else {
            $tSql = & $pId;
        }

        $tResult = $this->_db->fetchRow($tSql);

        return $tResult;
    }

    /**
     * 查找一条 主库
     */
    function fMasterRow($pId = 0) {
        if (false === stripos($pId, 'SELECT')) {
            $tOpt = $pId ? $this->_options(array('where' => $this->pk . ' = ' . abs($pId))) : $this->_options();
            $tOpt['where'] = empty($tOpt['where']) ? '' : ' WHERE ' . $tOpt['where'];
            $tOpt['order'] = empty($tOpt['order']) ? '' : ' ORDER BY ' . $tOpt['order'];
            $tSql = "SELECT {$tOpt['field']} FROM `{$tOpt['table']}` {$tOpt['where']} {$tOpt['order']}  LIMIT 0,1";
        } else {
            $tSql = & $pId;
        }

        $tResult = $this->_db->fetchMasterRow($tSql);

        return $tResult;
    }

    /**
     * 查找一字段 ( 基于 fRow )
     *
     * @param string $pField
     * @return string
     */
    function fOne($pField) {
        $this->field($pField);
        if (($tRow = $this->fRow(0)) && isset($tRow[$pField])) {
            return $tRow[$pField];
        }
        return false;
    }

    /**
     * 查找一字段 ( 基于 fRow ) 主库
     *
     * @param string $pField
     * @return string
     */
    function fMasterOne($pField) {
        $this->field($pField);
        if (($tRow = $this->fMasterRow(0)) && isset($tRow[$pField])) {
            return $tRow[$pField];
        }
        return false;
    }

    /**
     * 查找多条
     */
    function fList($pOpt = array()) {
        if (!is_array($pOpt)) {
            $pOpt = array('where' => $this->pk . (strpos($pOpt, ',') ? ' IN(' . $pOpt . ')' : ' = ' . $pOpt));
        }
        $tOpt = $this->_options($pOpt);
        $tSql = "SELECT {$tOpt['field']} FROM  `{$tOpt['table']}`";
        $this->join && $tSql .= ' a ' . implode(' ', $this->join);

        empty($tOpt['where']) || $tSql .= ' WHERE ' . $tOpt['where'];
        empty($tOpt['group']) || $tSql .= ' GROUP BY ' . $tOpt['group'];
        empty($tOpt['order']) || $tSql .= ' ORDER BY ' . $tOpt['order'];
        empty($tOpt['having']) || $tSql .= ' HAVING ' . $tOpt['having'];
        empty($tOpt['limit']) || $tSql .= ' LIMIT ' . $tOpt['limit'];

        return $this->_db->fetchAll($tSql);
    }

    /**
     * 查找多条 主库
     */
    function fMasterList($pOpt = array()) {
        if (!is_array($pOpt)) {
            $pOpt = array('where' => $this->pk . (strpos($pOpt, ',') ? ' IN(' . $pOpt . ')' : ' = ' . $pOpt));
        }
        $tOpt = $this->_options($pOpt);
        $tSql = "SELECT {$tOpt['field']} FROM  `{$tOpt['table']}`";
        $this->join && $tSql .= ' a ' . implode(' ', $this->join);

        empty($tOpt['where']) || $tSql .= ' WHERE ' . $tOpt['where'];
        empty($tOpt['group']) || $tSql .= ' GROUP BY ' . $tOpt['group'];
        empty($tOpt['order']) || $tSql .= ' ORDER BY ' . $tOpt['order'];
        empty($tOpt['having']) || $tSql .= ' HAVING ' . $tOpt['having'];
        empty($tOpt['limit']) || $tSql .= ' LIMIT ' . $tOpt['limit'];

        return $this->_db->fetchMasterAll($tSql);
    }

    /**
     * 数据表名
     * @return array
     */
    function getTables() {
        return $this->_db->query("SHOW TABLES")->fetchAll(3);
    }

    /**
     * 数据表字段
     * @param string $table 表名
     * @return mixed
     */
    function getFields($table = '') {
        static $fields = array();
        $table || $table = $this->_table;
        # 静态 读取表字段
        if (empty($fields[$table])) {
            # 数据库 读取表字段

            $fields[$table] = array();
            if ($tQuery = $this->_db->query("SHOW FULL FIELDS FROM `$table`")) {
                foreach ($tQuery->fetchAll(2) as $v1) {
                    $fields[$table][$v1['Field']] = array('type' => $v1['Type'], 'key' => $v1['Key'], 'null' => $v1['Null'], 'default' => $v1['Default'], 'comment' => $v1['Comment']);
                }
            }
        }
        return $fields[$table];
    }

    /**
     * 联表语句
     * @var array
     */
    public $join = array();

    /**
     * 联表查询
     * @param string $table 联表名
     * @param string $where 联表条件
     * @param string $prefix INNER|LEFT|RIGHT 联表方式
     * @return $this
     */
    function join($table, $where, $prefix = '') {
        $this->join[] = " $prefix JOIN $table ON $where ";

        return $this;
    }

    /**
     * 事务开始
     */
    function begin() {
        # 已经有事务，退出事务
        $this->back();
        if (!$this->_db->beginTransaction()) {
            return false;
        }

        return $this->_beginTransaction = true;
    }

    /**
     * 事务提交
     */
    function commit() {
        if ($this->_beginTransaction) {
            $this->_beginTransaction = false;
            $this->_db->commit();
        }
        return true;
    }

    /**
     * 事务回滚
     */
    function back() {
        if ($this->_beginTransaction) {
            $this->_beginTransaction = false;
            $this->_db->rollback();
        }
        return false;
    }

    /**
     * 锁表
     */
    function lock($sql = 'FOR UPDATE') {
        $this->_lock = $sql;
        return $this;
    }

    /**
     * 设置表名称
     */
    public function setTableName() {
        $prefix_len = strlen($this->_tablePrefix);
        if (substr($this->_table, 0, $prefix_len) != $this->_tablePrefix) {
            $this->_table = $this->_tablePrefix . $this->_table;
        }
    }


}
