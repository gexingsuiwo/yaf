<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author wangliuyang
 */
class ErrorController extends \Our\Controller\Base{
        
	//从2.1开始, errorAction支持直接通过参数获取异常
	public function errorAction($exception) {
            if ($exception->getCode() > 100000) {
                //这里可以捕获到应用内抛出的异常
                //echo $exception->getCode();
                //echo $exception->getMessage();
                throw new \Exception($exception->getMessage());
                return;
            }
            switch ($exception->getCode()) {
                case 404://404
                case 515:
                case 516:
                case 517:
                    //输出404                    
                    $this->show404();
                    exit();
                    break;
                default :
                    break;
            }
            
            throw new \Exception($exception->getMessage());
	}
}
