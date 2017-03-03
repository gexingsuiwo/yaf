<?php

/**
 * @todo
 * @version	v1.0.0
 */

namespace Lib;

class Context {

    /**
     * 数值(类型)
     * @var int
     */
    const T_INT = 1;

    /**
     * 数字(类型)
     * @var int
     */
    const T_NUMBER = 2;

    /**
     * 单精度(类型)
     * @var int
     */
    const T_FLOAT = 3;

    /**
     * 双精度(类型)
     * @var int
     */
    const T_DOUBLE = 4;

    /**
     * 字符串(类型)
     * @var int
     */
    const T_STRING = 5;

    /**
     * 数组(类型)
     * @var int
     */
    const T_ARRAY = 6;

    /**
     * 布尔型(类型)
     * @var int
     */
    const T_BOOL = 7;

    /**
     * 取得GET方式提交的数据
     * @param mixed	$key		查询名称
     * @param int	$type		指定数据类型
     * @param mixed	$default	数据不存在时的默认值
     * @return mixed
     */
    public static function get($key, $type = null, $default = null) {        
        $result = isset($_GET[$key]) ? $_GET[$key] : $default;
        return self::validate($result, $type);
    }

    /**
     * 取得REQUEST方式提交的数据
     * @param mixed	$key		查询名称
     * @param int	$type		指定数据类型(Cls_Context::T_*)
     * @param mixed	$default	数据不存在时的默认值
     * @return mixed
     */
    public static function request($key, $type = null, $default = null) {
        $result = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
        return self::validate($result, $type);
    }

    /**
     * 取得POST方式提交的数据
     * @param mixed	$key		查询名称
     * @param int	$type		指定数据类型
     * @param mixed	$default	数据不存在时的默认值
     * @return mixed
     */
    public static function post($key, $type = null, $default = null) {
        $result = isset($_POST[$key]) ? $_POST[$key] : $default;
        return self::validate($result, $type);
    }

    /**
     * 根据提供的数据和类型，返回验证后的类型
     * @param mixed	$value	数值
     * @param int	$type	验证类型
     * @return mixed		验证后的数值
     */
    public static function validate($value, $type = null) {
        switch (strval($type)) {
            //数值
            case self::T_INT :
                return (int) $value;

            //数字
            case self::T_NUMBER :
                return \Lib\Validate::isNum($value) ? $value : '0';

            //浮点数
            case self::T_FLOAT :
            case self::T_DOUBLE :
                return (float) $value;

            //字符串
            case self::T_STRING :
                return (string) $value;

            //数组
            case self::T_ARRAY :
                return is_array($value) ? $value : array();

            //布尔型
            case self::T_BOOL :
                return $value == 'false' ? false : (boolean) $value;
        }
        return $value;
    }

    /**
     * 获取所有POST提交数据
     * @return array
     */
    public static function postAll() {
        return $_POST;
    }

    /**
     * 获取所有GET提交数据
     * @return array
     */
    public static function getAll() {
        $gets = $_GET;
        //去掉框架路由参数
        if (isset($gets['ctl'])) {
            unset($gets['ctl']);
        }
        if (isset($gets['act'])) {
            unset($gets['act']);
        }
        if (isset($gets['site'])) {
            unset($gets['site']);
        }        
        return $gets;
    }

    /**
     * 判断是否POST请求
     *
     */
    public static function isPost() {
        return 'post' == strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * 判断请求是否是AJAX请求,只支持jquery
     * @param boolean $exit	如果不是，是否自动停止程序执行
     * @return boolean
     */
    public static function isAjax($exit = false) {
        $result = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ? true : false;
        if ($result === false && $exit)
            exit('Access Deny');
        return $result;
    }

    /**
     * 判断是否是微信
     */
    public static function isWeixin() {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) {
            return false;
        }
        return true;
    }

}
