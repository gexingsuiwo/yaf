<?php

namespace Lib\Base;

/**
 * @desc : 数据库操作基类, 保证同一个数据库只有一个连接实例存在
 * @author : liuyang
 */
class Mysql {
    
    /**
     * 读库连接实例
     * @var $_r
     */    
    private $_r = null;
    
    /**
     * 写库连接实例
     * @var $_w
     */
    private $_w = null;
    
    /**
     * 数据库配置
     * @var $_config
     */    
    private $_config = array();
    
    /**
     * debug开关
     * @var $_bebug
     */
    private $_debug = false;
    
    /**
     * 字符集
     * @var $_characterSet
     */
    private $_characterSet = 'utf8';

    /**
     * pdo类对象
     * @var PDOStmtement
     */
    private $_stmtement;

    public function __construct($config = array()) {
        $this->setConfig($config);
    }

    /**
     * 设置配置文件
     * @param array $config 数据库配置
     * @throws \InvalidArgumentException if either name or class_name is not
     * @see get() to retrieve an instance for the given $name.     * 
     */
    private function setConfig($config = array()) {
        if (!is_array($config) || !isset($config['r']['host']) || !isset($config['r']['port']) || !isset($config['r']['name']) || !isset($config['r']['user']) || !isset($config['r']['pass'])) {
            throw new \Exception('read database params wrong');
        }
        //读库参数
        $this->_config['r']['dsn'] = "mysql:host={$config['r']['host']};port={$config['r']['port']};dbname={$config['r']['name']}";
        $this->_config['r']['user'] = $config['r']['user'];
        $this->_config['r']['pass'] = $config['r']['pass'];

        //如果没有配置写库参数，默认为读写使用一个库
        $config['w'] = isset($config['w']) ? $config['w'] : $config['r'];
        if (!isset($config['w']['host']) || !isset($config['w']['port']) || !isset($config['w']['name']) || !isset($config['w']['user']) || !isset($config['w']['pass'])) {
            throw new \Exception('write database params wrong');
        }
        //写库参数
        $this->_config['w']['dsn'] = "mysql:host={$config['w']['host']};port={$config['w']['port']};dbname={$config['w']['name']}";
        $this->_config['w']['user'] = $config['w']['user'];
        $this->_config['w']['pass'] = $config['w']['pass'];
        //设置字符集
        if (isset($config['encoding'])) {
            $this->_characterSet = $config['encoding'];
        }
    }

    /**
     * 连接数据库
     *
     * @param String $type r|w
     * @return Object PDO
     */
    public function connect($type = 'r') {


        $conn = \Yaf\Registry::get($this->_config[$type]['dsn']);
        if (empty($conn)) {
            $conn = new \PDO($this->_config[$type]['dsn'], $this->_config[$type]['user'], $this->_config[$type]['pass']);
            //修改默认错误处理方式为抛出异常
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            if (!empty($this->_characterSet)) {
                $conn->exec("SET NAMES {$this->_characterSet}");
            }
            \Yaf\Registry::set($this->_config[$type]['dsn'], $conn);
        }
        $instance = '_' . $type;
        return $this->$instance = $conn;
    }

    /**
     * 查询执行sql
     *
     * @param String $sql
     * @param String $type r|w
     */
    public function query($sql, $type = 'r') {
        $instance = '_' . $type;
        if (!$this->$instance) {
            $this->connect($type);
        }
        $this->ping($type); //mysql ping
        $ret = $this->$instance->query($sql);
        
        if ($this->_debug) {
            echo $sql . "\r\n";
        }

        if (FALSE === $ret) {
            throw new \Exception(print_r($this->$instance->errorInfo()));
        }
        return $ret;
    }

    /**
     * 主库执行sql
     * @param string $sql
     * @throws \Lib\Base\Exception
     * @return string
     */
    public function exec($sql) {
        if (!$this->_w) {
            $this->connect('w');
        }
        $this->ping('w'); //mysql ping
        $ret = $this->_w->exec($sql);
        
        if ($this->_debug) {
            echo $sql . "\r\n";
        }

        if (FALSE === $ret) {
            throw new \Exception(print_r($this->_w->errorInfo()));
        }
        return $ret;
    }


