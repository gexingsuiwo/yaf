<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author wangliuyang
 */
class LayoutPlugin extends Yaf\Plugin_Abstract {

    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        $tpl_dir = APPLICATION_DIR . '/modules/' . \Yaf\Dispatcher::getInstance()->getRequest()->getModuleName() . '/' . TEMPLATE_DIR;
        $config = array(
            'template_dir' => $tpl_dir,
            'compile_dir'  => _CACHE_DIR_ . "/template_c/",
            'cache_dir'    => _CACHE_DIR_  . "/_templates_cache/",
            'left_delimiter' => '<!--{',
            'right_delimiter' => '}-->',
            'caching' => 0,
            'cache_lifetime' => 60,
            'error_reporting' => 'E_ALL & ~E_NOTICE'
        ); 
        
        //整合smarty            
        $dispatcher = \Yaf\Dispatcher::getInstance();
        $dispatcher->disableView();
        $dispatcher->autoRender(false);
        
        $objSmarty = new Smarty_Adapter(null, $config);
        $dispatcher->setView($objSmarty);
        
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {

    }

}

