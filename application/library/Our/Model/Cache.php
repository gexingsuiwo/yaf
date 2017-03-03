<?php

/**
 * 缓存模型
 * @author wangliuyang
 *
 */
namespace Our\Model;

class Cache
{
    
    public $cacheKeyPrefix = 'v2_';
    
    function __construct() {
        
    }

    /**
     * 获取缓存键名 memcache
     * key命名规则建议：版本_模块_用途描述_参数1_参数2
     */
    static function MecacheKeys($keyword)
    {
        $cacheKeys = array(

            'verifyCode' => array(//验证码
                'key' => 'verifyCode','time' => '1800'
            ),
            'user_info' => array(//用户信息
                'key' => 'user_info_%d','time'=>'84600'
            )
                
        );
        
        return $keyword ? (isset($cacheKeys[$keyword]) ? $cacheKeys[$keyword] : false) : $cacheKeys;
    }
    
    
    /**
     * 获取缓存键名 redis
     * key命名规则建议：版本_模块_用途描述_参数1_参数2
     */
    static function RedisKeys($keyword)
    {
        $cacheKeys = array(
            'sms_code_mobile' => array(//短信 格式：项目名+手机号
                'key' => 'sms_code_%s_%s', 'time' => '86400'
            ),
            'login_fail_key' => array(
                'key' => 'login_fail_key_%s','time' => '1800'
            ),

        );
        return $keyword ? (isset($cacheKeys[$keyword]) ? $cacheKeys[$keyword] : false) : $cacheKeys;
    }   
    /**
     * 获取缓存键名 cookie
     * key命名规则建议：版本_模块_用途描述_参数1_参数2
     */
    static function CookieKeys($keyword)
    {
        $cacheKeys = array(
                
                'admin_login_num'=>array(
                        'key' => 'admin_login_num_%s','time' =>'84600'
                )
    
        );
    
        return $keyword ? (isset($cacheKeys[$keyword]) ? $cacheKeys[$keyword] : false) : $cacheKeys;
    }

    /**
     * 获取缓存key memcache
     * @param type $keyword
     * @param type $params
     * @return string
     */
    static function getMcKey($keyword, $params = array()) {
        $key = isset(self::MecacheKeys($keyword)['key']) ? self::MecacheKeys($keyword)['key'] : false;
        if(empty($params)){
            return $key ? $key : false;
        }
        return $key ? vsprintf($key, $params) : false;
    }
    
    /**
     * 获取缓存key时间 memcache
     * @param type $keyword
     * @param type $params
     * @return string
     */    
    static function getMcKeyTime($keyword) {
        
        return isset(self::MecacheKeys($keyword)['time']) ? self::MecacheKeys($keyword)['time'] : false;
    }

    /**
     * 获取缓存key redis
     * @param type $keyword
     * @param type $params
     * @return string
     */
    static function getRedisKey($keyword, $params = array()) {
        $key = isset(self::RedisKeys($keyword)['key']) ? self::RedisKeys($keyword)['key'] : false;
        if(empty($params)){
            return $key ? $key : false;
        }
        return $key ? vsprintf($key, $params) : false;
    }
    
    /**
     * 获取缓存key时间 redis
     * @param type $keyword
     * @param type $params
     * @return string
     */    
    static function getRedisKeyTime($keyword) {
        return isset(self::RedisKeys($keyword)['time']) ? self::RedisKeys($keyword)['time'] : false;         
    }    
    
    /**
     * 获取缓存key memcache
     * @param type $keyword
     * @param type $params
     * @return string
     */
    static function getCookieKey($keyword, $params = array()) {
        $key = isset(self::CookieKeys($keyword)['key']) ? self::CookieKeys($keyword)['key'] : false;
        if(empty($params)){
            return $key ? $key : false;
        }
        return $key ? vsprintf($key, $params) : false;
    }
    
    /**
     * 获取缓存key时间 memcache
     * @param type $keyword
     * @param type $params
     * @return string
     */
    static function getCookieKeyTime($keyword) {
    
        return isset(self::CookieKeys($keyword)['time']) ? self::CookieKeys($keyword)['time'] : false;
    }
    
}