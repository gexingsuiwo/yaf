<?php
/**
 * 任务脚本入口文件
 * 标准执行方法 /usr/bin/php 项目路径/cli/crontab/Common.php "uri=Order/Close/index" "param1=21&param2=22"
 * @author wangliuyang
 */

namespace Crontab;

use Our\Model_Cron;

// 程序启动时间  
define('APP_START_TIME', microtime(true));

define("APP_PATH", realpath(dirname(__FILE__) . '/../../'));

$app = new \Yaf\Application(APP_PATH . "/conf/application.ini");
$app->getDispatcher()->catchException(true);  //捕获异常开关
$app->bootstrap()->execute(__NAMESPACE__ . "\main", $argc, $argv);

//主函数
function main($argc, $argv) {
    //非cli模式不能运行
    if(PHP_MODE != 'cli') {
        die("is not cli mode");
    }

    $request = array();
    foreach ($argv as $key => $value) {
        if ($key != 0) {  
            parse_str($value, $output);
            $request = array_merge($request, $output);
        }  
    }
    
    define(CUR_URL, trim($request['uri'], '/'));
    
    $uri_arr = explode('/', trim($request['uri'], '/'));
    if(count($uri_arr) < 2) {
        throw new \Lib\Base\Exception("uri format error");
    }
    
    $class_file = dirname(__FILE__) . '/' . $uri_arr[0] . '/' . $uri_arr[1] . '.php';//class 文件
    
    if(!file_exists($class_file)) {
        throw new \Lib\Base\Exception("class file is not exist");
    }
    
    require_once $class_file;
    $a = __NAMESPACE__ . '\\' . $uri_arr[0] . '\\' . $uri_arr[1];
    
    $cur_obj = new $a();
    if(!method_exists($cur_obj,  $uri_arr[2]))
    {
        throw new \Lib\Base\Exception("action is not exist");
    }
     
    //执行脚本文件
    $exec_res = call_user_func_array(array($cur_obj, $uri_arr[2]), array('request' => $request));
    
    //程序结束时间
    define('APP_END_TIME', microtime(true));
    
    //记录log
    log($request, $exec_res);
}

//记录cron执行日志
function log($request, $exec_res) {
    
    $log_data['begin_time'] = APP_START_TIME;
    $log_data['end_time'] = APP_END_TIME;
    $log_data['uri'] = $request['uri'];
    unset($request['uri']);
    $log_data['params'] = json_encode($request);
    $log_data['result'] = json_encode($exec_res);
    $model_cron = new Model_Cron();
    
    $model_cron->insert_log($log_data);
}
