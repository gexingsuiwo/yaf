<?php

/**
 * 基础异常类
 */
namespace Lib\Base;
define('E_FATAL',  E_ERROR | E_USER_ERROR |  E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR| E_PARSE );

class Exception extends \Exception {

    static $logType = 'mongodb';
    static $mongoErrorKey = 'yaf_php_error_log';
    static $errorType = '';
    /**
     * 构造函数
     * @param string $message
     * @param string $code
     */
    public function __construct($message = null, $code = null) {
        parent::__construct($message, $code);
    }

    //自定义错误输出方法 trigger_error会触发
    //只能捕获系统产生的一些Warning、Notice级别的Error
    public static function errorHandler($errno, $errstr, $errfile, $errline) {

        $errtype = self::parseErrorType($errno);
        $message = $errstr . ' in ' . $errfile . ' on line ' . $errline;
        
        $logdata['client_ip'] = PHP_MODE == 'cli' ? '' : get_client_ip(); //获取客户端IP
        $logdata['server_ip'] = PHP_MODE == 'cli' ? '' : get_server_ip(); //获取服务端IP
        $logdata['url'] = PHP_MODE == 'cli' ? '' : CUR_URL;
        $logdata['mode'] = PHP_MODE;
        $logdata['error_type'] = $errtype;
        $logdata['message'] = $message;
        $logdata['time'] = time();
        $logdata['type'] = self::$errorType ? self::$errorType : 'error';
        
        //写入日志
        self::writeLog($logdata);
        if(PHP_MODE != 'cli') {
            if(APP_DEBUG) {
                printf("<font color='#FF0000'><b>%s</b></font>:%s in<b>%s</b> on line <b>%d</b><br>\n", $errtype, $errstr, $errfile, $errline);
            }
        } else {
            printf("<font color='#FF0000'><b>%s</b></font>:%s in<b>%s</b> on line <b>%d</b><br>\n", $errtype, $errstr, $errfile, $errline);
        }
        
        error_log($message, 0);
    }

    // 用户定义的异常处理函数 trow new \Exception触发
    public static function exceptionHandler($exception) {
        self::$errorType = 'exception';
        self::errorHandler(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }

    /**
     * 系统运行中的错误输出方法
        调用情况：
        1、脚本正常退出时；
        2、在脚本运行(run-time not parse-time)出错退出时；
        3、用户调用exit方法退出时
     * php把要调用的函数调入内存。当页面所有ＰＨＰ语句都执行完成时，再调用此函数。
    */
    public static function shutdownFunction() {
        $error = error_get_last(); //shutdown只能抓到最后的错误，trace无法获取
        self::$errorType = 'shutdown';
        if($error && ($error["type"]===($error["type"] & E_FATAL))) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
            self::errorHandler($errno,$errstr,$errfile,$errline);
        }
    }

    /**
     * 所有错误类型
     */
    static function parseErrorType($type) {
        switch ($type) {
            case E_ERROR: // 1 
                return 'Fatal Error';
            case E_WARNING: // 2 
                return 'Warning';
            case E_PARSE: // 4 
                return 'Parse error';
            case E_NOTICE: // 8 
                return 'Notice';
            case E_CORE_ERROR: // 16 
                return 'Core error';
            case E_CORE_WARNING: // 32 
                return 'Core warning';
            case E_COMPILE_ERROR: // 64 
                return 'Compile error';
            case E_COMPILE_WARNING: // 128 
                return 'Compile warning';
            case E_USER_ERROR: // 256 
                return 'User error';
            case E_USER_WARNING: // 512 
                return 'User warning';
            case E_USER_NOTICE: // 1024 
                return 'User notice';
            case E_STRICT: // 2048 //
                return 'Strict Notice';
            case E_RECOVERABLE_ERROR: // 4096 
                return 'Recoverable Error';
            case E_DEPRECATED: // 8192 
                return 'Deprecated';
            case E_USER_DEPRECATED: // 16384 
                return 'User deprecated';

        }

        return $type;
    }

    /**
     * 写入错误日志
     */
    static function writeLog($data) {
        
        $data['stime'] = new \Mongodate(time());
        switch (self::$logType) {
            case 'mongodb' :
                if(class_exists('Mongo')) {
                    $mongo = new \Lib\Base\MyMongo();
                    #建立索引
                    $mongo->addIndex(self::$mongoErrorKey, array('stime' => '1'),array('expireAfterSeconds' => 86400));
                    $mongo->addIndex(self::$mongoErrorKey, array('time' => '1'));

                    $mongo->insert(self::$mongoErrorKey, $data);                   
                    break;
                }
        }
    }

}

?>