<?php

namespace Our\Model;
use Lib\Mysql\SqlBase;

class Cron extends Base
{
    private $_logTable;
    public function __construct()
    {
        $this->_logTable = new SqlBase('admin_cron_log');
    }
    
    public function addLog($data) {
        return $this->_logTable->insert($data);
    }
}
