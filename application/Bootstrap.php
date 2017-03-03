<?php
/**
 * @name Bootstrap
 * @author wangliuyang
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:\Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract{
    
    private $_config;
    //初始化配置
    public function _initConfig() {
        $this->_config = \Yaf\Application::app()->getConfig(ini_get('yaf.environ'));
        
        \Yaf\Registry::set('app_config', $this->_config);
    }
    
    /*
     * 初始化全局变量
     */
    public function _initVariable() {
        
        //项目目录
        define("APPLICATION_DIR", dirname(__FILE__) . '/');
        
        //PHP模式(cgi-fcgi、cli、 cli-server、fpm-fcgi等)
        define('PHP_MODE', php_sapi_name());
        if(PHP_MODE == 'cli') {
            return;
        }
        //可写临时缓存目录
        define('_CACHE_DIR_', isset($_SERVER['YAF_CACHE_DIR']) ? $_SERVER['YAF_CACHE_DIR'] : APP_PATH . '/cache');

        //模板目录
        define('TEMPLATE_DIR', 'views/'); 
        //smarty 编译目录
        define('VIEW_COMPILE_DIR', _CACHE_DIR_ . "/_templates_c"); 
        //smarty 缓存目录
        define('VIEW_CACHE_DIR', _CACHE_DIR_ . "/_templates_cache");
        //Libray 目录
        define("LIBRARY_DIR", dirname(__FILE__) . '/library');
        //当前域名
        define('BASE_URL', $_SERVER['HTTP_HOST']);
        //当前URL
        define('CUR_URL',  'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        //网站名称
        define('SITE_NAME', $this->_config->site->name);
        //cookies加密salt
        define('ENCRYPT_KEY', $this->_config->cookie->encryptKey);
        //cookies密钥常量
        define('SALES_KEY', $this->_config->cookie->saltKey);
        //应用坏境 develop;test;product
        define('APP_ENV', ini_get('yaf.environ'));
        //性能分析开关
        define('APP_ANALYZE', $this->_config->app->analyze);
        //phpconsole调试开关
        define('APP_DEBUG', $this->_config->app->debug);
        //网站域名
        define('SITE_DOMAIN', $this->_config->site->domain);
        
    }
        
    //初始化通用函数
    public function _initCommonFunctions(){  
        \Yaf\Loader::import(APPLICATION_DIR . '/common/function.php');
    }
    
    //注册自定义类库的命名空间
    public function _initLocalName() {
        
        \Yaf\Loader::getInstance()->registerLocalNamespace(array(
            'Lib','Smarty','Our','AliYun','Qiniu','Crontab'
        )); 
        
    }
    
    
    //配置路由
    public function _initRoute(\Yaf\Dispatcher $dispatcher) {
        
        $router = $dispatcher->getRouter();
        
        $config = new \Yaf\Config\Ini(APP_PATH . '/conf/route.ini', 'common');
        
        if ($config->routes) {
            $router->addConfig($config->routes);
        }

    }

      
    //配置redis
    public function _initRedis() {
        if(!isset($this->_config->redis)) {
            return;
        }        
        $redis = $this->_config->redis;
        
        $redis_master = explode(':', $redis->master->host);
        $redis_slave  = explode(':', $redis->slave->host);
        
        $config = array(
            'default' => array(
                'r' => array(
                    'host' => $redis_slave[0],
                    'port' => $redis_slave[1],
                    'auth' => $redis->auth,
                ),
                'w' => array(
                    'host' => $redis_master[0],
                    'port' => $redis_master[1],
                    'auth' => $redis->auth,
                ),
                'encoding' => 'utf8',
                'table_prefix' => $redis->prefix           
            ),

        );
        
        \Yaf\Registry::set('config_redis', $config);
    }
    
    //配置memcache
    public function _initMemcache() {
        if(!isset($this->_config->memcache)) {
            return;
        }        
        $memcached = $this->_config->memcache;
        $config = array(
            'default' => array(
                'servers' => $memcached->server,
                'key_prefix' => $memcached->prefix,
                'lifetime' => 86400,
            )
        );

        \Yaf\Registry::set('config_memcache', $config);
    }

    //配置session
    public function _initSession() {}

    //配置数据库
    public function _initMysql() {
        if(!isset($this->_config->mysql)) {
            return;
        }
        $mysql = $this->_config->mysql;
        $config = array(
            'default' => array(
                'r' => array(
                        'host' => $mysql->slave->host,
                        'port' => $mysql->slave->port,
                        'user' => $mysql->slave->user,
                        'pass' => $mysql->slave->pass,
                        'name' => $mysql->slave->name,
                ),
                'w' => array(
                        'host' => $mysql->master->host,
                        'port' => $mysql->master->port,
                        'user' => $mysql->master->user,
                        'pass' => $mysql->master->pass,
                        'name' => $mysql->master->name,
                ),
                'encoding' => 'utf8',
                'table_prefix' => $mysql->prefix
            ),

        );
        
        \Yaf\Registry::set('config_database', $config);
       
    }

    //配置mongo
    public function _initMongo() {
        if(!isset($this->_config->mongo)) {
            return;
        }
        
        $mongo = $this->_config->mongo;
        $config = array(
            'default' => array(
                'host' => $mongo->host,
                'port' => $mongo->port,
                'user' => $mongo->user,
                'pass' => $mongo->pass,
                'dbname' => $mongo->dbname,
                'persist' => $mongo->persist,
                'persist_key' => $mongo->persist_key,
                'query_safety' => '',
            ),

        );
        
        \Yaf\Registry::set('config_mongo', $config);
    }
    
    //初始化视图
    public function _initView(\Yaf\Dispatcher $dispatcher) {
        $dispatcher->disableView();//关闭其自动渲染
    }
    
    //初始化错误处理
    public function _initError() {

        register_shutdown_function(array(
            "Lib\Base\Exception",
            "shutdownFunction"
        ));
        
        set_error_handler("Lib\Base\Exception::errorHandler", APP_DEBUG ? E_ALL : E_ALL ^ E_NOTICE);
        set_exception_handler("Lib\Base\Exception::exceptionHandler");
        
    }
    
    //注册插件
    public function _initPlugin(\Yaf\Dispatcher $dispatcher) {
        if(PHP_MODE == 'cli') return;
        
        if( APP_ANALYZE && function_exists('xhprof_enable')) {
            $dispatcher->registerPlugin(new XhprofPlugin());
        }       
        
        if(APP_DEBUG){
            $dispatcher->registerPlugin(new DebugPlugin());
        }
        
        $dispatcher->registerPlugin(new LayoutPlugin());
    }    

}