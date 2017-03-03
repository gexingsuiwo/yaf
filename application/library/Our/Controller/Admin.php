<?php

namespace Our\Controller;

use Lib\Router;
use Business\Admin\LoginModel;

/**
 * admin模块控制器抽象类
 * @author wangliuyang 
 */
abstract class Admin extends Base {
    
    /**   
     * @var boolean 是否开启用户验证
     */    
    public $doAuth = true;
    
    /**
     *
     * @var array 当前后台用户
     */
    protected $_adminUserInfo = array();
    /**
     *
     * @var array 当前用户ID
     */
    protected $_adminUserId = 0;
    

    public function init() {
        parent::init();
        //不进行登录及权限验证
        $filterRoute = array(
            'Admin' => array(
                'Login' => array('index' => '','logout' => '','verifycode'=>''),
            ),
        );
        
        if(isset($filterRoute[Router::retSite()][Router::retController()][Router::retAction()] ))
        {
            return;
        }
        if($this->doAuth){
            
            $loginRes = $this->auth();
                                    
        }
                        
    }
    
    
    /**
     * 用户登陆认证
     * @return bool
     */
    protected function auth() {
        $loginModel = new LoginModel();
        $isLogin = $loginModel->isLogin();
        
        return $isLogin;
    }
     
    

}