    /**
     * 获取数据影响函数
     */
    public function rowCount() {
        if ($this->_stmtement) {
            return $this->_stmtement->rowCount();
        } else {
            throw new \Exception('Call the wrong data');
        }
    }

    
    /**
     * 获取自增ID
     */
    public function lastInsertId() {
        return $this->_w->lastInsertId();
    }

    /**
     * 从从库取符合条件的一条记录
     *
     * @param String $sql
     * @return Array
     */
    public function fetchRow($sql) {
        $this->_stmtement = $this->query($sql);
        if ($this->_stmtement) {
            return $this->_stmtement->fetch(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(print_r($this->_r->errorInfo()));
        }
    }

    /**
     * 从主库取符合条件的一条记录
     *
     * @param String $sql
     * @return Array
     */
    public function fetchMasterRow($sql) {
        $this->_stmtement = $this->query($sql, 'w');
        if ($this->_stmtement) {
            return $this->_stmtement->fetch(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(print_r($this->_w->errorInfo()));
        }
    }

    /**
     * 从从库取指定字段的数据
     *
     * @param string $sql
     * @return string
     */
    public function fetchOne($sql, $field) {
        $record = $this->fetchRow($sql);
        return $record[$field];
    }

    /**
     * 从主库取指定字段的数据
     *
     * @param string $sql
     * @return string
     */
    public function fetchMasterOne($sql, $field) {
        $record = $this->fetchMasterRow($sql);
        return $record[$field];
    }

    /**
     * 从从库取出所有满足条件的数据
     *
     * @param String $sql
     * @return Array
     */
    public function fetchAll($sql) {
        
        $this->_stmtement = $this->query($sql);
        if ($this->_stmtement) {
            return $this->_stmtement->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(print_r($this->_r->errorInfo()));
        }
    }

    /**
     * 从主库取出所有满足条件的数据
     * @param String $sql
     * @return Array
     */
    public function fetchMasterAll($sql) {
        $this->_stmtement = $this->query($sql, 'w');
        if ($this->_stmtement) {
            return $this->_stmtement->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            throw new \Exception(print_r($this->_w->errorInfo()));
        }
    }

    /**
     * 销毁某个数据库连接实例
     * @param String $type r|w
     */
    public function close($type = 'r') {
        $conn = '_' . $type;
        unset($this->$conn);
        //清除注册表内对象实例
        $lib_base_registry = lib_base_registry::get_instance();
        $lib_base_registry->clear($this->_config[$type]['dsn']);
    }

    /**
     *
     * pdo ping
     * 处理mysql has gone away问题,重新连接
     * @param $type
     * @return bool
     * */
    public function ping($type) {
        $instance = '_' . $type;
        $status = $this->$instance->getAttribute(\PDO::ATTR_SERVER_INFO);
        /* 连接超时和丢失连接的时候重新连 */
        if (strpos($status, 'gone away') || strpos($status, 'Lost connection')) {
            /* 进行PDO连接 */
            //分别把当前pdo对象置null，重新new pdo对象
            $this->close($type);
            $this->connect($type);
        }
    }

    /**
     * 开启事务
     * @param String $type r|w
     */
    public function beginTransaction($type='w')
    {
        $instance = '_' . $type;
        if (! $this->$instance)
        {
            $this->connect($type);
        }
        
        return $this->$instance->beginTransaction();
    }

    /**
     * 提交事务
     * @param String $type r|w
     */
    public function commit($type='w')
    {
        $instance = '_' . $type;
        if (! $this->$instance)
        {
            $this->connect($type);
        }
        
        $this->$instance->commit();
    }

    /**
     * 回滚事务
     * @param String $type r|w
     */
    public function rollBack($type='w')
    {
        $instance = '_'.$type;
        if (! $this->$instance)
        {
            $this->connect($type);
        }
        $this->$instance->rollBack();
    }    

}
