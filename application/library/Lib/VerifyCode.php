<?php

/**
 * 验证码类
 * @author wangliuyang
 */

namespace Lib;
use Our\Model\Cache as Cache;

class VerifyCode {

    private $_width = 100;
    private $_height = 25;
    private $_codeNum = 4;
    private $_cacheKey = '';
    private $_cacheTime;
    public $mc;

    public function __construct($cacheKey = '', $width = 100, $height = 25, $codeNum = 4) {
        if (empty($cacheKey)) {
            $cacheKey = $this->getCurUserKey();
        }
        $this->_cacheKey = Cache::getMcKey('yzm',array($cacheKey));
        $this->_cacheTime= Cache::getMcKeyTime('yzm');
        $this->mc = new \Lib\Base\Memcache();

        $this->_width = $width;
        $this->_height = $height;
        $this->_codeNum = $codeNum;
    }

    /**
     * 随进获取验证码
     */
    public function getCode() {
        $codeType = mt_rand(1, 3);

        switch ($codeType) {
            case 1 :
                $this->getMathCode();
                break;
            case 2 :
                $this->getNumCode();
                break;
            case 3 :
                $this->getCharCode();
                break;
        }
    }

    /**
     * 获取数学验证码
     */
    public function getMathCode() {
        $im = imagecreate($this->_width, $this->_height);

        //imagecolorallocate($im, 14, 114, 180); // background color
        $red = imagecolorallocate($im, 255, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);

        $num1 = mt_rand(1, 20);
        $num2 = mt_rand(1, 20);

        //计算结果
        $ariType = mt_rand(1, 3);
        switch ($ariType) {
            case 1:
                $arithmetic = '+';
                $codeResult = $num1 + $num2;
                break;
            case 2:
                $arithmetic = '-';
                if ($num1 < $num2) {
                    $test_num = $num1;
                    $num1 = $num2;
                    $num2 = $test_num;
                }
                $codeResult = $num1 - $num2;
                break;
            case 3:
                $arithmetic = 'x';
                $num1 = mt_rand(1, 9);
                $num2 = mt_rand(1, 9);
                $codeResult = $num1 * $num2;
                break;
        }

        //存储数据
        $this->mc->set($this->_cacheKey, $codeResult, $this->_cacheTime);
        //$this->lib_redis->set($this->_cacheKey, $code_result);
        //$this->lib_redis->setTimeout($this->_cacheKey, $this->_cacheTime);

        $gray = imagecolorallocate($im, 118, 151, 199);
        $black = imagecolorallocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

        //画背景
        imagefilledrectangle($im, 0, 0, 100, 24, $black);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $gray);
        }

        imagestring($im, 5, 5, 4, $num1, $red);
        imagestring($im, 5, 30, 3, $arithmetic, $red);
        imagestring($im, 5, 45, 4, $num2, $red);
        imagestring($im, 5, 70, 3, "=", $red);
        imagestring($im, 5, 80, 2, "?", $white);

        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 数字验证码
     */
    public function getNumCode() {
        $code = "";
        for ($i = 0; $i < $this->_codeNum; $i++) {
            $code .= rand(0, 9);
        }
        //4位验证码也可以用rand(1000,9999)直接生成
        //存储数据
        $this->mc->set($this->_cacheKey, $code, $this->_cacheTime);

        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($this->_width, $this->_height);
        $black = imagecolorallocate($im, 0, 0, 0);
        $gray = imagecolorallocate($im, 200, 200, 200);
        $bgcolor = imagecolorallocate($im, 255, 255, 255);

        imagefill($im, 0, 0, $gray);

        //画边框
        imagerectangle($im, 0, 0, $this->_width - 1, $this->_height - 1, $black);

        //随机绘制两条虚线，起干扰作用
        $style = array(
            $black,
            $black,
            $black,
            $black,
            $black,
            $gray,
            $gray,
            $gray,
            $gray,
            $gray
        );
        imagesetstyle($im, $style);
        $y1 = rand(0, $this->_height);
        $y2 = rand(0, $this->_height);
        $y3 = rand(0, $this->_height);
        $y4 = rand(0, $this->_height);
        imageline($im, 0, $y1, $this->_width, $y3, IMG_COLOR_STYLED);
        imageline($im, 0, $y2, $this->_width, $y4, IMG_COLOR_STYLED);

        //在画布上随机生成大量黑点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $black);
        }
        //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $this->_codeNum; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 12);
        }
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 生成字符验证码
     */
    public function getCharCode() {
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $this->_codeNum; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }

        //存储数据
        $this->mc->set($this->_cacheKey, $code, $this->_cacheTime);

        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($this->_width, $this->_height);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $this->_width, $this->_height, $bgcolor);
        //画边框
        imagerectangle($im, 0, 0, $this->_width - 1, $this->_height - 1, $gray);
        //imagefill($im, 0, 0, $bgcolor);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $this->_codeNum; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 14);
        }
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 检查验证码
     */
    public function checkCode($code) {
        $verifyCode = $this->mc->get($this->_cacheKey);

        if ($verifyCode && $verifyCode == $code) {
            //删除验证码
            $this->mc->delete($this->_cacheKey);

            return true;
        } else {
            //删除验证码
            $this->mc->delete($this->_cacheKey);
        }

        return false;
    }

    /**
     * 获取当前用户唯一key
     */
    public function getCurUserKey() {
        $userKey = \Lib\Cookies::getCookie('verfiy_code_user');
        if (!$userKey) {
            $userKey = md5(microtime() . rand(1111, 9999));
            $userKey = substr($userKey, 8, 16);
            \Lib\Cookies::setCookie('verfiy_code_user', $userKey, 0, '/');
        }

        return $userKey;
    }

}
