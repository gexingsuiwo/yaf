<?php

namespace Our\Controller;
use Lib\Router;

/**
 * 控制器抽象类
 * @author wangliuyang 
 */
 class Base extends \Yaf\Controller_Abstract {
    static $site;
    static $ctl;
    static $act;
    protected $_succ = 'succ';
    protected $_fail = 'fail';
    public $_view;
    
    public function init() {

        $this->_view = $this->getView();
        
        self::$site = Router::retSite();
        self::$ctl = Router::retController();
        self::$act = Router::retAction();
        
        $this->_view->assign('scripts_path', '/' . self::$site . '/resources/scripts' );
        $this->_view->assign('css_path', '/' . self::$site . '/resources/css' );
        $this->_view->assign('images_path', '/' . self::$site . '/resources/images' );
       
        $this->_view->assign('base_scripts_path', '/resources/scripts' );
        $this->_view->assign('base_css_path', '/resources/css' );
        $this->_view->assign('base_images_path', '/resources/images' );
        
        $this->_view->assign('site', self::$site);
        $this->_view->assign('ctl', self::$ctl);
        $this->_view->assign('act', self::$act);
        
        $this->_view->assign('site_name', SITE_NAME);       
        
    }
    
    
    //系统设置
    public function __isset($name)
    {
        return isset($this->$name);
    }
    
    public function __call($name, $arguments)
    {
        if (!$this->__isset($name))
        {
            throw new \Exception('此操作不存在' . $name);
            exit();
        }
    }    
    
     /**
     * 信息提示
     * @param $message 提示内容
     * @author liuyang
     */
    public function showMessage($message = '', $url = '')
    {
        $this->_view->assign('message', $message);
        $this->_view->assign('url', $url, 'NO_DENY');
        
        $this->_view->setScriptPath(APPLICATION_DIR  . TEMPLATE_DIR);
        $this->_view->display('message');
        exit;
    }
    
    /**
     * 404 页面不存在
     */
    public function show404($message = '')
    {
        $this->_view->assign('message', $message);
        
        $this->_view->setScriptPath(APPLICATION_DIR  . TEMPLATE_DIR);
        $this->_view->display('error_404');        
        exit;
    }



}
