<?php

/**
 * @todo	cookie加密存取
 * @author wangliuyang
 */

namespace Lib;

class Cookies {

    public static function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {


        if (!empty($value)) {
            $value = serialize($value);
            $encrypt = \Lib\Cookies\encrypt::get_method(ENCRYPT_KEY, 'aes');

            $value = $encrypt->encrypt($value);
        }

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public static function getCookie($name) {
        if (!empty($_COOKIE[$name])) {
            $encrypt = \Lib\Cookies\encrypt::get_method(ENCRYPT_KEY, 'aes');
            $value = $encrypt->decrypt($_COOKIE[$name]);
            return unserialize($value);
        }
        return false;
    }

}
