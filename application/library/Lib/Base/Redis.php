<?php

/**
 * 基础Redis类
 * @author liuyang
 * Redis扩展项目地址（含手册）：
 * https://github.com/nicolasff/phpredis
 */

namespace Lib\Base;

class Redis {

    protected $_config;
    protected $_redis = NULL;
    protected $_w = NULL;
    protected $_r = NULL;
    protected $keyPrefix;

    public function __construct($db_config = '') {
        $config_redis = \Yaf\Registry::get('config_redis');
        if (empty($db_config)) {
            $this->_config = $config_redis['default'];
        } elseif (!is_array($db_config)) {
            $this->_config = $config_redis[$db_config];
        }

        if (!$this->_config) {
            throw new \Exception('redis数据库配置错误！');
        }

        $this->_w = $this->connect('w');
        $this->_r = $this->connect('r');
        $this->keyPrefix = isset($this->_config['key_prefix']) ? $this->_config['key_prefix'] : '';
    }

    /**
     * 设置配置文件
     * @param array $config
     */
    private function setConfig($config = array()) {
        if (!is_array($config) || !isset($config['slave']['params']['host']) || !isset($config['slave']['params']['port']) || !isset($config['slave']['params']['auth'])) {

            throw new \Exception('Redis ReadDatabase Params Wrong');
        }
        if (!is_array($config) || !isset($config['params']['host']) || !isset($config['params']['port']) || !isset($config['params']['auth'])) {
            throw new \Exception('Redis WriteDatabase Params Wrong');
        }
        $this->_config['r']['host'] = $config['slave']['params']['host'];
        $this->_config['r']['port'] = $config['slave']['params']['port'];
        $this->_config['r']['auth'] = $config['slave']['params']['auth'];

        $this->_config['w']['host'] = $config['params']['host'];
        $this->_config['w']['port'] = $config['params']['port'];
        $this->_config['w']['auth'] = $config['params']['auth'];
    }

    /**
     * 连接redis服务器
     */
    public function connect($type = 'w') {
        $key = $this->_config[$type]['host'] . '_' . $this->_config[$type]['port'];
        //$lib_base_registry = new \Yaf\Registry;
        $conn = \Yaf\Registry::get($key);

        if (!$conn) {
            $conn = new \Redis();
            $conn->connect($this->_config[$type]['host'], $this->_config[$type]['port']);
            if (!empty($this->_config[$type]['auth'])) {
                $conn->auth($this->_config[$type]['auth']);
            }
            \Yaf\Registry::set($key, $conn);
        }
        $instance = '_' . $type;
        return $this->$instance = $conn;
    }


    /**
     * 设置key前缀
     * @param string $key
     */
    protected function setPrefix($key) {
        return $this->keyPrefix . $key;
    }
    
    /**
     * 选择DB
     * 使用例子
     * $redis->select(2, 'w');
      $redis->set('h', '600');
      $redis->select(2, 'r');
      $redis->get('h');
     */
    public function select($db_number = 0, $db = 'w') {
        $db_number = intval($db_number);
        $db = '_' . $db;
        return $this->$db->select($db_number);
    }

    /**
     * 关闭链接
     */
    public function close() {
        $this->_w->close();
        $this->_r->close();
    }

    /**
     * get
     */
    public function get($key) {
        $key = $this->setPrefix($key);
        return $this->_r->get($key);
    }

    /**
     * set
     */
    public function set($key, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->set($key, $value);
    }

    /**
     * 设置过期时间
     * @param string $key
     * @param int $time 单位秒
     */
    public function setTimeout($key, $time) {
        $key = $this->setPrefix($key);
        return $this->_w->setTimeout($key, $time);
    }

    /**
     * 检测键是否存在
     * @param string $key
     */
    public function exists($key) {
        $key = $this->setPrefix($key);
        return $this->_r->exists($key);
    }

    /**
     * 设置key过期时间
     * @param string $key
     * @param string $time unix时间戳
     */
    public function expireat($key, $time) {
        $key = $this->setPrefix($key);
        return $this->_w->expireAt($key, $time);
    }

    /**
     * delete
     */
    public function delete($key) {
        $key = $this->setPrefix($key);
        return $this->_w->delete($key);
    }

    /**
     * deletes
     * 删除多条
     */
    public function deletes($keys) {
        foreach ($keys as &$key) {
            $key = $this->setPrefix($key);
        }
        $keys_str = implode(',', $keys);
        return $this->_w->delete($keys_str);
    }

    /**
     * move
     */
    public function move($key, $db) {
        $key = $this->setPrefix($key);
        return $this->_w->set($key, $db);
    }

    /**
     * 原子操作 自加1
     * @param string $key
     */
    public function incr($key) {
        $key = $this->setPrefix($key);
        return $this->_w->incr($key);
    }

