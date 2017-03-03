<?php
/**
 * pretty display debug info
 * Class DebugPlugin
 * @author wangliuyang
 * @since 2016/11/15
 */
class DebugPlugin extends Yaf\Plugin_Abstract {

	public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', true);

            require_once(APP_PATH . '/public/tools/phpconsole/src/PhpConsole/__autoload.php');
            
            $handler = PhpConsole\Handler::getInstance();
            /*You can override default Handler behavior: 
            $handler->setHandleErrors(false); // disable errors handling 
            $handler->setHandleExceptions(false); // disable exceptions handling 
            $handler->setCallOldHandlers(false); // disable passing errors & exceptions to prviously defined handlers */ 
            $handler->start(); // initialize handlers
     
        
	}

	public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
        /**
         * phpConsole调试(需要安装google扩展)
         */

	}
}
