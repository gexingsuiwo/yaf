<?php
//设置时区
date_default_timezone_set('Etc/GMT-8');

define('APP_PATH', realpath(dirname(__FILE__) . '/../'));

$application = new \Yaf\Application( APP_PATH . "/conf/application.ini");
$application->bootstrap()->run();

