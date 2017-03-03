<?php
/**
 * 基础路由类
 * @author wangliuyang
 */

namespace Lib;
class Router
{
    protected static $site;
    protected static $controller;
    protected static $action;

    public function __construct()
    {

    }

    /**
     * 返回当前应用
     */
    public static function retSite()
    {
        self::$site = \Yaf\Dispatcher::getInstance()->getRequest()->getModuleName();

        return self::$site;
    }

    /**
     * 返回当前控制器
     */
    public static function retController()
    {
        self::$controller = \Yaf\Dispatcher::getInstance()->getRequest()->getControllerName();
        return self::$controller;
    }

    /**
     * 返回当前动作
     */
    public static function retAction()
    {
        self::$action = \Yaf\Dispatcher::getInstance()->getRequest()->getActionName();        
        return self::$action;
    }

}