    /**
     * 操作key 增加 $num值，值可为负
     * @param string $key 键
     * @param string $num 自增值
     */
    public function incrBy($key, $num) {
        $key = $this->setPrefix($key);
        return $this->_w->incrBy($key, $num);
    }

    /**
     * 原子递减 自减1
     * @param string $key
     */
    public function decr($key) {
        $key = $this->setPrefix($key);
        return $this->_w->decr($key);
    }

    /**
     * 原子递减 自减
     * @param string $key
     */
    public function decrBy($key, $value = 1) {
        $key = $this->setPrefix($key);
        return $this->_w->decrBy($key, $value);
    }

    //hash部分
    public function hSet($key, $filed, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->hSet($key, $filed, $value);
    }

    public function hGet($key, $filed) {
        $key = $this->setPrefix($key);
        return $this->_r->hGet($key, $filed);
    }

    public function hLen($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hLen($key);
    }

    public function hDel($key) {
        $key = $this->setPrefix($key);
        return $this->_w->hDel($key);
    }

    public function hKeys($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hKeys($key);
    }

    public function hVals($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hVals($key);
    }

    public function hGetAll($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hGetAll($key);
    }

    public function hExists($key, $field) {
        $key = $this->setPrefix($key);
        return $this->_r->hExists($key, $field);
    }

    public function hIncrBy($key, $field, $num) {
        $key = $this->setPrefix($key);
        return $this->_w->hIncrBy($key, $field, $num);
    }

    public function hmset($key, $arr) {
        $key = $this->setPrefix($key);
        return $this->_w->hmset($key, $arr);
    }

    public function hmGet($key, $arr) {
        $key = $this->setPrefix($key);
        return $this->_r->hmGet($key, $arr);
    }

    /**
     * 返回名称为key的hash中所有键
     * @param string $key KEY名称
     */
    public function hashKeys($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hKeys($key);
    }

    /**
     * 返回名称为key的hash中所有键
     * @param string $key KEY名称
     */
    public function hashVals($key) {
        $key = $this->setPrefix($key);
        return $this->_r->hVals($key);
    }

    /**
     * lPush
     * List
     */
    public function lPush($key, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->lPush($key, $value);
    }

    /**
     * POP
     */
    public function rPop($key) {
        $key = $this->setPrefix($key);
        return $this->_w->rPop($key);
    }

    /**
     * lsize
     */
    public function lSize($key) {
        $key = $this->setPrefix($key);
        return $this->_w->lSize($key);
    }

    /**
     * lget
     */
    public function lGet($key, $index) {
        $key = $this->setPrefix($key);
        return $this->_r->lGet($key, $index);
    }

    /**
     * rpoplpush
     * list 
     */
    public function rpoplpush($key, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->rpoplpush($key, $value);
    }

    /**
     * 获取指定起始与结束的队列元素
     * @param string $key
     * @param int $start
     * @param int $stop
     */
    public function lRange($key, $start, $stop) {
        $key = $this->setPrefix($key);
        return $this->_r->lRange($key, $start, $stop);
    }

    /**
     * 列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
     * @param string $key
     * @param int $start
     * @param int $end
     */
    public function ltrim($key, $start, $end) {
        $key = $this->setPrefix($key);
        return $this->_w->ltrim($key, $start, $end);
    }

    /**
     * zAdd
     * Sorted sets
     */
    public function zAdd($key, $score, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->zAdd($key, $score, $value);
    }

    /**
     * zDelete
     * Sorted sets
     */
    public function zDelete($key, $value) {
        $key = $this->setPrefix($key);
        return $this->_w->zDelete($key, $value);
    }

    /**
     * zRange
     * Sorted sets
     * 返回名称为 key 的 zset 中 member 元素的排名(按 score 从小到大排序)即下标
     */
    public function zRange($key, $start = 0, $end = 10, $withscores = true) {
        $key = $this->setPrefix($key);
        return $this->_r->zRange($key, $start, $end, $withscores);
    }

    /**
     * keys
     * @param string $key KEY名称
     */
    public function keys($key) {
        $key = $this->setPrefix($key);
        return $this->_r->keys($key);
    }

    public function Info() {
        return $this->_r->INFO();
    }

    /**
     * 得到一个key的生存时间
     * @param string $key KEY名称
     */
    public function getTtl($key) {
        $key = $this->setPrefix($key);
        return $this->_r->ttl($key);
    }

    /**
     * 获取KEY存储的值类型
     * none(key不存在) int(0)  string(字符串) int(1)   list(列表) int(3)  set(集合) int(2)   zset(有序集) int(4)    hash(哈希表) int(5)
     * @param string $key KEY名称
     */
    public function dataType($key) {
        $key = $this->setPrefix($key);
        return $this->_r->type($key);
    }

    /**
     * 判断key是否存在
     * @param string $key KEY名称
     */
    public function isExists($key) {
        $key = $this->setPrefix($key);
        return $this->_r->exists($key);
    }

}
