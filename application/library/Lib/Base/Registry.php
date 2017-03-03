<?php

namespace Lib\Base;

/**
 *
 * 单实例注册器对象
 * @author wangliuyang
 */
class Registry {

    public static $instance = null;
    private $_store = array();

    public static function get_instance() {
        if (is_null(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    public function is_valid($key) {
        if (array_key_exists($key, $this->_store))
            return true;
        else
            return false;
    }

    public function &get($key) {
        return $this->_store[$key];
    }

    public function set($key, &$obj) {
        $this->_store[$key] = &$obj;
    }

    /**
     * 清除注册表内数据
     *
     * @param String $key
     */
    public function clear($key) {
        if ($this->is_valid($key)) {
            unset($this->_store[$key]);
        }
        return true;
    }

    /**
     * 禁止直接new此对象,只能通过单态模式访问
     *
     */
    private function __construct() {
        
    }

}

?>