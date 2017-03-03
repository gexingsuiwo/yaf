<?php

/**
 * 基础Memcache类
 * @author liuyang
 */

namespace Lib\Base;

class Memcache {

    /**
     * Memcache 服务配置
     * @var array
     */
    protected $_config;

    /**
     * Memcache key前缀
     * @var string
     */
    public $keyPrefix;

    /**
     * Memcache对象
     * @var Memcache
     */
    protected $mc;

    public function __construct($dbConfig = '') {
        $config = \Yaf\Registry::get('config_memcache');
        if (empty($dbConfig)) {
            $this->_config = $config['default'];
        } elseif (!is_array($dbConfig)) {
            $this->_config = $config[$dbConfig];
        }

        if (!$this->_config) {
            throw new \Exception('memcache配置错误！');
        }

        $this->mc = new \Memcache;
        $servers = explode(" ", $this->_config['servers']);
        if (count($servers) == 0) {
            throw new \Exception('Memcache service configuration error');
        }
        foreach ($servers as $value) {
            $service = explode(":", $value);

            $this->mc->addServer($service[0], $service[1]);
        }
        $this->keyPrefix = $this->_config['key_prefix'];
    }

    /**
     * 设置key前缀
     * @param string $key
     */
    protected function setPrefix($key) {
        return $this->keyPrefix . $key;
    }

    /**
     * 获取指定索引数据
     * @param string|array $key
     */
    public function get($key) {
        $key = $this->setPrefix($key);
        //不获取缓存
        if (isset($_GET['nc'])) {
            $this->mc->delete($key);
            return false;
        }

        return $this->mc->get($key);
    }

    /**
     * 设置数据
     * @param string $key
     * @param string $value
     * @param int $lifetime
     */
    public function set($key, $value, $lifetime = -1) {
        $key = $this->setPrefix($key);
        if ($lifetime < 0) {
            $lifetime = $this->_config['lifetime'];
        }
        return $this->mc->set($key, $value, MEMCACHE_COMPRESSED, $lifetime);
    }

    /**
     * 删除指定索引值
     * @param string $key
     * @param int $timeout
     */
    public function delete($key, $timeout = 0) {
        $key = $this->setPrefix($key);
        return $this->mc->delete($key, $timeout);
    }

    /**
     * 清除所有数据
     */
    public function flush() {
        return $this->mc->flush();
    }

    /**
     * 增加指定索引的值
     * @param string $key
     * @param int $value
     */
    public function increment($key, $value = 1) {
        $key = $this->setPrefix($key);
        return $this->mc->increment($key, $value);
    }

    /**
     * 减小指定索引的值
     * @param string $key
     * @param int $value
     */
    public function decrement($key, $value = 1) {
        $key = $this->setPrefix($key);
        return $this->mc->decrement($key, $value);
    }

    /**
     * 返回服务器统计信息
     */
    public function getStats() {
        return $this->mc->getStats();
    }

    /**
     * 返回服务器池统计信息
     */
    public function getExtendedStats() {
        return $this->mc->getExtendedStats();
    }

}
