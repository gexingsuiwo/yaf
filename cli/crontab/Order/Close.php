<?php
/**
 * 订单关闭cron(* * * * *)
 * @author wangliuyang
 */

namespace Crontab\Order;

use Business\Admin\UsersModel;
class Close {
    
    public function index($params) {
       
        print_r($params);
        
        $model_users = new UsersModel();
        print_r($model_users->getList());
        return 'exec succ';
    }
}
