<?php

/*
 * 测试文件
 * @author wangliuyang
 */

use Qiniu\Upload;
use Our\Model\Sms as Sms;
use Our\Model\Image AS Image;

class IndexController extends \Our\Controller\Base {
    public $actions = array (
        "dummy" => "actions/Dummy_action.php",
      );
    public function indexAction() {

        $this->getView()->assign('param', 'smarty say hello to you!');
        $this->getView()->display('index/index');
        
    }
    
    public function testAction() {

        //mongo
        $mongo = new \Lib\Base\MyMongo();         
        #删除数据库
        //$mongo->dropCollection('test','collection1');
        #删除集合
        //$mongo->deleteAll('collection1');
        #删除索引
        //$mongo->removeAllIndexes('collection1');
        #建立索引
        //$mongo->addIndex('collection1', array('age' => '-1'), array('unique' => TRUE));
        #建立TTL索引()
         $mongo->addIndex('a1', array('time' => '1'), array('expireAfterSeconds' => 10));
         $mongo->addIndex('a1', array('age'=>'1'));
        $mongo->insert('a1', array('age'=>17,'time'=> new \Mongodate(time())));
        #列出索引
        dump($mongo->listIndexes('collection1'));
        #插入数据
        $mongo->insert('collection1', array('age' => '14','user' => 'test1', 'sex' => 'male'));
        $mongo->insert('collection1', array('age' => '22','user' => 'test2', 'sex' => 'male'));
        $mongo->insert('collection1', array('age' => '25','user' => 'test3', 'sex' => 'female'));
        $mongo->insert('collection1', array('age' => '35','user' => 'test4', 'sex' => 'female'));
        $mongo->insert('collection1', array('age' => '40','user' => 'test4', 'sex' => 'female', 'country' => array('province' => 'beijing', 'city' => 'beijing')));
        #判断不存在就插入 存在就修改
        $mongo->where(array('user'=>'test5'))->update('collection1', array('age' => '45'), array('upsert' => true, 'multi' => true));
        //$mongo->where(array('age' => '101'))->update('collection1', array('age' => '100'));
        //$mongo->update_all('collection1', array('age' => '101'));
        #查询
        //dump($mongo->whereBetween('age', '20', '50')->get('collection1'));
        //dump($mongo->select(array('age'))->whereIn('age', array('22', '35'))->get('collection1'));
        dump($mongo->like('user', 'test', 'im')->offset(2)->limit(10)->get('collection1'));//分页查询
        #聚合
        dump('count:' . $mongo->count('collection1'));
        //$mongo->delete();
        //dump($mongo->command(array('buildInfo' => 1, 'collStats' => 'collection1')));
        
        //redis用法
        echo "<pre>";
        $redis = new \Lib\Base\Redis();
        $key = 'key1';
        $redis->set($key, 'redis value already set');
        var_dump($redis->get($key));
        echo "</pre>";
        
        echo "<pre>";
        //memcache用法
        $mem = new \Lib\Base\MemCache();
        $key = 'key2';
        $mem->set($key, 'memcache value already set');
        var_dump($mem->get($key));
        echo "</pre>";
        
        echo "<pre>";
        //cookie用法
        $cookie = new \Lib\Cookies();
        $cookie->setcookie('test', 'Cookie value already set', time()+24*90*3600, '/');
        echo $cookie->getCookie('test');
        echo "</pre>";
        
        echo "<pre> 18611960850 is mobile:";
        $utils = new \Lib\Utils();
        
        var_dump($utils->_is_mobile('18611960850'));
        
        $validate = new \Lib\Validate();
        var_dump($validate->is_mobile('1861196085'));
        echo "</pre>";
        
        echo "<pre>";
        
        var_dump(\Lib\Context::get('site', \Lib\context::T_STRING, ''));
        echo "</pre>";
        
        echo "<pre>";
        $http = new \Lib\Http();
        var_dump($http->get('https://www.baidu.com'));
        echo "</pre>";
        
        //mysql用法
        $database = new \Lib\Mysql\SqlBase('user');
        $database->db='test';
        #获取表所有字段
        $result = $database->getFields();
        #插入
        #$result = $database->insert(array('user_name'=>'wangliuyang', 'phone'=>'18611960850'));
        #获取单条记录
        $result = $database->where("user_name='liuyang'")->fRow();
        #获取列表
        $result = $database->where("user_name='liuyang'")->fList();
        #获取所有表
        $result = $database->getTables();
        #更新
        $result = $database->where('id=2')->update(array('phone' => '18611960850'));
        #删除
        #$result = $database->where('id=4')->del();
        #$result = $database->del(1,2,3);
        #$result = $database->del("status=0");
        //$result = $database->ffUsername('liuyang');
        
        # 查询某字段，返回字符串
        $result = $database->fOne('count(*)');
        $result = $database->where("user_name='liuyang'")->fRow('user_name');
        #$result = $database->query('select * from yaf_user');
        
        
        # 列表
        $result = $database->where("id > 0")->field('id,user_name')->limit('0,2')->order('id desc')->fList();
        $result = $database->fList('13,14,15');
        $result = $database->fList(array('where'=>"user_name='liuyang'", 'limit'=>'0,2', 'order'=>'id desc', 'field'=>'id,user_name'));
        
        # 联表查询
        $result = $database->join('yaf_user_ext c', 'c.user_id=a.id', 'LEFT')->fList();
        
        #事务操作
        //$database->begin();
        //$database->insert(array('user_name'=>'wangliuyang', 'phone'=>'18611960850'));
        //$database->commit();
        var_dump($result);
    }
    
    public function forwardAction() {
        
    }
    

    public function ssAction() { 
        $this->redirect('/?site=Admin&ctl=Login&act=index');

    }
    /**
     * 上传图片demo
     */
    public function uploadAction() {
        if(\Lib\Context::isPost()) {
            $image = new Image();
            $res = $image->upload('test','file');
            var_dump($res);
        }
        
        
        $image = new Image();
        $data = 'data:image/png;base64,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';
        $res = $image->uploadBase64Pic($data);
        var_dump($res);
        
        var_dump($image->uploadLocalFile($res['info']['locurl']));
        $this->_view->display('index/upload');
    }
    
    public function smsAction() {
        //require_once LIBRARY_DIR . '/Our/Model/Sms.php';
        
        $model_sms = new Sms();
//var_dump($model_sms->checkCode('18611960850', '019650'));exit;
        $send_res = $model_sms->sendCode('18611960850');
        
        var_dump($send_res);
    }
    
}

