<?php

/**
 * @todo	验证类
 * @author wangliuyang
 */

namespace Lib;

class Validate {

    /**
     * 各种验证规则
     * @var <array>
     */
    private static $_rules = array(
        'email' => '/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/',
        'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
        'currency' => '/^\d+(\.\d+)?$/',
        'mobile' => '/^1[3458]\d{9}$/',
        'identityid' => '/^[A-Za-z0-9]{18}$|^[A-Za-z0-9]{15}$/', //普通身份证
        //'hk_identityid' => '/^[A-Za-z]{1,2}[0-9]{6}[1-9Aa]{1}$/',	//香港身份证
        'hk_identityid' => '/^[A-Za-z]{1,}[0-9]{1,}$/', //香港身份证
        'tw_identityid' => '/^[A-Za-z]{1}[1-2]\d{8}$/', //台湾身份证
        'am_identityid' => '/^\d{8}$/', //澳门身份证
        //'passport' => '/^(14|15)\d{7}$|^[Gg]\d{8}$|^[Pp]\d{7}$|^[Ss]\d{7,8}$|^[Dd]\d{1,10}$/',	//护照
        'passport' => '/^\d{1,}$|^[A-Za-z]{1,}\d{1,}$/', //护照
        'is_chinese_character' => '/^[\x{4e00}-\x{9fa5}]+$/u' //汉字
    );

    /**
     * @todo	是否字母加数字
     * @param <type> $value
     * @return <type>
     */
    public static function isAlnum($value) {
        return ctype_alnum($value);
    }

    /**
     * @todo	是否字母
     * @param <type> $value
     * @return <type>
     */
    public static function isAlpha($value) {
        return ctype_alpha($value);
    }

    /**
     * @todo	是否数字(必须是字符串类型)ctype_digit('42')为true，ctype_digit(42)为false
     * @param <type> $value
     * @return <type>
     */
    public static function isDigits($value) {
        return ctype_digit($value);
    }

    /**
     * @todo	是否数字isNum('42')和isNum(42)均返回true
     * @param <type> $value
     * @param <type> $max
     * @return <type>
     */
    public static function isNum($value, $max = null) {
        $regexp = $max ? '/^\d{1,' . $max . '}$/' : '/^\d+$/';
        return preg_match($regexp, $value);
    }

    /**
     * 判断是否id的格式
     * @param <int> $value
     * @return <boolean>
     */
    public static function isId($value) {
        $isNum = self::isNum($value);
        if ($isNum) {
            return $value > 0;
        }
        return false;
    }

    /**
     * @todo	是否email
     * @param <type> $value
     * @return <type>
     */
    public static function isEmail($value) {
        return self::regx($value, 'email');
    }

    /**
     * @todo	是否货币
     * @param <type> $value
     * @return <type>
     */
    public static function isCurrency($value) {
        return self::regx($value, 'currency');
    }

    /**
     * @todo	是否url
     * @param <type> $value
     * @return <type>
     */
    public static function isUrl($value) {
        return self::regx($value, 'url');
    }

    /**
     * @todo	是否手机号
     * @param <type> $value
     * @return <type>
     */
    public static function isMobile($value) {
        return self::regx($value, 'mobile');
    }

    /**
     * 是否有效数据（不为空即为有效）
     * @param <string> $value
     * @return boolean
     */
    public static function isEffective($value) {
        return !empty($value);
    }

    /**
     * 是否不是空字符串
     *
     * @param string $value
     * @return boolean
     */
    public static function noEmptyStr($value) {
        return ('' != $value);
    }

    /**
     * 是否大于0
     *
     * @param mixed $value
     * @return boolean
     */
    public static function greatZero($value) {
        return ($value > 0);
    }

    /**
     * @todo	通用验证
     * @param <type> $value
     * @param <type> $type
     * @return <type>
     */
    public static function regx($value, $type) {
        $vType = strtolower($type);
        $pattern = empty(self::$_rules[$vType]) ? $type : self::$_rules[$vType];
        if (!preg_match("/^\/.*?\/$/", $pattern)) {
            return false;
        }
        $num = preg_match($pattern, $value);
        if (false === $num || 0 === $num) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 是否简体中文
     * @param string $str
     * @return bool 
     */
    public static function isGb($str) {
        $str = mb_convert_encoding($str, 'gbk', 'utf-8');
        if (strlen($str) >= 2) {
            $str = strtok($str, '');
            if ((ord($str[0]) < 161) || (ord($str[0]) > 247)) {
                return false;
            } else {
                if ((ord($str[1]) < 161) || (ord($str[1]) > 254)) {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 是否繁体中文
     * @param string $str
     * @return bool
     */
    public static function isBig5($str) {
        $str = iconv('utf-8', 'gbk//IGNORE', $str);
        if (strlen($str) >= 2) {
            $str = strtok($str, "");
            if (ord($str[0]) < 161) {
                return false;
            } else {
                if (((ord($str[1]) >= 64) && (ord($str[1]) <= 126)) || ((ord($str[1]) >= 161) && (ord($str[1]) <= 254))) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @ignore
     */
    public function __call($name, $arguments) {
        return 'unkown method'; //错误信息不可修改
    }

    /**
     * @todo	是否普通身份证
     * @param <type> $value
     * @return <type>
     */
    public static function isIdentityId($value) {
        return self::regx($value, 'identityid');
    }

    /**
     * @todo	是否香港身份证
     * @param <type> $value
     * @return <type>
     */
    public static function isHkIdentityId($value) {
        return self::regx($value, 'hk_identityid');
    }

    /**
     * @todo	是否台湾身份证
     * @param <type> $value
     * @return <type>
     */
    public static function isTwIdentityId($value) {
        return self::regx($value, 'tw_identityid');
    }

    /**
     * @todo	是否澳门身份证
     * @param <type> $value
     * @return <type>
     */
    public static function isAmIdentityId($value) {
        return self::regx($value, 'am_identityid');
    }

    /**
     * @todo	是否护照
     * @param <type> $value
     * @return <type>
     */
    public static function isPassport($value) {
        return self::regx($value, 'passport');
    }

    /**
     * @todo	是否军官证
     * @param <type> $value
     * @return <type>
     */
    public static function isArmyId($value) {
        //$mb_strlen = mb_strlen($value, 'utf-8');
        //if ($mb_strlen != 10)
        //{
        //	return FALSE;	
        //}
        //
		////截取 前两个判断是否汉字
        //$before_two  = mb_substr($value, 0, 2, 'utf-8');
        //$is_chinese_character = preg_match(self::$_rules['is_chinese_character'], $before_two);
        //if (!$is_chinese_character)
        //{
        //	return FALSE;		
        //}
        //
		//$other_str = mb_substr($value, 2, $mb_strlen, 'utf-8'); 
        //if (!preg_match("/^\d{8}$/", $other_str))
        //{
        //	return FALSE;		
        //}
        $reg = "/^[\x{4e00}-\x{9fa5}]{1,}\d{1,}$|^[A-Za-z]{1,}\d{1,}$/u";
        $result = preg_match($reg, $value);

        return $result;
    }

    /**
     * @todo	是否汉字
     * @param <type> $value
     * @return <type>
     */
    public static function isChineseCharacter($value) {
        $result = preg_match(self::$_rules['is_chinese_character'], $value);
        return $result;
    }

}
